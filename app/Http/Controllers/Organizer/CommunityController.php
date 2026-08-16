<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('organizer/communities/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $community = Community::query()->create([
            'owner_id' => $request->user()->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(4)),
            'city' => $validated['city'],
            'tagline' => $validated['tagline'] ?: 'Meet people. Do stuff. Make mates.',
            'description' => $validated['description'] ?? '',
            'platform_fee_percent' => 10,
        ]);

        $community->addMember($request->user(), 'organizer');

        Inertia::flash('toast', ['type' => 'success', 'message' => $community->name.' is live. Create your first event.']);

        return to_route('organizer.events.create');
    }
}
