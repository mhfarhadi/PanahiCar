<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MayaSelect from '@/Components/MayaSelect.vue';
import {
    colorLabel,
    samsungBatteryConditionOptions,
} from '@/Utils/deviceLabels';

const props = defineProps({
    catalog: {
        type: Object,
        required: true,
    },
});

const loading = ref(false);
const submitted = ref(null);
const errors = ref({});
const generalError = ref('');
const marketFeedback = ref(null);

const form = ref({
    requester_name: '',
    requester_mobile: '',
    brand: '',
    model: '',
    storage: '',
    color: '',
    condition_grade: '',
    registration_status: '',
    battery_health: '',
    battery_condition: '',
    max_price: '',
    description: '',
});

const selectedBrand = computed(() =>
    props.catalog.brands.find((item) => item.name === form.value.brand)
);

const availableModels = computed(() => {
    if (!selectedBrand.value) return [];

    return props.catalog.models.filter(
        (item) =>
            Number(item.brand_id) === Number(selectedBrand.value.id)
    );
});

const selectedModel = computed(() =>
    availableModels.value.find(
        (item) => item.name === form.value.model
    )
);

const availableStorages = computed(() => {
    if (!selectedModel.value) return [];

    const ids = props.catalog.modelStorages
        .filter(
            (item) =>
                Number(item.device_model_id) ===
                Number(selectedModel.value.id)
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
                Number(item.device_model_id) ===
                Number(selectedModel.value.id)
        )
        .map((item) => Number(item.color_option_id));

    return props.catalog.colors.filter((item) =>
        ids.includes(Number(item.id))
    );
});

const isSamsung = computed(() => form.value.brand === 'Samsung');
const isApple = computed(() => form.value.brand === 'Apple');

const specificationsComplete = computed(() =>
    Boolean(
        form.value.condition_grade &&
        form.value.registration_status &&
        (
            isSamsung.value
                ? form.value.battery_condition
                : form.value.battery_health !== ''
        )
    )
);

const phoneColorThemes = {
    Black: {
        frame: '#17191d',
        edge: '#343840',
        accent: '#4d5563',
        soft: 'rgba(84, 94, 110, 0.30)',
        screenA: '#cfd5dd',
        screenB: '#eceff3',
        glow: 'rgba(35, 40, 49, 0.34)',
    },
    White: {
        frame: '#e8e9e8',
        edge: '#bfc3c4',
        accent: '#9ca3a5',
        soft: 'rgba(220, 225, 224, 0.50)',
        screenA: '#f8faf9',
        screenB: '#e5ece9',
        glow: 'rgba(202, 211, 208, 0.40)',
    },
    Blue: {
        frame: '#476a8d',
        edge: '#294964',
        accent: '#70a8d6',
        soft: 'rgba(91, 153, 205, 0.34)',
        screenA: '#d9edf8',
        screenB: '#dfe7f6',
        glow: 'rgba(72, 135, 186, 0.40)',
    },
    Green: {
        frame: '#4d7668',
        edge: '#315347',
        accent: '#78b49d',
        soft: 'rgba(98, 181, 149, 0.32)',
        screenA: '#dcefe7',
        screenB: '#edf3df',
        glow: 'rgba(69, 145, 116, 0.38)',
    },
    Pink: {
        frame: '#d995aa',
        edge: '#a96579',
        accent: '#f0b3c5',
        soft: 'rgba(231, 145, 172, 0.34)',
        screenA: '#f8e0e8',
        screenB: '#f1e5f3',
        glow: 'rgba(218, 116, 151, 0.38)',
    },
    Graphite: {
        frame: '#55585b',
        edge: '#333537',
        accent: '#82878b',
        soft: 'rgba(92, 96, 101, 0.32)',
        screenA: '#d7dadd',
        screenB: '#eceeed',
        glow: 'rgba(62, 66, 70, 0.36)',
    },
    Gold: {
        frame: '#c3a36b',
        edge: '#8f713f',
        accent: '#e5c98e',
        soft: 'rgba(215, 180, 113, 0.36)',
        screenA: '#f5ead2',
        screenB: '#f1e7df',
        glow: 'rgba(194, 153, 78, 0.38)',
    },
    Silver: {
        frame: '#b7bec3',
        edge: '#818b92',
        accent: '#d5dadd',
        soft: 'rgba(188, 199, 205, 0.40)',
        screenA: '#edf2f4',
        screenB: '#dfe9e8',
        glow: 'rgba(145, 161, 169, 0.36)',
    },
    'Sierra Blue': {
        frame: '#7196a9',
        edge: '#4d7184',
        accent: '#9bc1d2',
        soft: 'rgba(108, 164, 190, 0.36)',
        screenA: '#d8eef5',
        screenB: '#e6e8f5',
        glow: 'rgba(91, 147, 174, 0.40)',
    },
    'Natural Titanium': {
        frame: '#9f9587',
        edge: '#72695d',
        accent: '#c2b7a7',
        soft: 'rgba(170, 157, 139, 0.35)',
        screenA: '#eee9df',
        screenB: '#e7ece8',
        glow: 'rgba(145, 132, 114, 0.38)',
    },
    'Black Titanium': {
        frame: '#343536',
        edge: '#1f2021',
        accent: '#646769',
        soft: 'rgba(65, 68, 71, 0.34)',
        screenA: '#d4d7d9',
        screenB: '#e9ebec',
        glow: 'rgba(39, 41, 43, 0.40)',
    },
    'Blue Titanium': {
        frame: '#526977',
        edge: '#354954',
        accent: '#819aa7',
        soft: 'rgba(80, 111, 128, 0.34)',
        screenA: '#dbe8ee',
        screenB: '#e3e7ef',
        glow: 'rgba(65, 96, 114, 0.40)',
    },
    'White Titanium': {
        frame: '#dad7cf',
        edge: '#aaa69c',
        accent: '#f0ede5',
        soft: 'rgba(217, 214, 203, 0.45)',
        screenA: '#faf8f3',
        screenB: '#e9eeeb',
        glow: 'rgba(190, 186, 174, 0.38)',
    },
    Midnight: {
        frame: '#26313a',
        edge: '#121b22',
        accent: '#526775',
        soft: 'rgba(42, 65, 80, 0.36)',
        screenA: '#d4e2e8',
        screenB: '#e7e8ef',
        glow: 'rgba(30, 49, 62, 0.42)',
    },
    Starlight: {
        frame: '#ddd5c4',
        edge: '#aea48e',
        accent: '#eee6d5',
        soft: 'rgba(221, 210, 187, 0.42)',
        screenA: '#faf3e3',
        screenB: '#edf0e8',
        glow: 'rgba(199, 183, 150, 0.40)',
    },
    '(PRODUCT)RED': {
        frame: '#b6323d',
        edge: '#791d27',
        accent: '#e45f68',
        soft: 'rgba(204, 54, 68, 0.34)',
        screenA: '#f5d8da',
        screenB: '#f2e4ea',
        glow: 'rgba(190, 43, 57, 0.40)',
    },
    'Alpine Green': {
        frame: '#466657',
        edge: '#294438',
        accent: '#739583',
        soft: 'rgba(71, 117, 94, 0.34)',
        screenA: '#dcebe2',
        screenB: '#e9eee4',
        glow: 'rgba(57, 106, 82, 0.40)',
    },
    'Space Black': {
        frame: '#252527',
        edge: '#111113',
        accent: '#55555b',
        soft: 'rgba(45, 45, 51, 0.34)',
        screenA: '#d3d3d8',
        screenB: '#ececef',
        glow: 'rgba(29, 29, 33, 0.42)',
    },
    'Deep Purple': {
        frame: '#51405d',
        edge: '#32263b',
        accent: '#846c93',
        soft: 'rgba(91, 62, 108, 0.35)',
        screenA: '#e6dced',
        screenB: '#eee5ef',
        glow: 'rgba(77, 50, 91, 0.42)',
    },
    Purple: {
        frame: '#8a6ca0',
        edge: '#5c4670',
        accent: '#b899ca',
        soft: 'rgba(145, 98, 174, 0.34)',
        screenA: '#eadcf2',
        screenB: '#efe5f3',
        glow: 'rgba(131, 82, 161, 0.40)',
    },
    Yellow: {
        frame: '#e4ca67',
        edge: '#aa9137',
        accent: '#f1dd8a',
        soft: 'rgba(230, 202, 94, 0.38)',
        screenA: '#f8f0c9',
        screenB: '#f3eedf',
        glow: 'rgba(205, 173, 52, 0.38)',
    },
    'Desert Titanium': {
        frame: '#a98769',
        edge: '#725943',
        accent: '#caaa8c',
        soft: 'rgba(177, 136, 101, 0.36)',
        screenA: '#f0e2d7',
        screenB: '#eee9df',
        glow: 'rgba(157, 113, 77, 0.40)',
    },
    Teal: {
        frame: '#397c7c',
        edge: '#235353',
        accent: '#6eb0ad',
        soft: 'rgba(54, 145, 142, 0.35)',
        screenA: '#d5efeb',
        screenB: '#dfebee',
        glow: 'rgba(43, 123, 121, 0.40)',
    },
    Ultramarine: {
        frame: '#5366b1',
        edge: '#34427f',
        accent: '#8497dd',
        soft: 'rgba(83, 105, 190, 0.36)',
        screenA: '#dfe5fa',
        screenB: '#e8e1f4',
        glow: 'rgba(73, 91, 174, 0.42)',
    },
};

