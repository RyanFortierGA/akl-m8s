<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Connection;
use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $communities = Community::query()
            ->where('owner_id', $request->user()->id)
            ->orWhereHas('members', fn ($query) => $query
                ->where('users.id', $request->user()->id)
                ->where('community_user.role', 'organizer'))
            ->withCount('members')
            ->get();

        abort_if($communities->isEmpty(), 403);

        $communityIds = $communities->pluck('id');

        $events = Event::query()
            ->with('community')
            ->whereIn('community_id', $communityIds)
            ->orderBy('starts_at')
            ->get();

        $upcoming = $events->filter(fn (Event $event) => $event->starts_at->isFuture());
        $rsvps = Rsvp::query()
            ->whereIn('event_id', $events->pluck('id'))
            ->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED])
            ->get();

        $ticketSales = $rsvps->sum('amount_paid_cents');
        $connections = Connection::query()->whereIn('event_id', $events->pluck('id'))->count();

        return Inertia::render('organizer/Dashboard', [
            'communities' => $communities->map(fn (Community $community) => [
                'id' => $community->id,
                'name' => $community->name,
                'slug' => $community->slug,
                'city' => $community->city,
                'member_count' => $community->members_count,
            ])->values()->all(),
            'stats' => [
                'members' => $communities->sum('members_count'),
                'ticket_sales_cents' => $ticketSales,
                'ticket_sales_label' => '$'.number_format($ticketSales / 100, 0),
                'connections' => $connections,
                'active_this_month' => $rsvps->unique('user_id')->count(),
            ],
            'upcoming' => $upcoming->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'emoji' => $event->emoji,
                'starts_at_label' => $event->starts_at->timezone('Pacific/Auckland')->format('D j M · g:ia'),
                'going' => $event->confirmedRsvps()->count(),
                'capacity' => $event->capacity,
                'revenue_cents' => (int) $event->confirmedRsvps()->sum('amount_paid_cents'),
                'community_slug' => $event->community->slug,
                'slug' => $event->slug,
                'attendees_url' => route('organizer.events.attendees', [$event->community->slug, $event->slug]),
            ])->values()->all(),
        ]);
    }
}
