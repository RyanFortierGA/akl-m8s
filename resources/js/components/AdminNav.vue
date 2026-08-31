<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    active: 'dashboard' | 'planner' | 'people' | 'waitlist';
}>();

const links = [
    { key: 'dashboard', href: '/admin', label: 'Overview' },
    { key: 'waitlist', href: '/admin/waitlist', label: 'Waitlist' },
    { key: 'planner', href: '/admin/planner', label: 'Planner' },
    { key: 'people', href: '/admin/people', label: 'People' },
] as const;
</script>

<template>
    <nav class="flex flex-wrap gap-2 text-sm">
        <Link
            v-for="link in links"
            :key="link.key"
            :href="link.href"
            class="rounded-full px-3 py-1.5"
            :class="
                props.active === link.key
                    ? 'bg-primary text-primary-foreground'
                    : 'border bg-card text-muted-foreground hover:text-foreground'
            "
        >
            {{ link.label }}
        </Link>
        <Link
            href="/admin/events/new"
            class="rounded-full border border-dashed px-3 py-1.5 text-muted-foreground hover:text-foreground"
        >
            + New night
        </Link>
    </nav>
</template>
