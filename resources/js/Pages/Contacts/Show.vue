<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntityNoteHistory from '@/Components/EntityNoteHistory.vue';
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
    purchasedFromShop: {
        type: Array,
        default: () => [],
    },
    notes: {
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

const saleTypeLabel = (type) =>
    type === 'installment' ? 'اقساطی' : 'نقدی';

const paymentHistoryLabel = (stats) => {
    if (!stats?.cleared_count) return 'هنوز سابقه کافی برای ارزیابی وجود ندارد';

    if (!stats.delayed_count) return 'سابقه پرداخت منظم';

    return `${Number(stats.delayed_count).toLocaleString('fa-IR')} چک با تأخیر · میانگین ${Number(stats.average_delay_days).toLocaleString('fa-IR')} روز`;
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
                            <div class="mb-6 flex flex-col items-center gap-5 sm:flex-row sm:items-start">
                                <div class="flex h-36 w-36 sm:h-44 sm:w-44 shrink-0 items-center justify-center overflow-hidden rounded-[34px] sm:rounded-[38px] bg-violet-50 text-4xl dark:bg-violet-950/40">
                                    <img
                                        v-if="contact.avatar_path"
                                        :src="`/storage/${contact.avatar_path}`"
                                        :alt="contact.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <span v-else>👤</span>
                                </div>

                                <div class="min-w-0 text-center sm:pt-2 sm:text-right">
                                    <p class="text-xs text-slate-400">شخص</p>
                                    <p class="mt-1 truncate text-lg font-black">{{ contact.name }}</p>
                                </div>
                            </div>

                            <h2 class="text-lg font-black">اطلاعات شخص</h2>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2">

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

                            </div>
                        </section>

                        <EntityNoteHistory
                            entity-type="contact"
                            :entity-id="contact.id"
                            :notes="notes"
                            title="یادداشت‌های این شخص"
                            empty-text="هنوز یادداشتی برای این شخص ثبت نشده است."
                        />

                        <section class="rounded-[30px] bg-white p-6 shadow-sm dark:bg-slate-900">
                            <h2 class="text-lg font-black">گوشی‌های خریداری‌شده از ما</h2>

                            <div
                                v-if="!purchasedFromShop.length"
                                class="mt-5 rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500 dark:bg-slate-950"
                            >
                                هنوز خریدی از ما ثبت نشده است.
                            </div>

                            <div v-else class="mt-5 space-y-3">
                                <Link
                                    v-for="item in purchasedFromShop"
                                    :key="item.sale_id"
                                    :href="route('sales.show', item.sale_id)"
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

                                        <span class="rounded-lg bg-violet-50 px-2 py-1 text-xs font-bold text-violet-700 dark:bg-violet-950/30 dark:text-violet-300">
                                            {{ saleTypeLabel(item.sale_type) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                                        <span>
                                            قیمت فروش:
                                            <strong>{{ money(item.sale_price) }}</strong>
                                        </span>

                                        <span>
                                            تاریخ:
                                            <strong>{{ persianDate(item.sale_date) }}</strong>
                                        </span>

                                        <span v-if="item.sale_type === 'installment' && item.contract_total">
                                            کل قرارداد:
                                            <strong>{{ money(item.contract_total) }}</strong>
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </section>

                        <section class="grid grid-cols-2 gap-3 sm:grid-cols-3">
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

                            <div class="col-span-2 sm:col-span-1 rounded-[24px] bg-white p-5 shadow-sm dark:bg-slate-900">
                                <p class="text-xs text-slate-400">خرید از ما</p>
                                <p class="mt-2 text-2xl font-black">
                                    {{ contact.stats.purchased_from_shop_count.toLocaleString('fa-IR') }}
                                </p>
                            </div>
                        </section>

                        <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold text-slate-400">سابقه پرداخت چک‌ها</p>
                                    <p class="mt-2 font-black">
                                        {{ paymentHistoryLabel(contact.payment_stats) }}
                                    </p>
                                </div>

                                <span
                                    v-if="contact.payment_stats.cleared_count > 0"
                                    class="rounded-xl px-3 py-1.5 text-xs font-black"
                                    :class="
                                        contact.payment_stats.delayed_count === 0
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300'
                                    "
                                >
                                    {{
                                        contact.payment_stats.delayed_count === 0
                                            ? 'منظم'
                                            : 'نیاز به توجه'
                                    }}
                                </span>
                            </div>

                            <p class="mt-3 text-xs leading-6 text-slate-400">
                                این شاخص فقط بر اساس زمان پاس شدن چک‌های ثبت‌شده محاسبه می‌شود.
                            </p>
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