const defaultPhoneTheme = {
    frame: '#211924',
    edge: '#120d15',
    accent: '#a441b7',
    soft: 'rgba(164, 65, 183, 0.25)',
    screenA: '#e7f5ef',
    screenB: '#f1dff4',
    glow: 'rgba(111, 66, 124, 0.25)',
};

const selectedPhoneTheme = computed(
    () => phoneColorThemes[form.value.color] ?? defaultPhoneTheme
);

const phonePreviewStyle = computed(() => ({
    '--phone-frame': selectedPhoneTheme.value.frame,
    '--phone-edge': selectedPhoneTheme.value.edge,
    '--phone-accent': selectedPhoneTheme.value.accent,
    '--phone-soft': selectedPhoneTheme.value.soft,
    '--phone-screen-a': selectedPhoneTheme.value.screenA,
    '--phone-screen-b': selectedPhoneTheme.value.screenB,
    '--phone-glow': selectedPhoneTheme.value.glow,
}));

const conditionClass = computed(() => {
    return {
        'A+': 'condition-pristine',
        A: 'condition-excellent',
        B: 'condition-clean',
        C: 'condition-worn',
    }[form.value.condition_grade] ?? 'condition-neutral';
});

const conditionPreviewLabel = computed(() => {
    return {
        'A+': 'در حد نو',
        A: 'بسیار تمیز',
        B: 'تمیز',
        C: 'خط و خش‌دار',
    }[form.value.condition_grade] ?? 'وضعیت آزاد';
});

const batteryPreviewVisible = computed(() =>
    isSamsung.value
        ? Boolean(form.value.battery_condition)
        : form.value.battery_health !== ''
);

const batteryPreviewText = computed(() => {
    if (isSamsung.value) {
        return samsungBatteryOptions.find(
            (item) => item.value === form.value.battery_condition
        )?.label ?? '';
    }

    return form.value.battery_health !== ''
        ? `${String(form.value.battery_health)}%`
        : '';
});

const batteryPreviewLevel = computed(() => {
    if (isSamsung.value) {
        return {
            excellent: 'high',
            good: 'medium',
            poor: 'low',
            replace: 'critical',
        }[form.value.battery_condition] ?? 'medium';
    }

    const value = Number(form.value.battery_health || 0);

    if (value >= 90) return 'high';
    if (value >= 80) return 'medium';
    if (value >= 60) return 'low';
    return 'critical';
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

const colorOptions = computed(() => [
    { value: '', label: 'رنگ مهم نیست' },
    ...availableColors.value.map((item) => ({
        value: item.name,
        label: colorLabel(item.name),
    })),
]);

const conditionOptions = [
    { value: 'A+', label: 'در حد نو' },
    { value: 'A', label: 'بسیار تمیز' },
    { value: 'B', label: 'تمیز' },
    { value: 'C', label: 'خط و خش‌دار' },
];

const registrationOptions = [
    { value: 'registered', label: 'رجیستر شده' },
    { value: 'unregistered', label: 'رجیستر نشده' },
];

const samsungBatteryOptions = [
    ...samsungBatteryConditionOptions.map((item) => ({
        value: item.value,
        label: item.label,
    })),
];

watch(
    () => form.value.brand,
    () => {
        form.value.model = '';
        form.value.storage = '';
        form.value.color = '';
        form.value.battery_health = '';
        form.value.battery_condition = '';
    }
);

watch(
    () => form.value.model,
    () => {
        form.value.storage = '';
        form.value.color = '';
    }
);

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const formatPriceInput = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    return digits ? Number(digits).toLocaleString('fa-IR') : '';
};

const setPrice = (event) => {
    form.value.max_price = normalizeDigits(event.target.value)
        .replace(/\D/g, '');
};

const setMobile = (event) => {
    const normalized = normalizeDigits(event.target.value)
        .replace(/[^\d+\-()\s]/g, '')
        .slice(0, 20);

    form.value.requester_mobile = toPersianDigits(normalized);
    event.target.value = form.value.requester_mobile;
};

const setBatteryHealth = (event) => {
    const normalized = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 3);

    if (!normalized) {
        form.value.battery_health = '';
        return;
    }

    form.value.battery_health = String(
        Math.min(100, Math.max(0, Number(normalized)))
    );
};

const fieldError = (field) => errors.value[field]?.[0] ?? '';

const csrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '';

const specificationSummary = computed(() => {
    const parts = [
        form.value.storage,
        form.value.color ? colorLabel(form.value.color) : '',
        conditionOptions.find(
            (item) => item.value === form.value.condition_grade
        )?.label,
        registrationOptions.find(
            (item) => item.value === form.value.registration_status
        )?.label,
    ].filter(
        (item) =>
            item &&
            !item.includes('مهم نیست')
    );

    if (isSamsung.value && form.value.battery_condition) {
        parts.push(
            samsungBatteryOptions.find(
                (item) => item.value === form.value.battery_condition
            )?.label
        );
    }

    if (!isSamsung.value && form.value.battery_health !== '') {
        parts.push(`باتری ${toPersianDigits(form.value.battery_health)}٪`);
    }

    return parts;
});

