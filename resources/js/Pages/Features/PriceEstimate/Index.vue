<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MayaSelect from '@/Components/MayaSelect.vue';
import {
    batteryConditionLabel,
    colorLabel,
    conditionLabel,
    registrationStatusLabel,
    samsungBatteryConditionOptions,
} from '@/Utils/deviceLabels';

const props = defineProps({
    catalog: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    currentUsdRate: {
        type: Number,
        default: 0,
    },
    estimate: {
        type: Object,
        default: null,
    },
});

const brand = ref(props.filters.brand || '');
const model = ref(props.filters.model || '');
const storage = ref(props.filters.storage || '');
const color = ref(props.filters.color || '');
const conditionGrade = ref(props.filters.condition_grade || '');
const batteryHealth = ref(props.filters.battery_health ?? '');
const batteryCondition = ref(props.filters.battery_condition || '');
const registrationStatus = ref(props.filters.registration_status || '');

const selectedBrand = computed(() =>
    props.catalog.brands.find((item) => item.name === brand.value)
);

const isSamsung = computed(() => brand.value === 'Samsung');

const availableModels = computed(() => {
    if (!selectedBrand.value) return [];

    return props.catalog.models.filter(
        (item) => Number(item.brand_id) === Number(selectedBrand.value.id)
    );
});

const selectedModel = computed(() =>
    availableModels.value.find((item) => item.name === model.value)
);

const availableStorages = computed(() => {
    if (!selectedModel.value) return [];

    const ids = props.catalog.modelStorages
        .filter(
            (item) =>
                Number(item.device_model_id) === Number(selectedModel.value.id)
        )
        .map((item) => Number(item.storage_option_id));

    return props.catalog.storages.filter((item) =>
        ids.includes(Number(item.id))
    );
});

const availableColors = computed(() => {
    if (!selectedModel.value) return [];

    const ids = props.catalog.modelColors
        .filter(
            (item) =>
                Number(item.device_model_id) === Number(selectedModel.value.id)
        )
        .map((item) => Number(item.color_option_id));

    return props.catalog.colors.filter((item) =>
        ids.includes(Number(item.id))
    );
});

const brandOptions = computed(() =>
    props.catalog.brands.map((item) => ({
        value: item.name,
        label: item.name,
    }))
);

const modelOptions = computed(() =>
    availableModels.value.map((item) => ({
        value: item.name,
        label: item.name,
    }))
);

const storageOptions = computed(() =>
    availableStorages.value.map((item) => ({
        value: item.name,
        label: item.name,
    }))
);

const colorOptions = computed(() =>
    availableColors.value.map((item) => ({
        value: item.name,
        label: colorLabel(item.name),
    }))
);

const conditionOptions = [
    { value: '', label: 'مهم نیست' },
    { value: 'A+', label: 'در حد نو' },
    { value: 'A', label: 'بسیار تمیز' },
    { value: 'B', label: 'تمیز' },
    { value: 'C', label: 'خط و خش‌دار' },
];

const registrationOptions = [
    { value: '', label: 'مهم نیست' },
    { value: 'registered', label: 'رجیستر شده' },
    { value: 'unregistered', label: 'رجیستر نشده' },
];

const samsungBatteryOptions = [
    { value: '', label: 'مهم نیست' },
    ...samsungBatteryConditionOptions.map((item) => ({
        value: item.value,
        label: item.label,
    })),
];

watch(brand, (value, oldValue) => {
    if (oldValue !== undefined && value !== props.filters.brand) {
        model.value = '';
        storage.value = '';
        color.value = '';
        batteryHealth.value = '';
        batteryCondition.value = '';
    }
});

watch(model, (value, oldValue) => {
    if (oldValue !== undefined && value !== props.filters.model) {
        storage.value = '';
        color.value = '';
    }
});

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

const handleBatteryHealth = (event) => {
    const normalized = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 3);

    if (!normalized) {
        batteryHealth.value = '';
        event.target.value = '';
        return;
    }

    const clamped = Math.min(100, Math.max(0, Number(normalized)));

    batteryHealth.value = String(clamped);
    event.target.value = toPersianDigits(clamped);
};

