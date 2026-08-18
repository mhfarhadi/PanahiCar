<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { computed, nextTick, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { colorLabel } from '@/Utils/vehicleLabels';

const props = defineProps({
    catalog: { type: Object, required: true },
    contacts: { type: Array, default: () => [] },
    createdContactId: { type: [Number, String], default: null },
    optionLabels: { type: Object, default: () => ({}) },
});

const selectedBrandId = ref('');
const selectedModelId = ref('');

const filteredModels = computed(() =>
    props.catalog.models.filter(
        (model) => String(model.brand_id) === String(selectedBrandId.value)
    )
);

const filteredColors = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelColors
        .filter(
            (item) => String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.color_option_id));

    return props.catalog.colors.filter((color) =>
        allowedIds.includes(String(color.id))
    );
});

const now = new Date();
const localDate = [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, '0'),
    String(now.getDate()).padStart(2, '0'),
].join('-');

const form = useForm({
    brand: '',
    model: '',
    model_year: '',
    mileage: '',
    color: '',
    transmission: '',
    fuel_type: '',
    body_condition: '',
    insurance_months: '',
    vin: '',
    description: '',
    announcer_id: '',
    announced_price: '',
    announced_at: localDate,
    images: [],
});

watch(selectedBrandId, () => {
    selectedModelId.value = '';
    form.brand = '';
    form.model = '';
    form.color = '';
});

watch(selectedModelId, (value) => {
    form.color = '';
    const model = props.catalog.models.find(
        (item) => String(item.id) === String(value)
    );
    form.model = model?.name ?? '';
});

const selectBrand = () => {
    const brand = props.catalog.brands.find(
        (item) => String(item.id) === String(selectedBrandId.value)
    );
    form.brand = brand?.name ?? '';
};

const DRAFT_KEY = 'automaya_announced_create_draft';

const restoreDraft = async () => {
    const raw = sessionStorage.getItem(DRAFT_KEY);

    if (raw) {
        try {
            const draft = JSON.parse(raw);
            selectedBrandId.value = draft.selectedBrandId ?? '';
            await nextTick();
            selectedModelId.value = draft.selectedModelId ?? '';
            await nextTick();
            Object.entries(draft.form ?? {}).forEach(([key, value]) => {
                if (key in form && key !== 'images') {
                    form[key] = value;
                }
            });
        } catch (error) {
            console.error('Could not restore announced device draft.', error);
        }
        sessionStorage.removeItem(DRAFT_KEY);
    }

    if (props.createdContactId) {
        form.announcer_id = props.createdContactId;
    }
};

const openCreateContact = () => {
    sessionStorage.setItem(
        DRAFT_KEY,
        JSON.stringify({
            form: { ...form.data(), images: [] },
            selectedBrandId: selectedBrandId.value,
            selectedModelId: selectedModelId.value,
        })
    );

    router.visit(
        route('contacts.create', {
            return_to: 'announced-devices.create',
            contact_type: 'colleague',
        })
    );
};

restoreDraft();

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const formatPrice = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');
    if (!digits) return '';
    return Number(digits).toLocaleString('fa-IR');
};

const handleAnnouncedPrice = (event) => {
    const digits = normalizeDigits(event.target.value).replace(/\D/g, '');
    form.announced_price = digits;
    event.target.value = formatPrice(digits);
};

const handleImages = (event) => {
    form.images = Array.from(event.target.files);
};

const selectedAnnouncer = computed(() =>
    props.contacts.find(
        (contact) => String(contact.id) === String(form.announcer_id)
    )
);

