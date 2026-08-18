<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { vehiclePhoto, illustration } from '@/Utils/carPhotos';

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
    <Head title="خانه | automaya" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-2">
            <div class="am-page-inner-narrow !max-w-md">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-neutral-400">سلام، {{ firstName }}</p>
                        <h1 class="text-xl font-black tracking-tight">اتوگالری مایا</h1>
                    </div>
                    <div class="text-left text-[10px] font-medium text-neutral-400">
                        <div>{{ today }}</div>
                        <div v-if="usdTiny">دلار {{ usdTiny }}</div>
                    </div>
                </div>

                <Link
                    v-if="hero"
                    :href="route('devices.show', hero.id)"
                    class="am-lift relative mb-4 block overflow-hidden rounded-[28px]"
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
                    class="relative mb-4 block overflow-hidden rounded-[28px] am-lift"
                >
                    <img :src="illustration('showroom')" alt="اتوگالری مایا" class="am-photo h-56 w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                    <div class="absolute bottom-4 right-4 left-4 text-white">
                        <p class="text-[11px] font-medium text-white/70">شروع کار</p>
                        <h2 class="mt-1 text-xl font-black">نمایشگاه را بسازید</h2>
                        <p class="mt-1 text-xs text-white/80">ابزارهای اقساط، قرارداد و برآورد قیمت آماده‌اند.</p>
                    </div>
                </Link>

                <div class="mb-5 grid grid-cols-3 gap-2.5">
                    <Link :href="route('devices.index')" class="rounded-[22px] bg-white p-3 shadow-[0_10px_24px_rgba(0,0,0,0.04)] dark:bg-[#161618]">
                        <p class="text-[10px] text-neutral-400">موجودی</p>
                        <p class="mt-1 text-lg font-black">{{ inventoryCount.toLocaleString('fa-IR') }}</p>
                    </Link>
                    <Link :href="route('sales.index')" class="rounded-[22px] bg-white p-3 shadow-[0_10px_24px_rgba(0,0,0,0.04)] dark:bg-[#161618]">
                        <p class="text-[10px] text-neutral-400">فروش ماه</p>
                        <p class="mt-1 text-lg font-black">{{ salesThisMonth.toLocaleString('fa-IR') }}</p>
                    </Link>
                    <Link :href="route('installments.index')" class="rounded-[22px] bg-white p-3 shadow-[0_10px_24px_rgba(0,0,0,0.04)] dark:bg-[#161618]">
                        <p class="text-[10px] text-neutral-400">اقساط باز</p>
                        <p class="mt-1 text-lg font-black">{{ receivables.total_count.toLocaleString('fa-IR') }}</p>
                    </Link>
                </div>

                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-black">موجودی نمایشگاه</h3>
                    <Link :href="route('devices.index')" class="text-[11px] font-semibold text-neutral-400">همه</Link>
                </div>

                <div class="-mx-1 mb-6 flex gap-3 overflow-x-auto px-1 pb-2">
                    <Link
                        v-for="(car, index) in featuredVehicles"
                        :key="car.id"
                        :href="route('devices.show', car.id)"
                        class="w-[168px] shrink-0 overflow-hidden rounded-[24px] bg-white shadow-[0_12px_30px_rgba(0,0,0,0.05)] dark:bg-[#161618]"
                    >
                        <img :src="vehiclePhoto(car, index)" class="h-28 w-full object-cover" />
                        <div class="p-3">
                            <p class="truncate text-[13px] font-black">{{ car.brand }} {{ car.model }}</p>
                            <p class="mt-1 text-[10px] text-neutral-400">{{ car.model_year }} · {{ car.color }}</p>
                            <p class="mt-2 text-xs font-bold">
                                {{ car.suggested_sale_price ? money(car.suggested_sale_price) : '—' }}
                            </p>
                        </div>
                    </Link>
                </div>

                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-black">امکانات</h3>
                    <Link :href="route('features.index')" class="text-[11px] font-semibold text-neutral-400">همه ابزارها</Link>
                </div>

                <div class="mb-6 grid grid-cols-2 gap-3">
                    <Link
                        v-for="card in featureCards"
                        :key="card.title"
                        :href="route(card.route)"
                        class="am-lift relative h-28 overflow-hidden rounded-[24px]"
                    >
                        <img :src="card.photo" class="h-full w-full object-cover" />
                        <div class="absolute inset-0 bg-black/35" />
                        <p class="absolute bottom-3 right-3 text-sm font-black text-white">{{ card.title }}</p>
                    </Link>
                </div>

                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-black">سررسید اقساط</h3>
                    <Link :href="route('installments.index')" class="text-[11px] font-semibold text-neutral-400">همه</Link>
                </div>

                <div class="space-y-2">
                    <Link
                        v-for="item in upcomingInstallments.slice(0, 3)"
                        :key="item.id"
                        :href="route('sales.show', item.sale_id)"
                        class="flex items-center justify-between rounded-[22px] bg-white px-4 py-3 shadow-[0_10px_24px_rgba(0,0,0,0.04)] dark:bg-[#161618]"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold">{{ item.buyer_name }}</p>
                            <p class="mt-0.5 text-[11px] text-neutral-400">{{ item.brand }} {{ item.model }}</p>
                        </div>
                        <p class="text-xs font-black">{{ money(item.remaining_amount) }}</p>
                    </Link>
                    <p v-if="!upcomingInstallments.length" class="py-6 text-center text-sm text-neutral-400">
                        قسط بازی برای نمایش نیست.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
