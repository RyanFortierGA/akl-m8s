<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/login')->assertRedirect('/login');
    }

    public function test_members_cannot_open_admin(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admins_are_sent_to_the_admin_desk_after_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_create_an_event_and_attach_a_stripe_price(): void
    {
        [$admin] = $this->aucklandClub();

        $this->actingAs($admin)
            ->post('/admin/events', [
                'title' => 'Friday Football',
                'emoji' => '⚽',
                'description' => 'Come alone.',
                'starts_at' => now()->addDays(5)->timezone('Pacific/Auckland')->format('Y-m-d\TH:i'),
                'venue_name' => 'Mt Eden 5-a-side',
                'venue_address' => 'Mt Eden',
                'suburb' => 'Mt Eden',
                'capacity' => 20,
                'price' => 15,
                'stripe_price_id' => 'price_friday_football',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'title' => 'Friday Football',
            'stripe_price_id' => 'price_friday_football',
            'price_cents' => 1500,
            'status' => Event::STATUS_PUBLISHED,
        ]);
    }

    public function test_admin_can_see_signup_and_waitlist_counts(): void
    {
        [$admin, $community] = $this->aucklandClub();
        $confirmed = User::factory()->create();
        $waitlisted = User::factory()->create();

        $event = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $admin->id,
            'title' => 'Bowling',
            'slug' => 'bowling-night',
            'emoji' => '🎳',
            'description' => 'Lanes are booked.',
            'starts_at' => now()->addDays(4),
            'venue_name' => 'Kingsland Bowl',
            'capacity' => 1,
            'price_cents' => 0,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        Rsvp::query()->create([
            'event_id' => $event->id,
            'user_id' => $confirmed->id,
            'status' => Rsvp::STATUS_CONFIRMED,
            'party_size' => 1,
        ]);

        Rsvp::query()->create([
            'event_id' => $event->id,
            'user_id' => $waitlisted->id,
            'status' => Rsvp::STATUS_WAITLISTED,
            'party_size' => 1,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/Dashboard')
                ->where('stats.signups', 1)
                ->where('stats.waitlist', 1)
                ->where('upcoming.0.signups', 1)
                ->where('upcoming.0.waitlist', 1));

        $this->actingAs($admin)
            ->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/Show')
                ->where('event.signups', 1)
                ->where('event.waitlist', 1)
                ->has('signups', 1)
                ->has('waitlist', 1));
    }

    /**
     * @return array{0: User, 1: Community}
     */
    private function aucklandClub(): array
    {
        $admin = User::factory()->admin()->create();

        $community = Community::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
            'tagline' => 'Meet people. Do stuff. Make mates.',
        ]);

        return [$admin, $community];
    }
}
