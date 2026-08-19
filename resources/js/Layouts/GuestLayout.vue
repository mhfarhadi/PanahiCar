<script setup>
import { Link } from '@inertiajs/vue3';
import { BRAND_EN, BRAND_FA } from '@/Utils/brand';
import { publicAsset } from '@/Utils/publicAsset';
</script>

<template>
    <div
        dir="rtl"
        class="flex min-h-screen flex-col items-center justify-center px-5 py-10 text-slate-900"
        style="background: linear-gradient(145deg, #dff7ec 0%, #dceefb 48%, #e8f4fd 100%);"
    >
        <Link :href="route('login')" class="mb-8 text-center">
            <span
                class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full text-lg font-black text-white shadow-lg"
                style="background: linear-gradient(135deg, #86efac, #60a5fa);"
            >پ</span>
            <p class="text-xl font-black tracking-tight">{{ BRAND_FA }}</p>
            <p class="mt-1 text-sm text-slate-400">{{ BRAND_EN }} · مدیریت نمایشگاه خودرو</p>
        </Link>

        <div class="w-full max-w-[400px]">
            <slot />
        </div>
    </div>
</template>
