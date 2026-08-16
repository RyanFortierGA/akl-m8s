<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

type EventShow = {
    title: string;
    emoji: string;
    description: string;
    starts_at_label: string;
    venue_name: string;
    venue_address: string;
    suburb: string;
    capacity: number;
    price_label: string;
    spots_remaining: number;
    going: number;
    waitlist: number;
    coming_alone: number;
    coming_with_friend: number;
    newcomers: number;
    is_full: boolean;
    community: { name: string; slug: string };
    slug: string;
};

type Attendee = {
    id: number;
    name: string;
    suburb: string | null;
    coming_alone: boolean;
    events_attended: number;
};

const { event, attendees, rsvp, canChat, canMeet } = defineProps<{
    event: EventShow;
    attendees: Attendee[];
    rsvp: { status: string; confirmed: boolean } | null;
    canChat: boolean;
    canMeet: boolean;
}>();

const user = usePage().props.auth.user;
</script>

<template>
    <Head :title="event.title" />
    <div class="mx-auto grid max-w-5xl gap-8 p-6 lg:grid-cols-[1.4fr_0.8fr]">
        <article>
            <p class="text-sm font-semibold uppercase tracking-widest text-primary">
                {{ event.community.name }}
            </p>
            <h1 class="mt-2 text-4xl font-black">
                {{ event.emoji }} {{ event.title }}
            </h1>
            <p class="mt-3 text-lg text-muted-foreground">{{ event.starts_at_label }}</p>
            <p class="text-muted-foreground">
                {{ event.venue_name }}{{ event.suburb ? ` · ${event.suburb}` : '' }}
            </p>
            <p class="mt-6 max-w-2xl leading-7">{{ event.description }}</p>

            <div class="mt-8 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Coming alone</div>
                    <div class="text-3xl font-black">{{ event.coming_alone }}</div>
                </div>
                <div class="rounded-2xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">With one mate</div>
                    <div class="text-3xl font-black">{{ event.coming_with_friend }}</div>
                </div>
                <div class="rounded-2xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">3 or fewer nights</div>
                    <div class="text-3xl font-black">{{ event.newcomers }}</div>
                </div>
            </div>

            <p class="mt-4 font-semibold text-primary">You won’t be the only person coming alone.</p>

            <div v-if="attendees.length" class="mt-10">
                <h2 class="font-bold">Who’s going</h2>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <div
                        v-for="person in attendees"
                        :key="person.id"
                        class="rounded-xl border bg-card px-4 py-3 text-sm"
                    >
                        <div class="font-semibold">{{ person.name }}</div>
                        <div class="text-muted-foreground">
                            {{ person.suburb || 'Auckland' }} ·
                            {{ person.coming_alone ? 'coming alone' : 'bringing a mate' }} ·
                            {{ person.events_attended }} nights
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <aside class="h-fit rounded-3xl border bg-card p-6">
            <div class="text-3xl font-black">{{ event.price_label }}</div>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ event.going }}/{{ event.capacity }} going · {{ event.spots_remaining }} spots left
                <span v-if="event.waitlist"> · waitlist {{ event.waitlist }}</span>
            </p>

            <div v-if="!user" class="mt-6 space-y-3">
                <Button as-child class="w-full">
                    <Link href="/register">Join to reserve a spot</Link>
                </Button>
                <p class="text-center text-xs text-muted-foreground">
                    Already in? <Link href="/login" class="text-primary">Log in</Link>
                </p>
            </div>
            <div v-else-if="rsvp?.confirmed" class="mt-6 space-y-3">
                <div class="rounded-xl bg-primary/15 p-4 font-semibold text-primary">You’re in.</div>
                <Button v-if="canChat" as-child class="w-full">
                    <Link :href="`/c/${event.community.slug}/${event.slug}/chat`">Event chat</Link>
                </Button>
                <Button v-if="canMeet" as-child variant="secondary" class="w-full">
                    <Link :href="`/c/${event.community.slug}/${event.slug}/meet`">Who did you meet?</Link>
                </Button>
            </div>
            <div v-else-if="rsvp?.status === 'waitlisted'" class="mt-6 rounded-xl bg-secondary p-4">
                You’re on the waitlist.
            </div>
            <Form
                v-else
                :action="`/c/${event.community.slug}/${event.slug}/rsvp`"
                method="post"
                class="mt-6 space-y-3"
            >
                <label class="block text-sm">
                    How are you coming?
                    <select
                        name="party_size"
                        class="mt-2 w-full rounded-md border bg-background px-3 py-2"
                    >
                        <option value="1">Alone — most people are</option>
                        <option value="2">With one mate</option>
                    </select>
                </label>
                <Button type="submit" class="w-full">
                    {{ event.is_full ? 'Join waitlist' : `Reserve your spot · ${event.price_label}` }}
                </Button>
            </Form>
            <p class="mt-4 text-xs text-muted-foreground">
                Free RSVPs flake. A paid spot means you’ll actually turn up.
            </p>
        </aside>
    </div>
</template>
