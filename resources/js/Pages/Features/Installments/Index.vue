<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { toJalaali, toGregorian, jalaaliMonthLength } from 'jalaali-js';

const mode = ref('regular');
const loading = ref(false);
const result = ref(null);
const available = ref(true);
const generalError = ref('');
const errors = ref({});

const form = ref({
    sale_price: '',
    down_payment: '',
    monthly_profit_rate: '6.5',
    installment_count: 12,
    monthly_cap: '',
    sale_date: '',
    first_due_date: '',
});

const customPayments = ref([
    {
        due_date: '',
        amount: '',
    },
]);

const modes = [
    {
        key: 'regular',
        title: 'اقساط منظم',
        subtitle: 'چک‌های مساوی و سررسید ماهانه',
    },
    {
        key: 'monthly_cap',
        title: 'سقف پرداخت',
        subtitle: 'تعداد اقساط مناسب را پیدا کن',
    },
    {
        key: 'custom',
        title: 'چک نامنظم',
        subtitle: 'مبلغ و تاریخ متفاوت برای هر چک',
    },
];

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const formatJalaliDate = (value) => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) return '—';

    const jalali = toJalaali(
        Number(match[1]),
        Number(match[2]),
        Number(match[3])
    );

    return toPersianDigits(
        `${jalali.jy}/${String(jalali.jm).padStart(2, '0')}/${String(jalali.jd).padStart(2, '0')}`
    );
};

const formatPriceInput = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    return digits ? Number(digits).toLocaleString('fa-IR') : '';
};

const setMoneyField = (field, event) => {
    form.value[field] = normalizeDigits(event.target.value).replace(/\D/g, '');
};

const setProfitRate = (event) => {
    let normalized = normalizeDigits(event.target.value)
        .replace(/٫/g, '.')
        .replace(/,/g, '.')
        .replace(/[^\d.]/g, '');

    const parts = normalized.split('.');
    const whole = parts.shift() ?? '';
    const decimal = (parts.join('') || '').slice(0, 1);

    form.value.monthly_profit_rate = decimal.length
        ? `${whole}.${decimal}`
        : whole;
};

const normalizeProfitRate = () => {
    const value = Number(
        normalizeDigits(form.value.monthly_profit_rate)
            .replace(/٫/g, '.')
            .replace(/,/g, '.')
    );

    form.value.monthly_profit_rate = Number.isFinite(value)
        ? Math.min(100, Math.max(0, value)).toFixed(1)
        : '0.0';
};

const setInstallmentCount = (event) => {
    const normalized = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 2);

    form.value.installment_count =
        normalized === '' ? '' : Number(normalized);
};

const setCustomPaymentAmount = (index, event) => {
    customPayments.value[index].amount = normalizeDigits(event.target.value)
        .replace(/\D/g, '');
};

const addCustomPayment = () => {
    customPayments.value.push({
        due_date: '',
        amount: '',
    });
};

const removeCustomPayment = (index) => {
    if (customPayments.value.length === 1) {
        customPayments.value[0] = {
            due_date: '',
            amount: '',
        };
        return;
    }

    customPayments.value.splice(index, 1);
};

const standardFirstDueDate = (saleDate) => {
    const match = String(saleDate || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) return '';

    const jalali = toJalaali(
        Number(match[1]),
        Number(match[2]),
        Number(match[3])
    );

    const zeroBasedMonth = jalali.jm;
    const nextYear = jalali.jy + Math.floor(zeroBasedMonth / 12);
    const nextMonth = (zeroBasedMonth % 12) + 1;
    const nextDay = Math.min(
        jalali.jd,
        jalaaliMonthLength(nextYear, nextMonth)
    );

    const gregorian = toGregorian(
        nextYear,
        nextMonth,
        nextDay
    );

    return [
        gregorian.gy,
        String(gregorian.gm).padStart(2, '0'),
        String(gregorian.gd).padStart(2, '0'),
    ].join('-');
};

const normalizePickerValue = (value) => {
    if (!value) return '';

    if (typeof value.format === 'function') {
        const date = value.clone ? value.clone() : value;

        if (typeof date.locale === 'function') {
            date.locale('en');
        }

        return normalizeDigits(date.format('YYYY-MM-DD'));
    }

    return normalizeDigits(String(value));
};

const syncPickerDate = (field, value) => {
    const normalizedValue = normalizePickerValue(value);

    if (!normalizedValue) return;

    form.value[field] = normalizedValue;

    if (field === 'sale_date') {
        form.value.first_due_date = standardFirstDueDate(normalizedValue);
    }
};

const syncCustomPaymentDate = (index, value) => {
    const normalizedValue = normalizePickerValue(value);

    if (!normalizedValue) return;

    customPayments.value[index].due_date = normalizedValue;
};

