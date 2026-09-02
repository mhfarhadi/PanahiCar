<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import { illustration } from '@/Utils/carPhotos';
import { formatCount } from '@/Utils/featuresForm';
import { BRAND_FA, pageTitle } from '@/Utils/brand';

const page = usePage();
const loggedIn = computed(() => Boolean(page.props.auth?.user));

const tools = [
    {
        title: 'ماشین‌حساب اقساط',
        description: 'برنامه چک‌های ماهانه خودرو',
        route: 'features.installments.index',
        photo: illustration('installments'),
        badge: 'محبوب',
    },
    {
        title: 'قرارداد فروش',
        description: 'قرارداد چاپی خرید خودرو',
        route: 'features.contracts.index',
        photo: illustration('contract'),
    },
    {
        title: 'برآورد قیمت',
        description: 'بر اساس موجودی و فروش واقعی',
        route: 'features.price-estimates.index',
        photo: illustration('estimate'),
    },
    {
        title: 'ضمانت طلا',
        description: 'وزن طلا برای پوشش اقساط',
        route: 'features.gold-collateral.index',
        photo: illustration('gold'),
    },
    {
        title: 'چی می‌خوام؟',
        description: 'ثبت نیاز خرید خودرو',
        route: 'features.wanted.index',
        photo: illustration('wanted'),
    },
    {
        title: 'چیا می‌خوان؟',
        description: 'تقاضای همکاران نمایشگاه',
        route: 'features.wanted-market.index',
        photo: illustration('market'),
    },
    {
        title: 'پرینتر چک',
        description: 'آماده‌سازی چاپ روی برگه چک',
        route: 'features.check-printer.index',
        photo: illustration('check'),
        wide: true,
    },
];
</script>

<template>
    <Head :title="pageTitle('امکانات')" />

    <FeaturesLayout title="امکانات" subtitle="ابزارهای عمومی نمایشگاه" home>
        <section class="ph-rx-tools-hero">
            <div>
                <p class="ph-rx-kicker">ابزارهای {{ BRAND_FA }}</p>
                <h1 class="ph-rx-tools-hero__title">همه امکانات در یک نگاه</h1>
                <p class="ph-rx-tools-hero__text">بدون ورود هم می‌توانید از ابزارهای عمومی استفاده کنید.</p>
            </div>
            <img :src="illustration('showroom')" :alt="BRAND_FA" class="ph-rx-tools-hero__photo" />
        </section>

        <section class="ph-rx-tools-metrics">
            <article class="ph-rx-metric">
                <p class="ph-rx-metric__value">{{ formatCount(tools.length) }}</p>
                <p class="ph-rx-metric__label">ابزار فعال</p>
                <p class="ph-rx-metric__hint">دسترسی عمومی</p>
            </article>
            <article class="ph-rx-metric ph-rx-metric--accent">
                <p class="ph-rx-metric__value">۸۹٪</p>
                <p class="ph-rx-metric__label">کاربرد روزانه</p>
                <p class="ph-rx-metric__hint">اقساط و برآورد</p>
            </article>
            <Link
                :href="loggedIn ? route('dashboard') : route('login')"
                class="ph-rx-metric ph-rx-metric--link"
            >
                <p class="ph-rx-metric__value">{{ loggedIn ? 'پنل' : 'ورود' }}</p>
                <p class="ph-rx-metric__label">{{ loggedIn ? 'داشبورد' : 'کارکنان' }}</p>
                <p class="ph-rx-metric__hint">مدیریت نمایشگاه</p>
            </Link>
        </section>

        <section class="ph-rx-tools-grid">
            <Link
                v-for="tool in tools"
                :key="tool.route"
                :href="route(tool.route)"
                class="ph-rx-tool-card"
                :class="{ 'ph-rx-tool-card--wide': tool.wide }"
            >
                <img :src="tool.photo" :alt="tool.title" class="ph-rx-tool-card__photo" />
                <div class="ph-rx-tool-card__overlay" />
                <div class="ph-rx-tool-card__body">
                    <span v-if="tool.badge" class="ph-rx-tool-card__badge">{{ tool.badge }}</span>
                    <p class="ph-rx-tool-card__title">{{ tool.title }}</p>
                    <p class="ph-rx-tool-card__desc">{{ tool.description }}</p>
                </div>
            </Link>
        </section>
    </FeaturesLayout>
</template>
