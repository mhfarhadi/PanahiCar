<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { mediaUrl } from '@/Utils/carPhotos';

const props = defineProps({
    sales: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            count: 0,
            total_sale_amount: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const saleType = ref(props.filters.sale_type || 'all');
const period = ref(props.filters.period || 'all');

let searchTimer = null;

watch([search, saleType, period], () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('sales.index'),
            {
                search: search.value || undefined,
                sale_type: saleType.value !== 'all' ? saleType.value : undefined,
                period: period.value !== 'all' ? period.value : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300);
});

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const formatDate = (value) => {
    if (!value) return '—';

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(`${value}T00:00:00`));
};

const saleTypeLabel = (type) =>
    type === 'installment' ? 'اقساطی' : 'نقدی';
</script>

<template>
    <Head title="خودرو‌های فروخته‌شده | automaya" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5">
                    <h1 class="text-xl font-black">فروش‌ها</h1>
                    <p class="mt-1 text-[11px] font-bold text-slate-400">
                        سوابق فروش و سود هر خودرو
                    </p>
                </div>

                <div class="mb-4">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="برند، مدل، VIN یا خریدار..."
                        autocomplete="off"
                        class="am-input"
                    />
                </div>

                <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
                    <button
                        v-for="option in [
                            { value: 'all', label: 'همه' },
                            { value: 'cash', label: 'نقدی' },
                            { value: 'installment', label: 'اقساطی' },
                        ]"
                        :key="option.value"
                        type="button"
                        class="am-chip shrink-0"
                        :class="saleType === option.value ? 'am-chip-on' : ''"
                        @click="saleType = option.value"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <select
                    v-model="period"
                    class="am-input mb-4"
                >
                    <option value="all">همه زمان‌ها</option>
                    <option value="last_7_days">۷ روز گذشته</option>
                    <option value="current_month">ماه جاری شمسی</option>
                    <option value="previous_month">ماه گذشته شمسی</option>
                </select>

                <div class="mb-5 grid grid-cols-2 gap-3">
                    <div class="am-card !p-4">
                        <p class="text-[10px] font-bold text-slate-400">تعداد</p>
                        <p class="mt-1 text-lg font-black">
                            {{ Number(summary.count || 0).toLocaleString('fa-IR') }}
                        </p>
                    </div>

                    <div class="am-card !p-4">
                        <p class="text-[10px] font-bold text-slate-400">مجموع فروش</p>

                        <p class="mt-1 text-lg font-black">
                            {{ formatMoney(summary.total_sale_amount) }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="sales.length === 0"
                    class="am-soft py-12 text-center"
                >
                    <p class="text-sm font-black">هنوز فروشی ثبت نشده</p>
                </div>

                <div v-else class="space-y-3">
                    <Link
                        v-for="sale in sales"
                        :key="sale.id"
                        :href="route('sales.show', sale.id)"
                        class="am-row"
                    >
                        <div class="am-thumb">
                            <img
                                :src="mediaUrl(sale.cover_image, sale.id)"
                                :alt="`${sale.brand} ${sale.model}`"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-black">
                                {{ sale.brand }} {{ sale.model }}
                            </h2>
                            <p class="mt-0.5 truncate text-[11px] text-slate-400">
                                {{ sale.buyer_name }} · {{ formatDate(sale.sale_date) }}
                            </p>
                            <p class="mt-0.5 text-[10px] font-bold text-slate-400">
                                {{ saleTypeLabel(sale.sale_type) }}
                            </p>
                        </div>

                        <div class="shrink-0 text-left">
                            <p class="text-xs font-black">
                                {{ formatMoney(sale.sale_price) }}
                            </p>
                            <p
                                v-if="sale.profit !== null"
                                class="mt-1 text-[10px] font-bold"
                                :class="sale.profit >= 0 ? 'text-emerald-600' : 'text-red-500'"
                            >
                                {{ sale.profit >= 0 ? '+' : '' }}{{ formatMoney(sale.profit) }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
