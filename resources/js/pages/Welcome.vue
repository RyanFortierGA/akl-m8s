<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';

type EventCard = {
    id: number;
    title: string;
    slug: string;
    emoji: string;
    starts_at_label: string;
    venue_name: string;
    suburb: string;
    capacity: number;
    price_label: string;
    spots_remaining: number;
    going: number;
    coming_alone: number;
    coming_with_friend: number;
    newcomers: number;
    url: string;
    community: { slug: string };
};

type Community = {
    name: string;
    tagline: string;
    member_count: number;
};

defineProps<{
    community: Community | null;
    events: EventCard[];
}>();

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <Head title="Meet people. Do stuff. Make mates." />

    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(216,255,46,0.18),_transparent_42%)]" />
        <div class="mx-auto grid max-w-6xl gap-12 px-4 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:py-24">
            <div>
                <p class="mb-4 text-sm font-semibold tracking-[0.2em] text-[#D8FF2E] uppercase">Auckland club</p>
                <h1 class="max-w-xl text-5xl leading-[0.95] font-black tracking-tight sm:text-7xl">
                    Auckland<br />M8s
                </h1>
                <p class="mt-6 max-w-lg text-lg text-white/70">
                    A reliable way for guys in Auckland to make new friends by showing
                    up to fun, structured nights with other guys who also want to meet people.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        :href="user ? '/events' : '/register'"
                        class="rounded-full bg-[#D8FF2E] px-6 py-3 text-sm font-bold text-[#071018]"
                    >
                        {{ user ? 'See this month' : 'Join the club' }}
                    </Link>
                    <Link href="/events" class="rounded-full border border-white/20 px-6 py-3 text-sm font-semibold">
                        Upcoming nights
                    </Link>
                </div>
                <p class="mt-6 text-sm text-white/50">
                    {{ community?.member_count ?? 9 }} founding members · Come alone. Most people are.
                </p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl">
                <p class="text-sm font-semibold text-[#D8FF2E]">The barrier we actually solve</p>
                <h2 class="mt-3 text-2xl font-bold">“What if everyone already knows each other?”</h2>
                <p class="mt-3 text-white/65">
                    You won’t be the only person standing there. We design every night around people
                    who don’t know anyone yet.
                </p>
                <dl class="mt-8 grid grid-cols-2 gap-4 text-sm">
                    <div class="rounded-2xl bg-black/20 p-4">
                        <dt class="text-white/50">Coming alone</dt>
                        <dd class="text-3xl font-black">{{ events[0]?.coming_alone ?? 8 }}</dd>
                    </div>
                    <div class="rounded-2xl bg-black/20 p-4">
                        <dt class="text-white/50">New to the club</dt>
                        <dd class="text-3xl font-black">{{ events[0]?.newcomers ?? 12 }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-20">
        <div class="mb-8 flex items-end justify-between">
            <div>
                <h2 class="text-3xl font-black">This month</h2>
                <p class="text-white/60">Curated nights. You just show up.</p>
            </div>
            <Link href="/events" class="text-sm text-[#D8FF2E]">All events</Link>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            <Link
                v-for="event in events"
                :key="event.id"
                :href="event.url"
                class="group rounded-3xl border border-white/10 bg-[#0E1A26] p-5 transition hover:border-[#D8FF2E]/50"
            >
                <div class="flex items-start justify-between">
                    <span class="text-4xl">{{ event.emoji }}</span>
                    <span class="rounded-full bg-[#D8FF2E]/15 px-3 py-1 text-sm font-semibold text-[#D8FF2E]">
                        {{ event.price_label }}
                    </span>
                </div>
                <h3 class="mt-4 text-xl font-bold">{{ event.title }}</h3>
                <p class="mt-1 text-sm text-white/60">{{ event.starts_at_label }}</p>
                <p class="text-sm text-white/60">{{ event.venue_name }} · {{ event.suburb }}</p>
                <div class="mt-5 flex items-center justify-between text-sm">
                    <span>{{ event.going }}/{{ event.capacity }} going</span>
                    <span class="text-[#D8FF2E]">{{ event.spots_remaining }} spots left</span>
                </div>
                <p class="mt-3 text-xs text-white/45">
                    👤 {{ event.coming_alone }} coming alone · 🆕 {{ event.newcomers }} newer members
                </p>
            </Link>
        </div>
    </section>
</template>
