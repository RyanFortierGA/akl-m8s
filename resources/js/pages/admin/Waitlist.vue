<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import AdminNav from '@/components/AdminNav.vue';
import { Input } from '@/components/ui/input';

type Entry = {
    id: number;
    name: string;
    email: string;
    suburb: string | null;
    age: number | null;
    instagram: string | null;
    interests_count: number;
    profile_complete: boolean;
    signed_up_at: string;
    signed_up_label: string | null;
};

const props = defineProps<{
    q: string;
    prelaunch: boolean;
    launchLabel: string;
    stats: {
        total: number;
        profile_complete: number;
        this_month: number;
        suburbs: number;
    };
    entries: Entry[];
    suburbs: { suburb: string; count: number }[];
}>();

const search = ref(props.q);
let timer: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (timer) {
        clearTimeout(timer);
    }
    timer = setTimeout(() => {
        router.get('/admin/waitlist', { q: value || undefined }, {
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
    <Head title="Waitlist" />
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold tracking-widest text-primary uppercase">Admin</p>
                <h1 class="text-3xl font-black">Waitlist</h1>
                <p class="text-muted-foreground">
                    Real signups for Auckland M8s.
                    <span v-if="prelaunch">Launching in {{ launchLabel }}.</span>
                </p>
            </div>
            <AdminNav active="waitlist" />
        </div>

        <div class="grid gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">On the waitlist</div>
                <div class="text-2xl font-black">{{ stats.total }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Profile done</div>
                <div class="text-2xl font-black">{{ stats.profile_complete }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">This month</div>
                <div class="text-2xl font-black">{{ stats.this_month }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Suburbs</div>
                <div class="text-2xl font-black">{{ stats.suburbs }}</div>
            </div>
        </div>

        <Input v-model="search" placeholder="Search name, email, suburb…" class="max-w-md" />

        <section v-if="suburbs.length" class="flex flex-wrap gap-2">
            <span
                v-for="row in suburbs.slice(0, 12)"
                :key="row.suburb"
                class="rounded-full border bg-card px-3 py-1 text-xs"
            >
                {{ row.suburb }} · {{ row.count }}
            </span>
        </section>

        <div class="overflow-hidden rounded-2xl border">
            <div
                v-for="entry in entries"
                :key="entry.id"
                class="grid gap-2 border-b bg-card px-4 py-3 last:border-b-0 md:grid-cols-[1.4fr_1fr_auto] md:items-center"
            >
                <div>
                    <div class="font-semibold">{{ entry.name }}</div>
                    <div class="text-sm text-muted-foreground">
                        {{ entry.email }}
                        <span v-if="entry.suburb"> · {{ entry.suburb }}</span>
                        <span v-if="entry.age"> · {{ entry.age }}</span>
                    </div>
                    <div v-if="entry.instagram" class="text-xs text-muted-foreground">@{{ entry.instagram }}</div>
                </div>
                <div class="text-sm">
                    <div v-if="entry.profile_complete" class="text-emerald-600">Profile complete</div>
                    <div v-else class="text-muted-foreground">Signed up, profile not finished</div>
                    <div class="text-muted-foreground">
                        <span v-if="entry.interests_count">{{ entry.interests_count }} interests · </span>
                        {{ entry.signed_up_label }}
                    </div>
                </div>
                <div class="text-xs text-muted-foreground">
                    {{ entry.signed_up_at }}
                </div>
            </div>
            <div v-if="!entries.length" class="p-6 text-sm text-muted-foreground">
                No signups match that search.
            </div>
        </div>

        <p class="text-sm text-muted-foreground">
            Demo accounts are excluded. Event waitlists live on each night.
            <Link href="/admin" class="text-primary">Back to overview →</Link>
        </p>
    </div>
</template>
