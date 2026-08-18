<script setup>
import { computed, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import JalaliDateInput from '@/Components/JalaliDateInput.vue';
import { showroomPhoto } from '@/Utils/carPhotos';
import {
    formatDecimalInput,
    formatIntegerInput,
    formatPriceInput,
    numberToPersianWords,
    parsePrice,
    todayIso,
    toPersianDigits,
} from '@/Utils/featuresForm';

const props = defineProps({
    catalog: { type: Object, default: () => ({ brands: [], models: [], colors: [] }) },
});

const seller = ref({ shop_name: '', name: '', national_id: '', mobile: '', address: '' });
const buyer = ref({ name: '', national_id: '', mobile: '', address: '' });
const vehicle = ref({ brand: '', model: '', model_year: '', color: '', mileage: '', vin: '' });
const sale = ref({
    sale_date: todayIso(),
    sale_price: '',
    down_payment: '',
    guarantee_type: 'check',
});

const models = computed(() => {
    const selected = (props.catalog.brands || []).find((row) => row.name === vehicle.value.brand);
    if (!selected) return [];
    return (props.catalog.models || []).filter((item) => Number(item.brand_id) === Number(selected.id));
});

watch(() => vehicle.value.brand, () => {
    vehicle.value.model = '';
});

const priceWords = computed(() => {
    const words = numberToPersianWords(parsePrice(sale.value.sale_price));
    return words ? `${words} تومان` : '';
});

const printContract = () => window.print();
</script>

<template>
    <Head title="قرارداد فروش | automaya" />

    <FeaturesLayout title="قرارداد" subtitle="قرارداد فروش خودرو">
        <div class="relative mb-5 overflow-hidden rounded-[28px] print:hidden">
            <img :src="showroomPhoto(5)" alt="" class="h-36 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div class="absolute bottom-4 right-4 text-white">
                <p class="text-[11px] text-white/70">چاپ روی A4</p>
                <h1 class="text-xl font-black">قرارداد فروش خودرو</h1>
            </div>
        </div>

        <div class="space-y-3 print:hidden">
            <p class="text-xs font-bold text-neutral-400">فروشنده</p>
            <input v-model="seller.shop_name" class="am-input" placeholder="نام نمایشگاه" />
            <input v-model="seller.name" class="am-input" placeholder="نام فروشنده" />
            <input v-model="seller.national_id" class="am-input" placeholder="کد ملی" />
            <input v-model="seller.mobile" class="am-input" placeholder="موبایل" />
            <input v-model="seller.address" class="am-input" placeholder="نشانی" />

            <p class="pt-2 text-xs font-bold text-neutral-400">خریدار</p>
            <input v-model="buyer.name" class="am-input" placeholder="نام خریدار" />
            <input v-model="buyer.national_id" class="am-input" placeholder="کد ملی" />
            <input v-model="buyer.mobile" class="am-input" placeholder="موبایل" />
            <input v-model="buyer.address" class="am-input" placeholder="نشانی" />

            <p class="pt-2 text-xs font-bold text-neutral-400">خودرو</p>
            <select v-model="vehicle.brand" class="am-input">
                <option value="">برند</option>
                <option v-for="item in catalog.brands" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <select v-model="vehicle.model" class="am-input">
                <option value="">مدل</option>
                <option v-for="item in models" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <input
                class="am-input"
                :value="formatIntegerInput(vehicle.model_year)"
                placeholder="سال مدل"
                inputmode="numeric"
                @input="vehicle.model_year = formatIntegerInput($event.target.value)"
            />
            <select v-model="vehicle.color" class="am-input">
                <option value="">رنگ</option>
                <option v-for="item in catalog.colors" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <input
                class="am-input"
                :value="formatIntegerInput(vehicle.mileage)"
                placeholder="کارکرد (کیلومتر)"
                inputmode="numeric"
                @input="vehicle.mileage = formatIntegerInput($event.target.value)"
            />
            <input v-model="vehicle.vin" class="am-input" placeholder="VIN (اختیاری)" />

            <p class="pt-2 text-xs font-bold text-neutral-400">مالی</p>
            <JalaliDateInput v-model="sale.sale_date" placeholder="تاریخ فروش" input-class="contract-sale-date" />
            <input class="am-input" :value="sale.sale_price" placeholder="قیمت فروش" @input="sale.sale_price = formatPriceInput($event.target.value)" />
            <input class="am-input" :value="sale.down_payment" placeholder="پیش‌پرداخت" @input="sale.down_payment = formatPriceInput($event.target.value)" />
            <select v-model="sale.guarantee_type" class="am-input">
                <option value="check">ضمانت چک</option>
                <option value="gold">ضمانت طلا</option>
            </select>
            <button type="button" class="am-btn-primary w-full" @click="printContract">چاپ قرارداد</button>
        </div>

        <article class="mt-6 hidden rounded-[24px] bg-white p-6 text-sm leading-8 print:block">
            <h2 class="text-center text-lg font-black">قرارداد فروش خودرو</h2>
            <p class="mt-4">
                این قرارداد در تاریخ {{ toPersianDigits(sale.sale_date) }} بین فروشنده
                <strong>{{ seller.name || '—' }}</strong>
                (نمایشگاه {{ seller.shop_name || '—' }}) و خریدار
                <strong>{{ buyer.name || '—' }}</strong>
                منعقد شد.
            </p>
            <p>
                موضوع قرارداد، فروش یک دستگاه {{ vehicle.brand }} {{ vehicle.model }}
                سال {{ vehicle.model_year ? toPersianDigits(vehicle.model_year) : '—' }} به رنگ {{ vehicle.color || '—' }}
                با کارکرد {{ vehicle.mileage ? toPersianDigits(vehicle.mileage) : '—' }} کیلومتر
                <span v-if="vehicle.vin">و شماره شاسی {{ vehicle.vin }}</span>
                است.
            </p>
            <p>
                ثمن معامله {{ sale.sale_price || '—' }} تومان
                <span v-if="priceWords">({{ priceWords }})</span>
                است. پیش‌پرداخت {{ sale.down_payment || 'صفر' }} تومان و ضمانت معامله از نوع
                {{ sale.guarantee_type === 'gold' ? 'طلا' : 'چک' }} می‌باشد.
            </p>
            <div class="mt-10 grid grid-cols-2 gap-8 text-center">
                <div>
                    <p class="font-black">امضای فروشنده</p>
                    <p class="mt-8">{{ seller.name }}</p>
                </div>
                <div>
                    <p class="font-black">امضای خریدار</p>
                    <p class="mt-8">{{ buyer.name }}</p>
                </div>
            </div>
        </article>
    </FeaturesLayout>
</template>
