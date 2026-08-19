<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { toJalaali, toGregorian, jalaaliMonthLength } from 'jalaali-js';
import { colorLabel, formatMileage, formatYear } from '@/Utils/vehicleLabels';

const props = defineProps({
    device: {
        type: Object,
        required: true,
    },
    contacts: {
        type: Array,
        default: () => [],
    },
});

const now = new Date();
const localDate = [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, '0'),
    String(now.getDate()).padStart(2, '0'),
].join('-');

const form = useForm({
    buyer_id: '',
    sale_type: 'cash',
    guarantee_type: 'check',
    gold_rate_per_gram: '',
    gold_received_weight: '',
    gold_type: '',
    gold_description: '',
    sale_price: '',
    down_payment: '',
    monthly_profit_rate: '6.5',
    installment_count: 12,
    first_due_date: '',
    sale_date: localDate,
    notes: '',
});

const buyerSearch = ref('');

const goldRateLoading = ref(false);
const goldRateFound = ref(false);
const goldRateSource = ref(null);
const goldRateError = ref('');

const loadGoldRate = async (date) => {
    if (form.sale_type !== 'installment' || form.guarantee_type !== 'gold') {
        form.gold_rate_per_gram = '';
        goldRateFound.value = false;
        goldRateSource.value = null;
        goldRateError.value = '';
        return;
    }

    if (!date) {
        return;
    }

    goldRateLoading.value = true;
    goldRateError.value = '';

    try {
        const response = await fetch(
            route('sales.gold-rate', { date }),
            {
                headers: {
                    Accept: 'application/json',
                },
            }
        );

        if (!response.ok) {
            throw new Error('Gold rate request failed');
        }

        const data = await response.json();

        goldRateFound.value = Boolean(data.found);
        goldRateSource.value = data.source ?? null;

        form.gold_rate_per_gram =
            data.found && data.rate_per_gram
                ? String(data.rate_per_gram)
                : '';
    } catch (error) {
        goldRateFound.value = false;
        goldRateSource.value = null;
        form.gold_rate_per_gram = '';
        goldRateError.value =
            'دریافت نرخ طلا انجام نشد؛ نرخ هر گرم طلای ۱۸ عیار را دستی وارد کنید.';
    } finally {
        goldRateLoading.value = false;
    }
};

watch(
    [
        () => form.sale_date,
        () => form.sale_type,
        () => form.guarantee_type,
    ],
    ([date]) => {
        loadGoldRate(date);
    },
    { immediate: true }
);

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const normalize = (value) =>
    normalizeDigits(value).toLowerCase().trim();

const filteredContacts = computed(() => {
    const query = normalize(buyerSearch.value);

    let results = !query
        ? [...props.contacts]
        : props.contacts.filter((contact) =>
              normalize(`${contact.name} ${contact.mobile ?? ''}`).includes(query)
          );

    const selected = props.contacts.find(
        (contact) => String(contact.id) === String(form.buyer_id)
    );

    if (selected && !results.some((contact) => contact.id === selected.id)) {
        results.unshift(selected);
    }

    return results;
});

const selectedBuyer = computed(() =>
    props.contacts.find(
        (contact) => String(contact.id) === String(form.buyer_id)
    )
);

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

const formatPrice = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    if (!digits) return '';

    return Number(digits).toLocaleString('fa-IR');
};

const handleSalePrice = (event) => {
    form.sale_price = normalizeDigits(event.target.value).replace(/\D/g, '');
};

const handleGoldRate = (event) => {
    form.gold_rate_per_gram = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 10);
};

const handleGoldReceivedWeight = (event) => {
    let normalized = normalizeDigits(event.target.value)
        .replace(/٫/g, '.')
        .replace(/,/g, '.')
        .replace(/[^\d.]/g, '');

    const parts = normalized.split('.');
    const whole = parts.shift() ?? '';
    const decimal = (parts.join('') || '').slice(0, 4);

    form.gold_received_weight =
        decimal.length ? `${whole}.${decimal}` : whole;
};

const handleDownPayment = (event) => {
    form.down_payment = normalizeDigits(event.target.value).replace(/\D/g, '');
};

