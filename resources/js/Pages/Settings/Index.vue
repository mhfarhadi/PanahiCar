<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

</script>

<template>
    <Head title="تنظیمات | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-5xl">
                <div class="mb-6">
                    <p class="text-sm font-bold text-violet-600">
                        مایاهمراه
                    </p>

                    <h1 class="mt-1 text-2xl font-black">
                        تنظیمات
                    </h1>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        مدیریت اشخاص و تنظیمات برنامه
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <Link
                        :href="route('contacts.index')"
                        class="group rounded-3xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-xl dark:bg-violet-950/40"
                        >
                            👤
                        </div>

                        <h2 class="font-black">
                            اشخاص
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            مدیریت همکاران، مشتریان و اشخاص عادی
                        </p>
                    </Link>

                    <div
                        class="rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-900"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl dark:bg-slate-800"
                        >
                            ●
                        </div>

                        <h2 class="font-black">
                            رنگ برنامه
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            انتخاب رنگ اصلی رابط کاربری مایاهمراه
                        </p>

                        <p class="mt-4 text-xs font-bold text-violet-600">
                            در مرحله بعد
                        </p>
                    </div>

                    <div
                        class="rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-900"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black dark:bg-slate-800"
                        >
                            Aa
                        </div>

                        <h2 class="font-black">
                            فونت
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            انتخاب فونت و اندازه نوشته‌های برنامه
                        </p>

                        <p class="mt-4 text-xs font-bold text-violet-600">
                            در مرحله بعد
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
