<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    installments: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            open_count: 0,
            open_amount: 0,
            overdue_count: 0,
            overdue_amount: 0,
            due_soon_count: 0,
            due_soon_amount: 0,
            paid_count: 0,
            paid_amount: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: 'open',
        }),
    },
});

const search = ref(props.filters.search || '');

const money = (value) =>
    `${Number(value || 0).toLocaleString('fa-IR')} تومان`;

const formatDate = (value) => {
    if (!value) return '—';

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(`${value}T00:00:00`));
};

const formatNumber = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const tabs = [
    { label: 'همه', value: 'all' },
    { label: 'باز', value: 'open' },
    { label: 'معوق', value: 'overdue' },
    { label: '۷ روز آینده', value: 'due_soon' },
    { label: 'پاس‌شده', value: 'paid' },
];

const submitSearch = () => {
    router.get(
        route('installments.index'),
        {
            search: search.value || undefined,
            status: props.filters.status || 'open',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const clearSearch = () => {
    search.value = '';

    router.get(
        route('installments.index'),
        {
            status: props.filters.status || 'open',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const statusLabel = (item) => {
    if (item.status === 'paid') return 'چک پاس شده';
    if (item.is_overdue) return 'معوق';
    if (item.is_due_soon) return 'سررسید نزدیک';
    if (Number(item.paid_amount || 0) > 0) return 'پرداخت ناقص';

    return 'باز';
};

const statusClass = (item) => {
    if (item.status === 'paid') {
        return 'bg-[#eaf8ef] text-[#4b9867] dark:bg-emerald-950/30 dark:text-emerald-300';
    }

    if (item.is_overdue) {
        return 'bg-[#fff0f1] text-[#d85b66] dark:bg-red-950/30 dark:text-red-300';
    }

    if (item.is_due_soon) {
        return 'bg-[#fff7e7] text-[#b98225] dark:bg-amber-950/30 dark:text-amber-300';
    }

    return 'bg-[#eef4ff] text-[#6382b8] dark:bg-sky-950/30 dark:text-sky-300';
};

const overdueDays = (item) => {
    if (!item.is_overdue) return 0;

    const due = new Date(`${item.due_date}T00:00:00Z`);
    const today = new Date();
    const todayUtc = Date.UTC(
        today.getFullYear(),
        today.getMonth(),
        today.getDate(),
    );

    return Math.max(
        0,
        Math.floor((todayUtc - due.getTime()) / 86400000),
    );
};
</script>

<template>
    <Head title="چک‌ها و مطالبات | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="relative min-h-screen overflow-hidden bg-[#fbfbfa] px-4 py-6 text-slate-900 dark:bg-[#0d1118] dark:text-slate-100 sm:px-6 lg:px-9 lg:py-8 xl:px-11"
        >
            <div
                class="pointer-events-none absolute -left-24 -top-28 h-72 w-72 rounded-full border border-[#f6d88d]/30 dark:border-amber-300/5"
            />

            <div class="relative mx-auto max-w-[1480px]">
                <!-- Header -->
                <div
                    class="mb-7 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <p
                            class="mb-2 text-[11px] font-black tracking-[0.16em] text-[#ff6d76]"
                        >
                            MAYA HAMRAH
                        </p>

                        <h1 class="text-[26px] font-black tracking-tight sm:text-[30px]">
                            چک‌ها و مطالبات
                        </h1>

                        <p class="mt-2 text-sm leading-7 text-slate-400">
                            مدیریت سررسیدها و وضعیت وصول چک‌های فروش اقساطی
                        </p>
                    </div>

                    <Link
                        :href="route('sales.index')"
                        class="w-fit rounded-2xl border border-white bg-white/80 px-4 py-3 text-xs font-black text-slate-500 shadow-[0_8px_25px_rgba(35,45,65,0.05)] transition hover:text-slate-900 dark:border-white/5 dark:bg-white/[0.03] dark:text-slate-300"
                    >
                        مشاهده فروش‌ها
                    </Link>
                </div>

                <!-- Summary -->
                <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <Link
                        :href="route('installments.index', { status: 'open', search: filters.search || undefined })"
                        class="rounded-[24px] bg-[#eef4ff] p-5 transition hover:-translate-y-0.5 dark:bg-sky-950/20"
                    >
                        <p class="text-[11px] font-black text-[#6685b9]">
                            چک‌های باز
                        </p>
                        <p class="mt-3 text-xl font-black">
                            {{ money(summary.open_amount) }}
                        </p>
                        <p class="mt-2 text-[11px] text-[#7d96bf]">
                            {{ formatNumber(summary.open_count) }} چک
                        </p>
                    </Link>

                    <Link
                        :href="route('installments.index', { status: 'overdue', search: filters.search || undefined })"
                        class="rounded-[24px] bg-[#fff0f1] p-5 transition hover:-translate-y-0.5 dark:bg-red-950/20"
                    >
                        <p class="text-[11px] font-black text-[#d85e68]">
                            معوق
                        </p>
                        <p class="mt-3 text-xl font-black">
                            {{ money(summary.overdue_amount) }}
                        </p>
                        <p class="mt-2 text-[11px] text-[#df7d85]">
                            {{ formatNumber(summary.overdue_count) }} چک
                        </p>
                    </Link>

                    <Link
                        :href="route('installments.index', { status: 'due_soon', search: filters.search || undefined })"
                        class="rounded-[24px] bg-[#fff7e8] p-5 transition hover:-translate-y-0.5 dark:bg-amber-950/20"
                    >
                        <p class="text-[11px] font-black text-[#bd8526]">
                            سررسید ۷ روز آینده
                        </p>
                        <p class="mt-3 text-xl font-black">
                            {{ money(summary.due_soon_amount) }}
                        </p>
                        <p class="mt-2 text-[11px] text-[#ca9740]">
                            {{ formatNumber(summary.due_soon_count) }} چک
                        </p>
                    </Link>

                    <Link
                        :href="route('installments.index', { status: 'paid', search: filters.search || undefined })"
                        class="rounded-[24px] bg-[#edf8ef] p-5 transition hover:-translate-y-0.5 dark:bg-emerald-950/20"
                    >
                        <p class="text-[11px] font-black text-[#4f9968]">
                            پاس‌شده
                        </p>
                        <p class="mt-3 text-xl font-black">
                            {{ money(summary.paid_amount) }}
                        </p>
                        <p class="mt-2 text-[11px] text-[#6da47f]">
                            {{ formatNumber(summary.paid_count) }} چک
                        </p>
                    </Link>
                </div>

                <!-- Filters -->
                <section
                    class="mb-5 rounded-[28px] border border-white bg-white/75 p-4 shadow-[0_16px_50px_rgba(40,50,70,0.045)] backdrop-blur dark:border-white/5 dark:bg-white/[0.025]"
                >
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex max-w-full gap-2 overflow-x-auto pb-1">
                            <Link
                                v-for="tab in tabs"
                                :key="tab.value"
                                :href="
                                    route('installments.index', {
                                        status: tab.value,
                                        search: filters.search || undefined,
                                    })
                                "
                                class="shrink-0 rounded-full px-4 py-2 text-xs font-black transition"
                                :class="
                                    filters.status === tab.value
                                        ? 'bg-[#ff6d76] text-white shadow-[0_7px_18px_rgba(255,109,118,0.20)]'
                                        : 'bg-[#f4f6f8] text-slate-500 hover:bg-slate-200/70 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10'
                                "
                            >
                                {{ tab.label }}
                            </Link>
                        </div>

                        <form
                            class="flex w-full gap-2 xl:max-w-md"
                            @submit.prevent="submitSearch"
                        >
                            <input
                                v-model="search"
                                type="search"
                                class="min-w-0 flex-1 rounded-2xl border-0 bg-[#f4f6f8] px-4 py-3 text-sm placeholder:text-slate-400 focus:ring-2 focus:ring-[#ff6d76]/30 dark:bg-white/5"
                                placeholder="نام، موبایل، مدل یا IMEI..."
                            />

                            <button
                                type="submit"
                                class="rounded-2xl bg-slate-900 px-4 py-3 text-xs font-black text-white dark:bg-white dark:text-slate-900"
                            >
                                جست‌وجو
                            </button>

                            <button
                                v-if="filters.search"
                                type="button"
                                class="rounded-2xl bg-[#fff0f1] px-4 py-3 text-xs font-black text-[#d85e68] dark:bg-red-950/20 dark:text-red-300"
                                @click="clearSearch"
                            >
                                ×
                            </button>
                        </form>
                    </div>
                </section>

                <!-- Checks -->
                <section
                    class="rounded-[30px] border border-white bg-white/75 p-4 shadow-[0_18px_60px_rgba(40,50,70,0.05)] backdrop-blur dark:border-white/5 dark:bg-white/[0.025] sm:p-6"
                >
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-black">
                                فهرست چک‌ها
                            </h2>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ formatNumber(installments.length) }} نتیجه
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="installments.length"
                        class="space-y-2.5"
                    >
                        <div
                            v-for="item in installments"
                            :key="item.id"
                            class="group rounded-[20px] border border-transparent bg-[#f7f8fa] p-4 transition hover:border-[#ffdadd] hover:bg-white dark:bg-white/[0.025] dark:hover:border-rose-300/10 dark:hover:bg-white/[0.04]"
                        >
                            <div
                                class="grid gap-4 lg:grid-cols-[minmax(180px,1.4fr)_minmax(150px,1fr)_130px_minmax(150px,1fr)_auto] lg:items-center"
                            >
                                <!-- Customer -->
                                <div class="min-w-0">
                                    <Link
                                        :href="route('contacts.show', item.buyer_id)"
                                        class="font-black transition hover:text-[#ff6570]"
                                    >
                                        {{ item.buyer_name }}
                                    </Link>

                                    <p class="mt-1 text-[11px] text-slate-400" dir="ltr">
                                        {{ item.buyer_mobile }}
                                    </p>
                                </div>

                                <!-- Device -->
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black">
                                        {{ item.brand }} {{ item.model }}
                                    </p>

                                    <p class="mt-1 truncate text-[11px] text-slate-400">
                                        <span v-if="item.storage">
                                            {{ item.storage }}
                                        </span>
                                        <span v-if="item.imei">
                                            · IMEI {{ item.imei }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Installment -->
                                <div>
                                    <p class="text-[10px] text-slate-400">
                                        شماره قسط
                                    </p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ formatNumber(item.installment_number) }}
                                    </p>
                                </div>

                                <!-- Date & amount -->
                                <div>
                                    <p class="text-[10px] text-slate-400">
                                        سررسید
                                    </p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ formatDate(item.due_date) }}
                                    </p>

                                    <p
                                        v-if="item.is_overdue"
                                        class="mt-1 text-[10px] font-black text-red-500"
                                    >
                                        {{ formatNumber(overdueDays(item)) }} روز گذشته
                                    </p>
                                </div>

                                <!-- Status -->
                                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                    <div class="text-left lg:ml-2">
                                        <p class="text-sm font-black">
                                            {{
                                                item.status === 'paid'
                                                    ? money(item.paid_amount)
                                                    : money(item.remaining_amount)
                                            }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-slate-400">
                                            {{
                                                item.status === 'paid'
                                                    ? `پاس‌شده در ${formatDate(item.paid_at)}`
                                                    : 'مانده چک'
                                            }}
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-full px-3 py-1.5 text-[10px] font-black"
                                        :class="statusClass(item)"
                                    >
                                        {{ statusLabel(item) }}
                                    </span>

                                    <Link
                                        :href="route('sales.show', item.sale_id)"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-slate-400 shadow-[0_5px_14px_rgba(35,45,65,0.05)] transition hover:text-[#ff6570] dark:bg-white/5"
                                        title="مشاهده قرارداد"
                                        aria-label="مشاهده قرارداد"
                                    >
                                        ←
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-[20px] bg-[#f7f8fa] px-5 py-12 text-center dark:bg-white/[0.025]"
                    >
                        <p class="text-sm font-black text-slate-500 dark:text-slate-300">
                            چکی با این شرایط پیدا نشد.
                        </p>
                        <p class="mt-2 text-xs text-slate-400">
                            فیلتر یا عبارت جست‌وجو را تغییر دهید.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
