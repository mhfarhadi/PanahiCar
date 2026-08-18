<script setup>
import { ref, watch } from 'vue';
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
} from '@/Utils/featuresForm';

const loading = ref(false);
const result = ref(null);
const generalError = ref('');
const errors = ref({});

const form = ref({
    sale_price: '',
    down_payment: '',
    monthly_profit_rate: '6.5',
    installment_count: '12',
    gold_rate_per_gram: '',
    sale_date: todayIso(),
    first_due_date: '',
});

const addJalaliMonths = ({ jy, jm, jd }, count) => {
    const zeroBasedMonth = jm - 1 + count;
    const jyNext = jy + Math.floor(zeroBasedMonth / 12);
    const jmNext = ((zeroBasedMonth % 12) + 12) % 12 + 1;
    const jdNext = Math.min(jd, jalaaliMonthLength(jyNext, jmNext));

    return { jy: jyNext, jm: jmNext, jd: jdNext };
};

const setDefaultDueDate = () => {
    const match = String(form.value.sale_date || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) return;
    const jalali = addJalaliMonths(toJalaali(Number(match[1]), Number(match[2]), Number(match[3])), 1);
    const gregorian = toGregorian(jalali.jy, jalali.jm, jalali.jd);
    form.value.first_due_date = `${gregorian.gy}-${String(gregorian.gm).padStart(2, '0')}-${String(gregorian.gd).padStart(2, '0')}`;
};

setDefaultDueDate();
watch(() => form.value.sale_date, setDefaultDueDate);

const moneyField = (key, event) => {
    form.value[key] = formatPriceInput(event.target.value);
};
const decimalField = (key, event) => {
    form.value[key] = formatDecimalInput(event.target.value);
};
const integerField = (key, event) => {
    form.value[key] = formatIntegerInput(event.target.value);
};

const fieldError = (name) => errors.value[name]?.[0];

const submit = async () => {
    loading.value = true;
    generalError.value = '';
    errors.value = {};
    result.value = null;

    try {
        const data = await postJson(route('features.gold-collateral.calculate'), {
            sale_price: parsePrice(form.value.sale_price),
            down_payment: parsePrice(form.value.down_payment),
            monthly_profit_rate: parseDecimal(form.value.monthly_profit_rate),
            installment_count: parseInteger(form.value.installment_count),
            gold_rate_per_gram: parsePrice(form.value.gold_rate_per_gram),
            sale_date: form.value.sale_date,
            first_due_date: form.value.first_due_date,
        });
        result.value = data.result;
    } catch (error) {
        errors.value = error.payload?.errors || {};
        generalError.value = error.message;
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head title="ضمانت طلا | automaya" />

    <FeaturesLayout title="ضمانت طلا" subtitle="پوشش دو ماه سود">
        <div class="relative mb-5 overflow-hidden rounded-[28px]">
            <img :src="showroomPhoto(8)" alt="" class="h-36 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div class="absolute bottom-4 right-4 text-white">
                <p class="text-[11px] text-white/70">طلای ۱۸ عیار</p>
                <h1 class="text-xl font-black">ضمانت اقساط</h1>
            </div>
        </div>

        <form class="space-y-3" @submit.prevent="submit">
            <input
                class="am-input"
                :value="form.gold_rate_per_gram"
                placeholder="قیمت هر گرم طلا (تومان)"
                @input="moneyField('gold_rate_per_gram', $event)"
            />
            <p v-if="fieldError('gold_rate_per_gram')" class="text-xs text-red-500">{{ fieldError('gold_rate_per_gram') }}</p>
            <input class="am-input" :value="form.sale_price" placeholder="قیمت فروش" @input="moneyField('sale_price', $event)" />
            <input class="am-input" :value="form.down_payment" placeholder="پیش‌پرداخت" @input="moneyField('down_payment', $event)" />
            <input
                class="am-input"
                :value="formatDecimalInput(form.monthly_profit_rate)"
                placeholder="نرخ سود ماهانه ٪"
                inputmode="decimal"
                @input="decimalField('monthly_profit_rate', $event)"
            />
            <input
                class="am-input"
                :value="formatIntegerInput(form.installment_count)"
                placeholder="تعداد اقساط"
                inputmode="numeric"
                @input="integerField('installment_count', $event)"
            />
            <JalaliDateInput v-model="form.sale_date" placeholder="تاریخ فروش" input-class="gold-sale-date" />
            <JalaliDateInput v-model="form.first_due_date" placeholder="اولین سررسید" input-class="gold-due-date" />
            <p v-if="generalError" class="text-sm text-red-500">{{ generalError }}</p>
            <button type="submit" class="am-btn-primary w-full" :disabled="loading">
                {{ loading ? 'در حال محاسبه...' : 'محاسبه وزن طلا' }}
            </button>
        </form>

        <div v-if="result" class="mt-5 grid grid-cols-2 gap-2.5">
            <div class="col-span-2 rounded-[22px] bg-neutral-900 p-5 text-white">
                <p class="text-[11px] text-white/60">وزن لازم</p>
                <p class="mt-1 text-2xl font-black">
                    {{ Number(result.collateral.required_weight).toLocaleString('fa-IR') }} گرم
                </p>
            </div>
            <div class="rounded-[22px] bg-white p-4 dark:bg-[#161618]">
                <p class="text-[10px] text-neutral-400">پوشش ضمانت</p>
                <p class="mt-1 text-sm font-black">{{ formatMoney(result.collateral.coverage_amount) }}</p>
            </div>
            <div class="rounded-[22px] bg-white p-4 dark:bg-[#161618]">
                <p class="text-[10px] text-neutral-400">قسط ماهانه</p>
                <p class="mt-1 text-sm font-black">{{ formatMoney(result.installments.installment_amount) }}</p>
            </div>
            <div class="col-span-2 rounded-[22px] bg-white p-4 dark:bg-[#161618]">
                <p class="text-[10px] text-neutral-400">نرخ واردشده هر گرم</p>
                <p class="mt-1 text-sm font-black">{{ formatMoney(result.gold_rate.rate_per_gram) }}</p>
            </div>
        </div>
    </FeaturesLayout>
</template>
