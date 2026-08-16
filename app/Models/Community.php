<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $members_count
 */
class Community extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'city',
        'tagline',
        'description',
        'primary_color',
        'accent_color',
        'platform_fee_percent',
        'is_public',
    ];

    protected static function booted(): void
    {
        static::creating(function (Community $community): void {
            $community->slug ??= Str::slug($community->name);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'platform_fee_percent' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'joined_at');
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function addMember(User $user, string $role = 'member'): void
    {
        if ($this->members()->where('users.id', $user->id)->exists()) {
            return;
        }

        $this->members()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }
}
