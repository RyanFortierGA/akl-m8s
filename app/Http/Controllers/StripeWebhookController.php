<?php

namespace App\Http\Controllers;

use App\Services\RsvpService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, RsvpService $rsvps): Response
    {
        $secret = (string) config('services.stripe.webhook_secret');

        try {
            $event = $secret
                ? Webhook::constructEvent($request->getContent(), (string) $request->header('Stripe-Signature'), $secret)
                : \Stripe\Event::constructFrom($request->all());
        } catch (Throwable) {
            return response('Invalid payload', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $rsvps->confirmFromStripe((string) $session->id, isset($session->payment_intent) ? (string) $session->payment_intent : null);
        }

        return response('ok');
    }
}