const selectMode = (nextMode) => {
    mode.value = nextMode;
    result.value = null;
    available.value = true;
    errors.value = {};
    generalError.value = '';
};

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const requestPayload = computed(() => {
    const payload = {
        mode: mode.value,
        sale_price: Number(form.value.sale_price || 0),
        down_payment: Number(form.value.down_payment || 0),
        monthly_profit_rate: Number(form.value.monthly_profit_rate || 0),
        sale_date: form.value.sale_date,
    };

    if (mode.value === 'custom') {
        return {
            ...payload,
            payments: customPayments.value.map((payment) => ({
                due_date: payment.due_date,
                amount: Number(payment.amount || 0),
            })),
        };
    }

    return {
        ...payload,
        installment_count:
            mode.value === 'regular'
                ? Number(form.value.installment_count || 0)
                : null,
        monthly_cap:
            mode.value === 'monthly_cap'
                ? Number(form.value.monthly_cap || 0)
                : null,
        first_due_date: form.value.first_due_date,
    };
});

const calculate = async () => {
    result.value = null;
    errors.value = {};
    generalError.value = '';
    available.value = true;

    loading.value = true;

    try {
        const response = await fetch(
            route('features.installments.calculate'),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(requestPayload.value),
            }
        );

        const data = await response.json();

        if (response.status === 422) {
            errors.value = data.errors ?? {};
            generalError.value =
                data.message ?? 'اطلاعات واردشده معتبر نیست.';
            return;
        }

        if (!response.ok) {
            throw new Error('Installment calculation failed');
        }

        available.value = data.available !== false;
        result.value = data.result ?? null;

        if (!available.value) {
            generalError.value =
                'با این سقف پرداخت، در محدوده فعلی مایاهمراه تا ۶۰ قسط نتیجه‌ای پیدا نشد.';
        }
    } catch (error) {
        generalError.value =
            'محاسبه انجام نشد. اتصال را بررسی کنید و دوباره تلاش کنید.';
    } finally {
        loading.value = false;
    }
};

const fieldError = (field) => errors.value[field]?.[0] ?? '';
</script>

