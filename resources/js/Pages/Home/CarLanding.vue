<script setup>
import PublicShell from '@/Layouts/PublicShell.vue';
import { illustration } from '@/Utils/carPhotos';
import { BRAND_FA, pageTitle } from '@/Utils/brand';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const loggedIn = computed(() => Boolean(page.props.auth?.user));

const highlights = [
    { title: 'لیزینگ خودرو', text: 'طرح‌های اقساطی شفاف برای نمایشگاه و مشتری' },
    { title: 'مدیریت موجودی', text: 'ثبت، پیگیری و فروش خودرو در یک پنل واحد' },
    { title: 'امکانات عمومی', text: 'ماشین‌حساب اقساط، قرارداد و برآورد قیمت' },
];
</script>

<template>
    <Head :title="pageTitle('لیزینگ خودرو')" />

    <PublicShell :back-href="route('home')" back-label="همه بخش‌ها">
        <div class="ph-car-landing">
            <header class="ph-car-landing__topbar">
                <div class="flex items-center gap-2">
                    <span class="ph-brand-mark flex h-10 w-10 items-center justify-center rounded-full text-sm font-black text-white">پ</span>
                    <div>
                        <p class="text-[11px] font-bold text-slate-400">بخش خودرو</p>
                        <p class="text-sm font-black">{{ BRAND_FA }}</p>
                    </div>
                </div>
                <Link
                    :href="loggedIn ? route('dashboard') : route('login')"
                    class="ph-car-landing__login"
                >
                    {{ loggedIn ? 'داشبورد' : 'ورود به سیستم' }}
                </Link>
            </header>

            <section class="ph-car-landing__hero">
                <div class="ph-car-landing__hero-copy">
                    <p class="ph-car-landing__eyebrow">لیزینگ و مدیریت نمایشگاه</p>
                    <h1 class="ph-car-landing__headline">
                        مسیر ساده‌تر برای
                        <span class="ph-car-landing__accent">خرید و فروش اقساطی خودرو</span>
                    </h1>
                    <p class="ph-car-landing__lede">
                        پناهی؛ پلتفرم مینیمال برای نمایشگاه‌ها — از موجودی و فروش تا اقساط و قرارداد،
                        با تمرکز بر سرعت و شفافیت مالی.
                    </p>
                    <div class="ph-car-landing__actions">
                        <Link :href="route('features.index')" class="am-btn-primary">
                            امکانات عمومی
                        </Link>
                        <Link
                            v-if="!loggedIn"
                            :href="route('login')"
                            class="am-btn-secondary"
                        >
                            ورود کارکنان
                        </Link>
                    </div>
                </div>

                <div class="ph-car-landing__hero-visual">
                    <img
                        :src="illustration('showroom')"
                        alt="نمایشگاه خودرو"
                        class="ph-car-landing__photo"
                    />
                    <div class="ph-car-landing__stat">
                        <p class="text-[10px] font-bold text-slate-400">تمرکز</p>
                        <p class="mt-1 text-lg font-black">اقساط · موجودی · فروش</p>
                    </div>
                </div>
            </section>

            <section class="ph-car-landing__highlights">
                <article
                    v-for="item in highlights"
                    :key="item.title"
                    class="ph-car-landing__highlight"
                >
                    <h2 class="text-sm font-black">{{ item.title }}</h2>
                    <p class="mt-2 text-[12px] leading-6 text-slate-500 dark:text-slate-400">{{ item.text }}</p>
                </article>
            </section>

            <footer class="ph-car-landing__footer">
                <p class="text-[11px] text-slate-400">
                    ابزارهای عمومی بدون ورود · پنل مدیریت پس از ورود کارکنان
                </p>
            </footer>
        </div>
    </PublicShell>
</template>
