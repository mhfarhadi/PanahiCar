<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';

const props = defineProps({
    device: {
        type: Object,
        required: true,
    },
    contacts: {
        type: Array,
        default: () => [],
    },
});

const now = new Date();
const localDate = [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, '0'),
    String(now.getDate()).padStart(2, '0'),
].join('-');

const form = useForm({
    buyer_id: '',
    sale_price: '',
    sale_date: localDate,
    notes: '',
});

const buyerSearch = ref('');

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const normalize = (value) =>
    normalizeDigits(value).toLowerCase().trim();

const filteredContacts = computed(() => {
    const query = normalize(buyerSearch.value);

    let results = !query
        ? [...props.contacts]
        : props.contacts.filter((contact) =>
              normalize(`${contact.name} ${contact.mobile ?? ''}`).includes(query)
          );

    const selected = props.contacts.find(
        (contact) => String(contact.id) === String(form.buyer_id)
    );

    if (selected && !results.some((contact) => contact.id === selected.id)) {
        results.unshift(selected);
    }

    return results;
});

const selectedBuyer = computed(() =>
    props.contacts.find(
        (contact) => String(contact.id) === String(form.buyer_id)
    )
);

const formatPrice = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    if (!digits) return '';

    return Number(digits).toLocaleString('fa-IR');
};

const handleSalePrice = (event) => {
    form.sale_price = normalizeDigits(event.target.value).replace(/\D/g, '');
};

const profit = computed(() => {
    const sale = Number(form.sale_price || 0);
    const purchase = Number(props.device.purchase_price || 0);

    if (!sale || !purchase) return null;

    return sale - purchase;
});

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const submit = () => {
    form.post(route('sales.store', props.device.id));
};
</script>

<template>
    <Head title="ثبت فروش | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-4xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">
                            مایاهمراه
                        </p>

                        <h1 class="mt-1 text-2xl font-black">
                            ثبت فروش نقدی
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ device.brand }} {{ device.model }}
                            <span v-if="device.storage">· {{ device.storage }}</span>
                        </p>
                    </div>

                    <Link
                        :href="route('devices.show', device.id)"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <div class="mb-5 rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-900">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-xs text-slate-400">دستگاه</p>
                            <p class="mt-1 font-black">
                                {{ device.brand }} {{ device.model }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">رنگ</p>
                            <p class="mt-1 font-bold">
                                {{ device.color || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">IMEI</p>
                            <p class="mt-1 font-bold" dir="ltr">
                                {{ device.imei || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">قیمت خرید</p>
                            <p class="mt-1 font-black">
                                {{ formatMoney(device.purchase_price) }} تومان
                            </p>
                        </div>
                    </div>
                </div>

                <form
                    class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                خریدار *
                            </label>

                            <input
                                v-model="buyerSearch"
                                type="text"
                                placeholder="جستجو با نام یا موبایل"
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                            />

                            <div
                                class="mt-3 max-h-52 space-y-2 overflow-y-auto rounded-2xl border border-slate-100 p-2 dark:border-slate-800"
                            >
                                <button
                                    v-for="contact in filteredContacts"
                                    :key="contact.id"
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-xl px-3 py-3 text-right transition"
                                    :class="
                                        String(form.buyer_id) === String(contact.id)
                                            ? 'bg-violet-600 text-white'
                                            : 'hover:bg-slate-50 dark:hover:bg-slate-800'
                                    "
                                    @click="form.buyer_id = contact.id"
                                >
                                    <span class="font-bold">
                                        {{ contact.name }}
                                    </span>

                                    <span class="text-xs opacity-70" dir="ltr">
                                        {{ contact.mobile }}
                                    </span>
                                </button>
                            </div>

                            <div
                                v-if="selectedBuyer"
                                class="mt-3 rounded-2xl bg-violet-50 p-4 dark:bg-violet-950/30"
                            >
                                <p class="font-black">
                                    {{ selectedBuyer.name }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500" dir="ltr">
                                    {{ selectedBuyer.mobile }}
                                </p>
                            </div>

                            <p
                                v-if="form.errors.buyer_id"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.buyer_id }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                قیمت فروش *
                            </label>

                            <input
                                :value="formatPrice(form.sale_price)"
                                type="text"
                                inputmode="numeric"
                                placeholder="مثلاً ۱۳۵,۰۰۰,۰۰۰"
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                @input="handleSalePrice"
                            />

                            <p
                                v-if="form.errors.sale_price"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.sale_price }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                تاریخ فروش *
                            </label>

                            <Vue3PersianDatetimePicker
                                v-model="form.sale_date"
                                format="YYYY-MM-DD"
                                display-format="jYYYY/jMM/jDD"
                                type="date"
                                auto-submit
                                custom-input=".sale-date-input"
                            />

                            <input
                                type="text"
                                class="sale-date-input w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                placeholder="تاریخ فروش"
                                readonly
                            />

                            <p
                                v-if="form.errors.sale_date"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ form.errors.sale_date }}
                            </p>
                        </div>

                        <div
                            v-if="profit !== null"
                            class="sm:col-span-2 rounded-2xl p-4"
                            :class="
                                profit >= 0
                                    ? 'bg-emerald-50 dark:bg-emerald-950/30'
                                    : 'bg-red-50 dark:bg-red-950/30'
                            "
                        >
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                سود / زیان این فروش
                            </p>

                            <p
                                class="mt-1 text-xl font-black"
                                :class="
                                    profit >= 0
                                        ? 'text-emerald-600'
                                        : 'text-red-600'
                                "
                            >
                                {{ profit >= 0 ? '+' : '' }}{{ formatMoney(profit) }} تومان
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                توضیحات
                            </label>

                            <textarea
                                v-model="form.notes"
                                rows="4"
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                placeholder="اختیاری"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-7 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-violet-600 px-6 py-3 text-sm font-black text-white transition hover:bg-violet-700 disabled:opacity-50"
                        >
                            ثبت فروش نقدی
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