<template>
    <Head title="ماشین حساب اقساط | مایاهمراه" />

    <div dir="rtl" class="calculator-page">
        <div class="page-shell">
            <div class="shell-background" />

            <header class="topbar">
                <div class="topbar-left">
                    <Link
                        :href="route('features.index')"
                        class="circle-btn"
                        aria-label="بازگشت به امکانات"
                    >
                        ←
                    </Link>

                    <div class="title-wrap">
                        <strong>ماشین حساب اقساط</strong>
                        <small>Maya Installments</small>
                    </div>
                </div>

                <span class="tool-badge">عمومی</span>
            </header>

            <main class="main-grid">
                <section class="intro-card reveal">
                    <div>
                        <small class="eyebrow">محاسبه با منطق مالی مایاهمراه</small>
                        <h1>
                            قسط را قبل از
                            <span>فروش</span>
                            دقیق ببین
                        </h1>
                        <p>
                            مبلغ چک، سود، تنفس و جمع نهایی قرارداد با همان
                            قواعدی محاسبه می‌شود که در فروش واقعی مایاهمراه
                            استفاده می‌شود.
                        </p>
                    </div>

                    <div class="intro-pill">
                        <span>گرد کردن چک</span>
                        <strong>۱۰٬۰۰۰ تومان</strong>
                    </div>
                </section>

                <section class="mode-strip reveal delay-1">
                    <button
                        v-for="item in modes"
                        :key="item.key"
                        type="button"
                        class="mode-card"
                        :class="{
                            active: mode === item.key,
                            pending: item.key === 'custom',
                        }"
                        @click="selectMode(item.key)"
                    >
                        <span class="mode-dot" />

                        <div>
                            <strong>{{ item.title }}</strong>
                            <small>{{ item.subtitle }}</small>
                        </div>

                        <span
                            v-if="item.key === 'custom'"
                            class="soon-badge"
                        >
                            در حال تعریف
                        </span>
                    </button>
                </section>

                <section class="workspace-grid">
                    <article class="form-card reveal delay-1">
                        <div class="section-head">
                            <div>
                                <small>ورودی‌ها</small>
                                <h2>
                                    {{
                                        mode === 'regular'
                                            ? 'اقساط منظم'
                                            : mode === 'monthly_cap'
                                                ? 'سقف پرداخت ماهانه'
                                                : 'چک‌های نامنظم'
                                    }}
                                </h2>
                            </div>

                            <span class="step-badge">01</span>
                        </div>

                        <div
                            v-if="mode === 'custom'"
                            class="custom-notice"
                        >
                            <strong>مبلغ چک همان توان پرداخت مشتری است</strong>
                            <p>
                                سود هر فاصله روی مانده همان لحظه محاسبه و به
                                مانده اضافه می‌شود؛ سپس مبلغ چک از آن کم
                                می‌شود.
                            </p>
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label>قیمت فروش</label>
                                <div class="money-input">
                                    <input
                                        :value="formatPriceInput(form.sale_price)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="۳۰۰,۰۰۰,۰۰۰"
                                        @input="setMoneyField('sale_price', $event)"
                                    >
                                    <span>تومان</span>
                                </div>
                                <small v-if="fieldError('sale_price')" class="error-text">
                                    {{ fieldError('sale_price') }}
                                </small>
                            </div>

                            <div class="field">
                                <label>پیش‌پرداخت</label>
                                <div class="money-input">
                                    <input
                                        :value="formatPriceInput(form.down_payment)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="۱۰۰,۰۰۰,۰۰۰"
                                        @input="setMoneyField('down_payment', $event)"
                                    >
                                    <span>تومان</span>
                                </div>
                                <small v-if="fieldError('down_payment')" class="error-text">
                                    {{ fieldError('down_payment') }}
                                </small>
                            </div>

                            <div class="field">
                                <label>سود ماهانه</label>
                                <div class="money-input">
                                    <input
                                        :value="toPersianDigits(form.monthly_profit_rate)"
                                        type="text"
                                        inputmode="decimal"
                                        placeholder="۶.۵"
                                        @input="setProfitRate"
                                        @blur="normalizeProfitRate"
                                    >
                                    <span>٪</span>
                                </div>
                                <small v-if="fieldError('monthly_profit_rate')" class="error-text">
                                    {{ fieldError('monthly_profit_rate') }}
                                </small>
                            </div>

                            <div
                                v-if="mode === 'regular'"
                                class="field"
                            >
                                <label>تعداد اقساط</label>
                                <div class="money-input">
                                    <input
                                        :value="toPersianDigits(form.installment_count)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="۱۲"
                                        @input="setInstallmentCount"
                                    >
                                    <span>قسط</span>
                                </div>
                                <small v-if="fieldError('installment_count')" class="error-text">
                                    {{ fieldError('installment_count') }}
                                </small>
                            </div>

                            <div
                                v-if="mode === 'monthly_cap'"
                                class="field"
                            >
                                <label>حداکثر مبلغ هر چک</label>
                                <div class="money-input">
                                    <input
                                        :value="formatPriceInput(form.monthly_cap)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="۸۰,۰۰۰,۰۰۰"
                                        @input="setMoneyField('monthly_cap', $event)"
                                    >
                                    <span>تومان</span>
                                </div>
                                <small v-if="fieldError('monthly_cap')" class="error-text">
                                    {{ fieldError('monthly_cap') }}
                                </small>
                            </div>

                            <div class="field">
                                <label>تاریخ فروش</label>

                                <Vue3PersianDatetimePicker
                                    v-model="form.sale_date"
                                    format="YYYY-MM-DD"
                                    display-format="jYYYY/jMM/jDD"
                                    type="date"
                                    convert-numbers
                                    auto-submit
                                    custom-input=".public-sale-date-input"
                                    @change="value => syncPickerDate('sale_date', value)"
                                />

                                <input
                                    type="text"
                                    class="date-input public-sale-date-input"
                                    placeholder="تاریخ فروش"
                                    readonly
                                >

                                <small v-if="fieldError('sale_date')" class="error-text">
                                    {{ fieldError('sale_date') }}
                                </small>
                            </div>

                            <div
                                v-if="mode !== 'custom'"
                                class="field"
                            >
                                <label>اولین سررسید</label>

                                <Vue3PersianDatetimePicker
                                    v-model="form.first_due_date"
                                    format="YYYY-MM-DD"
                                    display-format="jYYYY/jMM/jDD"
                                    type="date"
                                    convert-numbers
                                    auto-submit
                                    custom-input=".public-first-due-date-input"
                                    @change="value => syncPickerDate('first_due_date', value)"
                                />

                                <input
                                    type="text"
                                    class="date-input public-first-due-date-input"
                                    placeholder="حداقل یک ماه شمسی بعد"
                                    readonly
                                >

                                <small v-if="fieldError('first_due_date')" class="error-text">
                                    {{ fieldError('first_due_date') }}
                                </small>
                            </div>
                        </div>

                        <div
                            v-if="mode === 'custom'"
                            class="custom-payments"
                        >
                            <div class="custom-payments-head">
                                <div>
                                    <strong>چک‌های مشتری</strong>
                                    <small>تاریخ و مبلغ واقعی هر پرداخت را وارد کن</small>
                                </div>

                                <button
                                    type="button"
                                    class="add-payment-btn"
                                    @click="addCustomPayment"
                                >
                                    + افزودن چک
                                </button>
                            </div>

                            <div class="custom-payment-list">
                                <article
                                    v-for="(payment, index) in customPayments"
                                    :key="index"
                                    class="custom-payment-row"
                                >
                                    <span class="payment-index">
                                        {{ toPersianDigits(index + 1) }}
                                    </span>

                                    <div class="field">
                                        <label>تاریخ چک</label>

                                        <Vue3PersianDatetimePicker
                                            v-model="payment.due_date"
                                            format="YYYY-MM-DD"
                                            display-format="jYYYY/jMM/jDD"
                                            type="date"
                                            convert-numbers
                                            auto-submit
                                            :custom-input="`.custom-payment-date-${index}`"
                                            @change="value => syncCustomPaymentDate(index, value)"
                                        />

                                        <input
                                            type="text"
                                            :class="[
                                                'date-input',
                                                `custom-payment-date-${index}`,
                                            ]"
                                            placeholder="تاریخ سررسید"
                                            readonly
                                        >

                                        <small
                                            v-if="fieldError(`payments.${index}.due_date`)"
                                            class="error-text"
                                        >
                                            {{ fieldError(`payments.${index}.due_date`) }}
                                        </small>
                                    </div>

                                    <div class="field">
                                        <label>مبلغ چک</label>

                                        <div class="money-input">
                                            <input
                                                :value="formatPriceInput(payment.amount)"
                                                type="text"
                                                inputmode="numeric"
                                                placeholder="۵۰,۰۰۰,۰۰۰"
                                                @input="setCustomPaymentAmount(index, $event)"
                                            >
                                            <span>تومان</span>
                                        </div>

                                        <small
                                            v-if="fieldError(`payments.${index}.amount`)"
                                            class="error-text"
                                        >
                                            {{ fieldError(`payments.${index}.amount`) }}
                                        </small>
                                    </div>

                                    <button
                                        type="button"
                                        class="remove-payment-btn"
                                        aria-label="حذف چک"
                                        @click="removeCustomPayment(index)"
                                    >
                                        ×
                                    </button>
                                </article>
                            </div>

                            <small
                                v-if="fieldError('payments')"
                                class="error-text"
                            >
                                {{ fieldError('payments') }}
                            </small>
                        </div>

                        <div
                            v-if="generalError"
                            class="message-box"
                            :class="{ warning: mode === 'custom' || !available }"
                        >
                            {{ generalError }}
                        </div>

                        <button
                            type="button"
                            class="calculate-btn"
                            :disabled="loading"
                            @click="calculate"
                        >
                            <span>
                                {{
                                    loading
                                        ? 'در حال محاسبه...'
                                        : mode === 'monthly_cap'
                                            ? 'پیدا کردن بهترین تعداد قسط'
                                            : mode === 'custom'
                                                ? 'محاسبه چک‌های نامنظم'
                                                : 'محاسبه اقساط'
                                }}
                            </span>
                            <span class="btn-arrow">←</span>
                        </button>
                    </article>

                    <aside class="result-card reveal delay-2">
                        <div class="section-head result-head">
                            <div>
                                <small>خروجی</small>
                                <h2>نتیجه محاسبه</h2>
                            </div>

                            <span class="step-badge mint">02</span>
                        </div>

                        <div
                            v-if="!result"
                            class="empty-result"
                        >
                            <span class="empty-orbit">
                                <span />
                            </span>
                            <strong>
                                هنوز محاسبه‌ای انجام نشده
                            </strong>
                            <p>
                                {{
                                    mode === 'custom'
                                        ? 'چک‌ها را وارد کن تا سود هر بازه و مانده بعد از هر پرداخت نمایش داده شود.'
                                        : 'ورودی‌ها را کامل کن تا مبلغ واقعی چک‌ها و جمع قرارداد نمایش داده شود.'
                                }}
                            </p>
                        </div>

                        <template v-else-if="mode === 'custom'">
                            <div class="hero-result">
                                <small>مانده پس از آخرین چک</small>

                                <strong>
                                    {{ formatMoney(result.remaining_balance) }}
                                </strong>

                                <span>تومان</span>

                                <p>
                                    مبلغ چک‌ها تغییر نکرده؛ سود هر بازه روی
                                    مانده قبلی محاسبه شده است.
                                </p>
                            </div>

                            <div class="result-metrics">
                                <div>
                                    <span>اصل اولیه</span>
                                    <strong>{{ formatMoney(result.principal) }}</strong>
                                </div>

                                <div>
                                    <span>جمع پرداخت‌ها</span>
                                    <strong>{{ formatMoney(result.total_paid) }}</strong>
                                </div>

                                <div>
                                    <span>سود انباشته</span>
                                    <strong>{{ formatMoney(result.total_profit) }}</strong>
                                </div>

                                <div>
                                    <span>تعداد چک‌ها</span>
                                    <strong>
                                        {{ toPersianDigits(result.payments.length) }}
                                        چک
                                    </strong>
                                </div>

                                <div class="wide">
                                    <span>مانده فعلی</span>
                                    <strong class="green">
                                        {{ formatMoney(result.remaining_balance) }}
                                        تومان
                                    </strong>
                                </div>
                            </div>

                            <div class="schedule-card">
                                <div class="schedule-head">
                                    <strong>گردش مانده</strong>
                                    <span>
                                        {{ toPersianDigits(result.payments.length) }}
                                        پرداخت
                                    </span>
                                </div>

                                <div class="schedule-list custom-schedule-list">
                                    <div
                                        v-for="payment in result.payments"
                                        :key="payment.installment_number"
                                        class="schedule-row custom-result-row"
                                    >
                                        <span class="schedule-number">
                                            {{ toPersianDigits(payment.installment_number) }}
                                        </span>

                                        <div>
                                            <small>سررسید</small>
                                            <strong>{{ formatJalaliDate(payment.due_date) }}</strong>
                                        </div>

                                        <div class="schedule-amount">
                                            <small>مبلغ چک</small>
                                            <strong>{{ formatMoney(payment.amount) }}</strong>
                                        </div>

                                        <div class="custom-result-detail">
                                            <span>
                                                سود این بازه:
                                                <b>{{ formatMoney(payment.profit) }}</b>
                                            </span>

                                            <span>
                                                فاصله:
                                                <b v-if="payment.interval_months">
                                                    {{ toPersianDigits(payment.interval_months) }} ماه
                                                </b>
                                                <b v-if="payment.interval_months && payment.interval_days">
                                                    و
                                                </b>
                                                <b v-if="payment.interval_days">
                                                    {{ toPersianDigits(payment.interval_days) }} روز
                                                </b>
                                                <b v-if="!payment.interval_months && !payment.interval_days">
                                                    همان روز
                                                </b>
                                            </span>

                                            <span>
                                                مانده بعد:
                                                <b>{{ formatMoney(payment.balance_after) }}</b>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="hero-result">
                                <small>
                                    {{
                                        mode === 'monthly_cap'
                                            ? 'پیشنهاد مایاهمراه'
                                            : 'مبلغ هر چک'
                                    }}
                                </small>

                                <strong>
                                    {{ formatMoney(result.installment_amount) }}
                                </strong>

                                <span>تومان</span>

                                <p v-if="mode === 'monthly_cap'">
                                    با
                                    <b>{{ toPersianDigits(result.installment_count) }}</b>
                                    قسط، مبلغ هر چک از سقف انتخابی عبور نمی‌کند.
                                </p>
                            </div>

                            <div class="result-metrics">
                                <div>
                                    <span>مانده</span>
                                    <strong>{{ formatMoney(result.principal) }}</strong>
                                </div>

                                <div>
                                    <span>تعداد</span>
                                    <strong>
                                        {{ toPersianDigits(result.installment_count) }}
                                        قسط
                                    </strong>
                                </div>

                                <div>
                                    <span>مجموع اقساط</span>
                                    <strong>{{ formatMoney(result.installment_total) }}</strong>
                                </div>

                                <div>
                                    <span>سود کل</span>
                                    <strong>{{ formatMoney(result.installment_profit) }}</strong>
                                </div>

                                <div class="wide">
                                    <span>مبلغ نهایی قرارداد</span>
                                    <strong class="green">
                                        {{ formatMoney(result.contract_total) }}
                                        تومان
                                    </strong>
                                </div>
                            </div>

                            <div
                                v-if="result.deferment_months || result.deferment_days"
                                class="deferment-card"
                            >
                                <div>
                                    <span>تنفس اضافه</span>
                                    <strong>
                                        <template v-if="result.deferment_months">
                                            {{ toPersianDigits(result.deferment_months) }} ماه
                                        </template>
                                        <template v-if="result.deferment_months && result.deferment_days">
                                            و
                                        </template>
                                        <template v-if="result.deferment_days">
                                            {{ toPersianDigits(result.deferment_days) }} روز
                                        </template>
                                    </strong>
                                </div>

                                <div>
                                    <span>سود زمان اضافه</span>
                                    <strong>
                                        {{ formatMoney(result.deferment_profit) }}
                                        تومان
                                    </strong>
                                </div>
                            </div>

                            <div class="schedule-card">
                                <div class="schedule-head">
                                    <strong>برنامه چک‌ها</strong>
                                    <span>
                                        {{ toPersianDigits(result.installments.length) }}
                                        چک
                                    </span>
                                </div>

                                <div class="schedule-list">
                                    <div
                                        v-for="installment in result.installments"
                                        :key="installment.installment_number"
                                        class="schedule-row"
                                    >
                                        <span class="schedule-number">
                                            {{ toPersianDigits(installment.installment_number) }}
                                        </span>

                                        <div>
                                            <small>سررسید</small>
                                            <strong>{{ formatJalaliDate(installment.due_date) }}</strong>
                                        </div>

                                        <div class="schedule-amount">
                                            <small>مبلغ</small>
                                            <strong>
                                                {{ formatMoney(installment.amount) }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </aside>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
.calculator-page {
    min-height: 100dvh;
    padding: 24px;
    background: #8e8e8e;
}

.page-shell {
    position: relative;
    width: min(1240px, 100%);
    min-height: calc(100dvh - 48px);
    margin: 0 auto;
    overflow: hidden;
    border-radius: 42px;
    background: linear-gradient(135deg, #b7efe4 0%, #edf2fb 100%);
    box-shadow:
        0 28px 80px rgba(37, 46, 61, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.55);
}

.shell-background {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 10% 12%, rgba(255, 255, 255, 0.48), transparent 24%),
        radial-gradient(circle at 88% 14%, rgba(255, 255, 255, 0.34), transparent 18%),
        radial-gradient(circle at 91% 91%, rgba(255, 170, 150, 0.18), transparent 16%);
}

.topbar {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 28px 10px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.circle-btn {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.82);
    color: #161616;
    font-size: 21px;
    box-shadow: 0 8px 24px rgba(60, 73, 91, 0.08);
    transition: transform 180ms ease;
}

.title-wrap {
    display: flex;
    flex-direction: column;
}

.title-wrap strong {
    color: #101215;
    font-size: 30px;
    font-weight: 900;
    letter-spacing: -0.04em;
}

.title-wrap small {
    margin-top: 3px;
    color: #6a717a;
    font-size: 12px;
    font-weight: 600;
}

.tool-badge {
    padding: 11px 17px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.76);
    color: #61736e;
    font-size: 13px;
    font-weight: 900;
}

