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
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mh-page-inner-narrow">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="mh-kicker">MAYA HAMRAH</p>

                        <h1 class="mh-title">
                            {{ device.brand }} {{ device.model }}
                        </h1>

                        <p class="mh-subtitle">
                            جزئیات کامل دستگاه موجود در مغازه
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="route('devices.edit', device.id)"
                            class="mh-primary"
                        >
                            ویرایش مشخصات
                        </Link>

                        <Link
                            :href="route('devices.index')"
                            class="mh-secondary"
                        >
                            بازگشت به موجودی
                        </Link>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1fr_1.25fr]">
                    <!-- Images -->
                    <section
                        class="mh-surface"
                    >
                        <div
                            class="flex min-h-[360px] items-center justify-center overflow-hidden rounded-[24px] bg-[#f3f4f6] dark:bg-white/[0.04]"
                        >
                            <img
                                v-if="activeImage"
                                :src="activeImage"
                                :alt="`${device.brand} ${device.model}`"
                                class="h-full max-h-[500px] w-full object-contain"
                            />

                            <span v-else class="text-7xl">📱</span>
                        </div>

                        <div
                            v-if="device.images?.length > 1"
                            class="mt-4 grid grid-cols-4 gap-3"
                        >
                            <button
                                v-for="image in device.images"
                                :key="image.id"
                                type="button"
                                class="overflow-hidden rounded-2xl border-2 bg-[#f4f6f8] transition dark:bg-white/[0.04]"
                                :class="
                                    activeImage === `/storage/${image.image_path}`
                                        ? 'border-[#ff6d76]'
                                        : 'border-transparent'
                                "
                                @click="activeImage = `/storage/${image.image_path}`"
                            >
                                <img
                                    :src="`/storage/${image.image_path}`"
                                    class="h-20 w-full object-cover"
                                />
                            </button>
                        </div>
                    </section>

                    <!-- Details -->
                    <div class="space-y-6">
                        <section
                            class="mh-surface sm:!p-7"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-black">
                                        مشخصات دستگاه
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        اطلاعات فنی و وضعیت گوشی
                                    </p>
                                </div>

                                <span
                                    class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                                >
                                    موجود
                                </span>
                            </div>

                            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">برند</p>
                                    <p class="mt-1 font-bold">{{ device.brand }}</p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">مدل</p>
                                    <p class="mt-1 font-bold">{{ device.model }}</p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">حافظه</p>
                                    <p class="mt-1 font-bold">{{ device.storage || '—' }}</p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">رنگ</p>
                                    <p class="mt-1 font-bold">{{ colorLabel(device.color) }}</p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">
                                        {{ device.brand === 'Samsung' ? 'کشور سازنده' : 'پارت نامبر' }}
                                    </p>
                                    <p class="mt-1 font-bold">{{
                                            device.brand === 'Samsung'
                                                ? manufacturingCountryLabel(device.manufacturing_country)
                                                : (device.part_number || '—')
                                        }}</p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">نوع سیم‌کارت</p>
                                    <p class="mt-1 font-bold">{{ simTypeLabel(device.sim_type) }}</p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">
                                        {{ device.brand === 'Samsung' ? 'وضعیت باتری' : 'سلامت باتری' }}
                                    </p>
                                    <p class="mt-1 font-bold">
                                        {{
                                            device.brand === 'Samsung'
                                                ? batteryConditionLabel(device.battery_condition)
                                                : (device.battery_health !== null
                                                    ? `${device.battery_health}%`
                                                    : '—')
                                        }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">تمیزی</p>
                                    <p class="mt-1 font-bold">
                                        {{ conditionLabel(device.condition_grade) }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">رجیستری</p>
                                    <p class="mt-1 font-bold">
                                        {{ registrationStatusLabel(device.registration_status) }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface sm:col-span-2 lg:col-span-3">
                                    <p class="text-xs text-slate-400">IMEI</p>
                                    <p class="mt-1 font-bold" dir="ltr">
                                        {{ device.imei || '—' }}
                                    </p>
                                </div>
                            </div>

                        </section>

                        <EntityNoteHistory
                            entity-type="device"
                            :entity-id="device.id"
                            :notes="deviceNotes"
                            title="یادداشت‌های دستگاه"
                            empty-text="هنوز یادداشتی برای این دستگاه ثبت نشده است."
                        />

                        <section
                            class="mh-surface sm:!p-7"
                        >
                            <h2 class="text-lg font-black">اطلاعات خرید</h2>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">فروشنده</p>
                                    <p class="mt-1 font-black">
                                        {{ device.seller_name || '—' }}
                                    </p>

                                    <a
                                        v-if="device.seller_mobile"
                                        :href="`tel:${device.seller_mobile}`"
                                        class="mt-2 inline-block text-sm font-bold mh-accent-text"
                                    >
                                        {{ device.seller_mobile }}
                                    </a>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">تاریخ خرید</p>
                                    <p class="mt-1 font-bold">
                                        {{ persianDate(device.purchase_date) }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">قیمت خرید</p>
                                    <p class="mt-1 font-black">
                                        {{ money(device.purchase_price) }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface">
                                    <p class="text-xs text-slate-400">نرخ دلار روز خرید</p>
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
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        {{ persianDate(device.purchase_usd_rate_date) }}
                                    </p>
                                </div>

                                <div class="mh-accent-soft rounded-2xl p-4">
                                    <p class="text-xs text-slate-500">قیمت پیشنهادی فروش +۱۰٪</p>
                                    <p class="mt-1 text-lg font-black">
                                        {{ money(device.suggested_sale_price) }}
                                    </p>
                                </div>
                            </div>

                        </section>

                        <EntityNoteHistory
                            v-if="device.purchase_id"
                            entity-type="purchase"
                            :entity-id="device.purchase_id"
                            :notes="purchaseNotes"
                            title="یادداشت‌های خرید"
                            empty-text="هنوز یادداشتی برای این خرید ثبت نشده است."
                        />

                        <Link
                            :href="route('sales.create', device.id)"
                            class="mh-primary block w-full px-6 py-4 text-center text-base"
                        >
                            ثبت فروش این گوشی
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
