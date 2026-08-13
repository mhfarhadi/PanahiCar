<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntityNoteHistory from '@/Components/EntityNoteHistory.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    batteryConditionLabel,
    colorLabel,
    manufacturingCountryLabel,
    conditionLabel,
    registrationStatusLabel,
    simTypeLabel,
} from '@/Utils/deviceLabels';

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
});

const activeImage = ref(
    props.device.images?.length
        ? `/storage/${props.device.images[0].image_path}`
        : null
);

const money = (value) => {
    if (value === null || value === undefined) return '—';
    return Number(value).toLocaleString('fa-IR') + ' تومان';
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
</script>

<template>
    <Head :title="`${device.brand} ${device.model} | مایاهمراه`" />

    <AuthenticatedLayout>
        <div dir="rtl" class="mh-page">
            <div class="mx-auto max-w-[1380px]">

                <!-- Page header -->
                <header
                    class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <Link
                                :href="route('devices.index')"
                                class="text-xs font-bold text-slate-400 transition hover:text-[#ff6570]"
                            >
                                موجودی
                            </Link>

                            <span class="text-slate-300 dark:text-slate-700">/</span>

                            <span
                                class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                            >
                                موجود در فروشگاه
                            </span>
                        </div>

                        <h1
                            class="text-[30px] font-black tracking-tight sm:text-[36px]"
                        >
                            {{ device.brand }} {{ device.model }}
                        </h1>

                        <div
                            class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs font-bold text-slate-400"
                        >
                            <span v-if="device.storage">
                                {{ device.storage }}
                            </span>

                            <span v-if="device.storage">•</span>

                            <span>
                                {{ colorLabel(device.color) }}
                            </span>

                            <span>•</span>

                            <span>
                                {{ conditionLabel(device.condition_grade) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="route('devices.edit', device.id)"
                            class="mh-secondary"
                        >
                            ویرایش مشخصات
                        </Link>

                        <Link
                            :href="route('devices.index')"
                            class="mh-secondary"
                        >
                            بازگشت
                        </Link>
                    </div>
                </header>

                <!-- Hero -->
                <div
                    class="grid gap-6 lg:grid-cols-[minmax(0,1.08fr)_minmax(360px,.92fr)]"
                >
                    <!-- Gallery -->
                    <section class="mh-surface lg:sticky lg:top-6 lg:self-start">
                        <div
                            class="mb-4 flex items-center justify-between gap-3"
                        >
                            <div>
                                <p class="text-sm font-black">
                                    تصاویر دستگاه
                                </p>

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
                                class="mh-accent-soft rounded-full px-3 py-1 text-[10px] font-black"
                            >
                                گالری
                            </span>
                        </div>

                        <div
                            class="flex min-h-[390px] items-center justify-center overflow-hidden rounded-[26px] bg-[#f3f4f6] dark:bg-white/[0.035] sm:min-h-[470px]"
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
                                <span class="text-7xl">📱</span>
                                <span class="text-xs font-bold">
                                    تصویری ثبت نشده
                                </span>
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
                                class="overflow-hidden rounded-[16px] border-2 bg-[#f4f6f8] p-0.5 transition dark:bg-white/[0.04]"
                                :class="
                                    activeImage === `/storage/${image.image_path}`
                                        ? 'border-[#ff6d76] shadow-[0_5px_15px_rgba(255,109,118,.14)]'
                                        : 'border-transparent opacity-70 hover:opacity-100'
                                "
                                @click="activeImage = `/storage/${image.image_path}`"
                            >
                                <img
                                    :src="`/storage/${image.image_path}`"
                                    :alt="`${device.brand} ${device.model}`"
                                    class="h-16 w-full rounded-[12px] object-cover sm:h-20"
                                />
                            </button>
                        </div>
                    </section>

                    <!-- Commercial summary -->
                    <div class="space-y-5">
                        <section class="mh-surface sm:!p-7">
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div>
                                    <p class="text-[11px] font-black text-[#ff6570]">
                                        خلاصه دستگاه
                                    </p>

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

                            <!-- Important specs -->
                            <div class="mt-6 grid grid-cols-3 gap-2.5">
                                <div class="mh-soft-surface !p-3">
                                    <p class="text-[10px] text-slate-400">
                                        حافظه
                                    </p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ device.storage || '—' }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface !p-3">
                                    <p class="text-[10px] text-slate-400">
                                        رنگ
                                    </p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ colorLabel(device.color) }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface !p-3">
                                    <p class="text-[10px] text-slate-400">
                                        تمیزی
                                    </p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ conditionLabel(device.condition_grade) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div
                                class="mt-5 overflow-hidden rounded-[24px] border border-white bg-white/70 dark:border-white/5 dark:bg-white/[0.025]"
                            >
                                <div
                                    class="grid divide-y divide-slate-100 dark:divide-white/5"
                                >
                                    <div
                                        class="flex items-center justify-between gap-5 px-4 py-4"
                                    >
                                        <div>
                                            <p class="text-[10px] text-slate-400">
                                                قیمت خرید
                                            </p>
                                            <p class="mt-1 text-base font-black">
                                                {{ money(device.purchase_price) }}
                                            </p>
                                        </div>

                                        <div class="text-left">
                                            <p class="text-[10px] text-slate-400">
                                                تاریخ خرید
                                            </p>
                                            <p class="mt-1 text-xs font-bold">
                                                {{ persianDate(device.purchase_date) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center justify-between gap-5 bg-[#fff0f1]/70 px-4 py-5 dark:bg-[#ff6d76]/[0.07]"
                                    >
                                        <div>
                                            <p
                                                class="text-[10px] font-black text-[#d85e68] dark:text-[#ff9299]"
                                            >
                                                قیمت پیشنهادی فروش +۱۰٪
                                            </p>

                                            <p
                                                class="mt-1 text-xl font-black text-[#d85e68] dark:text-[#ff9299]"
                                            >
                                                {{ money(device.suggested_sale_price) }}
                                            </p>
                                        </div>

                                        <span
                                            class="rounded-full bg-white/70 px-3 py-1.5 text-[10px] font-black text-[#d85e68] dark:bg-white/5 dark:text-[#ff9299]"
                                        >
                                            پیشنهاد
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Seller / USD -->
                            <div
                                class="mt-5 grid gap-3 sm:grid-cols-2"
                            >
                                <div class="mh-soft-surface">
                                    <p class="text-[10px] text-slate-400">
                                        فروشنده
                                    </p>

                                    <p class="mt-1 font-black">
                                        {{ device.seller_name || '—' }}
                                    </p>

                                    <a
                                        v-if="device.seller_mobile"
                                        :href="`tel:${device.seller_mobile}`"
                                        class="mt-1.5 inline-block text-xs font-bold mh-accent-text"
                                    >
                                        {{ device.seller_mobile }}
                                    </a>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-[10px] text-slate-400">
                                        نرخ دلار روز خرید
                                    </p>

                                    <p class="mt-1 font-black">
                                        <template v-if="device.purchase_usd_rate">
                                            {{ money(device.purchase_usd_rate) }}
                                        </template>
                                        <template v-else>
                                            —
                                        </template>
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
                                class="mh-primary mt-5 flex w-full px-6 py-4 text-center text-base"
                            >
                                ثبت فروش این گوشی
                            </Link>
                        </section>

                        <!-- IMEI quick panel -->
                        <section
                            class="rounded-[24px] border border-white bg-white/65 p-4 shadow-[0_12px_35px_rgba(40,50,70,.04)] dark:border-white/5 dark:bg-white/[0.025]"
                        >
                            <div
                                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="text-[10px] text-slate-400">
                                        IMEI
                                    </p>

                                    <p
                                        class="mt-1 font-mono text-sm font-bold tracking-[0.08em]"
                                        dir="ltr"
                                    >
                                        {{ device.imei || '—' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="rounded-full bg-[#f4f6f8] px-3 py-1.5 text-[10px] font-black text-slate-500 dark:bg-white/5 dark:text-slate-300"
                                    >
                                        {{ registrationStatusLabel(device.registration_status) }}
                                    </span>

                                    <span
                                        class="rounded-full bg-[#f4f6f8] px-3 py-1.5 text-[10px] font-black text-slate-500 dark:bg-white/5 dark:text-slate-300"
                                    >
                                        {{ simTypeLabel(device.sim_type) }}
                                    </span>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Technical details -->
                <section class="mh-surface mt-6 sm:!p-7">
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p class="text-[11px] font-black text-[#ff6570]">
                                مشخصات کامل
                            </p>

                            <h2 class="mt-1 text-xl font-black">
                                اطلاعات فنی دستگاه
                            </h2>
                        </div>

                        <p class="text-xs text-slate-400">
                            اطلاعات ثبت‌شده هنگام خرید دستگاه
                        </p>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <!-- Identity -->
                        <div class="mh-soft-surface !p-0 overflow-hidden">
                            <div class="px-5 py-4">
                                <p class="text-xs font-black text-slate-500 dark:text-slate-300">
                                    مشخصات اصلی
                                </p>
                            </div>

                            <div
                                class="divide-y divide-slate-200/60 border-t border-slate-200/60 dark:divide-white/5 dark:border-white/5"
                            >
                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">برند</span>
                                    <span class="text-sm font-black">{{ device.brand }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">مدل</span>
                                    <span class="text-sm font-black">{{ device.model }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">حافظه</span>
                                    <span class="text-sm font-black">{{ device.storage || '—' }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">رنگ</span>
                                    <span class="text-sm font-black">{{ colorLabel(device.color) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">
                                        {{
                                            device.brand === 'Samsung'
                                                ? 'کشور سازنده'
                                                : 'پارت نامبر'
                                        }}
                                    </span>

                                    <span class="text-sm font-black">
                                        {{
                                            device.brand === 'Samsung'
                                                ? manufacturingCountryLabel(device.manufacturing_country)
                                                : (device.part_number || '—')
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">نوع سیم‌کارت</span>
                                    <span class="text-sm font-black">
                                        {{ simTypeLabel(device.sim_type) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Technical condition -->
                        <div class="mh-soft-surface !p-0 overflow-hidden">
                            <div class="px-5 py-4">
                                <p class="text-xs font-black text-slate-500 dark:text-slate-300">
                                    وضعیت فنی
                                </p>
                            </div>

                            <div
                                class="divide-y divide-slate-200/60 border-t border-slate-200/60 dark:divide-white/5 dark:border-white/5"
                            >
                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">
                                        {{
                                            device.brand === 'Samsung'
                                                ? 'وضعیت باتری'
                                                : 'سلامت باتری'
                                        }}
                                    </span>

                                    <span class="text-sm font-black">
                                        {{
                                            device.brand === 'Samsung'
                                                ? batteryConditionLabel(device.battery_condition)
                                                : (
                                                    device.battery_health !== null
                                                        ? `${device.battery_health}%`
                                                        : '—'
                                                )
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">تمیزی دستگاه</span>
                                    <span class="text-sm font-black">
                                        {{ conditionLabel(device.condition_grade) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">وضعیت رجیستری</span>
                                    <span class="text-sm font-black">
                                        {{ registrationStatusLabel(device.registration_status) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">IMEI</span>
                                    <span
                                        class="text-sm font-black"
                                        dir="ltr"
                                    >
                                        {{ device.imei || '—' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">تاریخ خرید</span>
                                    <span class="text-sm font-black">
                                        {{ persianDate(device.purchase_date) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                    <span class="text-xs text-slate-400">قیمت خرید</span>
                                    <span class="text-sm font-black">
                                        {{ money(device.purchase_price) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Notes -->
                <div
                    class="mt-6 grid gap-6"
                    :class="device.purchase_id ? 'lg:grid-cols-2' : 'grid-cols-1'"
                >
                    <EntityNoteHistory
                        entity-type="device"
                        :entity-id="device.id"
                        :notes="deviceNotes"
                        title="یادداشت‌های دستگاه"
                        empty-text="هنوز یادداشتی برای این دستگاه ثبت نشده است."
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
