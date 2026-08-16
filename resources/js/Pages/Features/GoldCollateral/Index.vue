<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { toGregorian, toJalaali, jalaaliMonthLength } from 'jalaali-js';

const props = defineProps({
    goldRate: {
        type: Object,
        default: () => ({}),
    },
});

const loading = ref(false);
const result = ref(null);
const errors = ref({});
const generalError = ref('');

const form = ref({
    sale_price: '',
    down_payment: '',
    monthly_profit_rate: '6.5',
    installment_count: 6,
    sale_date: '',
    first_due_date: '',
});

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const formatWeight = (value) =>
    Number(value || 0).toLocaleString('fa-IR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4,
    });

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
    const digits = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 2);

    form.value.installment_count = digits === '' ? '' : Number(digits);
};

const formatJalaliDate = (value) => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) return '—';

    const date = toJalaali(
        Number(match[1]),
        Number(match[2]),
        Number(match[3])
    );

    return toPersianDigits(
        `${date.jy}/${String(date.jm).padStart(2, '0')}/${String(date.jd).padStart(2, '0')}`
    );
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

    const gregorian = toGregorian(nextYear, nextMonth, nextDay);

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

const currentPrincipal = computed(() =>
    Math.max(
        0,
        Number(form.value.sale_price || 0) -
            Number(form.value.down_payment || 0)
    )
);

const displayedGoldRate = computed(() =>
    result.value?.gold_rate ?? props.goldRate ?? {}
);

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const fieldError = (field) => errors.value[field]?.[0] ?? '';

