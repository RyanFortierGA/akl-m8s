<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Rsvp;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    public function configured(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    public function checkoutUrl(Event $event, Rsvp $rsvp): string
    {
        $event->loadMissing('community');
        $rsvp->loadMissing('user');

        if (! $this->configured()) {
            $rsvp->forceFill([
                'stripe_checkout_session_id' => 'local_'.$rsvp->id,
            ])->save();

            return route('events.checkout.local', [$event->community->slug, $event->slug]);
        }

        $stripe = new StripeClient((string) config('services.stripe.secret'));

        $lineItem = $event->stripe_price_id
            ? [
                'quantity' => 1,
                'price' => $event->stripe_price_id,
            ]
            : [
                'quantity' => 1,
                'price_data' => [
                    'currency' => (string) config('services.stripe.currency', 'nzd'),
                    'unit_amount' => $event->price_cents,
                    'product_data' => [
                        'name' => trim($event->emoji.' '.$event->title),
                        'description' => $event->community->name.' · '.$event->starts_at->timezone('Pacific/Auckland')->format('D j M, g:ia'),
                    ],
                ],
            ];

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => route('events.checkout.success', [$event->community->slug, $event->slug]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('events.show', [$event->community->slug, $event->slug]),
            'customer_email' => $rsvp->user->email,
            'metadata' => [
                'rsvp_id' => (string) $rsvp->id,
                'event_id' => (string) $event->id,
                'user_id' => (string) $rsvp->user_id,
            ],
            'line_items' => [$lineItem],
        ]);

        $rsvp->forceFill([
            'stripe_checkout_session_id' => $session->id,
        ])->save();

        return (string) $session->url;
    }

    public function retrieveSession(string $sessionId): Session
    {
        $stripe = new StripeClient((string) config('services.stripe.secret'));

        return $stripe->checkout->sessions->retrieve($sessionId);
    }
}
