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
        description: 'افزودن گوشی جدید',
        href: route('devices.create'),
        icon: 'M12 5v14M5 12h14',
        tone: 'bg-[#fff6de] text-[#c88918] dark:bg-amber-950/30 dark:text-amber-300',
    },
    {
        title: 'برآورد قیمت',
        description: 'ارزیابی قیمت گوشی',
        href: route('price-estimates.index'),
        icon: 'M11 18a7 7 0 100-14 7 7 0 000 14Zm5 0-4-4',
        tone: 'bg-[#eaf8ef] text-[#4a9d68] dark:bg-emerald-950/30 dark:text-emerald-300',
    },
    {
        title: 'فروش گوشی',
        description: 'نقدی یا اقساطی',
        href: route('devices.index', { mode: 'sell' }),
        icon: 'M4 12h14m-5-5 5 5-5 5',
        tone: 'bg-[#fff0f1] text-[#e6636d] dark:bg-rose-950/30 dark:text-rose-300',
    },
    {
        title: 'فروش‌ها',
        description: 'سوابق و مشتریان',
        href: route('sales.index'),
        icon: 'M5 12l4 4L19 6',
        tone: 'bg-[#edf4ff] text-[#5f83c5] dark:bg-sky-950/30 dark:text-sky-300',
    },
    {
        title: 'گوشی‌های اعلامی',
        description: 'اعلام همکاران',
        href: route('announced-devices.index'),
        icon: 'M12 3l8 9-8 9-8-9 8-9Z',
        tone: 'bg-[#f3edff] text-[#8468bb] dark:bg-violet-950/30 dark:text-violet-300',
    },
    {
        title: 'تنظیمات',
        description: 'اشخاص و ظاهر برنامه',
        href: route('settings.index'),
        icon: 'M12 8a4 4 0 100 8 4 4 0 000-8Zm0-5v2m0 14v2M3 12h2m14 0h2',
        tone: 'bg-[#f0f2f5] text-slate-500 dark:bg-white/5 dark:text-slate-300',
    },
];

const stats = computed(() => [
    {
        label: 'موجودی فعلی',
        value: `${props.inventoryCount.toLocaleString('fa-IR')} دستگاه`,
        href: route('devices.index'),
        note: 'گوشی آماده فروش',
        card: 'bg-[#fff8e8] dark:bg-[#2b2415]',
        accent: 'text-[#c78a22] dark:text-amber-300',
        ring: 'conic-gradient(#f1b84b 0deg 265deg, rgba(241,184,75,.20) 265deg 360deg)',
    },
    {
        label: 'فروش این ماه',
        value: `${props.salesThisMonth.toLocaleString('fa-IR')} دستگاه`,
        href: route('sales.index'),
        note: 'عملکرد ماه جاری',
        card: 'bg-[#fff0f2] dark:bg-[#2a191e]',
        accent: 'text-[#e46270] dark:text-rose-300',
        ring: 'conic-gradient(#ef6c78 0deg 210deg, rgba(239,108,120,.18) 210deg 360deg)',
    },
    {
        label: 'مطالبات اقساطی',
        value: money(props.receivables.total_amount),
        href: route('installments.index'),
        note: `${props.receivables.total_count.toLocaleString('fa-IR')} چک باز`,
        card: 'bg-[#edf8ef] dark:bg-[#17271d]',
        accent: 'text-[#4e9c69] dark:text-emerald-300',
        ring: 'conic-gradient(#65b77d 0deg 295deg, rgba(101,183,125,.18) 295deg 360deg)',
    },
]);
</script>

