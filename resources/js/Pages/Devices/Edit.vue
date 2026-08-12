<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    colorLabel,
    registrationStatusLabel,
    samsungBatteryConditionOptions,
    manufacturingCountryOptions,
} from '@/Utils/deviceLabels';

const props = defineProps({
    device: {
        type: Object,
        required: true,
    },
    catalog: {
        type: Object,
        required: true,
    },
});

const initialBrand = props.catalog.brands.find(
    (brand) => brand.name === props.device.brand
);

const selectedBrandId = ref(initialBrand?.id ?? '');

const initialModel = props.catalog.models.find(
    (model) =>
        model.name === props.device.model &&
        String(model.brand_id) === String(selectedBrandId.value)
);

const selectedModelId = ref(initialModel?.id ?? '');

const selectedBrand = computed(() =>
    props.catalog.brands.find(
        (item) => String(item.id) === String(selectedBrandId.value)
    )
);

const isSamsung = computed(
    () => selectedBrand.value?.name === 'Samsung'
);

const filteredModels = computed(() =>
    props.catalog.models.filter(
        (model) => String(model.brand_id) === String(selectedBrandId.value)
    )
);

const filteredStorages = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelStorages
        .filter(
            (item) =>
                String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.storage_option_id));

    return props.catalog.storages.filter((storage) =>
        allowedIds.includes(String(storage.id))
    );
});

const filteredColors = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelColors
        .filter(
            (item) =>
                String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.color_option_id));

    return props.catalog.colors.filter((color) =>
        allowedIds.includes(String(color.id))
    );
});

const filteredPartNumbers = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelPartNumbers
        .filter(
            (item) =>
                String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.part_number_option_id));

    return props.catalog.partNumbers.filter((part) =>
        allowedIds.includes(String(part.id))
    );
});

const form = useForm({
    brand: props.device.brand ?? '',
    model: props.device.model ?? '',
    storage: props.device.storage ?? '',
    color: props.device.color ?? '',
    part_number: props.device.part_number ?? '',
    manufacturing_country: props.device.manufacturing_country ?? '',
    sim_type: props.device.sim_type ?? '',
    battery_health: props.device.battery_health ?? '',
    battery_condition: props.device.battery_condition ?? '',
    condition_grade: props.device.condition_grade ?? '',
    imei: props.device.imei ?? '',
    registration_status: props.device.registration_status ?? '',
});

watch(selectedBrandId, () => {
    selectedModelId.value = '';
    form.brand = '';
    form.model = '';
    form.storage = '';
    form.color = '';
    form.part_number = '';
    form.manufacturing_country = '';
    form.battery_health = '';
    form.battery_condition = '';
});

watch(selectedModelId, (value) => {
    form.storage = '';
    form.color = '';
    form.part_number = '';

    const model = props.catalog.models.find(
        (item) => String(item.id) === String(value)
    );

    form.model = model?.name ?? '';
});

const normalizeDigits = (value) => {
    return String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));
};

const selectBrand = () => {
    const brand = props.catalog.brands.find(
        (item) => String(item.id) === String(selectedBrandId.value)
    );

    form.brand = brand?.name ?? '';
};

const submit = () => {
    form.patch(route('devices.update', props.device.id));
};
</script>

