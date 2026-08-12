<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    contact: {
        type: Object,
        required: true,
    },
    canChangeType: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    _method: 'patch',
    name: props.contact.name || '',
    mobile: props.contact.mobile || '',
    phone: props.contact.phone || '',
    contact_type: props.contact.contact_type || 'individual',
    avatar: null,
    remove_avatar: false,
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

const avatarPreview = ref(
    props.contact.avatar_path
        ? `/storage/${props.contact.avatar_path}`
        : ''
);

const handleAvatar = (event) => {
    const file = event.target.files?.[0] ?? null;

    form.avatar = file;
    form.remove_avatar = false;

    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const removeAvatar = () => {
    form.avatar = null;
    form.remove_avatar = true;
    avatarPreview.value = '';
};

const submit = () => {
    form.post(route('contacts.update', props.contact.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`ویرایش ${contact.name} | مایاهمراه`" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-3xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">ویرایش شخص</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            اطلاعات هویتی و تماس را اصلاح کنید
                        </p>
                    </div>

                    <Link
                        :href="route('contacts.show', contact.id)"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <div
                    v-if="contact.archived_at"
                    class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300"
                >
                    این شخص در حال حاضر آرشیو شده است.
                </div>

                <form
                    class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                عکس پروفایل
                            </label>

                            <div class="flex flex-col gap-4 rounded-2xl border border-dashed border-slate-200 p-4 dark:border-slate-700 sm:flex-row sm:items-center">
                                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-3xl bg-violet-50 text-4xl dark:bg-violet-950/40">
                                    <img
                                        v-if="avatarPreview"
                                        :src="avatarPreview"
                                        :alt="contact.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <span v-else>👤</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="block w-full text-sm text-slate-500 file:ml-4 file:rounded-xl file:border-0 file:bg-violet-50 file:px-4 file:py-2 file:font-bold file:text-violet-700 dark:text-slate-400 dark:file:bg-violet-950/40 dark:file:text-violet-300"
                                        @change="handleAvatar"
                                    />

                                    <button
                                        v-if="avatarPreview"
                                        type="button"
                                        class="mt-3 text-xs font-bold text-red-500"
                                        @click="removeAvatar"
                                    >
                                        حذف عکس فعلی
                                    </button>

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
                                class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-800 dark:bg-slate-950"
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
                                نوع شخص
                            </label>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    class="rounded-2xl border p-4"
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
                                        :disabled="!canChangeType"
                                    />
                                    <span class="font-black">شخص عادی</span>
                                </label>

                                <label
                                    class="rounded-2xl border p-4"
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
                                        :disabled="!canChangeType"
                                    />
                                    <span class="font-black">همکار</span>
                                </label>
                            </div>

                            <p
                                v-if="!canChangeType"
                                class="mt-2 text-xs text-slate-400"
                            >
                                تغییر نوع شخص فقط برای مدیر اصلی مجاز است.
                            </p>
                        </div>

                        <div class="sm:col-span-2 rounded-2xl bg-slate-50 p-4 text-sm leading-7 text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                            توضیحات از این صفحه ویرایش نمی‌شوند. برای حفظ سابقه،
                            یادداشت جدید را از پروفایل شخص اضافه کنید.
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-violet-600 px-6 py-3 font-black text-white transition hover:bg-violet-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'در حال ذخیره...' : 'ذخیره تغییرات' }}
                        </button>

                        <Link
                            :href="route('contacts.show', contact.id)"
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