<template>
    <Head title="داشبورد مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="relative min-h-[calc(100vh-64px)] overflow-hidden bg-[#fbfbfa] px-4 py-6 text-slate-900 dark:bg-[#0d1118] dark:text-slate-100 sm:px-6 lg:min-h-[calc(100vh-40px)] lg:px-9 lg:py-8 xl:px-11"
        >
            <!-- Decorative ambient shapes -->
            <div
                class="pointer-events-none absolute -left-24 -top-28 h-72 w-72 rounded-full border border-[#f6d88d]/35 dark:border-amber-300/5"
            />
            <div
                class="pointer-events-none absolute -left-14 -top-16 h-52 w-52 rounded-full border border-[#f6d88d]/30 dark:border-amber-300/5"
            />
            <div
                class="pointer-events-none absolute -bottom-32 -right-28 h-72 w-72 rounded-full bg-[#e8f5ea]/50 blur-3xl dark:bg-emerald-900/5"
            />

            <div class="relative mx-auto max-w-[1480px]">
                <!-- Heading -->
                <div
                    class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="mb-2 text-[11px] font-black tracking-[0.16em] text-[#ff6d76]"
                        >
                            MAYA HAMRAH
                        </p>

                        <h1
                            class="text-[26px] font-black tracking-tight sm:text-[30px]"
                        >
                            داشبورد
                        </h1>

                        <p
                            class="mt-2 max-w-xl text-sm leading-7 text-slate-400"
                        >
                            نمای سریع از موجودی، فروش و تعهدات مالی امروز
                        </p>
                    </div>

                    <div
                        class="flex w-fit items-center gap-3 rounded-2xl border border-white bg-white/75 px-4 py-3 shadow-[0_8px_30px_rgba(38,47,64,0.05)] backdrop-blur dark:border-white/5 dark:bg-white/[0.03]"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f2f4f7] text-slate-400 dark:bg-white/5"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-[18px] w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            >
                                <path d="M7 3v3m10-3v3M4 9h16M5 5h14v15H5V5Z" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-[10px] font-bold text-slate-400">
                                امروز
                            </p>
                            <p class="mt-0.5 text-xs font-black">
                                {{ today }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Primary overview -->
                <section class="grid gap-5 xl:grid-cols-12">
                    <!-- Stats area -->
                    <div
                        class="relative overflow-hidden rounded-[30px] border border-white bg-white/70 p-5 shadow-[0_18px_60px_rgba(40,50,70,0.055)] backdrop-blur-sm dark:border-white/5 dark:bg-white/[0.025] sm:p-6 xl:col-span-8"
                    >
                        <div
                            class="pointer-events-none absolute -left-20 -top-24 h-52 w-52 rounded-full border border-[#f2d59c]/30"
                        />
                        <div
                            class="pointer-events-none absolute -left-10 -top-14 h-36 w-36 rounded-full border border-[#f2d59c]/25"
                        />

                        <div
                            class="relative mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h2 class="text-base font-black">
                                    وضعیت کسب‌وکار
                                </h2>
                                <p class="mt-1 text-xs text-slate-400">
                                    خلاصه‌ای از مهم‌ترین اعداد امروز
                                </p>
                            </div>

                            <Link
                                :href="route('sales.index')"
                                class="hidden rounded-full bg-[#f5f6f8] px-4 py-2 text-[11px] font-black text-slate-500 transition hover:bg-slate-200/70 dark:bg-white/5 dark:text-slate-300 sm:block"
                            >
                                مشاهده جزئیات
                            </Link>
                        </div>

                        <div class="relative grid gap-3 sm:grid-cols-3">
                            <Link
                                v-for="item in stats"
                                :key="item.label"
                                :href="item.href"
                                class="group relative min-h-[190px] overflow-hidden rounded-[24px] p-5 transition duration-300 hover:-translate-y-1"
                                :class="item.card"
                            >
                                <div
                                    class="absolute -bottom-12 -left-10 h-28 w-28 rounded-full bg-white/30 blur-2xl dark:bg-white/[0.02]"
                                />

                                <div
                                    class="mb-5 flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="text-[11px] font-bold text-slate-500/80 dark:text-slate-400"
                                        >
                                            {{ item.label }}
                                        </p>
                                        <p
                                            class="mt-2 text-[11px] font-medium text-slate-400"
                                        >
                                            {{ item.note }}
                                        </p>
                                    </div>

                                    <div
                                        class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                                        :style="{ background: item.ring }"
                                    >
                                        <div
                                            class="h-6 w-6 rounded-full"
                                            :class="item.card"
                                        />
                                    </div>
                                </div>

                                <p
                                    class="mt-7 text-xl font-black leading-tight tracking-tight"
                                    :class="item.accent"
                                >
                                    {{ item.value }}
                                </p>

                                <div
                                    class="mt-4 flex items-center gap-1 text-[10px] font-black opacity-60 transition group-hover:opacity-100"
                                    :class="item.accent"
                                >
                                    <span>نمایش</span>
                                    <span>←</span>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Currency wallet -->
                    <div
                        class="relative overflow-hidden rounded-[30px] border border-[#f9ead9] bg-[linear-gradient(145deg,#fff9ef_0%,#fff1e8_100%)] p-6 shadow-[0_18px_60px_rgba(82,60,38,0.06)] dark:border-orange-300/5 dark:bg-[linear-gradient(145deg,#211c18_0%,#1b1717_100%)] xl:col-span-4"
                    >
                        <div
                            class="pointer-events-none absolute -bottom-12 -left-8 h-40 w-40 rounded-full bg-[#ffd7cb]/35 blur-2xl dark:bg-rose-900/10"
                        />
                        <div
                            class="pointer-events-none absolute left-8 top-10 h-9 w-9 rounded-full bg-[#f4b84d]"
                        />
                        <div
                            class="pointer-events-none absolute left-[42px] top-[32px] h-9 w-9 rounded-full bg-[#fff3e8] dark:bg-[#211c18]"
                        />

                        <div class="relative">
                            <div class="mb-9">
                                <p
                                    class="text-[11px] font-bold text-slate-400"
                                >
                                    نرخ‌های مرجع امروز
                                </p>
                                <h2 class="mt-1 text-base font-black">
                                    ارز و بازار
                                </h2>
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/70 text-sm font-black text-emerald-600 shadow-sm dark:bg-white/5 dark:text-emerald-300"
                                        >
                                            $
                                        </div>

                                        <div class="flex-1">
                                            <p class="text-[10px] text-slate-400">
                                                دلار آمریکا
                                            </p>
                                            <p
                                                class="mt-1 text-lg font-black tracking-tight"
                                            >
                                                {{ formatCurrencyRate(props.currencyRates.usd) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-3 flex items-center justify-between border-t border-slate-900/5 pt-3 text-[10px] dark:border-white/5"
                                    >
                                        <span
                                            :class="
                                                Number(props.currencyRates.usd?.change || 0) >= 0
                                                    ? 'text-emerald-600 dark:text-emerald-300'
                                                    : 'text-red-600 dark:text-red-300'
                                            "
                                        >
                                            {{ formatCurrencyChange(props.currencyRates.usd) }}
                                        </span>

                                        <span class="text-slate-400">
                                            {{ props.currencyRates.stale ? 'آخرین نرخ ذخیره‌شده' : 'نرخ بازار' }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/70 text-[10px] font-black text-sky-600 shadow-sm dark:bg-white/5 dark:text-sky-300"
                                        >
                                            AED
                                        </div>

                                        <div class="flex-1">
                                            <p class="text-[10px] text-slate-400">
                                                درهم امارات
                                            </p>
                                            <p
                                                class="mt-1 text-lg font-black tracking-tight"
                                            >
                                                {{ formatCurrencyRate(props.currencyRates.aed) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-3 flex items-center justify-between border-t border-slate-900/5 pt-3 text-[10px] dark:border-white/5"
                                    >
                                        <span
                                            :class="
                                                Number(props.currencyRates.aed?.change || 0) >= 0
                                                    ? 'text-emerald-600 dark:text-emerald-300'
                                                    : 'text-red-600 dark:text-red-300'
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
                        </div>
                    </div>
                </section>

                <!-- Secondary overview -->
                <section class="mt-5 grid gap-5 xl:grid-cols-12">
                    <!-- Receivables -->
                    <div
                        class="rounded-[30px] border border-white bg-white/75 p-5 shadow-[0_18px_60px_rgba(40,50,70,0.055)] backdrop-blur-sm dark:border-white/5 dark:bg-white/[0.025] sm:p-6 xl:col-span-8"
                    >
                        <div
                            class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h2 class="text-base font-black">
                                    چک‌ها و مطالبات
                                </h2>
                                <p class="mt-1 text-xs text-slate-400">
                                    نزدیک‌ترین تعهدات وصول‌نشده
                                </p>
                            </div>

                            <div
                                class="w-fit rounded-full bg-[#f4f6f8] px-3 py-1.5 text-[10px] font-black text-slate-500 dark:bg-white/5 dark:text-slate-300"
                            >
                                {{ receivables.total_count.toLocaleString('fa-IR') }} چک باز
                            </div>
                        </div>

                        <div class="mb-5 grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-[20px] bg-[#fff0f1] p-4 dark:bg-rose-950/20"
                            >
                                <p class="text-[10px] font-black text-[#dc606a]">
                                    معوق
                                </p>
                                <p class="mt-2 text-base font-black">
                                    {{ money(receivables.overdue_amount) }}
                                </p>
                                <p class="mt-1 text-[10px] text-[#de7780]">
                                    {{ receivables.overdue_count.toLocaleString('fa-IR') }} چک
                                </p>
                            </div>

                            <div
                                class="rounded-[20px] bg-[#fff7e8] p-4 dark:bg-amber-950/20"
                            >
                                <p class="text-[10px] font-black text-[#bc8426]">
                                    سررسید ۷ روز آینده
                                </p>
                                <p class="mt-2 text-base font-black">
                                    {{ money(receivables.due_soon_amount) }}
                                </p>
                                <p class="mt-1 text-[10px] text-[#c89642]">
                                    {{ receivables.due_soon_count.toLocaleString('fa-IR') }} چک
                                </p>
                            </div>

                            <div
                                class="rounded-[20px] bg-[#eef4ff] p-4 dark:bg-sky-950/20"
                            >
                                <p class="text-[10px] font-black text-[#6686bd]">
                                    کل مطالبات باز
                                </p>
                                <p class="mt-2 text-base font-black">
                                    {{ money(receivables.total_amount) }}
                                </p>
                                <p class="mt-1 text-[10px] text-[#7894c3]">
                                    {{ receivables.total_count.toLocaleString('fa-IR') }} چک
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-slate-100 pt-5 dark:border-white/5"
                        >
                            <h3 class="text-xs font-black">
                                نزدیک‌ترین سررسیدها
                            </h3>

                            <Link
                                :href="route('installments.index')"
                                class="text-[10px] font-black text-[#ff6570]"
                            >
                                همه چک‌ها ←
                            </Link>
                        </div>

                        <div
                            v-if="upcomingInstallments.length"
                            class="mt-3 space-y-2"
                        >
                            <Link
                                v-for="item in upcomingInstallments"
                                :key="item.id"
                                :href="route('sales.show', item.sale_id)"
                                class="group flex flex-col gap-3 rounded-[18px] border border-transparent bg-[#f7f8fa] px-4 py-3.5 transition hover:border-[#ffdadd] hover:bg-[#fff6f7] dark:bg-white/[0.025] dark:hover:border-rose-300/10 dark:hover:bg-rose-950/10 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <span class="text-sm font-black">
                                            {{ item.buyer_name }}
                                        </span>

                                        <span
                                            v-if="item.is_overdue"
                                            class="rounded-full bg-[#ffe2e4] px-2 py-1 text-[9px] font-black text-[#d95862] dark:bg-red-950/40 dark:text-red-300"
                                        >
                                            معوق
                                        </span>
                                    </div>

                                    <p
                                        class="mt-1.5 truncate text-[10px] text-slate-400"
                                    >
                                        {{ item.brand }} {{ item.model }}
                                        · قسط {{ item.installment_number.toLocaleString('fa-IR') }}
                                        · {{ persianDate(item.due_date) }}
                                    </p>
                                </div>

                                <div
                                    class="shrink-0 text-sm font-black"
                                >
                                    {{ money(item.remaining_amount) }}
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="mt-3 rounded-[18px] bg-[#f7f8fa] p-5 text-center text-xs text-slate-400 dark:bg-white/[0.025]"
                        >
                            هیچ چک وصول‌نشده‌ای وجود ندارد.
                        </div>
                    </div>

                    <!-- Quick actions -->
                    <div
                        class="rounded-[30px] border border-white bg-white/75 p-5 shadow-[0_18px_60px_rgba(40,50,70,0.055)] backdrop-blur-sm dark:border-white/5 dark:bg-white/[0.025] sm:p-6 xl:col-span-4"
                    >
                        <div class="mb-5">
                            <h2 class="text-base font-black">
                                دسترسی سریع
                            </h2>
                            <p class="mt-1 text-xs text-slate-400">
                                عملیات پرکاربرد مایاهمراه
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <Link
                                v-for="action in actions"
                                :key="action.title"
                                :href="action.href"
                                class="group min-h-[132px] rounded-[20px] bg-[#f7f8fa] p-4 transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-[0_12px_30px_rgba(35,45,65,0.07)] dark:bg-white/[0.025] dark:hover:bg-white/5"
                            >
                                <div
                                    class="mb-4 flex h-9 w-9 items-center justify-center rounded-xl"
                                    :class="action.tone"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        class="h-[17px] w-[17px]"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path :d="action.icon" />
                                    </svg>
                                </div>

                                <h3 class="text-[12px] font-black">
                                    {{ action.title }}
                                </h3>
                                <p
                                    class="mt-1.5 text-[10px] leading-5 text-slate-400"
                                >
                                    {{ action.description }}
                                </p>
                            </Link>
                        </div>
                    </div>
                </section>

                <div
                    class="mt-5 flex flex-col gap-2 px-1 pb-1 text-[10px] text-slate-400 sm:flex-row sm:items-center sm:justify-between"
                >
                    <span>
                        موجودی، فروش و مطالبات بر اساس داده‌های واقعی سیستم
                    </span>
                    <span>
                        MayaHamrah
                    </span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
