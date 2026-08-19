<script setup>
import { computed, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { toJalaali, toGregorian, jalaaliMonthLength } from 'jalaali-js';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import JalaliDateInput from '@/Components/JalaliDateInput.vue';
import { showroomPhoto } from '@/Utils/carPhotos';
import {
    formatDecimalInput,
    formatIntegerInput,
    formatMoney,
    formatPriceInput,
    parseDecimal,
    parseInteger,
    parsePrice,
    postJson,
    todayIso,
    toPersianDigits,
} from '@/Utils/featuresForm';

const mode = ref('regular');
const loading = ref(false);
const result = ref(null);
const available = ref(true);
const generalError = ref('');
const errors = ref({});
let paymentSeq = 1;

const form = ref({
    sale_price: '',
    down_payment: '',
    monthly_profit_rate: '6.5',
    installment_count: '12',
    monthly_cap: '',
    sale_date: todayIso(),
    first_due_date: '',
});

const payments = ref([]);

const addJalaliMonths = ({ jy, jm, jd }, count) => {
    const zeroBasedMonth = jm - 1 + count;
    const jyNext = jy + Math.floor(zeroBasedMonth / 12);
    const jmNext = ((zeroBasedMonth % 12) + 12) % 12 + 1;
    const jdNext = Math.min(jd, jalaaliMonthLength(jyNext, jmNext));

    return { jy: jyNext, jm: jmNext, jd: jdNext };
};

const addMonthsToIso = (iso, count) => {
    const match = String(iso || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) return todayIso();

    const jalali = addJalaliMonths(
        toJalaali(Number(match[1]), Number(match[2]), Number(match[3])),
        count
    );
    const gregorian = toGregorian(jalali.jy, jalali.jm, jalali.jd);

    return `${gregorian.gy}-${String(gregorian.gm).padStart(2, '0')}-${String(gregorian.gd).padStart(2, '0')}`;
};

const setDefaultDueDate = () => {
    form.value.first_due_date = addMonthsToIso(form.value.sale_date, 1);
};

const addPayment = () => {
    const last = payments.value.at(-1);
    payments.value.push({
        id: paymentSeq++,
        due_date: last?.due_date
            ? addMonthsToIso(last.due_date, 1)
            : addMonthsToIso(form.value.sale_date, 1),
        amount: '',
    });
};

const removePayment = (id) => {
    if (payments.value.length === 1) {
        payments.value[0].amount = '';
        return;
    }

    payments.value = payments.value.filter((row) => row.id !== id);
};

setDefaultDueDate();
addPayment();

watch(() => form.value.sale_date, setDefaultDueDate);
watch(mode, () => {
    result.value = null;
    available.value = true;
    generalError.value = '';
    errors.value = {};
});

const submit = async () => {
    loading.value = true;
    generalError.value = '';
    errors.value = {};
    result.value = null;

    try {
        const payload = {
            mode: mode.value,
            sale_price: parsePrice(form.value.sale_price),
            down_payment: parsePrice(form.value.down_payment),
            monthly_profit_rate: parseDecimal(form.value.monthly_profit_rate),
            sale_date: form.value.sale_date,
        };

        if (mode.value === 'custom') {
            payload.payments = payments.value.map((row) => ({
                due_date: row.due_date,
                amount: parsePrice(row.amount),
            }));
        } else {
            payload.first_due_date = form.value.first_due_date;
            payload.installment_count = parseInteger(form.value.installment_count);
            payload.monthly_cap = parsePrice(form.value.monthly_cap);
        }

        const data = await postJson(route('features.installments.calculate'), payload);
        available.value = data.available;
        result.value = data.result;
        if (!data.available) {
            generalError.value = 'با این سقف ماهانه برنامه اقساطی پیدا نشد.';
        }
    } catch (error) {
        errors.value = error.payload?.errors || {};
        generalError.value = error.message;
    } finally {
        loading.value = false;
    }
};

const fieldError = (name) => errors.value[name]?.[0];
const moneyField = (key, event) => {
    form.value[key] = formatPriceInput(event.target.value);
};
const decimalField = (key, event) => {
    form.value[key] = formatDecimalInput(event.target.value);
};
const integerField = (key, event) => {
    form.value[key] = formatIntegerInput(event.target.value);
};
const paymentAmount = (row, event) => {
    row.amount = formatPriceInput(event.target.value);
};

const modes = [
    { key: 'regular', title: 'اقساط منظم' },
    { key: 'monthly_cap', title: 'سقف ماهانه' },
    { key: 'custom', title: 'اقساط نامنظم' },
];

const installments = computed(() => result.value?.installments || []);
const customPayments = computed(() => result.value?.payments || []);
const isCustomResult = computed(() => Array.isArray(result.value?.payments));
</script>

<template>
    <Head title="ماشین‌حساب اقساط | Panahi Car" />

    <FeaturesLayout title="اقساط" subtitle="برنامه چک خودرو">
        <div class="relative mb-5 overflow-hidden rounded-[28px]">
            <img :src="showroomPhoto(0)" alt="" class="h-36 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div class="absolute bottom-4 right-4 text-white">
                <p class="text-[11px] text-white/70">ماشین‌حساب</p>
                <h1 class="text-xl font-black">اقساط خودرو</h1>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-3 gap-2">
            <button
                v-for="item in modes"
                :key="item.key"
                type="button"
                class="rounded-full px-2 py-2 text-[11px] font-bold"
                :class="mode === item.key ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-500 dark:bg-[#161618]'"
                @click="mode = item.key"
            >
                {{ item.title }}
            </button>
        </div>

        <form class="space-y-3" @submit.prevent="submit">
            <input class="am-input" :value="form.sale_price" placeholder="قیمت فروش" @input="moneyField('sale_price', $event)" />
            <p v-if="fieldError('sale_price')" class="text-xs text-red-500">{{ fieldError('sale_price') }}</p>
            <input class="am-input" :value="form.down_payment" placeholder="پیش‌پرداخت" @input="moneyField('down_payment', $event)" />
            <input
                class="am-input"
                :value="formatDecimalInput(form.monthly_profit_rate)"
                placeholder="نرخ سود ماهانه ٪"
                inputmode="decimal"
                @input="decimalField('monthly_profit_rate', $event)"
            />
            <input
                v-if="mode === 'regular'"
                class="am-input"
                :value="formatIntegerInput(form.installment_count)"
                placeholder="تعداد اقساط"
                inputmode="numeric"
                @input="integerField('installment_count', $event)"
            />
            <input
                v-else-if="mode === 'monthly_cap'"
                class="am-input"
                :value="form.monthly_cap"
                placeholder="سقف پرداخت ماهانه"
                @input="moneyField('monthly_cap', $event)"
            />
            <JalaliDateInput v-model="form.sale_date" placeholder="تاریخ فروش" input-class="sale-date-input" />
            <JalaliDateInput
                v-if="mode !== 'custom'"
                v-model="form.first_due_date"
                placeholder="اولین سررسید"
                input-class="first-due-input"
            />
            <p v-if="fieldError('first_due_date')" class="text-xs text-red-500">{{ fieldError('first_due_date') }}</p>

            <div v-if="mode === 'custom'" class="space-y-3">
                <div
                    v-for="(row, index) in payments"
                    :key="row.id"
                    class="rounded-[22px] bg-white p-3 dark:bg-[#161618]"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-bold text-neutral-500">چک {{ toPersianDigits(index + 1) }}</p>
                        <button
                            type="button"
                            class="text-[11px] font-bold text-red-500"
                            @click="removePayment(row.id)"
                        >
                            حذف
                        </button>
                    </div>
                    <div class="space-y-2">
                        <JalaliDateInput
                            v-model="row.due_date"
                            placeholder="تاریخ سررسید"
                            :input-class="`custom-due-${row.id}`"
                        />
                        <input
                            class="am-input"
                            :value="row.amount"
                            placeholder="مبلغ چک"
                            @input="paymentAmount(row, $event)"
                        />
                    </div>
                </div>
                <button
                    type="button"
                    class="w-full rounded-full bg-white py-2.5 text-xs font-bold dark:bg-[#161618]"
                    @click="addPayment"
                >
                    افزودن چک
                </button>
                <p v-if="fieldError('payments')" class="text-xs text-red-500">{{ fieldError('payments') }}</p>
            </div>

            <p v-if="generalError" class="text-sm text-red-500">{{ generalError }}</p>
            <button type="submit" class="am-btn-primary w-full" :disabled="loading">
                {{ loading ? 'در حال محاسبه...' : 'محاسبه اقساط' }}
            </button>
        </form>

        <div v-if="result" class="mt-5 space-y-3">
            <div v-if="isCustomResult" class="grid grid-cols-2 gap-2.5">
                <div class="rounded-[22px] bg-white p-4 dark:bg-[#161618]">
                    <p class="text-[10px] text-neutral-400">جمع پرداخت</p>
                    <p class="mt-1 text-sm font-black">{{ formatMoney(result.total_paid) }}</p>
                </div>
                <div class="rounded-[22px] bg-white p-4 dark:bg-[#161618]">
                    <p class="text-[10px] text-neutral-400">سود کل</p>
                    <p class="mt-1 text-sm font-black">{{ formatMoney(result.total_profit) }}</p>
                </div>
                <div class="col-span-2 rounded-[22px] bg-neutral-900 p-4 text-white">
                    <p class="text-[10px] text-white/60">مانده</p>
                    <p class="mt-1 text-sm font-black">{{ formatMoney(result.remaining_balance) }}</p>
                </div>
            </div>
            <div v-else class="grid grid-cols-2 gap-2.5">
                <div class="rounded-[22px] bg-white p-4 dark:bg-[#161618]">
                    <p class="text-[10px] text-neutral-400">قسط ماهانه</p>
                    <p class="mt-1 text-sm font-black">{{ formatMoney(result.installment_amount) }}</p>
                </div>
                <div class="rounded-[22px] bg-white p-4 dark:bg-[#161618]">
                    <p class="text-[10px] text-neutral-400">جمع قرارداد</p>
                    <p class="mt-1 text-sm font-black">{{ formatMoney(result.contract_total) }}</p>
                </div>
            </div>
            <div class="space-y-2">
                <div
                    v-for="item in isCustomResult ? customPayments : installments"
                    :key="item.installment_number"
                    class="flex items-center justify-between rounded-[22px] bg-white px-4 py-3 dark:bg-[#161618]"
                >
                    <p class="text-sm font-bold">قسط {{ toPersianDigits(item.installment_number) }}</p>
                    <p class="text-xs font-black">{{ formatMoney(item.amount) }}</p>
                </div>
            </div>
        </div>
    </FeaturesLayout>
</template>
