<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

type EventCard = {
    id: number;
    title: string;
    slug: string;
    emoji: string;
    starts_at_label: string;
    weekday: string;
    date_label: string;
    time_label: string;
    venue_name: string;
    suburb: string;
    capacity: number;
    price_label: string;
    spots_remaining: number;
    going: number;
    waitlist: number;
    coming_alone: number;
    coming_with_friend: number;
    newcomers: number;
    faces: { initials: string }[];
    url: string;
    description: string;
    community: { slug: string };
};

type Community = {
    name: string;
    tagline: string;
    member_count: number;
};

const props = defineProps<{
    community: Community | null;
    events: EventCard[];
}>();

const page = usePage();
const user = page.props.auth.user;

const featured = computed(() => props.events[0] ?? null);
const rest = computed(() => props.events.slice(1));

const suburbs = [
    'Ponsonby',
    'Grey Lynn',
    'Mt Eden',
    'Kingsland',
    'Takapuna',
    'K Road',
    'Parnell',
    'Newmarket',
    'Freemans Bay',
    'Epsom',
];

function tone(event: EventCard) {
    const map: Record<string, string> = {
        '⚽': 'bg-[#2F5C57] text-[#F3ECE3]',
        '🎳': 'bg-[#C45C26] text-[#F6EFE6]',
        '🏀': 'bg-[#8C4A2F] text-[#F6EFE6]',
        '🎱': 'bg-[#1E2C2A] text-[#F3ECE3]',
        '🍻': 'bg-[#A9783C] text-[#F6EFE6]',
    };

    return map[event.emoji] ?? 'bg-[#2F5C57] text-[#F3ECE3]';
}
</script>

