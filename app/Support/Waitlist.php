<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Waitlist
{
    /**
     * Real signups only. Excludes admin and seeded demo mates.
     *
     * @return Builder<User>
     */
    public static function query(): Builder
    {
        return User::query()
            ->where('is_admin', false)
            ->where('email', 'not like', '%@aklm8s.nz')
            ->orderByDesc('created_at');
    }

    public static function count(): int
    {
        return self::query()->count();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function entries(?string $search = null): array
    {
        return self::query()
            ->withCount('interests')
            ->when(filled($search), function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('suburb', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'suburb' => $user->suburb,
                'age' => $user->age,
                'instagram' => $user->instagramHandle(),
                'interests_count' => (int) $user->interests_count,
                'profile_complete' => filled($user->suburb),
                'signed_up_at' => $user->created_at?->timezone('Pacific/Auckland')->format('j M Y g:ia'),
                'signed_up_label' => $user->created_at
                    ? Carbon::parse($user->created_at)->timezone('Pacific/Auckland')->diffForHumans()
                    : null,
            ])
            ->all();
    }
}
