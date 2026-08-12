<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { colorLabel } from '@/Utils/deviceLabels';

const props = defineProps({
    device: {
        type: Object,
        required: true,
    },
});

const now = new Date();
const localDate = [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, '0'),
    String(now.getDate()).padStart(2, '0'),
].join('-');

const form = useForm({
    purchase_price: props.device.announced_price ?? '',
    purchase_date: localDate,
    notes: '',
});

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
        .replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));

const formatPrice = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');
    return digits ? Number(digits).toLocaleString('fa-IR') : '';
};

const threeDigitToWords = (number) => {
    const ones = [
        '', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه',
        'ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده',
        'شانزده', 'هفده', 'هجده', 'نوزده'
    ];

    const tens = [
        '', '', 'بیست', 'سی', 'چهل', 'پنجاه',
        'شصت', 'هفتاد', 'هشتاد', 'نود'
    ];

    const hundreds = [
        '', 'یکصد', 'دویست', 'سیصد', 'چهارصد',
        'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'
    ];

    const parts = [];

    if (number >= 100) {
        parts.push(hundreds[Math.floor(number / 100)]);
        number %= 100;
    }

    if (number >= 20) {
        parts.push(tens[Math.floor(number / 10)]);
        number %= 10;

        if (number > 0) parts.push(ones[number]);
    } else if (number > 0) {
        parts.push(ones[number]);
    }

    return parts.join(' و ');
};

const numberToPersianWords = (value) => {
    const number = Number(value);

    if (!Number.isFinite(number) || number <= 0) return '';

    const scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];
    const groups = [];
    let remaining = Math.floor(number);
    let scaleIndex = 0;

    while (remaining > 0 && scaleIndex < scales.length) {
        const group = remaining % 1000;

        if (group > 0) {
            const words = threeDigitToWords(group);
            groups.unshift(
                scales[scaleIndex]
                    ? `${words} ${scales[scaleIndex]}`
                    : words
            );
        }

        remaining = Math.floor(remaining / 1000);
        scaleIndex++;
    }

    return groups.join(' و ');
};

const purchasePriceWords = computed(() => {
    const words = numberToPersianWords(form.purchase_price);
    return words ? `${words} تومان` : '';
});

const handlePurchasePrice = (event) => {
    const digits = normalizeDigits(event.target.value).replace(/\D/g, '');
    form.purchase_price = digits;
    event.target.value = formatPrice(digits);
};

const money = (value) => {
    if (value === null || value === undefined || value === '') return '—';
    return Number(value).toLocaleString('fa-IR') + ' تومان';
};

const submit = () => {
    form.post(route('announced-devices.purchase.store', props.device.id));
};
</script>

<template>
    <Head title="خرید گوشی اعلامی | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-4xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-violet-600">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">خرید گوشی اعلامی</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            با ثبت خرید، گوشی از لیست اعلامی خارج و وارد موجودی مغازه می‌شود.
                        </p>
                    </div>

                    <Link
                        :href="route('announced-devices.index')"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        بازگشت
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <section class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7">
                        <h2 class="text-lg font-black">مشخصات گوشی</h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                <p class="text-xs text-slate-400">دستگاه</p>
                                <p class="mt-1 font-black">
                                    {{ device.brand }} {{ device.model }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                <p class="text-xs text-slate-400">حافظه / رنگ</p>
                                <p class="mt-1 font-bold">
                                    {{ device.storage || '—' }} · {{ colorLabel(device.color) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                <p class="text-xs text-slate-400">IMEI</p>
                                <p class="mt-1 font-bold" dir="ltr">
                                    {{ device.imei || '—' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                <p class="text-xs text-slate-400">اعلام‌کننده / فروشنده</p>
                                <p class="mt-1 font-black">
                                    {{ device.announcer_name || '—' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                                <p class="text-xs text-slate-400">شماره تماس</p>
                                <p class="mt-1 font-bold" dir="ltr">
                                    {{ device.announcer_mobile || '—' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-violet-50 p-4 dark:bg-violet-950/30">
                                <p class="text-xs text-slate-500">قیمت اعلامی</p>
                                <p class="mt-1 font-black text-violet-700 dark:text-violet-300">
                                    {{ money(device.announced_price) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-7">
                        <h2 class="text-lg font-black">ثبت خرید واقعی</h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    قیمت خرید *
                                </label>

                                <input
                                    :value="formatPrice(form.purchase_price)"
                                    type="text"
                                    inputmode="numeric"
                                    @input="handlePurchasePrice"
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                />

                                <p
                                    v-if="purchasePriceWords"
                                    class="mt-2 text-sm font-bold text-violet-600 dark:text-violet-300"
                                >
                                    {{ purchasePriceWords }}
                                </p>

                                <p
                                    v-if="form.errors.purchase_price"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.purchase_price }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    تاریخ خرید *
                                </label>

                                <Vue3PersianDatetimePicker
                                    v-model="form.purchase_date"
                                    format="YYYY-MM-DD"
                                    display-format="jYYYY/jMM/jDD"
                                    convert-numbers
                                    :editable="false"
                                    :auto-submit="true"
                                    color="#7c3aed"
                                    input-class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-right focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                                    placeholder="انتخاب تاریخ خرید"
                                />
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-bold">
                                توضیحات خرید
                            </label>

                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="در صورت نیاز توضیحات خرید را وارد کنید..."
                                class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950"
                            ></textarea>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse gap-3 pb-8 sm:flex-row">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-violet-600 px-8 py-3 font-black text-white shadow-lg shadow-violet-200 transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
                        >
                            {{ form.processing ? 'در حال ثبت خرید...' : 'تأیید خرید و انتقال به موجودی' }}
                        </button>

                        <Link
                            :href="route('announced-devices.index')"
                            class="rounded-2xl bg-white px-8 py-3 text-center font-bold text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-300"
                        >
                            انصراف
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
