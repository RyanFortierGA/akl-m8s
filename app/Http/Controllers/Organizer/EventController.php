<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use App\Models\Rsvp;
use App\Support\EventPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function create(Request $request): Response
    {
        $communities = $this->organizerCommunities($request);

        abort_if($communities->isEmpty(), 403);

        return Inertia::render('organizer/events/Create', [
            'communities' => $communities->map(fn (Community $community) => [
                'id' => $community->id,
                'name' => $community->name,
            ])->values()->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'community_id' => ['required', 'exists:communities,id'],
            'title' => ['required', 'string', 'max:120'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:4000'],
            'starts_at' => ['required', 'date'],
            'venue_name' => ['required', 'string', 'max:160'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'suburb' => ['nullable', 'string', 'max:80'],
            'capacity' => ['required', 'integer', 'min:2', 'max:200'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $community = Community::query()->findOrFail($validated['community_id']);
        abort_unless($request->user()->organizes($community), 403);

        $event = $community->events()->create([
            'organizer_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(4)),
            'emoji' => $validated['emoji'] ?: '🗓️',
            'description' => $validated['description'] ?? '',
            'starts_at' => $validated['starts_at'],
            'venue_name' => $validated['venue_name'],
            'venue_address' => $validated['venue_address'] ?? null,
            'suburb' => $validated['suburb'] ?? $community->city,
            'capacity' => $validated['capacity'],
            'price_cents' => (int) round(((float) $validated['price']) * 100),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Event is live.']);

        return to_route('events.show', [$community->slug, $event->slug]);
    }

    public function attendees(string $community, string $event, Request $request): Response
    {
        $record = $this->findEvent($community, $event);
        abort_unless($request->user()->organizes($record->community), 403);

        $attendees = $record->rsvps()
            ->with('user')
            ->whereIn('status', [
                Rsvp::STATUS_CONFIRMED,
                Rsvp::STATUS_ATTENDED,
                Rsvp::STATUS_WAITLISTED,
                Rsvp::STATUS_NO_SHOW,
            ])
            ->get()
            ->map(fn (Rsvp $rsvp) => [
                'id' => $rsvp->id,
                'user_id' => $rsvp->user->id,
                'name' => $rsvp->user->name,
                'email' => $rsvp->user->email,
                'suburb' => $rsvp->user->suburb,
                'status' => $rsvp->status,
                'events_attended' => $rsvp->user->attendedEventsCount(),
                'coming_alone' => $rsvp->party_size === 1,
            ]);

        return Inertia::render('organizer/events/Attendees', [
            'event' => EventPresenter::summary($record),
            'attendees' => $attendees->values()->all(),
        ]);
    }

    public function markAttendance(string $community, string $event, Request $request): RedirectResponse
    {
        $record = $this->findEvent($community, $event);
        abort_unless($request->user()->organizes($record->community), 403);

        $validated = $request->validate([
            'rsvp_id' => ['required', 'exists:rsvps,id'],
            'status' => ['required', 'in:attended,no_show,confirmed'],
        ]);

        Rsvp::query()
            ->where('event_id', $record->id)
            ->whereKey($validated['rsvp_id'])
            ->update(['status' => $validated['status']]);

        return back();
    }

    private function organizerCommunities(Request $request)
    {
        return Community::query()
            ->where('owner_id', $request->user()->id)
            ->orWhereHas('members', fn ($query) => $query
                ->where('users.id', $request->user()->id)
                ->where('community_user.role', 'organizer'))
            ->get();
    }

    private function findEvent(string $community, string $event): Event
    {
        $communityModel = Community::query()->where('slug', $community)->firstOrFail();

        return $communityModel->events()->with('community')->where('slug', $event)->firstOrFail();
    }
}
