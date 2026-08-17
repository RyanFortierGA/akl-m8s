<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="club-grain min-h-screen bg-[#F3ECE3] text-[#1E2C2A]">
        <header class="sticky top-0 z-30 border-b border-[#1E2C2A]/10 bg-[#F3ECE3]/85 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
                <Link href="/" class="flex items-center gap-2.5 tracking-tight">
                    <span
                        class="flex h-8 items-center rounded-sm bg-[#C45C26] px-2 font-serif text-[15px] font-semibold text-[#F6EFE6]"
                    >
                        M8
                    </span>
                    <span class="text-sm font-medium">Auckland M8s</span>
                </Link>
                <nav class="flex items-center gap-4 text-sm">
                    <Link href="/events" class="text-[#1E2C2A]/70 hover:text-[#1E2C2A]">Nights</Link>
                    <Link
                        v-if="user"
                        href="/dashboard"
                        class="rounded-full bg-[#1E2C2A] px-4 py-2 font-medium text-[#F3ECE3]"
                    >
                        My nights
                    </Link>
                    <template v-else>
                        <Link href="/login" class="text-[#1E2C2A]/70 hover:text-[#1E2C2A]">Log in</Link>
                        <Link
                            href="/register"
                            class="rounded-full bg-[#C45C26] px-4 py-2 font-medium text-[#F6EFE6]"
                        >
                            Join
                        </Link>
                    </template>
                </nav>
            </div>
        </header>
        <slot />
    </div>
</template>
