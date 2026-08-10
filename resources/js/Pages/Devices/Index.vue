<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    devices: {
        type: Array,
        default: () => [],
    },
});

const money = (value) => {
    if (value === null || value === undefined) return '—';
    return Number(value).toLocaleString('fa-IR') + ' تومان';
};

const simType = (value) => {
    if (value === 'single') return 'تک‌سیم';
    if (value === 'dual') return 'دو‌سیم';
    return '—';
};

const conditionLabel = (value) => {
    const labels = {
        'A+': 'در حد نو',
        A: 'بسیار تمیز',
        B: 'تمیز',
        C: 'خط و خش‌دار',
    };

    return labels[value] ?? value ?? '—';
};
</script>

<template>
    <Head title="موجودی" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-violet-600">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">موجودی گوشی‌ها</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ devices.length.toLocaleString('fa-IR') }} دستگاه در موجودی
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            :href="route('devices.create')"
                            class="rounded-2xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-700"
                        >
                            + ثبت دستگاه
                        </Link>

                        <Link
                            :href="route('dashboard')"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            بازگشت
                        </Link>
                    </div>
                </div>

                <div
                    v-if="!devices.length"
                    class="rounded-[30px] bg-white p-12 text-center shadow-sm dark:bg-slate-900"
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
                    :href="route('devices.show', device.id)"
                    class="block cursor-pointer overflow-hidden rounded-[30px] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900"
                >
                        <div class="flex h-44 items-center justify-center bg-slate-100 dark:bg-slate-800">
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
                                        {{ device.color || '—' }}
                                    </p>
                                </div>

                                <span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                    موجود
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">سلامت باتری</p>
                                    <p class="mt-1 font-bold">
                                        {{ device.battery_health !== null ? `${device.battery_health}%` : '—' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">تمیزی</p>
                                    <p class="mt-1 font-bold">
                                        {{ conditionLabel(device.condition_grade) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">پارت نامبر</p>
                                    <p class="mt-1 font-bold">
                                        {{ device.part_number || '—' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">سیم‌کارت</p>
                                    <p class="mt-1 font-bold">
                                        {{ simType(device.sim_type) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500">قیمت خرید</span>
                                    <span class="font-black">{{ money(device.purchase_price) }}</span>
                                </div>

                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-sm text-slate-500">پیشنهاد فروش +۱۰٪</span>
                                    <span class="font-black text-violet-700">
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
