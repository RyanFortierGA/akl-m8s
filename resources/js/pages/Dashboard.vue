<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

type EventCard = {
    id: number;
    title: string;
    emoji: string;
    starts_at_label: string;
    url: string;
    price_label: string;
    venue_name: string;
};

type Mate = {
    id: number;
    name: string;
    suburb: string | null;
    met_at: string | null;
    met_at_emoji: string | null;
    contact_shared: boolean;
};

defineProps<{
    upcoming: EventCard[];
    past: EventCard[];
    discover: EventCard[];
    mates: Mate[];
    meetPrompt: { title: string; emoji: string; url: string } | null;
    stats: { events_attended: number; mates: number; is_admin: boolean };
}>();

const page = usePage();
const prelaunch = computed(() => (page.props.club as { prelaunch: boolean }).prelaunch);
const launchLabel = computed(() => (page.props.club as { launchLabel: string }).launchLabel);
</script>

<template>
    <Head title="My nights" />
    <div class="mx-auto max-w-5xl space-y-8 p-6">
        <div class="flex items-end justify-between">
            <div>
                <h1 class="text-3xl font-black">Your nights</h1>
                <p class="text-muted-foreground">
                    {{ stats.events_attended }} attended · {{ stats.mates }} mates made
                </p>
            </div>
            <div class="flex gap-2">
                <Button v-if="stats.is_admin" as-child variant="secondary">
                    <Link href="/admin">Admin</Link>
                </Button>
            </div>
        </div>

        <Link
            v-if="meetPrompt"
            :href="meetPrompt.url"
            class="block rounded-2xl border border-primary/40 bg-primary/10 p-5"
        >
            <div class="font-bold">{{ meetPrompt.emoji }} How was {{ meetPrompt.title }}?</div>
            <div class="text-sm text-muted-foreground">Tick the guys you met. That’s how this becomes a social life.</div>
        </Link>

        <section>
            <h2 class="mb-3 font-bold">Upcoming</h2>
            <div v-if="!upcoming.length" class="rounded-2xl border bg-card p-6 text-muted-foreground">
                <template v-if="prelaunch">
                    Nights kick off in {{ launchLabel }}. You are on the waitlist.
                </template>
                <template v-else>
                    No nights booked yet.
                    <Link href="/events" class="text-primary">Grab a spot.</Link>
                </template>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <Link
                    v-for="event in upcoming"
                    :key="event.id"
                    :href="event.url"
                    class="rounded-2xl border bg-card p-4"
                >
                    <div class="text-2xl">{{ event.emoji }}</div>
                    <div class="font-bold">{{ event.title }}</div>
                    <div class="text-sm text-muted-foreground">{{ event.starts_at_label }} · {{ event.venue_name }}</div>
                </Link>
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-bold">Mates</h2>
                <Link href="/mates" class="text-sm text-primary">See all</Link>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="mate in mates"
                    :key="mate.id"
                    href="/mates"
                    class="rounded-full border bg-card px-4 py-2 text-sm"
                >
                    {{ mate.met_at_emoji }} {{ mate.name }}
                </Link>
                <p v-if="!mates.length" class="text-sm text-muted-foreground">
                    After your first night, you’ll add people here instead of swapping Instagrams in the car park.
                </p>
            </div>
        </section>

        <section v-if="!prelaunch">
            <h2 class="mb-3 font-bold">You might like</h2>
            <div class="grid gap-3 md:grid-cols-3">
                <Link
                    v-for="event in discover"
                    :key="event.id"
                    :href="event.url"
                    class="rounded-2xl border bg-card p-4"
                >
                    <div>{{ event.emoji }} {{ event.title }}</div>
                    <div class="text-sm text-muted-foreground">{{ event.price_label }} · {{ event.starts_at_label }}</div>
                </Link>
            </div>
        </section>
    </div>
</template>
