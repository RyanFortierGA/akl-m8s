<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SuburbSelect from '@/components/SuburbSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Interest = { id: number; name: string; emoji: string };

defineProps<{
    interests: Interest[];
    selected: number[];
    profile: { age: number | null; suburb: string | null; instagram: string | null };
}>();
</script>

<template>
    <Head title="What are you into?" />
    <div class="mx-auto max-w-2xl p-6">
        <h1 class="text-3xl font-black">What are you into?</h1>
        <p class="mt-2 text-muted-foreground">
            This is how we match you to nights, and how other guys see you’re not a weirdo in a vacuum.
        </p>
        <Form action="/onboarding" method="put" class="mt-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <Label>Age</Label>
                    <Input name="age" type="number" :default-value="profile.age ?? undefined" min="18" />
                </div>
                <div>
                    <Label>Suburb</Label>
                    <SuburbSelect :default-value="profile.suburb" />
                </div>
                <div>
                    <Label>Instagram</Label>
                    <Input name="instagram" :default-value="profile.instagram ?? ''" placeholder="handle" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                <label
                    v-for="interest in interests"
                    :key="interest.id"
                    class="flex items-center gap-2 rounded-xl border bg-card px-3 py-2"
                >
                    <input
                        type="checkbox"
                        name="interest_ids[]"
                        :value="interest.id"
                        :checked="selected.includes(interest.id)"
                    />
                    <span>{{ interest.emoji }} {{ interest.name }}</span>
                </label>
            </div>
            <Button type="submit">Save and see events</Button>
        </Form>
    </div>
</template>
