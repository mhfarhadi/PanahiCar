<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

</script>

<template>
    <Head title="تنظیمات | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mx-auto max-w-5xl">
                <div class="mb-6">
                    <p class="text-sm font-bold text-[#ff6570]">
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
                        class="group rounded-3xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:bg-white/[0.035]"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff0f1] text-xl dark:bg-[#ff6d76]/[0.10]"
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
                        class="rounded-3xl bg-white p-5 shadow-sm dark:bg-white/[0.035]"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f1f3f5] text-xl dark:bg-white/[0.06]"
                        >
                            ●
                        </div>

                        <h2 class="font-black">
                            رنگ برنامه
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            انتخاب رنگ اصلی رابط کاربری مایاهمراه
                        </p>

                        <p class="mt-4 text-xs font-bold text-[#ff6570]">
                            در مرحله بعد
                        </p>
                    </div>

                    <div
                        class="rounded-3xl bg-white p-5 shadow-sm dark:bg-white/[0.035]"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f1f3f5] text-xl font-black dark:bg-white/[0.06]"
                        >
                            Aa
                        </div>

                        <h2 class="font-black">
                            فونت
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            انتخاب فونت و اندازه نوشته‌های برنامه
                        </p>

                        <p class="mt-4 text-xs font-bold text-[#ff6570]">
                            در مرحله بعد
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
