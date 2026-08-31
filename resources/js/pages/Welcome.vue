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
    series_label: string | null;
    faces: { initials: string }[];
    url: string;
    description: string;
    community: { slug: string };
};

type RegularNight = {
    key: string;
    label: string;
    emoji: string;
    blurb: string;
    next_label: string | null;
    url: string;
};

type Community = {
    name: string;
    tagline: string;
    member_count: number;
};

const props = defineProps<{
    community: Community | null;
    events: EventCard[];
    regularNights: RegularNight[];
    waitlistCount: number;
}>();

const page = usePage();
const user = page.props.auth.user;
const club = computed(() => page.props.club as { prelaunch: boolean; launchLabel: string });
const prelaunch = computed(() => club.value.prelaunch);
const launchLabel = computed(() => club.value.launchLabel);

const featured = computed(() => props.events[0] ?? null);
const rest = computed(() => props.events.slice(1));

function tone(event: EventCard) {
    const map: Record<string, string> = {
        '⚽': 'bg-[#2F5C57] text-[#F3ECE3]',
        '🎳': 'bg-[#C45C26] text-[#F6EFE6]',
        '🏀': 'bg-[#8C4A2F] text-[#F6EFE6]',
        '🎱': 'bg-[#1E2C2A] text-[#F3ECE3]',
        '🍻': 'bg-[#A9783C] text-[#F6EFE6]',
        '🃏': 'bg-[#3D4F3A] text-[#F3ECE3]',
        '🧠': 'bg-[#5C4A72] text-[#F6EFE6]',
    };

    return map[event.emoji] ?? 'bg-[#2F5C57] text-[#F3ECE3]';
}
</script>

