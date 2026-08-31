<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name?: string;
        defaultValue?: string | null;
        required?: boolean;
    }>(),
    {
        name: 'suburb',
        defaultValue: '',
        required: false,
    },
);

const suburbs = computed(() => (usePage().props.aucklandSuburbs as string[]) ?? []);
</script>

<template>
    <select
        :name="name"
        :required="required"
        class="mt-1 w-full rounded-md border bg-background px-3 py-2"
    >
        <option value="" :selected="!defaultValue">Pick a suburb</option>
        <option
            v-for="suburb in suburbs"
            :key="suburb"
            :value="suburb"
            :selected="suburb === defaultValue"
        >
            {{ suburb }}
        </option>
    </select>
</template>
