<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Event;
use App\Models\EventMessage;
use App\Support\EventPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $events = Event::query()->with('community')->published()->upcoming()->get();

        return Inertia::render('events/Index', [
            'events' => EventPresenter::collection($events),
        ]);
    }

    public function show(string $community, string $event, Request $request): Response
    {
        $record = $this->findEvent($community, $event);

        return Inertia::render('events/Show', [
            'event' => EventPresenter::summary($record),
            'attendees' => EventPresenter::attendees($record),
            'rsvp' => EventPresenter::currentRsvp($request->user(), $record),
            'canChat' => $request->user() ? $this->canAccessEvent($request, $record) : false,
            'canMeet' => $request->user() && $this->canAccessEvent($request, $record) && $record->hasStarted(),
        ]);
    }

    public function chat(string $community, string $event, Request $request): Response
    {
        $record = $this->findEvent($community, $event);
        abort_unless($this->canAccessEvent($request, $record), 403);

        $messages = $record->messages()
            ->with('user')
            ->latest()
            ->limit(80)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (EventMessage $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'name' => $message->user->name,
                'mine' => $message->user_id === $request->user()->id,
                'created_at' => $message->created_at?->timezone('Pacific/Auckland')->format('g:ia'),
            ]);

        return Inertia::render('events/Chat', [
            'event' => EventPresenter::summary($record),
            'messages' => $messages,
        ]);
    }

    public function storeMessage(string $community, string $event, Request $request): RedirectResponse
    {
        $record = $this->findEvent($community, $event);
        abort_unless($this->canAccessEvent($request, $record), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $record->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back();
    }

    private function findEvent(string $community, string $event): Event
    {
        $communityModel = Community::query()->where('slug', $community)->firstOrFail();

        return $communityModel->events()
            ->with('community')
            ->where('slug', $event)
            ->firstOrFail();
    }

    private function canAccessEvent(Request $request, Event $event): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->organizes($event->community)) {
            return true;
        }

        $rsvp = $event->rsvps()->where('user_id', $user->id)->first();

        return $rsvp?->isConfirmed() ?? false;
    }
}
