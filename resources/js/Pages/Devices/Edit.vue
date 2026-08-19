<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { colorLabel } from '@/Utils/vehicleLabels';

const props = defineProps({
    device: {
        type: Object,
        required: true,
    },
    catalog: {
        type: Object,
        required: true,
    },
    optionLabels: {
        type: Object,
        default: () => ({}),
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

const filteredModels = computed(() =>
    props.catalog.models.filter(
        (model) => String(model.brand_id) === String(selectedBrandId.value)
    )
);

const filteredColors = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelColors
        .filter(
            (item) => String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.color_option_id));

    return props.catalog.colors.filter((color) =>
        allowedIds.includes(String(color.id))
    );
});

const form = useForm({
    brand: props.device.brand ?? '',
    model: props.device.model ?? '',
    model_year: props.device.model_year ?? '',
    mileage: props.device.mileage ?? '',
    color: props.device.color ?? '',
    transmission: props.device.transmission ?? '',
    fuel_type: props.device.fuel_type ?? '',
    body_condition: props.device.body_condition ?? '',
    insurance_months: props.device.insurance_months ?? '',
    vin: props.device.vin ?? '',
});

watch(selectedBrandId, () => {
    selectedModelId.value = '';
    form.brand = '';
    form.model = '';
    form.color = '';
});

watch(selectedModelId, (value) => {
    form.color = '';

    const model = props.catalog.models.find(
        (item) => String(item.id) === String(value)
    );

    form.model = model?.name ?? '';
});

const selectBrand = () => {
    const brand = props.catalog.brands.find(
        (item) => String(item.id) === String(selectedBrandId.value)
    );

    form.brand = brand?.name ?? '';
};

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const submit = () => {
    form.patch(route('devices.update', props.device.id));
};
</script>

<template>
    <Head :title="`ویرایش ${device.brand} ${device.model} | Panahi Car`" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page">
            <div class="am-page-inner-narrow">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="am-kicker">Panahi Car</p>
                        <h1 class="am-title">ویرایش مشخصات خودرو</h1>
                        <p class="am-subtitle">
                            {{ device.brand }} {{ device.model }}
                        </p>
                    </div>

                    <Link
                        :href="route('devices.show', device.id)"
                        class="am-btn-secondary shrink-0"
                    >
                        بازگشت
                    </Link>
                </div>

                <div
                    class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300"
                >
                    اطلاعات خرید، فروشنده، قیمت خرید و سوابق مالی از این صفحه تغییر نمی‌کنند.
                </div>

                <form class="space-y-6" @submit.prevent="submit">
                    <section class="am-card sm:!p-7">
                        <div class="mb-6">
                            <h2 class="text-lg font-black">مشخصات خودرو</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                اطلاعات فنی قابل ویرایش
                            </p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <InputLabel value="برند *" class="mb-2 font-bold" />
                                <select
                                    v-model="selectedBrandId"
                                    class="am-input"
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
                                <InputError class="mt-1" :message="form.errors.brand" />
                            </div>

                            <div>
                                <InputLabel value="مدل *" class="mb-2 font-bold" />
                                <select
                                    v-model="selectedModelId"
                                    :disabled="!selectedBrandId"
                                    class="am-input disabled:cursor-not-allowed disabled:opacity-50"
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
                                <InputError class="mt-1" :message="form.errors.model" />
                            </div>

                            <div>
                                <InputLabel value="سال مدل *" class="mb-2 font-bold" />
                                <TextInput
                                    :model-value="String(form.model_year ?? '')"
                                    type="text"
                                    inputmode="numeric"
                                    class="am-input border-0 shadow-none focus:ring-2"
                                    @update:model-value="form.model_year = normalizeDigits($event).replace(/\D/g, '').slice(0, 4)"
                                />
                                <InputError class="mt-1" :message="form.errors.model_year" />
                            </div>

                            <div>
                                <InputLabel value="کارکرد (کیلومتر) *" class="mb-2 font-bold" />
                                <TextInput
                                    :model-value="String(form.mileage ?? '')"
                                    type="text"
                                    inputmode="numeric"
                                    class="am-input border-0 shadow-none focus:ring-2"
                                    @update:model-value="form.mileage = normalizeDigits($event).replace(/\D/g, '')"
                                />
                                <InputError class="mt-1" :message="form.errors.mileage" />
                            </div>

                            <div>
                                <InputLabel value="رنگ" class="mb-2 font-bold" />
                                <select
                                    v-model="form.color"
                                    :disabled="!selectedModelId"
                                    class="am-input disabled:cursor-not-allowed disabled:opacity-50"
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
                                <InputError class="mt-1" :message="form.errors.color" />
                            </div>

                            <div>
                                <InputLabel value="گیربکس *" class="mb-2 font-bold" />
                                <select v-model="form.transmission" class="am-input">
                                    <option value="">انتخاب کنید</option>
                                    <option
                                        v-for="(label, value) in optionLabels.transmissions"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.transmission" />
                            </div>

                            <div>
                                <InputLabel value="نوع سوخت *" class="mb-2 font-bold" />
                                <select v-model="form.fuel_type" class="am-input">
                                    <option value="">انتخاب کنید</option>
                                    <option
                                        v-for="(label, value) in optionLabels.fuelTypes"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.fuel_type" />
                            </div>

                            <div>
                                <InputLabel value="وضعیت بدنه *" class="mb-2 font-bold" />
                                <select v-model="form.body_condition" class="am-input">
                                    <option value="">انتخاب کنید</option>
                                    <option
                                        v-for="(label, value) in optionLabels.bodyConditions"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.body_condition" />
                            </div>

                            <div>
                                <InputLabel value="بیمه شخص ثالث (ماه)" class="mb-2 font-bold" />
                                <TextInput
                                    :model-value="String(form.insurance_months ?? '')"
                                    type="text"
                                    inputmode="numeric"
                                    class="am-input border-0 shadow-none focus:ring-2"
                                    @update:model-value="form.insurance_months = normalizeDigits($event).replace(/\D/g, '').slice(0, 2)"
                                />
                                <InputError class="mt-1" :message="form.errors.insurance_months" />
                            </div>

                            <div>
                                <InputLabel value="VIN" class="mb-2 font-bold" />
                                <TextInput
                                    v-model="form.vin"
                                    type="text"
                                    dir="ltr"
                                    class="am-input border-0 text-left shadow-none focus:ring-2"
                                />
                                <InputError class="mt-1" :message="form.errors.vin" />
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 pb-8 sm:flex-row">
                        <PrimaryButton
                            type="submit"
                            :disabled="form.processing"
                            class="am-btn-primary px-8"
                        >
                            {{ form.processing ? 'در حال ذخیره...' : 'ذخیره تغییرات' }}
                        </PrimaryButton>

                        <Link
                            :href="route('devices.show', device.id)"
                            class="am-btn-secondary px-8 text-center"
                        >
                            انصراف
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