const calculate = async () => {
    loading.value = true;
    result.value = null;
    errors.value = {};
    generalError.value = '';

    try {
        const response = await fetch(
            route('features.gold-collateral.calculate'),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    sale_price: Number(form.value.sale_price || 0),
                    down_payment: Number(form.value.down_payment || 0),
                    monthly_profit_rate: Number(form.value.monthly_profit_rate || 0),
                    installment_count: Number(form.value.installment_count || 0),
                    sale_date: form.value.sale_date,
                    first_due_date: form.value.first_due_date,
                }),
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
            generalError.value =
                data.message ??
                'محاسبه انجام نشد. اتصال را بررسی کنید و دوباره تلاش کنید.';
            return;
        }

        result.value = data.result;
    } catch (error) {
        generalError.value =
            'محاسبه انجام نشد. اتصال را بررسی کنید و دوباره تلاش کنید.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head title="ضمانت طلا | مایاهمراه" />

    <div dir="rtl" class="gold-page">
        <div class="page-shell">
            <div class="ambient ambient-one" />
            <div class="ambient ambient-two" />

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
                        <strong>ضمانت طلا</strong>
                        <small>Maya Gold Guarantee</small>
                    </div>
                </div>

                <span class="public-badge">عمومی</span>
            </header>

            <main class="main-grid">
                <section class="hero-card reveal">
                    <div class="hero-copy">
                        <small class="eyebrow">وثیقه متناسب با بدهی واقعی</small>

                        <h1>
                            قبل از فروش بدان
                            <span>چند گرم طلا</span>
                            لازم است
                        </h1>

                        <p>
                            اصل بدهی به‌علاوه دو ماه سود قرارداد پوشش داده
                            می‌شود و وزن لازم با نرخ طلای ۱۸ عیار محاسبه
                            می‌شود.
                        </p>

                        <div class="formula-pill">
                            <span>فرمول پوشش</span>
                            <strong>اصل بدهی + سود ۲ ماه</strong>
                        </div>
                    </div>

                    <div class="rate-orbit">
                        <div class="rate-disc">
                            <small>طلای ۱۸ عیار</small>

                            <strong v-if="displayedGoldRate.rate_per_gram">
                                {{ formatMoney(displayedGoldRate.rate_per_gram) }}
                            </strong>
                            <strong v-else>—</strong>

                            <span>تومان / گرم</span>
                        </div>

                        <div class="rate-meta">
                            <span>
                                {{
                                    displayedGoldRate.source === 'navasan'
                                        ? 'نرخ نوسان'
                                        : 'آخرین نرخ موجود'
                                }}
                            </span>

                            <small v-if="displayedGoldRate.rate_date">
                                {{ formatJalaliDate(displayedGoldRate.rate_date) }}
                            </small>
                        </div>

                        <span
                            v-if="displayedGoldRate.stale"
                            class="stale-badge"
                        >
                            آخرین نرخ ذخیره‌شده
                        </span>
                    </div>
                </section>

                <section class="workspace-grid">
                    <article class="form-card reveal delay-1">
                        <div class="section-head">
                            <div>
                                <small>ورودی قرارداد</small>
                                <h2>مشخصات فروش اقساطی</h2>
                            </div>

                            <span class="step-badge">01</span>
                        </div>

                        <div class="principal-preview">
                            <span>اصل بدهی فعلی</span>
                            <strong>{{ formatMoney(currentPrincipal) }}</strong>
                            <small>تومان</small>
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label>قیمت فروش</label>

                                <div class="money-input">
                                    <input
                                        :value="formatPriceInput(form.sale_price)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="۱۴۰,۰۰۰,۰۰۰"
                                        @input="setMoneyField('sale_price', $event)"
                                    >
                                    <span>تومان</span>
                                </div>

                                <small v-if="fieldError('sale_price')" class="error-text">
                                    {{ fieldError('sale_price') }}
                                </small>
                            </div>

                            <div class="field">
                                <label>پیش‌پرداخت نقدی</label>

                                <div class="money-input">
                                    <input
                                        :value="formatPriceInput(form.down_payment)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="۴۰,۰۰۰,۰۰۰"
                                        @input="setMoneyField('down_payment', $event)"
                                    >
                                    <span>تومان</span>
                                </div>

                                <small v-if="fieldError('down_payment')" class="error-text">
                                    {{ fieldError('down_payment') }}
                                </small>
                            </div>

                            <div class="field">
                                <label>سود ماهانه قرارداد</label>

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

                            <div class="field">
                                <label>تعداد اقساط</label>

                                <div class="money-input">
                                    <input
                                        :value="toPersianDigits(form.installment_count)"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="۶"
                                        @input="setInstallmentCount"
                                    >
                                    <span>قسط</span>
                                </div>

                                <small v-if="fieldError('installment_count')" class="error-text">
                                    {{ fieldError('installment_count') }}
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
                                    custom-input=".gold-sale-date-input"
                                    @change="value => syncPickerDate('sale_date', value)"
                                />

                                <input
                                    type="text"
                                    class="date-input gold-sale-date-input"
                                    placeholder="تاریخ فروش"
                                    readonly
                                >

                                <small v-if="fieldError('sale_date')" class="error-text">
                                    {{ fieldError('sale_date') }}
                                </small>
                            </div>

                            <div class="field">
                                <label>اولین سررسید</label>

                                <Vue3PersianDatetimePicker
                                    v-model="form.first_due_date"
                                    format="YYYY-MM-DD"
                                    display-format="jYYYY/jMM/jDD"
                                    type="date"
                                    convert-numbers
                                    auto-submit
                                    custom-input=".gold-first-due-date-input"
                                    @change="value => syncPickerDate('first_due_date', value)"
                                />

                                <input
                                    type="text"
                                    class="date-input gold-first-due-date-input"
                                    placeholder="حداقل یک ماه شمسی بعد"
                                    readonly
                                >

                                <small v-if="fieldError('first_due_date')" class="error-text">
                                    {{ fieldError('first_due_date') }}
                                </small>
                            </div>
                        </div>

                        <div v-if="generalError" class="message-box">
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
                                        : 'محاسبه ضمانت طلا'
                                }}
                            </span>

                            <span class="btn-arrow">←</span>
                        </button>
                    </article>

                    <aside class="result-card reveal delay-2">
                        <div class="section-head">
                            <div>
                                <small>خروجی ضمانت</small>
                                <h2>مقدار طلای لازم</h2>
                            </div>

                            <span class="step-badge gold">02</span>
                        </div>

                        <div v-if="!result" class="empty-result">
                            <div class="gold-mark">
                                <span />
                            </div>

                            <strong>هنوز محاسبه‌ای انجام نشده</strong>

                            <p>
                                مشخصات فروش را وارد کن تا مقدار پوشش، وزن طلا
                                و برنامه اقساط نمایش داده شود.
                            </p>
                        </div>

                        <template v-else>
                            <div class="weight-result">
                                <small>حداقل طلای مورد نیاز</small>

                                <div>
                                    <strong>
                                        {{ formatWeight(result.collateral.required_weight) }}
                                    </strong>
                                    <span>گرم</span>
                                </div>

                                <p>
                                    مبنا: طلای ۱۸ عیار با نرخ
                                    {{ formatMoney(result.gold_rate.rate_per_gram) }}
                                    تومان برای هر گرم
                                </p>
                            </div>

                            <div class="coverage-grid">
                                <div>
                                    <span>اصل بدهی</span>
                                    <strong>
                                        {{ formatMoney(result.collateral.base_principal) }}
                                    </strong>
                                    <small>تومان</small>
                                </div>

                                <div>
                                    <span>سود پوشش ۲ ماهه</span>
                                    <strong>
                                        {{ formatMoney(result.collateral.coverage_profit) }}
                                    </strong>
                                    <small>تومان</small>
                                </div>

                                <div class="coverage-total">
                                    <span>جمع مبلغ تحت پوشش</span>
                                    <strong>
                                        {{ formatMoney(result.collateral.coverage_amount) }}
                                    </strong>
                                    <small>تومان</small>
                                </div>
                            </div>

                            <div class="installment-summary">
                                <div>
                                    <span>مبلغ هر قسط</span>
                                    <strong>
                                        {{ formatMoney(result.installments.installment_amount) }}
                                    </strong>
                                </div>

                                <div>
                                    <span>جمع اقساط</span>
                                    <strong>
                                        {{ formatMoney(result.installments.installment_total) }}
                                    </strong>
                                </div>

                                <div>
                                    <span>سود کل اقساط</span>
                                    <strong>
                                        {{ formatMoney(result.installments.installment_profit) }}
                                    </strong>
                                </div>
                            </div>
                        </template>
                    </aside>
                </section>

                <section
                    v-if="result"
                    class="schedule-card reveal"
                >
                    <div class="section-head">
                        <div>
                            <small>برنامه پرداخت</small>
                            <h2>سررسید اقساط</h2>
                        </div>

                        <span class="schedule-count">
                            {{ toPersianDigits(result.installments.installments.length) }}
                            قسط
                        </span>
                    </div>

                    <div class="schedule-list">
                        <article
                            v-for="installment in result.installments.installments"
                            :key="installment.installment_number"
                            class="schedule-row"
                        >
                            <span class="installment-number">
                                {{ toPersianDigits(installment.installment_number) }}
                            </span>

                            <div>
                                <small>سررسید</small>
                                <strong>{{ formatJalaliDate(installment.due_date) }}</strong>
                            </div>

                            <div>
                                <small>مبلغ قسط</small>
                                <strong>{{ formatMoney(installment.amount) }} تومان</strong>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="explain-card reveal">
                    <div class="explain-number">۲</div>

                    <div>
                        <small>چرا دو ماه سود؟</small>
                        <h2>وثیقه فقط اصل پول را پوشش نمی‌دهد</h2>
                        <p>
                            اگر پرداخت مشتری عقب بیفتد، سرمایه فروشنده در این
                            فاصله درگیر می‌ماند. مایاهمراه برای نسخه فعلی ابزار،
                            اصل مانده بدهی را به‌علاوه دو ماه سود همان قرارداد
                            مبنای ضمانت قرار می‌دهد.
                        </p>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
