<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use App\Support\Auckland;
use App\Support\EventPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        assert($user instanceof User);

        $upcoming = Event::query()
            ->with('community')
            ->published()
            ->upcoming()
            ->whereHas('rsvps', fn ($query) => $query
                ->where('user_id', $user->id)
                ->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED, Rsvp::STATUS_WAITLISTED]))
            ->get();

        $past = Event::query()
            ->with('community')
            ->whereHas('rsvps', fn ($query) => $query
                ->where('user_id', $user->id)
                ->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED]))
            ->past()
            ->limit(8)
            ->get();

        $discover = Auckland::prelaunch()
            ? collect()
            : Event::query()
                ->with('community')
                ->published()
                ->upcoming()
                ->limit(6)
                ->get();

        $mates = Connection::query()
            ->with(['mate', 'event.community'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (Connection $connection) => [
                'id' => $connection->mate->id,
                'name' => $connection->mate->name,
                'suburb' => $connection->mate->suburb,
                'instagram' => $connection->contact_shared_at ? $connection->mate->instagramHandle() : null,
                'phone' => $connection->contact_shared_at ? $connection->mate->phone : null,
                'contact_shared' => (bool) $connection->contact_shared_at,
                'card_url' => route('cards.show', $connection->mate->contact_token),
                'met_at' => $connection->event?->title,
                'met_at_emoji' => $connection->event?->emoji,
                'met_on' => $connection->event?->starts_at?->timezone('Pacific/Auckland')->format('j M Y'),
            ]);

        $promptEvent = $past->first(fn (Event $event) => $event->hasEnded());

        return Inertia::render('Dashboard', [
            'upcoming' => EventPresenter::collection($upcoming),
            'past' => EventPresenter::collection($past),
            'discover' => EventPresenter::collection($discover),
            'mates' => $mates->values()->all(),
            'meetPrompt' => $promptEvent ? [
                'title' => $promptEvent->title,
                'emoji' => $promptEvent->emoji,
                'url' => route('events.meet', [$promptEvent->community->slug, $promptEvent->slug]),
            ] : null,
            'stats' => [
                'events_attended' => $user->attendedEventsCount(),
                'mates' => Connection::query()->where('user_id', $user->id)->count(),
                'is_admin' => $user->isAdmin(),
            ],
        ]);
    }
}