const submit = () => {
    if (!brand.value || !model.value || !storage.value) return;

    router.get(
        route('features.price-estimates.index'),
        {
            brand: brand.value,
            model: model.value,
            storage: storage.value,
            color: color.value || undefined,
            condition_grade: conditionGrade.value || undefined,
            battery_health:
                !isSamsung.value && batteryHealth.value !== ''
                    ? batteryHealth.value
                    : undefined,
            battery_condition:
                isSamsung.value && batteryCondition.value
                    ? batteryCondition.value
                    : undefined,
            registration_status: registrationStatus.value || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
};

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const formatDate = (value) => {
    if (!value) return '—';

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(`${value}T00:00:00`));
};

const confidenceLabel = (value) =>
    ({
        high: 'بالا',
        medium: 'متوسط',
        low: 'پایین',
        none: 'نامشخص',
    })[value] || value;

const batteryLabel = (sale) => {
    if (sale.battery_health !== null && sale.battery_health !== undefined) {
        return `${Number(sale.battery_health).toLocaleString('fa-IR')}٪`;
    }

    if (sale.battery_condition) {
        return batteryConditionLabel(sale.battery_condition);
    }

    return '—';
};

const divarSearchUrl = computed(() => {
    const query = [brand.value, model.value, storage.value, color.value]
        .filter(Boolean)
        .join(' ');

    return query
        ? `https://divar.ir/s/iran?q=${encodeURIComponent(query)}`
        : '#';
});
</script>

<template>
    <Head title="برآورد قیمت | مایاهمراه" />

    <div dir="rtl" class="estimator-page">
        <div class="estimator-shell">
            <div class="ambient ambient-one"></div>
            <div class="ambient ambient-two"></div>

            <header class="topbar">
                <div class="topbar-title">
                    <Link
                        :href="route('features.index')"
                        class="back-button"
                        title="بازگشت به امکانات"
                    >
                        ←
                    </Link>

                    <div>
                        <h1>برآورد قیمت</h1>
                        <p>Price Estimate</p>
                    </div>
                </div>

                <div class="maya-badge">M</div>
            </header>

            <main class="main-content">
                <section class="intro-card">
                    <p class="eyebrow">بر پایه داده واقعی مایاهمراه</p>

                    <h2>
                        قیمت گوشی را با سابقه فروش واقعی مقایسه کن
                    </h2>

                    <p>
                        فروش‌های هم‌مدل و هم‌حافظه با نرخ دلار امروز
                        هم‌تراز می‌شوند و مشخصات نزدیک‌تر وزن بیشتری می‌گیرند.
                    </p>
                </section>

                <form class="form-card" @submit.prevent="submit">
                    <div class="section-head">
                        <div>
                            <span>مشخصات دستگاه</span>
                            <strong>گوشی موردنظر را انتخاب کن</strong>
                        </div>

                        <div class="usd-chip">
                            <small>دلار امروز</small>
                            <strong>
                                {{
                                    currentUsdRate
                                        ? `${formatMoney(currentUsdRate)} تومان`
                                        : 'نامشخص'
                                }}
                            </strong>
                        </div>
                    </div>

                    <div class="fields-grid">
                        <div class="select-field">
                            <span>برند</span>
                            <MayaSelect
                                v-model="brand"
                                :options="brandOptions"
                                placeholder="انتخاب برند"
                            />
                        </div>

                        <div class="select-field">
                            <span>مدل</span>
                            <MayaSelect
                                v-model="model"
                                :options="modelOptions"
                                :disabled="!brand"
                                placeholder="انتخاب مدل"
                            />
                        </div>

                        <div class="select-field">
                            <span>حافظه</span>
                            <MayaSelect
                                v-model="storage"
                                :options="storageOptions"
                                :disabled="!model"
                                placeholder="انتخاب حافظه"
                            />
                        </div>

                        <div class="select-field">
                            <span>رنگ</span>
                            <MayaSelect
                                v-model="color"
                                :options="colorOptions"
                                :disabled="!model"
                                placeholder="انتخاب رنگ"
                            />
                        </div>
                    </div>

                    <div class="secondary-fields">
                        <div class="select-field">
                            <span>وضعیت ظاهری</span>
                            <MayaSelect
                                v-model="conditionGrade"
                                :options="conditionOptions"
                                placeholder="مهم نیست"
                            />
                        </div>

                        <div class="select-field">
                            <span>رجیستری</span>
                            <MayaSelect
                                v-model="registrationStatus"
                                :options="registrationOptions"
                                placeholder="مهم نیست"
                            />
                        </div>

                        <div v-if="isSamsung" class="select-field">
                            <span>وضعیت باتری</span>
                            <MayaSelect
                                v-model="batteryCondition"
                                :options="samsungBatteryOptions"
                                placeholder="مهم نیست"
                            />
                        </div>

                        <label v-else>
                            <span>سلامت باتری</span>
                            <div class="percent-field">
                                <input
                                    :value="toPersianDigits(batteryHealth)"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="3"
                                    placeholder="مثلاً ۹۲"
                                    @input="handleBatteryHealth"
                                />
                                <b>٪</b>
                            </div>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="estimate-button"
                        :disabled="!brand || !model || !storage"
                    >
                        محاسبه برآورد
                    </button>
                </form>

                <section
                    v-if="estimate && estimate.available"
                    class="results"
                >
                    <div class="estimate-card">
                        <p>برآورد مرکزی</p>

                        <strong>
                            {{ formatMoney(estimate.estimate) }}
                            <small>تومان</small>
                        </strong>

                        <div class="range">
                            <span>
                                بازه مشاهده‌شده:
                                {{ formatMoney(estimate.range_min) }}
                                تا
                                {{ formatMoney(estimate.range_max) }}
                                تومان
                            </span>
                        </div>
                    </div>

                    <div class="quality-card">
                        <span>کیفیت داده</span>

                        <strong>
                            {{
                                Number(estimate.comparable_count)
                                    .toLocaleString('fa-IR')
                            }}
                            فروش مشابه
                        </strong>

                        <p>
                            سطح اعتماد:
                            <b>{{ confidenceLabel(estimate.confidence) }}</b>
                        </p>
                    </div>

                    <div class="comparable-card">
                        <div class="section-head">
                            <div>
                                <span>مبنای محاسبه</span>
                                <strong>فروش‌های واقعی مشابه</strong>
                            </div>
                        </div>

                        <div class="comparable-list">
                            <article
                                v-for="sale in estimate.comparables"
                                :key="sale.sale_id"
                            >
                                <div>
                                    <strong>
                                        {{ sale.brand }}
                                        {{ sale.model }}
                                        · {{ sale.storage }}
                                    </strong>

                                    <p>
                                        فروش {{ formatDate(sale.sale_date) }}
                                        · رنگ {{ sale.color ? colorLabel(sale.color) : '—' }}
                                        · {{ conditionLabel(sale.condition_grade) || '—' }}
                                        · باتری {{ batteryLabel(sale) }}
                                        · {{
                                            registrationStatusLabel(
                                                sale.registration_status
                                            ) || '—'
                                        }}
                                    </p>
                                </div>

                                <div class="sale-price">
                                    <strong>
                                        {{ formatMoney(sale.normalized_price) }}
                                        تومان
                                    </strong>

                                    <small>
                                        مبلغ فروش:
                                        {{ formatMoney(sale.sale_price) }}
                                    </small>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section
                    v-else-if="estimate && !estimate.available"
                    class="empty-card"
                >
                    <strong>هنوز داده کافی نداریم</strong>
                    <p>
                        برای همین مدل و حافظه فروش ثبت‌شده کافی در مایاهمراه
                        وجود ندارد.
                    </p>
                </section>

                <section
                    v-if="brand && model && storage"
                    class="market-card"
                >
                    <div>
                        <span>بررسی جداگانه بازار</span>
                        <strong>آگهی‌های مشابه در دیوار</strong>

                        <p>
                            قیمت‌های دیوار در فرمول برآورد مایاهمراه وارد
                            نمی‌شوند و فقط برای مقایسه بازار هستند.
                        </p>
                    </div>

                    <a
                        :href="divarSearchUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        مشاهده در دیوار
                    </a>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
.estimator-page {
    min-height: 100dvh;
    padding: 24px;
    background: #8e8e8e;
    font-family: 'Vazirmatn Variable', sans-serif;
    color: #17201f;
}

.estimator-shell {
    position: relative;
    width: min(1080px, 100%);
    margin: 0 auto;
    overflow: hidden;
    border-radius: 38px;
    background: #eef3f1;
    box-shadow: 0 28px 80px rgba(37, 46, 61, 0.15);
}

.ambient {
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    filter: blur(4px);
}

.ambient-one {
    width: 380px;
    height: 380px;
    top: -210px;
    right: -120px;
    background: rgba(113, 208, 191, 0.35);
}

.ambient-two {
    width: 320px;
    height: 320px;
    bottom: -180px;
    left: -120px;
    background: rgba(177, 163, 218, 0.25);
}

.topbar,
.main-content {
    position: relative;
    z-index: 1;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 26px 28px 12px;
}

.topbar-title {
    display: flex;
    align-items: center;
    gap: 14px;
}

.topbar h1 {
    margin: 0;
    font-size: 30px;
    font-weight: 900;
}

.topbar p {
    margin: 2px 0 0;
    color: #74807d;
    font-size: 12px;
}

.back-button,
.maya-badge {
    display: grid;
    width: 52px;
    height: 52px;
    place-items: center;
    border-radius: 50%;
}

.back-button {
    background: rgba(255,255,255,.82);
    font-size: 20px;
}

.maya-badge {
    background: #1d7169;
    color: white;
    font-weight: 900;
}

.main-content {
    padding: 10px 28px 28px;
}

.intro-card,
.form-card,
.estimate-card,
.quality-card,
.comparable-card,
.empty-card,
.market-card {
    border: 1px solid rgba(255,255,255,.9);
    background: rgba(255,255,255,.82);
    box-shadow: 0 10px 30px rgba(36,51,52,.055);
}

.intro-card {
    padding: 28px;
    border-radius: 28px;
}

.eyebrow,
.section-head span,
.market-card span,
.quality-card > span {
    color: #558e88;
    font-size: 12px;
    font-weight: 900;
}

.intro-card h2 {
    max-width: 720px;
    margin: 8px 0 0;
    font-size: clamp(25px, 4vw, 38px);
    line-height: 1.35;
    font-weight: 900;
}

.intro-card > p:last-child {
    max-width: 720px;
    margin: 12px 0 0;
    color: #6d7775;
    font-size: 13px;
    line-height: 2;
}

.form-card {
    margin-top: 18px;
    padding: 22px;
    border-radius: 28px;
}

.section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.section-head strong {
    display: block;
    margin-top: 3px;
    font-size: 17px;
}

.usd-chip {
    padding: 9px 13px;
    border-radius: 14px;
    background: #edf4f2;
}

.usd-chip small,
.usd-chip strong {
    display: block;
}

.usd-chip small {
    color: #75817e;
    font-size: 10px;
}

.usd-chip strong {
    margin-top: 2px;
    font-size: 12px;
}

.fields-grid,
.secondary-fields {
    display: grid;
    gap: 13px;
}

.fields-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-top: 20px;
}

