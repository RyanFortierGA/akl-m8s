<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
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
    stripe_price_id: string | null;
    stripe_product_name: string | null;
};

const props = defineProps<{
    event: EventForm | null;
    stripeConfigured: boolean;
    stripePrices: StripePrice[];
}>();

const selectedPrice = ref(props.event?.stripe_price_id ?? '');
const price = ref(props.event?.price?.toString() ?? '15');

const selected = computed(() =>
    props.stripePrices.find((item) => item.price_id === selectedPrice.value) ?? null,
);

watch(selectedPrice, (value) => {
    const match = props.stripePrices.find((item) => item.price_id === value);
    if (match) {
        price.value = String(match.amount_cents / 100);
    }
});

const editing = computed(() => Boolean(props.event));
const action = computed(() =>
    editing.value ? `/admin/events/${props.event?.id}` : '/admin/events',
);
</script>

<template>
    <Head :title="editing ? 'Edit event' : 'New event'" />
    <div class="mx-auto max-w-xl p-6">
        <Link href="/admin" class="text-sm text-primary">Back to admin</Link>
        <h1 class="mt-3 text-3xl font-black">{{ editing ? 'Edit event' : 'Create a night' }}</h1>
        <p class="mt-2 text-muted-foreground">
            Pick a Stripe product so payment, reporting, and this event stay in sync.
        </p>

        <Form
            :action="action"
            :method="editing ? 'put' : 'post'"
            class="mt-8 space-y-4"
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
                <Input name="capacity" type="number" :default-value="event?.capacity ?? 20" required />
            </div>

            <div class="rounded-2xl border bg-card p-4 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <Label class="text-base">Stripe product</Label>
                    <Link v-if="stripeConfigured" href="?refresh=1" class="text-xs text-primary">Refresh from Stripe</Link>
                </div>
                <p v-if="!stripeConfigured" class="text-sm text-muted-foreground">
                    Add <code>STRIPE_SECRET</code> to load products. You can still publish the night with a price below.
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
                <p v-if="selected" class="text-xs text-muted-foreground">
                    Checkout will charge Stripe price <code>{{ selected.price_id }}</code>.
                </p>
                <div v-if="stripeConfigured">
                    <Label>Or paste a Price ID</Label>
                    <Input v-model="selectedPrice" placeholder="price_..." />
                </div>
            </div>

            <div>
                <Label>Price NZD</Label>
                <Input name="price" type="number" step="1" v-model="price" required />
                <p class="mt-1 text-xs text-muted-foreground">Filled automatically when you pick a Stripe product.</p>
            </div>

            <Button type="submit" class="w-full">{{ editing ? 'Save event' : 'Publish event' }}</Button>
        </Form>
    </div>
</template>
