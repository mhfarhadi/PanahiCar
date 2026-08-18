<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { computed, ref } from 'vue';
import { colorLabel, formatMileage, formatYear } from '@/Utils/vehicleLabels';
import { mediaUrl } from '@/Utils/carPhotos';

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
        'bg-[#f1f3f5] text-slate-600 dark:bg-white/[0.06] dark:text-slate-300'
    );
};

const isInstallment = computed(
    () => props.sale.sale_type === 'installment',
);

const isGoldGuarantee = computed(
    () => isInstallment.value && props.sale.guarantee_type === 'gold',
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


const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const today = new Date();

const localToday = [
    today.getFullYear(),
    String(today.getMonth() + 1).padStart(2, '0'),
    String(today.getDate()).padStart(2, '0'),
].join('-');

const selectedInstallment = ref(null);

const clearanceForm = useForm({
    paid_at: '',
});

const syncPaidAt = (value) => {
    if (!value) return;

    if (typeof value.format === 'function') {
        const date = value.clone ? value.clone() : value;

        if (typeof date.locale === 'function') {
            date.locale('en');
        }

        clearanceForm.paid_at = normalizeDigits(
            date.format('YYYY-MM-DD'),
        );

        return;
    }

    clearanceForm.paid_at = normalizeDigits(String(value));
};

const openPaidModal = (installment) => {
    selectedInstallment.value = installment;
    clearanceForm.clearErrors();
    clearanceForm.paid_at = '';
};

const closePaidModal = () => {
    if (clearanceForm.processing) return;

    selectedInstallment.value = null;
    clearanceForm.reset();
    clearanceForm.clearErrors();
};

const submitInstallmentPaid = () => {
    if (!selectedInstallment.value) return;

    clearanceForm.post(
        route(
            'installments.mark-paid',
            selectedInstallment.value.id,
        ),
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedInstallment.value = null;
                clearanceForm.reset();
            },
        },
    );
};

const clearanceDelayDays = (installment) => {
    if (
        installment.status !== 'paid' ||
        !installment.due_date ||
        !installment.paid_at
    ) {
        return 0;
    }

    const due = new Date(`${installment.due_date}T00:00:00Z`);
    const paid = new Date(`${installment.paid_at}T00:00:00Z`);

    const days = Math.floor(
        (paid.getTime() - due.getTime()) / 86400000,
    );

    return Math.max(0, days);
};

const hasCheckDetails = (installment) =>
    Boolean(
        installment.bank_name ||
        installment.check_number ||
        installment.sayad_id ||
        installment.images?.length,
    );
</script>

