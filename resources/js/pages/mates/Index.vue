<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

type Mate = {
    id: number;
    name: string;
    suburb: string | null;
    bio: string | null;
    instagram: string | null;
    phone: string | null;
    contact_shared: boolean;
    card_url: string;
    share_url: string;
    met_at: string | null;
    met_at_emoji: string | null;
    met_on: string | null;
    mutual_events: number;
};

defineProps<{
    mates: Mate[];
    myCardUrl: string;
}>();
</script>

<template>
    <Head title="Mates" />
    <div class="mx-auto max-w-4xl p-6">
        <div class="flex items-end justify-between">
            <div>
                <h1 class="text-3xl font-black">Mates</h1>
                <p class="text-muted-foreground">People you actually met in real life.</p>
            </div>
            <Button as-child variant="secondary">
                <Link :href="myCardUrl">My contact card</Link>
            </Button>
        </div>

        <div v-if="!mates.length" class="mt-8 rounded-2xl border bg-card p-8 text-muted-foreground">
            After an event, you’ll get a “who did you meet?” screen. That’s how this list fills up.
        </div>

        <div class="mt-8 space-y-4">
            <article v-for="mate in mates" :key="mate.id" class="rounded-2xl border bg-card p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold">{{ mate.name }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ mate.suburb }} · {{ mate.mutual_events }} mutual events
                        </p>
                        <p v-if="mate.met_at" class="mt-2 text-sm">
                            Met at: {{ mate.met_at_emoji }} {{ mate.met_at }} · {{ mate.met_on }}
                        </p>
                        <p v-if="mate.contact_shared" class="mt-3 text-sm">
                            <span v-if="mate.instagram">IG @{{ mate.instagram }}</span>
                            <span v-if="mate.phone"> · {{ mate.phone }}</span>
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Form v-if="!mate.contact_shared" :action="mate.share_url" method="post">
                            <Button type="submit" size="sm">Share contact</Button>
                        </Form>
                        <Button as-child size="sm" variant="secondary">
                            <Link :href="mate.card_url">QR / card</Link>
                        </Button>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>
