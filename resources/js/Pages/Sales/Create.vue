<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { toJalaali, toGregorian, jalaaliMonthLength } from 'jalaali-js';

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
    sale_price: '',
    down_payment: '',
    monthly_profit_rate: '6.5',
    installment_count: 12,
    first_due_date: '',
    sale_date: '',
    notes: '',
});

const buyerSearch = ref('');

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

const installmentProfit = computed(() =>
    baseInstallmentProfit.value + defermentProfit.value
);

const installmentTotal = computed(() =>
    installmentPrincipal.value + installmentProfit.value
);

const contractTotal = computed(() => {
    if (form.sale_type !== 'installment') {
        return Number(form.sale_price || 0);
    }

    return Number(form.down_payment || 0) + installmentTotal.value;
});

const installmentAmount = computed(() => {
    const count = Number(form.installment_count || 0);

    if (!count || !installmentTotal.value) return 0;

    return Math.floor(installmentTotal.value / count);
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
    <Head title="ثبت فروش | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-4xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">
                            مایاهمراه
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            {{ form.sale_type === 'cash' ? 'ثبت فروش نقدی' : 'ثبت فروش اقساطی' }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ device.brand }} {{ device.model }}
                            <span v-if="device.storage">· {{ device.storage }}</span>
                        </p>
                    </div>

                    <Link
                        :href="route('devices.show', device.id)"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <div class="mb-5 rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-900">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-xs text-slate-400">دستگاه</p>
                            <p class="mt-1 font-black">
                                {{ device.brand }} {{ device.model }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">رنگ</p>
                            <p class="mt-1 font-bold">
                                {{ device.color || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">IMEI</p>
                            <p class="mt-1 font-bold" dir="ltr">
                                {{ device.imei || '—' }}
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
                    class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
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
                                            ? 'border-violet-600 bg-violet-600 text-white'
                                            : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300'
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
                                            ? 'border-violet-600 bg-violet-600 text-white'
                                            : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300'
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
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                            />

                            <div
                                class="mt-3 max-h-52 space-y-2 overflow-y-auto rounded-2xl border border-slate-100 p-2 dark:border-slate-800"
                            >
                                <button
                                    v-for="contact in filteredContacts"
                                    :key="contact.id"
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-xl px-3 py-3 text-right transition"
                                    :class="
                                        String(form.buyer_id) === String(contact.id)
                                            ? 'bg-violet-600 text-white'
                                            : 'hover:bg-slate-50 dark:hover:bg-slate-800'
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
                                class="mt-3 rounded-2xl bg-violet-50 p-4 dark:bg-violet-950/30"
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
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
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
                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    پیش‌پرداخت *
                                </label>

                                <input
                                    :value="formatPrice(form.down_payment)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۳۰,۰۰۰,۰۰۰"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
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
                                        class="flex w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 text-xl font-black transition hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700"
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
                                            class="w-full rounded-2xl border-slate-200 bg-slate-50 pl-12 text-center focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
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
                                        class="flex w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 text-xl font-black transition hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700"
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
                                        class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
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
                                    class="first-due-date-input w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
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
                                        اولین چک باید حداقل یک ماه شمسی بعد از تاریخ فروش باشد.
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
                                class="sm:col-span-2 grid gap-3 rounded-2xl bg-violet-50 p-4 dark:bg-violet-950/30 sm:grid-cols-2 lg:grid-cols-6"
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
                                        مبلغ تقریبی هر قسط
                                    </p>
                                    <p class="mt-1 font-black text-violet-700 dark:text-violet-300">
                                        {{ formatMoney(installmentAmount) }} تومان
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        مجموع اقساط
                                    </p>
                                    <p class="mt-1 font-black text-violet-700 dark:text-violet-300">
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
                                class="sale-date-input w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
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
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                placeholder="اختیاری"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-7 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-violet-600 px-6 py-3 text-sm font-black text-white transition hover:bg-violet-700 disabled:opacity-50"
                        >
                            {{ form.sale_type === 'cash' ? 'ثبت فروش نقدی' : 'ثبت فروش اقساطی' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
