<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRsvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_reserve_a_free_event(): void
    {
        $organizer = User::factory()->create();
        $member = User::factory()->create();

        $community = Community::query()->create([
            'owner_id' => $organizer->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
            'tagline' => 'Make mates',
        ]);

        $community->addMember($organizer, 'organizer');

        $event = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $organizer->id,
            'title' => 'Friday Football',
            'slug' => 'friday-football',
            'emoji' => '⚽',
            'description' => 'Come alone.',
            'starts_at' => now()->addDays(3),
            'venue_name' => 'Mt Eden 5-a-side',
            'capacity' => 20,
            'price_cents' => 0,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $this->actingAs($member)
            ->post(route('events.rsvp', [$community->slug, $event->slug]))
            ->assertRedirect();

        $this->assertDatabaseHas('rsvps', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'confirmed',
        ]);
    }
}