const submit = async () => {
    if (!specificationsComplete.value) {
        errors.value = {
            ...errors.value,
            ...(!form.value.condition_grade
                ? {
                    condition_grade: [
                        'وضعیت ظاهری رو انتخاب کن؛ این یکی برای تشخیص قیمت مهمه.',
                    ],
                }
                : {}),
            ...(!form.value.registration_status
                ? {
                    registration_status: [
                        'رجیستری باید مشخص باشه؛ ثبت‌شده یا نشده.',
                    ],
                }
                : {}),
            ...(
                isSamsung.value && !form.value.battery_condition
                    ? {
                        battery_condition: [
                            'وضعیت باتری رو هم مشخص کن تا قیمت درست سنجیده بشه.',
                        ],
                    }
                    : {}
            ),
            ...(
                !isSamsung.value &&
                form.value.battery_health === ''
                    ? {
                        battery_health: [
                            'سلامت باتری رو وارد کن؛ بدون اون قیمت دقیق درنمیاد.',
                        ],
                    }
                    : {}
            ),
        };

        return;
    }

    loading.value = true;
    submitted.value = null;
    errors.value = {};
    generalError.value = '';
    marketFeedback.value = null;

    try {
        const response = await fetch(
            route('features.wanted.store'),
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    ...form.value,
                    battery_health:
                        !isSamsung.value &&
                        form.value.battery_health !== ''
                            ? Number(form.value.battery_health)
                            : null,
                    battery_condition:
                        isSamsung.value &&
                        form.value.battery_condition
                            ? form.value.battery_condition
                            : null,
                    max_price: Number(form.value.max_price || 0),
                }),
            }
        );

        const data = await response.json();

        if (response.status === 422) {
            errors.value = data.errors ?? {};
            marketFeedback.value = data.market_feedback ?? null;

            generalError.value = marketFeedback.value
                ? ''
                : data.message ?? 'اطلاعات واردشده معتبر نیست.';

            if (marketFeedback.value) {
                requestAnimationFrame(() => {
                    document
                        .querySelector('.market-feedback-card')
                        ?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                        });
                });
            }

            return;
        }

        if (!response.ok) {
            generalError.value =
                data.message ??
                'ثبت درخواست انجام نشد. دوباره تلاش کنید.';
            return;
        }

        submitted.value = data.request;
    } catch (error) {
        generalError.value =
            'ثبت درخواست انجام نشد. اتصال را بررسی کنید و دوباره تلاش کنید.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head title="چی می‌خوام؟ | مایاهمراه" />

    <div dir="rtl" class="wanted-page">
        <div class="page-shell">
            <div class="mesh mesh-a" />
            <div class="mesh mesh-b" />
            <div class="mesh mesh-c" />

            <header class="topbar">
                <div class="topbar-title">
                    <Link
                        :href="route('features.index')"
                        class="back-button"
                        aria-label="بازگشت به امکانات"
                    >
                        ←
                    </Link>

                    <div>
                        <strong>چی می‌خوام؟</strong>
                        <small>Maya Wanted</small>
                    </div>
                </div>

                <span class="public-pill">عمومی</span>
            </header>

            <main class="main-content">
                <section class="hero">
                    <div class="hero-copy">
                        <small class="eyebrow">
                            درخواستت را به بازار همکارها بفرست
                        </small>

                        <h1>
                            بگو دقیقاً
                            <span>دنبال چی هستی</span>
                        </h1>

                        <p>
                            مدل، مشخصات و سقف خریدت را ثبت کن. این درخواست
                            به یک سیگنال واقعی تقاضای بازار تبدیل می‌شود و
                            در ادامه همکارها می‌توانند آن را ببینند.
                        </p>

                        <div class="hero-tags">
                            <span>مدل دقیق</span>
                            <span>سقف قیمت</span>
                            <span>شرایط دلخواه</span>
                        </div>
                    </div>

                    <div class="radar-card">
                        <div class="radar">
                            <span class="radar-ring ring-one" />
                            <span class="radar-ring ring-two" />
                            <span class="radar-ring ring-three" />
                            <span class="radar-sweep" />

                            <div class="radar-core">
                                <small>تقاضای من</small>
                                <strong>
                                    {{ form.model || 'گوشی' }}
                                </strong>
                                <span>
                                    {{
                                        form.storage
                                            ? `${form.storage}`
                                            : 'هنوز انتخاب نشده'
                                    }}
                                </span>
                            </div>

                            <span class="ping ping-a" />
                            <span class="ping ping-b" />
                            <span class="ping ping-c" />
                        </div>

                        <div class="radar-footer">
                            <span>Market signal</span>
                            <strong>درخواست واقعی خرید</strong>
                        </div>
                    </div>
                </section>

                <section class="workspace">
                    <form
                        class="request-card"
                        @submit.prevent="submit"
                    >
                        <div class="section-head">
                            <div>
                                <small>01 / دستگاه</small>
                                <h2>گوشی موردنظر</h2>
                            </div>

                            <span class="head-chip">
                                {{ form.brand || 'انتخاب دستگاه' }}
                            </span>
                        </div>

                        <div class="device-grid">
                            <div class="field">
                                <span>برند</span>
                                <MayaSelect
                                    v-model="form.brand"
                                    :options="brandOptions"
                                    placeholder="انتخاب برند"
                                />
                                <small v-if="fieldError('brand')" class="error">
                                    {{ fieldError('brand') }}
                                </small>
                            </div>

                            <div class="field">
                                <span>مدل</span>
                                <MayaSelect
                                    v-model="form.model"
                                    :options="modelOptions"
                                    :disabled="!form.brand"
                                    placeholder="انتخاب مدل"
                                />
                                <small v-if="fieldError('model')" class="error">
                                    {{ fieldError('model') }}
                                </small>
                            </div>

                            <div class="field">
                                <span>حافظه</span>
                                <MayaSelect
                                    v-model="form.storage"
                                    :options="storageOptions"
                                    :disabled="!form.model"
                                    placeholder="انتخاب حافظه"
                                />
                                <small v-if="fieldError('storage')" class="error">
                                    {{ fieldError('storage') }}
                                </small>
                            </div>

                            <div class="field">
                                <span>رنگ</span>
                                <MayaSelect
                                    v-model="form.color"
                                    :options="colorOptions"
                                    :disabled="!form.model"
                                    placeholder="رنگ مهم نیست"
                                />
                                <small v-if="fieldError('color')" class="error">
                                    {{ fieldError('color') }}
                                </small>
                            </div>
                        </div>

                        <div
                            class="mobile-live-preview"
                            :style="phonePreviewStyle"
                        >
                            <div class="mobile-preview-head">
                                <div>
                                    <small>پیش‌نمایش زنده</small>
                                    <strong>
                                        {{
                                            form.model ||
                                            'گوشی موردنظر'
                                        }}
                                    </strong>
                                </div>

                                <span class="mobile-live-badge">
                                    LIVE
                                </span>
                            </div>

                            <div class="mobile-phone-scene">
                                <div class="mobile-phone-aura" />

                                <div
                                    class="mobile-phone-shape"
                                    :class="conditionClass"
                                >
                                    <div class="mobile-phone-island" />

                                    <div class="mobile-phone-screen">
                                        <div
                                            v-if="form.brand"
                                            class="mobile-brand-mark"
                                            :class="{
                                                apple: isApple,
                                                samsung: isSamsung,
                                            }"
                                        >
                                            <template v-if="isApple">
                                                <span></span>
                                            </template>

                                            <template v-else-if="isSamsung">
                                                <strong>
                                                    SAMSUNG
                                                </strong>
                                            </template>

                                            <template v-else>
                                                <strong>
                                                    {{ form.brand }}
                                                </strong>
                                            </template>
                                        </div>

                                        <div class="mobile-phone-copy">
                                            <small>
                                                {{
                                                    form.brand ||
                                                    'Brand'
                                                }}
                                            </small>

                                            <strong>
                                                {{
                                                    form.model ||
                                                    'مدل گوشی'
                                                }}
                                            </strong>

                                            <span>
                                                {{
                                                    form.storage ||
                                                    'حافظه'
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="mobile-condition-layer"
                                            aria-hidden="true"
                                        >
                                            <i
                                                class="mobile-scratch scratch-a"
                                            />
                                            <i
                                                class="mobile-scratch scratch-b"
                                            />
                                            <i
                                                class="mobile-scratch scratch-c"
                                            />

                                            <span
                                                class="mobile-polish"
                                            />

                                            <span
                                                class="mobile-shine"
                                            >
                                                ✦
                                            </span>
                                        </div>

                                        <div
                                            v-if="batteryPreviewVisible"
                                            class="mobile-battery"
                                            :class="`battery-${batteryPreviewLevel}`"
                                        >
                                            <span class="mobile-battery-icon">
                                                <i />
                                            </span>

                                            <strong>
                                                {{ batteryPreviewText }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="mobile-preview-meta">
                                    <span>
                                        <i
                                            class="mobile-color-dot"
                                        />
                                        {{
                                            form.color
                                                ? colorLabel(form.color)
                                                : 'رنگ'
                                        }}
                                    </span>

                                    <span
                                        :class="conditionClass"
                                    >
                                        {{
                                            conditionPreviewLabel
                                        }}
                                    </span>
                                </div>
                            </div>

                            <small class="mobile-preview-hint">
                                رنگ، تمیزی و باتری رو تغییر بده؛
                                گوشی همون لحظه واکنش نشون می‌ده.
                            </small>
                        </div>

                        <div class="specification-band">
                            <div class="field">
                                <span>وضعیت ظاهری</span>
                                <MayaSelect
                                    v-model="form.condition_grade"
                                    :options="conditionOptions"
                                    placeholder="انتخاب وضعیت ظاهری"
                                />
                                <small
                                    v-if="fieldError('condition_grade')"
                                    class="error"
                                >
                                    {{ fieldError('condition_grade') }}
                                </small>
                            </div>

                            <div class="field">
                                <span>رجیستری</span>
                                <MayaSelect
                                    v-model="form.registration_status"
                                    :options="registrationOptions"
                                    placeholder="انتخاب رجیستری"
                                />
                                <small
                                    v-if="fieldError('registration_status')"
                                    class="error"
                                >
                                    {{ fieldError('registration_status') }}
                                </small>
                            </div>

                            <div v-if="isSamsung" class="field">
                                <span>وضعیت باتری</span>
                                <MayaSelect
                                    v-model="form.battery_condition"
                                    :options="samsungBatteryOptions"
                                    placeholder="انتخاب وضعیت باتری"
                                />
                                <small
                                    v-if="fieldError('battery_condition')"
                                    class="error"
                                >
                                    {{ fieldError('battery_condition') }}
                                </small>
                            </div>

                            <label v-else class="field">
                                <span>حداقل سلامت باتری</span>

                                <div class="percent-input">
                                    <input
                                        :value="toPersianDigits(form.battery_health)"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="3"
                                        placeholder="مثلاً ۹۰"
                                        @input="setBatteryHealth"
                                    >
                                    <b>٪</b>
                                </div>

                                <small v-if="fieldError('battery_health')" class="error">
                                    {{ fieldError('battery_health') }}
                                </small>
                            </label>
                        </div>

                        <div class="price-zone">
                            <div>
                                <small>02 / قیمت</small>
                                <h2>تا چقدر می‌خری؟</h2>
                                <p>
                                    سقف واقعی خریدت را وارد کن؛ این عدد یکی از
                                    ارزشمندترین سیگنال‌های بازار مایاهمراه است.
                                </p>
                            </div>

                            <label class="price-input">
                                <input
                                    :value="formatPriceInput(form.max_price)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۸۵,۰۰۰,۰۰۰"
                                    @input="setPrice"
                                >
                                <span>تومان</span>
                            </label>

                            <small
                                v-if="fieldError('max_price') && !marketFeedback"
                                class="error price-error"
                            >
                                {{ fieldError('max_price') }}
                            </small>

                            <Transition name="market-feedback">
                                <div
                                    v-if="marketFeedback"
                                    class="market-feedback-card"
                                >
                                    <div class="market-feedback-icon">
                                        <span>!</span>
                                    </div>

                                    <div class="market-feedback-content">
                                        <small class="market-feedback-label">
                                            مایا بازار رو چک کرد
                                        </small>

                                        <h3>
                                            {{ marketFeedback.headline }}
                                        </h3>

                                        <p>
                                            {{ marketFeedback.body }}
                                        </p>

                                        <div class="market-feedback-stats">
                                            <div>
                                                <span>قیمت شما</span>
                                                <strong>
                                                    {{
                                                        formatMoney(
                                                            marketFeedback.candidate_price
                                                        )
                                                    }}
                                                </strong>
                                                <small>تومان</small>
                                            </div>

                                            <div
                                                v-if="
                                                    marketFeedback.demand_reference_price
                                                "
                                            >
                                                <span>
                                                    مرجع بازار
                                                </span>
                                                <strong>
                                                    {{
                                                        formatMoney(
                                                            marketFeedback.demand_reference_price
                                                        )
                                                    }}
                                                </strong>
                                                <small>تومان</small>
                                            </div>

                                            <div
                                                v-if="
                                                    marketFeedback.sale_reference_price
                                                "
                                            >
                                                <span>
                                                    فروش مشابه
                                                </span>
                                                <strong>
                                                    {{
                                                        formatMoney(
                                                            marketFeedback.sale_reference_price
                                                        )
                                                    }}
                                                </strong>
                                                <small>تومان</small>
                                            </div>
                                        </div>

                                        <div class="market-feedback-final">
                                            پس این درخواست رو ثبت نمی‌کنم.
                                            یه عدد واقعی‌تر بزن 😉
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <div class="contact-zone">
                            <div class="section-head compact">
                                <div>
                                    <small>03 / تماس</small>
                                    <h2>همکارها چطور پیدات کنند؟</h2>
                                </div>
                            </div>

                            <div class="contact-grid">
                                <label class="text-field">
                                    <span>نام شما</span>
                                    <input
                                        v-model="form.requester_name"
                                        type="text"
                                        maxlength="150"
                                        placeholder="نام یا نام فروشگاه"
                                    >
                                    <small v-if="fieldError('requester_name')" class="error">
                                        {{ fieldError('requester_name') }}
                                    </small>
                                </label>

                                <label class="text-field">
                                    <span>شماره موبایل</span>
                                    <input
                                        :value="form.requester_mobile"
                                        type="text"
                                        inputmode="tel"
                                        maxlength="20"
                                        placeholder="۰۹۱۲..."
                                        @input="setMobile"
                                    >
                                    <small v-if="fieldError('requester_mobile')" class="error">
                                        {{ fieldError('requester_mobile') }}
                                    </small>
                                </label>
                            </div>

                            <label class="text-field description-field">
                                <span>توضیحات تکمیلی</span>
                                <textarea
                                    v-model="form.description"
                                    maxlength="2000"
                                    rows="4"
                                    placeholder="مثلاً جعبه مهم است، خط دور فریم نداشته باشد، فقط رنگ خاصی می‌خواهم..."
                                />
                                <small v-if="fieldError('description')" class="error">
                                    {{ fieldError('description') }}
                                </small>
                            </label>
                        </div>

                        <div v-if="generalError" class="message error-message">
                            {{ generalError }}
                        </div>

                        <div v-if="submitted" class="message success-message">
                            <span>✓</span>
                            <div>
                                <strong>درخواستت ثبت شد</strong>
                                <small>
                                    {{ submitted.model }}
                                    {{ submitted.storage }}
                                    تا
                                    {{ formatMoney(submitted.max_price) }}
                                    تومان
                                </small>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="submit-button"
                            :disabled="
                                loading ||
                                !form.brand ||
                                !form.model ||
                                !form.storage ||
                                !specificationsComplete ||
                                !form.max_price ||
                                !form.requester_name ||
                                !form.requester_mobile
                            "
                        >
                            <span>
                                {{
                                    loading
                                        ? 'در حال ثبت درخواست...'
                                        : 'ثبت «چی می‌خوام؟»'
                                }}
                            </span>

                            <b>←</b>
                        </button>
                    </form>

                    <aside class="preview-card">
                        <div class="preview-head">
                            <small>پیش‌نمایش تقاضا</small>
                            <span class="live-dot">LIVE</span>
                        </div>

                        <div
                            class="phone-stage"
                            :style="phonePreviewStyle"
                        >
                            <div class="phone-aura" />

                            <div
                                class="phone-shape"
                                :class="conditionClass"
                            >
                                <div class="phone-island" />

                                <div class="phone-screen">
                                    <div
                                        v-if="form.brand"
                                        class="brand-mark"
                                        :class="{
                                            apple: isApple,
                                            samsung: isSamsung,
                                            generic: !isApple && !isSamsung,
                                        }"
                                    >
                                        <template v-if="isApple">
                                            <span class="apple-symbol"></span>
                                            <small>APPLE</small>
                                        </template>

                                        <template v-else-if="isSamsung">
                                            <span class="samsung-logo">
                                                <strong>SAMSUNG</strong>
                                            </span>
                                        </template>

                                        <template v-else>
                                            <strong>{{ form.brand }}</strong>
                                        </template>
                                    </div>

                                    <div class="phone-copy">
                                        <small>{{ form.brand || 'Brand' }}</small>
                                        <strong>{{ form.model || 'مدل گوشی' }}</strong>
                                        <span>{{ form.storage || 'حافظه' }}</span>
                                    </div>

                                    <div class="condition-layer" aria-hidden="true">
                                        <i class="micro-scratch scratch-one" />
                                        <i class="micro-scratch scratch-two" />
                                        <i class="micro-scratch scratch-three" />
                                        <i class="micro-scratch scratch-four" />

                                        <i class="edge-scuff scuff-one" />
                                        <i class="edge-scuff scuff-two" />

                                        <span class="screen-polish" />

                                        <span class="shine-star shine-star-main">
                                            ✦
                                        </span>
                                        <span class="shine-star shine-star-mini">
                                            ✧
                                        </span>
                                    </div>

                                    <div
                                        v-if="batteryPreviewVisible"
                                        class="battery-badge"
                                        :class="`battery-${batteryPreviewLevel}`"
                                    >
                                        <span class="battery-icon">
                                            <i />
                                        </span>

                                        <strong>{{ batteryPreviewText }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="phone-visual-meta">
                                <span class="visual-chip color-chip">
                                    <i />
                                    {{
                                        form.color
                                            ? colorLabel(form.color)
                                            : 'رنگ انتخاب نشده'
                                    }}
                                </span>

                                <span
                                    class="visual-chip condition-chip"
                                    :class="conditionClass"
                                >
                                    {{ conditionPreviewLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="request-summary">
                            <div class="summary-price">
                                <small>سقف خرید</small>
                                <strong>
                                    {{
                                        form.max_price
                                            ? formatMoney(form.max_price)
                                            : '—'
                                    }}
                                </strong>
                                <span>تومان</span>
                            </div>

                            <div class="summary-specs">
                                <span
                                    v-for="item in specificationSummary"
                                    :key="item"
                                >
                                    {{ item }}
                                </span>

                                <span v-if="!specificationSummary.length">
                                    مشخصات تکمیلی هنوز انتخاب نشده
                                </span>
                            </div>
                        </div>

                        <div class="data-note">
                            <span class="data-icon">↗</span>
                            <div>
                                <strong>این فقط یک فرم نیست</strong>
                                <p>
                                    قیمت مدنظر و مشخصات درخواست‌شده به ساخت
                                    دیتای واقعی تقاضای بازار کمک می‌کند.
                                </p>
                            </div>
                        </div>
                    </aside>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
.market-feedback-card {
    position: relative;
    display: grid;
    grid-template-columns: 58px minmax(0, 1fr);
    gap: 16px;
    margin-top: 18px;
    padding: 20px;
    overflow: hidden;
    border: 1px solid rgba(199, 67, 93, .28);
    border-radius: 24px;
    background:
        radial-gradient(circle at 5% 15%, rgba(255, 103, 131, .22), transparent 32%),
        linear-gradient(135deg, #fff1f4 0%, #fff8f2 58%, #fff 100%);
    box-shadow:
        0 18px 46px rgba(139, 43, 66, .15),
        inset 0 1px 0 rgba(255,255,255,.9);
}

.market-feedback-card::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(
        110deg,
        transparent 32%,
        rgba(255,255,255,.72) 50%,
        transparent 68%
    );
    transform: translateX(-130%);
    animation: market-feedback-scan 1.8s ease-out .15s 1;
}

.market-feedback-icon {
    position: relative;
    z-index: 1;
    display: grid;
    width: 54px;
    height: 54px;
    place-items: center;
    border-radius: 18px;
    background: #c7435d;
    color: white;
    box-shadow: 0 10px 24px rgba(199, 67, 93, .28);
}

.market-feedback-icon span {
    font-size: 25px;
    font-weight: 950;
}

.market-feedback-content {
    position: relative;
    z-index: 1;
    min-width: 0;
}

.market-feedback-label {
    color: #bd4058;
    font-size: 10px;
    font-weight: 950;
}

.market-feedback-content h3 {
    margin: 5px 0 0;
    color: #382027;
    font-size: 18px;
    line-height: 1.8;
    font-weight: 950;
}

.market-feedback-content > p {
    margin: 8px 0 0;
    color: #755960;
    font-size: 12px;
    line-height: 2.05;
    font-weight: 650;
}

.market-feedback-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin-top: 15px;
}

.market-feedback-stats > div {
    padding: 10px 12px;
    border: 1px solid rgba(190, 70, 94, .1);
    border-radius: 15px;
    background: rgba(255,255,255,.72);
}

.market-feedback-stats span,
.market-feedback-stats small {
    display: block;
    color: #92747c;
    font-size: 9px;
    font-weight: 800;
}

.market-feedback-stats strong {
    display: block;
    margin-top: 3px;
    color: #41282f;
    font-size: 14px;
    font-weight: 950;
}

.market-feedback-final {
    margin-top: 14px;
    padding: 10px 13px;
    border-radius: 13px;
    background: rgba(199, 67, 93, .09);
    color: #a4384e;
    font-size: 12px;
    font-weight: 950;
}

.market-feedback-enter-active {
    animation: market-feedback-in .45s cubic-bezier(.2,.8,.2,1);
}

.market-feedback-leave-active {
    transition: opacity .18s ease, transform .18s ease;
}

.market-feedback-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

@keyframes market-feedback-in {
    from {
        opacity: 0;
        transform: translateY(16px) scale(.97);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes market-feedback-scan {
    from { transform: translateX(-130%); }
    to { transform: translateX(130%); }
}

@media (min-width: 641px) {
    .market-feedback-card {
        grid-column: 1 / -1;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .market-feedback-content {
        min-width: 0;
    }

    .market-feedback-stats {
        grid-template-columns:
            repeat(auto-fit, minmax(150px, 1fr));
        width: 100%;
        min-width: 0;
    }

    .market-feedback-stats > div {
        min-width: 0;
        overflow: hidden;
    }

    .market-feedback-stats strong {
        max-width: 100%;
        overflow-wrap: anywhere;
        font-size: clamp(12px, 1.35vw, 14px);
    }
}

@media (max-width: 640px) {
    .market-feedback-card {
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 11px;
        padding: 15px;
        border-radius: 20px;
    }

    .market-feedback-icon {
        width: 40px;
        height: 40px;
        border-radius: 13px;
    }

    .market-feedback-icon span {
        font-size: 18px;
    }

    .market-feedback-stats {
        grid-template-columns: 1fr;
    }

    .market-feedback-stats > div {
        display: grid;
        grid-template-columns: 1fr auto auto;
        align-items: center;
        gap: 5px;
    }

    .market-feedback-stats strong {
        margin-top: 0;
    }
}

.mobile-live-preview {
    display: none;
}

.wanted-page {
    min-height: 100dvh;
    padding: 24px;
    background: #88898b;
    color: #1d1722;
}

.page-shell {
    position: relative;
    width: min(1240px, 100%);
    margin: 0 auto;
    overflow: hidden;
    border-radius: 42px;
    background:
        radial-gradient(circle at 88% 9%, rgba(242, 174, 255, 0.55), transparent 25%),
        radial-gradient(circle at 5% 68%, rgba(143, 239, 213, 0.38), transparent 24%),
        linear-gradient(135deg, #f3edf8 0%, #eef5f2 48%, #f7eef6 100%);
    box-shadow:
        0 28px 80px rgba(37, 46, 61, 0.17),
        inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.mesh {
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    filter: blur(2px);
}

.mesh-a {
    width: 330px;
    height: 330px;
    top: 120px;
    right: -150px;
    background: rgba(236, 113, 255, 0.15);
}

.mesh-b {
    width: 240px;
    height: 240px;
    left: -110px;
    bottom: 260px;
    background: rgba(80, 224, 177, 0.14);
}

.mesh-c {
    width: 180px;
    height: 180px;
    right: 34%;
    bottom: -90px;
    background: rgba(255, 199, 104, 0.16);
}

.topbar,
.main-content {
    position: relative;
    z-index: 1;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 28px 10px;
}

.topbar-title {
    display: flex;
    align-items: center;
    gap: 14px;
}

.topbar-title > div {
    display: flex;
    flex-direction: column;
}

.topbar-title strong {
    font-size: 31px;
    font-weight: 950;
}

.topbar-title small {
    margin-top: 2px;
    color: #817888;
    font-size: 12px;
}

.back-button {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.78);
    color: #1b1720;
    font-size: 21px;
    box-shadow: 0 8px 24px rgba(72, 54, 80, 0.08);
}

.public-pill,
.head-chip,
.live-dot {
    border-radius: 999px;
    font-weight: 850;
}

.public-pill {
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.64);
    font-size: 12px;
}

.main-content {
    display: grid;
    gap: 18px;
    padding: 8px 28px 28px;
}

.hero {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(300px, 0.75fr);
    gap: 22px;
    align-items: stretch;
}

.hero-copy,
.radar-card,
.request-card,
.preview-card {
    border: 1px solid rgba(255, 255, 255, 0.58);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.75),
        0 16px 44px rgba(60, 50, 68, 0.07);
    backdrop-filter: blur(12px);
}

.hero-copy {
    padding: 34px;
    border-radius: 36px;
    background: rgba(255, 255, 255, 0.43);
}

.eyebrow,
.section-head small,
.preview-head small {
    color: #817888;
    font-size: 12px;
    font-weight: 800;
}

.hero-copy h1 {
    max-width: 660px;
    margin: 10px 0 12px;
    font-size: clamp(40px, 6vw, 72px);
    line-height: 1.05;
    letter-spacing: -0.055em;
    font-weight: 950;
}

.hero-copy h1 span {
    color: #a53cba;
}

.hero-copy p {
    max-width: 680px;
    margin: 0;
    color: #6e6673;
    font-size: 15px;
    line-height: 2;
}

.hero-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 20px;
}

.hero-tags span {
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.7);
    color: #6e5c73;
    font-size: 11px;
    font-weight: 800;
}

.radar-card {
    min-height: 330px;
    padding: 20px;
    border-radius: 36px;
    background: linear-gradient(145deg, #241c29, #302037);
    color: #fff;
    display: flex;
    flex-direction: column;
}

.radar {
    position: relative;
    flex: 1;
    min-height: 250px;
    display: grid;
    place-items: center;
    overflow: hidden;
    border-radius: 26px;
    background:
        radial-gradient(circle, rgba(224, 125, 239, 0.12), transparent 58%),
        #211925;
}

.radar-ring {
    position: absolute;
    border: 1px solid rgba(234, 161, 244, 0.2);
    border-radius: 999px;
}

.ring-one {
    width: 100px;
    height: 100px;
}

.ring-two {
    width: 180px;
    height: 180px;
}

.ring-three {
    width: 265px;
    height: 265px;
}

.radar::before,
.radar::after {
    content: '';
    position: absolute;
    background: rgba(234, 161, 244, 0.12);
}

.radar::before {
    width: 1px;
    height: 100%;
}

.radar::after {
    width: 100%;
    height: 1px;
}

.radar-sweep {
    position: absolute;
    width: 132px;
    height: 132px;
    top: 50%;
    left: 50%;
    transform-origin: 0 0;
    background: linear-gradient(
        35deg,
        rgba(215, 104, 235, 0.24),
        transparent 58%
    );
    clip-path: polygon(0 0, 100% 0, 0 100%);
    animation: sweep 5s linear infinite;
}

@keyframes sweep {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.radar-core {
    position: relative;
    z-index: 3;
    display: flex;
    width: 118px;
    height: 118px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: linear-gradient(145deg, #de77ed, #9f3db1);
    box-shadow: 0 18px 50px rgba(199, 80, 219, 0.28);
    text-align: center;
}

.radar-core small {
    font-size: 9px;
    opacity: 0.8;
}

.radar-core strong {
    max-width: 92px;
    margin: 4px 0 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 14px;
}

.radar-core span {
    font-size: 10px;
    opacity: 0.8;
}

.ping {
    position: absolute;
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #83f0cf;
    box-shadow: 0 0 0 5px rgba(131, 240, 207, 0.1);
}

.ping-a {
    top: 22%;
    right: 22%;
}

.ping-b {
    bottom: 25%;
    left: 20%;
}

.ping-c {
    top: 54%;
    left: 26%;
    background: #f5cb6a;
}

.radar-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 6px 1px;
}

.radar-footer span {
    color: #af9db5;
    font-size: 10px;
}

.radar-footer strong {
    font-size: 11px;
}

.workspace {
    display: grid;
    grid-template-columns: minmax(0, 1.12fr) minmax(320px, 0.88fr);
    gap: 18px;
    align-items: start;
}

.request-card,
.preview-card {
    border-radius: 34px;
    background: rgba(255, 255, 255, 0.6);
}

.request-card {
    padding: 26px;
}

.preview-card {
    position: sticky;
    top: 18px;
    padding: 24px;
}

.section-head,
.preview-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.section-head {
    margin-bottom: 20px;
}

.section-head.compact {
    margin-bottom: 14px;
}

.section-head h2 {
    margin: 4px 0 0;
    font-size: 23px;
    font-weight: 950;
}

.head-chip {
    padding: 8px 11px;
    background: #251c2a;
    color: #fff;
    font-size: 10px;
}

.device-grid,
.specification-band,
.contact-grid {
    display: grid;
    gap: 14px;
}

.device-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.specification-band {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: 14px;
    padding: 17px;
    border-radius: 23px;
    background: rgba(153, 74, 169, 0.06);
}

.field,
.text-field {
    display: grid;
    gap: 7px;
}

.field > span,
.text-field > span {
    color: #615868;
    font-size: 11px;
    font-weight: 850;
}

.percent-input,
.text-field input,
.text-field textarea {
    border: 1px solid #dfd8e1;
    border-radius: 14px;
    background: #fff;
    color: #251f29;
    font-family: inherit;
}

.percent-input {
    display: flex;
    min-height: 46px;
    align-items: center;
    padding: 0 13px;
}

.percent-input input {
    width: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    font-family: inherit;
    font-weight: 750;
}

.percent-input b {
    color: #8c8191;
    font-size: 11px;
}

.price-zone {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(240px, 0.8fr);
    gap: 18px;
    align-items: center;
    margin-top: 18px;
    padding: 22px;
    border-radius: 26px;
    background: linear-gradient(145deg, #241b29, #302036);
    color: #fff;
}

.price-zone small {
    color: #af9bb5;
    font-size: 10px;
    font-weight: 800;
}

.price-zone h2 {
    margin: 4px 0 5px;
    font-size: 23px;
    font-weight: 950;
}

.price-zone p {
    max-width: 480px;
    margin: 0;
    color: #baaebe;
    font-size: 11px;
    line-height: 1.8;
}

.price-input {
    display: flex;
    min-height: 62px;
    align-items: center;
    gap: 8px;
    padding: 0 16px;
    border-radius: 19px;
    background: #fff;
    color: #241c28;
}

.price-input input {
    width: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    color: inherit;
    font-family: inherit;
    font-size: 17px;
    font-weight: 950;
}

.price-input span {
    white-space: nowrap;
    color: #847989;
    font-size: 10px;
}

.price-error {
    grid-column: 1 / -1;
}

.contact-zone {
    margin-top: 18px;
    padding-top: 20px;
    border-top: 1px solid rgba(92, 72, 99, 0.1);
}

.contact-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.text-field input,
.text-field textarea {
    width: 100%;
    outline: 0;
    padding: 0 13px;
    font-size: 12px;
    font-weight: 700;
}

.text-field input {
    min-height: 48px;
}

.text-field textarea {
    resize: vertical;
    min-height: 95px;
    padding-top: 12px;
    line-height: 1.8;
}

.description-field {
    margin-top: 14px;
}

.error {
    color: #bc4058;
    font-size: 10px;
    line-height: 1.7;
}

.message {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-top: 16px;
    padding: 13px 15px;
    border-radius: 16px;
}

.error-message {
    background: #fff0f3;
    color: #a9334d;
    font-size: 12px;
}

.success-message {
    background: #e9f8f1;
    color: #176747;
}

.success-message > span {
    display: grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: #77d8ad;
    color: #153f30;
    font-weight: 950;
}

.success-message div {
    display: grid;
    gap: 2px;
}

.success-message strong {
    font-size: 12px;
}

.success-message small {
    font-size: 10px;
}

.submit-button {
    display: flex;
    width: 100%;
    min-height: 60px;
    align-items: center;
    justify-content: space-between;
    margin-top: 18px;
    padding: 0 18px;
    border: 0;
    border-radius: 19px;
    background: #a441b7;
    color: #fff;
    font-family: inherit;
    font-size: 14px;
    font-weight: 950;
    cursor: pointer;
    box-shadow: 0 13px 28px rgba(164, 65, 183, 0.2);
}

.submit-button:disabled {
    opacity: 0.48;
    cursor: not-allowed;
    box-shadow: none;
}

.submit-button b {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
}

.preview-head {
    margin-bottom: 20px;
}

.live-dot {
    padding: 6px 9px;
    background: #dff7ee;
    color: #268065;
    font-size: 9px;
    letter-spacing: 0.08em;
}

.phone-stage {
    position: relative;
    display: grid;
    place-items: center;
    margin: 4px auto 22px;
    padding: 28px 0 4px;
    isolation: isolate;
}

.phone-aura {
    position: absolute;
    z-index: -1;
    width: 78%;
    aspect-ratio: 1;
    border-radius: 999px;
    background:
        radial-gradient(
            circle,
            var(--phone-soft) 0%,
            var(--phone-glow) 36%,
            transparent 70%
        );
    filter: blur(12px);
    transform: translateY(8%);
    transition:
        background 420ms ease,
        transform 420ms ease;
}

.phone-shape {
    position: relative;
    width: min(230px, 72%);
    aspect-ratio: 0.55;
    padding: 8px;
    border: 1px solid color-mix(in srgb, var(--phone-edge) 72%, #fff 28%);
    border-radius: 42px;
    background:
        linear-gradient(
            145deg,
            color-mix(in srgb, var(--phone-frame) 82%, #fff 18%),
            var(--phone-frame) 55%,
            var(--phone-edge)
        );
    box-shadow:
        0 28px 55px var(--phone-glow),
        inset 0 1px 0 rgba(255, 255, 255, 0.34),
        inset 0 -2px 7px rgba(0, 0, 0, 0.22);
    transform: rotate(-1.2deg);
    transition:
        background 420ms ease,
        border-color 420ms ease,
        box-shadow 420ms ease,
        transform 420ms cubic-bezier(.2,.8,.2,1),
        filter 420ms ease;
}

.phone-shape:hover {
    transform: rotate(0deg) translateY(-3px) scale(1.012);
}

.phone-screen {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.24);
    border-radius: 34px;
    background:
        radial-gradient(
            circle at 50% 22%,
            var(--phone-soft),
            transparent 31%
        ),
        linear-gradient(
            160deg,
            var(--phone-screen-a),
            var(--phone-screen-b)
        );
    text-align: center;
    transition:
        background 420ms ease,
        filter 320ms ease;
}

.phone-screen::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        linear-gradient(
            118deg,
            rgba(255, 255, 255, 0.24) 0%,
            rgba(255, 255, 255, 0.05) 24%,
            transparent 42%
        );
    opacity: 0.75;
}

.phone-island {
    position: absolute;
    z-index: 8;
    top: 18px;
    left: 50%;
    width: 72px;
    height: 19px;
    transform: translateX(-50%);
    border-radius: 999px;
    background: #171219;
    box-shadow:
        inset 0 1px 1px rgba(255, 255, 255, 0.08),
        0 2px 5px rgba(0, 0, 0, 0.16);
}

.phone-copy {
    position: relative;
    z-index: 4;
    display: flex;
    max-width: 86%;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transform: translateY(16px);
}

.phone-copy > small {
    color: #806f86;
    font-size: 10px;
}

.phone-copy > strong {
    max-width: 100%;
    margin: 5px 0 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 21px;
    font-weight: 950;
}

.phone-copy > span {
    color: #846e89;
    font-size: 11px;
}

.brand-mark {
    position: absolute;
    z-index: 5;
    top: 58px;
    left: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: translateX(-50%);
    color: color-mix(in srgb, var(--phone-edge) 82%, #111 18%);
    transition:
        color 360ms ease,
        transform 360ms ease;
}

.brand-mark.apple {
    flex-direction: column;
    gap: 1px;
}

.apple-symbol {
    display: block;
    font-family:
        -apple-system,
        BlinkMacSystemFont,
        'Segoe UI',
        sans-serif;
    font-size: 34px;
    line-height: 0.95;
    font-weight: 500;
}

.brand-mark.apple small {
    margin-top: 3px;
    font-size: 6px;
    font-weight: 900;
    letter-spacing: 0.22em;
    opacity: 0.6;
}

.samsung-logo {
    position: relative;
    display: grid;
    place-items: center;
    min-width: 76px;
    height: 29px;
    transform: rotate(-4deg);
}

.samsung-logo::before {
    content: '';
    position: absolute;
    inset: 4px 1px;
    border: 1.4px solid currentColor;
    border-radius: 50%;
    transform: skewX(-13deg);
    opacity: 0.92;
}

.brand-mark.samsung strong {
    position: relative;
    z-index: 1;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
    font-size: 8px;
    font-weight: 900;
    letter-spacing: 0.045em;
    transform: rotate(4deg);
}

.brand-mark.generic strong {
    max-width: 125px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.44);
    font-size: 9px;
    font-weight: 950;
    backdrop-filter: blur(7px);
}

.condition-layer {
    position: absolute;
    z-index: 6;
    inset: 0;
    pointer-events: none;
    border-radius: inherit;
}

.micro-scratch,
.edge-scuff {
    position: absolute;
    display: block;
    opacity: 0;
    transition: opacity 300ms ease;
}

.micro-scratch {
    width: 1px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.76);
    box-shadow:
        1px 0 0 rgba(73, 57, 80, 0.15),
        0 0 5px rgba(255, 255, 255, 0.18);
    transform-origin: center;
}

.scratch-one {
    top: 31%;
    right: 22%;
    height: 52px;
    transform: rotate(24deg);
}

.scratch-two {
    top: 52%;
    left: 27%;
    height: 35px;
    transform: rotate(-31deg);
}

.scratch-three {
    top: 68%;
    right: 34%;
    height: 24px;
    transform: rotate(61deg);
}

.scratch-four {
    top: 22%;
    left: 35%;
    height: 18px;
    transform: rotate(-54deg);
}

.edge-scuff {
    width: 24px;
    height: 7px;
    border-radius: 999px;
    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(52, 43, 56, 0.34),
            transparent
        );
    filter: blur(0.2px);
}

.scuff-one {
    right: -3px;
    bottom: 24%;
    transform: rotate(86deg);
}

.scuff-two {
    left: -5px;
    top: 34%;
    transform: rotate(92deg);
}

.screen-polish {
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background:
        linear-gradient(
            120deg,
            transparent 18%,
            rgba(255, 255, 255, 0.18) 32%,
            transparent 46%
        );
    opacity: 0;
    transform: translateX(-35%);
    transition: opacity 300ms ease;
}

.shine-star {
    position: absolute;
    z-index: 8;
    opacity: 0;
    color: rgba(255, 255, 255, 0.96);
    text-shadow:
        0 0 8px rgba(255, 255, 255, 0.95),
        0 0 16px var(--phone-soft);
    pointer-events: none;
}

.shine-star-main {
    top: 21%;
    right: 17%;
    font-size: 29px;
}

.shine-star-mini {
    top: 31%;
    right: 31%;
    font-size: 15px;
}

.phone-shape.condition-pristine {
    transform: rotate(-1deg);
    filter: saturate(1.08) brightness(1.055);
    box-shadow:
        0 30px 60px var(--phone-glow),
        0 0 34px var(--phone-soft),
        inset 0 1px 0 rgba(255, 255, 255, 0.48),
        inset 0 -2px 7px rgba(0, 0, 0, 0.16);
}

.phone-shape.condition-pristine .screen-polish {
    opacity: 1;
    animation: phone-polish 3.8s ease-in-out infinite;
}

.phone-shape.condition-pristine .shine-star-main {
    opacity: 1;
    animation: shine-pop 2.4s ease-in-out infinite;
}

.phone-shape.condition-pristine .shine-star-mini {
    opacity: 0.86;
    animation: shine-pop 2.4s 0.35s ease-in-out infinite;
}

.phone-shape.condition-excellent {
    filter: saturate(1.035) brightness(1.025);
}

.phone-shape.condition-excellent .scratch-one {
    opacity: 0.08;
}

.phone-shape.condition-excellent .screen-polish {
    opacity: 0.58;
}

.phone-shape.condition-excellent .shine-star-main {
    opacity: 0.38;
    font-size: 20px;
    animation: soft-shine 3.8s ease-in-out infinite;
}

.phone-shape.condition-clean .micro-scratch {
    opacity: 0.30;
}

.phone-shape.condition-clean .edge-scuff {
    opacity: 0.20;
}

.phone-shape.condition-clean .screen-polish {
    opacity: 0.10;
}

.phone-shape.condition-worn {
    filter: saturate(0.86) brightness(0.965);
    transform: rotate(-2deg);
}

.phone-shape.condition-worn .micro-scratch {
    opacity: 0.56;
}

.phone-shape.condition-worn .scratch-two,
.phone-shape.condition-worn .scratch-three {
    opacity: 0.72;
}

.phone-shape.condition-worn .edge-scuff {
    opacity: 0.55;
}

.phone-shape.condition-worn .phone-screen::after {
    content: '';
    position: absolute;
    z-index: 2;
    right: 7px;
    bottom: 9%;
    width: 34px;
    height: 34px;
    border-right: 1px solid rgba(65, 49, 70, 0.22);
    border-bottom: 1px solid rgba(65, 49, 70, 0.18);
    border-radius: 0 0 18px 0;
    transform: skew(-8deg);
}

@keyframes phone-polish {
    0%,
    48%,
    100% {
        transform: translateX(-42%);
        opacity: 0.18;
    }

    62% {
        transform: translateX(42%);
        opacity: 0.72;
    }
}

@keyframes shine-pop {
    0%,
    42%,
    100% {
        transform: scale(0.72) rotate(-8deg);
        opacity: 0.32;
    }

    55% {
        transform: scale(1.18) rotate(8deg);
        opacity: 1;
    }

    68% {
        transform: scale(0.94) rotate(0deg);
        opacity: 0.7;
    }
}

@keyframes soft-shine {
    0%,
    100% {
        transform: scale(0.82);
        opacity: 0.18;
    }

    50% {
        transform: scale(1.08);
        opacity: 0.48;
    }
}

.battery-badge {
    position: absolute;
    z-index: 10;
    top: 51px;
    right: 12px;
    display: inline-flex;
    min-height: 30px;
    align-items: center;
    gap: 6px;
    padding: 5px 8px;
    border: 1px solid rgba(255, 255, 255, 0.46);
    border-radius: 999px;
    background: rgba(25, 22, 28, 0.78);
    color: #fff;
    box-shadow:
        0 8px 18px rgba(31, 25, 35, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.14);
    backdrop-filter: blur(9px);
}

.battery-badge strong {
    font-size: 8px;
    font-weight: 950;
    white-space: nowrap;
}

.battery-icon {
    position: relative;
    display: block;
    width: 21px;
    height: 10px;
    padding: 1.5px;
    border: 1.3px solid currentColor;
    border-radius: 3px;
}

.battery-icon::after {
    content: '';
    position: absolute;
    top: 2px;
    right: -3px;
    width: 2px;
    height: 4px;
    border-radius: 0 1px 1px 0;
    background: currentColor;
}

.battery-icon i {
    display: block;
    width: 100%;
    height: 100%;
    margin-right: auto;
    border-radius: 1.5px;
    background: #79e1b5;
    transform-origin: left center;
}

.battery-medium .battery-icon i {
    width: 74%;
    background: #d8d96d;
}

.battery-low .battery-icon i {
    width: 48%;
    background: #e5ad62;
}

.battery-critical .battery-icon i {
    width: 24%;
    background: #e36d78;
}

.phone-visual-meta {
    display: flex;
    width: min(320px, 92%);
    flex-wrap: wrap;
    justify-content: center;
    gap: 7px;
    margin-top: 17px;
}

.visual-chip {
    display: inline-flex;
    min-height: 28px;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border: 1px solid rgba(88, 70, 95, 0.08);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.62);
    color: #6c5e71;
    font-size: 8px;
    font-weight: 850;
    backdrop-filter: blur(7px);
}

.color-chip i {
    width: 9px;
    height: 9px;
    border: 1px solid rgba(31, 26, 34, 0.15);
    border-radius: 999px;
    background: var(--phone-frame);
    box-shadow: 0 0 0 3px var(--phone-soft);
}

.condition-chip.condition-pristine {
    background: rgba(110, 224, 178, 0.18);
    color: #28745b;
}

.condition-chip.condition-excellent {
    background: rgba(139, 213, 194, 0.15);
    color: #3e7569;
}

.condition-chip.condition-clean {
    background: rgba(239, 201, 106, 0.16);
    color: #85681f;
}

.condition-chip.condition-worn {
    background: rgba(231, 127, 133, 0.14);
    color: #9a474d;
}

.request-summary {
    display: grid;
    gap: 12px;
}

.summary-price {
    padding: 17px;
    border-radius: 22px;
    background: #251c2a;
    color: #fff;
}

.summary-price small {
    display: block;
    color: #ad9db2;
    font-size: 10px;
}

.summary-price strong {
    display: inline-block;
    margin-top: 4px;
    font-size: 26px;
    font-weight: 350;
}

.summary-price span {
    margin-right: 5px;
    color: #d995e5;
    font-size: 10px;
    font-weight: 850;
}

.summary-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.summary-specs span {
    padding: 7px 9px;
    border-radius: 999px;
    background: rgba(153, 74, 169, 0.08);
    color: #725978;
    font-size: 9px;
    font-weight: 800;
}

.data-note {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 11px;
    margin-top: 18px;
    padding: 15px;
    border-radius: 20px;
    background: rgba(75, 211, 169, 0.11);
}

.data-icon {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: #77deb8;
    color: #194b3b;
    font-weight: 950;
}

.data-note strong {
    font-size: 11px;
}

.data-note p {
    margin: 3px 0 0;
    color: #6e766f;
    font-size: 9px;
    line-height: 1.8;
}

@media (max-width: 920px) {
    .hero,
    .workspace {
        grid-template-columns: 1fr;
    }

    .preview-card {
        position: static;
    }

    .phone-shape {
        max-width: 205px;
    }

    .phone-stage {
        padding-top: 20px;
    }
}

@media (max-width: 640px) {
    .mobile-live-preview {
        position: sticky;
        z-index: 18;
        top: 8px;
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 9px;
        margin: 14px 0;
        padding: 12px 13px 11px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .72);
        border-radius: 22px;
        background:
            radial-gradient(
                circle at 82% 14%,
                var(--phone-soft),
                transparent 32%
            ),
            linear-gradient(
                135deg,
                rgba(255,255,255,.91),
                rgba(247,241,249,.94)
            );
        box-shadow:
            0 12px 32px rgba(65, 47, 71, .14),
            inset 0 1px 0 rgba(255,255,255,.9);
        backdrop-filter: blur(18px);
    }

    .mobile-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .mobile-preview-head > div {
        display: flex;
        min-width: 0;
        flex-direction: column;
    }

    .mobile-preview-head small {
        color: #8b7c90;
        font-size: 8px;
        font-weight: 850;
    }

    .mobile-preview-head strong {
        max-width: 190px;
        margin-top: 1px;
        overflow: hidden;
        color: #312735;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 12px;
        font-weight: 950;
    }

    .mobile-live-badge {
        flex: 0 0 auto;
        padding: 5px 7px;
        border-radius: 999px;
        background: #dff7ee;
        color: #278166;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .08em;
    }

    .mobile-phone-scene {
        position: relative;
        display: grid;
        grid-template-columns: 82px minmax(0, 1fr);
        min-height: 118px;
        align-items: center;
        gap: 12px;
        isolation: isolate;
    }

    .mobile-phone-aura {
        position: absolute;
        z-index: -1;
        right: -10px;
        width: 118px;
        height: 118px;
        border-radius: 999px;
        background:
            radial-gradient(
                circle,
                var(--phone-soft),
                var(--phone-glow) 38%,
                transparent 72%
            );
        filter: blur(8px);
        transition: background 420ms ease;
    }

    .mobile-phone-shape {
        position: relative;
        justify-self: center;
        width: 64px;
        aspect-ratio: .55;
        padding: 3px;
        border: 1px solid
            color-mix(
                in srgb,
                var(--phone-edge) 72%,
                #fff 28%
            );
        border-radius: 14px;
        background:
            linear-gradient(
                145deg,
                color-mix(
                    in srgb,
                    var(--phone-frame) 82%,
                    #fff 18%
                ),
                var(--phone-frame) 55%,
                var(--phone-edge)
            );
        box-shadow:
            0 12px 24px var(--phone-glow),
            inset 0 1px 0 rgba(255,255,255,.34);
        transform: rotate(-1deg);
        transition:
            background 420ms ease,
            border-color 420ms ease,
            box-shadow 420ms ease,
            filter 320ms ease,
            transform 320ms ease;
    }

    .mobile-phone-screen {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background:
            radial-gradient(
                circle at 50% 22%,
                var(--phone-soft),
                transparent 31%
            ),
            linear-gradient(
                160deg,
                var(--phone-screen-a),
                var(--phone-screen-b)
            );
        text-align: center;
        transition: background 420ms ease;
    }

    .mobile-phone-island {
        position: absolute;
        z-index: 8;
        top: 5px;
        left: 50%;
        width: 21px;
        height: 5px;
        transform: translateX(-50%);
        border-radius: 999px;
        background: #171219;
    }

    .mobile-brand-mark {
        position: absolute;
        z-index: 5;
        top: 17px;
        left: 50%;
        max-width: 50px;
        transform: translateX(-50%);
        color:
            color-mix(
                in srgb,
                var(--phone-edge) 82%,
                #111 18%
            );
    }

    .mobile-brand-mark.apple span {
        font-family:
            -apple-system,
            BlinkMacSystemFont,
            'Segoe UI',
            sans-serif;
        font-size: 13px;
    }

    .mobile-brand-mark.samsung strong,
    .mobile-brand-mark:not(.apple):not(.samsung) strong {
        display: block;
        max-width: 48px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: Arial, sans-serif;
        font-size: 4px;
        font-weight: 950;
    }

    .mobile-phone-copy {
        position: relative;
        z-index: 4;
        display: flex;
        max-width: 90%;
        flex-direction: column;
        align-items: center;
        transform: translateY(6px);
    }

    .mobile-phone-copy small {
        color: #806f86;
        font-size: 4px;
    }

    .mobile-phone-copy strong {
        max-width: 52px;
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 6px;
        font-weight: 950;
    }

    .mobile-phone-copy span {
        color: #846e89;
        font-size: 4px;
    }

    .mobile-condition-layer {
        position: absolute;
        z-index: 6;
        inset: 0;
        pointer-events: none;
    }

    .mobile-scratch {
        position: absolute;
        width: 1px;
        border-radius: 999px;
        background: rgba(255,255,255,.84);
        opacity: 0;
        transition: opacity 260ms ease;
    }

    .mobile-scratch.scratch-a {
        top: 35%;
        right: 20%;
        height: 15px;
        transform: rotate(24deg);
    }

    .mobile-scratch.scratch-b {
        top: 58%;
        left: 25%;
        height: 11px;
        transform: rotate(-32deg);
    }

    .mobile-scratch.scratch-c {
        bottom: 15%;
        right: 35%;
        height: 9px;
        transform: rotate(42deg);
    }

    .mobile-polish {
        position: absolute;
        inset: 0;
        opacity: 0;
        background:
            linear-gradient(
                120deg,
                transparent 18%,
                rgba(255,255,255,.35) 34%,
                transparent 49%
            );
    }

    .mobile-shine {
        position: absolute;
        top: 21%;
        right: 15%;
        opacity: 0;
        color: #fff;
        font-size: 10px;
        text-shadow:
            0 0 7px rgba(255,255,255,.95),
            0 0 12px var(--phone-soft);
    }

    .mobile-phone-shape.condition-pristine {
        filter: saturate(1.08) brightness(1.055);
        box-shadow:
            0 13px 26px var(--phone-glow),
            0 0 16px var(--phone-soft);
    }

    .mobile-phone-shape.condition-pristine
    .mobile-polish {
        opacity: 1;
        animation: mobile-phone-polish 2.8s ease-in-out infinite;
    }

    .mobile-phone-shape.condition-pristine
    .mobile-shine {
        opacity: 1;
        animation: mobile-phone-shine 2.1s ease-in-out infinite;
    }

    .mobile-phone-shape.condition-excellent
    .scratch-a {
        opacity: .08;
    }

    .mobile-phone-shape.condition-excellent
    .mobile-polish {
        opacity: .42;
    }

    .mobile-phone-shape.condition-clean
    .mobile-scratch {
        opacity: .28;
    }

    .mobile-phone-shape.condition-worn {
        filter: saturate(.86) brightness(.96);
        transform: rotate(-2deg);
    }

    .mobile-phone-shape.condition-worn
    .mobile-scratch {
        opacity: .62;
    }

    .mobile-phone-shape.condition-worn
    .scratch-b {
        opacity: .82;
    }

    .mobile-battery {
        position: absolute;
        z-index: 10;
        top: 17px;
        right: 3px;
        display: inline-flex;
        min-height: 12px;
        align-items: center;
        gap: 2px;
        padding: 2px 3px;
        border-radius: 999px;
        background: rgba(25,22,28,.82);
        color: #fff;
        backdrop-filter: blur(5px);
    }

    .mobile-battery strong {
        font-size: 3.5px;
        font-weight: 950;
        white-space: nowrap;
    }

    .mobile-battery-icon {
        position: relative;
        display: block;
        width: 8px;
        height: 4px;
        padding: .5px;
        border: .7px solid currentColor;
        border-radius: 1px;
    }

    .mobile-battery-icon::after {
        content: '';
        position: absolute;
        top: 1px;
        right: -2px;
        width: 1px;
        height: 2px;
        background: currentColor;
    }

    .mobile-battery-icon i {
        display: block;
        width: 100%;
        height: 100%;
        background: #79e1b5;
    }

    .mobile-battery.battery-medium
    .mobile-battery-icon i {
        width: 74%;
        background: #d8d96d;
    }

    .mobile-battery.battery-low
    .mobile-battery-icon i {
        width: 48%;
        background: #e5ad62;
    }

    .mobile-battery.battery-critical
    .mobile-battery-icon i {
        width: 24%;
        background: #e36d78;
    }

    .mobile-preview-meta {
        display: flex;
        min-width: 0;
        flex-wrap: wrap;
        align-content: center;
        gap: 6px;
    }

    .mobile-preview-meta > span {
        display: inline-flex;
        min-height: 24px;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 999px;
        background: rgba(255,255,255,.72);
        color: #6b5c70;
        font-size: 7px;
        font-weight: 850;
    }

    .mobile-color-dot {
        width: 7px;
        height: 7px;
        border: 1px solid rgba(31,26,34,.15);
        border-radius: 999px;
        background: var(--phone-frame);
        box-shadow: 0 0 0 2px var(--phone-soft);
    }

    .mobile-preview-meta
    .condition-pristine {
        background: rgba(110,224,178,.18);
        color: #28745b;
    }

    .mobile-preview-meta
    .condition-excellent {
        background: rgba(139,213,194,.15);
        color: #3e7569;
    }

    .mobile-preview-meta
    .condition-clean {
        background: rgba(239,201,106,.16);
        color: #85681f;
    }

    .mobile-preview-meta
    .condition-worn {
        background: rgba(231,127,133,.14);
        color: #9a474d;
    }

    .mobile-preview-hint {
        display: block;
        color: #8a7a8f;
        font-size: 7px;
        line-height: 1.7;
        font-weight: 750;
    }

    /*
     * The full desktop preview would otherwise appear again
     * after the form on mobile. Keep the experience focused:
     * one live preview, exactly where the user is editing specs.
     */
    .workspace > .preview-card {
        display: none;
    }

    @keyframes mobile-phone-polish {
        0%,
        100% {
            transform: translateX(-42%);
            opacity: .18;
        }

        55% {
            transform: translateX(42%);
            opacity: .75;
        }
    }

    @keyframes mobile-phone-shine {
        0%,
        100% {
            transform: scale(.7);
            opacity: .3;
        }

        50% {
            transform: scale(1.15);
            opacity: 1;
        }
    }

    .wanted-page {
        padding: 0;
        background: #f2edf5;
    }

    .page-shell {
        min-height: 100dvh;
        border-radius: 0;
    }

    .topbar {
        padding: 18px 16px 8px;
    }

    .main-content {
        padding: 8px 14px 22px;
    }

    .back-button {
        width: 46px;
        height: 46px;
    }

    .topbar-title strong {
        font-size: 22px;
    }

    .hero-copy,
    .radar-card,
    .request-card,
    .preview-card {
        border-radius: 28px;
    }

    .hero-copy {
        padding: 23px 19px;
    }

    .hero-copy h1 {
        font-size: 43px;
    }

    .hero-copy p {
        font-size: 13px;
    }

    .radar-card {
        min-height: 300px;
    }

    .request-card,
    .preview-card {
        padding: 19px;
    }

    .device-grid,
    .specification-band,
    .contact-grid,
    .price-zone {
        grid-template-columns: 1fr;
    }

    .specification-band {
        padding: 14px;
    }

    .price-zone {
        padding: 18px;
    }

    .phone-shape {
        width: 190px;
    }
}
</style>
