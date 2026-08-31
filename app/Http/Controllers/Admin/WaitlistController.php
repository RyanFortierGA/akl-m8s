<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Auckland;
use App\Support\Waitlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WaitlistController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $entries = collect(Waitlist::entries($search));
        $monthStart = now('Pacific/Auckland')->startOfMonth();

        $suburbs = $entries
            ->groupBy(fn (array $person) => $person['suburb'] ?: 'Not set')
            ->map(fn ($group, $suburb) => [
                'suburb' => $suburb,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        return Inertia::render('admin/Waitlist', [
            'q' => $search,
            'prelaunch' => Auckland::prelaunch(),
            'launchLabel' => Auckland::launchLabel(),
            'stats' => [
                'total' => Waitlist::count(),
                'profile_complete' => $entries->where('profile_complete', true)->count(),
                'this_month' => Waitlist::query()->where('created_at', '>=', $monthStart)->count(),
                'suburbs' => count($suburbs),
            ],
            'entries' => $entries->values()->all(),
            'suburbs' => $suburbs,
        ]);
    }
}