<template>
    <Head title="Meet people. Do stuff. Make mates." />

    <section class="mx-auto max-w-6xl px-4 pt-14 pb-10 sm:pt-20">
        <p class="text-sm text-[#C45C26]">Auckland · for guys who want actual friends</p>
        <h1 class="mt-4 max-w-3xl font-serif text-5xl leading-[0.95] font-medium tracking-tight sm:text-7xl">
            Show up.
            <span class="italic text-[#C45C26]">Leave with mates.</span>
        </h1>
        <p class="mt-6 max-w-xl text-lg leading-relaxed text-[#1E2C2A]/72">
            Curated nights around the city. You don’t need a group already.
            Most people here are also coming on their own.
        </p>
        <div class="mt-8 flex flex-wrap items-center gap-3">
            <Link
                :href="user ? '/events' : '/register'"
                class="rounded-full bg-[#1E2C2A] px-6 py-3 text-sm font-semibold text-[#F3ECE3]"
            >
                {{ user ? 'See this month' : 'Join the club' }}
            </Link>
            <Link href="/events" class="rounded-full border border-[#1E2C2A]/20 px-6 py-3 text-sm font-medium">
                Upcoming nights
            </Link>
            <p class="text-sm text-[#1E2C2A]/55">
                {{ community?.member_count ?? 0 }} members · Auckland only
            </p>
        </div>
    </section>

    <div class="overflow-hidden border-y border-[#1E2C2A]/10 py-3">
        <div class="club-marquee flex w-max gap-10 text-sm tracking-wide text-[#1E2C2A]/55">
            <span v-for="(suburb, index) in [...suburbs, ...suburbs]" :key="`${suburb}-${index}`">
                {{ suburb }}
            </span>
        </div>
    </div>

    <section v-if="featured" class="mx-auto max-w-6xl px-4 py-16">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <h2 class="font-serif text-3xl font-medium">This month</h2>
                <p class="mt-1 text-[#1E2C2A]/60">Real nights. You just book in and turn up.</p>
            </div>
            <Link href="/events" class="text-sm text-[#C45C26]">All nights</Link>
        </div>

        <Link
            :href="featured.url"
            class="group grid overflow-hidden rounded-[28px] md:grid-cols-[1.15fr_0.85fr]"
        >
            <div :class="['flex flex-col justify-between p-8 sm:p-10', tone(featured)]">
                <div>
                    <p class="text-sm/6 opacity-80">Next up · {{ featured.suburb }}</p>
                    <div class="mt-6 font-serif text-6xl leading-none">{{ featured.emoji }}</div>
                    <h3 class="mt-5 font-serif text-4xl leading-tight font-medium sm:text-5xl">
                        {{ featured.title }}
                    </h3>
                    <p class="mt-4 max-w-md text-sm/6 opacity-85">{{ featured.description }}</p>
                </div>
                <div class="mt-10 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <div class="font-serif text-3xl">{{ featured.weekday }} {{ featured.date_label }}</div>
                        <div class="text-sm opacity-80">{{ featured.time_label }} · {{ featured.venue_name }}</div>
                    </div>
                    <div class="text-sm font-medium">{{ featured.price_label }} · {{ featured.spots_remaining }} spots left</div>
                </div>
            </div>
            <div class="flex flex-col justify-between bg-[#E7DDD1] p-8 text-[#1E2C2A] sm:p-10">
                <div>
                    <p class="text-sm text-[#C45C26]">You will not be the only one arriving solo</p>
                    <p class="mt-4 font-serif text-3xl leading-snug font-medium">
                        {{ featured.coming_alone }} coming alone.
                        {{ featured.going }} already in.
                    </p>
                    <p class="mt-4 text-sm leading-relaxed text-[#1E2C2A]/70">
                        That’s the design, not a coincidence. We keep nights small enough that
                        people actually talk, and the first twenty minutes are for mixing.
                    </p>
                </div>
                <div class="mt-10 flex items-center justify-between">
                    <div class="flex -space-x-2">
                        <span
                            v-for="(face, index) in featured.faces"
                            :key="index"
                            class="flex size-9 items-center justify-center rounded-full border border-[#E7DDD1] bg-[#1E2C2A] text-xs font-semibold text-[#F3ECE3]"
                        >
                            {{ face.initials }}
                        </span>
                    </div>
                    <span class="text-sm font-medium group-hover:underline">Grab a spot →</span>
                </div>
            </div>
        </Link>

        <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="event in rest"
                :key="event.id"
                :href="event.url"
                class="group flex flex-col rounded-3xl border border-[#1E2C2A]/10 bg-[#F8F3EC] p-6 transition hover:-translate-y-0.5"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs tracking-wide text-[#C45C26] uppercase">{{ event.suburb }}</p>
                        <h3 class="mt-2 font-serif text-2xl font-medium">{{ event.emoji }} {{ event.title }}</h3>
                    </div>
                    <span class="rounded-full bg-[#1E2C2A]/6 px-3 py-1 text-sm">{{ event.price_label }}</span>
                </div>
                <p class="mt-4 text-sm text-[#1E2C2A]/65">
                    {{ event.weekday }} {{ event.date_label }} · {{ event.time_label }}
                </p>
                <p class="text-sm text-[#1E2C2A]/65">{{ event.venue_name }}</p>
                <div class="mt-5 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1.5">
                            <span
                                v-for="(face, index) in event.faces"
                                :key="index"
                                class="flex size-6 items-center justify-center rounded-full bg-[#1E2C2A] text-[10px] text-[#F3ECE3]"
                            >
                                {{ face.initials }}
                            </span>
                        </div>
                        <span>{{ event.going }}/{{ event.capacity }}</span>
                    </div>
                    <span class="text-[#C45C26]">{{ event.spots_remaining }} left</span>
                </div>
                <p class="mt-3 text-xs text-[#1E2C2A]/50">
                    {{ event.coming_alone }} coming alone
                    <span v-if="event.waitlist"> · waitlist {{ event.waitlist }}</span>
                </p>
            </Link>
        </div>
    </section>

    <section v-else class="mx-auto max-w-6xl px-4 py-16">
        <div class="rounded-[28px] bg-[#E7DDD1] p-10">
            <h2 class="font-serif text-3xl font-medium">Nights go up here</h2>
            <p class="mt-3 max-w-lg text-[#1E2C2A]/70">
                Once the first events are published, this is where people pick a night and show up.
            </p>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-6 px-4 pb-8 md:grid-cols-3">
        <article class="rounded-3xl bg-[#F8F3EC] p-6">
            <p class="text-sm text-[#C45C26]">01</p>
            <h3 class="mt-3 font-serif text-2xl font-medium">Pick a night</h3>
            <p class="mt-3 text-sm leading-relaxed text-[#1E2C2A]/70">
                Football, bowling, a bar table, a Sunday run. Small enough that you are not lost in a crowd.
            </p>
        </article>
        <article class="rounded-3xl bg-[#F8F3EC] p-6">
            <p class="text-sm text-[#C45C26]">02</p>
            <h3 class="mt-3 font-serif text-2xl font-medium">Come as you are</h3>
            <p class="mt-3 text-sm leading-relaxed text-[#1E2C2A]/70">
                No group chat to break into. First twenty minutes are for names, suburbs, and what you actually like doing.
            </p>
        </article>
        <article class="rounded-3xl bg-[#1E2C2A] p-6 text-[#F3ECE3]">
            <p class="text-sm text-[#E0A077]">03</p>
            <h3 class="mt-3 font-serif text-2xl font-medium">Keep the good ones</h3>
            <p class="mt-3 text-sm leading-relaxed text-[#F3ECE3]/75">
                After the night you tick who you met. That’s how this becomes a social life, not a one-off.
            </p>
        </article>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12">
        <div class="rounded-[28px] border border-[#1E2C2A]/10 px-8 py-12 text-center">
            <p class="font-serif text-3xl leading-snug font-medium sm:text-4xl">
                “What if everyone already knows each other?”
            </p>
            <p class="mx-auto mt-4 max-w-xl text-[#1E2C2A]/65">
                That is the thing we actually solve. If you are new in Auckland, between friend groups,
                or just a bit over waiting for someone to organise it — this is for you.
            </p>
            <Link
                :href="user ? '/events' : '/register'"
                class="mt-8 inline-flex rounded-full bg-[#C45C26] px-6 py-3 text-sm font-semibold text-[#F6EFE6]"
            >
                {{ user ? 'See the nights' : 'Join Auckland M8s' }}
            </Link>
        </div>
    </section>

    <footer class="mx-auto flex max-w-6xl items-center justify-between px-4 py-10 text-sm text-[#1E2C2A]/50">
        <span>Auckland M8s</span>
        <span>Meet people. Do stuff. Make mates.</span>
    </footer>
</template>