.main-grid {
    position: relative;
    z-index: 1;
    padding: 8px 28px 28px;
}

.intro-card {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    padding: 28px;
    border-radius: 34px;
    background: rgba(255, 255, 255, 0.4);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.7),
        0 16px 44px rgba(60, 73, 91, 0.07);
    backdrop-filter: blur(10px);
}

.eyebrow,
.section-head small {
    color: #69757e;
    font-size: 12px;
    font-weight: 800;
}

.intro-card h1 {
    max-width: 660px;
    margin-top: 8px;
    color: #101215;
    font-size: clamp(38px, 6vw, 70px);
    line-height: 1.08;
    font-weight: 300;
    letter-spacing: -0.055em;
}

.intro-card h1 span {
    font-weight: 900;
}

.intro-card p {
    max-width: 660px;
    margin-top: 16px;
    color: #65717b;
    font-size: 15px;
    line-height: 2;
}

.intro-pill {
    align-self: flex-end;
    min-width: 190px;
    padding: 18px;
    border-radius: 26px;
    background: #0e1012;
    color: white;
}

.intro-pill span,
.intro-pill strong {
    display: block;
}

.intro-pill span {
    color: rgba(255, 255, 255, 0.58);
    font-size: 12px;
}

.intro-pill strong {
    margin-top: 6px;
    font-size: 19px;
    font-weight: 900;
}

