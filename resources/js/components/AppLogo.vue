<script setup lang="ts">
const name = 'Auckland M8s';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-sm font-black text-primary-foreground"
    >
        M8
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold">{{ name }}</span>
    </div>
</template>
