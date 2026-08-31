<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import AdminNav from '@/components/AdminNav.vue';
import { Input } from '@/components/ui/input';

type Person = {
    id: number;
    name: string;
    email: string;
    suburb: string | null;
    age: number | null;
    instagram: string | null;
    nights_confirmed: number;
    nights_attended: number;
    nights_waitlisted: number;
    role: string;
    joined_at: string | null;
    last_night: string | null;
    last_night_emoji: string | null;
};

const props = defineProps<{
    q: string;
    stats: {
        members: number;
        repeat: number;
        never_attended: number;
        suburbs: number;
    };
    people: Person[];
    suburbs: { suburb: string; count: number }[];
}>();

const search = ref(props.q);
let timer: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (timer) {
        clearTimeout(timer);
    }
    timer = setTimeout(() => {
        router.get('/admin/people', { q: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 250);
});

onBeforeUnmount(() => {
    if (timer) {
        clearTimeout(timer);
    }
});
</script>

<template>
    <Head title="People" />
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold tracking-widest text-primary uppercase">Admin</p>
                <h1 class="text-3xl font-black">People</h1>
                <p class="text-muted-foreground">Everyone in Auckland M8s. Who shows up, who comes back.</p>
            </div>
            <AdminNav active="people" />
        </div>

        <div class="grid gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Members</div>
                <div class="text-2xl font-black">{{ stats.members }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Repeat show-ups</div>
                <div class="text-2xl font-black">{{ stats.repeat }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Not booked yet</div>
                <div class="text-2xl font-black">{{ stats.never_attended }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Suburbs</div>
                <div class="text-2xl font-black">{{ stats.suburbs }}</div>
            </div>
        </div>

        <Input v-model="search" placeholder="Search name, email, suburb…" class="max-w-md" />

        <section v-if="suburbs.length" class="flex flex-wrap gap-2">
            <span
                v-for="row in suburbs.slice(0, 10)"
                :key="row.suburb"
                class="rounded-full border bg-card px-3 py-1 text-xs"
            >
                {{ row.suburb }} · {{ row.count }}
            </span>
        </section>

        <div class="overflow-hidden rounded-2xl border">
            <div
                v-for="person in people"
                :key="person.id"
                class="grid gap-2 border-b bg-card px-4 py-3 last:border-b-0 md:grid-cols-[1.4fr_1fr_auto] md:items-center"
            >
                <div>
                    <div class="font-semibold">
                        {{ person.name }}
                        <span v-if="person.role === 'organizer'" class="text-xs text-primary">admin</span>
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ person.email }}
                        <span v-if="person.suburb"> · {{ person.suburb }}</span>
                        <span v-if="person.age"> · {{ person.age }}</span>
                    </div>
                    <div v-if="person.instagram" class="text-xs text-muted-foreground">@{{ person.instagram }}</div>
                </div>
                <div class="text-sm">
                    <div>{{ person.nights_confirmed }} booked · {{ person.nights_attended }} attended</div>
                    <div class="text-muted-foreground">
                        <span v-if="person.nights_waitlisted">Waitlist {{ person.nights_waitlisted }} · </span>
                        <span v-if="person.last_night">{{ person.last_night_emoji }} {{ person.last_night }}</span>
                        <span v-else>No nights yet</span>
                    </div>
                </div>
                <div class="text-xs text-muted-foreground">
                    Joined {{ person.joined_at || 'unknown' }}
                </div>
            </div>
            <div v-if="!people.length" class="p-6 text-sm text-muted-foreground">
                No members match that search.
            </div>
        </div>

        <p class="text-sm text-muted-foreground">
            Per-night lists live on each event.
            <Link href="/admin" class="text-primary">Back to overview →</Link>
        </p>
    </div>
</template>