.secondary-fields {
    grid-template-columns: repeat(3, 1fr);
    margin-top: 13px;
}

label > span,
.select-field > span {
    display: block;
    margin-bottom: 7px;
    color: #596461;
    font-size: 12px;
    font-weight: 800;
}

select,
select option,
select optgroup,
input {
    font-family: 'Vazirmatn Variable', sans-serif !important;
}

select,
input {
    width: 100%;
    min-height: 46px;
    border: 1px solid #dbe3e1;
    border-radius: 14px;
    background: white;
    padding: 0 13px;
    color: #1d2523;
    font: inherit;
    font-size: 13px;
    font-weight: 700;
}

select:focus,
input:focus {
    outline: none;
    border-color: #5d9f97;
    box-shadow: 0 0 0 4px rgba(93,159,151,.1);
}

select:disabled {
    opacity: .45;
}

.percent-field {
    position: relative;
}

.percent-field input {
    padding-left: 42px;
}

.percent-field b {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #87918f;
}

.estimate-button {
    display: block;
    margin: 18px 0 0 auto;
    border: 0;
    border-radius: 15px;
    background: #1d7169;
    padding: 12px 24px;
    color: white;
    font: inherit;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
}

.estimate-button:disabled {
    cursor: not-allowed;
    opacity: .35;
}

