<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminNav from '@/components/AdminNav.vue';
import { Button } from '@/components/ui/button';

type EventRow = {
    id: number;
    title: string;
    emoji: string;
    starts_at_label: string;
    signups: number;
    spots: number;
    capacity: number;
    waitlist: number;
    pending: number;
    price_label: string;
    profit_label: string;
    is_profitable: boolean;
    break_even_tickets: number | null;
    stripe_product_name: string | null;
    stripe_price_id: string | null;
    public_url: string;
    edit_url: string;
    attendees_url: string;
};

defineProps<{
    community: { name: string; member_count: number };
    stripeConfigured: boolean;
    stats: {
        members: number;
        upcoming_nights: number;
        signups: number;
        waitlist: number;
        ticket_sales_label: string;
        cost_label: string;
        profit_label: string;
        is_profitable: boolean;
        connections: number;
    };
    upcoming: EventRow[];
    past: EventRow[];
}>();
</script>

<template>
    <Head title="Admin" />
    <div class="mx-auto max-w-5xl space-y-8 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold tracking-widest text-primary uppercase">Admin</p>
                <h1 class="text-3xl font-black">{{ community.name }}</h1>
                <p class="text-muted-foreground">Plan nights, track break-even, watch who shows up.</p>
            </div>
            <div class="flex flex-col items-end gap-3">
                <AdminNav active="dashboard" />
                <Button as-child>
                    <Link href="/admin/events/new">New event</Link>
                </Button>
            </div>
        </div>

        <p v-if="!stripeConfigured" class="rounded-2xl border border-primary/40 bg-primary/10 p-4 text-sm">
            Stripe is not connected yet. Events still work with a local checkout.
            Add <code>STRIPE_SECRET</code> to <code>.env</code>, then you can pick a Stripe product on each event.
        </p>

        <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Upcoming</div>
                <div class="text-3xl font-black">{{ stats.upcoming_nights }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Signups</div>
                <div class="text-3xl font-black">{{ stats.signups }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Waitlist</div>
                <div class="text-3xl font-black">{{ stats.waitlist }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Revenue</div>
                <div class="text-3xl font-black">{{ stats.ticket_sales_label }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Costs</div>
                <div class="text-3xl font-black">{{ stats.cost_label }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Profit</div>
                <div
                    class="text-3xl font-black"
                    :class="stats.is_profitable ? 'text-emerald-600' : 'text-red-600'"
                >
                    {{ stats.profit_label }}
                </div>
            </div>
        </div>

        <section>
            <h2 class="mb-3 font-bold">Upcoming</h2>
            <div v-if="!upcoming.length" class="rounded-2xl border bg-card p-6 text-muted-foreground">
                No nights yet. Create the first one.
            </div>
            <div class="overflow-hidden rounded-2xl border">
                <div
                    v-for="event in upcoming"
                    :key="event.id"
                    class="grid gap-3 border-b bg-card px-4 py-3 last:border-b-0 md:grid-cols-[1.4fr_1fr_auto] md:items-center"
                >
                    <div>
                        <div class="font-semibold">{{ event.emoji }} {{ event.title }}</div>
                        <div class="text-sm text-muted-foreground">{{ event.starts_at_label }} · {{ event.price_label }}</div>
                        <div class="text-xs text-muted-foreground">
                            {{ event.stripe_product_name || event.stripe_price_id || 'No Stripe product attached' }}
                        </div>
                    </div>
                    <div class="text-sm">
                        <div><span class="font-bold">{{ event.signups }}</span> signed up · {{ event.spots }}/{{ event.capacity }} spots</div>
                        <div class="text-muted-foreground">
                            Waitlist {{ event.waitlist }}
                            <span v-if="event.pending"> · {{ event.pending }} pending payment</span>
                        </div>
                        <div :class="event.is_profitable ? 'text-emerald-600' : 'text-red-600'">
                            {{ event.profit_label }}
                            <span v-if="event.break_even_tickets !== null" class="text-muted-foreground">
                                · break-even {{ event.break_even_tickets }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <Link :href="event.attendees_url" class="text-primary">People</Link>
                        <Link :href="event.edit_url">Edit</Link>
                        <a :href="event.public_url" class="text-muted-foreground">Public</a>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="past.length">
            <h2 class="mb-3 font-bold">Past</h2>
            <div class="overflow-hidden rounded-2xl border">
                <div
                    v-for="event in past"
                    :key="event.id"
                    class="flex items-center justify-between border-b bg-card px-4 py-3 last:border-b-0"
                >
                    <div>
                        <div class="font-semibold">{{ event.emoji }} {{ event.title }}</div>
                        <div class="text-sm text-muted-foreground">
                            {{ event.starts_at_label }} · {{ event.signups }} went ·
                            <span :class="event.is_profitable ? 'text-emerald-600' : 'text-red-600'">
                                {{ event.profit_label }}
                            </span>
                        </div>
                    </div>
                    <Link :href="event.attendees_url" class="text-sm text-primary">People</Link>
                </div>
            </div>
        </section>
    </div>
</template>
