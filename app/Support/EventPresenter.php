<?php

namespace App\Support;

use App\Models\Community;
use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Support\Collection;

class EventPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function summary(Event $event): array
    {
        $proof = $event->socialProof();

        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'emoji' => $event->emoji,
            'description' => $event->description,
            'starts_at' => $event->starts_at->toIso8601String(),
            'starts_at_label' => $event->starts_at->timezone('Pacific/Auckland')->format('D j M · g:ia'),
            'ends_at' => $event->ends_at?->toIso8601String(),
            'venue_name' => $event->venue_name,
            'venue_address' => $event->venue_address,
            'suburb' => $event->suburb,
            'capacity' => $event->capacity,
            'price_cents' => $event->price_cents,
            'price_label' => $event->formattedPrice(),
            'stripe_price_id' => $event->stripe_price_id,
            'stripe_product_name' => $event->stripe_product_name,
            'status' => $event->status,
            'spots_remaining' => $proof['spots_remaining'],
            'going' => $proof['going'],
            'waitlist' => $event->waitlistCount(),
            'coming_alone' => $proof['coming_alone'],
            'coming_with_friend' => $proof['coming_with_friend'],
            'newcomers' => $proof['newcomers'],
            'is_full' => $event->isFull(),
            'has_ended' => $event->hasEnded(),
            'has_started' => $event->hasStarted(),
            'url' => route('events.show', [$event->community->slug, $event->slug]),
            'community' => [
                'id' => $event->community->id,
                'name' => $event->community->name,
                'slug' => $event->community->slug,
                'city' => $event->community->city,
                'tagline' => $event->community->tagline,
            ],
        ];
    }

    /**
     * @param  Collection<int, Event>  $events
     * @return list<array<string, mixed>>
     */
    public static function collection(Collection $events): array
    {
        return $events->map(fn (Event $event) => self::summary($event))->values()->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function currentRsvp(?User $user, Event $event): ?array
    {
        if (! $user) {
            return null;
        }

        $rsvp = $event->rsvps()->where('user_id', $user->id)->first();

        if (! $rsvp) {
            return null;
        }

        return [
            'id' => $rsvp->id,
            'status' => $rsvp->status,
            'party_size' => $rsvp->party_size,
            'confirmed' => $rsvp->isConfirmed(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function attendees(Event $event): array
    {
        return $event->confirmedRsvps()
            ->with('user.interests')
            ->get()
            ->map(fn (Rsvp $rsvp) => [
                'id' => $rsvp->user->id,
                'name' => $rsvp->user->name,
                'suburb' => $rsvp->user->suburb,
                'party_size' => $rsvp->party_size,
                'coming_alone' => $rsvp->party_size === 1,
                'events_attended' => $rsvp->user->attendedEventsCount(),
                'interests' => $rsvp->user->interests->map(fn ($interest) => [
                    'name' => $interest->name,
                    'emoji' => $interest->emoji,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function communityCard(Community $community): array
    {
        return [
            'id' => $community->id,
            'name' => $community->name,
            'slug' => $community->slug,
            'city' => $community->city,
            'tagline' => $community->tagline,
            'description' => $community->description,
            'member_count' => $community->members()->count(),
        ];
    }
}
