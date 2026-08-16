<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Connection;
use App\Models\Event;
use App\Models\EventReview;
use App\Support\EventPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MeetController extends Controller
{
    public function show(string $community, string $event, Request $request): Response
    {
        $record = $this->findEvent($community, $event);
        abort_unless($this->attended($request, $record), 403);

        $mates = Connection::query()
            ->where('user_id', $request->user()->id)
            ->pluck('mate_id')
            ->all();

        $review = EventReview::query()
            ->where('event_id', $record->id)
            ->where('user_id', $request->user()->id)
            ->first();

        $people = collect(EventPresenter::attendees($record))
            ->reject(fn (array $person) => $person['id'] === $request->user()->id)
            ->map(fn (array $person) => [
                ...$person,
                'connected' => in_array($person['id'], $mates, true),
            ])
            ->values()
            ->all();

        return Inertia::render('events/Meet', [
            'event' => EventPresenter::summary($record),
            'people' => $people,
            'review' => $review ? [
                'rating' => $review->rating,
                'would_hang_again' => $review->would_hang_again,
            ] : null,
        ]);
    }

    public function store(string $community, string $event, Request $request): RedirectResponse
    {
        $record = $this->findEvent($community, $event);
        abort_unless($this->attended($request, $record), 403);

        $validated = $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'would_hang_again' => ['nullable', 'boolean'],
            'mate_ids' => ['array'],
            'mate_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if (isset($validated['rating'])) {
            EventReview::query()->updateOrCreate(
                ['event_id' => $record->id, 'user_id' => $request->user()->id],
                [
                    'rating' => $validated['rating'],
                    'would_hang_again' => (bool) ($validated['would_hang_again'] ?? true),
                ],
            );
        }

        foreach ($validated['mate_ids'] ?? [] as $mateId) {
            if ((int) $mateId === $request->user()->id) {
                continue;
            }

            Connection::query()->firstOrCreate([
                'user_id' => $request->user()->id,
                'mate_id' => $mateId,
            ], [
                'event_id' => $record->id,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Mates saved. Next event is easier.']);

        return to_route('mates.index');
    }

    private function findEvent(string $community, string $event): Event
    {
        $communityModel = Community::query()->where('slug', $community)->firstOrFail();

        return $communityModel->events()->with('community')->where('slug', $event)->firstOrFail();
    }

    private function attended(Request $request, Event $event): bool
    {
        $rsvp = $event->rsvps()->where('user_id', $request->user()->id)->first();

        return $rsvp?->isConfirmed() ?? false;
    }
}