.gold-page {
    min-height: 100dvh;
    padding: 24px;
    background: #898989;
    color: #171611;
}

.page-shell {
    position: relative;
    width: min(1240px, 100%);
    margin: 0 auto;
    overflow: hidden;
    border-radius: 42px;
    background:
        radial-gradient(circle at 88% 3%, rgba(255, 232, 126, 0.65), transparent 25%),
        linear-gradient(135deg, #eef6ef 0%, #f2f1e8 48%, #e9f2ef 100%);
    box-shadow:
        0 28px 80px rgba(37, 46, 61, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.ambient {
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    filter: blur(1px);
}

.ambient-one {
    width: 280px;
    height: 280px;
    left: -90px;
    top: 180px;
    background: rgba(146, 240, 190, 0.28);
}

.ambient-two {
    width: 210px;
    height: 210px;
    right: -80px;
    bottom: 160px;
    background: rgba(255, 212, 105, 0.24);
}

.topbar,
.main-grid {
    position: relative;
    z-index: 1;
}

.topbar {
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
}

.title-wrap {
    display: flex;
    flex-direction: column;
}

.title-wrap strong {
    font-size: 31px;
    font-weight: 900;
}

.title-wrap small {
    color: #77786e;
    font-size: 12px;
    margin-top: 2px;
}

.public-badge,
.step-badge,
.schedule-count,
.stale-badge {
    border-radius: 999px;
    font-weight: 800;
}

.public-badge {
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.65);
    font-size: 12px;
}

.main-grid {
    display: grid;
    gap: 18px;
    padding: 8px 28px 28px;
}

.hero-card,
.form-card,
.result-card,
.schedule-card,
.explain-card {
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.7),
        0 16px 44px rgba(60, 73, 91, 0.07);
    backdrop-filter: blur(12px);
}

.hero-card {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(270px, 0.6fr);
    gap: 28px;
    align-items: center;
    padding: 34px;
    border-radius: 36px;
    background: rgba(255, 255, 255, 0.42);
}

.eyebrow,
.section-head small,
.explain-card small {
    color: #75766c;
    font-size: 12px;
    font-weight: 800;
}

.hero-copy h1 {
    max-width: 720px;
    margin: 11px 0 12px;
    font-size: clamp(35px, 6vw, 68px);
    line-height: 1.1;
    letter-spacing: -0.055em;
    font-weight: 950;
}

.hero-copy h1 span {
    color: #9a7400;
}

.hero-copy p {
    max-width: 650px;
    margin: 0;
    color: #64655f;
    font-size: 16px;
    line-height: 2;
}

.formula-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    padding: 10px 14px;
    border-radius: 999px;
    background: #191a16;
    color: #fff;
}

