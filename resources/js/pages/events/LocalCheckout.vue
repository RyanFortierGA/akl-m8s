<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    event: {
        title: string;
        emoji: string;
        price_label: string;
        community_slug: string;
        slug: string;
    };
}>();
</script>

<template>
    <Head title="Confirm your spot" />
    <div class="mx-auto max-w-lg p-8">
        <h1 class="text-3xl font-black">Confirm {{ event.emoji }} {{ event.title }}</h1>
        <p class="mt-3 text-muted-foreground">
            Stripe keys are not set yet, so this is the local checkout. Add
            <code>STRIPE_SECRET</code> later and this becomes a real card payment.
        </p>
        <p class="mt-4 text-2xl font-black">{{ event.price_label }}</p>
        <Form
            :action="`/c/${event.community_slug}/${event.slug}/checkout`"
            method="post"
            class="mt-6"
        >
            <Button type="submit" class="w-full">Pay and confirm spot</Button>
        </Form>
        <Link
            :href="`/c/${event.community_slug}/${event.slug}`"
            class="mt-4 inline-block text-sm text-muted-foreground"
        >
            Back
        </Link>
    </div>
</template>
