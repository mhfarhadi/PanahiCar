<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import JalaliDateInput from '@/Components/JalaliDateInput.vue';
import { showroomPhoto } from '@/Utils/carPhotos';
import {
    formatPriceInput,
    normalizeDigits,
    numberToPersianWords,
    toPersianDigits,
} from '@/Utils/featuresForm';

const banks = [
    'بانک ملی ایران', 'بانک سپه', 'بانک ملت', 'بانک تجارت', 'بانک صادرات ایران',
    'بانک پاسارگاد', 'بانک سامان', 'بانک پارسیان', 'بانک اقتصاد نوین', 'بانک شهر',
];

const check = ref({
    bank_name: 'بانک ملت',
    check_number: '',
    sayad_id: '',
    payee: '',
    amount: '',
    due_date: '',
});

const amountWords = computed(() => {
    const words = numberToPersianWords(check.value.amount);
    return words ? `${words} ریال` : 'مبلغ به حروف';
});

const amountDisplay = computed(() => {
    const digits = normalizeDigits(check.value.amount).replace(/\D/g, '');
    return digits ? Number(digits).toLocaleString('fa-IR') : '';
});

const printCheck = () => window.print();
</script>

<template>
    <Head title="پرینتر چک | automaya" />

    <FeaturesLayout title="پرینتر چک" subtitle="پیش‌نمایش چاپ">
        <div class="relative mb-5 overflow-hidden rounded-[28px] print:hidden">
            <img :src="showroomPhoto(6)" alt="" class="h-36 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div class="absolute bottom-4 right-4 text-white">
                <p class="text-[11px] text-white/70">برگه چک</p>
                <h1 class="text-xl font-black">چاپ اطلاعات چک</h1>
            </div>
        </div>

        <form class="space-y-3 print:hidden" @submit.prevent="printCheck">
            <select v-model="check.bank_name" class="am-input">
                <option v-for="bank in banks" :key="bank" :value="bank">{{ bank }}</option>
            </select>
            <input
                class="am-input"
                :value="check.check_number"
                placeholder="شماره چک"
                @input="check.check_number = normalizeDigits($event.target.value).replace(/\D/g, '').slice(0, 16)"
            />
            <input
                class="am-input"
                :value="check.sayad_id"
                placeholder="شناسه صیاد"
                @input="check.sayad_id = normalizeDigits($event.target.value).replace(/\D/g, '').slice(0, 16)"
            />
            <input v-model="check.payee" class="am-input" placeholder="در وجه" />
            <input
                class="am-input"
                :value="formatPriceInput(check.amount)"
                placeholder="مبلغ (ریال)"
                @input="check.amount = normalizeDigits($event.target.value).replace(/\D/g, '')"
            />
            <JalaliDateInput v-model="check.due_date" placeholder="تاریخ سررسید" input-class="check-due-date" />
            <button type="submit" class="am-btn-primary w-full">پیش‌نمایش و چاپ</button>
        </form>

        <section class="mt-5 overflow-hidden rounded-[28px] border border-[#e4dcc8] bg-[#f6f1e4] p-5 text-neutral-900 shadow-sm print:border-0 print:shadow-none">
            <div class="flex items-start justify-between text-neutral-900">
                <div>
                    <p class="text-xs font-bold text-neutral-600">{{ check.bank_name }}</p>
                    <p class="mt-1 text-sm font-black text-neutral-900">چک فروش خودرو</p>
                </div>
                <p class="text-xs font-bold text-neutral-900" dir="ltr">{{ toPersianDigits(check.check_number) || '—' }}</p>
            </div>
            <div class="mt-6 space-y-3 text-sm text-neutral-900">
                <p>تاریخ: <strong>{{ toPersianDigits(check.due_date) || '—' }}</strong></p>
                <p>در وجه: <strong>{{ check.payee || '—' }}</strong></p>
                <p>مبلغ: <strong>{{ amountDisplay ? `${amountDisplay} ریال` : '—' }}</strong></p>
                <p class="leading-7">{{ amountWords }}</p>
                <p v-if="check.sayad_id" class="text-xs text-neutral-700" dir="ltr">صیاد {{ toPersianDigits(check.sayad_id) }}</p>
            </div>
        </section>
    </FeaturesLayout>
</template>