.mode-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 20px;
}

.mode-card {
    position: relative;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 12px;
    min-height: 86px;
    padding: 16px;
    border-radius: 26px;
    background: rgba(248, 250, 252, 0.78);
    text-align: right;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.7);
    transition:
        transform 180ms ease,
        background 180ms ease,
        box-shadow 180ms ease;
}

.mode-card.active {
    background: rgba(255, 255, 255, 0.96);
    box-shadow:
        inset 0 0 0 2px rgba(77, 178, 152, 0.22),
        0 12px 28px rgba(59, 87, 80, 0.08);
}

.mode-dot {
    width: 13px;
    height: 13px;
    border-radius: 999px;
    background: #cad2d5;
}

.mode-card.active .mode-dot {
    background: #52d89a;
    box-shadow: 0 0 0 6px rgba(82, 216, 154, 0.12);
}

.mode-card strong,
.mode-card small {
    display: block;
}

.mode-card strong {
    color: #171a1e;
    font-size: 17px;
    font-weight: 900;
}

.mode-card small {
    margin-top: 4px;
    color: #77818a;
    font-size: 12px;
    line-height: 1.6;
}

.mode-card.pending {
    background: rgba(243, 239, 251, 0.82);
}

.soon-badge {
    padding: 7px 10px;
    border-radius: 999px;
    background: #ece5f7;
    color: #81749a;
    font-size: 10px;
    font-weight: 900;
    white-space: nowrap;
}

