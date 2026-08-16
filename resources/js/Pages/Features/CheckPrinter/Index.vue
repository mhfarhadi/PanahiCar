<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { toJalaali } from 'jalaali-js';

const bankOptions = [
    'بانک ملی ایران',
    'بانک سپه',
    'بانک ملت',
    'بانک تجارت',
    'بانک صادرات ایران',
    'بانک رفاه کارگران',
    'بانک کشاورزی',
    'بانک مسکن',
    'بانک صنعت و معدن',
    'بانک توسعه صادرات ایران',
    'بانک توسعه تعاون',
    'پست بانک ایران',
    'بانک اقتصاد نوین',
    'بانک پارسیان',
    'بانک پاسارگاد',
    'بانک سامان',
    'بانک سینا',
    'بانک شهر',
    'بانک دی',
    'بانک گردشگری',
    'بانک ایران زمین',
    'بانک خاورمیانه',
    'بانک کارآفرین',
    'بانک سرمایه',
    'بانک قرض‌الحسنه مهر ایران',
    'بانک قرض‌الحسنه رسالت',
];

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

const formatMoney = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');
    return digits ? Number(digits).toLocaleString('fa-IR') : '';
};

const ones = [
    '',
    'یک',
    'دو',
    'سه',
    'چهار',
    'پنج',
    'شش',
    'هفت',
    'هشت',
    'نه',
];
const teens = [
    'ده',
    'یازده',
    'دوازده',
    'سیزده',
    'چهارده',
    'پانزده',
    'شانزده',
    'هفده',
    'هجده',
    'نوزده',
];
const tens = [
    '',
    '',
    'بیست',
    'سی',
    'چهل',
    'پنجاه',
    'شصت',
    'هفتاد',
    'هشتاد',
    'نود',
];
const hundreds = [
    '',
    'صد',
    'دویست',
    'سیصد',
    'چهارصد',
    'پانصد',
    'ششصد',
    'هفتصد',
    'هشتصد',
    'نهصد',
];
const scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];

const threeDigitWords = (number) => {
    const parts = [];
    const h = Math.floor(number / 100);
    const rest = number % 100;

    if (h) parts.push(hundreds[h]);

    if (rest >= 10 && rest < 20) {
        parts.push(teens[rest - 10]);
    } else {
        const t = Math.floor(rest / 10);
        const o = rest % 10;

        if (t) parts.push(tens[t]);
        if (o) parts.push(ones[o]);
    }

    return parts.join(' و ');
};

const numberToPersianWords = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    if (!digits) return '';
    if (/^0+$/.test(digits)) return 'صفر';

    let number = Number(digits);

    if (!Number.isSafeInteger(number) || number < 0) return '';

    const parts = [];
    let scaleIndex = 0;

    while (number > 0 && scaleIndex < scales.length) {
        const chunk = number % 1000;

        if (chunk) {
            const chunkWords = threeDigitWords(chunk);
            parts.unshift(
                scales[scaleIndex]
                    ? `${chunkWords} ${scales[scaleIndex]}`
                    : chunkWords
            );
        }

        number = Math.floor(number / 1000);
        scaleIndex += 1;
    }

    return parts.join(' و ');
};

const makeCheck = (index = 0) => ({
    id: `${Date.now()}-${index}-${Math.random()}`,
    bank_name: '',
    check_number: '',
    sayad_id: '',
    payee: '',
    amount: '',
    due_date: '',
});

const checks = ref([makeCheck(0)]);
const activeIndex = ref(0);

const activeCheck = computed(() => checks.value[activeIndex.value] ?? checks.value[0]);

const checkCount = computed({
    get: () => checks.value.length,
    set: (value) => {
        const count = Math.min(12, Math.max(1, Number(value) || 1));

        while (checks.value.length < count) {
            checks.value.push(makeCheck(checks.value.length));
        }

        if (checks.value.length > count) {
            checks.value.splice(count);
        }

        if (activeIndex.value >= count) {
            activeIndex.value = count - 1;
        }
    },
});

const amountWords = (check) => {
    const words = numberToPersianWords(check?.amount);
    return words ? `${words} ریال` : 'مبلغ به حروف';
};

const setMoney = (check, event) => {
    check.amount = normalizeDigits(event.target.value).replace(/\D/g, '');
};

