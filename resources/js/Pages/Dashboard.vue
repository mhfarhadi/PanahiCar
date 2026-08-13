<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    inventoryCount: {
        type: Number,
        default: 0,
    },
    salesThisMonth: {
        type: Number,
        default: 0,
    },
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
    upcomingInstallments: {
        type: Array,
        default: () => [],
    },
    currencyRates: {
        type: Object,
        default: () => ({
            usd: null,
            aed: null,
            stale: true,
        }),
    },
});


const money = (value) =>
    `${Number(value || 0).toLocaleString('fa-IR')} تومان`;

const persianDate = (value) => {
    if (!value) return '—';

    const date = new Date(`${value}T00:00:00`);

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

const formatCurrencyRate = (rate) => {
    if (!rate?.value) return '—';

    return `${Number(rate.value).toLocaleString('fa-IR')} تومان`;
};

const formatCurrencyChange = (rate) => {
    if (!rate || rate.change === null || rate.change === undefined) return '';

    const change = Number(rate.change);

    if (!Number.isFinite(change) || change === 0) return 'بدون تغییر';

    const sign = change > 0 ? '+' : '';

    return `${sign}${change.toLocaleString('fa-IR')} تومان`;
};

const now = new Date();

const weekday = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
    weekday: 'long',
}).format(now);

const date = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
}).format(now);

const today = `${weekday} | ${date}`;

const actions = [
    {
        title: 'ثبت دستگاه',
        description: 'ثبت گوشی جدید در موجودی',
        icon: '＋',
        href: route('devices.create'),
    },
    {
        title: 'برآورد قیمت',
        description: 'بررسی حدود قیمت گوشی در بازار',
        icon: '⌕',
        href: route('price-estimates.index'),
    },
    {
        title: 'فروش گوشی',
        description: 'ثبت فروش نقدی یا اقساطی',
        icon: '✓',
        href: route('devices.index', { mode: 'sell' }),
    },
    {
        title: 'گوشی‌های فروخته‌شده',
        description: 'مشاهده سوابق فروش و مشتریان',
        icon: '◫',
        href: route('sales.index'),
    },
    {
        title: 'گوشی‌های اعلامی',
        description: 'لیست گوشی‌های معرفی‌شده توسط همکاران',
        icon: '☎',
        href: route('announced-devices.index'),
    },
    {
        title: 'تنظیمات',
        description: 'اشخاص و تنظیمات ظاهری برنامه',
        icon: '⚙',
        href: route('settings.index'),
    },
];

const stats = computed(() => [
    {
        label: 'موجودی فعلی',
        value: `${props.inventoryCount.toLocaleString('fa-IR')} دستگاه`,
        href: route('devices.index'),
    },
    {
        label: 'فروش این ماه',
        value: `${props.salesThisMonth.toLocaleString('fa-IR')} دستگاه`,
        href: route('sales.index'),
    },
    {
        label: 'مطالبات اقساطی',
        value: money(props.receivables.total_amount),
        href: route('sales.index'),
    },
]);
</script>