.workspace-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
    gap: 22px;
    margin-top: 20px;
}

.form-card,
.result-card {
    padding: 24px;
    border-radius: 34px;
    background: rgba(249, 251, 252, 0.92);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.9),
        0 16px 40px rgba(60, 73, 91, 0.07);
}

.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.section-head h2 {
    margin-top: 4px;
    color: #121519;
    font-size: 29px;
    font-weight: 900;
    letter-spacing: -0.04em;
}

.step-badge {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    border-radius: 999px;
    background: #fde7df;
    color: #a97465;
    font-size: 12px;
    font-weight: 900;
}

.step-badge.mint {
    background: #dcf4ed;
    color: #66877e;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 22px;
}

.field {
    min-width: 0;
}

.field label {
    display: block;
    margin-bottom: 8px;
    color: #4f5962;
    font-size: 12px;
    font-weight: 800;
}

.money-input {
    position: relative;
}

.money-input input,
.date-input {
    width: 100%;
    height: 58px;
    border: 0;
    border-radius: 19px;
    background: #f0f3f4;
    color: #171a1e;
    padding: 0 18px;
    font-size: 15px;
    font-weight: 800;
    outline: none;
    box-shadow: inset 0 0 0 1px rgba(224, 229, 232, 0.85);
    transition:
        box-shadow 180ms ease,
        background 180ms ease;
}

