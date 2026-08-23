<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PeopleController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $community = Community::query()->where('slug', 'auckland-m8s')->firstOrFail();
        $search = trim($request->string('q')->toString());

        $members = $community->members()
            ->withCount([
                'rsvps as nights_confirmed' => fn ($query) => $query->whereIn('status', [
                    Rsvp::STATUS_CONFIRMED,
                    Rsvp::STATUS_ATTENDED,
                ]),
                'rsvps as nights_attended' => fn ($query) => $query->where('status', Rsvp::STATUS_ATTENDED),
                'rsvps as nights_waitlisted' => fn ($query) => $query->where('status', Rsvp::STATUS_WAITLISTED),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('suburb', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('nights_confirmed')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                $last = $user->rsvps()
                    ->with('event')
                    ->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED, Rsvp::STATUS_WAITLISTED])
                    ->latest()
                    ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'suburb' => $user->suburb,
                    'age' => $user->age,
                    'instagram' => $user->instagramHandle(),
                    'nights_confirmed' => (int) $user->nights_confirmed,
                    'nights_attended' => (int) $user->nights_attended,
                    'nights_waitlisted' => (int) $user->nights_waitlisted,
                    'role' => $user->pivot->role ?? 'member',
                    'joined_at' => $user->pivot->joined_at
                        ? Carbon::parse($user->pivot->joined_at)->timezone('Pacific/Auckland')->format('j M Y')
                        : null,
                    'last_night' => $last?->event?->title,
                    'last_night_emoji' => $last?->event?->emoji,
                ];
            });

        $suburbs = $members
            ->groupBy(fn (array $person) => $person['suburb'] ?: 'Unknown')
            ->map(fn ($group, $suburb) => [
                'suburb' => $suburb,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        return Inertia::render('admin/People', [
            'q' => $search,
            'stats' => [
                'members' => $members->count(),
                'repeat' => $members->where('nights_confirmed', '>', 1)->count(),
                'never_attended' => $members->where('nights_confirmed', 0)->count(),
                'suburbs' => count($suburbs),
            ],
            'people' => $members->values()->all(),
            'suburbs' => $suburbs,
        ]);
    }
}
