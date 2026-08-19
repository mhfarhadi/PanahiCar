<script setup>
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';
import { BRAND_EN, BRAND_FA } from '@/Utils/brand';
</script>

<template>
    <div dir="rtl" class="ph-guest-shell">
        <div class="absolute left-5 top-5 z-10">
            <ThemeToggle />
        </div>

        <Link :href="route('home')" class="mb-8 text-center">
            <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full text-lg font-black text-white shadow-lg ph-brand-mark">پ</span>
            <p class="text-xl font-black tracking-tight">{{ BRAND_FA }}</p>
            <p class="mt-1 text-sm text-slate-400">{{ BRAND_EN }} · مدیریت نمایشگاه خودرو</p>
        </Link>

        <div class="w-full max-w-[400px]">
            <slot />
        </div>
    </div>
</template>
