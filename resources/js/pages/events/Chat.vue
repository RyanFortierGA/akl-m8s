<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Message = {
    id: number;
    body: string;
    name: string;
    mine: boolean;
    created_at: string;
};

defineProps<{
    event: { title: string; emoji: string; community: { slug: string }; slug: string };
    messages: Message[];
}>();
</script>

<template>
    <Head :title="`${event.title} chat`" />
    <div class="mx-auto flex max-w-3xl flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-black">{{ event.emoji }} {{ event.title }}</h1>
            <Link :href="`/c/${event.community.slug}/${event.slug}`" class="text-sm text-primary">
                Back to event
            </Link>
        </div>
        <div class="min-h-[420px] space-y-3 rounded-2xl border bg-card p-4">
            <div
                v-for="message in messages"
                :key="message.id"
                class="max-w-[80%] rounded-2xl px-4 py-2"
                :class="message.mine ? 'ml-auto bg-primary text-primary-foreground' : 'bg-secondary'"
            >
                <div class="text-xs opacity-70">{{ message.name }} · {{ message.created_at }}</div>
                <div>{{ message.body }}</div>
            </div>
            <p v-if="!messages.length" class="text-sm text-muted-foreground">
                First message. Ask who wants to grab food after.
            </p>
        </div>
        <Form
            :action="`/c/${event.community.slug}/${event.slug}/chat`"
            method="post"
            reset-on-success
            class="flex gap-2"
        >
            <Input name="body" placeholder="Message the group..." required />
            <Button type="submit">Send</Button>
        </Form>
    </div>
</template>