const setDigits = (check, field, event, max = null) => {
    let value = normalizeDigits(event.target.value).replace(/\D/g, '');

    if (max) value = value.slice(0, max);

    check[field] = value;
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

const setDate = (check, value) => {
    const normalizedValue = normalizePickerValue(value);

    if (!normalizedValue) return;

    check.due_date = normalizedValue;
};

const formatJalaliDate = (value) => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) return '';

    const date = toJalaali(
        Number(match[1]),
        Number(match[2]),
        Number(match[3])
    );

    return toPersianDigits(
        `${date.jy}/${String(date.jm).padStart(2, '0')}/${String(date.jd).padStart(2, '0')}`
    );
};

const printChecks = () => {
    window.print();
};
</script>

<template>
    <Head title="پرینتر چک | مایاهمراه" />

    <div dir="rtl" class="printer-page">
        <div class="printer-screen">
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
                            <strong>پرینتر چک</strong>
                            <small>Maya Check Printer</small>
                        </div>
                    </div>

                    <span class="public-badge">عمومی</span>
                </header>

                <main class="main-grid">
                    <section class="hero-card">
                        <div class="hero-copy">
                            <small class="eyebrow">چاپ دقیق روی خودِ برگه چک</small>

                            <h1>
                                اطلاعات را وارد کن؛
                                <span>جای درست چاپش کن</span>
                            </h1>

                            <p>
                                چند چک را پشت سر هم آماده کن، مبلغ به حروف را
                                خودکار ببین و قبل از چاپ نهایی جای اطلاعات را
                                کنترل کن.
                            </p>
                        </div>

                        <div class="printer-visual" aria-hidden="true">
                            <div class="paper-feed paper-back" />
                            <div class="printer-body">
                                <span class="printer-slot" />
                                <span class="printer-light" />
                            </div>
                            <div class="paper-feed paper-front">
                                <span />
                                <span />
                                <span />
                            </div>
                        </div>
                    </section>

                    <section class="workspace">
                        <article class="control-panel">
                            <div class="panel-head">
                                <div>
                                    <small>مرحله ۱</small>
                                    <h2>چک‌ها را آماده کن</h2>
                                </div>

                                <label class="count-control">
                                    <span>تعداد چک</span>
                                    <input
                                        v-model.number="checkCount"
                                        type="number"
                                        min="1"
                                        max="12"
                                    />
                                </label>
                            </div>

                            <div class="check-tabs">
                                <button
                                    v-for="(check, index) in checks"
                                    :key="check.id"
                                    type="button"
                                    class="check-tab"
                                    :class="{ active: activeIndex === index }"
                                    @click="activeIndex = index"
                                >
                                    <span>{{ toPersianDigits(index + 1) }}</span>
                                    <small>
                                        {{ check.bank_name || 'بدون بانک' }}
                                    </small>
                                </button>
                            </div>

                            <div v-if="activeCheck" class="form-grid">
                                <label class="field field-wide">
                                    <span>بانک / قالب چک</span>
                                    <select v-model="activeCheck.bank_name">
                                        <option value="">انتخاب بانک</option>
                                        <option
                                            v-for="bank in bankOptions"
                                            :key="bank"
                                            :value="bank"
                                        >
                                            {{ bank }}
                                        </option>
                                    </select>
                                    <small>
                                        فعلاً چیدمان چاپ عمومی است؛ مختصات
                                        اختصاصی هر بانک در مرحله کالیبراسیون
                                        اضافه می‌شود.
                                    </small>
                                </label>

                                <label class="field">
                                    <span>شماره چک</span>
                                    <input
                                        :value="toPersianDigits(activeCheck.check_number)"
                                        inputmode="numeric"
                                        placeholder="مثلاً ۱۲۳۴۵۶"
                                        @input="setDigits(activeCheck, 'check_number', $event)"
                                    />
                                </label>

                                <label class="field">
                                    <span>شناسه صیاد</span>
                                    <input
                                        :value="toPersianDigits(activeCheck.sayad_id)"
                                        inputmode="numeric"
                                        maxlength="16"
                                        placeholder="۱۶ رقم"
                                        @input="setDigits(activeCheck, 'sayad_id', $event, 16)"
                                    />
                                </label>

                                <label class="field field-wide">
                                    <span>در وجه</span>
                                    <input
                                        v-model="activeCheck.payee"
                                        type="text"
                                        placeholder="نام شخص یا شرکت"
                                    />
                                </label>

                                <label class="field">
                                    <span>مبلغ چک — ریال</span>
                                    <input
                                        :value="formatMoney(activeCheck.amount)"
                                        inputmode="numeric"
                                        placeholder="۰"
                                        @input="setMoney(activeCheck, $event)"
                                    />
                                </label>

                                <label class="field">
                                    <span>تاریخ چک</span>

                                    <Vue3PersianDatetimePicker
                                        v-model="activeCheck.due_date"
                                        format="YYYY-MM-DD"
                                        display-format="jYYYY/jMM/jDD"
                                        type="date"
                                        convert-numbers
                                        auto-submit
                                        custom-input=".check-printer-date-input"
                                        @change="value => setDate(activeCheck, value)"
                                    />

                                    <input
                                        type="text"
                                        class="date-input check-printer-date-input"
                                        placeholder="انتخاب تاریخ"
                                        readonly
                                    />
                                </label>

                                <div class="words-preview field-wide">
                                    <span>مبلغ به حروف</span>
                                    <strong>{{ amountWords(activeCheck) }}</strong>
                                </div>
                            </div>
                        </article>

                        <aside class="preview-panel">
                            <div class="preview-head">
                                <div>
                                    <small>مرحله ۲</small>
                                    <h2>پیش‌نمایش چاپ</h2>
                                </div>

                                <span class="preview-status">قالب عمومی</span>
                            </div>

                            <div v-if="activeCheck" class="check-preview">
                                <div class="mock-watermark" aria-hidden="true">
                                    نمونه · غیرقابل استفاده
                                </div>

                                <div class="mock-bank-strip" aria-hidden="true">
                                    <span class="mock-bank-mark">M</span>
                                    <div>
                                        <strong>چک صیادی</strong>
                                        <small>پیش‌نمایش جایگذاری اطلاعات</small>
                                    </div>
                                </div>

                                <div class="mock-security-line mock-security-one" aria-hidden="true" />
                                <div class="mock-security-line mock-security-two" aria-hidden="true" />

                                <div class="check-preview-top">
                                    <div>
                                        <small>بانک</small>
                                        <strong>
                                            {{ activeCheck.bank_name || 'بانک انتخاب نشده' }}
                                        </strong>
                                    </div>

                                    <div class="check-no">
                                        <small>شماره چک</small>
                                        <strong>
                                            {{ toPersianDigits(activeCheck.check_number) || '—' }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="preview-date">
                                    <span>تاریخ</span>
                                    <strong>
                                        {{ formatJalaliDate(activeCheck.due_date) || '____/__/__' }}
                                    </strong>
                                </div>

                                <div class="preview-payee">
                                    <span>در وجه</span>
                                    <strong>
                                        {{ activeCheck.payee || '................................' }}
                                    </strong>
                                </div>

                                <div class="preview-words">
                                    {{ amountWords(activeCheck) }}
                                </div>

                                <div class="preview-amount">
                                    <span>مبلغ</span>
                                    <strong>
                                        {{ formatMoney(activeCheck.amount) || '۰' }}
                                    </strong>
                                    <small>ریال</small>
                                </div>

                                <div class="preview-sayad">
                                    <span>شناسه صیاد</span>
                                    <strong dir="ltr">
                                        {{ toPersianDigits(activeCheck.sayad_id) || '—' }}
                                    </strong>
                                </div>

                                <div class="preview-signature">
                                    <span>محل امضا</span>
                                </div>
                            </div>

                            <div class="calibration-note">
                                <div class="calibration-icon">⌖</div>
                                <div>
                                    <strong>قبل از چاپ روی چک واقعی</strong>
                                    <p>
                                        این نسخه هنوز مختصات اختصاصی بانک‌ها
                                        را ندارد. ابتدا خروجی را روی کاغذ
                                        آزمایشی بررسی می‌کنیم و سپس کالیبراسیون
                                        X/Y و قالب هر بانک را دقیق می‌کنیم.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="print-button"
                                @click="printChecks"
                            >
                                <span>چاپ آزمایشی</span>
                                <small>{{ toPersianDigits(checks.length) }} چک آماده</small>
                            </button>
                        </aside>
                    </section>
                </main>
            </div>
        </div>

        <div class="printer-print" aria-hidden="true">
            <section
                v-for="(check, index) in checks"
                :key="`print-${check.id}`"
                class="print-sheet"
            >
                <div class="print-check">
                    <div class="print-bank">
                        {{ check.bank_name || '' }}
                    </div>

                    <div class="print-number">
                        {{ toPersianDigits(check.check_number) }}
                    </div>

                    <div class="print-date">
                        {{ formatJalaliDate(check.due_date) }}
                    </div>

                    <div class="print-payee">
                        {{ check.payee }}
                    </div>

                    <div class="print-words">
                        {{ amountWords(check) }}
                    </div>

                    <div class="print-amount">
                        {{ formatMoney(check.amount) }}
                        <span v-if="check.amount">ریال</span>
                    </div>

                    <div class="print-sayad">
                        {{ toPersianDigits(check.sayad_id) }}
                    </div>
                </div>

                <div class="print-sequence">
                    {{ toPersianDigits(index + 1) }} / {{ toPersianDigits(checks.length) }}
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.printer-page,
.printer-page * {
    box-sizing: border-box;
}

.printer-page {
    min-height: 100vh;
    background:
        radial-gradient(circle at 12% 8%, rgba(255, 178, 120, 0.28), transparent 28rem),
        radial-gradient(circle at 88% 42%, rgba(111, 215, 203, 0.18), transparent 30rem),
        #f4f1ea;
    color: #17201d;
    font-family: "Vazirmatn Variable", "Vazirmatn", Tahoma, sans-serif;
}

.page-shell {
    position: relative;
    width: min(1420px, calc(100% - 32px));
    margin: 0 auto;
    padding: 24px 0 64px;
    overflow: hidden;
}

.ambient {
    position: absolute;
    border: 1px solid rgba(23, 32, 29, 0.09);
    border-radius: 999px;
    pointer-events: none;
}

.ambient-one {
    width: 360px;
    height: 360px;
    top: 80px;
    left: -220px;
}

.ambient-two {
    width: 220px;
    height: 220px;
    right: -130px;
    bottom: 70px;
}

.topbar {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.circle-btn {
    display: grid;
    width: 44px;
    height: 44px;
    place-items: center;
    border: 1px solid rgba(23, 32, 29, 0.1);
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.62);
    color: inherit;
    text-decoration: none;
    backdrop-filter: blur(16px);
}

.title-wrap {
    display: grid;
    gap: 1px;
}

.title-wrap strong {
    font-size: 15px;
}

.title-wrap small {
    color: #7b827f;
    font-size: 10px;
    letter-spacing: 0.08em;
}

.public-badge,
.preview-status {
    border: 1px solid rgba(23, 32, 29, 0.09);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.55);
    padding: 8px 13px;
    color: #68716d;
    font-size: 11px;
}

