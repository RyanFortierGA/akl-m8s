<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type EventCard = {
    id: number;
    title: string;
    emoji: string;
    starts_at_label: string;
    venue_name: string;
    suburb: string;
    capacity: number;
    price_label: string;
    spots_remaining: number;
    going: number;
    coming_alone: number;
    newcomers: number;
    url: string;
};

defineProps<{ events: EventCard[] }>();
</script>

<template>
    <Head title="Upcoming nights" />
    <div class="mx-auto max-w-5xl p-6">
        <h1 class="text-3xl font-black">Upcoming nights</h1>
        <p class="mt-2 text-muted-foreground">Show up. Meet people. Come back.</p>
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
                        <h2 class="font-bold">{{ event.title }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ event.starts_at_label }} · {{ event.venue_name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ event.coming_alone }} coming alone · {{ event.newcomers }} newer members
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold">{{ event.price_label }}</div>
                    <div class="text-sm text-primary">{{ event.spots_remaining }} left</div>
                </div>
            </Link>
        </div>
    </div>
</template>