.formula-pill span {
    color: #bfc0b7;
    font-size: 11px;
}

.formula-pill strong {
    font-size: 13px;
}

.rate-orbit {
    position: relative;
    min-height: 285px;
    display: grid;
    place-items: center;
}

.rate-orbit::before,
.rate-orbit::after {
    content: '';
    position: absolute;
    border-radius: 999px;
    border: 1px solid rgba(142, 112, 0, 0.18);
}

.rate-orbit::before {
    width: 276px;
    height: 276px;
}

.rate-orbit::after {
    width: 230px;
    height: 230px;
}

.rate-disc {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 205px;
    height: 205px;
    border-radius: 999px;
    text-align: center;
    background: linear-gradient(145deg, #f4d962, #f6eaa8);
    box-shadow: 0 20px 50px rgba(174, 137, 0, 0.2);
}

.rate-disc small {
    font-size: 11px;
    color: #74621d;
}

.rate-disc strong {
    margin: 8px 0 2px;
    font-size: 25px;
    font-weight: 950;
}

.rate-disc span {
    font-size: 11px;
    color: #74621d;
}

.rate-meta {
    position: absolute;
    bottom: -2px;
    z-index: 3;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 8px 24px rgba(74, 68, 35, 0.08);
}

.rate-meta span {
    font-size: 11px;
    font-weight: 900;
}

.rate-meta small {
    font-size: 10px;
    color: #75766c;
}

.stale-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 4;
    padding: 7px 10px;
    background: #fff3c3;
    color: #7e6506;
    font-size: 10px;
}

