<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        dir="rtl"
        class="flex min-h-screen flex-col items-center justify-center bg-[#f4f4f5] px-5 py-10 text-neutral-900 dark:bg-[#0b0b0c] dark:text-neutral-100"
    >
        <Link href="/" class="mb-8 text-center">
            <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-neutral-900 text-lg font-black text-white">آ</span>
            <p class="text-xl font-black tracking-tight">automaya</p>
            <p class="mt-1 text-sm text-neutral-400">مدیریت نمایشگاه خودرو</p>
        </Link>

        <div class="w-full max-w-[400px]">
            <slot />
        </div>
    </div>
</template>
