<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

type Person = {
    id: number;
    name: string;
    suburb: string | null;
    connected: boolean;
    events_attended: number;
};

const props = defineProps<{
    event: { title: string; emoji: string; community: { slug: string }; slug: string };
    people: Person[];
    review: { rating: number; would_hang_again: boolean } | null;
}>();

const selected = ref<number[]>(
    props.people.filter((person) => person.connected).map((person) => person.id),
);
const rating = ref(props.review?.rating ?? 5);

function toggle(id: number) {
    selected.value = selected.value.includes(id)
        ? selected.value.filter((value) => value !== id)
        : [...selected.value, id];
}

function save() {
    router.post(`/c/${props.event.community.slug}/${props.event.slug}/meet`, {
        rating: rating.value,
        would_hang_again: true,
        mate_ids: selected.value,
    });
}
</script>

<template>
    <Head title="Who did you meet?" />
    <div class="mx-auto max-w-3xl p-6">
        <h1 class="text-3xl font-black">Who did you meet?</h1>
        <p class="mt-2 text-muted-foreground">
            {{ event.emoji }} {{ event.title }}. Tick the guys you’d hang out with again.
        </p>

        <div class="mt-6">
            <p class="mb-2 text-sm font-semibold">How was it?</p>
            <div class="flex gap-2">
                <button
                    v-for="star in 5"
                    :key="star"
                    class="text-2xl"
                    type="button"
                    @click="rating = star"
                >
                    {{ star <= rating ? '⭐' : '✩' }}
                </button>
            </div>
        </div>

        <div class="mt-8 space-y-2">
            <button
                v-for="person in people"
                :key="person.id"
                type="button"
                class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left"
                :class="selected.includes(person.id) ? 'border-primary bg-primary/10' : 'bg-card'"
                @click="toggle(person.id)"
            >
                <span>
                    <span class="font-semibold">{{ person.name }}</span>
                    <span class="block text-sm text-muted-foreground">
                        {{ person.suburb || 'Auckland' }} · {{ person.events_attended }} nights
                    </span>
                </span>
                <span class="text-sm text-primary">
                    {{ selected.includes(person.id) ? 'Added' : 'Add as mate' }}
                </span>
            </button>
        </div>

        <div class="mt-8 flex gap-3">
            <Button @click="save">Save mates</Button>
            <Button as-child variant="secondary">
                <Link href="/mates">See your mates</Link>
            </Button>
        </div>
    </div>
</template>