<template>
    <Head title="Meet people. Do stuff. Make mates." />

    <section class="mx-auto max-w-6xl px-4 pt-14 pb-10 sm:pt-20">
        <p v-if="prelaunch" class="text-sm font-medium text-[#C45C26]">
            Events starting in {{ launchLabel }}!
        </p>
        <p v-else class="text-sm text-[#C45C26]">Auckland nights for guys who want a proper social life</p>

        <h1 class="mt-4 max-w-3xl font-serif text-5xl leading-[0.95] font-medium tracking-tight sm:text-7xl">
            <template v-if="prelaunch">
                Join the waitlist.
                <span class="italic text-[#C45C26]">Be first in.</span>
            </template>
            <template v-else>
                Show up.
                <span class="italic text-[#C45C26]">Leave with mates.</span>
            </template>
        </h1>

        <p class="mt-6 max-w-xl text-lg leading-relaxed text-[#1E2C2A]/72">
            <template v-if="prelaunch">
                Footy, trivia, MTG, bar nights. We are lining up venues and regular nights across Auckland.
                Sign up now and we will let you know when bookings open.
            </template>
            <template v-else>
                Footy, trivia, MTG, bar nights. Organised so you just book in and turn up.
                Solo or with mates. Both work here.
            </template>
        </p>

        <div class="mt-8 flex flex-wrap items-center gap-3">
            <Link
                v-if="prelaunch && !user"
                href="/register"
                class="rounded-full bg-[#1E2C2A] px-6 py-3 text-sm font-semibold text-[#F3ECE3]"
            >
                Join the waitlist
            </Link>
            <Link
                v-else-if="prelaunch && user"
                href="/dashboard"
                class="rounded-full bg-[#1E2C2A] px-6 py-3 text-sm font-semibold text-[#F3ECE3]"
            >
                You are on the list
            </Link>
            <Link
                v-else
                :href="user ? '/events' : '/register'"
                class="rounded-full bg-[#1E2C2A] px-6 py-3 text-sm font-semibold text-[#F3ECE3]"
            >
                {{ user ? 'See this month' : 'Join the club' }}
            </Link>

            <Link
                v-if="!prelaunch"
                href="/events"
                class="rounded-full border border-[#1E2C2A]/20 px-6 py-3 text-sm font-medium"
            >
                Upcoming nights
            </Link>

            <p class="text-sm text-[#1E2C2A]/55">
                <template v-if="prelaunch">
                    {{ waitlistCount }} on the waitlist
                </template>
                <template v-else>
                    {{ community?.member_count ?? 0 }} members in Auckland
                </template>
            </p>
        </div>
    </section>

    <section v-if="prelaunch" class="mx-auto max-w-6xl px-4 pb-10">
        <div class="rounded-[28px] bg-[#E7DDD1] p-8 sm:p-10">
            <p class="text-sm font-medium text-[#C45C26]">What you are signing up for</p>
            <h2 class="mt-2 font-serif text-3xl font-medium">Regular nights, booked and sorted</h2>
            <p class="mt-3 max-w-2xl leading-relaxed text-[#1E2C2A]/70">
                Small groups, mixed teams, venues locked in. Flying solo is normal here.
                Bringing a mate works too. Either way you get a proper night out without organising it yourself.
            </p>
            <Link
                v-if="!user"
                href="/register"
                class="mt-6 inline-flex rounded-full bg-[#C45C26] px-6 py-3 text-sm font-semibold text-[#F6EFE6]"
            >
                Join the waitlist
            </Link>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-10">
        <div class="mb-5 flex items-end justify-between gap-4">
            <div>
                <h2 class="font-serif text-2xl font-medium">Regular nights</h2>
                <p class="mt-1 text-sm text-[#1E2C2A]/60">
                    <template v-if="prelaunch">Starting {{ launchLabel }}. The stuff we will run every week or so.</template>
                    <template v-else>The stuff we run every week or so.</template>
                </p>
            </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <component
                :is="prelaunch ? 'div' : Link"
                v-for="night in regularNights"
                :key="night.key"
                v-bind="prelaunch ? {} : { href: night.url }"
                class="rounded-2xl border border-[#1E2C2A]/10 bg-[#F8F3EC] p-4"
                :class="prelaunch ? '' : 'transition hover:border-[#C45C26]/40'"
            >
                <div class="text-2xl">{{ night.emoji }}</div>
                <h3 class="mt-2 font-serif text-lg font-medium">{{ night.label }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-[#1E2C2A]/65">{{ night.blurb }}</p>
                <p class="mt-3 text-xs font-medium text-[#C45C26]">
                    <template v-if="prelaunch">Starting {{ launchLabel }}</template>
                    <template v-else>{{ night.next_label ? `Next: ${night.next_label}` : 'Dates coming soon' }}</template>
                </p>
            </component>
        </div>
    </section>

    <section v-if="featured && !prelaunch" class="mx-auto max-w-6xl px-4 py-10">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <h2 class="font-serif text-3xl font-medium">This month</h2>
                <p class="mt-1 text-[#1E2C2A]/60">Pick a date. We handle the rest.</p>
            </div>
            <Link href="/events" class="text-sm text-[#C45C26]">All nights</Link>
        </div>

        <Link
            :href="featured.url"
            class="group grid overflow-hidden rounded-[28px] md:grid-cols-[1.15fr_0.85fr]"
        >
            <div :class="['flex flex-col justify-between p-8 sm:p-10', tone(featured)]">
                <div>
                    <p class="text-sm/6 opacity-80">
                        Next up<span v-if="featured.series_label"> · {{ featured.series_label }}</span>
                        · {{ featured.suburb }}
                    </p>
                    <div class="mt-6 font-serif text-6xl leading-none">{{ featured.emoji }}</div>
                    <h3 class="mt-5 font-serif text-4xl leading-tight font-medium sm:text-5xl">
                        {{ featured.title }}
                    </h3>
                    <p class="mt-4 max-w-md text-sm/6 opacity-85">{{ featured.description }}</p>
                </div>
                <div class="mt-10 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <div class="font-serif text-3xl">{{ featured.weekday }} {{ featured.date_label }}</div>
                        <div class="text-sm opacity-80">{{ featured.time_label }}, {{ featured.venue_name }}</div>
                    </div>
                    <div class="text-sm font-medium">{{ featured.price_label }}, {{ featured.spots_remaining }} spots left</div>
                </div>
            </div>
            <div class="flex flex-col justify-between bg-[#E7DDD1] p-8 text-[#1E2C2A] sm:p-10">
                <div>
                    <p class="text-sm text-[#C45C26]">Built for solo arrivals. Great with mates too.</p>
                    <p class="mt-4 font-serif text-3xl leading-snug font-medium">
                        {{ featured.coming_alone }} flying solo.
                        {{ featured.coming_with_friend }} with a mate.
                        {{ featured.going }} in total.
                    </p>
                    <p class="mt-4 text-sm leading-relaxed text-[#1E2C2A]/70">
                        Small groups, mixed teams, first twenty minutes for names.
                        You get a proper night out even if you already have friends.
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
                    {{ event.weekday }} {{ event.date_label }}, {{ event.time_label }}
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
                    {{ event.coming_alone }} solo, {{ event.coming_with_friend }} with a mate
                    <span v-if="event.waitlist">. Waitlist {{ event.waitlist }}</span>
                </p>
            </Link>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-6 px-4 pb-8 md:grid-cols-3">
        <article class="rounded-3xl bg-[#F8F3EC] p-6">
            <p class="text-sm text-[#C45C26]">01</p>
            <h3 class="mt-3 font-serif text-2xl font-medium">Pick a night</h3>
            <p class="mt-3 text-sm leading-relaxed text-[#1E2C2A]/70">
                Footy, trivia, MTG, bowling, bar tables. Small enough that you are not lost in a crowd.
            </p>
        </article>
        <article class="rounded-3xl bg-[#F8F3EC] p-6">
            <p class="text-sm text-[#C45C26]">02</p>
            <h3 class="mt-3 font-serif text-2xl font-medium">Come as you are</h3>
            <p class="mt-3 text-sm leading-relaxed text-[#1E2C2A]/70">
                Solo or with a mate. First twenty minutes are for names, suburbs, and what you actually like doing.
            </p>
        </article>
        <article class="rounded-3xl bg-[#1E2C2A] p-6 text-[#F3ECE3]">
            <p class="text-sm text-[#E0A077]">03</p>
            <h3 class="mt-3 font-serif text-2xl font-medium">Keep the good ones</h3>
            <p class="mt-3 text-sm leading-relaxed text-[#F3ECE3]/75">
                After the night you tick who you met. That is how this becomes a social life, not a one-off.
            </p>
        </article>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12">
        <div class="rounded-[28px] border border-[#1E2C2A]/10 px-8 py-12 text-center">
            <p class="font-serif text-3xl leading-snug font-medium sm:text-4xl">
                <template v-if="prelaunch">
                    First nights drop in {{ launchLabel }}.
                </template>
                <template v-else>
                    “What if everyone already knows each other?”
                </template>
            </p>
            <p class="mx-auto mt-4 max-w-xl text-[#1E2C2A]/65">
                <template v-if="prelaunch">
                    Join the waitlist and we will email you when bookings open.
                    {{ waitlistCount }} guys already signed up.
                </template>
                <template v-else>
                    We mix the room on purpose. New in Auckland, between friend groups, or just sick of organising it yourself.
                    This is still for you.
                </template>
            </p>
            <Link
                :href="prelaunch ? (user ? '/dashboard' : '/register') : (user ? '/events' : '/register')"
                class="mt-8 inline-flex rounded-full bg-[#C45C26] px-6 py-3 text-sm font-semibold text-[#F6EFE6]"
            >
                <template v-if="prelaunch">
                    {{ user ? 'You are on the list' : 'Join the waitlist' }}
                </template>
                <template v-else>
                    {{ user ? 'See the nights' : 'Join Auckland M8s' }}
                </template>
            </Link>
        </div>
    </section>

    <footer class="mx-auto flex max-w-6xl items-center justify-between px-4 py-10 text-sm text-[#1E2C2A]/50">
        <span>Auckland M8s</span>
        <span>Meet people. Do stuff. Make mates.</span>
    </footer>
</template>
