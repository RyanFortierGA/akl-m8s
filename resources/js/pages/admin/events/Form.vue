<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AdminNav from '@/components/AdminNav.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type StripePrice = {
    price_id: string;
    product_id: string;
    product_name: string;
    amount_cents: number;
    amount_label: string;
    currency: string;
};

type EventForm = {
    id: number;
    title: string;
    emoji: string;
    description: string;
    starts_at: string;
    venue_name: string;
    venue_address: string | null;
    suburb: string | null;
    capacity: number;
    price: number;
    venue_cost: number;
    host_cost: number;
    other_cost: number;
    cost_notes: string | null;
    stripe_price_id: string | null;
    stripe_product_name: string | null;
};

const props = defineProps<{
    event: EventForm | null;
    stripeConfigured: boolean;
    stripePrices: StripePrice[];
    feePercent: number;
}>();

const selectedPrice = ref(props.event?.stripe_price_id ?? '');
const price = ref(props.event?.price?.toString() ?? '15');
const capacity = ref(props.event?.capacity?.toString() ?? '20');
const venueCost = ref(props.event?.venue_cost?.toString() ?? '0');
const hostCost = ref(props.event?.host_cost?.toString() ?? '0');
const otherCost = ref(props.event?.other_cost?.toString() ?? '0');

const selected = computed(() =>
    props.stripePrices.find((item) => item.price_id === selectedPrice.value) ?? null,
);

watch(selectedPrice, (value) => {
    const match = props.stripePrices.find((item) => item.price_id === value);
    if (match) {
        price.value = String(match.amount_cents / 100);
    }
});

const budget = computed(() => {
    const ticket = Math.max(0, Number(price.value) || 0);
    const seats = Math.max(1, Number(capacity.value) || 1);
    const costs =
        (Number(venueCost.value) || 0) +
        (Number(hostCost.value) || 0) +
        (Number(otherCost.value) || 0);
    const fee = props.feePercent / 100;
    const netPer = ticket * (1 - fee);
    const breakEvenTickets =
        costs <= 0 ? 0 : netPer > 0 ? Math.ceil(costs / netPer) : null;
    const breakEvenPrice =
        costs <= 0 ? 0 : Math.ceil(costs / seats / (1 - fee));
    const fullProfit = ticket * seats * (1 - fee) - costs;

    return {
        costs,
        breakEvenTickets,
        breakEvenPrice,
        fullProfit,
        covers: breakEvenTickets !== null && breakEvenTickets <= seats,
    };
});

const editing = computed(() => Boolean(props.event));
const action = computed(() =>
    editing.value ? `/admin/events/${props.event?.id}` : '/admin/events',
);

function money(n: number) {
    const sign = n < 0 ? '-' : '';
    return `${sign}$${Math.abs(Math.round(n))}`;
}
</script>

