<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

type Upcoming = {
    id: number;
    title: string;
    emoji: string;
    starts_at_label: string;
    going: number;
    capacity: number;
    revenue_cents: number;
    attendees_url: string;
};

defineProps<{
    communities: { name: string; city: string; member_count: number }[];
    stats: {
        members: number;
        ticket_sales_label: string;
        connections: number;
        active_this_month: number;
    };
    upcoming: Upcoming[];
}>();
</script>

<template>
    <Head title="Organizer" />
    <div class="mx-auto max-w-5xl space-y-8 p-6">
        <div class="flex items-end justify-between">
            <div>
                <h1 class="text-3xl font-black">{{ communities[0]?.name || 'Organizer' }}</h1>
                <p class="text-muted-foreground">Community health, not just tickets.</p>
            </div>
            <div class="flex gap-2">
                <Button as-child variant="secondary">
                    <Link href="/organizer/communities/new">New community</Link>
                </Button>
                <Button as-child>
                    <Link href="/organizer/events/new">New event</Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Members</div>
                <div class="text-3xl font-black">{{ stats.members }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Active this month</div>
                <div class="text-3xl font-black">{{ stats.active_this_month }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Ticket sales</div>
                <div class="text-3xl font-black">{{ stats.ticket_sales_label }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Connections made</div>
                <div class="text-3xl font-black">{{ stats.connections }}</div>
            </div>
        </div>

        <section>
            <h2 class="mb-3 font-bold">Upcoming</h2>
            <div class="overflow-hidden rounded-2xl border">
                <div
                    v-for="event in upcoming"
                    :key="event.id"
                    class="flex items-center justify-between border-b bg-card px-4 py-3 last:border-b-0"
                >
                    <div>
                        <div class="font-semibold">{{ event.emoji }} {{ event.title }}</div>
                        <div class="text-sm text-muted-foreground">{{ event.starts_at_label }}</div>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <span>{{ event.going }}/{{ event.capacity }}</span>
                        <span>${{ Math.round(event.revenue_cents / 100) }}</span>
                        <Link :href="event.attendees_url" class="text-primary">Attendees</Link>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