<template>
    <Head :title="`جزئیات فروش ${sale.brand} ${sale.model} | automaya`" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-[#2563eb]">
                            automaya
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
                        class="rounded-2xl border border-slate-200/60 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-white/5 dark:bg-white/[0.035] dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <div class="grid gap-5 lg:grid-cols-[360px_minmax(0,1fr)]">
                    <section
                        class="overflow-hidden rounded-[30px] bg-white shadow-sm dark:bg-white/[0.035]"
                    >
                        <div
                            class="flex h-64 items-center justify-center overflow-hidden bg-[#f1f3f5] dark:bg-white/[0.06]"
                        >
                            <img
                                :src="mediaUrl(sale.cover_image, sale.id)"
                                :alt="`${sale.brand} ${sale.model}`"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-xl font-black">
                                        {{ sale.brand }} {{ sale.model }}
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ formatYear(sale.model_year) }}
                                        <span v-if="sale.mileage !== null && sale.mileage !== undefined">
                                            · {{ formatMileage(sale.mileage) }}
                                        </span>
                                        <span v-if="sale.color">
                                            · {{ colorLabel(sale.color) }}
                                        </span>
                                    </p>
                                </div>

                                <span
                                    class="rounded-xl bg-[#eff6ff] px-3 py-1.5 text-xs font-black text-[#1d4ed8] dark:bg-[#2563eb]/[0.08] dark:text-[#93c5fd]"
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
                                        class="font-black text-[#2563eb]"
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
                                    v-if="isInstallment"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span class="text-slate-400">
                                        نوع ضمانت
                                    </span>

                                    <span
                                        class="font-black"
                                        :class="
                                            isGoldGuarantee
                                                ? 'text-amber-700 dark:text-amber-300'
                                                : 'text-[#1d4ed8] dark:text-[#93c5fd]'
                                        "
                                    >
                                        {{ isGoldGuarantee ? 'ضمانت طلا' : 'ضمانت چک' }}
                                    </span>
                                </div>

                                <div
                                    v-if="sale.vin"
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span class="text-slate-400">
                                        VIN
                                    </span>

                                    <span
                                        class="font-bold"
                                        dir="ltr"
                                    >
                                        {{ sale.vin }}
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="sale.notes"
                                class="mt-5 rounded-2xl bg-[#f7f8fa] p-4 text-sm leading-7 text-slate-600 dark:bg-white/[0.025] dark:text-slate-300"
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
                            class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-white/[0.035] sm:p-6"
                        >
                            <div class="mb-6 flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-black">
                                        خلاصه مالی قرارداد
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        خرید، فروش و نتیجه مالی این معامله
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full bg-[#f1f3f5] px-3 py-1.5 text-xs font-bold text-slate-600 dark:bg-white/[0.06] dark:text-slate-300"
                                >
                                    {{ saleTypeLabel(sale.sale_type) }}
                                </span>
                            </div>

                            <!-- معامله اصلی -->
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div
                                    class="rounded-2xl border border-slate-100 bg-[#f7f8fa] p-4 dark:border-white/5 dark:bg-white/[0.025]"
                                >
                                    <p class="text-xs font-bold text-slate-400">
                                        قیمت خرید
                                    </p>

                                    <p class="mt-2 text-lg font-black">
                                        {{ formatMoney(sale.purchase_price) }}
                                        <span class="text-xs font-bold text-slate-400">تومان</span>
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-sky-100 bg-sky-50/60 p-4 dark:border-sky-950 dark:bg-sky-950/20"
                                >
                                    <p class="text-xs font-bold text-sky-600 dark:text-sky-400">
                                        قیمت فروش
                                    </p>

                                    <p class="mt-2 text-lg font-black">
                                        {{ formatMoney(sale.sale_price) }}
                                        <span class="text-xs font-bold text-slate-400">تومان</span>
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border p-4"
                                    :class="
                                        Number(sale.trading_profit || 0) >= 0
                                            ? 'border-emerald-100 bg-emerald-50/70 dark:border-emerald-950 dark:bg-emerald-950/20'
                                            : 'border-red-100 bg-red-50/70 dark:border-red-950 dark:bg-red-950/20'
                                    "
                                >
                                    <p
                                        class="text-xs font-bold"
                                        :class="
                                            Number(sale.trading_profit || 0) >= 0
                                                ? 'text-emerald-600'
                                                : 'text-red-600'
                                        "
                                    >
                                        سود معامله
                                    </p>

                                    <p
                                        class="mt-2 text-lg font-black"
                                        :class="
                                            Number(sale.trading_profit || 0) >= 0
                                                ? 'text-emerald-700 dark:text-emerald-300'
                                                : 'text-red-700 dark:text-red-300'
                                        "
                                    >
                                        <template v-if="sale.trading_profit !== null">
                                            {{ Number(sale.trading_profit) >= 0 ? '+' : '' }}{{ formatMoney(sale.trading_profit) }}
                                            <span class="text-xs font-bold">تومان</span>
                                        </template>

                                        <template v-else>—</template>
                                    </p>
                                </div>
                            </div>

                            <!-- جزئیات اقساط -->
                            <template v-if="isInstallment">
                                <div
                                    class="mt-5 rounded-3xl border border-slate-100 bg-[#f7f8fa]/60 p-4 dark:border-white/5 dark:bg-white/[0.025]/50"
                                >
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <p class="text-sm font-black">
                                            جزئیات فروش اقساطی
                                        </p>

                                        <span class="text-xs text-slate-400">
                                            نرخ ماهانه
                                            {{ Number(sale.monthly_profit_rate || 0).toLocaleString('fa-IR', {
                                                minimumFractionDigits: 1,
                                                maximumFractionDigits: 1,
                                            }) }}
                                            ٪
                                        </span>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div class="rounded-2xl bg-white p-4 dark:bg-white/[0.035]">
                                            <p class="text-xs font-bold text-slate-400">
                                                پیش‌پرداخت
                                            </p>

                                            <p class="mt-2 font-black">
                                                {{ formatMoney(sale.down_payment) }}
                                                <span class="text-xs text-slate-400">تومان</span>
                                            </p>
                                        </div>

                                        <div class="rounded-2xl bg-[#eff6ff] p-4 dark:bg-[#2563eb]/[0.06]">
                                            <p class="text-xs font-bold text-[#2563eb]">
                                                سود اقساط
                                            </p>

                                            <p class="mt-2 font-black text-[#1d4ed8] dark:text-[#93c5fd]">
                                                +{{ formatMoney(sale.installment_profit) }}
                                                <span class="text-xs">تومان</span>
                                            </p>
                                        </div>

                                        <div class="rounded-2xl bg-slate-900 p-4 text-white dark:bg-white dark:text-slate-900">
                                            <p class="text-xs font-bold opacity-60">
                                                مبلغ کل قرارداد
                                            </p>

                                            <p class="mt-2 text-lg font-black">
                                                {{ formatMoney(sale.contract_total) }}
                                                <span class="text-xs opacity-60">تومان</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="sale.total_profit !== null"
                                    class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 dark:border-emerald-950 dark:bg-emerald-950/20"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-emerald-600">
                                            سود کل قرارداد
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                            سود معامله + سود اقساط
                                        </p>
                                    </div>

                                    <p class="text-xl font-black text-emerald-700 dark:text-emerald-300">
                                        {{ Number(sale.total_profit) >= 0 ? '+' : '' }}{{ formatMoney(sale.total_profit) }}
                                        <span class="text-xs">تومان</span>
                                    </p>
                                </div>
                            </template>

                            <div
                                v-if="isGoldGuarantee"
                                class="mt-5 rounded-[26px] border border-amber-200/70 bg-amber-50/70 p-5 dark:border-amber-400/10 dark:bg-amber-950/15"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-amber-800 dark:text-amber-300">
                                            ضمانت طلا
                                        </p>

                                        <p class="mt-1 text-xs leading-6 text-amber-700/70 dark:text-amber-300/70">
                                            پوشش اصل مانده بدهی + سود دو ماه قرارداد
                                        </p>
                                    </div>

                                    <span class="rounded-full bg-white px-3 py-1.5 text-[10px] font-black text-amber-700 dark:bg-white/5 dark:text-amber-300">
                                        {{ sale.gold_karat || 18 }} عیار
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">اصل مانده</p>
                                        <p class="mt-1 font-black">
                                            {{ formatMoney(sale.gold_base_principal) }}
                                            <span class="text-xs">تومان</span>
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">پوشش دو ماه سود</p>
                                        <p class="mt-1 font-black text-amber-700 dark:text-amber-300">
                                            +{{ formatMoney(sale.gold_coverage_profit) }}
                                            <span class="text-xs">تومان</span>
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">حداقل وزن لازم</p>
                                        <p class="mt-1 font-black">
                                            {{ Number(sale.gold_required_weight || 0).toLocaleString('fa-IR', { maximumFractionDigits: 4 }) }}
                                            گرم
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-amber-500 p-4 text-white">
                                        <p class="text-[10px] font-bold opacity-80">وزن واقعی دریافتی</p>
                                        <p class="mt-1 text-lg font-black">
                                            {{ Number(sale.gold_received_weight || 0).toLocaleString('fa-IR', { maximumFractionDigits: 4 }) }}
                                            گرم
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">نوع طلای تحویلی</p>
                                        <p class="mt-1 text-sm font-black">
                                            {{ sale.gold_type || '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">نرخ مبنای هر گرم</p>
                                        <p class="mt-1 text-sm font-black">
                                            {{ formatMoney(sale.gold_rate_per_gram) }}
                                            تومان
                                        </p>
                                        <p class="mt-1 text-[10px] text-slate-400">
                                            {{ formatDate(sale.gold_rate_date || sale.sale_date) }}
                                            · {{ sale.gold_rate_source === 'manual' ? 'ثبت دستی' : 'نوسان' }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="sale.gold_description"
                                    class="mt-3 rounded-2xl bg-white/80 p-4 text-sm leading-7 text-slate-600 dark:bg-white/[0.035] dark:text-slate-300"
                                >
                                    {{ sale.gold_description }}
                                </div>
                            </div>
                        </section>

                        <section
                            v-if="isInstallment"
                            class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-white/[0.035] sm:p-6"
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
                                    class="rounded-2xl bg-[#f7f8fa] p-4 dark:bg-white/[0.025]"
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
                                class="rounded-2xl bg-[#f7f8fa] p-6 text-center text-sm text-slate-500 dark:bg-white/[0.025] dark:text-slate-400"
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
                                    class="rounded-[22px] border border-slate-100 p-4 dark:border-white/5"
                                >
                                    <div
                                        class="grid gap-3 sm:grid-cols-[80px_1fr_1fr_1fr_auto] sm:items-center"
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
                                                {{ installment.status === 'paid' ? (isGoldGuarantee ? 'پرداخت‌شده' : 'چک پاس شده') : installmentStatusLabel(installment.status) }}
                                            </span>

                                            <span
                                                v-if="installment.status === 'paid' && installment.paid_at"
                                                class="text-xs text-slate-400"
                                            >
                                                {{ isGoldGuarantee ? 'پرداخت‌شده' : 'پاس‌شده' }} در {{ formatDate(installment.paid_at) }}
                                            </span>

                                            <span
                                                v-if="clearanceDelayDays(installment) > 0"
                                                class="text-xs font-black text-red-600 dark:text-red-400"
                                            >
                                                {{ formatNumber(clearanceDelayDays(installment)) }}
                                                {{ isGoldGuarantee ? 'روز تأخیر در پرداخت قسط' : 'روز تأخیر در پاس شدن چک' }}
                                            </span>

                                            <button
                                                v-if="installment.status !== 'paid'"
                                                type="button"
                                                class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-black text-white transition hover:bg-emerald-700"
                                                @click="openPaidModal(installment)"
                                            >
                                                {{ isGoldGuarantee ? 'ثبت وصول قسط' : 'ثبت پاس شدن چک' }}
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        v-if="!isGoldGuarantee && hasCheckDetails(installment)"
                                        class="mt-4 rounded-[18px] bg-[#f7f8fa] p-4 dark:bg-white/[0.025]"
                                    >
                                        <div
                                            class="grid gap-3 sm:grid-cols-3"
                                        >
                                            <div>
                                                <p class="text-[10px] text-slate-400">
                                                    بانک
                                                </p>
                                                <p class="mt-1 text-xs font-black">
                                                    {{ installment.bank_name || '—' }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-[10px] text-slate-400">
                                                    شماره چک
                                                </p>
                                                <p
                                                    class="mt-1 text-xs font-black"
                                                    dir="ltr"
                                                >
                                                    {{ installment.check_number || '—' }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-[10px] text-slate-400">
                                                    شناسه صیاد
                                                </p>
                                                <p
                                                    class="mt-1 break-all text-xs font-black"
                                                    dir="ltr"
                                                >
                                                    {{ installment.sayad_id || '—' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            v-if="installment.images?.length"
                                            class="mt-4"
                                        >
                                            <p class="mb-2 text-[10px] font-black text-slate-400">
                                                تصاویر چک
                                            </p>

                                            <div
                                                class="flex gap-2 overflow-x-auto pb-1"
                                            >
                                                <a
                                                    v-for="image in installment.images"
                                                    :key="image.id"
                                                    :href="`/storage/${image.image_path}`"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="block shrink-0 overflow-hidden rounded-xl border border-slate-200/60 bg-white dark:border-white/5 dark:bg-white/5"
                                                >
                                                    <img
                                                        :src="`/storage/${image.image_path}`"
                                                        alt="تصویر چک"
                                                        class="h-20 w-28 object-cover"
                                                    />
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-else-if="!isGoldGuarantee"
                                        class="mt-3 text-[11px] text-slate-400"
                                    >
                                        مشخصات فیزیکی چک هنوز ثبت نشده است.
                                    </div>

                                    <div
                                        v-else
                                        class="mt-3 inline-flex rounded-full bg-amber-50 px-3 py-1.5 text-[10px] font-black text-amber-700 dark:bg-amber-950/20 dark:text-amber-300"
                                    >
                                        ضمانت این قسط: طلا
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="selectedInstallment"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            @click.self="closePaidModal"
        >
            <div
                dir="rtl"
                class="w-full max-w-md rounded-[28px] border border-slate-200/60 bg-white p-5 text-slate-900 shadow-2xl dark:border-white/10 dark:bg-white/[0.035] dark:text-slate-100 sm:p-6"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold text-emerald-600">
                            ثبت پاس شدن چک
                        </p>

                        <h2 class="mt-1 text-xl font-black">
                            {{ isGoldGuarantee ? 'تاریخ وصول قسط' : 'تاریخ پاس شدن چک' }}
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            قسط
                            {{ formatNumber(selectedInstallment.installment_number) }}
                            با سررسید
                            {{ formatDate(selectedInstallment.due_date) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl bg-[#f1f3f5] px-3 py-2 text-sm font-black text-slate-500 dark:bg-white/[0.06] dark:text-slate-300"
                        @click="closePaidModal"
                    >
                        ×
                    </button>
                </div>

                <div class="mt-6">
                    <label class="mb-2 block text-sm font-bold">
                        {{ isGoldGuarantee ? 'قسط چه تاریخی پرداخت شد؟' : 'چک چه تاریخی پاس شد؟' }}
                    </label>

                    <Vue3PersianDatetimePicker
                        v-model="clearanceForm.paid_at"
                        :initial-value="localToday"
                        format="YYYY-MM-DD"
                        display-format="jYYYY/jMM/jDD"
                        type="date"
                        convert-numbers
                        auto-submit
                        custom-input=".check-paid-at-input"
                        @change="syncPaidAt"
                    />

                    <input
                        type="text"
                        class="check-paid-at-input w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] text-center font-black focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/10 dark:bg-white/[0.025]"
                        :placeholder="isGoldGuarantee ? 'تاریخ وصول قسط' : 'تاریخ پاس شدن چک'"
                        readonly
                    />

                    <p
                        v-if="clearanceForm.errors.paid_at"
                        class="mt-2 text-xs font-bold text-red-500"
                    >
                        {{ clearanceForm.errors.paid_at }}
                    </p>

                    <p class="mt-3 text-xs leading-6 text-slate-400">
                        {{ isGoldGuarantee ? 'تاریخ واقعی پرداخت قسط را وارد کنید؛ این تاریخ در سابقه پرداخت مشتری ثبت می‌شود.' : 'تاریخ واقعی وصول چک را وارد کنید؛ این تاریخ برای محاسبه خوش‌حسابی مشتری استفاده خواهد شد.' }}
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200/60 px-4 py-3 text-sm font-black text-slate-600 dark:border-white/10 dark:text-slate-300"
                        :disabled="clearanceForm.processing"
                        @click="closePaidModal"
                    >
                        انصراف
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-700 disabled:opacity-50"
                        :disabled="
                            clearanceForm.processing ||
                            !clearanceForm.paid_at
                        "
                        @click="submitInstallmentPaid"
                    >
                        {{
                            clearanceForm.processing
                                ? 'در حال ثبت...'
                                : isGoldGuarantee ? 'تأیید وصول قسط' : 'تأیید پاس شدن چک'
                        }}
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
