<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    batteryConditionLabel,
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
const conditionGrade = ref(props.filters.condition_grade || '');
const batteryHealth = ref(props.filters.battery_health ?? '');
const batteryCondition = ref(props.filters.battery_condition || '');
const registrationStatus = ref(props.filters.registration_status || '');

const selectedBrand = computed(() =>
    props.catalog.brands.find((item) => item.name === brand.value)
);

const isSamsung = computed(() => brand.value === 'Samsung');

const availableModels = computed(() => {
    if (!selectedBrand.value) {
        return [];
    }

    return props.catalog.models.filter(
        (item) => Number(item.brand_id) === Number(selectedBrand.value.id)
    );
});

const selectedModel = computed(() =>
    availableModels.value.find((item) => item.name === model.value)
);

const availableStorages = computed(() => {
    if (!selectedModel.value) {
        return [];
    }

    const storageIds = props.catalog.modelStorages
        .filter(
            (item) =>
                Number(item.device_model_id) === Number(selectedModel.value.id)
        )
        .map((item) => Number(item.storage_option_id));

    return props.catalog.storages.filter((item) =>
        storageIds.includes(Number(item.id))
    );
});

watch(brand, (value, oldValue) => {
    if (oldValue !== undefined && value !== props.filters.brand) {
        model.value = '';
        storage.value = '';
        batteryHealth.value = '';
        batteryCondition.value = '';
    }
});

watch(model, (value, oldValue) => {
    if (oldValue !== undefined && value !== props.filters.model) {
        storage.value = '';
    }
});

const submit = () => {
    if (!brand.value || !model.value || !storage.value) {
        return;
    }

    router.get(
        route('price-estimates.index'),
        {
            brand: brand.value,
            model: model.value,
            storage: storage.value,
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

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const handleBatteryHealth = (event) => {
    const normalized = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 3);

    if (normalized === '') {
        batteryHealth.value = '';
        event.target.value = '';
        return;
    }

    const clamped = Math.min(100, Math.max(0, Number(normalized)));

    batteryHealth.value = String(clamped);
    event.target.value = batteryHealth.value;
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
        none: 'بدون ارزیابی',
    })[value] || value;

const confidenceClass = (value) =>
    ({
        high: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300',
        medium: 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300',
        low: 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300',
    })[value] ||
    'bg-[#f1f3f5] text-slate-600 dark:bg-white/[0.06] dark:text-slate-300';

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
    const query = [brand.value, model.value, storage.value]
        .filter(Boolean)
        .join(' ');

    if (!query) {
        return '#';
    }

    return `https://divar.ir/s/iran?q=${encodeURIComponent(query)}`;
});
</script>

