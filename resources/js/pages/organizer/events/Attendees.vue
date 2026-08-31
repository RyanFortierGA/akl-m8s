<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

type Attendee = {
    id: number;
    name: string;
    email: string;
    suburb: string | null;
    status: string;
    events_attended: number;
    coming_alone: boolean;
};

defineProps<{
    event: { title: string; emoji: string; community: { slug: string }; slug: string };
    attendees: Attendee[];
}>();

function setStatus(id: number, status: string, community: string, slug: string) {
    router.post(`/c/${community}/${slug}/attendees`, { rsvp_id: id, status });
}
</script>

<template>
    <Head :title="`${event.title} attendees`" />
    <div class="mx-auto max-w-4xl p-6">
        <Link href="/organizer" class="text-sm text-primary">Back to organizer</Link>
        <h1 class="mt-3 text-3xl font-black">{{ event.emoji }} {{ event.title }}</h1>
        <p class="text-muted-foreground">Who actually comes back is the whole product.</p>
        <div class="mt-6 overflow-hidden rounded-2xl border">
            <div
                v-for="person in attendees"
                :key="person.id"
                class="flex items-center justify-between border-b bg-card px-4 py-3 last:border-b-0"
            >
                <div>
                    <div class="font-semibold">{{ person.name }}</div>
                    <div class="text-sm text-muted-foreground">
                        {{ person.suburb }} · {{ person.events_attended }} events ·
                        {{ person.coming_alone ? 'solo' : 'with a mate' }}
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="capitalize">{{ person.status.replace('_', ' ') }}</span>
                    <button class="text-primary" @click="setStatus(person.id, 'attended', event.community.slug, event.slug)">
                        Attended
                    </button>
                    <button class="text-muted-foreground" @click="setStatus(person.id, 'no_show', event.community.slug, event.slug)">
                        No-show
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