.main-grid {
    position: relative;
    z-index: 2;
    display: grid;
    gap: 18px;
}

.hero-card {
    position: relative;
    display: grid;
    min-height: 280px;
    grid-template-columns: minmax(0, 1fr) 360px;
    align-items: center;
    overflow: hidden;
    border: 1px solid rgba(23, 32, 29, 0.08);
    border-radius: 38px;
    background: #242a28;
    padding: clamp(28px, 5vw, 62px);
    color: #fff;
}

.hero-card::after {
    position: absolute;
    width: 390px;
    height: 390px;
    right: -140px;
    bottom: -260px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 50%;
    content: "";
}

.hero-copy {
    position: relative;
    z-index: 2;
    max-width: 650px;
}

.eyebrow {
    color: #fac49d;
    font-size: 12px;
    font-weight: 800;
}

.hero-copy h1 {
    max-width: 620px;
    margin: 16px 0 12px;
    font-size: clamp(34px, 5vw, 66px);
    font-weight: 900;
    line-height: 1.08;
    letter-spacing: -0.045em;
}

.hero-copy h1 span {
    color: #f3a66e;
}

.hero-copy p {
    max-width: 610px;
    margin: 0;
    color: rgba(255, 255, 255, 0.66);
    font-size: 14px;
    line-height: 2;
}