<template>
    <Head title="برآورد قیمت | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-[#ff6570]">
                            مایاهمراه
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            برآورد قیمت گوشی
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            ارزش فروش واقعی + سیگنال مستقل کف خرید همکاران
                        </p>
                    </div>

                    <Link
                        :href="route('dashboard')"
                        class="rounded-2xl border border-slate-200/60 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-white/5 dark:bg-white/[0.035] dark:text-slate-300"
                    >
                        داشبورد
                    </Link>
                </div>

                <form
                    class="rounded-[30px] border border-slate-200/60 bg-white p-5 shadow-sm dark:border-white/5 dark:bg-white/[0.035] sm:p-6"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-black">
                                برند
                            </label>

                            <select
                                v-model="brand"
                                class="price-estimate-select w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] px-4 py-3 text-sm font-bold focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/10 dark:bg-white/[0.025]"
                            >
                                <option value="">انتخاب برند</option>
                                <option
                                    v-for="item in catalog.brands"
                                    :key="item.id"
                                    :value="item.name"
                                >
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black">
                                مدل
                            </label>

                            <select
                                v-model="model"
                                :disabled="!brand"
                                class="price-estimate-select w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] px-4 py-3 text-sm font-bold focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.025]"
                            >
                                <option value="">انتخاب مدل</option>
                                <option
                                    v-for="item in availableModels"
                                    :key="item.id"
                                    :value="item.name"
                                >
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black">
                                حافظه
                            </label>

                            <select
                                v-model="storage"
                                :disabled="!model"
                                class="price-estimate-select w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] px-4 py-3 text-sm font-bold focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.025]"
                            >
                                <option value="">انتخاب حافظه</option>
                                <option
                                    v-for="item in availableStorages"
                                    :key="item.id"
                                    :value="item.name"
                                >
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-black">
                                وضعیت ظاهری
                            </label>

                            <select
                                v-model="conditionGrade"
                                class="price-estimate-select w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] px-4 py-3 text-sm font-bold focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/10 dark:bg-white/[0.025]"
                            >
                                <option value="">مهم نیست</option>
                                <option value="A+">در حد نو</option>
                                <option value="A">بسیار تمیز</option>
                                <option value="B">تمیز</option>
                                <option value="C">خط و خش‌دار</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black">
                                وضعیت رجیستری
                            </label>

                            <select
                                v-model="registrationStatus"
                                class="price-estimate-select w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] px-4 py-3 text-sm font-bold focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/10 dark:bg-white/[0.025]"
                            >
                                <option value="">مهم نیست</option>
                                <option value="registered">رجیستر شده</option>
                                <option value="unregistered">رجیستر نشده</option>
                            </select>
                        </div>

                        <div v-if="isSamsung">
                            <label class="mb-2 block text-sm font-black">
                                وضعیت باتری
                            </label>

                            <select
                                v-model="batteryCondition"
                                class="price-estimate-select w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] px-4 py-3 text-sm font-bold focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/10 dark:bg-white/[0.025]"
                            >
                                <option value="">مهم نیست</option>
                                <option
                                    v-for="item in samsungBatteryConditionOptions"
                                    :key="item.value"
                                    :value="item.value"
                                >
                                    {{ item.label }}
                                </option>
                            </select>
                        </div>

                        <div v-else>
                            <label class="mb-2 block text-sm font-black">
                                سلامت باتری
                            </label>

                            <div class="relative">
                                <input
                                    :value="batteryHealth"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9۰-۹٠-٩]*"
                                    maxlength="3"
                                    placeholder="مثلاً ۹۲"
                                    @input="handleBatteryHealth"
                                    class="w-full rounded-2xl border-slate-200/60 bg-[#f7f8fa] px-4 py-3 pl-12 text-sm font-bold focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/10 dark:bg-white/[0.025]"
                                />
                                <span
                                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-slate-400"
                                >
                                    ٪
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            نرخ دلار فعلی:
                            <strong class="text-slate-800 dark:text-slate-200">
                                {{ currentUsdRate ? formatMoney(currentUsdRate) : 'نامشخص' }}
                                <span v-if="currentUsdRate">تومان</span>
                            </strong>
                            <span v-if="currentUsdRate" class="mr-1">
                                · منبع: نوسان
                            </span>
                        </div>

                        <button
                            type="submit"
                            :disabled="!brand || !model || !storage"
                            class="rounded-2xl bg-[#ff6d76] px-6 py-3 text-sm font-black text-white transition hover:bg-[#f45f6a] disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            محاسبه برآورد
                        </button>
                    </div>
                </form>

                <div
                    v-if="estimate && estimate.available"
                    class="mt-5 space-y-5"
                >
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div
                            class="rounded-[30px] bg-[#ff6d76] p-6 text-white shadow-sm lg:col-span-2"
                        >
                            <p class="text-sm font-bold text-white/75">
                                برآورد مرکزی
                            </p>

                            <p class="mt-3 text-3xl font-black sm:text-4xl">
                                {{ formatMoney(estimate.estimate) }}
                                <span class="text-base text-white/75">
                                    تومان
                                </span>
                            </p>

                            <p class="mt-4 text-sm text-white/75">
                                بازه فروش‌های مشابه پس از تعدیل دلاری:
                                {{ formatMoney(estimate.range_min) }}
                                تا
                                {{ formatMoney(estimate.range_max) }}
                                تومان
                            </p>
                        </div>

                        <div
                            class="rounded-[30px] border border-slate-200/60 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-white/[0.035]"
                        >
                            <p class="text-xs font-bold text-slate-400">
                                کیفیت داده
                            </p>

                            <div class="mt-3 flex items-center justify-between gap-3">
                                <span class="text-lg font-black">
                                    {{ Number(estimate.comparable_count).toLocaleString('fa-IR') }}
                                    فروش مشابه
                                </span>

                                <span
                                    class="rounded-xl px-3 py-1.5 text-xs font-black"
                                    :class="confidenceClass(estimate.confidence)"
                                >
                                    اعتماد {{ confidenceLabel(estimate.confidence) }}
                                </span>
                            </div>

                            <p class="mt-4 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                در نسخه فعلی فقط فروش‌های دقیقاً هم‌مدل و هم‌حافظه مقایسه می‌شوند.
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-[30px] border border-slate-200/60 bg-white p-5 shadow-sm dark:border-white/5 dark:bg-white/[0.035] sm:p-6"
                    >
                        <div class="mb-5">
                            <h2 class="text-lg font-black">
                                فروش‌های مبنای محاسبه
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                قیمت هر فروش با نرخ دلار همان روز به نرخ امروز تبدیل شده است.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="sale in estimate.comparables"
                                :key="sale.sale_id"
                                class="rounded-2xl bg-[#f7f8fa] p-4 dark:bg-white/[0.025]"
                            >
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div>
                                        <p class="font-black">
                                            {{ sale.brand }} {{ sale.model }}
                                            · {{ sale.storage }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            فروش {{ formatDate(sale.sale_date) }}
                                            · وضعیت {{ conditionLabel(sale.condition_grade) || '—' }}
                                            · باتری {{ batteryLabel(sale) }}
                                            · {{ registrationStatusLabel(sale.registration_status) || '—' }}
                                        </p>
                                    </div>

                                    <div class="sm:text-left">
                                        <p class="text-sm font-black">
                                            {{ formatMoney(sale.normalized_price) }}
                                            تومان
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            اصل فروش:
                                            {{ formatMoney(sale.sale_price) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300"
                    >
                        وضعیت ظاهری، سلامت باتری و رجیستری در شباهت نمونه‌ها لحاظ می‌شوند؛
                        بنابراین نتیجه فعلاً یک برآورد پایه و شفاف از سوابق فروش است.
                    </div>
                </div>

                <div
                    v-if="
                        estimate &&
                        estimate.demand_signal &&
                        estimate.demand_signal.available
                    "
                    class="relative mt-5 overflow-hidden rounded-[30px] border border-violet-200/70 bg-gradient-to-l from-violet-50 via-white to-white p-5 shadow-sm dark:border-violet-900/40 dark:from-violet-950/20 dark:via-white/[0.035] dark:to-white/[0.035] sm:p-6"
                >
                    <div class="absolute -right-12 -top-16 h-44 w-44 rounded-full border border-violet-300/30"></div>
                    <div class="absolute -right-4 -top-8 h-28 w-28 rounded-full border border-violet-300/30"></div>

                    <div class="relative grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-xs font-black text-violet-600 dark:text-violet-300">
                                    {{
                                        estimate.demand_signal.provisional
                                            ? 'سیگنال اولیه بازار'
                                            : 'کف خرید همکاران'
                                    }}
                                </p>

                                <span
                                    v-if="estimate.demand_signal.provisional"
                                    class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black text-amber-700 dark:bg-amber-950/40 dark:text-amber-300"
                                >
                                    موقت
                                </span>
                            </div>

                            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">
                                {{ formatMoney(estimate.suggested_purchase_price) }}
                                <span class="text-sm text-slate-500 dark:text-slate-400">
                                    تومان
                                </span>
                            </p>

                            <p class="mt-3 max-w-2xl text-xs leading-6 text-slate-500 dark:text-slate-400">
                                {{
                                    estimate.demand_signal.provisional
                                        ? 'فعلاً از داده‌های اولیه‌ی بازار برای خالی نبودن سیستم استفاده شده؛ این داده‌ها ضدتقلب یا اعتماد واقعی را بالا نمی‌برند و با ورود تقاضای واقعی کنار می‌روند.'
                                        : 'این عدد از سقف خرید همکاران واقعی، با وزن‌دهی به تازگی و نزدیکی مشخصات محاسبه شده و مستقل از ارزش فروش است.'
                                }}
                            </p>
                        </div>

                        <div class="grid min-w-[220px] grid-cols-3 gap-2 lg:grid-cols-1">
                            <div class="rounded-2xl bg-white/70 p-3 dark:bg-white/[0.045]">
                                <p class="text-[10px] font-bold text-slate-400">
                                    تقاضای واقعی
                                </p>
                                <p class="mt-1 text-sm font-black">
                                    {{
                                        Number(
                                            estimate.demand_signal
                                                .organic_demand_count || 0
                                        ).toLocaleString('fa-IR')
                                    }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white/70 p-3 dark:bg-white/[0.045]">
                                <p class="text-[10px] font-bold text-slate-400">
                                    اعتماد
                                </p>
                                <p class="mt-1 text-sm font-black">
                                    {{
                                        confidenceLabel(
                                            estimate.demand_signal.confidence
                                        )
                                    }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white/70 p-3 dark:bg-white/[0.045]">
                                <p class="text-[10px] font-bold text-slate-400">
                                    بازه داده
                                </p>
                                <p class="mt-1 text-sm font-black">
                                    {{
                                        Number(
                                            estimate.demand_signal.lookback_days
                                        ).toLocaleString('fa-IR')
                                    }}
                                    روز
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="estimate && !estimate.available"
                    class="mt-5 rounded-[30px] border border-slate-200/60 bg-white p-8 text-center shadow-sm dark:border-white/5 dark:bg-white/[0.035]"
                >
                    <p class="text-lg font-black">
                        {{
                            estimate.demand_signal &&
                            estimate.demand_signal.available
                                ? 'هنوز فروش واقعی مشابه نداریم'
                                : 'داده کافی برای برآورد نداریم'
                        }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        {{
                            estimate.demand_signal &&
                            estimate.demand_signal.available
                                ? 'سیگنال هدف خرید همکاران بالا در دسترس است؛ با اولین فروش‌های واقعی، ارزش بازار فروش هم جداگانه نمایش داده می‌شود.'
                                : 'هنوز فروش یا تقاضای قابل استفاده‌ای برای همین مدل و حافظه وجود ندارد.'
                        }}
                    </p>
                </div>

                <div
                    v-if="brand && model && storage"
                    class="mt-5 rounded-[30px] border border-slate-200/60 bg-white p-5 shadow-sm dark:border-white/5 dark:bg-white/[0.035] sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-xs font-black text-slate-400">
                                بررسی بازار
                            </p>

                            <h2 class="mt-1 text-lg font-black">
                                آگهی‌های مشابه در دیوار
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                جستجو برای {{ brand }} {{ model }} {{ storage }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                قیمت آگهی‌های دیوار در محاسبه برآورد مایاهمراه دخالت ندارد.
                            </p>
                        </div>

                        <a
                            :href="divarSearchUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="shrink-0 rounded-2xl bg-red-600 px-5 py-3 text-center text-sm font-black text-white transition hover:bg-red-700"
                        >
                            دیدن آگهی‌های مشابه در دیوار
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.price-estimate-select {
    appearance: none;
    padding-left: 2.75rem;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%2364758B' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 0.9rem center;
    background-size: 1rem;
}
</style>