<template>
    <Head title="داشبورد مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">

                <!-- Top -->
                <div
                    class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-600 text-xl font-black text-white shadow-lg shadow-violet-200 dark:shadow-none"
                            >
                                م
                            </div>

                            <div>
                                <h1 class="text-2xl font-black tracking-tight">
                                    مایاهمراه
                                </h1>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    مدیریت خرید و فروش گوشی‌های کارکرده
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="text-right lg:text-left">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            امروز
                        </p>
                        <p class="mt-1 font-bold">
                            {{ today }}
                        </p>
                    </div>
                </div>

                <!-- Currency -->
                <div class="mb-6 grid gap-4 sm:grid-cols-2">
                    <div
                        class="rounded-3xl border border-white bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    قیمت دلار
                                </p>
                                <p class="mt-2 text-2xl font-black">
                                    {{ formatCurrencyRate(props.currencyRates.usd) }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-xl font-black text-emerald-600 dark:bg-emerald-950"
                            >
                                $
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                            <span
                                :class="
                                    Number(props.currencyRates.usd?.change || 0) >= 0
                                        ? 'text-emerald-600'
                                        : 'text-red-600'
                                "
                            >
                                {{ formatCurrencyChange(props.currencyRates.usd) }}
                            </span>

                            <span class="text-slate-400">
                                {{ props.currencyRates.stale ? 'آخرین نرخ ذخیره‌شده' : 'نرخ بازار' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-white bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    قیمت درهم
                                </p>
                                <p class="mt-2 text-2xl font-black">
                                    {{ formatCurrencyRate(props.currencyRates.aed) }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-lg font-black text-sky-600 dark:bg-sky-950"
                            >
                                AED
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                            <span
                                :class="
                                    Number(props.currencyRates.aed?.change || 0) >= 0
                                        ? 'text-emerald-600'
                                        : 'text-red-600'
                                "
                            >
                                {{ formatCurrencyChange(props.currencyRates.aed) }}
                            </span>

                            <span class="text-slate-400">
                                {{ props.currencyRates.stale ? 'آخرین نرخ ذخیره‌شده' : 'نرخ بازار' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mb-6 grid gap-4 sm:grid-cols-3">
                    <div
                        v-for="item in stats"
                        :key="item.label"
                        class="rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-900"
                    >
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ item.label }}
                        </p>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="text-xl font-black">
                                {{ item.value }}
                            </p>

                            <Link
                                v-if="item.href"
                                :href="item.href"
                                class="rounded-xl bg-violet-600 px-3 py-2 text-xs font-black text-white transition hover:bg-violet-700"
                            >
                                نمایش
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Receivables -->
                <section
                    class="mb-6 rounded-[32px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                >
                    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-black">وضعیت چک‌ها و مطالبات</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                فقط چک‌های وصول‌نشده در این بخش محاسبه می‌شوند.
                            </p>
                        </div>

                        <div class="text-xs font-bold text-slate-400">
                            {{ receivables.total_count.toLocaleString('fa-IR') }} چک باز
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div
                            class="rounded-2xl border border-red-100 bg-red-50 p-4 dark:border-red-950 dark:bg-red-950/30"
                        >
                            <p class="text-xs font-bold text-red-600">معوق</p>
                            <p class="mt-2 text-lg font-black">
                                {{ money(receivables.overdue_amount) }}
                            </p>
                            <p class="mt-1 text-xs text-red-500">
                                {{ receivables.overdue_count.toLocaleString('fa-IR') }} چک
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-amber-100 bg-amber-50 p-4 dark:border-amber-950 dark:bg-amber-950/30"
                        >
                            <p class="text-xs font-bold text-amber-700">سررسید ۷ روز آینده</p>
                            <p class="mt-2 text-lg font-black">
                                {{ money(receivables.due_soon_amount) }}
                            </p>
                            <p class="mt-1 text-xs text-amber-600">
                                {{ receivables.due_soon_count.toLocaleString('fa-IR') }} چک
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-violet-100 bg-violet-50 p-4 dark:border-violet-950 dark:bg-violet-950/30"
                        >
                            <p class="text-xs font-bold text-violet-700">کل مطالبات باز</p>
                            <p class="mt-2 text-lg font-black">
                                {{ money(receivables.total_amount) }}
                            </p>
                            <p class="mt-1 text-xs text-violet-600">
                                {{ receivables.total_count.toLocaleString('fa-IR') }} چک
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-black">نزدیک‌ترین سررسیدها</h3>

                            <Link
                                :href="route('sales.index')"
                                class="text-xs font-black text-violet-600"
                            >
                                مشاهده فروش‌ها
                            </Link>
                        </div>

                        <div
                            v-if="upcomingInstallments.length"
                            class="space-y-2"
                        >
                            <Link
                                v-for="item in upcomingInstallments"
                                :key="item.id"
                                :href="route('sales.show', item.sale_id)"
                                class="flex flex-col gap-3 rounded-2xl border border-slate-100 p-4 transition hover:border-violet-200 hover:bg-violet-50/50 dark:border-slate-800 dark:hover:border-violet-900 dark:hover:bg-violet-950/20 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-black">
                                            {{ item.buyer_name }}
                                        </span>

                                        <span
                                            v-if="item.is_overdue"
                                            class="rounded-lg bg-red-100 px-2 py-1 text-[11px] font-black text-red-600 dark:bg-red-950"
                                        >
                                            معوق
                                        </span>
                                    </div>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ item.brand }} {{ item.model }}
                                        · قسط {{ item.installment_number.toLocaleString('fa-IR') }}
                                        · سررسید {{ persianDate(item.due_date) }}
                                    </p>
                                </div>

                                <div class="font-black">
                                    {{ money(item.remaining_amount) }}
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-500 dark:bg-slate-950 dark:text-slate-400"
                        >
                            هیچ چک وصول‌نشده‌ای وجود ندارد.
                        </div>
                    </div>
                </section>

                <!-- Main actions -->
                <div
                    class="rounded-[32px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black">
                                دسترسی سریع
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                بخش موردنظر را انتخاب کنید
                            </p>
                        </div>

                        <div
                            class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700 dark:bg-violet-950 dark:text-violet-300"
                        >
                            نسخه اولیه
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <component
                            v-for="action in actions"
                            :key="action.title"
                            :is="action.href ? Link : 'button'"
                            :href="action.href"
                            type="button"
                            class="group rounded-3xl border border-slate-100 bg-slate-50 p-5 text-right transition duration-200 hover:-translate-y-1 hover:border-violet-200 hover:bg-violet-50 hover:shadow-lg dark:border-slate-800 dark:bg-slate-950 dark:hover:border-violet-800 dark:hover:bg-violet-950/40"
                        >
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl font-black shadow-sm transition group-hover:bg-violet-600 group-hover:text-white dark:bg-slate-900"
                            >
                                {{ action.icon }}
                            </div>

                            <h3 class="text-base font-black">
                                {{ action.title }}
                            </h3>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ action.description }}
                            </p>
                        </component>
                    </div>
                </div>

                <!-- Bottom -->
                <div
                    class="mt-6 rounded-3xl border border-violet-100 bg-gradient-to-l from-violet-50 to-white p-5 dark:border-violet-950 dark:from-violet-950/40 dark:to-slate-900"
                >
                    <p class="font-black">
                        مایاهمراه آماده شروع است.
                    </p>
                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        موجودی، فروش و مطالبات اقساطی اکنون با داده‌های واقعی سیستم محاسبه می‌شوند.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
