<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    sales: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');

let searchTimer = null;

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('sales.index'),
            { search: search.value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300);
});

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const formatDate = (value) => {
    if (!value) return '—';

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(`${value}T00:00:00`));
};

const saleTypeLabel = (type) =>
    type === 'installment' ? 'اقساطی' : 'نقدی';
</script>

<template>
    <Head title="گوشی‌های فروخته‌شده | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">
                            مایاهمراه
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            گوشی‌های فروخته‌شده
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            سوابق فروش، خریداران و سود هر دستگاه
                        </p>
                    </div>

                    <Link
                        :href="route('dashboard')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        داشبورد
                    </Link>
                </div>

                <div class="mb-5">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="جستجو با برند، مدل، حافظه، رنگ، IMEI یا خریدار..."
                        autocomplete="off"
                        class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-slate-800 dark:bg-slate-900"
                    />
                </div>

                <div
                    v-if="sales.length === 0"
                    class="rounded-3xl bg-white p-8 text-center shadow-sm dark:bg-slate-900"
                >
                    <p class="text-lg font-black">
                        هنوز فروشی ثبت نشده
                    </p>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        بعد از ثبت فروش، اطلاعات دستگاه در این بخش نمایش داده می‌شود.
                    </p>
                </div>

                <div
                    v-else
                    class="grid items-stretch gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="sale in sales"
                        :key="sale.id"
                    class="flex h-full flex-col overflow-hidden rounded-[30px] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900"
                    >
                    <div class="w-full shrink-0 overflow-hidden bg-slate-100 dark:bg-slate-800" style="height: 224px; min-height: 224px; max-height: 224px;">
                        <img
                            v-if="sale.cover_image"
                            :src="`/storage/${sale.cover_image}`"
                            :alt="`${sale.brand} ${sale.model}`"
                            class="block w-full object-cover" style="height: 224px; min-height: 224px; max-height: 224px;"
                        />

                        <div
                            v-else
                            class="flex w-full items-center justify-center bg-slate-100 dark:bg-slate-800" style="height: 224px; min-height: 224px; max-height: 224px;"
                        >
                            <span class="text-8xl opacity-40">📱</span>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-black">
                                        {{ sale.brand }} {{ sale.model }}
                                    </h2>

                                    <span
                                        class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-black text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                                    >
                                        فروخته شد
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    <span v-if="sale.storage">
                                        {{ sale.storage }}
                                    </span>
                                    <span v-if="sale.color">
                                        · {{ sale.color }}
                                    </span>
                                </p>
                            </div>

                            <span
                                class="rounded-xl bg-violet-50 px-3 py-1.5 text-xs font-black text-violet-700 dark:bg-violet-950/30 dark:text-violet-300"
                            >
                                {{ saleTypeLabel(sale.sale_type) }}
                            </span>
                        </div>

                        <div class="mt-5 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-400">
                                    خریدار
                                </span>

                                <Link
                                    :href="route('contacts.show', sale.buyer_id)"
                                    class="font-black text-violet-600"
                                >
                                    {{ sale.buyer_name }}
                                </Link>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-400">
                                    موبایل
                                </span>

                                <a
                                    :href="`tel:${sale.buyer_mobile}`"
                                    class="font-bold"
                                    dir="ltr"
                                >
                                    {{ sale.buyer_mobile }}
                                </a>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-400">
                                    تاریخ فروش
                                </span>

                                <span class="font-bold">
                                    {{ formatDate(sale.sale_date) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-400">
                                    قیمت خرید
                                </span>

                                <span class="font-bold">
                                    {{ formatMoney(sale.purchase_price) }} تومان
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-slate-400">
                                    قیمت فروش
                                </span>

                                <span class="font-black">
                                    {{ formatMoney(sale.sale_price) }} تومان
                                </span>
                            </div>

                            <div
                                v-if="sale.profit !== null"
                                class="flex items-center justify-between gap-4 border-t border-slate-100 pt-3 dark:border-slate-800"
                            >
                                <span class="text-slate-400">
                                    سود / زیان
                                </span>

                                <span
                                    class="font-black"
                                    :class="
                                        sale.profit >= 0
                                            ? 'text-emerald-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ sale.profit >= 0 ? '+' : '' }}{{ formatMoney(sale.profit) }} تومان
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="sale.imei"
                            class="mt-4 rounded-2xl bg-slate-50 p-3 text-xs text-slate-500 dark:bg-slate-950 dark:text-slate-400"
                        >
                            IMEI:
                            <span class="font-bold" dir="ltr">
                                {{ sale.imei }}
                            </span>
                        </div>

                        <Link
                            :href="route('sales.show', sale.id)"
                            class="mt-4 flex w-full items-center justify-center rounded-2xl bg-violet-600 px-4 py-3 text-sm font-black text-white transition hover:bg-violet-700"
                        >
                            مشاهده جزئیات فروش
                        </Link>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
