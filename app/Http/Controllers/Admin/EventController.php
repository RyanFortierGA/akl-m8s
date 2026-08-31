<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use App\Services\StripeCatalogService;
use App\Support\Auckland;
use App\Support\EventBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(private StripeCatalogService $stripe) {}

    public function create(Request $request): Response
    {
        return Inertia::render('admin/events/Form', [
            'event' => null,
            'stripeConfigured' => $this->stripe->configured(),
            'stripePrices' => $this->stripe->prices($request->boolean('refresh')),
            'feePercent' => (int) $this->auckland()->platform_fee_percent,
            'seriesOptions' => collect(Auckland::seriesCatalog())
                ->map(fn (array $item, string $key) => ['key' => $key, ...$item])
                ->values()
                ->all(),
            'suburbs' => Auckland::suburbs(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        $event = new Event;
        $this->fillEvent($event, $request);
        $event->community_id = $this->auckland()->id;
        $event->organizer_id = $user->id;
        $event->slug = Str::slug((string) $event->title).'-'.Str::lower(Str::random(4));
        $event->status = Event::STATUS_PUBLISHED;
        $event->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Event is live.']);

        return to_route('admin.events.show', $event);
    }

    public function edit(Request $request, Event $event): Response
    {
        return Inertia::render('admin/events/Form', [
            'event' => $this->formEvent($event),
            'stripeConfigured' => $this->stripe->configured(),
            'stripePrices' => $this->stripe->prices($request->boolean('refresh')),
            'feePercent' => (int) $this->auckland()->platform_fee_percent,
            'seriesOptions' => collect(Auckland::seriesCatalog())
                ->map(fn (array $item, string $key) => ['key' => $key, ...$item])
                ->values()
                ->all(),
            'suburbs' => Auckland::suburbs(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->fillEvent($event, $request);
        $event->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Event updated.']);

        return to_route('admin.events.show', $event);
    }

    public function show(Event $event): Response
    {
        $event->load('community');

        $people = $event->rsvps()
            ->with('user')
            ->latest()
            ->get()
            ->map(fn (Rsvp $rsvp) => [
                'id' => $rsvp->id,
                'user_id' => $rsvp->user->id,
                'name' => $rsvp->user->name,
                'email' => $rsvp->user->email,
                'suburb' => $rsvp->user->suburb,
                'status' => $rsvp->status,
                'party_size' => $rsvp->party_size,
                'coming_alone' => $rsvp->party_size === 1,
                'events_attended' => $rsvp->user->attendedEventsCount(),
            ]);

        return Inertia::render('admin/events/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'emoji' => $event->emoji,
                'starts_at_label' => $event->starts_at->timezone('Pacific/Auckland')->format('D j M · g:ia'),
                'venue_name' => $event->venue_name,
                'capacity' => $event->capacity,
                'price_label' => $event->formattedPrice(),
                'signups' => $event->signupCount(),
                'spots' => $event->takenSpots(),
                'waitlist' => $event->waitlistCount(),
                'pending' => $event->pendingPaymentCount(),
                'stripe_product_name' => $event->stripe_product_name,
                'stripe_price_id' => $event->stripe_price_id,
                'public_url' => route('events.show', [$event->community->slug, $event->slug]),
                'edit_url' => route('admin.events.edit', $event),
            ],
            'budget' => EventBudget::for($event),
            'signups' => $people->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED])->values()->all(),
            'waitlist' => $people->where('status', Rsvp::STATUS_WAITLISTED)->values()->all(),
            'pending' => $people->where('status', Rsvp::STATUS_PENDING)->values()->all(),
        ]);
    }

    public function markAttendance(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'rsvp_id' => ['required', Rule::exists('rsvps', 'id')->where('event_id', $event->id)],
            'status' => ['required', 'in:attended,no_show,confirmed,waitlisted'],
        ]);

        Rsvp::query()
            ->where('event_id', $event->id)
            ->whereKey($validated['rsvp_id'])
            ->update(['status' => $validated['status']]);

        return back();
    }

    private function fillEvent(Event $event, Request $request): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:4000'],
            'starts_at' => ['required', 'date'],
            'venue_name' => ['required', 'string', 'max:160'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'suburb' => Auckland::suburbRules(required: true),
            'series' => Auckland::seriesRules(),
            'capacity' => ['required', 'integer', 'min:2', 'max:200'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'venue_cost' => ['nullable', 'numeric', 'min:0'],
            'host_cost' => ['nullable', 'numeric', 'min:0'],
            'other_cost' => ['nullable', 'numeric', 'min:0'],
            'cost_notes' => ['nullable', 'string', 'max:1000'],
            'stripe_price_id' => ['nullable', 'string', 'max:80'],
        ]);

        $event->fill([
            'title' => $validated['title'],
            'emoji' => $validated['emoji'] ?: '🗓️',
            'description' => $validated['description'] ?? '',
            'starts_at' => $validated['starts_at'],
            'venue_name' => $validated['venue_name'],
            'venue_address' => $validated['venue_address'] ?? null,
            'suburb' => $validated['suburb'],
            'series' => $validated['series'] ?? null,
            'capacity' => $validated['capacity'],
            'price_cents' => (int) round(((float) ($validated['price'] ?? 0)) * 100),
            'venue_cost_cents' => (int) round(((float) ($validated['venue_cost'] ?? 0)) * 100),
            'host_cost_cents' => (int) round(((float) ($validated['host_cost'] ?? 0)) * 100),
            'other_cost_cents' => (int) round(((float) ($validated['other_cost'] ?? 0)) * 100),
            'cost_notes' => $validated['cost_notes'] ?? null,
        ]);

        $this->stripe->attachToEvent($event, $validated['stripe_price_id'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function formEvent(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'emoji' => $event->emoji,
            'description' => $event->description,
            'starts_at' => $event->starts_at->timezone('Pacific/Auckland')->format('Y-m-d\TH:i'),
            'venue_name' => $event->venue_name,
            'venue_address' => $event->venue_address,
            'suburb' => $event->suburb,
            'series' => $event->series,
            'capacity' => $event->capacity,
            'price' => $event->price_cents / 100,
            'venue_cost' => $event->venue_cost_cents / 100,
            'host_cost' => $event->host_cost_cents / 100,
            'other_cost' => $event->other_cost_cents / 100,
            'cost_notes' => $event->cost_notes,
            'stripe_price_id' => $event->stripe_price_id,
            'stripe_product_name' => $event->stripe_product_name,
        ];
    }

    private function auckland(): Community
    {
        return Community::query()->where('slug', 'auckland-m8s')->firstOrFail();
    }
}
