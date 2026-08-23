<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Rsvp;

class EventBudget
{
    /**
     * @return array{
     *     venue_cost_cents: int,
     *     host_cost_cents: int,
     *     other_cost_cents: int,
     *     total_cost_cents: int,
     *     cost_notes: ?string,
     *     revenue_cents: int,
     *     fees_cents: int,
     *     net_revenue_cents: int,
     *     profit_cents: int,
     *     ticket_price_cents: int,
     *     capacity: int,
     *     signups: int,
     *     break_even_tickets: int|null,
     *     break_even_price_cents: int|null,
     *     projected_full_revenue_cents: int,
     *     projected_full_profit_cents: int,
     *     fee_percent: int,
     *     is_profitable: bool,
     *     profit_label: string,
     *     revenue_label: string,
     *     cost_label: string,
     *     break_even_tickets_label: string,
     *     break_even_price_label: string,
     * }
     */
    public static function for(Event $event, ?int $feePercent = null): array
    {
        $event->loadMissing('community');

        $feePercent ??= (int) ($event->community->platform_fee_percent ?? 10);
        $venue = (int) $event->venue_cost_cents;
        $host = (int) $event->host_cost_cents;
        $other = (int) $event->other_cost_cents;
        $totalCost = $venue + $host + $other;

        $paid = $event->rsvps()
            ->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED])
            ->get(['amount_paid_cents', 'platform_fee_cents']);

        $revenue = (int) $paid->sum('amount_paid_cents');
        $fees = (int) $paid->sum('platform_fee_cents');
        $net = $revenue - $fees;
        $profit = $net - $totalCost;

        $ticket = (int) $event->price_cents;
        $capacity = max(1, (int) $event->capacity);
        $netPerTicket = $ticket > 0
            ? (int) round($ticket * (1 - ($feePercent / 100)))
            : 0;

        $breakEvenTickets = null;
        if ($netPerTicket > 0 && $totalCost > 0) {
            $breakEvenTickets = (int) ceil($totalCost / $netPerTicket);
        } elseif ($totalCost === 0) {
            $breakEvenTickets = 0;
        }

        $breakEvenPrice = null;
        if ($totalCost > 0 && $feePercent < 100) {
            $neededNet = $totalCost / $capacity;
            $breakEvenPrice = (int) (ceil($neededNet / (1 - ($feePercent / 100))) * 100);
        } elseif ($totalCost === 0) {
            $breakEvenPrice = 0;
        }

        $projectedFullRevenue = $ticket * $capacity;
        $projectedFullFees = (int) round($projectedFullRevenue * ($feePercent / 100));
        $projectedFullProfit = ($projectedFullRevenue - $projectedFullFees) - $totalCost;

        return [
            'venue_cost_cents' => $venue,
            'host_cost_cents' => $host,
            'other_cost_cents' => $other,
            'total_cost_cents' => $totalCost,
            'cost_notes' => $event->cost_notes,
            'revenue_cents' => $revenue,
            'fees_cents' => $fees,
            'net_revenue_cents' => $net,
            'profit_cents' => $profit,
            'ticket_price_cents' => $ticket,
            'capacity' => $capacity,
            'signups' => $paid->count(),
            'break_even_tickets' => $breakEvenTickets,
            'break_even_price_cents' => $breakEvenPrice,
            'projected_full_revenue_cents' => $projectedFullRevenue,
            'projected_full_profit_cents' => $projectedFullProfit,
            'fee_percent' => $feePercent,
            'is_profitable' => $profit >= 0,
            'profit_label' => self::money($profit),
            'revenue_label' => self::money($revenue),
            'cost_label' => self::money($totalCost),
            'break_even_tickets_label' => $breakEvenTickets === null
                ? 'n/a'
                : (string) $breakEvenTickets,
            'break_even_price_label' => $breakEvenPrice === null
                ? 'n/a'
                : self::money($breakEvenPrice),
        ];
    }

    /**
     * Live calculator from form inputs (no DB).
     *
     * @return array{break_even_tickets: int|null, break_even_price_cents: int|null, projected_full_profit_cents: int, total_cost_cents: int}
     */
    public static function project(
        int $ticketCents,
        int $capacity,
        int $venueCostCents,
        int $hostCostCents,
        int $otherCostCents,
        int $feePercent = 10,
    ): array {
        $totalCost = $venueCostCents + $hostCostCents + $otherCostCents;
        $capacity = max(1, $capacity);
        $netPerTicket = $ticketCents > 0
            ? (int) round($ticketCents * (1 - ($feePercent / 100)))
            : 0;

        $breakEvenTickets = null;
        if ($netPerTicket > 0 && $totalCost > 0) {
            $breakEvenTickets = (int) ceil($totalCost / $netPerTicket);
        } elseif ($totalCost === 0) {
            $breakEvenTickets = 0;
        }

        $breakEvenPrice = null;
        if ($totalCost > 0 && $feePercent < 100) {
            $neededNet = $totalCost / $capacity;
            $breakEvenPrice = (int) (ceil($neededNet / (1 - ($feePercent / 100))) * 100);
        } elseif ($totalCost === 0) {
            $breakEvenPrice = 0;
        }

        $projectedRevenue = $ticketCents * $capacity;
        $projectedFees = (int) round($projectedRevenue * ($feePercent / 100));

        return [
            'break_even_tickets' => $breakEvenTickets,
            'break_even_price_cents' => $breakEvenPrice,
            'projected_full_profit_cents' => ($projectedRevenue - $projectedFees) - $totalCost,
            'total_cost_cents' => $totalCost,
        ];
    }

    public static function money(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';

        return $sign.'$'.number_format(abs($cents) / 100, 0);
    }
}