<template>
    <Head :title="editing ? 'Edit event' : 'New event'" />
    <div class="mx-auto max-w-xl space-y-6 p-6">
        <AdminNav active="dashboard" />
        <div>
            <Link href="/admin" class="text-sm text-primary">Back to admin</Link>
            <h1 class="mt-3 text-3xl font-black">{{ editing ? 'Edit event' : 'Create a night' }}</h1>
            <p class="mt-2 text-muted-foreground">
                Set the ticket, costs, and capacity — break-even updates as you type.
            </p>
        </div>

        <Form
            :action="action"
            :method="editing ? 'put' : 'post'"
            class="space-y-4"
        >
            <div class="grid grid-cols-4 gap-3">
                <div>
                    <Label>Emoji</Label>
                    <Input name="emoji" :default-value="event?.emoji ?? '⚽'" />
                </div>
                <div class="col-span-3">
                    <Label>Title</Label>
                    <Input name="title" required :default-value="event?.title ?? ''" placeholder="Friday Football" />
                </div>
            </div>

            <div>
                <Label>Description</Label>
                <textarea
                    name="description"
                    class="mt-1 min-h-28 w-full rounded-md border bg-background px-3 py-2"
                    :default-value="event?.description ?? ''"
                    placeholder="Come alone — most people are."
                />
            </div>

            <div>
                <Label>Starts</Label>
                <Input name="starts_at" type="datetime-local" required :default-value="event?.starts_at ?? ''" />
            </div>
            <div>
                <Label>Venue</Label>
                <Input name="venue_name" required :default-value="event?.venue_name ?? ''" placeholder="Mt Eden 5-a-side" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <Label>Address</Label>
                    <Input name="venue_address" :default-value="event?.venue_address ?? ''" />
                </div>
                <div>
                    <Label>Suburb</Label>
                    <Input name="suburb" :default-value="event?.suburb ?? ''" placeholder="Mt Eden" />
                </div>
            </div>
            <div>
                <Label>Capacity</Label>
                <Input name="capacity" type="number" v-model="capacity" required />
            </div>

            <div class="rounded-2xl border bg-card p-4 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <Label class="text-base">Stripe product</Label>
                    <Link v-if="stripeConfigured" href="?refresh=1" class="text-xs text-primary">Refresh from Stripe</Link>
                </div>
                <p v-if="!stripeConfigured" class="text-sm text-muted-foreground">
                    Add <code>STRIPE_SECRET</code> to load products. You can still publish with a price below.
                </p>
                <select
                    v-model="selectedPrice"
                    class="w-full rounded-md border bg-background px-3 py-2"
                >
                    <option value="">No Stripe product — use the price below</option>
                    <option v-for="item in stripePrices" :key="item.price_id" :value="item.price_id">
                        {{ item.product_name }} · {{ item.amount_label }} {{ item.currency }}
                    </option>
                </select>
                <input type="hidden" name="stripe_price_id" :value="selectedPrice" />
                <div v-if="stripeConfigured">
                    <Label>Or paste a Price ID</Label>
                    <Input v-model="selectedPrice" placeholder="price_..." />
                </div>
            </div>

            <div>
                <Label>Ticket price NZD</Label>
                <Input name="price" type="number" step="1" v-model="price" required />
            </div>

            <div class="rounded-2xl border bg-card p-4 space-y-3">
                <Label class="text-base">Budget / costs (NZD)</Label>
                <p class="text-xs text-muted-foreground">
                    Platform fee assumed at {{ feePercent }}%. Net per ticket feeds break-even.
                </p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <Label>Venue</Label>
                        <Input name="venue_cost" type="number" step="1" min="0" v-model="venueCost" />
                    </div>
                    <div>
                        <Label>Host / gear</Label>
                        <Input name="host_cost" type="number" step="1" min="0" v-model="hostCost" />
                    </div>
                    <div>
                        <Label>Other</Label>
                        <Input name="other_cost" type="number" step="1" min="0" v-model="otherCost" />
                    </div>
                </div>
                <div>
                    <Label>Cost notes</Label>
                    <Input name="cost_notes" :default-value="event?.cost_notes ?? ''" placeholder="Pitch hire, balls, drinks…" />
                </div>

                <div class="grid gap-2 rounded-xl bg-muted/50 p-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Total costs</span>
                        <span class="font-semibold">{{ money(budget.costs) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Break-even tickets</span>
                        <span class="font-semibold">
                            <template v-if="budget.breakEvenTickets === null">n/a</template>
                            <template v-else>
                                {{ budget.breakEvenTickets }}
                                <span
                                    class="ml-1 text-xs"
                                    :class="budget.covers ? 'text-emerald-600' : 'text-red-600'"
                                >
                                    {{ budget.covers ? 'fits capacity' : 'over capacity' }}
                                </span>
                            </template>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Break-even price (full house)</span>
                        <span class="font-semibold">{{ money(budget.breakEvenPrice ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-muted-foreground">Profit if full</span>
                        <span
                            class="font-black"
                            :class="budget.fullProfit >= 0 ? 'text-emerald-600' : 'text-red-600'"
                        >
                            {{ money(budget.fullProfit) }}
                        </span>
                    </div>
                </div>
            </div>

            <Button type="submit" class="w-full">{{ editing ? 'Save event' : 'Publish event' }}</Button>
        </Form>
    </div>
</template>
