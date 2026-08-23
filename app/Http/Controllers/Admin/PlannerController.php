<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Event;
use App\Support\EventBudget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlannerController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $community = Community::query()->where('slug', 'auckland-m8s')->firstOrFail();

        $month = Carbon::parse(
            $request->string('month', now('Pacific/Auckland')->format('Y-m'))->toString().'-01',
            'Pacific/Auckland',
        )->startOfMonth();

        $rangeStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $rangeEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $events = $community->events()
            ->with('community')
            ->whereBetween('starts_at', [$rangeStart, $rangeEnd])
            ->orderBy('starts_at')
            ->get();

        $days = [];
        $cursor = $rangeStart->copy();

        while ($cursor <= $rangeEnd) {
            $key = $cursor->toDateString();
            $dayEvents = $events
                ->filter(fn (Event $event) => $event->starts_at->timezone('Pacific/Auckland')->toDateString() === $key)
                ->values()
                ->map(function (Event $event) {
                    $budget = EventBudget::for($event);

                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'emoji' => $event->emoji,
                        'time_label' => $event->starts_at->timezone('Pacific/Auckland')->format('g:ia'),
                        'suburb' => $event->suburb,
                        'signups' => $event->signupCount(),
                        'capacity' => $event->capacity,
                        'waitlist' => $event->waitlistCount(),
                        'price_label' => $event->formattedPrice(),
                        'profit_label' => $budget['profit_label'],
                        'is_profitable' => $budget['is_profitable'],
                        'break_even_tickets' => $budget['break_even_tickets'],
                        'url' => route('admin.events.show', $event),
                        'edit_url' => route('admin.events.edit', $event),
                    ];
                })
                ->all();

            $days[] = [
                'date' => $key,
                'day' => $cursor->day,
                'in_month' => $cursor->month === $month->month,
                'is_today' => $cursor->isToday(),
                'events' => $dayEvents,
            ];

            $cursor->addDay();
        }

        $monthEvents = $events->filter(
            fn (Event $event) => $event->starts_at->timezone('Pacific/Auckland')->between(
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth()->endOfDay(),
            )
        );

        $monthProfit = 0;
        $monthRevenue = 0;
        $monthCost = 0;
        $monthSignups = 0;

        foreach ($monthEvents as $event) {
            $budget = EventBudget::for($event);
            $monthProfit += $budget['profit_cents'];
            $monthRevenue += $budget['revenue_cents'];
            $monthCost += $budget['total_cost_cents'];
            $monthSignups += $budget['signups'];
        }

        return Inertia::render('admin/Planner', [
            'month' => $month->format('Y-m'),
            'month_label' => $month->format('F Y'),
            'prev_month' => $month->copy()->subMonth()->format('Y-m'),
            'next_month' => $month->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'stats' => [
                'nights' => $monthEvents->count(),
                'signups' => $monthSignups,
                'revenue_label' => EventBudget::money($monthRevenue),
                'cost_label' => EventBudget::money($monthCost),
                'profit_label' => EventBudget::money($monthProfit),
                'is_profitable' => $monthProfit >= 0,
            ],
        ]);
    }
}