.workspace-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(340px, 0.92fr);
    gap: 18px;
}

.form-card,
.result-card,
.schedule-card,
.explain-card {
    border-radius: 34px;
    background: rgba(255, 255, 255, 0.58);
}

.form-card,
.result-card {
    padding: 26px;
}

.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}

.section-head h2 {
    margin: 5px 0 0;
    font-size: 24px;
    font-weight: 950;
}

.step-badge {
    display: grid;
    place-items: center;
    width: 43px;
    height: 43px;
    background: #181915;
    color: #fff;
    font-size: 11px;
}

.step-badge.gold {
    background: #eed35d;
    color: #3a3211;
}

.principal-preview {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 18px;
    padding: 13px 15px;
    border-radius: 18px;
    background: rgba(238, 211, 93, 0.18);
}

.principal-preview span {
    margin-left: auto;
    color: #706a4b;
    font-size: 12px;
}

.principal-preview strong {
    font-size: 19px;
    font-weight: 950;
}

.principal-preview small {
    color: #7b765e;
    font-size: 10px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.field {
    display: grid;
    gap: 8px;
}

.field label {
    color: #4e5049;
    font-size: 12px;
    font-weight: 850;
}

.money-input {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 54px;
    padding: 0 14px;
    border: 1px solid rgba(100, 102, 90, 0.14);
    border-radius: 17px;
    background: rgba(255, 255, 255, 0.74);
}

.money-input input,
.date-input {
    width: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    color: #171611;
    font-family: inherit;
    font-size: 14px;
    font-weight: 800;
}

.money-input span {
    white-space: nowrap;
    color: #77786e;
    font-size: 10px;
}

.date-input {
    min-height: 54px;
    padding: 0 14px;
    border: 1px solid rgba(100, 102, 90, 0.14);
    border-radius: 17px;
    background: rgba(255, 255, 255, 0.74);
    cursor: pointer;
}

.error-text {
    color: #bd3f45;
    font-size: 10px;
    line-height: 1.7;
}

.message-box {
    margin-top: 16px;
    padding: 12px 14px;
    border-radius: 15px;
    background: #fff0f0;
    color: #a83039;
    font-size: 12px;
    line-height: 1.8;
}

.calculate-btn {
    width: 100%;
    min-height: 58px;
    margin-top: 20px;
    padding: 0 18px;
    border: 0;
    border-radius: 19px;
    background: #181915;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: inherit;
    font-size: 14px;
    font-weight: 900;
    cursor: pointer;
}

.calculate-btn:disabled {
    opacity: 0.6;
    cursor: wait;
}

.btn-arrow {
    display: grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: #eed35d;
    color: #282511;
}

.empty-result {
    min-height: 470px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #77786e;
}

.gold-mark {
    width: 110px;
    height: 110px;
    margin-bottom: 20px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    border: 1px solid rgba(147, 112, 0, 0.2);
}

.gold-mark span {
    width: 48px;
    height: 34px;
    transform: skew(-12deg);
    border-radius: 8px;
    background: linear-gradient(145deg, #e3c24a, #f5e58c);
    box-shadow: 0 12px 28px rgba(174, 137, 0, 0.18);
}

.empty-result strong {
    color: #24241f;
    font-size: 16px;
}

.empty-result p {
    max-width: 320px;
    margin: 9px 0 0;
    font-size: 12px;
    line-height: 1.9;
}

.weight-result {
    padding: 24px;
    border-radius: 26px;
    background: linear-gradient(145deg, #191a16, #29291f);
    color: #fff;
}

.weight-result small {
    color: #c8c8bb;
    font-size: 11px;
}

.weight-result div {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-top: 6px;
}

.weight-result strong {
    font-size: clamp(42px, 6vw, 66px);
    font-weight: 300;
    letter-spacing: -0.05em;
}

.weight-result span {
    color: #eed35d;
    font-size: 17px;
    font-weight: 900;
}

.weight-result p {
    margin: 12px 0 0;
    color: #babbb1;
    font-size: 11px;
    line-height: 1.8;
}

.coverage-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
}

.coverage-grid > div {
    padding: 14px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.72);
}

.coverage-grid span,
.installment-summary span,
.schedule-row small {
    display: block;
    color: #7b7c73;
    font-size: 10px;
}

.coverage-grid strong,
.installment-summary strong {
    display: block;
    margin-top: 5px;
    font-size: 15px;
    font-weight: 950;
}

.coverage-grid small {
    color: #999a90;
    font-size: 9px;
}

.coverage-total {
    grid-column: 1 / -1;
    background: rgba(238, 211, 93, 0.27) !important;
}

.installment-summary {
    display: grid;
    gap: 1px;
    overflow: hidden;
    margin-top: 12px;
    border-radius: 20px;
    background: rgba(91, 93, 82, 0.09);
}

.installment-summary > div {
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.75);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.installment-summary strong {
    margin: 0;
}

.schedule-card {
    padding: 26px;
}

.schedule-count {
    padding: 8px 12px;
    background: #ecda80;
    color: #514411;
    font-size: 11px;
}

.schedule-list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.schedule-row {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 10px 12px;
    align-items: center;
    padding: 14px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.7);
}

