<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="تنظیمات | automaya" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5">
                    <h1 class="text-xl font-black">تنظیمات</h1>
                    <p class="mt-1 text-[11px] font-bold text-slate-400">
                        اشخاص و ظاهر برنامه
                    </p>
                </div>

                <div class="space-y-3">
                    <Link
                        :href="route('features.index')"
                        class="am-row"
                    >
                        <div class="am-thumb !h-12 !w-12 overflow-hidden">
                            <img src="/images/illustrations/illustration-showroom.png" class="h-full w-full object-cover" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black">امکانات</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                اقساط، برآورد قیمت، قرارداد و بازار همکاران
                            </p>
                        </div>
                    </Link>

                    <Link
                        :href="route('contacts.index')"
                        class="am-row"
                    >
                        <div class="am-thumb !h-12 !w-12 text-lg">👤</div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black">اشخاص</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                همکاران، مشتریان و اشخاص عادی
                            </p>
                        </div>
                    </Link>

                    <div class="am-row">
                        <div class="am-thumb !h-12 !w-12 text-lg">●</div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black">رنگ برنامه</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400">در مرحله بعد</p>
                        </div>
                    </div>

                    <div class="am-row">
                        <div class="am-thumb !h-12 !w-12 text-sm font-black">Aa</div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black">فونت</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400">در مرحله بعد</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
