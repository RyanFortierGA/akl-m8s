<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
</script>

<template>
    <Head title="Start a community" />
    <div class="mx-auto max-w-xl p-6">
        <h1 class="text-3xl font-black">Become an organizer</h1>
        <p class="mt-2 text-muted-foreground">
            Run Wellington M8s, Hamilton Golf, or whatever your city needs. The platform takes 10% of tickets.
        </p>
        <Form action="/organizer/communities" method="post" class="mt-8 space-y-4">
            <div>
                <Label>Name</Label>
                <Input name="name" required placeholder="Wellington M8s" />
            </div>
            <div>
                <Label>City</Label>
                <Input name="city" required placeholder="Wellington" />
            </div>
            <div>
                <Label>Tagline</Label>
                <Input name="tagline" placeholder="Meet people. Do stuff. Make mates." />
            </div>
            <div>
                <Label>Description</Label>
                <textarea
                    name="description"
                    class="mt-1 min-h-28 w-full rounded-md border bg-background px-3 py-2"
                />
            </div>
            <Button type="submit" class="w-full">Create community</Button>
        </Form>
    </div>
</template>
