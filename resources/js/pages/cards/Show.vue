<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    person: {
        name: string;
        suburb: string | null;
        instagram: string | null;
        phone: string | null;
        bio: string | null;
        events_attended: number;
    };
    qr: string;
    vcardUrl: string;
}>();
</script>

<template>
    <Head :title="`${person.name} · M8s card`" />
    <div class="mx-auto max-w-md px-4 py-16 text-center">
        <p class="text-sm tracking-[0.25em] text-[#D8FF2E] uppercase">Auckland M8s</p>
        <h1 class="mt-4 text-4xl font-black">{{ person.name }}</h1>
        <p class="mt-2 text-white/60">
            {{ person.suburb || 'Auckland' }} · {{ person.events_attended }} nights
        </p>
        <p v-if="person.bio" class="mt-4 text-white/70">{{ person.bio }}</p>
        <div class="mx-auto mt-8 w-60 rounded-3xl bg-white p-4" v-html="qr" />
        <div class="mt-6 space-y-1 text-sm">
            <p v-if="person.instagram">Instagram @{{ person.instagram }}</p>
            <p v-if="person.phone">{{ person.phone }}</p>
        </div>
        <Button as-child class="mt-8">
            <a :href="vcardUrl">Add to contacts</a>
        </Button>
    </div>
</template>
