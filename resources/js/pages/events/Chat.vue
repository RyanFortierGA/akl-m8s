<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
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
    <div class="mx-auto flex min-h-[calc(100dvh-2rem)] max-w-2xl flex-col gap-6 p-4 sm:p-6">
        <div class="flex shrink-0 items-center justify-between gap-4">
            <div>
                <p class="text-sm text-muted-foreground">Event chat</p>
                <h1 class="text-2xl font-black">{{ event.emoji }} {{ event.title }}</h1>
            </div>
            <Link :href="`/c/${event.community.slug}/${event.slug}`" class="shrink-0 text-sm text-primary">
                Back to event
            </Link>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border bg-card shadow-sm">
            <div class="flex-1 space-y-4 overflow-y-auto p-4 sm:p-5">
                <p v-if="!messages.length" class="py-12 text-center text-sm text-muted-foreground">
                    No messages yet. Ask who wants food after.
                </p>
                <div
                    v-for="message in messages"
                    :key="message.id"
                    class="flex gap-2"
                    :class="message.mine ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[min(85%,20rem)] rounded-2xl px-4 py-2.5 shadow-sm"
                        :class="message.mine ? 'bg-primary text-primary-foreground' : 'bg-muted'"
                    >
                        <div
                            class="mb-1 text-xs font-semibold tracking-wide uppercase"
                            :class="message.mine ? 'text-primary-foreground/75' : 'text-muted-foreground'"
                        >
                            {{ message.name }}
                        </div>
                        <div class="text-[15px] leading-relaxed whitespace-pre-wrap">{{ message.body }}</div>
                        <div
                            class="mt-1.5 text-[11px] tabular-nums"
                            :class="message.mine ? 'text-primary-foreground/60' : 'text-muted-foreground'"
                        >
                            {{ message.created_at }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="shrink-0 border-t bg-background p-4 sm:p-5">
                <Form
                    :action="`/c/${event.community.slug}/${event.slug}/chat`"
                    method="post"
                    reset-on-success
                    class="flex gap-2"
                >
                    <Input name="body" placeholder="Message the group..." required class="min-h-11" />
                    <Button type="submit" class="shrink-0">Send</Button>
                </Form>
            </div>
        </div>
    </div>
</template>