.results {
    display: grid;
    grid-template-columns: 1.5fr .7fr;
    gap: 16px;
    margin-top: 18px;
}

.estimate-card,
.quality-card,
.comparable-card {
    border-radius: 26px;
    padding: 22px;
}

.estimate-card {
    background: #176d66;
    color: white;
}

.estimate-card > p {
    margin: 0;
    color: rgba(255,255,255,.75);
    font-size: 12px;
    font-weight: 800;
}

.estimate-card > strong {
    display: block;
    margin-top: 10px;
    font-size: clamp(28px, 5vw, 44px);
}

.estimate-card > strong small {
    font-size: 13px;
    color: rgba(255,255,255,.72);
}

.range {
    margin-top: 13px;
    color: rgba(255,255,255,.76);
    font-size: 12px;
}

.quality-card > strong {
    display: block;
    margin-top: 16px;
    font-size: 20px;
}

.quality-card p {
    margin: 10px 0 0;
    color: #65706e;
    font-size: 12px;
}

.comparable-card {
    grid-column: 1 / -1;
}

.comparable-list {
    display: grid;
    gap: 8px;
    margin-top: 16px;
}

.comparable-list article {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 14px;
    border-radius: 16px;
    background: #f4f7f6;
}

.comparable-list article > div:first-child > strong {
    font-size: 13px;
}

