<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

type EventCard = {
    id: number;
    title: string;
    emoji: string;
    starts_at_label: string;
    venue_name: string;
    suburb: string;
    series_label: string | null;
    capacity: number;
    price_label: string;
    spots_remaining: number;
    going: number;
    coming_alone: number;
    coming_with_friend: number;
    newcomers: number;
    url: string;
};

defineProps<{
    events: EventCard[];
    waitlistCount: number;
}>();

const page = usePage();
const user = page.props.auth.user;
const club = computed(() => page.props.club as { prelaunch: boolean; launchLabel: string });
const prelaunch = computed(() => club.value.prelaunch);
const launchLabel = computed(() => club.value.launchLabel);
</script>

<template>
    <Head :title="prelaunch ? 'Waitlist' : 'Upcoming nights'" />
    <div class="mx-auto max-w-5xl p-6">
        <template v-if="prelaunch">
            <p class="text-sm font-medium text-[#C45C26]">Events starting in {{ launchLabel }}!</p>
            <h1 class="mt-2 text-3xl font-black">Join the waitlist</h1>
            <p class="mt-2 max-w-lg text-muted-foreground">
                We are lining up footy, trivia, MTG, and bar nights across Auckland.
                Sign up now and we will let you know when bookings open.
            </p>
            <p class="mt-4 text-sm text-muted-foreground">{{ waitlistCount }} on the waitlist</p>
            <Link
                :href="user ? '/dashboard' : '/register'"
                class="mt-6 inline-flex rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground"
            >
                {{ user ? 'You are on the list' : 'Join the waitlist' }}
            </Link>
            <Link href="/" class="mt-4 block text-sm text-primary">Back to home</Link>
        </template>

        <template v-else>
            <h1 class="text-3xl font-black">Upcoming nights</h1>
            <p class="mt-2 text-muted-foreground">Organised nights around Auckland. Solo or with mates.</p>
            <div class="mt-8 space-y-4">
                <Link
                    v-for="event in events"
                    :key="event.id"
                    :href="event.url"
                    class="flex items-center justify-between rounded-2xl border bg-card p-5 hover:border-primary"
                >
                    <div class="flex items-center gap-4">
                        <div class="text-3xl">{{ event.emoji }}</div>
                        <div>
                            <h2 class="font-bold">
                                {{ event.title }}
                                <span v-if="event.series_label" class="text-sm font-normal text-muted-foreground">
                                    ({{ event.series_label }})
                                </span>
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                {{ event.starts_at_label }}, {{ event.venue_name }}, {{ event.suburb }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ event.coming_alone }} solo, {{ event.coming_with_friend }} with a mate
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold">{{ event.price_label }}</div>
                        <div class="text-sm text-primary">{{ event.spots_remaining }} left</div>
                    </div>
                </Link>
            </div>
        </template>
    </div>
</template>
