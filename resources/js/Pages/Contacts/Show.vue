<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    contact: {
        type: Object,
        required: true,
    },
    announcedDevices: {
        type: Array,
        default: () => [],
    },
    soldToShop: {
        type: Array,
        default: () => [],
    },
});

const money = (value) => {
    if (value === null || value === undefined) return '—';
    return Number(value).toLocaleString('fa-IR') + ' تومان';
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

const statusLabel = (status) => {
    if (status === 'announced') return 'اعلامی';
    if (status === 'in_stock') return 'موجود';
    if (status === 'sold') return 'فروخته‌شده';
    return status || '—';
};
</script>

<template>
    <Head :title="`${contact.name} | مایاهمراه`" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-violet-600">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">{{ contact.name }}</h1>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-xl px-3 py-1 text-xs font-bold"
                                :class="
                                    contact.contact_type === 'colleague'
                                        ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-300'
                                        : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                "
                            >
                                {{ contact.contact_type === 'colleague' ? 'همکار' : 'شخص عادی' }}
                            </span>

                            <span class="text-sm text-slate-500">
                                پروفایل و سابقه همکاری
                            </span>
                        </div>
                    </div>

                    <Link
                        :href="route('contacts.index')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت به اشخاص
                    </Link>
                </div>

                <div class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
                    <div class="space-y-5">
                        <section class="rounded-[30px] bg-white p-6 shadow-sm dark:bg-slate-900">
                            <h2 class="text-lg font-black">اطلاعات شخص</h2>

                            <div class="mt-5 space-y-4">
                                <div>
                                    <p class="text-xs text-slate-400">نام</p>
                                    <p class="mt-1 font-black">{{ contact.name }}</p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-400">موبایل</p>
                                    <a
                                        :href="`tel:${contact.mobile}`"
                                        class="mt-1 inline-block font-bold text-violet-600"
                                        dir="ltr"
                                    >
                                        {{ contact.mobile }}
                                    </a>
                                </div>

                                <div v-if="contact.phone">
                                    <p class="text-xs text-slate-400">تلفن ثابت</p>
                                    <p class="mt-1 font-bold" dir="ltr">
                                        {{ contact.phone }}
                                    </p>
                                </div>

                                <div v-if="contact.description">
                                    <p class="text-xs text-slate-400">توضیحات</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">
                                        {{ contact.description }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section class="grid grid-cols-2 gap-3">
                            <div class="rounded-[24px] bg-white p-5 shadow-sm dark:bg-slate-900">
                                <p class="text-xs text-slate-400">گوشی‌های اعلامی</p>
                                <p class="mt-2 text-2xl font-black">
                                    {{ contact.stats.announced_count.toLocaleString('fa-IR') }}
                                </p>
                            </div>

                            <div class="rounded-[24px] bg-white p-5 shadow-sm dark:bg-slate-900">
                                <p class="text-xs text-slate-400">فروش به مغازه</p>
                                <p class="mt-2 text-2xl font-black">
                                    {{ contact.stats.sold_to_shop_count.toLocaleString('fa-IR') }}
                                </p>
                            </div>
                        </section>
                    </div>

                    <div class="space-y-5">
                        <section class="rounded-[30px] bg-white p-6 shadow-sm dark:bg-slate-900">
                            <h2 class="text-lg font-black">گوشی‌های اعلامی</h2>

                            <div
                                v-if="!announcedDevices.length"
                                class="mt-5 rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500 dark:bg-slate-950"
                            >
                                سابقه گوشی اعلامی ندارد.
                            </div>

                            <div v-else class="mt-5 space-y-3">
                                <div
                                    v-for="device in announcedDevices"
                                    :key="device.id"
                                    class="rounded-2xl border border-slate-100 p-4 dark:border-slate-800"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-black">
                                                {{ device.brand }} {{ device.model }}
                                            </p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ device.storage || '—' }} · {{ device.color || '—' }}
                                            </p>
                                        </div>

                                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold dark:bg-slate-800">
                                            {{ statusLabel(device.status) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                                        <span>
                                            قیمت اعلامی:
                                            <strong>{{ money(device.announced_price) }}</strong>
                                        </span>

                                        <span>
                                            تاریخ:
                                            <strong>{{ persianDate(device.announced_at) }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[30px] bg-white p-6 shadow-sm dark:bg-slate-900">
                            <h2 class="text-lg font-black">فروش گوشی به مغازه</h2>

                            <div
                                v-if="!soldToShop.length"
                                class="mt-5 rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500 dark:bg-slate-950"
                            >
                                سابقه فروش گوشی به مغازه ندارد.
                            </div>

                            <div v-else class="mt-5 space-y-3">
                                <Link
                                    v-for="item in soldToShop"
                                    :key="item.purchase_id"
                                    :href="route('devices.show', item.device_id)"
                                    class="block rounded-2xl border border-slate-100 p-4 transition hover:border-violet-300 hover:bg-violet-50/40 dark:border-slate-800 dark:hover:bg-violet-950/20"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-black">
                                                {{ item.brand }} {{ item.model }}
                                            </p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ item.storage || '—' }} · {{ item.color || '—' }}
                                            </p>
                                        </div>

                                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold dark:bg-slate-800">
                                            {{ statusLabel(item.status) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                                        <span>
                                            قیمت خرید:
                                            <strong>{{ money(item.purchase_price) }}</strong>
                                        </span>

                                        <span>
                                            تاریخ:
                                            <strong>{{ persianDate(item.purchase_date) }}</strong>
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
