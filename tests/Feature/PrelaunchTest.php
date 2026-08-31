<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PrelaunchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auckland.prelaunch' => true]);
    }

    public function test_homepage_hides_events_during_prelaunch(): void
    {
        $admin = User::factory()->admin()->create();
        $community = Community::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
        ]);
        $community->addMember($admin, 'organizer');

        Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $admin->id,
            'title' => 'Friday Football',
            'slug' => 'friday-football',
            'emoji' => '⚽',
            'description' => 'Test',
            'starts_at' => now()->addDays(3),
            'venue_name' => 'Mt Eden',
            'capacity' => 20,
            'price_cents' => 1500,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('events', 0)
                ->where('waitlistCount', 1));
    }

    public function test_public_event_pages_are_hidden_during_prelaunch(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $community = Community::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
        ]);

        $event = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $admin->id,
            'title' => 'Friday Football',
            'slug' => 'friday-football',
            'emoji' => '⚽',
            'description' => 'Test',
            'starts_at' => now()->addDays(3),
            'venue_name' => 'Mt Eden',
            'capacity' => 20,
            'price_cents' => 1500,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $this->actingAs($member)
            ->get(route('events.show', [$community->slug, $event->slug]))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('events.show', [$community->slug, $event->slug]))
            ->assertOk();
    }

    public function test_events_index_shows_waitlist_during_prelaunch(): void
    {
        $this->get(route('events.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('events/Index')
                ->has('events', 0));
    }
}
