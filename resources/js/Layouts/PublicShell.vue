<script setup>
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    backHref: { type: String, default: null },
    backLabel: { type: String, default: 'بازگشت' },
    minimal: { type: Boolean, default: false },
});
</script>

<template>
    <div dir="rtl" class="ph-public-shell" :class="{ 'ph-public-shell--splash': minimal }">
        <header class="ph-public-shell__header">
            <div class="min-w-0 flex-1">
                <Link v-if="backHref" :href="backHref" class="ph-public-back">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M11 6l-6 6 6 6" />
                    </svg>
                    {{ backLabel }}
                </Link>
            </div>
            <ThemeToggle />
        </header>

        <main class="ph-public-shell__main">
            <slot />
        </main>
    </div>
</template>