const submit = () => {
    form.post(route('announced-devices.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="ثبت خودرو اعلامی | automaya" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page">
            <div class="am-page-inner-narrow">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="am-kicker">automaya</p>
                        <h1 class="am-title">ثبت خودرو اعلامی</h1>
                        <p class="am-subtitle">
                            مشخصات خودرو و اطلاعات اعلام‌کننده را ثبت کنید.
                        </p>
                    </div>
                    <Link :href="route('announced-devices.index')" class="am-btn-secondary shrink-0">
                        بازگشت
                    </Link>
                </div>

                <form class="space-y-6" @submit.prevent="submit">
                    <section class="am-card sm:!p-7">
                        <div class="mb-6">
                            <h2 class="text-lg font-black">مشخصات خودرو</h2>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <InputLabel value="برند *" class="mb-2 font-bold" />
                                <select v-model="selectedBrandId" class="am-input" @change="selectBrand">
                                    <option value="">انتخاب برند</option>
                                    <option v-for="brand in catalog.brands" :key="brand.id" :value="brand.id">
                                        {{ brand.name }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.brand" />
                            </div>

                            <div>
                                <InputLabel value="مدل *" class="mb-2 font-bold" />
                                <select
                                    v-model="selectedModelId"
                                    :disabled="!selectedBrandId"
                                    class="am-input disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">انتخاب مدل</option>
                                    <option v-for="model in filteredModels" :key="model.id" :value="model.id">
                                        {{ model.name }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.model" />
                            </div>

                            <div>
                                <InputLabel value="سال مدل *" class="mb-2 font-bold" />
                                <TextInput
                                    :model-value="String(form.model_year ?? '')"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۱۴۰۲"
                                    class="am-input border-0 shadow-none focus:ring-2"
                                    @update:model-value="form.model_year = normalizeDigits($event).replace(/\D/g, '').slice(0, 4)"
                                />
                                <InputError class="mt-1" :message="form.errors.model_year" />
                            </div>

                            <div>
                                <InputLabel value="کارکرد (کیلومتر) *" class="mb-2 font-bold" />
                                <TextInput
                                    :model-value="String(form.mileage ?? '')"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۴۵۰۰۰"
                                    class="am-input border-0 shadow-none focus:ring-2"
                                    @update:model-value="form.mileage = normalizeDigits($event).replace(/\D/g, '')"
                                />
                                <InputError class="mt-1" :message="form.errors.mileage" />
                            </div>

                            <div>
                                <InputLabel value="رنگ" class="mb-2 font-bold" />
                                <select
                                    v-model="form.color"
                                    :disabled="!selectedModelId"
                                    class="am-input disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">انتخاب رنگ</option>
                                    <option v-for="color in filteredColors" :key="color.id" :value="color.name">
                                        {{ colorLabel(color.name) }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.color" />
                            </div>

                            <div>
                                <InputLabel value="گیربکس *" class="mb-2 font-bold" />
                                <select v-model="form.transmission" class="am-input">
                                    <option value="">انتخاب کنید</option>
                                    <option
                                        v-for="(label, value) in optionLabels.transmissions"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.transmission" />
                            </div>

                            <div>
                                <InputLabel value="نوع سوخت *" class="mb-2 font-bold" />
                                <select v-model="form.fuel_type" class="am-input">
                                    <option value="">انتخاب کنید</option>
                                    <option
                                        v-for="(label, value) in optionLabels.fuelTypes"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.fuel_type" />
                            </div>

                            <div>
                                <InputLabel value="وضعیت بدنه *" class="mb-2 font-bold" />
                                <select v-model="form.body_condition" class="am-input">
                                    <option value="">انتخاب کنید</option>
                                    <option
                                        v-for="(label, value) in optionLabels.bodyConditions"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError class="mt-1" :message="form.errors.body_condition" />
                            </div>

                            <div>
                                <InputLabel value="بیمه شخص ثالث (ماه)" class="mb-2 font-bold" />
                                <TextInput
                                    :model-value="String(form.insurance_months ?? '')"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۶"
                                    class="am-input border-0 shadow-none focus:ring-2"
                                    @update:model-value="form.insurance_months = normalizeDigits($event).replace(/\D/g, '').slice(0, 2)"
                                />
                                <InputError class="mt-1" :message="form.errors.insurance_months" />
                            </div>

                            <div>
                                <InputLabel value="VIN" class="mb-2 font-bold" />
                                <TextInput
                                    v-model="form.vin"
                                    type="text"
                                    placeholder="شماره شاسی (اختیاری)"
                                    dir="ltr"
                                    class="am-input border-0 text-left shadow-none focus:ring-2"
                                />
                                <InputError class="mt-1" :message="form.errors.vin" />
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel value="توضیحات" class="mb-2 font-bold" />
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="توضیحات تکمیلی..."
                                    class="am-input"
                                ></textarea>
                                <InputError class="mt-1" :message="form.errors.description" />
                            </div>
                        </div>
                    </section>

                    <section class="am-card sm:!p-7">
                        <h2 class="text-lg font-black">اطلاعات اعلام</h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <InputLabel value="اعلام‌کننده (همکار) *" class="mb-2 font-bold" />
                                <select v-model="form.announcer_id" class="am-input">
                                    <option value="">انتخاب همکار</option>
                                    <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                                        {{ contact.name }} — {{ contact.mobile }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.announcer_id" />

                                <div v-if="selectedAnnouncer" class="am-accent-soft mt-3 rounded-2xl p-4">
                                    <p class="font-black">{{ selectedAnnouncer.name }}</p>
                                    <p class="mt-1 text-sm text-slate-500" dir="ltr">
                                        {{ selectedAnnouncer.mobile }}
                                    </p>
                                </div>

                                <div class="mt-3 text-sm">
                                    همکار در لیست نیست؟
                                    <button type="button" class="font-bold am-accent" @click="openCreateContact">
                                        افزودن همکار جدید
                                    </button>
                                </div>
                            </div>

                            <div>
                                <InputLabel value="قیمت اعلامی" class="mb-2 font-bold" />
                                <input
                                    :value="formatPrice(form.announced_price)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۸۵۰,۰۰۰,۰۰۰"
                                    class="am-input"
                                    @input="handleAnnouncedPrice"
                                />
                                <InputError class="mt-1" :message="form.errors.announced_price" />
                            </div>

                            <div>
                                <InputLabel value="تاریخ اعلام *" class="mb-2 font-bold" />
                                <Vue3PersianDatetimePicker
                                    v-model="form.announced_at"
                                    format="YYYY-MM-DD"
                                    display-format="jYYYY/jMM/jDD"
                                    convert-numbers
                                    :editable="false"
                                    :auto-submit="true"
                                    color="#2563eb"
                                    input-class="am-input text-right"
                                    placeholder="انتخاب تاریخ"
                                />
                                <InputError class="mt-1" :message="form.errors.announced_at" />
                            </div>
                        </div>
                    </section>

                    <section class="am-card sm:!p-7">
                        <h2 class="text-lg font-black">تصاویر خودرو</h2>
                        <label
                            class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-[24px] border-2 border-dashed border-blue-200 bg-blue-50/60 px-4 py-10 text-center transition hover:border-blue-400 dark:border-blue-500/20 dark:bg-blue-500/[0.06]"
                        >
                            <span class="text-3xl">＋</span>
                            <span class="mt-2 font-black">انتخاب تصاویر</span>
                            <input type="file" multiple accept="image/*" class="hidden" @change="handleImages" />
                        </label>
                        <p v-if="form.images.length" class="mt-3 text-sm font-bold am-accent">
                            {{ form.images.length.toLocaleString('fa-IR') }} تصویر انتخاب شده
                        </p>
                        <InputError class="mt-2" :message="form.errors.images" />
                    </section>

                    <div class="flex justify-end">
                        <PrimaryButton class="am-btn-primary !px-8" :disabled="form.processing">
                            ثبت خودرو اعلامی
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
