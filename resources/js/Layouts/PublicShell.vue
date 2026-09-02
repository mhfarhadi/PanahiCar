<script setup>
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { BRAND_EN, BRAND_FA } from '@/Utils/brand';
import { Link } from '@inertiajs/vue3';

defineProps({
    backHref: { type: String, default: null },
    backLabel: { type: String, default: 'بازگشت' },
    showBrand: { type: Boolean, default: true },
});
</script>

<template>
    <div dir="rtl" class="ph-public-shell">
        <header class="ph-public-shell__header">
            <div class="ph-public-shell__start">
                <Link v-if="showBrand" :href="route('home')" class="ph-rx-brand">
                    <span class="ph-rx-brand__mark" aria-hidden="true">
                        <span /><span /><span /><span />
                    </span>
                    <span class="ph-rx-brand__text">
                        <span class="ph-rx-brand__fa">{{ BRAND_FA }}</span>
                        <span class="ph-rx-brand__en">{{ BRAND_EN }}</span>
                    </span>
                </Link>
                <Link v-else-if="backHref" :href="backHref" class="ph-public-back">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M11 6l-6 6 6 6" />
                    </svg>
                    {{ backLabel }}
                </Link>
            </div>

            <div class="ph-public-shell__end">
                <Link
                    v-if="backHref && showBrand"
                    :href="backHref"
                    class="ph-rx-ghost-btn hidden sm:inline-flex"
                >
                    {{ backLabel }}
                </Link>
                <ThemeToggle />
            </div>
        </header>

        <main class="ph-public-shell__main">
            <slot />
        </main>
    </div>
</template>