.money-input input {
    padding-left: 74px;
}

.money-input span {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #8a939b;
    font-size: 11px;
    font-weight: 800;
}

.money-input input:focus,
.date-input:focus {
    background: white;
    box-shadow: inset 0 0 0 2px rgba(78, 194, 162, 0.3);
}

.error-text {
    display: block;
    margin-top: 7px;
    color: #d85f68;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.6;
}

.calculate-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-height: 64px;
    margin-top: 20px;
    padding: 0 22px;
    border-radius: 21px;
    background: #0d0f10;
    color: white;
    font-size: 15px;
    font-weight: 900;
    box-shadow: 0 14px 28px rgba(13, 15, 16, 0.15);
    transition:
        transform 180ms ease,
        opacity 180ms ease;
}

.calculate-btn:disabled {
    cursor: not-allowed;
    opacity: 0.48;
}

.btn-arrow {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    font-size: 18px;
}

.custom-payments {
    margin-top: 18px;
    padding: 16px;
    border-radius: 22px;
    background: #eef3f3;
}

.custom-payments-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.custom-payments-head strong,
.custom-payments-head small {
    display: block;
}

.custom-payments-head strong {
    color: #20262a;
    font-size: 14px;
    font-weight: 900;
}

.custom-payments-head small {
    margin-top: 4px;
    color: #808a91;
    font-size: 10px;
}

.add-payment-btn {
    padding: 10px 14px;
    border-radius: 999px;
    background: #d9f3eb;
    color: #53796e;
    font-size: 11px;
    font-weight: 900;
}

.custom-payment-list {
    display: grid;
    gap: 10px;
    margin-top: 14px;
}

.custom-payment-row {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) minmax(0, 1fr) auto;
    align-items: end;
    gap: 10px;
    padding: 12px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.88);
}

.payment-index {
    align-self: center;
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: #e3f5ef;
    color: #5b7b72;
    font-size: 11px;
    font-weight: 900;
}

.remove-payment-btn {
    align-self: center;
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: #f8e8e9;
    color: #b8666d;
    font-size: 19px;
    font-weight: 700;
}

.custom-result-row {
    grid-template-columns: auto 1fr auto;
}

.custom-result-detail {
    grid-column: 2 / -1;
    display: flex;
    flex-wrap: wrap;
    gap: 6px 12px;
    padding-top: 8px;
    border-top: 1px solid #edf0f1;
    color: #7b858d;
    font-size: 9px;
}

.custom-result-detail b {
    color: #454e54;
    font-weight: 900;
}

.message-box,
.custom-notice {
    margin-top: 18px;
    padding: 15px 16px;
    border-radius: 18px;
    background: #fff0f1;
    color: #a95059;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.9;
}

.message-box.warning,
.custom-notice {
    background: #faf4df;
    color: #786d3e;
}

.custom-notice strong {
    display: block;
    color: #5f5736;
    font-size: 14px;
    font-weight: 900;
}

.custom-notice p {
    margin-top: 6px;
    line-height: 1.9;
}

.result-card {
    min-height: 560px;
}

.result-head {
    padding-bottom: 18px;
    border-bottom: 1px solid #e8ecee;
}

.empty-result {
    min-height: 420px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.empty-orbit {
    position: relative;
    display: grid;
    place-items: center;
    width: 104px;
    height: 104px;
    border: 1px solid #d8e0e2;
    border-radius: 999px;
}

.empty-orbit::before {
    content: '';
    position: absolute;
    width: 72px;
    height: 72px;
    border: 1px solid #e5eaec;
    border-radius: 999px;
}

.empty-orbit span {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    background: #6fe2a3;
    box-shadow: 0 0 0 9px rgba(111, 226, 163, 0.13);
}

.empty-result strong {
    margin-top: 24px;
    color: #22272b;
    font-size: 19px;
    font-weight: 900;
}

.empty-result p {
    max-width: 320px;
    margin-top: 8px;
    color: #838c94;
    font-size: 13px;
    line-height: 1.9;
}

.hero-result {
    margin-top: 20px;
    padding: 24px;
    border-radius: 28px;
    background: linear-gradient(145deg, #d8f5ec 0%, #f1f7f5 100%);
}

.hero-result small {
    color: #66827a;
    font-size: 12px;
    font-weight: 800;
}

.hero-result strong {
    display: block;
    margin-top: 8px;
    color: #111514;
    font-size: clamp(34px, 5vw, 54px);
    line-height: 1;
    font-weight: 300;
    letter-spacing: -0.05em;
}

.hero-result > span {
    display: block;
    margin-top: 8px;
    color: #60726d;
    font-size: 13px;
    font-weight: 800;
}

.hero-result p {
    margin-top: 14px;
    color: #657670;
    font-size: 12px;
    line-height: 1.8;
}

.result-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
}

.result-metrics > div {
    padding: 15px;
    border-radius: 19px;
    background: #f1f3f4;
}

.result-metrics .wide {
    grid-column: 1 / -1;
}

.result-metrics span,
.result-metrics strong {
    display: block;
}

.result-metrics span {
    color: #838b92;
    font-size: 10px;
}

.result-metrics strong {
    margin-top: 5px;
    color: #24292d;
    font-size: 14px;
    font-weight: 900;
}

.result-metrics strong.green {
    color: #39866c;
    font-size: 18px;
}

.deferment-card {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
    padding: 15px;
    border-radius: 20px;
    background: #fff6df;
}

.deferment-card span,
.deferment-card strong {
    display: block;
}

.deferment-card span {
    color: #8b7f58;
    font-size: 10px;
}

.deferment-card strong {
    margin-top: 5px;
    color: #6c613d;
    font-size: 13px;
    font-weight: 900;
}

.schedule-card {
    margin-top: 12px;
    padding: 16px;
    border-radius: 22px;
    background: #f2f4f5;
}

.schedule-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.schedule-head strong {
    color: #23282d;
    font-size: 14px;
    font-weight: 900;
}

.schedule-head span {
    padding: 6px 10px;
    border-radius: 999px;
    background: white;
    color: #758087;
    font-size: 10px;
    font-weight: 900;
}

.schedule-list {
    display: grid;
    gap: 8px;
    max-height: 300px;
    margin-top: 12px;
    overflow-y: auto;
}

.schedule-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 10px;
    padding: 11px;
    border-radius: 16px;
    background: white;
}

