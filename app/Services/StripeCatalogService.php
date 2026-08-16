<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\StripeClient;
use Throwable;

class StripeCatalogService
{
    public function configured(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    /**
     * @return list<array{price_id: string, product_id: string, product_name: string, amount_cents: int, amount_label: string, currency: string}>
     */
    public function prices(bool $refresh = false): array
    {
        if (! $this->configured()) {
            return [];
        }

        if ($refresh) {
            Cache::forget('stripe.catalog.prices');
        }

        try {
            return Cache::remember('stripe.catalog.prices', 300, fn () => $this->fetchPrices());
        } catch (Throwable $exception) {
            Log::warning('Stripe catalog failed', ['message' => $exception->getMessage()]);

            return [];
        }
    }

    /**
     * @return array{price_id: string, product_id: string, product_name: string, amount_cents: int, amount_label: string, currency: string}|null
     */
    public function findPrice(string $priceId): ?array
    {
        foreach ($this->prices() as $price) {
            if ($price['price_id'] === $priceId) {
                return $price;
            }
        }

        if (! $this->configured()) {
            return null;
        }

        try {
            return $this->mapPrice(
                (new StripeClient((string) config('services.stripe.secret')))->prices->retrieve($priceId, [
                    'expand' => ['product'],
                ]),
            );
        } catch (ApiErrorException) {
            return null;
        }
    }

    public function attachToEvent(Event $event, ?string $priceId): void
    {
        if (! filled($priceId)) {
            $event->forceFill([
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'stripe_product_name' => null,
            ]);

            return;
        }

        $price = $this->findPrice($priceId);

        if (! $price) {
            $event->forceFill([
                'stripe_price_id' => $priceId,
            ]);

            return;
        }

        $event->forceFill([
            'stripe_price_id' => $price['price_id'],
            'stripe_product_id' => $price['product_id'],
            'stripe_product_name' => $price['product_name'],
            'price_cents' => $price['amount_cents'],
        ]);
    }

    /**
     * @return list<array{price_id: string, product_id: string, product_name: string, amount_cents: int, amount_label: string, currency: string}>
     */
    private function fetchPrices(): array
    {
        $stripe = new StripeClient((string) config('services.stripe.secret'));
        $prices = $stripe->prices->all([
            'active' => true,
            'limit' => 100,
            'expand' => ['data.product'],
        ]);

        $mapped = [];

        foreach ($prices->data as $price) {
            if ($price->type !== 'one_time' || $price->unit_amount === null) {
                continue;
            }

            $item = $this->mapPrice($price);

            if ($item) {
                $mapped[] = $item;
            }
        }

        usort($mapped, fn (array $left, array $right) => strcasecmp($left['product_name'], $right['product_name']));

        return $mapped;
    }

    /**
     * @return array{price_id: string, product_id: string, product_name: string, amount_cents: int, amount_label: string, currency: string}|null
     */
    private function mapPrice(Price $price): ?array
    {
        if ($price->unit_amount === null) {
            return null;
        }

        $product = $price->product;
        $productId = is_string($product) ? $product : (string) $product->id;
        $productName = is_string($product) ? $productId : (string) ($product->name ?? $productId);

        return [
            'price_id' => $price->id,
            'product_id' => $productId,
            'product_name' => $productName,
            'amount_cents' => (int) $price->unit_amount,
            'amount_label' => '$'.number_format(((int) $price->unit_amount) / 100, 0),
            'currency' => strtoupper((string) $price->currency),
        ];
    }
}