.printer-visual {
    position: relative;
    width: 280px;
    height: 220px;
    margin-inline: auto;
}

.printer-body {
    position: absolute;
    z-index: 2;
    width: 250px;
    height: 118px;
    left: 15px;
    top: 56px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 30px 30px 42px 42px;
    background: linear-gradient(145deg, #434a47, #171b1a);
    box-shadow: 0 34px 80px rgba(0, 0, 0, 0.35);
}

.printer-slot {
    position: absolute;
    width: 172px;
    height: 8px;
    left: 39px;
    top: 25px;
    border-radius: 999px;
    background: #0f1211;
}

.printer-light {
    position: absolute;
    width: 8px;
    height: 8px;
    right: 28px;
    top: 52px;
    border-radius: 50%;
    background: #72debd;
    box-shadow: 0 0 14px rgba(114, 222, 189, 0.8);
}

.paper-feed {
    position: absolute;
    left: 45px;
    width: 190px;
    border-radius: 9px;
    background: #fffdfa;
}

.paper-back {
    height: 100px;
    top: 8px;
    transform: perspective(300px) rotateX(-10deg);
}

.paper-front {
    z-index: 3;
    height: 105px;
    top: 129px;
    padding: 22px;
    box-shadow: 0 22px 40px rgba(0, 0, 0, 0.14);
}

.paper-front span {
    display: block;
    width: 70%;
    height: 4px;
    margin-bottom: 11px;
    border-radius: 999px;
    background: #d6d1c9;
}

.paper-front span:nth-child(2) {
    width: 88%;
}

.paper-front span:nth-child(3) {
    width: 54%;
}

.workspace {
    display: grid;
    grid-template-columns: minmax(0, 1.14fr) minmax(390px, 0.86fr);
    gap: 18px;
    align-items: start;
}

.control-panel,
.preview-panel {
    border: 1px solid rgba(23, 32, 29, 0.08);
    border-radius: 34px;
    background: rgba(255, 255, 255, 0.72);
    box-shadow: 0 20px 65px rgba(56, 48, 36, 0.05);
    backdrop-filter: blur(20px);
}

.control-panel {
    padding: clamp(22px, 4vw, 38px);
}

.preview-panel {
    position: sticky;
    top: 18px;
    padding: 26px;
}

.panel-head,
.preview-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 24px;
}