<template>
    <Head :title="`ویرایش ${device.brand} ${device.model} | مایاهمراه`" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-5xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-violet-600">
                            مایاهمراه
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            ویرایش مشخصات دستگاه
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ device.brand }} {{ device.model }}
                        </p>
                    </div>

                    <Link
                        :href="route('devices.show', device.id)"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت به دستگاه
                    </Link>
                </div>

                <div
                    class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300"
                >
                    اطلاعات خرید، فروشنده، قیمت خرید و سوابق مالی از این صفحه تغییر نمی‌کنند.
                </div>

                <form
                    class="space-y-6"
                    @submit.prevent="submit"
                >
                    <section
                        class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                    >
                        <div class="mb-6">
                            <h2 class="text-lg font-black">
                                مشخصات دستگاه
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                اطلاعات فنی قابل ویرایش گوشی
                            </p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    برند *
                                </label>

                                <select
                                    v-model="selectedBrandId"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                    @change="selectBrand"
                                >
                                    <option value="">انتخاب برند</option>

                                    <option
                                        v-for="brand in catalog.brands"
                                        :key="brand.id"
                                        :value="brand.id"
                                    >
                                        {{ brand.name }}
                                    </option>
                                </select>

                                <p
                                    v-if="form.errors.brand"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.brand }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    مدل *
                                </label>

                                <select
                                    v-model="selectedModelId"
                                    :disabled="!selectedBrandId"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب مدل</option>

                                    <option
                                        v-for="model in filteredModels"
                                        :key="model.id"
                                        :value="model.id"
                                    >
                                        {{ model.name }}
                                    </option>
                                </select>

                                <p
                                    v-if="form.errors.model"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.model }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    حافظه
                                </label>

                                <select
                                    v-model="form.storage"
                                    :disabled="!selectedModelId"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب حافظه</option>

                                    <option
                                        v-for="storage in filteredStorages"
                                        :key="storage.id"
                                        :value="storage.name"
                                    >
                                        {{ storage.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    رنگ
                                </label>

                                <select
                                    v-model="form.color"
                                    :disabled="!selectedModelId"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب رنگ</option>

                                    <option
                                        v-for="color in filteredColors"
                                        :key="color.id"
                                        :value="color.name"
                                    >
                                        {{ colorLabel(color.name) }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="isSamsung">
                                <label class="mb-2 block text-sm font-bold">
                                    کشور سازنده
                                </label>

                                <select
                                    v-model="form.manufacturing_country"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب کشور سازنده</option>

                                    <option
                                        v-for="country in manufacturingCountryOptions"
                                        :key="country.value"
                                        :value="country.value"
                                    >
                                        {{ country.label }}
                                    </option>
                                </select>
                            </div>

                            <div v-else>
                                <label class="mb-2 block text-sm font-bold">
                                    پارت نامبر
                                </label>

                                <select
                                    v-model="form.part_number"
                                    :disabled="!selectedModelId || !filteredPartNumbers.length"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">
                                        {{ filteredPartNumbers.length ? 'انتخاب پارت نامبر' : 'پارت نامبر ندارد' }}
                                    </option>

                                    <option
                                        v-for="part in filteredPartNumbers"
                                        :key="part.id"
                                        :value="part.name"
                                    >
                                        {{ part.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    نوع سیم‌کارت
                                </label>

                                <select
                                    v-model="form.sim_type"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="single">تک‌سیم</option>
                                    <option value="dual">دو‌سیم</option>
                                </select>
                            </div>

                            <div v-if="isSamsung">
                                <label class="mb-2 block text-sm font-bold">
                                    وضعیت باتری
                                </label>

                                <select
                                    v-model="form.battery_condition"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب وضعیت باتری</option>

                                    <option
                                        v-for="option in samsungBatteryConditionOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div v-else>
                                <label class="mb-2 block text-sm font-bold">
                                    سلامت باتری
                                </label>

                                <div class="relative">
                                    <input
                                        :value="form.battery_health"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="مثلاً ۸۹ یا 89"
                                        class="w-full rounded-2xl border-slate-200 bg-slate-50 pl-12 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                        @input="form.battery_health = normalizeDigits($event.target.value).replace(/\D/g, '').slice(0, 3)"
                                    />

                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"
                                    >
                                        %
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    تمیزی دستگاه
                                </label>

                                <select
                                    v-model="form.condition_grade"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="A+">A+ | در حد نو</option>
                                    <option value="A">A | بسیار تمیز</option>
                                    <option value="B">B | تمیز</option>
                                    <option value="C">C | خط و خش‌دار</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    IMEI
                                </label>

                                <input
                                    :value="form.imei"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="15"
                                    placeholder="IMEI پانزده رقمی"
                                    dir="ltr"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 text-left focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                    @input="form.imei = normalizeDigits($event.target.value).replace(/\D/g, '').slice(0, 15)"
                                />

                                <p
                                    v-if="form.errors.imei"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.imei }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    وضعیت رجیستری
                                </label>

                                <select
                                    v-model="form.registration_status"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="registered">
                                        {{ registrationStatusLabel('registered') }}
                                    </option>
                                    <option value="unregistered">
                                        {{ registrationStatusLabel('unregistered') }}
                                    </option>
                                    <option value="unknown">نامشخص</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 pb-8 sm:flex-row">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-violet-600 px-8 py-3 font-black text-white shadow-lg shadow-violet-200 transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
                        >
                            {{ form.processing ? 'در حال ذخیره...' : 'ذخیره تغییرات' }}
                        </button>

                        <Link
                            :href="route('devices.show', device.id)"
                            class="rounded-2xl bg-white px-8 py-3 text-center font-bold text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-300"
                        >
                            انصراف
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