.comparable-list p,
.sale-price small {
    margin: 4px 0 0;
    color: #76817e;
    font-size: 11px;
}

.sale-price {
    flex-shrink: 0;
    text-align: left;
}

.sale-price strong,
.sale-price small {
    display: block;
}

.empty-card,
.market-card {
    margin-top: 18px;
    border-radius: 26px;
    padding: 22px;
}

.empty-card {
    text-align: center;
}

.empty-card p,
.market-card p {
    margin: 6px 0 0;
    color: #6f7a77;
    font-size: 12px;
    line-height: 1.9;
}

.market-card {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: center;
}

.market-card strong {
    display: block;
    margin-top: 4px;
    font-size: 17px;
}

.market-card a {
    flex-shrink: 0;
    border-radius: 14px;
    background: #b83131;
    padding: 11px 16px;
    color: white;
    font-size: 12px;
    font-weight: 900;
}

@media (max-width: 820px) {
    .estimator-page {
        padding: 14px;
    }

    .estimator-shell {
        border-radius: 28px;
    }

    .topbar {
        padding: 20px 18px 10px;
    }

    .main-content {
        padding: 8px 18px 20px;
    }

    .fields-grid,
    .secondary-fields,
    .results {
        grid-template-columns: 1fr;
    }

    .comparable-card {
        grid-column: auto;
    }
}

@media (max-width: 600px) {
    .estimator-page {
        padding: 0;
        background: #eef3f1;
    }

    .estimator-shell {
        border-radius: 0;
        box-shadow: none;
    }

    .intro-card,
    .form-card,
    .estimate-card,
    .quality-card,
    .comparable-card,
    .empty-card,
    .market-card {
        border-radius: 21px;
    }

    .section-head,
    .market-card,
    .comparable-list article {
        align-items: stretch;
        flex-direction: column;
    }

    .usd-chip {
        width: fit-content;
    }

    .sale-price {
        text-align: right;
    }
}
</style>