const handleMonthlyProfitRate = (event) => {
    let normalized = normalizeDigits(event.target.value)
        .replace(/٫/g, '.')
        .replace(/,/g, '.')
        .replace(/[^\d.]/g, '');

    const parts = normalized.split('.');
    const whole = parts.shift() ?? '';
    const decimal = (parts.join('') || '').slice(0, 1);

    normalized = decimal.length ? `${whole}.${decimal}` : whole;

    form.monthly_profit_rate = normalized;
};

const normalizeMonthlyProfitRate = () => {
    const value = Number(
        normalizeDigits(form.monthly_profit_rate)
            .replace(/٫/g, '.')
            .replace(/,/g, '.')
    );

    form.monthly_profit_rate = Number.isFinite(value)
        ? Math.min(100, Math.max(0, value)).toFixed(1)
        : '0.0';
};

const adjustMonthlyProfitRate = (delta) => {
    const current = Number(
        normalizeDigits(form.monthly_profit_rate)
            .replace(/٫/g, '.')
            .replace(/,/g, '.')
    ) || 0;

    const next = Math.min(
        100,
        Math.max(0, Math.round((current + delta) * 10) / 10)
    );

    form.monthly_profit_rate = next.toFixed(1);
};

const syncPickerDate = (field, value) => {
    if (!value) return;

    if (typeof value.format === 'function') {
        const date = value.clone ? value.clone() : value;

        if (typeof date.locale === 'function') {
            date.locale('en');
        }

        form[field] = normalizeDigits(date.format('YYYY-MM-DD'));
        return;
    }

    form[field] = normalizeDigits(String(value));
};

const handleInstallmentCount = (event) => {
    const normalized = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 2);

    form.installment_count =
        normalized === '' ? '' : Number(normalized);
};

const parseGregorianDate = (value) => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) return null;

    return {
        gy: Number(match[1]),
        gm: Number(match[2]),
        gd: Number(match[3]),
    };
};

const toUtcDay = ({ gy, gm, gd }) =>
    Date.UTC(gy, gm - 1, gd);

const addJalaliMonths = ({ jy, jm, jd }, count) => {
    const zeroBasedMonth = jm - 1 + count;
    const jyNext = jy + Math.floor(zeroBasedMonth / 12);
    const jmNext = ((zeroBasedMonth % 12) + 12) % 12 + 1;
    const jdNext = Math.min(jd, jalaaliMonthLength(jyNext, jmNext));

    return {
        jy: jyNext,
        jm: jmNext,
        jd: jdNext,
    };
};

const jalaliToGregorianParts = ({ jy, jm, jd }) =>
    toGregorian(jy, jm, jd);

const jalaliDateLabel = (date) => {
    if (!date) return '—';

    return toPersianDigits(
        `${date.jy}/${String(date.jm).padStart(2, '0')}/${String(date.jd).padStart(2, '0')}`
    );
};

const defermentBreakdown = computed(() => {
    const saleGregorian = parseGregorianDate(form.sale_date);
    const dueGregorian = parseGregorianDate(form.first_due_date);

    if (!saleGregorian || !dueGregorian) {
        return {
            ready: false,
            invalid: false,
            standardJalali: null,
            months: 0,
            days: 0,
            equivalentMonths: 0,
        };
    }

    const saleJalali = toJalaali(
        saleGregorian.gy,
        saleGregorian.gm,
        saleGregorian.gd
    );

    const standardJalali = addJalaliMonths(saleJalali, 1);
    const standardGregorian = jalaliToGregorianParts(standardJalali);

    const standardUtc = toUtcDay(standardGregorian);
    const dueUtc = toUtcDay(dueGregorian);

    if (dueUtc < standardUtc) {
        return {
            ready: true,
            invalid: true,
            standardJalali,
            months: 0,
            days: 0,
            equivalentMonths: 0,
        };
    }

    let months = 0;
    let cursor = standardJalali;

    while (true) {
        const next = addJalaliMonths(cursor, 1);
        const nextGregorian = jalaliToGregorianParts(next);

        if (toUtcDay(nextGregorian) > dueUtc) {
            break;
        }

        months++;
        cursor = next;
    }

    const cursorGregorian = jalaliToGregorianParts(cursor);
    const days = Math.floor(
        (dueUtc - toUtcDay(cursorGregorian)) / 86400000
    );

    return {
        ready: true,
        invalid: false,
        standardJalali,
        months,
        days,
        equivalentMonths: months + days / 30,
    };
});

