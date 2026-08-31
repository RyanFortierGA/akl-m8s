<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Interest;
use App\Support\Auckland;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Onboarding', [
            'interests' => Interest::query()->orderBy('name')->get(['id', 'name', 'emoji']),
            'selected' => $request->user()->interests()->pluck('interests.id'),
            'profile' => [
                'age' => $request->user()->age,
                'suburb' => $request->user()->suburb,
                'instagram' => $request->user()->instagram,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'age' => ['nullable', 'integer', 'min:18', 'max:99'],
            'suburb' => Auckland::suburbRules(),
            'instagram' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'interest_ids' => ['array'],
            'interest_ids.*' => ['integer', 'exists:interests,id'],
        ]);

        $request->user()->forceFill([
            'age' => $validated['age'] ?? $request->user()->age,
            'suburb' => $validated['suburb'] ?? $request->user()->suburb,
            'instagram' => $validated['instagram'] ?? $request->user()->instagram,
            'phone' => $validated['phone'] ?? $request->user()->phone,
        ])->save();

        $request->user()->interests()->sync($validated['interest_ids'] ?? []);

        $default = Community::query()->where('slug', 'auckland-m8s')->first();
        $default?->addMember($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profile saved. Grab a spot.']);

        return to_route('home');
    }
}
