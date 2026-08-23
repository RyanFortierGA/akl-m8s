<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'community_id',
        'organizer_id',
        'title',
        'slug',
        'emoji',
        'description',
        'starts_at',
        'ends_at',
        'venue_name',
        'venue_address',
        'suburb',
        'capacity',
        'price_cents',
        'venue_cost_cents',
        'host_cost_cents',
        'other_cost_cents',
        'cost_notes',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_product_name',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            $event->slug ??= Str::slug($event->title).'-'.Str::lower(Str::random(5));
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'price_cents' => 'integer',
            'venue_cost_cents' => 'integer',
            'host_cost_cents' => 'integer',
            'other_cost_cents' => 'integer',
        ];
    }

    public function totalCostCents(): int
    {
        return (int) $this->venue_cost_cents
            + (int) $this->host_cost_cents
            + (int) $this->other_cost_cents;
    }

    /** @return BelongsTo<Community, $this> */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /** @return BelongsTo<User, $this> */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /** @return HasMany<Rsvp, $this> */
    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    /** @return HasMany<EventMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(EventMessage::class);
    }

    /** @return HasMany<EventReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(EventReview::class);
    }

    /** @param  Builder<Event>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', self::STATUS_PUBLISHED);
    }

    /** @param  Builder<Event>  $query */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    /** @param  Builder<Event>  $query */
    public function scopePast(Builder $query): void
    {
        $query->where('starts_at', '<', now())->orderByDesc('starts_at');
    }

    /** @return HasMany<Rsvp, $this> */
    public function confirmedRsvps(): HasMany
    {
        return $this->rsvps()->whereIn('status', [Rsvp::STATUS_CONFIRMED, Rsvp::STATUS_ATTENDED]);
    }

    /** @return HasMany<Rsvp, $this> */
    public function waitlistedRsvps(): HasMany
    {
        return $this->rsvps()->where('status', Rsvp::STATUS_WAITLISTED);
    }

    public function signupCount(): int
    {
        return $this->confirmedRsvps()->count();
    }

    public function waitlistCount(): int
    {
        return $this->waitlistedRsvps()->count();
    }

    public function pendingPaymentCount(): int
    {
        return $this->rsvps()->where('status', Rsvp::STATUS_PENDING)->count();
    }

    public function hasStripePrice(): bool
    {
        return filled($this->stripe_price_id);
    }

    public function takenSpots(): int
    {
        return (int) $this->confirmedRsvps()->sum('party_size');
    }

    public function spotsRemaining(): int
    {
        return max(0, $this->capacity - $this->takenSpots());
    }

    public function isFull(): bool
    {
        return $this->spotsRemaining() <= 0;
    }

    public function isFree(): bool
    {
        return $this->price_cents === 0;
    }

    public function hasEnded(): bool
    {
        $end = $this->ends_at ?? $this->starts_at->addHours(3);

        return $end->isPast();
    }

    public function hasStarted(): bool
    {
        return $this->starts_at->isPast();
    }

    public function formattedPrice(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return '$'.number_format($this->price_cents / 100, 0);
    }

    /**
     * @return array<string, mixed>
     */
    public function socialProof(): array
    {
        $confirmed = $this->confirmedRsvps()->with('user')->get();
        $comingAlone = $confirmed->where('party_size', 1)->count();
        $comingWithFriend = $confirmed->where('party_size', '>', 1)->count();
        $newbies = $confirmed->filter(fn (Rsvp $rsvp) => $rsvp->user->attendedEventsCount() <= 3)->count();

        return [
            'going' => $confirmed->count(),
            'spots_remaining' => $this->spotsRemaining(),
            'coming_alone' => $comingAlone,
            'coming_with_friend' => $comingWithFriend,
            'newcomers' => $newbies,
        ];
    }
}
