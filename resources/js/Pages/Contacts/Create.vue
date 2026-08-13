<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    avatar: null,
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


const avatarPreview = ref('');

const handleAvatar = (event) => {
    const file = event.target.files?.[0] ?? null;

    form.avatar = file;

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }

    avatarPreview.value = file ? URL.createObjectURL(file) : '';
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
            class="mh-page"
        >
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-[#ff6570]">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">افزودن شخص</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            ثبت همکار، فروشنده یا خریدار جدید
                        </p>
                    </div>

                    <Link
                        :href="props.returnTo ? route(props.returnTo) : route('contacts.index')"
                        class="rounded-2xl border border-slate-200/60 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-white/5 dark:bg-white/[0.035] dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <form
                    class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-white/[0.035] sm:p-7"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                عکس پروفایل
                                <span class="font-normal text-slate-400">(اختیاری)</span>
                            </label>

                            <div class="flex flex-col gap-4 rounded-2xl border border-dashed border-slate-200/60 p-4 dark:border-white/10 sm:flex-row sm:items-center">
                                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-3xl bg-[#fff0f1] text-4xl dark:bg-[#ff6d76]/[0.10]">
                                    <img
                                        v-if="avatarPreview"
                                        :src="avatarPreview"
                                        alt="پیش‌نمایش عکس شخص"
                                        class="h-full w-full object-cover"
                                    />

                                    <span v-else>👤</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="block w-full text-sm text-slate-500 file:ml-4 file:rounded-xl file:border-0 file:bg-[#fff0f1] file:px-4 file:py-2 file:font-bold file:text-[#d85e68] dark:text-slate-400 dark:file:bg-violet-950/40 dark:file:text-violet-300"
                                        @change="handleAvatar"
                                    />

                                    <p class="mt-2 text-xs leading-6 text-slate-400">
                                        فقط برای شناسایی سریع شخص؛ JPG، PNG یا WEBP تا ۵ مگابایت.
                                    </p>

                                    <p
                                        v-if="form.errors.avatar"
                                        class="mt-2 text-xs font-bold text-red-500"
                                    >
                                        {{ form.errors.avatar }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                نام و نام خانوادگی
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-2xl border-slate-200/60 bg-white px-4 py-3 focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/5 dark:bg-white/[0.025]"
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
                                class="w-full rounded-2xl border-slate-200/60 bg-white px-4 py-3 text-left focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/5 dark:bg-white/[0.025]"
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
                                class="w-full rounded-2xl border-slate-200/60 bg-white px-4 py-3 text-left focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/5 dark:bg-white/[0.025]"
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
                                            ? 'border-[#ff6d76] bg-[#fff0f1] dark:bg-[#ff6d76]/[0.08]'
                                            : 'border-slate-200/60 dark:border-white/5'
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
                                            ? 'border-[#ff6d76] bg-[#fff0f1] dark:bg-[#ff6d76]/[0.08]'
                                            : 'border-slate-200/60 dark:border-white/5'
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
                                class="w-full rounded-2xl border-slate-200/60 bg-white px-4 py-3 focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/5 dark:bg-white/[0.025]"
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
                            class="rounded-2xl bg-[#ff6d76] px-6 py-3 font-black text-white transition hover:bg-[#f45f6a] disabled:opacity-50"
                        >
                            {{ form.processing ? 'در حال ثبت...' : 'ثبت شخص' }}
                        </button>

                        <Link
                            :href="route('contacts.index')"
                            class="rounded-2xl border border-slate-200/60 px-6 py-3 text-center font-bold text-slate-600 dark:border-white/5 dark:text-slate-300"
                        >
                            انصراف
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
