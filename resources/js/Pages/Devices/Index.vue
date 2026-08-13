<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    batteryConditionLabel,
    colorLabel,
    manufacturingCountryLabel,
    conditionLabel,
    simTypeLabel,
} from '@/Utils/deviceLabels';

const props = defineProps({
    devices: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const sellMode = props.filters.mode === 'sell';

let searchTimer = null;

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('devices.index'),
            { search: search.value, mode: sellMode ? 'sell' : undefined },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300);
});

const money = (value) => {
    if (value === null || value === undefined) return '—';
    return Number(value).toLocaleString('fa-IR') + ' تومان';
};

</script>

<template>
    <Head title="موجودی" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mh-page-inner">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="mh-kicker">MAYA HAMRAH</p>
                        <h1 class="mh-title">
                            {{ sellMode ? 'انتخاب گوشی برای فروش' : 'موجودی گوشی‌ها' }}
                        </h1>
                        <p class="mh-subtitle">
                            {{
                                sellMode
                                    ? 'برای ثبت فروش، گوشی موردنظر را انتخاب کنید'
                                    : `${devices.length.toLocaleString('fa-IR')} دستگاه در موجودی`
                            }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            :href="route('devices.create')"
                            class="mh-primary"
                        >
                            + ثبت دستگاه
                        </Link>

                        <Link
                            :href="route('dashboard')"
                            class="mh-secondary"
                        >
                            بازگشت
                        </Link>
                    </div>
                </div>

                <div class="mb-5">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="جستجو با برند، مدل، حافظه، رنگ، IMEI یا فروشنده..."
                        autocomplete="off"
                        class="mh-input"
                    />
                </div>

                <div
                    v-if="!devices.length"
                    class="mh-surface !p-12 text-center"
                >
                    <div class="text-5xl">📱</div>
                    <h2 class="mt-4 text-lg font-black">موجودی خالی است</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        هنوز دستگاهی در موجودی ثبت نشده است.
                    </p>
                </div>

                <div v-else class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <Link
                    v-for="device in devices"
                    :key="device.id"
                    :href="sellMode
                        ? route('sales.create', device.id)
                        : route('devices.show', device.id)"
                    class="mh-surface block cursor-pointer !overflow-hidden !p-0 transition hover:-translate-y-1"
                >
                        <div class="flex h-44 items-center justify-center bg-[#f3f4f6] dark:bg-white/[0.04]">
                            <img
                                v-if="device.cover_image"
                                :src="`/storage/${device.cover_image}`"
                                :alt="`${device.brand} ${device.model}`"
                                class="h-full w-full object-cover"
                            />
                            <span v-else class="text-5xl">📱</span>
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-black">
                                        {{ device.brand }} {{ device.model }}
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ device.storage || '—' }} ·
                                        {{ colorLabel(device.color) }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-xl px-3 py-1 text-xs font-bold"
                                    :class="
                                        sellMode
                                            ? 'mh-accent-soft'
                                            : 'bg-emerald-50 text-emerald-700'
                                    "
                                >
                                    {{ sellMode ? 'فروش این گوشی' : 'موجود' }}
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                <div class="mh-soft-surface !p-3">
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

                                <div class="mh-soft-surface !p-3">
                                    <p class="text-xs text-slate-400">تمیزی</p>
                                    <p class="mt-1 font-bold">
                                        {{ conditionLabel(device.condition_grade) }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface !p-3">
                                    <p class="text-xs text-slate-400">
                                        {{ device.brand === 'Samsung' ? 'کشور سازنده' : 'پارت نامبر' }}
                                    </p>
                                    <p class="mt-1 font-bold">
                                        {{
                                            device.brand === 'Samsung'
                                                ? manufacturingCountryLabel(device.manufacturing_country)
                                                : (device.part_number || '—')
                                        }}
                                    </p>
                                </div>

                                <div class="mh-soft-surface !p-3">
                                    <p class="text-xs text-slate-400">سیم‌کارت</p>
                                    <p class="mt-1 font-bold">
                                        {{ simTypeLabel(device.sim_type) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 border-t border-slate-200/60 pt-4 dark:border-white/5">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500">قیمت خرید</span>
                                    <span class="font-black">{{ money(device.purchase_price) }}</span>
                                </div>

                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-sm text-slate-500">پیشنهاد فروش +۱۰٪</span>
                                    <span class="font-black mh-accent-text">
                                        {{ money(device.suggested_sale_price) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-slate-400">
                                فروشنده: {{ device.seller_name || '—' }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
