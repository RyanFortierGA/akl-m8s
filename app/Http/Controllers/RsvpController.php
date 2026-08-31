<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Event;
use App\Services\RsvpService;
use App\Support\Auckland;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RsvpController extends Controller
{
    public function store(string $community, string $event, Request $request, RsvpService $rsvps): RedirectResponse
    {
        $this->guardPrelaunch($request);

        $record = $this->findEvent($community, $event);

        $validated = $request->validate([
            'party_size' => ['nullable', 'integer', 'min:1', 'max:2'],
        ]);

        $result = $rsvps->reserve($record, $request->user(), (int) ($validated['party_size'] ?? 1));

        if ($result['waitlisted']) {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'Event is full. You are on the waitlist.']);

            return back();
        }

        if ($result['checkout_url']) {
            return redirect()->away($result['checkout_url']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Spot reserved. See you there.']);

        return back();
    }

    public function localCheckout(string $community, string $event, Request $request): Response
    {
        $this->guardPrelaunch($request);

        $record = $this->findEvent($community, $event);

        return Inertia::render('events/LocalCheckout', [
            'event' => [
                'title' => $record->title,
                'emoji' => $record->emoji,
                'price_label' => $record->formattedPrice(),
                'community_slug' => $record->community->slug,
                'slug' => $record->slug,
            ],
        ]);
    }

    public function confirmLocal(string $community, string $event, Request $request, RsvpService $rsvps): RedirectResponse
    {
        $this->guardPrelaunch($request);

        $record = $this->findEvent($community, $event);
        $rsvp = $record->rsvps()->where('user_id', $request->user()->id)->firstOrFail();
        $rsvps->markPaidLocally($rsvp);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Spot confirmed. Stripe will take over once keys are added.']);

        return to_route('events.show', [$community, $event]);
    }

    public function success(string $community, string $event, Request $request, RsvpService $rsvps): RedirectResponse
    {
        $this->guardPrelaunch($request);

        $sessionId = $request->string('session_id')->toString();

        if ($sessionId) {
            $rsvps->confirmFromStripe($sessionId);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'You are in. Most people come solo, mates welcome too.']);

        return to_route('events.show', [$community, $event]);
    }

    private function guardPrelaunch(Request $request): void
    {
        if (! Auckland::prelaunch()) {
            return;
        }

        if ($request->user()?->isAdmin()) {
            return;
        }

        abort(404);
    }

    private function findEvent(string $community, string $event): Event
    {
        $communityModel = Community::query()->where('slug', $community)->firstOrFail();

        return $communityModel->events()->with('community')->where('slug', $event)->firstOrFail();
    }
}
