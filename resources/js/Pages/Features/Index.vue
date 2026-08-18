<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import { illustration } from '@/Utils/carPhotos';
import { formatCount } from '@/Utils/featuresForm';

const page = usePage();
const loggedIn = computed(() => Boolean(page.props.auth?.user));

const tools = [
    {
        title: 'ماشین‌حساب اقساط',
        description: 'برنامه چک‌های ماهانه خودرو',
        route: 'features.installments.index',
        photo: illustration('installments'),
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
    },
];
</script>

<template>
    <Head title="امکانات | automaya" />

    <FeaturesLayout title="امکانات" subtitle="ابزارهای نمایشگاه" home>
        <div class="relative mb-5 overflow-hidden rounded-[28px]">
            <img :src="illustration('showroom')" alt="اتوگالری مایا" class="am-photo h-48 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent" />
            <div class="absolute bottom-4 right-4 left-4 text-white">
                <p class="text-[11px] font-medium text-white/70">ابزارهای عمومی automaya</p>
                <h1 class="mt-1 text-2xl font-black">امکانات نمایشگاه</h1>
                <p class="mt-1 text-xs text-white/80">بدون ورود هم قابل استفاده‌اند.</p>
            </div>
        </div>

        <div class="mb-5 grid grid-cols-3 gap-2.5">
            <div class="rounded-[22px] bg-white p-3 shadow-[0_10px_24px_rgba(0,0,0,0.04)] dark:bg-[#161618]">
                <p class="text-[10px] text-neutral-400">ابزار</p>
                <p class="mt-1 text-lg font-black">{{ formatCount(tools.length) }}</p>
            </div>
            <div class="rounded-[22px] bg-white p-3 shadow-[0_10px_24px_rgba(0,0,0,0.04)] dark:bg-[#161618]">
                <p class="text-[10px] text-neutral-400">دسترسی</p>
                <p class="mt-1 text-sm font-black">عمومی</p>
            </div>
            <Link
                :href="loggedIn ? route('dashboard') : route('login')"
                class="rounded-[22px] bg-neutral-900 p-3 text-white"
            >
                <p class="text-[10px] text-white/60">{{ loggedIn ? 'خانه' : 'ورود' }}</p>
                <p class="mt-1 text-sm font-black">{{ loggedIn ? 'داشبورد' : 'نمایشگاه' }}</p>
            </Link>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <Link
                v-for="tool in tools"
                :key="tool.route"
                :href="route(tool.route)"
                class="am-lift relative h-36 overflow-hidden rounded-[24px]"
                :class="tool.route === 'features.check-printer.index' ? 'col-span-2 h-40' : ''"
            >
                <img :src="tool.photo" :alt="tool.title" class="h-full w-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent" />
                <div class="absolute bottom-3 right-3 left-3 text-white">
                    <p class="text-sm font-black">{{ tool.title }}</p>
                    <p class="mt-0.5 text-[11px] text-white/75">{{ tool.description }}</p>
                </div>
            </Link>
        </div>
    </FeaturesLayout>
</template>
