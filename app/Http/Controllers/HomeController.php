<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Support\EventPresenter;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $community = Community::query()->where('slug', 'auckland-m8s')->first();

        $events = $community
            ? $community->events()->with('community')->published()->upcoming()->get()
            : collect();

        return Inertia::render('Welcome', [
            'community' => $community ? EventPresenter::communityCard($community) : null,
            'events' => EventPresenter::collection($events),
        ]);
    }
}
