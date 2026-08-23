<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminNav from '@/components/AdminNav.vue';
import { Button } from '@/components/ui/button';

type DayEvent = {
    id: number;
    title: string;
    emoji: string;
    time_label: string;
    suburb: string | null;
    signups: number;
    capacity: number;
    waitlist: number;
    price_label: string;
    profit_label: string;
    is_profitable: boolean;
    break_even_tickets: number | null;
    url: string;
    edit_url: string;
};

type Day = {
    date: string;
    day: number;
    in_month: boolean;
    is_today: boolean;
    events: DayEvent[];
};

const props = defineProps<{
    month: string;
    month_label: string;
    prev_month: string;
    next_month: string;
    days: Day[];
    stats: {
        nights: number;
        signups: number;
        revenue_label: string;
        cost_label: string;
        profit_label: string;
        is_profitable: boolean;
    };
}>();

function go(month: string) {
    router.get('/admin/planner', { month }, { preserveState: true });
}
</script>

<template>
    <Head :title="`Planner · ${month_label}`" />
    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold tracking-widest text-primary uppercase">Admin</p>
                <h1 class="text-3xl font-black">Planner</h1>
                <p class="text-muted-foreground">Map the month, watch fill and break-even as you go.</p>
            </div>
            <AdminNav active="planner" />
        </div>

        <div class="grid gap-3 sm:grid-cols-5">
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Nights</div>
                <div class="text-2xl font-black">{{ stats.nights }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Signups</div>
                <div class="text-2xl font-black">{{ stats.signups }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Revenue</div>
                <div class="text-2xl font-black">{{ stats.revenue_label }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Costs</div>
                <div class="text-2xl font-black">{{ stats.cost_label }}</div>
            </div>
            <div class="rounded-2xl border bg-card p-4">
                <div class="text-sm text-muted-foreground">Profit</div>
                <div class="text-2xl font-black" :class="stats.is_profitable ? 'text-emerald-600' : 'text-red-600'">
                    {{ stats.profit_label }}
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <Button variant="secondary" @click="go(prev_month)">← Prev</Button>
            <h2 class="text-xl font-bold">{{ month_label }}</h2>
            <Button variant="secondary" @click="go(next_month)">Next →</Button>
        </div>

        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-2xl border bg-border text-sm">
            <div
                v-for="label in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']"
                :key="label"
                class="bg-muted px-2 py-2 text-center text-xs font-semibold text-muted-foreground"
            >
                {{ label }}
            </div>
            <div
                v-for="day in days"
                :key="day.date"
                class="min-h-28 bg-card p-2"
                :class="[
                    day.in_month ? '' : 'opacity-40',
                    day.is_today ? 'ring-2 ring-inset ring-primary' : '',
                ]"
            >
                <div class="mb-1 text-xs font-semibold">{{ day.day }}</div>
                <div class="space-y-1">
                    <Link
                        v-for="event in day.events"
                        :key="event.id"
                        :href="event.url"
                        class="block rounded-lg border px-1.5 py-1 hover:border-primary"
                    >
                        <div class="truncate text-xs font-semibold">
                            {{ event.emoji }} {{ event.title }}
                        </div>
                        <div class="text-[10px] text-muted-foreground">
                            {{ event.time_label }} · {{ event.signups }}/{{ event.capacity }}
                        </div>
                        <div
                            class="text-[10px]"
                            :class="event.is_profitable ? 'text-emerald-600' : 'text-red-600'"
                        >
                            {{ event.profit_label }}
                            <span v-if="event.break_even_tickets !== null" class="text-muted-foreground">
                                · BE {{ event.break_even_tickets }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <p class="text-sm text-muted-foreground">
            Tip: put venue / host / other costs on each event to unlock break-even and profit.
            <Link href="/admin/events/new" class="text-primary">Add a night →</Link>
        </p>
    </div>
</template>
