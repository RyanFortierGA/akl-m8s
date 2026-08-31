<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminNav from '@/components/AdminNav.vue';

type Person = {
    id: number;
    name: string;
    email: string;
    suburb: string | null;
    status: string;
    coming_alone: boolean;
    events_attended: number;
};

type Budget = {
    revenue_label: string;
    cost_label: string;
    profit_label: string;
    fees_cents: number;
    is_profitable: boolean;
    break_even_tickets_label: string;
    break_even_price_label: string;
    projected_full_profit_cents: number;
    signups: number;
    capacity: number;
    venue_cost_cents: number;
    host_cost_cents: number;
    other_cost_cents: number;
    cost_notes: string | null;
};

defineProps<{
    event: {
        id: number;
        title: string;
        emoji: string;
        starts_at_label: string;
        venue_name: string;
        capacity: number;
        price_label: string;
        signups: number;
        spots: number;
        waitlist: number;
        pending: number;
        stripe_product_name: string | null;
        stripe_price_id: string | null;
        public_url: string;
        edit_url: string;
    };
    budget: Budget;
    signups: Person[];
    waitlist: Person[];
    pending: Person[];
}>();

function setStatus(eventId: number, rsvpId: number, status: string) {
    router.post(`/admin/events/${eventId}/attendance`, { rsvp_id: rsvpId, status });
}

function money(cents: number) {
    const sign = cents < 0 ? '-' : '';
    return `${sign}$${Math.abs(Math.round(cents / 100))}`;
}
</script>

<template>
    <Head :title="`${event.title} signups`" />
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <AdminNav active="dashboard" />
        <div>
            <Link href="/admin" class="text-sm text-primary">Back to admin</Link>
            <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black">{{ event.emoji }} {{ event.title }}</h1>
                    <p class="text-muted-foreground">{{ event.starts_at_label }} · {{ event.venue_name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ event.stripe_product_name || event.stripe_price_id || 'No Stripe product' }} · {{ event.price_label }}
                    </p>
                </div>
                <div class="flex gap-3 text-sm">
                    <Link :href="event.edit_url">Edit / budget</Link>
                    <a :href="event.public_url" class="text-muted-foreground">Public page</a>
                </div>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Signups</div>
                <div class="text-3xl font-black">{{ event.signups }}</div>
                <div class="text-xs text-muted-foreground">{{ event.spots }}/{{ event.capacity }} spots filled</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Waitlist</div>
                <div class="text-3xl font-black">{{ event.waitlist }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Pending payment</div>
                <div class="text-3xl font-black">{{ event.pending }}</div>
            </div>
        </div>

        <section class="rounded-2xl border bg-card p-5">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-bold">Budget</h2>
                    <p class="text-sm text-muted-foreground">Live from ticket sales minus fees and your costs.</p>
                </div>
                <Link :href="event.edit_url" class="text-sm text-primary">Edit costs</Link>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                <div>
                    <div class="text-xs text-muted-foreground">Revenue</div>
                    <div class="text-xl font-black">{{ budget.revenue_label }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground">Costs</div>
                    <div class="text-xl font-black">{{ budget.cost_label }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground">Profit / loss</div>
                    <div
                        class="text-xl font-black"
                        :class="budget.is_profitable ? 'text-emerald-600' : 'text-red-600'"
                    >
                        {{ budget.profit_label }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground">If full</div>
                    <div
                        class="text-xl font-black"
                        :class="budget.projected_full_profit_cents >= 0 ? 'text-emerald-600' : 'text-red-600'"
                    >
                        {{ money(budget.projected_full_profit_cents) }}
                    </div>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-muted-foreground">
                <span>Break-even: {{ budget.break_even_tickets_label }} tickets</span>
                <span>or {{ budget.break_even_price_label }} / ticket at full house</span>
                <span v-if="budget.cost_notes">Notes: {{ budget.cost_notes }}</span>
            </div>
        </section>

        <section class="mt-2">
            <h2 class="font-bold">Signed up</h2>
            <div class="mt-3 overflow-hidden rounded-2xl border">
                <div v-if="!signups.length" class="p-4 text-sm text-muted-foreground">Nobody confirmed yet.</div>
                <div
                    v-for="person in signups"
                    :key="person.id"
                    class="flex items-center justify-between border-b bg-card px-4 py-3 last:border-b-0"
                >
                    <div>
                        <div class="font-semibold">{{ person.name }}</div>
                        <div class="text-sm text-muted-foreground">
                            {{ person.email }} · {{ person.suburb || 'Auckland' }} ·
                            {{ person.coming_alone ? 'solo' : 'with a mate' }}
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <button class="text-primary" @click="setStatus(event.id, person.id, 'attended')">Attended</button>
                        <button class="text-muted-foreground" @click="setStatus(event.id, person.id, 'no_show')">No-show</button>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="pending.length" class="mt-8">
            <h2 class="font-bold">Pending payment ({{ pending.length }})</h2>
            <div class="mt-3 overflow-hidden rounded-2xl border">
                <div
                    v-for="person in pending"
                    :key="person.id"
                    class="flex items-center justify-between border-b bg-card px-4 py-3 last:border-b-0"
                >
                    <div>
                        <div class="font-semibold">{{ person.name }}</div>
                        <div class="text-sm text-muted-foreground">{{ person.email }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-8">
            <h2 class="font-bold">Waitlist ({{ waitlist.length }})</h2>
            <div class="mt-3 overflow-hidden rounded-2xl border">
                <div v-if="!waitlist.length" class="p-4 text-sm text-muted-foreground">Waitlist is empty.</div>
                <div
                    v-for="person in waitlist"
                    :key="person.id"
                    class="flex items-center justify-between border-b bg-card px-4 py-3 last:border-b-0"
                >
                    <div>
                        <div class="font-semibold">{{ person.name }}</div>
                        <div class="text-sm text-muted-foreground">{{ person.email }}</div>
                    </div>
                    <button class="text-sm text-primary" @click="setStatus(event.id, person.id, 'confirmed')">
                        Move to signups
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
