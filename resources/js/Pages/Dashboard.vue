<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { vehiclePhoto, illustration } from '@/Utils/carPhotos';
import { BRAND_FA, pageTitle } from '@/Utils/brand';

const props = defineProps({
    inventoryCount: { type: Number, default: 0 },
    salesThisMonth: { type: Number, default: 0 },
    receivables: {
        type: Object,
        default: () => ({
            total_count: 0,
            total_amount: 0,
            overdue_count: 0,
            overdue_amount: 0,
            due_soon_count: 0,
            due_soon_amount: 0,
        }),
    },
    upcomingInstallments: { type: Array, default: () => [] },
    featuredVehicles: { type: Array, default: () => [] },
    currencyRates: {
        type: Object,
        default: () => ({ usd: null, aed: null, stale: true }),
    },
});

const page = usePage();
const firstName = computed(() => (page.props.auth?.user?.name || 'کاربر').split(' ')[0]);
const hero = computed(() => props.featuredVehicles[0] || null);

const money = (value) => `${Number(value || 0).toLocaleString('fa-IR')} تومان`;

const today = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
    weekday: 'long',
    day: 'numeric',
    month: 'short',
}).format(new Date());

const usdTiny = computed(() => {
    const value = props.currencyRates?.usd?.value;
    if (!value) return null;
    return Number(value).toLocaleString('fa-IR');
});

const featureCards = [
    { title: 'اقساط', route: 'features.installments.index', photo: illustration('installments') },
    { title: 'برآورد', route: 'features.price-estimates.index', photo: illustration('estimate') },
    { title: 'قرارداد', route: 'features.contracts.index', photo: illustration('contract') },
    { title: 'بازار', route: 'features.wanted-market.index', photo: illustration('market') },
];
</script>

