<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    sale: {
        type: Object,
        required: true,
    },
    installments: {
        type: Array,
        default: () => [],
    },
});

const formatMoney = (value) => {
    if (value === null || value === undefined) return '—';

    return Number(value).toLocaleString('fa-IR');
};

const formatNumber = (value) => {
    if (value === null || value === undefined || value === '') return '—';

    return Number(value).toLocaleString('fa-IR', {
        useGrouping: false,
    });
};

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

const installmentStatusLabel = (status) => {
    const labels = {
        pending: 'در انتظار پرداخت',
        partial: 'پرداخت ناقص',
        paid: 'پرداخت‌شده',
        overdue: 'معوق',
    };

    return labels[status] ?? status;
};

const installmentStatusClass = (status) => {
    const classes = {
        pending:
            'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300',
        partial:
            'bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300',
        paid:
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300',
        overdue:
            'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300',
    };

    return (
        classes[status] ??
        'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
    );
};

const isInstallment = computed(
    () => props.sale.sale_type === 'installment',
);

const totalInstallments = computed(() => props.installments.length);

const installmentsTotal = computed(() =>
    props.installments.reduce(
        (sum, installment) => sum + Number(installment.amount || 0),
        0,
    ),
);

const installmentsPaid = computed(() =>
    props.installments.reduce(
        (sum, installment) => sum + Number(installment.paid_amount || 0),
        0,
    ),
);

const installmentsRemaining = computed(
    () => installmentsTotal.value - installmentsPaid.value,
);

const paidInstallmentsCount = computed(
    () =>
        props.installments.filter(
            (installment) => installment.status === 'paid',
        ).length,
);

