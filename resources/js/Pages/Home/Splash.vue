<script setup>
import PublicShell from '@/Layouts/PublicShell.vue';
import { publicAsset } from '@/Utils/publicAsset';
import { pageTitle } from '@/Utils/brand';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head :title="pageTitle('Panahi Holding')" />

    <PublicShell minimal>
        <div class="ph-splash-hub">
            <div class="ph-splash-hub__pattern" :style="{ backgroundImage: `url('${publicAsset('/images/splash-pattern.svg')}')` }" aria-hidden="true" />

            <span class="ph-splash-blob ph-splash-blob--1" aria-hidden="true" />
            <span class="ph-splash-blob ph-splash-blob--2" aria-hidden="true" />
            <span class="ph-splash-ring ph-splash-ring--1" aria-hidden="true" />
            <span class="ph-splash-ring ph-splash-ring--2" aria-hidden="true" />
            <span class="ph-splash-dot ph-splash-dot--1" aria-hidden="true" />
            <span class="ph-splash-dot ph-splash-dot--2" aria-hidden="true" />

            <div class="ph-splash-hub__intro">
                <h1 class="ph-holding-brand" dir="ltr" aria-label="Panahi Holding">
                    <span class="ph-holding-brand__word">
                        <span class="ph-holding-brand__letter" :style="{ '--i': 0 }">P</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 1 }">A</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 2 }">N</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 3 }">A</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 4 }">H</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 5 }">I</span>
                    </span>
                    <span class="ph-holding-brand__gap" aria-hidden="true" />
                    <span class="ph-holding-brand__word">
                        <span class="ph-holding-brand__letter" :style="{ '--i': 7 }">H</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 8 }">O</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 9 }">L</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 10 }">D</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 11 }">I</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 12 }">N</span>
                        <span class="ph-holding-brand__letter" :style="{ '--i': 13 }">G</span>
                    </span>
                </h1>
            </div>

            <div class="ph-splash-hub__grid">
                <Link :href="route('cars.landing')" class="ph-splash-card">
                    <span class="ph-splash-card__icon ph-splash-card__icon--cars">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 16h14l-1.4-5H6.4L5 16Zm3-8h8M7.5 16.5a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Zm9 0a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z" />
                        </svg>
                    </span>
                    <h2 class="ph-splash-card__title">خودرو</h2>
                    <p class="ph-splash-card__desc">لیزینگ و مدیریت نمایشگاه</p>
                    <span class="ph-splash-card__btn">ورود</span>
                </Link>

                <Link :href="route('real-estate')" class="ph-splash-card ph-splash-card--muted">
                    <span class="ph-splash-card__soon">به‌زودی</span>
                    <span class="ph-splash-card__icon ph-splash-card__icon--estate">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 20V10l8-6 8 6v10M9 20v-6h6v6" />
                        </svg>
                    </span>
                    <h2 class="ph-splash-card__title">املاک و مستغلات</h2>
                    <p class="ph-splash-card__desc">در حال طراحی</p>
                    <span class="ph-splash-card__btn ph-splash-card__btn--outline">اطلاعات</span>
                </Link>
            </div>
        </div>
    </PublicShell>
</template>
