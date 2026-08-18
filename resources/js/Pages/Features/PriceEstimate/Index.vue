<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import { showroomPhoto } from '@/Utils/carPhotos';
import { formatMoney, formatCount, formatIntegerInput, parseInteger } from '@/Utils/featuresForm';
import { bodyConditionLabel, formatMileage, formatYear } from '@/Utils/vehicleLabels';

const props = defineProps({
    catalog: { type: Object, default: () => ({ brands: [], models: [], bodyConditions: {} }) },
    filters: { type: Object, default: () => ({}) },
    estimate: { type: Object, default: null },
});

const brand = ref(props.filters.brand || '');
const model = ref(props.filters.model || '');
const modelYear = ref(props.filters.model_year || '');
const bodyCondition = ref(props.filters.body_condition || '');

const models = computed(() =>
    (props.catalog.models || []).filter((item) => {
        const selected = (props.catalog.brands || []).find((row) => row.name === brand.value);
        return selected ? Number(item.brand_id) === Number(selected.id) : false;
    })
);

watch(brand, () => {
    if (!models.value.some((item) => item.name === model.value)) {
        model.value = '';
    }
});

const submit = () => {
    router.get(route('features.price-estimates.index'), {
        brand: brand.value,
        model: model.value,
        model_year: parseInteger(modelYear.value) || undefined,
        body_condition: bodyCondition.value || undefined,
    }, { preserveScroll: true });
};
</script>

<template>
    <Head title="برآورد قیمت | automaya" />

    <FeaturesLayout title="برآورد قیمت" subtitle="از موجودی و فروش نمایشگاه">
        <div class="relative mb-5 overflow-hidden rounded-[28px]">
            <img :src="showroomPhoto(2)" alt="" class="h-36 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div class="absolute bottom-4 right-4 text-white">
                <p class="text-[11px] text-white/70">بدون دیوار</p>
                <h1 class="text-xl font-black">برآورد قیمت خودرو</h1>
            </div>
        </div>

        <form class="space-y-3" @submit.prevent="submit">
            <select v-model="brand" class="am-input">
                <option value="">برند</option>
                <option v-for="item in catalog.brands" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <select v-model="model" class="am-input" :disabled="!brand">
                <option value="">مدل</option>
                <option v-for="item in models" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <input
                class="am-input"
                :value="formatIntegerInput(modelYear)"
                placeholder="سال مدل (اختیاری)"
                inputmode="numeric"
                @input="modelYear = formatIntegerInput($event.target.value)"
            />
            <select v-model="bodyCondition" class="am-input">
                <option value="">وضعیت بدنه (اختیاری)</option>
                <option v-for="(label, key) in catalog.bodyConditions" :key="key" :value="key">{{ label }}</option>
            </select>
            <button type="submit" class="am-btn-primary w-full">برآورد کن</button>
        </form>

        <div v-if="estimate" class="mt-5 space-y-3">
            <div v-if="estimate.available" class="rounded-[28px] bg-neutral-900 p-5 text-white">
                <p class="text-[11px] text-white/60">پیشنهاد نمایشگاه</p>
                <p class="mt-1 text-2xl font-black">{{ formatMoney(estimate.suggested_price) }}</p>
                <p class="mt-2 text-xs text-white/70">
                    بازه {{ formatMoney(estimate.low_price) }} تا {{ formatMoney(estimate.high_price) }}
                    · {{ formatCount(estimate.sample_count) }} نمونه
                </p>
            </div>
            <p v-else class="rounded-[22px] bg-white p-4 text-sm text-neutral-500 dark:bg-[#161618]">
                برای این مشخصات هنوز فروش یا موجودی قابل اتکا ثبت نشده.
            </p>

            <div
                v-for="(item, index) in estimate.comparables"
                :key="index"
                class="flex items-center justify-between rounded-[22px] bg-white px-4 py-3 dark:bg-[#161618]"
            >
                <div>
                    <p class="text-sm font-bold">{{ item.source === 'sale' ? 'فروش ثبت‌شده' : 'موجودی' }}</p>
                    <p class="mt-0.5 text-[11px] text-neutral-400">
                        {{ formatYear(item.model_year) }} · {{ formatMileage(item.mileage) }} ·
                        {{ bodyConditionLabel(item.body_condition, catalog.bodyConditions) }}
                    </p>
                </div>
                <p class="text-xs font-black">{{ formatMoney(item.price) }}</p>
            </div>
        </div>
    </FeaturesLayout>
</template>