const markInstallmentPaid = (installment) => {
    if (
        !window.confirm(
            `آیا چک قسط ${formatNumber(installment.installment_number)} پاس شده است؟`,
        )
    ) {
        return;
    }

    router.post(
        route('installments.mark-paid', installment.id),
        {},
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head :title="`جزئیات فروش ${sale.brand} ${sale.model} | مایاهمراه`" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">
                            مایاهمراه
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            جزئیات فروش
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            قرارداد، خریدار و وضعیت مالی فروش
                        </p>
                    </div>

                    <Link
                        :href="route('sales.index')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <div class="grid gap-5 lg:grid-cols-[360px_minmax(0,1fr)]">
                    <section
                        class="overflow-hidden rounded-[30px] bg-white shadow-sm dark:bg-slate-900"
                    >
                        <div
                            class="flex h-64 items-center justify-center overflow-hidden bg-slate-100 dark:bg-slate-800"
                        >
                            <img
                                v-if="sale.cover_image"
                                :src="`/storage/${sale.cover_image}`"
                                :alt="`${sale.brand} ${sale.model}`"
                                class="h-full w-full object-cover"
                            />

                            <span
                                v-else
                                class="text-8xl opacity-40"
                            >
                                📱
                            </span>
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-xl font-black">
                                        {{ sale.brand }} {{ sale.model }}
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        <span v-if="sale.storage">
                                            {{ sale.storage }}
                                        </span>

                                        <span v-if="sale.color">
                                            · {{ sale.color }}
                                        </span>
                                    </p>
                                </div>

                                <span
                                    class="rounded-xl bg-violet-50 px-3 py-1.5 text-xs font-black text-violet-700 dark:bg-violet-950/30 dark:text-violet-300"
                                >
                                    {{ saleTypeLabel(sale.sale_type) }}
                                </span>
                            </div>

                            <div class="mt-5 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-slate-400">
                                        خریدار
                                    </span>

                                    <Link
                                        :href="route('contacts.show', sale.buyer_id)"
                                        class="font-black text-violet-600"
                                    >
                                        {{ sale.buyer_name }}
                                    </Link>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-slate-400">
                                        موبایل
                                    </span>

                                    <a
                                        :href="`tel:${sale.buyer_mobile}`"
                                        class="font-bold"
                                        dir="ltr"
                                    >
                                        {{ sale.buyer_mobile }}
                                    </a>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-slate-400">
                                        تاریخ فروش
                                    </span>

                                    <span class="font-bold">
                                        {{ formatDate(sale.sale_date) }}
                                    </span>
                                </div>

                                <div
                                    v-if="sale.imei"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span class="text-slate-400">
                                        IMEI
                                    </span>

                                    <span
                                        class="font-bold"
                                        dir="ltr"
                                    >
                                        {{ sale.imei }}
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="sale.notes"
                                class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:bg-slate-950 dark:text-slate-300"
                            >
                                <p class="mb-1 text-xs font-bold text-slate-400">
                                    توضیحات فروش
                                </p>

                                {{ sale.notes }}
                            </div>
                        </div>
                    </section>

                    <div class="space-y-5">
                        <section
                            class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-6"
                        >
                            <div class="mb-5">
                                <h2 class="text-lg font-black">
                                    خلاصه مالی قرارداد
                                </h2>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    مبالغ بر اساس اطلاعات ثبت‌شده هنگام فروش
                                </p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                                >
                                    <p class="text-xs font-bold text-slate-400">
                                        قیمت خرید دستگاه
                                    </p>

                                    <p class="mt-2 text-lg font-black">
                                        {{ formatMoney(sale.purchase_price) }}
                                        <span class="text-xs font-bold">تومان</span>
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                                >
                                    <p class="text-xs font-bold text-slate-400">
                                        قیمت فروش پایه
                                    </p>

                                    <p class="mt-2 text-lg font-black">
                                        {{ formatMoney(sale.sale_price) }}
                                        <span class="text-xs font-bold">تومان</span>
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                                >
                                    <p class="text-xs font-bold text-slate-400">
                                        سود معامله
                                    </p>

                                    <p
                                        class="mt-2 text-lg font-black"
                                        :class="
                                            Number(sale.trading_profit || 0) >= 0
                                                ? 'text-emerald-600'
                                                : 'text-red-600'
                                        "
                                    >
                                        <template v-if="sale.trading_profit !== null">
                                            {{ Number(sale.trading_profit) >= 0 ? '+' : '' }}{{ formatMoney(sale.trading_profit) }}
                                            <span class="text-xs font-bold">تومان</span>
                                        </template>

                                        <template v-else>
                                            —
                                        </template>
                                    </p>
                                </div>

                                <template v-if="isInstallment">
                                    <div
                                        class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                                    >
                                        <p class="text-xs font-bold text-slate-400">
                                            پیش‌پرداخت
                                        </p>

                                        <p class="mt-2 text-lg font-black">
                                            {{ formatMoney(sale.down_payment) }}
                                            <span class="text-xs font-bold">تومان</span>
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-2xl bg-violet-50 p-4 dark:bg-violet-950/20"
                                    >
                                        <p class="text-xs font-bold text-violet-500">
                                            سود اقساط
                                        </p>

                                        <p class="mt-2 text-lg font-black text-violet-700 dark:text-violet-300">
                                            +{{ formatMoney(sale.installment_profit) }}
                                            <span class="text-xs font-bold">تومان</span>
                                        </p>

                                        <p class="mt-1 text-xs text-violet-500">
                                            نرخ ماهانه
                                            {{ Number(sale.monthly_profit_rate || 0).toLocaleString('fa-IR', {
                                                minimumFractionDigits: 1,
                                                maximumFractionDigits: 1,
                                            }) }}
                                            درصد
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-2xl bg-slate-900 p-4 text-white dark:bg-white dark:text-slate-900"
                                    >
                                        <p class="text-xs font-bold opacity-60">
                                            مبلغ کل قرارداد
                                        </p>

                                        <p class="mt-2 text-lg font-black">
                                            {{ formatMoney(sale.contract_total) }}
                                            <span class="text-xs font-bold">تومان</span>
                                        </p>
                                    </div>
                                </template>
                            </div>

                            <div
                                v-if="isInstallment && sale.total_profit !== null"
                                class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-950 dark:bg-emerald-950/20"
                            >
                                <div>
                                    <p class="text-xs font-bold text-emerald-600">
                                        سود کل قرارداد
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        سود معامله + سود تأمین مالی
                                    </p>
                                </div>

                                <p class="text-xl font-black text-emerald-700 dark:text-emerald-300">
                                    {{ Number(sale.total_profit) >= 0 ? '+' : '' }}{{ formatMoney(sale.total_profit) }}
                                    <span class="text-xs">تومان</span>
                                </p>
                            </div>
                        </section>

                        <section
                            v-if="isInstallment"
                            class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-6"
                        >
                            <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-black">
                                        برنامه اقساط
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ formatNumber(totalInstallments) }} قسط ثبت شده
                                    </p>
                                </div>

                                <div class="text-left">
                                    <p class="text-xs text-slate-400">
                                        پرداخت‌شده
                                    </p>

                                    <p class="mt-1 font-black text-emerald-600">
                                        {{ formatNumber(paidInstallmentsCount) }}
                                        از
                                        {{ formatNumber(totalInstallments) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mb-5 grid gap-3 sm:grid-cols-3">
                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                                >
                                    <p class="text-xs text-slate-400">
                                        مجموع اقساط
                                    </p>

                                    <p class="mt-1 font-black">
                                        {{ formatMoney(installmentsTotal) }}
                                        <span class="text-xs">تومان</span>
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-950/20"
                                >
                                    <p class="text-xs text-emerald-600">
                                        پرداخت‌شده
                                    </p>

                                    <p class="mt-1 font-black text-emerald-700 dark:text-emerald-300">
                                        {{ formatMoney(installmentsPaid) }}
                                        <span class="text-xs">تومان</span>
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl bg-amber-50 p-4 dark:bg-amber-950/20"
                                >
                                    <p class="text-xs text-amber-600">
                                        مانده اقساط
                                    </p>

                                    <p class="mt-1 font-black text-amber-700 dark:text-amber-300">
                                        {{ formatMoney(installmentsRemaining) }}
                                        <span class="text-xs">تومان</span>
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="installments.length === 0"
                                class="rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500 dark:bg-slate-950 dark:text-slate-400"
                            >
                                برای این فروش قسطی ثبت نشده است.
                            </div>

                            <div
                                v-else
                                class="space-y-3"
                            >
                                <div
                                    v-for="installment in installments"
                                    :key="installment.id"
                                    class="grid gap-3 rounded-2xl border border-slate-100 p-4 dark:border-slate-800 sm:grid-cols-[80px_1fr_1fr_1fr_auto] sm:items-center"
                                >
                                    <div>
                                        <p class="text-xs text-slate-400">
                                            قسط
                                        </p>

                                        <p class="mt-1 font-black">
                                            {{ formatNumber(installment.installment_number) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            سررسید
                                        </p>

                                        <p class="mt-1 font-bold">
                                            {{ formatDate(installment.due_date) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            مبلغ
                                        </p>

                                        <p class="mt-1 font-bold">
                                            {{ formatMoney(installment.amount) }}
                                            تومان
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            پرداخت
                                        </p>

                                        <p class="mt-1 font-bold">
                                            {{ formatMoney(installment.paid_amount) }}
                                            تومان
                                        </p>
                                    </div>

                                    <div class="flex flex-col items-start gap-2 sm:items-end">
                                        <span
                                            class="inline-flex rounded-xl px-3 py-1.5 text-xs font-black"
                                            :class="installmentStatusClass(installment.status)"
                                        >
                                            {{ installment.status === 'paid' ? 'چک پاس شده' : installmentStatusLabel(installment.status) }}
                                        </span>

                                        <span
                                            v-if="installment.status === 'paid' && installment.paid_at"
                                            class="text-xs text-slate-400"
                                        >
                                            پاس‌شده در {{ formatDate(installment.paid_at) }}
                                        </span>

                                        <button
                                            v-if="installment.status !== 'paid'"
                                            type="button"
                                            class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-black text-white transition hover:bg-emerald-700"
                                            @click="markInstallmentPaid(installment)"
                                        >
                                            ثبت پاس شدن چک
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