const baseInstallmentProfit = computed(() => {
    if (form.sale_type !== 'installment') return 0;

    const count = Number(form.installment_count || 0);

    if (!installmentPrincipal.value || !count) return 0;

    return Math.round(
        installmentPrincipal.value *
        (monthlyProfitRate.value / 100) *
        count
    );
});

const defermentProfit = computed(() => {
    if (
        form.sale_type !== 'installment' ||
        !installmentPrincipal.value ||
        defermentBreakdown.value.invalid
    ) {
        return 0;
    }

    return Math.round(
        installmentPrincipal.value *
        (monthlyProfitRate.value / 100) *
        defermentBreakdown.value.equivalentMonths
    );
});

const installmentPrincipal = computed(() => {
    if (form.sale_type !== 'installment') return 0;

    const salePrice = Number(form.sale_price || 0);
    const downPayment = Number(form.down_payment || 0);

    return Math.max(0, salePrice - downPayment);
});

const monthlyProfitRate = computed(() => {
    const normalized = normalizeDigits(form.monthly_profit_rate)
        .replace(',', '.')
        .replace(/[^\d.]/g, '');

    return Number(normalized || 0);
});

const calculatedInstallmentProfit = computed(() =>
    baseInstallmentProfit.value + defermentProfit.value
);

const calculatedInstallmentTotal = computed(() =>
    installmentPrincipal.value + calculatedInstallmentProfit.value
);

const installmentAmount = computed(() => {
    const count = Number(form.installment_count || 0);

    if (!count || !calculatedInstallmentTotal.value) return 0;

    const rawAmount = calculatedInstallmentTotal.value / count;

    return Math.round(rawAmount / 10_000) * 10_000;
});

const installmentTotal = computed(() => {
    const count = Number(form.installment_count || 0);

    if (!count || !installmentAmount.value) return 0;

    return installmentAmount.value * count;
});

const installmentProfit = computed(() =>
    Math.max(0, installmentTotal.value - installmentPrincipal.value)
);

const contractTotal = computed(() => {
    if (form.sale_type !== 'installment') {
        return Number(form.sale_price || 0);
    }

    return Number(form.down_payment || 0) + installmentTotal.value;
});

const goldCoverageProfit = computed(() => {
    if (
        form.sale_type !== 'installment' ||
        form.guarantee_type !== 'gold'
    ) {
        return 0;
    }

    return Math.round(
        installmentPrincipal.value *
        (monthlyProfitRate.value / 100) *
        2
    );
});

const goldCoverageAmount = computed(
    () => installmentPrincipal.value + goldCoverageProfit.value
);

const goldRequiredWeight = computed(() => {
    const rate = Number(form.gold_rate_per_gram || 0);

    if (!rate || !goldCoverageAmount.value) return 0;

    return goldCoverageAmount.value / rate;
});

