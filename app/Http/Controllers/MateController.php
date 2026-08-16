<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MateController extends Controller
{
    public function index(Request $request): Response
    {
        $mates = Connection::query()
            ->with(['mate.interests', 'event'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function (Connection $connection) use ($request) {
                $mutualEvents = Connection::query()
                    ->where('user_id', $request->user()->id)
                    ->where('mate_id', $connection->mate_id)
                    ->whereNotNull('event_id')
                    ->count();

                return [
                    'id' => $connection->mate->id,
                    'name' => $connection->mate->name,
                    'suburb' => $connection->mate->suburb,
                    'bio' => $connection->mate->bio,
                    'instagram' => $connection->contact_shared_at ? $connection->mate->instagramHandle() : null,
                    'phone' => $connection->contact_shared_at ? $connection->mate->phone : null,
                    'contact_shared' => (bool) $connection->contact_shared_at,
                    'contact_token' => $connection->mate->contact_token,
                    'card_url' => route('cards.show', $connection->mate->contact_token),
                    'share_url' => route('mates.share', $connection->mate->id),
                    'met_at' => $connection->event?->title,
                    'met_at_emoji' => $connection->event?->emoji,
                    'met_on' => $connection->event?->starts_at?->timezone('Pacific/Auckland')->format('j M Y'),
                    'mutual_events' => max(1, $mutualEvents),
                    'interests' => $connection->mate->interests->map(fn ($interest) => [
                        'name' => $interest->name,
                        'emoji' => $interest->emoji,
                    ])->values()->all(),
                ];
            });

        return Inertia::render('mates/Index', [
            'mates' => $mates->values()->all(),
            'myCardUrl' => route('cards.show', $request->user()->contact_token),
        ]);
    }

    public function share(Request $request, int $mate): RedirectResponse
    {
        $connection = Connection::query()
            ->where('user_id', $request->user()->id)
            ->where('mate_id', $mate)
            ->firstOrFail();

        $connection->forceFill(['contact_shared_at' => now()])->save();

        Connection::query()->firstOrCreate([
            'user_id' => $mate,
            'mate_id' => $request->user()->id,
        ], [
            'event_id' => $connection->event_id,
            'contact_shared_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Contact shared.']);

        return back();
    }
}
