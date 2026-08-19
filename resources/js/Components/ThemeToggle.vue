<script setup>
import { useAppearance } from '@/Composables/useAppearance';

const { isDark, toggleTheme } = useAppearance();
</script>

<template>
    <button
        type="button"
        class="ph-theme-switch"
        :class="{ 'is-dark': isDark }"
        :aria-label="isDark ? 'تغییر به حالت روشن' : 'تغییر به حالت تاریک'"
        @click="toggleTheme"
    >
        <span class="ph-theme-switch__track">
            <svg class="ph-theme-switch__icon ph-theme-switch__icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5 8.5 8.5 0 1 0 20.5 14.5Z" />
            </svg>
            <span class="ph-theme-switch__thumb" aria-hidden="true" />
            <svg class="ph-theme-switch__icon ph-theme-switch__icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="12" cy="12" r="4" />
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
            </svg>
        </span>
    </button>
</template>