.panel-head small,
.preview-head small {
    color: #a55f36;
    font-size: 10px;
    font-weight: 800;
}

.panel-head h2,
.preview-head h2 {
    margin: 4px 0 0;
    font-size: 21px;
}

.count-control {
    display: grid;
    gap: 5px;
    color: #737a77;
    font-size: 10px;
}

.count-control input {
    width: 82px;
    border: 1px solid rgba(23, 32, 29, 0.1);
    border-radius: 14px;
    background: #fff;
    padding: 9px 12px;
    text-align: center;
}

.check-tabs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    margin-bottom: 28px;
    padding-bottom: 4px;
}

.check-tab {
    display: grid;
    min-width: 94px;
    gap: 2px;
    border: 1px solid rgba(23, 32, 29, 0.08);
    border-radius: 18px;
    background: rgba(244, 241, 234, 0.7);
    padding: 10px 12px;
    color: #6c7470;
    text-align: right;
    cursor: pointer;
}

.check-tab span {
    font-size: 18px;
    font-weight: 900;
}

.check-tab small {
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.check-tab.active {
    border-color: #242a28;
    background: #242a28;
    color: #fff;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.field {
    display: grid;
    gap: 7px;
}

.field-wide {
    grid-column: 1 / -1;
}

.field > span,
.words-preview > span {
    color: #5e6763;
    font-size: 11px;
    font-weight: 800;
}

.field input,
.field select,
:deep(.date-input) {
    width: 100%;
    min-height: 49px;
    border: 1px solid rgba(23, 32, 29, 0.1);
    border-radius: 16px;
    outline: none;
    background: rgba(255, 255, 255, 0.92);
    padding: 0 15px;
    color: #17201d;
    font: inherit;
}

.field input:focus,
.field select:focus,
:deep(.date-input:focus) {
    border-color: rgba(225, 121, 59, 0.62);
    box-shadow: 0 0 0 4px rgba(225, 121, 59, 0.08);
}

.field small {
    color: #939996;
    font-size: 10px;
    line-height: 1.7;
}

.words-preview {
    display: grid;
    gap: 7px;
    border: 1px dashed rgba(23, 32, 29, 0.14);
    border-radius: 18px;
    background: #f6f3ec;
    padding: 15px 17px;
}

.words-preview strong {
    min-height: 25px;
    font-size: 14px;
    line-height: 1.8;
}

.check-preview {
    position: relative;
    min-height: 340px;
    overflow: hidden;
    border: 1px solid rgba(69, 89, 78, 0.18);
    border-radius: 26px;
    background:
        radial-gradient(circle at 12% 18%, rgba(255,255,255,.72), transparent 28%),
        radial-gradient(circle at 82% 72%, rgba(75,130,105,.11), transparent 34%),
        repeating-linear-gradient(
            0deg,
            rgba(61, 104, 83, 0.035) 0,
            rgba(61, 104, 83, 0.035) 1px,
            transparent 1px,
            transparent 7px
        ),
        linear-gradient(135deg, #edf6ef 0%, #dfeee4 48%, #eef5ea 100%);
    padding: 24px;
    color: #244236;
    box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.65),
        0 18px 42px rgba(45,70,58,.08);
}

.check-preview::before {
    position: absolute;
    width: 180px;
    height: 180px;
    left: -60px;
    bottom: -80px;
    border: 1px solid rgba(36, 66, 54, 0.12);
    border-radius: 50%;
    content: "";
}

.mock-watermark {
    position: absolute;
    z-index: 0;
    left: 50%;
    top: 54%;
    transform: translate(-50%, -50%) rotate(-17deg);
    color: rgba(76, 112, 94, 0.13);
    font-size: clamp(22px, 3vw, 34px);
    font-weight: 950;
    letter-spacing: -0.02em;
    white-space: nowrap;
    pointer-events: none;
    user-select: none;
}

.mock-bank-strip {
    position: absolute;
    z-index: 1;
    top: 18px;
    right: 24px;
    display: flex;
    align-items: center;
    gap: 9px;
    opacity: .55;
    pointer-events: none;
}

.mock-bank-mark {
    display: grid;
    width: 29px;
    height: 29px;
    place-items: center;
    border: 1px solid rgba(36,66,54,.28);
    border-radius: 50%;
    font-size: 10px;
    font-weight: 900;
}

.mock-bank-strip div {
    display: grid;
    line-height: 1.2;
}

.mock-bank-strip strong {
    font-size: 8px;
}

.mock-bank-strip small {
    color: rgba(36,66,54,.62);
    font-size: 6px;
}

.mock-security-line {
    position: absolute;
    z-index: 0;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(36,66,54,.11),
        transparent
    );
    pointer-events: none;
}

