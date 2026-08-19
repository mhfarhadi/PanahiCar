<script setup>
import PublicShell from '@/Layouts/PublicShell.vue';
import { pageTitle } from '@/Utils/brand';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head :title="pageTitle('املاک — به‌زودی')" />

    <PublicShell :back-href="route('home')" back-label="صفحه اصلی">
        <div class="ph-coming-soon">
            <div class="ph-coming-soon__badge">به‌زودی</div>
            <div class="ph-coming-soon__icon">
                <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 20V10l8-6 8 6v10M9 20v-6h6v6" />
                </svg>
            </div>
            <h1 class="ph-coming-soon__title">املاک و مستغلات</h1>
            <p class="ph-coming-soon__text">
                این بخش در حال طراحی است و به‌زودی راه‌اندازی می‌شود.
                مدیریت ملک، قرارداد اجاره و پرونده‌های سرمایه‌گذاری از همین درگاه در دسترس قرار می‌گیرد.
            </p>
            <Link :href="route('home')" class="am-btn-primary mt-8 inline-flex">
                بازگشت به انتخاب بخش
            </Link>
        </div>
    </PublicShell>
</template>
