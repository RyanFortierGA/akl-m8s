<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

type Person = {
    id: number;
    name: string;
    email: string;
    suburb: string | null;
    status: string;
    coming_alone: boolean;
    events_attended: number;
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
    signups: Person[];
    waitlist: Person[];
    pending: Person[];
}>();

function setStatus(eventId: number, rsvpId: number, status: string) {
    router.post(`/admin/events/${eventId}/attendance`, { rsvp_id: rsvpId, status });
}
</script>

<template>
    <Head :title="`${event.title} signups`" />
    <div class="mx-auto max-w-4xl p-6">
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
                <Link :href="event.edit_url">Edit / Stripe</Link>
                <a :href="event.public_url" class="text-muted-foreground">Public page</a>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
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

        <section class="mt-8">
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
                            {{ person.coming_alone ? 'alone' : 'with a mate' }}
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