<template>
    <Head :title="pageTitle('خانه')" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-2">
            <div class="am-page-inner-narrow">
                <div class="am-card mb-4">
                    <div class="mb-5 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400">سلام، {{ firstName }}</p>
                            <h1 class="mt-1 text-[28px] font-black tracking-tight">{{ BRAND_FA }}</h1>
                            <p class="mt-1 text-xs text-slate-400">نمایشگاه خودرو</p>
                        </div>
                        <div class="text-left text-[10px] font-medium text-slate-400">
                            <div>{{ today }}</div>
                            <div v-if="usdTiny">دلار {{ usdTiny }}</div>
                        </div>
                    </div>

                    <p class="text-[34px] font-black leading-none tracking-tight">
                        {{ inventoryCount.toLocaleString('fa-IR') }}
                        <span class="text-base font-bold text-slate-400">خودرو</span>
                    </p>

                    <div class="mt-5 grid grid-cols-3 gap-3 text-center text-[11px] text-slate-500">
                        <div>
                            <p class="mb-1 text-slate-400">نمایشگاه</p>
                            <p class="font-bold text-slate-800">{{ BRAND_FA }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-slate-400">فروش ماه</p>
                            <p class="font-bold text-slate-800">{{ salesThisMonth.toLocaleString('fa-IR') }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-slate-400">وضعیت</p>
                            <p class="font-bold text-emerald-600">فعال</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4 flex gap-2.5">
                    <Link :href="route('devices.index')" class="ph-stat-pill ph-stat-pill--mint am-lift">
                        <span class="mb-3 text-2xl">✓</span>
                        <span class="ph-stat-pill__label">موجودی</span>
                        <span class="mt-2 text-lg font-black">{{ inventoryCount.toLocaleString('fa-IR') }}</span>
                    </Link>
                    <Link :href="route('sales.index')" class="ph-stat-pill ph-stat-pill--sun am-lift">
                        <span class="mb-3 text-2xl">◉</span>
                        <span class="ph-stat-pill__label">فروش</span>
                        <span class="mt-2 text-lg font-black">{{ salesThisMonth.toLocaleString('fa-IR') }}</span>
                    </Link>
                    <div class="ph-stat-wide">
                        <div class="ph-stat-wide__stripes" />
                        <div class="relative z-[1] flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400">اقساط باز</p>
                                <p class="mt-1 text-2xl font-black">{{ receivables.total_count.toLocaleString('fa-IR') }}</p>
                            </div>
                            <span class="text-2xl">⏳</span>
                        </div>
                        <div class="relative z-[1] mt-3">
                            <p class="text-[11px] font-semibold text-slate-500">مانده</p>
                            <p class="text-sm font-black">{{ money(receivables.total_amount) }}</p>
                            <Link :href="route('installments.index')" class="am-btn-primary mt-3 !px-4 !py-2 text-xs">
                                مشاهده اقساط
                            </Link>
                        </div>
                    </div>
                </div>

                <Link
                    v-if="hero"
                    :href="route('devices.show', hero.id)"
                    class="am-lift relative mb-4 block overflow-hidden rounded-[32px]"
                >
                    <img :src="vehiclePhoto(hero, 0)" :alt="`${hero.brand} ${hero.model}`" class="am-photo h-56 w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/15 to-transparent" />
                    <div class="absolute bottom-4 right-4 left-4 text-white">
                        <p class="text-[11px] font-medium text-white/70">خودرو ویژه</p>
                        <h2 class="mt-1 text-xl font-black">{{ hero.brand }} {{ hero.model }}</h2>
                        <p class="mt-1 text-xs text-white/80">
                            {{ hero.model_year }} · {{ Number(hero.mileage || 0).toLocaleString('fa-IR') }} km · {{ hero.color }}
                        </p>
                        <p v-if="hero.suggested_sale_price" class="mt-2 text-sm font-black">
                            {{ money(hero.suggested_sale_price) }}
                        </p>
                    </div>
                </Link>

                <Link
                    v-else
                    :href="route('features.index')"
                    class="relative mb-4 block overflow-hidden rounded-[32px] am-lift"
                >
                    <img :src="illustration('showroom')" :alt="BRAND_FA" class="am-photo h-56 w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                    <div class="absolute bottom-4 right-4 left-4 text-white">
                        <p class="text-[11px] font-medium text-white/70">شروع کار</p>
                        <h2 class="mt-1 text-xl font-black">نمایشگاه را بسازید</h2>
                        <p class="mt-1 text-xs text-white/80">ابزارهای اقساط، قرارداد و برآورد قیمت آماده‌اند.</p>
                    </div>
                </Link>

                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-black">موجودی نمایشگاه</h3>
                    <Link :href="route('devices.index')" class="text-[11px] font-semibold text-slate-400">همه</Link>
                </div>

                <div class="-mx-1 mb-6 flex gap-3 overflow-x-auto px-1 pb-2">
                    <Link
                        v-for="(car, index) in featuredVehicles"
                        :key="car.id"
                        :href="route('devices.show', car.id)"
                        class="w-[168px] shrink-0 overflow-hidden rounded-[28px] ph-metric-card am-lift"
                    >
                        <img :src="vehiclePhoto(car, index)" class="h-28 w-full object-cover" />
                        <div class="p-3">
                            <p class="truncate text-[13px] font-black">{{ car.brand }} {{ car.model }}</p>
                            <p class="mt-1 text-[10px] text-slate-400">{{ car.model_year }} · {{ car.color }}</p>
                            <p class="mt-2 text-xs font-bold">
                                {{ car.suggested_sale_price ? money(car.suggested_sale_price) : '—' }}
                            </p>
                        </div>
                    </Link>
                </div>

                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-black">امکانات</h3>
                    <Link :href="route('features.index')" class="text-[11px] font-semibold text-slate-400">همه ابزارها</Link>
                </div>

                <div class="mb-6 grid grid-cols-2 gap-3">
                    <Link
                        v-for="card in featureCards"
                        :key="card.title"
                        :href="route(card.route)"
                        class="am-lift relative h-28 overflow-hidden rounded-[28px]"
                    >
                        <img :src="card.photo" class="h-full w-full object-cover" />
                        <div class="absolute inset-0 bg-black/35" />
                        <p class="absolute bottom-3 right-3 text-sm font-black text-white">{{ card.title }}</p>
                    </Link>
                </div>

                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-black">سررسید اقساط</h3>
                    <Link :href="route('installments.index')" class="text-[11px] font-semibold text-slate-400">همه</Link>
                </div>

                <div class="space-y-2">
                    <Link
                        v-for="(item, index) in upcomingInstallments.slice(0, 3)"
                        :key="item.id"
                        :href="route('sales.show', item.sale_id)"
                        class="ph-activity-card am-lift"
                        :class="index % 3 === 0 ? 'ph-activity-card--lavender' : index % 3 === 1 ? 'ph-activity-card--sun' : 'ph-activity-card--mint'"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/80 text-lg">📅</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold">{{ item.buyer_name }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">{{ item.brand }} {{ item.model }}</p>
                        </div>
                        <p class="text-xs font-black">{{ money(item.remaining_amount) }}</p>
                    </Link>
                    <p v-if="!upcomingInstallments.length" class="py-6 text-center text-sm text-slate-400">
                        قسط بازی برای نمایش نیست.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
