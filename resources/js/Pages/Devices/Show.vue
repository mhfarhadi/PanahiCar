<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    device: {
        type: Object,
        required: true,
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
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-violet-600">مایاهمراه</p>

                        <h1 class="mt-1 text-2xl font-black">
                            {{ device.brand }} {{ device.model }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            جزئیات کامل دستگاه موجود در مغازه
                        </p>
                    </div>

                    <Link
                        :href="route('devices.index')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت به موجودی
                    </Link>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1fr_1.25fr]">
                    <!-- Images -->
                    <section
                        class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900"
                    >
                        <div
                            class="flex min-h-[360px] items-center justify-center overflow-hidden rounded-[24px] bg-slate-100 dark:bg-slate-800"
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
                                class="overflow-hidden rounded-2xl border-2 bg-slate-50 transition"
                                :class="
                                    activeImage === `/storage/${image.image_path}`
                                        ? 'border-violet-500'
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
                            class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
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
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">برند</p>
                                    <p class="mt-1 font-bold">{{ device.brand }}</p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">مدل</p>
                                    <p class="mt-1 font-bold">{{ device.model }}</p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">حافظه</p>
                                    <p class="mt-1 font-bold">{{ device.storage || '—' }}</p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">رنگ</p>
                                    <p class="mt-1 font-bold">{{ device.color || '—' }}</p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">پارت نامبر</p>
                                    <p class="mt-1 font-bold">{{ device.part_number || '—' }}</p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">نوع سیم‌کارت</p>
                                    <p class="mt-1 font-bold">{{ simType(device.sim_type) }}</p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">سلامت باتری</p>
                                    <p class="mt-1 font-bold">
                                        {{ device.battery_health !== null ? `${device.battery_health}%` : '—' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">تمیزی</p>
                                    <p class="mt-1 font-bold">
                                        {{ conditionLabel(device.condition_grade) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">رجیستری</p>
                                    <p class="mt-1 font-bold">
                                        {{ device.registration_status || '—' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2 lg:col-span-3 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">IMEI</p>
                                    <p class="mt-1 font-bold" dir="ltr">
                                        {{ device.imei || '—' }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="device.description"
                                class="mt-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                            >
                                <p class="text-xs text-slate-400">توضیحات دستگاه</p>
                                <p class="mt-2 text-sm leading-7">
                                    {{ device.description }}
                                </p>
                            </div>
                        </section>

                        <section
                            class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                        >
                            <h2 class="text-lg font-black">اطلاعات خرید</h2>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">فروشنده</p>
                                    <p class="mt-1 font-black">
                                        {{ device.seller_name || '—' }}
                                    </p>

                                    <a
                                        v-if="device.seller_mobile"
                                        :href="`tel:${device.seller_mobile}`"
                                        class="mt-2 inline-block text-sm font-bold text-violet-600"
                                    >
                                        {{ device.seller_mobile }}
                                    </a>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">تاریخ خرید</p>
                                    <p class="mt-1 font-bold">
                                        {{ persianDate(device.purchase_date) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <p class="text-xs text-slate-400">قیمت خرید</p>
                                    <p class="mt-1 font-black">
                                        {{ money(device.purchase_price) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-violet-50 p-4 dark:bg-violet-950/30">
                                    <p class="text-xs text-slate-500">قیمت پیشنهادی فروش +۱۰٪</p>
                                    <p class="mt-1 text-lg font-black text-violet-700 dark:text-violet-300">
                                        {{ money(device.suggested_sale_price) }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="device.purchase_notes"
                                class="mt-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                            >
                                <p class="text-xs text-slate-400">توضیحات خرید</p>
                                <p class="mt-2 text-sm leading-7">
                                    {{ device.purchase_notes }}
                                </p>
                            </div>
                        </section>

                        <Link
                            :href="route('sales.create', device.id)"
                            class="block w-full rounded-2xl bg-violet-600 px-6 py-4 text-center text-base font-black text-white shadow-lg shadow-violet-200 transition hover:bg-violet-700 dark:shadow-none"
                        >
                            ثبت فروش این گوشی
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
