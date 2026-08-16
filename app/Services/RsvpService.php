<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RsvpService
{
    public function __construct(private StripeCheckoutService $stripe) {}

    /**
     * @return array{rsvp: Rsvp, checkout_url: ?string, waitlisted: bool}
     */
    public function reserve(Event $event, User $user, int $partySize = 1): array
    {
        return DB::transaction(function () use ($event, $user, $partySize) {
            /** @var Event $event */
            $event = Event::query()->with('community')->lockForUpdate()->findOrFail($event->id);

            $existing = Rsvp::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing && $existing->isConfirmed()) {
                return ['rsvp' => $existing, 'checkout_url' => null, 'waitlisted' => false];
            }

            if ($existing && $existing->status === Rsvp::STATUS_WAITLISTED) {
                return ['rsvp' => $existing, 'checkout_url' => null, 'waitlisted' => true];
            }

            $event->community->addMember($user);

            if ($event->isFull()) {
                $rsvp = Rsvp::query()->updateOrCreate(
                    ['event_id' => $event->id, 'user_id' => $user->id],
                    [
                        'status' => Rsvp::STATUS_WAITLISTED,
                        'party_size' => $partySize,
                    ],
                );

                return ['rsvp' => $rsvp, 'checkout_url' => null, 'waitlisted' => true];
            }

            $fee = (int) round($event->price_cents * ($event->community->platform_fee_percent / 100));

            $rsvp = Rsvp::query()->updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $user->id],
                [
                    'status' => $event->isFree() ? Rsvp::STATUS_CONFIRMED : Rsvp::STATUS_PENDING,
                    'party_size' => $partySize,
                    'platform_fee_cents' => $fee,
                    'amount_paid_cents' => $event->isFree() ? 0 : $event->price_cents,
                    'paid_at' => $event->isFree() ? now() : null,
                ],
            );

            if ($event->isFree()) {
                return ['rsvp' => $rsvp, 'checkout_url' => null, 'waitlisted' => false];
            }

            $checkoutUrl = $this->stripe->checkoutUrl($event, $rsvp);

            return ['rsvp' => $rsvp, 'checkout_url' => $checkoutUrl, 'waitlisted' => false];
        });
    }

    public function confirmFromStripe(string $sessionId, ?string $paymentIntentId = null): Rsvp
    {
        $rsvp = Rsvp::query()->where('stripe_checkout_session_id', $sessionId)->first()
            ?? throw new RuntimeException('RSVP not found for checkout session.');

        $rsvp->forceFill([
            'status' => Rsvp::STATUS_CONFIRMED,
            'paid_at' => now(),
            'stripe_payment_intent_id' => $paymentIntentId,
        ])->save();

        return $rsvp;
    }

    public function markPaidLocally(Rsvp $rsvp): Rsvp
    {
        $rsvp->forceFill([
            'status' => Rsvp::STATUS_CONFIRMED,
            'paid_at' => now(),
            'amount_paid_cents' => $rsvp->event->price_cents,
        ])->save();

        return $rsvp;
    }
}