.mock-security-one {
    width: 76%;
    top: 118px;
    right: 12%;
}

.mock-security-two {
    width: 64%;
    top: 214px;
    right: 18%;
}

.check-preview-top,
.preview-date,
.preview-payee,
.preview-words,
.preview-amount,
.preview-sayad,
.preview-signature {
    position: relative;
    z-index: 2;
}

.check-preview-top {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(36, 66, 54, 0.12);
}

.check-preview-top div,
.preview-date,
.preview-payee,
.preview-sayad {
    display: grid;
    gap: 4px;
}

.check-preview small,
.check-preview span {
    color: rgba(36, 66, 54, 0.62);
    font-size: 9px;
}

.check-no {
    text-align: left;
}

.preview-date {
    position: absolute;
    top: 92px;
    left: 24px;
    min-width: 120px;
    text-align: left;
}

.preview-payee {
    margin-top: 38px;
}

.preview-payee strong {
    min-height: 29px;
    border-bottom: 1px dotted rgba(36, 66, 54, 0.38);
}

.preview-words {
    min-height: 56px;
    margin-top: 18px;
    border-bottom: 1px dotted rgba(36, 66, 54, 0.34);
    font-size: 13px;
    font-weight: 800;
    line-height: 2;
}

.preview-amount {
    display: flex;
    align-items: baseline;
    gap: 7px;
    margin-top: 17px;
}

.preview-amount strong {
    font-size: 21px;
}

.preview-sayad {
    position: absolute;
    right: 24px;
    bottom: 24px;
    max-width: 185px;
}

.preview-sayad strong {
    letter-spacing: 0.12em;
}

