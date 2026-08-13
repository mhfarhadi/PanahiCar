<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    batteryConditionLabel,
    conditionLabel,
    registrationStatusLabel,
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

const selectedBrand = computed(() =>
    props.catalog.brands.find((item) => item.name === brand.value)
);

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
        none: 'بدون ارزیابی',
    })[value] || value;

const confidenceClass = (value) =>
    ({
        high: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300',
        medium: 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300',
        low: 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300',
    })[value] ||
    'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';

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
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">
                            مایاهمراه
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            برآورد قیمت گوشی
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            بر اساس فروش‌های واقعی ثبت‌شده و تعدیل قیمت با نرخ دلار
                        </p>
                    </div>

                    <Link
                        :href="route('dashboard')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        داشبورد
                    </Link>
                </div>

                <form
                    class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-black">
                                برند
                            </label>

                            <select
                                v-model="brand"
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
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
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:border-violet-500 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950"
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
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:border-violet-500 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950"
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
                            class="rounded-2xl bg-violet-600 px-6 py-3 text-sm font-black text-white transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-40"
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
                            class="rounded-[30px] bg-violet-600 p-6 text-white shadow-sm lg:col-span-2"
                        >
                            <p class="text-sm font-bold text-violet-100">
                                برآورد مرکزی
                            </p>

                            <p class="mt-3 text-3xl font-black sm:text-4xl">
                                {{ formatMoney(estimate.estimate) }}
                                <span class="text-base text-violet-100">
                                    تومان
                                </span>
                            </p>

                            <p class="mt-4 text-sm text-violet-100">
                                بازه فروش‌های مشابه پس از تعدیل دلاری:
                                {{ formatMoney(estimate.range_min) }}
                                تا
                                {{ formatMoney(estimate.range_max) }}
                                تومان
                            </p>
                        </div>

                        <div
                            class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
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
                        class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
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
                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
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
                        این نسخه هنوز اثر وضعیت ظاهری، سلامت باتری و رجیستری را در قیمت تعدیل نمی‌کند؛
                        بنابراین نتیجه فعلاً یک برآورد پایه و شفاف از سوابق فروش است.
                    </div>
                </div>

                <div
                    v-else-if="estimate && !estimate.available"
                    class="mt-5 rounded-[30px] border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-lg font-black">
                        داده کافی برای برآورد نداریم
                    </p>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        هنوز فروش ثبت‌شده‌ای برای همین مدل و حافظه وجود ندارد.
                    </p>
                </div>

                <div
                    v-if="brand && model && storage"
                    class="mt-5 rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
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
