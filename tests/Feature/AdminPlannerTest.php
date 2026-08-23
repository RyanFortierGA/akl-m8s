<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use App\Support\EventBudget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminPlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_break_even_math_from_costs_and_ticket_price(): void
    {
        $admin = User::factory()->admin()->create();
        $community = Community::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
            'platform_fee_percent' => 10,
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
            'venue_cost_cents' => 18000,
            'host_cost_cents' => 2000,
            'other_cost_cents' => 0,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $budget = EventBudget::for($event);

        // $200 costs, $15 ticket, 10% fee => $13.50 net => ceil(200/13.5) = 15 tickets
        $this->assertSame(15, $budget['break_even_tickets']);
        $this->assertSame(20000, $budget['total_cost_cents']);
    }

    public function test_admin_can_open_planner_and_people(): void
    {
        $admin = User::factory()->admin()->create();
        Community::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
        ]);

        $this->actingAs($admin)
            ->get('/admin/planner')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/Planner'));

        $this->actingAs($admin)
            ->get('/admin/people')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/People'));
    }

    public function test_admin_can_save_event_costs_and_see_profit(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $community = Community::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
            'platform_fee_percent' => 10,
        ]);

        $event = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $admin->id,
            'title' => 'Bowling',
            'slug' => 'bowling',
            'emoji' => '🎳',
            'description' => 'Test',
            'starts_at' => now()->addDays(4),
            'venue_name' => 'Lanes',
            'capacity' => 16,
            'price_cents' => 2500,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        Rsvp::query()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => Rsvp::STATUS_CONFIRMED,
            'party_size' => 1,
            'amount_paid_cents' => 2500,
            'platform_fee_cents' => 250,
        ]);

        $this->actingAs($admin)
            ->put("/admin/events/{$event->id}", [
                'title' => 'Bowling',
                'emoji' => '🎳',
                'description' => 'Test',
                'starts_at' => now()->addDays(4)->timezone('Pacific/Auckland')->format('Y-m-d\TH:i'),
                'venue_name' => 'Lanes',
                'capacity' => 16,
                'price' => 25,
                'venue_cost' => 100,
                'host_cost' => 0,
                'other_cost' => 20,
                'cost_notes' => 'Lanes + shoes',
            ])
            ->assertRedirect();

        $event->refresh();
        $this->assertSame(10000, $event->venue_cost_cents);
        $this->assertSame(2000, $event->other_cost_cents);

        $budget = EventBudget::for($event);
        // revenue 25 - fee 2.50 - costs 120 = -97.50
        $this->assertSame(-9750, $budget['profit_cents']);
        $this->assertFalse($budget['is_profitable']);

        $this->actingAs($admin)
            ->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/Show')
                ->where('budget.profit_cents', -9750)
                ->where('budget.break_even_tickets_label', '6'));
    }
}