.preview-signature {
    position: absolute;
    left: 25px;
    bottom: 22px;
    width: 120px;
    height: 58px;
    border: 1px dashed rgba(36, 66, 54, 0.22);
    border-radius: 14px;
    padding: 8px;
    text-align: center;
}

.calibration-note {
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr);
    gap: 13px;
    margin-top: 18px;
    border-radius: 20px;
    background: #fff3e8;
    padding: 15px;
}

.calibration-icon {
    display: grid;
    width: 44px;
    height: 44px;
    place-items: center;
    border-radius: 14px;
    background: #f2a66f;
    color: #fff;
    font-size: 20px;
}

.calibration-note strong {
    font-size: 12px;
}

.calibration-note p {
    margin: 4px 0 0;
    color: #8f735e;
    font-size: 10px;
    line-height: 1.8;
}

.print-button {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
    border: 0;
    border-radius: 18px;
    background: #242a28;
    padding: 15px 18px;
    color: #fff;
    font: inherit;
    cursor: pointer;
}

.print-button span {
    font-size: 13px;
    font-weight: 900;
}

.print-button small {
    color: rgba(255, 255, 255, 0.58);
}

.printer-print {
    display: none;
}

@media (max-width: 900px) {
    .page-shell {
        width: min(100% - 20px, 760px);
        padding-top: 14px;
    }

    .hero-card {
        min-height: auto;
        grid-template-columns: 1fr;
        gap: 28px;
        border-radius: 30px;
        padding: 30px 24px;
    }

    .hero-copy h1 {
        font-size: clamp(34px, 10vw, 50px);
    }

    .printer-visual {
        transform: scale(0.88);
        transform-origin: center;
    }

    .workspace {
        grid-template-columns: 1fr;
    }

    .preview-panel {
        position: static;
    }
}

@media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .field-wide {
        grid-column: auto;
    }

    .control-panel,
    .preview-panel {
        border-radius: 26px;
        padding: 19px;
    }

    .panel-head {
        align-items: flex-end;
    }

    .check-preview {
        min-height: 320px;
        padding: 18px;
    }

    .preview-date {
        top: 88px;
        left: 18px;
    }
}

@media print {
    @page {
        size: A4 portrait;
        margin: 0;
    }

    :global(html),
    :global(body) {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .printer-screen {
        display: none !important;
    }

    .printer-print {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .print-sheet {
        position: relative;
        width: 210mm;
        height: 297mm;
        margin: 0;
        break-after: page;
        page-break-after: always;
        overflow: hidden;
        background: #fff;
    }

    .print-sheet:last-child {
        break-after: auto;
        page-break-after: auto;
    }

    .print-check {
        position: absolute;
        top: 24mm;
        left: 15mm;
        width: 180mm;
        height: 82mm;
        direction: rtl;
        font-family: "Vazirmatn", Tahoma, sans-serif;
        color: #000;
    }

    .print-bank,
    .print-number,
    .print-date,
    .print-payee,
    .print-words,
    .print-amount,
    .print-sayad {
        position: absolute;
    }

    .print-bank {
        top: 4mm;
        right: 7mm;
        font-size: 10pt;
        font-weight: 700;
    }

    .print-number {
        top: 5mm;
        left: 7mm;
        font-size: 9pt;
    }

    .print-date {
        top: 17mm;
        left: 8mm;
        font-size: 11pt;
        font-weight: 700;
    }

    .print-payee {
        top: 29mm;
        right: 13mm;
        max-width: 135mm;
        font-size: 11pt;
        font-weight: 700;
    }

    .print-words {
        top: 41mm;
        right: 13mm;
        width: 145mm;
        font-size: 10pt;
        font-weight: 700;
        line-height: 1.8;
    }

    .print-amount {
        top: 61mm;
        right: 13mm;
        font-size: 12pt;
        font-weight: 800;
    }

    .print-amount span {
        margin-right: 2mm;
        font-size: 9pt;
    }

    .print-sayad {
        right: 13mm;
        bottom: 4mm;
        direction: ltr;
        font-size: 8pt;
        letter-spacing: 0.08em;
    }

    .print-sequence {
        position: absolute;
        right: 8mm;
        bottom: 8mm;
        color: #888;
        font-family: "Vazirmatn", Tahoma, sans-serif;
        font-size: 7pt;
    }

    * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
