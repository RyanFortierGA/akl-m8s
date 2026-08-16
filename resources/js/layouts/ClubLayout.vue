<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="min-h-screen bg-[#071018] text-[#F4F1E8]">
        <header
            class="sticky top-0 z-30 border-b border-white/10 bg-[#071018]/90 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4"
            >
                <Link href="/" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span
                        class="flex size-8 items-center justify-center rounded-md bg-[#D8FF2E] font-black text-[#071018]"
                    >M8</span>
                    Auckland M8s
                </Link>
                <nav class="flex items-center gap-3 text-sm">
                    <Link href="/events" class="text-white/70 hover:text-white">Events</Link>
                    <Link
                        v-if="user"
                        href="/dashboard"
                        class="rounded-full bg-[#D8FF2E] px-4 py-2 font-semibold text-[#071018]"
                    >
                        My nights
                    </Link>
                    <template v-else>
                        <Link href="/login" class="text-white/70 hover:text-white">Log in</Link>
                        <Link
                            href="/register"
                            class="rounded-full bg-[#D8FF2E] px-4 py-2 font-semibold text-[#071018]"
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
