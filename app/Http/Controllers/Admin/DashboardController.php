<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Connection;
use App\Models\Event;
use App\Models\Rsvp;
use App\Support\EventBudget;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $community = $this->auckland();
        $events = $community->events()->with('community')->orderBy('starts_at')->get();
        $upcoming = $events->filter(fn (Event $event) => $event->starts_at->isFuture())->values();
        $past = $events->filter(fn (Event $event) => $event->starts_at->isPast())->values();

        $eventIds = $events->pluck('id');
        $confirmed = Rsvp::query()
            ->whereIn('event_id', $eventIds)
            ->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED])
            ->get();

        $totalCost = (int) $events->sum(fn (Event $event) => $event->totalCostCents());
        $revenue = (int) $confirmed->sum('amount_paid_cents');
        $fees = (int) $confirmed->sum('platform_fee_cents');
        $profit = ($revenue - $fees) - $totalCost;

        return Inertia::render('admin/Dashboard', [
            'community' => [
                'name' => $community->name,
                'member_count' => $community->members()->count(),
            ],
            'stripeConfigured' => filled(config('services.stripe.secret')),
            'stats' => [
                'members' => $community->members()->count(),
                'upcoming_nights' => $upcoming->count(),
                'signups' => $confirmed->count(),
                'waitlist' => Rsvp::query()->whereIn('event_id', $eventIds)->where('status', Rsvp::STATUS_WAITLISTED)->count(),
                'ticket_sales_label' => EventBudget::money($revenue),
                'cost_label' => EventBudget::money($totalCost),
                'profit_label' => EventBudget::money($profit),
                'is_profitable' => $profit >= 0,
                'connections' => Connection::query()->whereIn('event_id', $eventIds)->count(),
            ],
            'upcoming' => $upcoming->map(fn (Event $event) => $this->eventRow($event))->all(),
            'past' => $past->map(fn (Event $event) => $this->eventRow($event))->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventRow(Event $event): array
    {
        $budget = EventBudget::for($event);

        return [
            'id' => $event->id,
            'title' => $event->title,
            'emoji' => $event->emoji,
            'starts_at_label' => $event->starts_at->timezone('Pacific/Auckland')->format('D j M · g:ia'),
            'signups' => $event->signupCount(),
            'spots' => $event->takenSpots(),
            'capacity' => $event->capacity,
            'waitlist' => $event->waitlistCount(),
            'pending' => $event->pendingPaymentCount(),
            'revenue_cents' => $budget['revenue_cents'],
            'profit_label' => $budget['profit_label'],
            'is_profitable' => $budget['is_profitable'],
            'break_even_tickets' => $budget['break_even_tickets'],
            'price_label' => $event->formattedPrice(),
            'stripe_product_name' => $event->stripe_product_name,
            'stripe_price_id' => $event->stripe_price_id,
            'public_url' => route('events.show', [$event->community->slug, $event->slug]),
            'edit_url' => route('admin.events.edit', $event),
            'attendees_url' => route('admin.events.show', $event),
        ];
    }

    private function auckland(): Community
    {
        return Community::query()->where('slug', 'auckland-m8s')->firstOrFail();
    }
}
