<script setup>
import { computed, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import { showroomPhoto } from '@/Utils/carPhotos';
import { formatPriceInput, formatIntegerInput, parseInteger, parsePrice, postJson } from '@/Utils/featuresForm';

const props = defineProps({
    catalog: { type: Object, default: () => ({ brands: [], models: [], colors: [], bodyConditions: {} }) },
});

const loading = ref(false);
const success = ref(null);
const generalError = ref('');
const errors = ref({});

const form = ref({
    requester_name: '',
    requester_mobile: '',
    brand: '',
    model: '',
    model_year: '',
    color: '',
    body_condition: 'pristine',
    max_price: '',
    description: '',
});

const models = computed(() => {
    const selected = (props.catalog.brands || []).find((row) => row.name === form.value.brand);
    if (!selected) return [];
    return (props.catalog.models || []).filter((item) => Number(item.brand_id) === Number(selected.id));
});

watch(() => form.value.brand, () => {
    form.value.model = '';
});

const submit = async () => {
    loading.value = true;
    generalError.value = '';
    errors.value = {};
    success.value = null;

    try {
        const data = await postJson(route('features.wanted.store'), {
            ...form.value,
            max_price: parsePrice(form.value.max_price),
            model_year: parseInteger(form.value.model_year),
        });
        success.value = data;
        form.value.max_price = '';
        form.value.description = '';
    } catch (error) {
        errors.value = error.payload?.errors || {};
        generalError.value = error.message;
    } finally {
        loading.value = false;
    }
};

const fieldError = (name) => errors.value[name]?.[0];
</script>

<template>
    <Head title="چی می‌خوام؟ | Panahi Car" />

    <FeaturesLayout title="چی می‌خوام؟" subtitle="ثبت نیاز خرید">
        <div class="relative mb-5 overflow-hidden rounded-[28px]">
            <img :src="showroomPhoto(3)" alt="" class="h-36 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div class="absolute bottom-4 right-4 text-white">
                <p class="text-[11px] text-white/70">بازار همکاران</p>
                <h1 class="text-xl font-black">خودروی مورد نیاز</h1>
            </div>
        </div>

        <div v-if="success" class="mb-4 rounded-[22px] bg-emerald-50 p-4 text-sm font-bold text-emerald-800">
            درخواست {{ success.request.brand }} {{ success.request.model }} ثبت شد.
        </div>

        <form class="space-y-3" @submit.prevent="submit">
            <input v-model="form.requester_name" class="am-input" placeholder="نام شما" />
            <p v-if="fieldError('requester_name')" class="text-xs text-red-500">{{ fieldError('requester_name') }}</p>
            <input v-model="form.requester_mobile" class="am-input" placeholder="موبایل" dir="ltr" />
            <p v-if="fieldError('requester_mobile')" class="text-xs text-red-500">{{ fieldError('requester_mobile') }}</p>
            <select v-model="form.brand" class="am-input">
                <option value="">برند</option>
                <option v-for="item in catalog.brands" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <select v-model="form.model" class="am-input" :disabled="!form.brand">
                <option value="">مدل</option>
                <option v-for="item in models" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <input
                class="am-input"
                :value="formatIntegerInput(form.model_year)"
                placeholder="سال مدل"
                inputmode="numeric"
                @input="form.model_year = formatIntegerInput($event.target.value)"
            />
            <select v-model="form.color" class="am-input">
                <option value="">رنگ (اختیاری)</option>
                <option v-for="item in catalog.colors" :key="item.id" :value="item.name">{{ item.name }}</option>
            </select>
            <select v-model="form.body_condition" class="am-input">
                <option v-for="(label, key) in catalog.bodyConditions" :key="key" :value="key">{{ label }}</option>
            </select>
            <input
                class="am-input"
                :value="form.max_price"
                placeholder="سقف قیمت خرید"
                @input="form.max_price = formatPriceInput($event.target.value)"
            />
            <textarea v-model="form.description" class="am-input min-h-24" placeholder="توضیحات (اختیاری)" />
            <p v-if="generalError" class="text-sm text-red-500">{{ generalError }}</p>
            <button type="submit" class="am-btn-primary w-full" :disabled="loading">
                {{ loading ? 'در حال ثبت...' : 'ثبت درخواست' }}
            </button>
        </form>
    </FeaturesLayout>
</template>
