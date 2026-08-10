<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    inventoryCount: {
        type: Number,
        default: 0,
    },
});


const today = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
}).format(new Date());

const actions = [
    {
        title: 'موجودی',
        description: 'مشاهده گوشی‌های موجود و مشخصات کامل',
        icon: '📱',
        href: route('devices.index'),
    },
    {
        title: 'ثبت دستگاه',
        description: 'ثبت گوشی جدید در موجودی',
        icon: '＋',
        href: route('devices.create'),
    },
    {
        title: 'برآورد قیمت',
        description: 'بررسی حدود قیمت گوشی در بازار',
        icon: '⌕',
    },
    {
        title: 'فروش گوشی',
        description: 'ثبت فروش نقدی یا اقساطی',
        icon: '✓',
    },
    {
        title: 'گوشی‌های فروخته‌شده',
        description: 'مشاهده سوابق فروش و مشتریان',
        icon: '◫',
    },
    {
        title: 'گوشی‌های اعلامی',
        description: 'لیست گوشی‌های معرفی‌شده توسط همکاران',
        icon: '☎',
        href: route('announced-devices.index'),
    },
];

const stats = [
    { label: 'موجودی فعلی', value: `${props.inventoryCount.toLocaleString('fa-IR')} دستگاه` },
    { label: 'فروش این ماه', value: '۰ دستگاه' },
    { label: 'مطالبات اقساطی', value: '۰ تومان' },
];
</script>

<template>
    <Head title="داشبورد مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">

                <!-- Top -->
                <div
                    class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-600 text-xl font-black text-white shadow-lg shadow-violet-200 dark:shadow-none"
                            >
                                م
                            </div>

                            <div>
                                <h1 class="text-2xl font-black tracking-tight">
                                    مایاهمراه
                                </h1>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    مدیریت خرید و فروش گوشی‌های کارکرده
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="text-right lg:text-left">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            امروز
                        </p>
                        <p class="mt-1 font-bold">
                            {{ today }}
                        </p>
                    </div>
                </div>

                <!-- Currency -->
                <div class="mb-6 grid gap-4 sm:grid-cols-2">
                    <div
                        class="rounded-3xl border border-white bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    قیمت دلار
                                </p>
                                <p class="mt-2 text-2xl font-black">
                                    —
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-xl font-black text-emerald-600 dark:bg-emerald-950"
                            >
                                $
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-slate-400">
                            اتصال به منبع قیمت در مرحله بعد
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    قیمت درهم
                                </p>
                                <p class="mt-2 text-2xl font-black">
                                    —
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-lg font-black text-sky-600 dark:bg-sky-950"
                            >
                                AED
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-slate-400">
                            اتصال به منبع قیمت در مرحله بعد
                        </p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mb-6 grid gap-4 sm:grid-cols-3">
                    <div
                        v-for="item in stats"
                        :key="item.label"
                        class="rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-900"
                    >
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ item.label }}
                        </p>

                        <p class="mt-2 text-xl font-black">
                            {{ item.value }}
                        </p>
                    </div>
                </div>

                <!-- Main actions -->
                <div
                    class="rounded-[32px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black">
                                دسترسی سریع
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                بخش موردنظر را انتخاب کنید
                            </p>
                        </div>

                        <div
                            class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700 dark:bg-violet-950 dark:text-violet-300"
                        >
                            نسخه اولیه
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <component
                            v-for="action in actions"
                            :key="action.title"
                            :is="action.href ? Link : 'button'"
                            :href="action.href"
                            type="button"
                            class="group rounded-3xl border border-slate-100 bg-slate-50 p-5 text-right transition duration-200 hover:-translate-y-1 hover:border-violet-200 hover:bg-violet-50 hover:shadow-lg dark:border-slate-800 dark:bg-slate-950 dark:hover:border-violet-800 dark:hover:bg-violet-950/40"
                        >
                            <div
                                class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl font-black shadow-sm transition group-hover:bg-violet-600 group-hover:text-white dark:bg-slate-900"
                            >
                                {{ action.icon }}
                            </div>

                            <h3 class="text-base font-black">
                                {{ action.title }}
                            </h3>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ action.description }}
                            </p>
                        </component>
                    </div>
                </div>

                <!-- Bottom -->
                <div
                    class="mt-6 rounded-3xl border border-violet-100 bg-gradient-to-l from-violet-50 to-white p-5 dark:border-violet-950 dark:from-violet-950/40 dark:to-slate-900"
                >
                    <p class="font-black">
                        مایاهمراه آماده شروع است.
                    </p>
                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        در قدم بعد اطلاعات واقعی موجودی، خرید، فروش و اقساط به این داشبورد متصل می‌شود.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
