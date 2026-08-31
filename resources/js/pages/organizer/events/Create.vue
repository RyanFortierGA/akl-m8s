<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    communities: { id: number; name: string }[];
}>();
</script>

<template>
    <Head title="Create event" />
    <div class="mx-auto max-w-xl p-6">
        <h1 class="text-3xl font-black">Create a night</h1>
        <Form action="/organizer/events" method="post" class="mt-8 space-y-4">
            <div>
                <Label>Community</Label>
                <select name="community_id" class="mt-1 w-full rounded-md border bg-background px-3 py-2">
                    <option v-for="community in communities" :key="community.id" :value="community.id">
                        {{ community.name }}
                    </option>
                </select>
            </div>
            <div class="grid grid-cols-4 gap-3">
                <div>
                    <Label>Emoji</Label>
                    <Input name="emoji" default-value="⚽" />
                </div>
                <div class="col-span-3">
                    <Label>Title</Label>
                    <Input name="title" required placeholder="Friday Football" />
                </div>
            </div>
            <div>
                <Label>Description</Label>
                <textarea
                    name="description"
                    class="mt-1 min-h-28 w-full rounded-md border bg-background px-3 py-2"
                    placeholder="Flying solo is normal here. Most people are."
                />
            </div>
            <div>
                <Label>Starts</Label>
                <Input name="starts_at" type="datetime-local" required />
            </div>
            <div>
                <Label>Venue</Label>
                <Input name="venue_name" required placeholder="Mt Eden 5-a-side" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <Label>Address</Label>
                    <Input name="venue_address" />
                </div>
                <div>
                    <Label>Suburb</Label>
                    <Input name="suburb" placeholder="Mt Eden" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <Label>Capacity</Label>
                    <Input name="capacity" type="number" default-value="20" required />
                </div>
                <div>
                    <Label>Price NZD</Label>
                    <Input name="price" type="number" step="1" default-value="15" required />
                </div>
            </div>
            <Button type="submit" class="w-full">Publish event</Button>
        </Form>
    </div>
</template>
