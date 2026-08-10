<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    returnTo: {
        type: String,
        default: '',
    },
    defaultContactType: {
        type: String,
        default: 'individual',
    },
});

const isAnnouncerContext = props.returnTo === 'announced-devices.create';

const form = useForm({
    name: '',
    mobile: '',
    phone: '',
    contact_type: props.defaultContactType,
    description: '',
    return_to: props.returnTo,
});

const normalizeDigits = (value) => {
    if (!value) return '';

    return value
        .replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
        .replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
};

const handleMobile = (event) => {
    form.mobile = normalizeDigits(event.target.value);
};

const handlePhone = (event) => {
    form.phone = normalizeDigits(event.target.value);
};

const submit = () => {
    form.post(route('contacts.store'));
};
</script>

<template>
    <Head title="افزودن شخص | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">افزودن شخص</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            ثبت همکار، فروشنده یا خریدار جدید
                        </p>
                    </div>

                    <Link
                        :href="props.returnTo ? route(props.returnTo) : route('contacts.index')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <form
                    class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                نام و نام خانوادگی
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-800 dark:bg-slate-950"
                                placeholder="مثلاً علی احمدی"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                شماره موبایل
                            </label>

                            <input
                                :value="form.mobile"
                                type="tel"
                                inputmode="numeric"
                                dir="ltr"
                                class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-left focus:border-violet-500 focus:ring-violet-500 dark:border-slate-800 dark:bg-slate-950"
                                placeholder="09123456789"
                                @input="handleMobile"
                            />

                            <p
                                v-if="form.errors.mobile"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.mobile }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                تلفن ثابت
                            </label>

                            <input
                                :value="form.phone"
                                type="tel"
                                inputmode="numeric"
                                dir="ltr"
                                class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-left focus:border-violet-500 focus:ring-violet-500 dark:border-slate-800 dark:bg-slate-950"
                                placeholder="اختیاری"
                                @input="handlePhone"
                            />

                            <p
                                v-if="form.errors.phone"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                نوع شخص *
                            </label>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    class="cursor-pointer rounded-2xl border p-4 transition"
                                    :class="
                                        form.contact_type === 'individual'
                                            ? 'border-violet-500 bg-violet-50 dark:bg-violet-950/30'
                                            : 'border-slate-200 dark:border-slate-800'
                                    "
                                >
                                    <input
                                        v-model="form.contact_type"
                                        type="radio"
                                        value="individual"
                                        class="ml-2"
                                        :disabled="isAnnouncerContext"
                                    />
                                    <span class="font-black">شخص عادی</span>
                                    <p class="mt-1 text-xs text-slate-500">
                                        مشتری یا فردی که مستقیم از او خرید یا به او فروش داریم
                                    </p>
                                </label>

                                <label
                                    class="cursor-pointer rounded-2xl border p-4 transition"
                                    :class="
                                        form.contact_type === 'colleague'
                                            ? 'border-violet-500 bg-violet-50 dark:bg-violet-950/30'
                                            : 'border-slate-200 dark:border-slate-800'
                                    "
                                >
                                    <input
                                        v-model="form.contact_type"
                                        type="radio"
                                        value="colleague"
                                        class="ml-2"
                                    />
                                    <span class="font-black">همکار</span>
                                    <p class="mt-1 text-xs text-slate-500">
                                        همکار بازار که می‌تواند برای ما گوشی اعلام کند
                                    </p>
                                </label>
                            </div>

                            <p
                                v-if="form.errors.contact_type"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.contact_type }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                توضیحات
                            </label>

                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-800 dark:bg-slate-950"
                                placeholder="مثلاً همکار بازار موبایل، توضیحات یا اطلاعات تکمیلی..."
                            ></textarea>

                            <p
                                v-if="form.errors.description"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-violet-600 px-6 py-3 font-black text-white transition hover:bg-violet-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'در حال ثبت...' : 'ثبت شخص' }}
                        </button>

                        <Link
                            :href="route('contacts.index')"
                            class="rounded-2xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-600 dark:border-slate-800 dark:text-slate-300"
                        >
                            انصراف
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