.installment-number {
    grid-row: 1 / span 2;
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: #191a16;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
}

.schedule-row strong {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    font-weight: 900;
}

.explain-card {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 20px;
    align-items: center;
    padding: 24px 28px;
}

.explain-number {
    display: grid;
    place-items: center;
    width: 76px;
    height: 76px;
    border-radius: 24px;
    background: #eed35d;
    font-size: 38px;
    font-weight: 300;
}

.explain-card h2 {
    margin: 4px 0 6px;
    font-size: 20px;
    font-weight: 950;
}

.explain-card p {
    max-width: 820px;
    margin: 0;
    color: #6b6d65;
    font-size: 12px;
    line-height: 1.9;
}

.reveal {
    animation: reveal 520ms ease both;
}

.delay-1 {
    animation-delay: 80ms;
}

.delay-2 {
    animation-delay: 150ms;
}

@keyframes reveal {
    from {
        opacity: 0;
        transform: translateY(12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 920px) {
    .hero-card,
    .workspace-grid {
        grid-template-columns: 1fr;
    }

    .rate-orbit {
        min-height: 250px;
    }

    .schedule-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .gold-page {
        padding: 0;
        background: #eef3ec;
    }

    .page-shell {
        min-height: 100dvh;
        border-radius: 0;
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
        font-size: 22px;
    }

    .hero-card {
        padding: 23px 18px;
        border-radius: 28px;
    }

    .hero-copy h1 {
        font-size: 41px;
    }

    .hero-copy p {
        font-size: 13px;
    }

    .rate-orbit {
        min-height: 230px;
    }

    .rate-orbit::before {
        width: 225px;
        height: 225px;
    }

    .rate-orbit::after {
        width: 190px;
        height: 190px;
    }

    .rate-disc {
        width: 170px;
        height: 170px;
    }

    .rate-disc strong {
        font-size: 21px;
    }

    .form-card,
    .result-card,
    .schedule-card {
        padding: 19px;
        border-radius: 27px;
    }

    .form-grid,
    .coverage-grid,
    .schedule-list {
        grid-template-columns: 1fr;
    }

    .coverage-total {
        grid-column: auto;
    }

    .empty-result {
        min-height: 320px;
    }

    .weight-result strong {
        font-size: 49px;
    }

    .schedule-row {
        grid-template-columns: auto 1fr;
    }

    .explain-card {
        padding: 20px;
        border-radius: 27px;
        grid-template-columns: 1fr;
    }

    .explain-number {
        width: 58px;
        height: 58px;
        border-radius: 19px;
        font-size: 28px;
    }
}
</style>
