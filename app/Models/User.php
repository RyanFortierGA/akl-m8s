<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int|null $age
 * @property string|null $suburb
 * @property string|null $instagram
 * @property string|null $phone
 * @property string|null $bio
 * @property string|null $contact_token
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property bool $is_admin
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'age', 'suburb', 'instagram', 'phone', 'bio', 'contact_token', 'is_admin'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->contact_token ??= Str::lower(Str::random(16));
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'age' => 'integer',
            'is_admin' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Interest, $this> */
    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class);
    }

    /** @return BelongsToMany<Community, $this> */
    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class)
            ->withPivot('role', 'joined_at');
    }

    /** @return HasMany<Community, $this> */
    public function ownedCommunities(): HasMany
    {
        return $this->hasMany(Community::class, 'owner_id');
    }

    /** @return HasMany<Event, $this> */
    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /** @return HasMany<Rsvp, $this> */
    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function mates(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'connections', 'user_id', 'mate_id')
            ->withPivot(['event_id', 'contact_shared_at'])
            ->withTimestamps();
    }

    public function attendedEventsCount(): int
    {
        return $this->rsvps()
            ->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED])
            ->whereHas('event', fn ($query) => $query->where('starts_at', '<', now()))
            ->count();
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function isOrganizer(): bool
    {
        return $this->ownedCommunities()->exists()
            || $this->communities()->wherePivot('role', 'organizer')->exists();
    }

    public function organizes(Community $community): bool
    {
        if ($community->owner_id === $this->id) {
            return true;
        }

        return $this->communities()
            ->where('communities.id', $community->id)
            ->wherePivot('role', 'organizer')
            ->exists();
    }

    public function instagramHandle(): ?string
    {
        if (! $this->instagram) {
            return null;
        }

        return ltrim(str_replace(['https://instagram.com/', 'https://www.instagram.com/', '@'], '', $this->instagram), '/');
    }

    /**
     * @return array<string, mixed>
     */
    public function publicCard(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'suburb' => $this->suburb,
            'instagram' => $this->instagramHandle(),
            'phone' => $this->phone,
            'bio' => $this->bio,
            'contact_token' => $this->contact_token,
            'events_attended' => $this->attendedEventsCount(),
        ];
    }
}