const formatWeight = (value) =>
    Number(value || 0).toLocaleString('fa-IR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });

const profit = computed(() => {
    const sale = Number(form.sale_price || 0);
    const purchase = Number(props.device.purchase_price || 0);

    if (!sale || !purchase) return null;

    return sale - purchase;
});

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const submit = () => {
    form.post(route('sales.store', props.device.id));
};
</script>

<template>
    <Head title="ثبت فروش | Panahi Car" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mx-auto max-w-4xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-[#2563eb]">
                            Panahi Car
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            {{ form.sale_type === 'cash' ? 'ثبت فروش نقدی' : 'ثبت فروش اقساطی' }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ device.brand }} {{ device.model }}
                            <span v-if="device.model_year">· {{ formatYear(device.model_year) }}</span>
                        </p>
                    </div>

                    <Link
                        :href="route('devices.show', device.id)"
                        class="rounded-2xl border border-slate-200/60 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-white/5 dark:bg-white/[0.035] dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <div class="mb-5 rounded-3xl bg-white p-5 shadow-sm dark:bg-white/[0.035]">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-xs text-slate-400">خودرو</p>
                            <p class="mt-1 font-black">
                                {{ device.brand }} {{ device.model }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">رنگ</p>
                            <p class="mt-1 font-bold">
                                {{ colorLabel(device.color) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">سال / کارکرد</p>
                            <p class="mt-1 font-bold">
                                {{ formatYear(device.model_year) }} · {{ formatMileage(device.mileage) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">VIN</p>
                            <p class="mt-1 font-bold" dir="ltr">
                                {{ device.vin || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">قیمت خرید</p>
                            <p class="mt-1 font-black">
                                {{ formatMoney(device.purchase_price) }} تومان
                            </p>
                        </div>
                    </div>
                </div>

                <form
                    class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-white/[0.035] sm:p-7"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                نوع فروش *
                            </label>

                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    class="rounded-2xl border px-4 py-4 font-black transition"
                                    :class="
                                        form.sale_type === 'cash'
                                            ? 'border-[#2563eb] bg-[#2563eb] text-white'
                                            : 'border-slate-200/60 bg-[#f7f8fa] text-slate-600 dark:border-white/10 dark:bg-white/[0.025] dark:text-slate-300'
                                    "
                                    @click="form.sale_type = 'cash'"
                                >
                                    فروش نقدی
                                </button>

                                <button
                                    type="button"
                                    class="rounded-2xl border px-4 py-4 font-black transition"
                                    :class="
                                        form.sale_type === 'installment'
                                            ? 'border-[#2563eb] bg-[#2563eb] text-white'
                                            : 'border-slate-200/60 bg-[#f7f8fa] text-slate-600 dark:border-white/10 dark:bg-white/[0.025] dark:text-slate-300'
                                    "
                                    @click="form.sale_type = 'installment'"
                                >
                                    فروش اقساطی
                                </button>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                خریدار *
                            </label>

                            <input
                                v-model="buyerSearch"
                                type="text"
                                placeholder="جستجو با نام یا موبایل"
                                class="w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                            />

                            <div
                                class="mt-3 max-h-52 space-y-2 overflow-y-auto rounded-2xl border border-slate-100 p-2 dark:border-white/5"
                            >
                                <button
                                    v-for="contact in filteredContacts"
                                    :key="contact.id"
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-xl px-3 py-3 text-right transition"
                                    :class="
                                        String(form.buyer_id) === String(contact.id)
                                            ? 'bg-[#2563eb] text-white'
                                            : 'hover:bg-[#f7f8fa] dark:hover:bg-slate-800'
                                    "
                                    @click="form.buyer_id = contact.id"
                                >
                                    <span class="font-bold">
                                        {{ contact.name }}
                                    </span>

                                    <span class="text-xs opacity-70" dir="ltr">
                                        {{ contact.mobile }}
                                    </span>
                                </button>
                            </div>

                            <div
                                v-if="selectedBuyer"
                                class="mt-3 rounded-2xl bg-[#eff6ff] p-4 dark:bg-[#2563eb]/[0.08]"
                            >
                                <p class="font-black">
                                    {{ selectedBuyer.name }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500" dir="ltr">
                                    {{ selectedBuyer.mobile }}
                                </p>
                            </div>

                            <p
                                v-if="form.errors.buyer_id"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.buyer_id }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                قیمت فروش *
                            </label>

                            <input
                                :value="formatPrice(form.sale_price)"
                                type="text"
                                inputmode="numeric"
                                placeholder="مثلاً ۱۳۵,۰۰۰,۰۰۰"
                                class="w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                @input="handleSalePrice"
                            />

                            <p
                                v-if="form.errors.sale_price"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.sale_price }}
                            </p>
                        </div>

                        <template v-if="form.sale_type === 'installment'">
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-bold">
                                    نوع ضمانت *
                                </label>

                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        class="rounded-2xl border px-4 py-4 text-right transition"
                                        :class="
                                            form.guarantee_type === 'check'
                                                ? 'border-[#2563eb] bg-[#eff6ff] text-[#1d4ed8] ring-2 ring-[#2563eb]/15 dark:bg-[#2563eb]/10 dark:text-[#93c5fd]'
                                                : 'border-slate-200/60 bg-[#f7f8fa] text-slate-600 dark:border-white/10 dark:bg-white/[0.025] dark:text-slate-300'
                                        "
                                        @click="form.guarantee_type = 'check'"
                                    >
                                        <span class="block font-black">
                                            ضمانت چک
                                        </span>
                                        <span class="mt-1 block text-xs opacity-70">
                                            مشخصات چک‌ها بعداً در بخش مطالبات ثبت می‌شود
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-2xl border px-4 py-4 text-right transition"
                                        :class="
                                            form.guarantee_type === 'gold'
                                                ? 'border-amber-400 bg-amber-50 text-amber-800 ring-2 ring-amber-400/15 dark:border-amber-400/30 dark:bg-amber-950/20 dark:text-amber-300'
                                                : 'border-slate-200/60 bg-[#f7f8fa] text-slate-600 dark:border-white/10 dark:bg-white/[0.025] dark:text-slate-300'
                                        "
                                        @click="form.guarantee_type = 'gold'"
                                    >
                                        <span class="block font-black">
                                            ضمانت طلا
                                        </span>
                                        <span class="mt-1 block text-xs opacity-70">
                                            وثیقه طلا به‌جای چک، با پوشش دو ماه سود
                                        </span>
                                    </button>
                                </div>

                                <p
                                    v-if="form.errors.guarantee_type"
                                    class="mt-2 text-xs font-bold text-red-500"
                                >
                                    {{ form.errors.guarantee_type }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    پیش‌پرداخت *
                                </label>

                                <input
                                    :value="formatPrice(form.down_payment)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۳۰,۰۰۰,۰۰۰"
                                    class="w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                    @input="handleDownPayment"
                                />

                                <p
                                    v-if="form.errors.down_payment"
                                    class="mt-2 text-xs font-bold text-red-500"
                                >
                                    {{ form.errors.down_payment }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    درصد سود ماهانه *
                                </label>

                                <div class="flex items-stretch gap-2">
                                    <button
                                        type="button"
                                        class="flex w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200/60 bg-[#f1f3f5] text-xl font-black transition hover:bg-slate-200 dark:border-white/10 dark:bg-white/[0.06] dark:hover:bg-slate-700"
                                        @click="adjustMonthlyProfitRate(-0.1)"
                                    >
                                        −
                                    </button>

                                    <div class="relative min-w-0 flex-1">
                                        <input
                                            :value="toPersianDigits(form.monthly_profit_rate)"
                                            type="text"
                                            inputmode="decimal"
                                            placeholder="۶.۵"
                                            class="w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] pl-12 text-center focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                            @input="handleMonthlyProfitRate"
                                            @blur="normalizeMonthlyProfitRate"
                                        />

                                        <span
                                            class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-400"
                                        >
                                            ٪
                                        </span>
                                    </div>

                                    <button
                                        type="button"
                                        class="flex w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200/60 bg-[#f1f3f5] text-xl font-black transition hover:bg-slate-200 dark:border-white/10 dark:bg-white/[0.06] dark:hover:bg-slate-700"
                                        @click="adjustMonthlyProfitRate(0.1)"
                                    >
                                        +
                                    </button>
                                </div>

                                <p class="mt-2 text-xs text-slate-400">
                                    پیش‌فرض ۶.۵٪ — برای هر فروش قابل تغییر
                                </p>

                                <p
                                    v-if="form.errors.monthly_profit_rate"
                                    class="mt-2 text-xs font-bold text-red-500"
                                >
                                    {{ form.errors.monthly_profit_rate }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    تعداد اقساط *
                                </label>

                                <input
                                    :value="toPersianDigits(form.installment_count)"
                                    type="text"
                                        inputmode="numeric"
                                    @input="handleInstallmentCount"
                                        class="w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                />

                                <p
                                    v-if="form.errors.installment_count"
                                    class="mt-2 text-xs font-bold text-red-500"
                                >
                                    {{ form.errors.installment_count }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-bold">
                                    اولین سررسید *
                                </label>

                                <Vue3PersianDatetimePicker
                                    v-model="form.first_due_date"
                                    format="YYYY-MM-DD"
                                    display-format="jYYYY/jMM/jDD"
                                    type="date"
                                    convert-numbers
                                    auto-submit
                                    @change="value => syncPickerDate('first_due_date', value)"
                                    custom-input=".first-due-date-input"
                                />

                                <input
                                    type="text"
                                    class="first-due-date-input w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                    placeholder="تاریخ اولین قسط"
                                    readonly
                                />

                                <p
                                    v-if="form.errors.first_due_date"
                                    class="mt-2 text-xs font-bold text-red-500"
                                >
                                    {{ form.errors.first_due_date }}
                                </p>

                                <div
                                    v-if="defermentBreakdown.ready"
                                    class="mt-3 rounded-2xl border p-4"
                                    :class="
                                        defermentBreakdown.invalid
                                            ? 'border-red-200 bg-red-50 dark:border-red-900/60 dark:bg-red-950/20'
                                            : defermentBreakdown.months || defermentBreakdown.days
                                                ? 'border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/20'
                                                : 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/20'
                                    "
                                >
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        شروع استاندارد اقساط:
                                        <strong class="text-slate-700 dark:text-slate-200">
                                            {{ jalaliDateLabel(defermentBreakdown.standardJalali) }}
                                        </strong>
                                    </p>

                                    <p
                                        v-if="defermentBreakdown.invalid"
                                        class="mt-2 text-sm font-black text-red-600 dark:text-red-400"
                                    >
                                        اولین قسط باید حداقل یک ماه شمسی بعد از تاریخ فروش باشد.
                                    </p>

                                    <template
                                        v-else-if="defermentBreakdown.months || defermentBreakdown.days"
                                    >
                                        <p class="mt-2 text-sm font-black text-amber-700 dark:text-amber-300">
                                            تنفس اضافه:
                                            <span v-if="defermentBreakdown.months">
                                                {{ toPersianDigits(defermentBreakdown.months) }} ماه
                                            </span>
                                            <span
                                                v-if="defermentBreakdown.months && defermentBreakdown.days"
                                            >
                                                و
                                            </span>
                                            <span v-if="defermentBreakdown.days">
                                                {{ toPersianDigits(defermentBreakdown.days) }} روز
                                            </span>
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-amber-600 dark:text-amber-400">
                                            سود زمان اضافه:
                                            {{ formatMoney(defermentProfit) }} تومان
                                        </p>
                                    </template>

                                    <p
                                        v-else
                                        class="mt-2 text-sm font-black text-emerald-700 dark:text-emerald-300"
                                    >
                                        بدون تنفس اضافه
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="form.guarantee_type === 'gold'"
                                class="sm:col-span-2 rounded-[26px] border border-amber-200/70 bg-amber-50/70 p-5 dark:border-amber-400/10 dark:bg-amber-950/15"
                            >
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-black text-amber-800 dark:text-amber-300">
                                            محاسبه ضمانت طلا
                                        </p>
                                        <p class="mt-1 text-xs leading-6 text-amber-700/70 dark:text-amber-300/70">
                                            اصل مانده بدهی + سود دو ماه با نرخ همین قرارداد
                                        </p>
                                    </div>

                                    <span
                                        class="w-fit rounded-full bg-white px-3 py-1.5 text-[10px] font-black text-amber-700 shadow-sm dark:bg-white/5 dark:text-amber-300"
                                    >
                                        مبنا: طلای ۱۸ عیار
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">
                                            اصل مانده بدهی
                                        </p>
                                        <p class="mt-1 font-black">
                                            {{ formatMoney(installmentPrincipal) }} تومان
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">
                                            پوشش دو ماه سود
                                        </p>
                                        <p class="mt-1 font-black text-amber-700 dark:text-amber-300">
                                            +{{ formatMoney(goldCoverageProfit) }} تومان
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-white/80 p-4 dark:bg-white/[0.035]">
                                        <p class="text-[10px] text-slate-400">
                                            مبلغ تحت پوشش
                                        </p>
                                        <p class="mt-1 font-black">
                                            {{ formatMoney(goldCoverageAmount) }} تومان
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-amber-500 p-4 text-white">
                                        <p class="text-[10px] font-bold opacity-80">
                                            حداقل وزن لازم
                                        </p>
                                        <p class="mt-1 text-lg font-black">
                                            {{ formatWeight(goldRequiredWeight) }} گرم
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-bold">
                                            نرخ هر گرم طلای ۱۸ عیار *
                                        </label>

                                        <div class="relative">
                                            <input
                                                :value="formatPrice(form.gold_rate_per_gram)"
                                                type="text"
                                                inputmode="numeric"
                                                :readonly="goldRateFound || goldRateLoading"
                                                :placeholder="
                                                    goldRateLoading
                                                        ? 'در حال دریافت نرخ...'
                                                        : goldRateFound
                                                            ? ''
                                                            : 'نرخ هر گرم را دستی وارد کنید'
                                                "
                                                class="w-full rounded-2xl border-amber-200 bg-white pl-16 focus:border-amber-500 focus:ring-amber-500/20 read-only:cursor-default dark:border-amber-400/10 dark:bg-white/[0.035]"
                                                @input="handleGoldRate"
                                            />

                                            <span
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400"
                                            >
                                                تومان
                                            </span>
                                        </div>

                                        <p
                                            v-if="goldRateLoading"
                                            class="mt-2 text-xs font-bold text-slate-400"
                                        >
                                            در حال دریافت نرخ طلای این تاریخ...
                                        </p>

                                        <p
                                            v-else-if="goldRateFound"
                                            class="mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-400"
                                        >
                                            نرخ طلای ۱۸ عیار · منبع: نوسان
                                        </p>

                                        <p
                                            v-else
                                            class="mt-2 text-xs font-bold text-amber-700 dark:text-amber-300"
                                        >
                                            نرخ خودکار در دسترس نیست؛ نرخ همان روز را دستی وارد کنید.
                                        </p>

                                        <p
                                            v-if="goldRateError"
                                            class="mt-2 text-xs font-bold text-red-500"
                                        >
                                            {{ goldRateError }}
                                        </p>

                                        <p
                                            v-if="form.errors.gold_rate_per_gram"
                                            class="mt-2 text-xs font-bold text-red-500"
                                        >
                                            {{ form.errors.gold_rate_per_gram }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold">
                                            وزن واقعی طلای تحویلی *
                                        </label>

                                        <div class="relative">
                                            <input
                                                :value="toPersianDigits(form.gold_received_weight)"
                                                type="text"
                                                inputmode="decimal"
                                                placeholder="مثلاً ۶"
                                                class="w-full rounded-2xl border-amber-200 bg-white pl-14 focus:border-amber-500 focus:ring-amber-500/20 dark:border-amber-400/10 dark:bg-white/[0.035]"
                                                @input="handleGoldReceivedWeight"
                                            />

                                            <span
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400"
                                            >
                                                گرم
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs text-slate-400">
                                            باید حداقل
                                            <strong class="text-amber-700 dark:text-amber-300">
                                                {{ formatWeight(goldRequiredWeight) }} گرم
                                            </strong>
                                            باشد.
                                        </p>

                                        <p
                                            v-if="form.errors.gold_received_weight"
                                            class="mt-2 text-xs font-bold text-red-500"
                                        >
                                            {{ form.errors.gold_received_weight }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold">
                                            نوع طلای دریافتی *
                                        </label>

                                        <input
                                            v-model="form.gold_type"
                                            type="text"
                                            maxlength="100"
                                            placeholder="مثلاً دستبند، النگو، زنجیر..."
                                            class="w-full rounded-2xl border-amber-200 bg-white focus:border-amber-500 focus:ring-amber-500/20 dark:border-amber-400/10 dark:bg-white/[0.035]"
                                        />

                                        <p
                                            v-if="form.errors.gold_type"
                                            class="mt-2 text-xs font-bold text-red-500"
                                        >
                                            {{ form.errors.gold_type }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-bold">
                                            توضیحات طلای تحویلی
                                        </label>

                                        <input
                                            v-model="form.gold_description"
                                            type="text"
                                            maxlength="10000"
                                            placeholder="مثلاً تعداد قطعات، مشخصات ظاهری یا توضیح تکمیلی"
                                            class="w-full rounded-2xl border-amber-200 bg-white focus:border-amber-500 focus:ring-amber-500/20 dark:border-amber-400/10 dark:bg-white/[0.035]"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="sm:col-span-2 grid gap-3 rounded-2xl bg-[#eff6ff] p-4 dark:bg-[#2563eb]/[0.08] sm:grid-cols-2 lg:grid-cols-6"
                            >
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        مانده پس از پیش‌پرداخت
                                    </p>
                                    <p class="mt-1 font-black">
                                        {{ formatMoney(installmentPrincipal) }} تومان
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        تعداد اقساط
                                    </p>
                                    <p class="mt-1 font-black">
                                        {{ toPersianDigits(form.installment_count || 0) }} قسط
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        مبلغ هر قسط
                                    </p>
                                    <p class="mt-1 font-black text-[#1d4ed8] dark:text-[#93c5fd]">
                                        {{ formatMoney(installmentAmount) }} تومان
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        مجموع اقساط
                                    </p>
                                    <p class="mt-1 font-black text-[#1d4ed8] dark:text-[#93c5fd]">
                                        {{ formatMoney(installmentTotal) }} تومان
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        سود کل اقساط
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-400">
                                        سود پایه:
                                        {{ formatMoney(baseInstallmentProfit) }}
                                        <span v-if="defermentProfit">
                                            + تنفس: {{ formatMoney(defermentProfit) }}
                                        </span>
                                    </p>
                                    <p class="mt-1 font-black text-amber-600 dark:text-amber-400">
                                        {{ formatMoney(installmentProfit) }} تومان
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        مبلغ نهایی قرارداد
                                    </p>
                                    <p class="mt-1 font-black text-emerald-600 dark:text-emerald-400">
                                        {{ formatMoney(contractTotal) }} تومان
                                    </p>
                                </div>
                            </div>
                        </template>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                تاریخ فروش *
                            </label>

                            <Vue3PersianDatetimePicker
                                v-model="form.sale_date"
                                :initial-value="localDate"
                                format="YYYY-MM-DD"
                                display-format="jYYYY/jMM/jDD"
                                type="date"
                                    convert-numbers
                                auto-submit
                                    @change="value => syncPickerDate('sale_date', value)"
                                custom-input=".sale-date-input"
                            />

                            <input
                                type="text"
                                class="sale-date-input w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                placeholder="تاریخ فروش"
                                readonly
                            />

                            <p
                                v-if="form.errors.sale_date"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.sale_date }}
                            </p>
                        </div>

                        <div
                            v-if="profit !== null"
                            class="sm:col-span-2 rounded-2xl p-4"
                            :class="
                                profit >= 0
                                    ? 'bg-emerald-50 dark:bg-emerald-950/30'
                                    : 'bg-red-50 dark:bg-red-950/30'
                            "
                        >
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                سود / زیان این فروش
                            </p>

                            <div class="mt-1 flex items-center gap-3 whitespace-nowrap overflow-x-auto">
                                <span
                                    class="text-xl font-black"
                                    :class="
                                        profit >= 0
                                            ? 'text-emerald-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ profit >= 0 ? '+' : '' }}{{ formatMoney(profit) }} تومان
                                </span>

                                <template v-if="form.sale_type === 'installment'">
                                    <span class="text-lg font-black text-slate-400">+</span>

                                    <span class="font-black text-amber-600 dark:text-amber-400">
                                        {{ formatMoney(installmentProfit) }} تومان سود فروش قسطی
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                توضیحات
                            </label>

                            <textarea
                                v-model="form.notes"
                                rows="4"
                                class="w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                placeholder="اختیاری"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-7 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-[#2563eb] px-6 py-3 text-sm font-black text-white transition hover:bg-[#1d4ed8] disabled:opacity-50"
                        >
                            {{ form.sale_type === 'cash' ? 'ثبت فروش نقدی' : 'ثبت فروش اقساطی' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
