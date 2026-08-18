<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntityNoteHistory from '@/Components/EntityNoteHistory.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    bodyConditionLabel,
    colorLabel,
    formatInsurance,
    formatMileage,
    formatYear,
    fuelTypeLabel,
    transmissionLabel,
} from '@/Utils/vehicleLabels';
import { mediaUrl, vehiclePhoto } from '@/Utils/carPhotos';

const props = defineProps({
    device: {
        type: Object,
        required: true,
    },
    deviceNotes: {
        type: Array,
        default: () => [],
    },
    purchaseNotes: {
        type: Array,
        default: () => [],
    },
    optionLabels: {
        type: Object,
        default: () => ({}),
    },
});

const activeImage = ref(
    props.device.images?.length
        ? mediaUrl(props.device.images[0].image_path, props.device.id)
        : vehiclePhoto(props.device)
);

const money = (value) => {
    if (value === null || value === undefined) return '—';

    return `${Number(value).toLocaleString('fa-IR')} تومان`;
};

const persianDate = (value) => {
    if (!value) return '—';

    const date = new Date(`${value}T00:00:00`);

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

const specRows = [
    { label: 'برند', value: () => props.device.brand },
    { label: 'مدل', value: () => props.device.model },
    { label: 'سال مدل', value: () => formatYear(props.device.model_year) },
    { label: 'کارکرد', value: () => formatMileage(props.device.mileage) },
    { label: 'رنگ', value: () => colorLabel(props.device.color) },
    {
        label: 'گیربکس',
        value: () => transmissionLabel(props.device.transmission, props.optionLabels.transmissions),
    },
    {
        label: 'نوع سوخت',
        value: () => fuelTypeLabel(props.device.fuel_type, props.optionLabels.fuelTypes),
    },
    {
        label: 'وضعیت بدنه',
        value: () => bodyConditionLabel(props.device.body_condition, props.optionLabels.bodyConditions),
    },
    {
        label: 'بیمه شخص ثالث',
        value: () => formatInsurance(props.device.insurance_months),
    },
    { label: 'VIN', value: () => props.device.vin || '—', ltr: true },
];
</script>

<template>
    <Head :title="`${device.brand} ${device.model} | automaya`" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page">
            <div class="am-page-inner">
                <header class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <Link
                                :href="route('devices.index')"
                                class="text-xs font-bold text-slate-400 transition hover:text-blue-600"
                            >
                                موجودی خودرو
                            </Link>
                            <span class="text-slate-300 dark:text-slate-700">/</span>
                            <span
                                class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                            >
                                موجود در نمایشگاه
                            </span>
                        </div>

                        <h1 class="text-[30px] font-black tracking-tight sm:text-[36px]">
                            {{ device.brand }} {{ device.model }}
                        </h1>

                        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs font-bold text-slate-400">
                            <span>{{ formatYear(device.model_year) }}</span>
                            <span>•</span>
                            <span>{{ colorLabel(device.color) }}</span>
                            <span>•</span>
                            <span>{{ formatMileage(device.mileage) }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="route('devices.edit', device.id)"
                            class="am-btn-secondary"
                        >
                            ویرایش مشخصات
                        </Link>
                        <Link
                            :href="route('devices.index')"
                            class="am-btn-secondary"
                        >
                            بازگشت
                        </Link>
                    </div>
                </header>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.08fr)_minmax(360px,.92fr)]">
                    <section class="am-card lg:sticky lg:top-6 lg:self-start">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black">تصاویر خودرو</p>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{
                                        device.images?.length
                                            ? `${device.images.length.toLocaleString('fa-IR')} تصویر ثبت شده`
                                            : 'بدون تصویر'
                                    }}
                                </p>
                            </div>
                            <span
                                v-if="device.images?.length"
                                class="am-accent-soft rounded-full px-3 py-1 text-[10px] font-black"
                            >
                                گالری
                            </span>
                        </div>

                        <div
                            class="flex min-h-[320px] items-center justify-center overflow-hidden rounded-[26px] bg-slate-100 dark:bg-white/[0.035] sm:min-h-[420px]"
                        >
                            <img
                                v-if="activeImage"
                                :src="activeImage"
                                :alt="`${device.brand} ${device.model}`"
                                class="h-full max-h-[560px] w-full object-contain p-3"
                            />
                            <div
                                v-else
                                class="flex flex-col items-center gap-3 text-slate-300 dark:text-slate-600"
                            >
                                <span class="text-7xl">🚗</span>
                                <span class="text-xs font-bold">تصویری ثبت نشده</span>
                            </div>
                        </div>

                        <div
                            v-if="device.images?.length > 1"
                            class="mt-4 grid grid-cols-4 gap-2.5 sm:grid-cols-5"
                        >
                            <button
                                v-for="image in device.images"
                                :key="image.id"
                                type="button"
                                class="overflow-hidden rounded-[16px] border-2 bg-slate-50 p-0.5 transition dark:bg-white/[0.04]"
                                :class="
                                    activeImage === mediaUrl(image.image_path, props.device.id)
                                        ? 'border-neutral-900 shadow-[0_5px_15px_rgba(0,0,0,.12)]'
                                        : 'border-transparent opacity-70 hover:opacity-100'
                                "
                                @click="activeImage = mediaUrl(image.image_path, props.device.id)"
                            >
                                <img
                                    :src="mediaUrl(image.image_path, props.device.id)"
                                    :alt="`${device.brand} ${device.model}`"
                                    class="h-16 w-full rounded-[12px] object-cover sm:h-20"
                                />
                            </button>
                        </div>
                    </section>

                    <div class="space-y-5">
                        <section class="am-card sm:!p-7">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-black am-accent">خلاصه خودرو</p>
                                    <h2 class="mt-1 text-xl font-black">
                                        {{ device.brand }} {{ device.model }}
                                    </h2>
                                </div>
                                <span
                                    class="rounded-2xl bg-emerald-50 px-3 py-2 text-[10px] font-black text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                                >
                                    آماده فروش
                                </span>
                            </div>

                            <div class="mt-6 grid grid-cols-3 gap-2.5">
                                <div class="am-soft !p-3">
                                    <p class="text-[10px] text-slate-400">سال</p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ formatYear(device.model_year) }}
                                    </p>
                                </div>
                                <div class="am-soft !p-3">
                                    <p class="text-[10px] text-slate-400">کارکرد</p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ formatMileage(device.mileage) }}
                                    </p>
                                </div>
                                <div class="am-soft !p-3">
                                    <p class="text-[10px] text-slate-400">رنگ</p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ colorLabel(device.color) }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-5 overflow-hidden rounded-[24px] border border-slate-100 bg-white/70 dark:border-white/5 dark:bg-white/[0.025]"
                            >
                                <div class="grid divide-y divide-slate-100 dark:divide-white/5">
                                    <div class="flex items-center justify-between gap-5 px-4 py-4">
                                        <div>
                                            <p class="text-[10px] text-slate-400">قیمت خرید</p>
                                            <p class="mt-1 text-base font-black">
                                                {{ money(device.purchase_price) }}
                                            </p>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-[10px] text-slate-400">تاریخ خرید</p>
                                            <p class="mt-1 text-xs font-bold">
                                                {{ persianDate(device.purchase_date) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="device.suggested_sale_price"
                                        class="flex items-center justify-between gap-5 bg-blue-50/70 px-4 py-5 dark:bg-blue-500/[0.07]"
                                    >
                                        <div>
                                            <p class="text-[10px] font-black am-accent">
                                                قیمت پیشنهادی فروش +۱۰٪
                                            </p>
                                            <p class="mt-1 text-xl font-black am-accent">
                                                {{ money(device.suggested_sale_price) }}
                                            </p>
                                        </div>
                                        <span
                                            class="am-accent-soft rounded-full px-3 py-1.5 text-[10px] font-black"
                                        >
                                            پیشنهاد
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="am-soft">
                                    <p class="text-[10px] text-slate-400">فروشنده</p>
                                    <p class="mt-1 font-black">{{ device.seller_name || '—' }}</p>
                                    <a
                                        v-if="device.seller_mobile"
                                        :href="`tel:${device.seller_mobile}`"
                                        class="mt-1.5 inline-block text-xs font-bold am-accent"
                                        dir="ltr"
                                    >
                                        {{ device.seller_mobile }}
                                    </a>
                                </div>

                                <div class="am-soft">
                                    <p class="text-[10px] text-slate-400">نرخ دلار روز خرید</p>
                                    <p class="mt-1 font-black">
                                        {{ device.purchase_usd_rate ? money(device.purchase_usd_rate) : '—' }}
                                    </p>
                                    <p
                                        v-if="device.purchase_usd_rate_date"
                                        class="mt-1.5 text-[10px] text-slate-400"
                                    >
                                        {{ persianDate(device.purchase_usd_rate_date) }}
                                    </p>
                                </div>
                            </div>

                            <Link
                                :href="route('sales.create', device.id)"
                                class="am-btn-primary mt-5 flex w-full px-6 py-4 text-center text-base"
                            >
                                ثبت فروش این خودرو
                            </Link>
                        </section>

                        <section
                            v-if="device.vin"
                            class="am-card !p-4"
                        >
                            <p class="text-[10px] text-slate-400">VIN</p>
                            <p
                                class="mt-1 font-mono text-sm font-bold tracking-[0.08em]"
                                dir="ltr"
                            >
                                {{ device.vin }}
                            </p>
                        </section>
                    </div>
                </div>

                <section class="am-card mt-6 sm:!p-7">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[11px] font-black am-accent">مشخصات کامل</p>
                            <h2 class="mt-1 text-xl font-black">اطلاعات فنی خودرو</h2>
                        </div>
                        <p class="text-xs text-slate-400">
                            اطلاعات ثبت‌شده هنگام خرید
                        </p>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <div class="am-soft !overflow-hidden !p-0">
                            <div class="px-5 py-4">
                                <p class="text-xs font-black text-slate-500 dark:text-slate-300">
                                    مشخصات اصلی
                                </p>
                            </div>
                            <div
                                class="divide-y divide-slate-200/60 border-t border-slate-200/60 dark:divide-white/5 dark:border-white/5"
                            >
                                <div
                                    v-for="row in specRows"
                                    :key="row.label"
                                    class="flex items-center justify-between gap-4 px-5 py-3.5"
                                >
                                    <span class="text-xs text-slate-400">{{ row.label }}</span>
                                    <span
                                        class="text-sm font-black"
                                        :dir="row.ltr ? 'ltr' : undefined"
                                    >
                                        {{ row.value() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="device.description"
                            class="am-soft"
                        >
                            <p class="text-xs font-black text-slate-500 dark:text-slate-300">
                                توضیحات
                            </p>
                            <p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-600 dark:text-slate-300">
                                {{ device.description }}
                            </p>
                        </div>
                    </div>
                </section>

                <div
                    class="mt-6 grid gap-6"
                    :class="device.purchase_id ? 'lg:grid-cols-2' : 'grid-cols-1'"
                >
                    <EntityNoteHistory
                        entity-type="device"
                        :entity-id="device.id"
                        :notes="deviceNotes"
                        title="یادداشت‌های خودرو"
                        empty-text="هنوز یادداشتی برای این خودرو ثبت نشده است."
                    />

                    <EntityNoteHistory
                        v-if="device.purchase_id"
                        entity-type="purchase"
                        :entity-id="device.purchase_id"
                        :notes="purchaseNotes"
                        title="یادداشت‌های خرید"
                        empty-text="هنوز یادداشتی برای این خرید ثبت نشده است."
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