.schedule-number {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: #e4f5ef;
    color: #5b7d73;
    font-size: 11px;
    font-weight: 900;
}

.schedule-row small,
.schedule-row strong {
    display: block;
}

.schedule-row small {
    color: #969da3;
    font-size: 9px;
}

.schedule-row strong {
    margin-top: 2px;
    color: #343a3f;
    font-size: 11px;
    font-weight: 900;
}

.schedule-amount {
    text-align: left;
}

.reveal {
    animation: revealUp 520ms ease both;
}

.delay-1 {
    animation-delay: 90ms;
}

.delay-2 {
    animation-delay: 160ms;
}

@keyframes revealUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (hover: hover) {
    .circle-btn:hover,
    .mode-card:hover:not(.active),
    .calculate-btn:not(:disabled):hover {
        transform: translateY(-2px);
    }
}

@media (max-width: 980px) {
    .workspace-grid {
        grid-template-columns: 1fr;
    }

    .result-card {
        min-height: auto;
    }
}

@media (max-width: 760px) {
    .calculator-page {
        padding: 0;
        background: linear-gradient(135deg, #b7efe4 0%, #edf2fb 100%);
    }

    .page-shell {
        min-height: 100dvh;
        border-radius: 0;
        box-shadow: none;
    }

    .topbar {
        padding: 18px 16px 8px;
    }

    .main-grid {
        padding: 8px 14px 22px;
    }

    .circle-btn {
        width: 46px;
        height: 46px;
    }

    .title-wrap strong {
        font-size: 20px;
    }

    .tool-badge {
        padding: 9px 12px;
        font-size: 10px;
    }

    .intro-card {
        flex-direction: column;
        gap: 20px;
        padding: 20px;
        border-radius: 28px;
    }

    .intro-card h1 {
        font-size: 42px;
    }

    .intro-card p {
        font-size: 13px;
    }

    .intro-pill {
        align-self: stretch;
        min-width: 0;
    }

    .mode-strip {
        grid-template-columns: 1fr;
        gap: 9px;
    }

    .mode-card {
        min-height: 72px;
        border-radius: 22px;
    }

    .workspace-grid {
        gap: 14px;
        margin-top: 14px;
    }

    .form-card,
    .result-card {
        padding: 18px;
        border-radius: 28px;
    }

    .section-head h2 {
        font-size: 24px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 13px;
    }

    .custom-payment-row {
        grid-template-columns: auto 1fr auto;
    }

    .custom-payment-row .field {
        grid-column: 2;
    }

    .payment-index {
        grid-row: 1 / span 2;
    }

    .remove-payment-btn {
        grid-column: 3;
        grid-row: 1 / span 2;
    }

    .money-input input,
    .date-input {
        height: 56px;
    }

    .result-metrics {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 440px) {
    .intro-card h1 {
        font-size: 35px;
    }

    .topbar-left {
        gap: 10px;
    }

    .title-wrap small {
        display: none;
    }

    .mode-card {
        grid-template-columns: auto 1fr;
    }

    .soon-badge {
        grid-column: 2;
        justify-self: start;
    }

    .result-metrics {
        grid-template-columns: 1fr;
    }

    .result-metrics .wide {
        grid-column: auto;
    }

    .deferment-card {
        grid-template-columns: 1fr;
    }

    .schedule-row {
        grid-template-columns: auto 1fr;
    }

    .schedule-amount {
        grid-column: 2;
        text-align: right;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation: none !important;
        transition: none !important;
    }
}
</style>
