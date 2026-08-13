<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, nextTick, ref, watch } from 'vue';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    colorLabel,
    registrationStatusLabel,
    samsungBatteryConditionOptions,
    manufacturingCountryOptions,
} from '@/Utils/deviceLabels';

const props = defineProps({
    catalog: {
        type: Object,
        required: true,
    },
    contacts: {
        type: Array,
        default: () => [],
    },
    createdContactId: {
        type: [Number, String],
        default: null,
    },
});

const selectedBrandId = defineModel('selectedBrandId', {
    default: '',
});

const selectedModelId = defineModel('selectedModelId', {
    default: '',
});

const selectedBrand = computed(() =>
    props.catalog.brands.find(
        (item) => String(item.id) === String(selectedBrandId.value)
    )
);

const isSamsung = computed(
    () => selectedBrand.value?.name === 'Samsung'
);

const filteredModels = computed(() =>
    props.catalog.models.filter(
        (model) => String(model.brand_id) === String(selectedBrandId.value)
    )
);

const filteredStorages = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelStorages
        .filter(
            (item) =>
                String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.storage_option_id));

    return props.catalog.storages.filter((storage) =>
        allowedIds.includes(String(storage.id))
    );
});

const filteredColors = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelColors
        .filter(
            (item) =>
                String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.color_option_id));

    return props.catalog.colors.filter((color) =>
        allowedIds.includes(String(color.id))
    );
});

const filteredPartNumbers = computed(() => {
    if (!selectedModelId.value) return [];

    const allowedIds = props.catalog.modelPartNumbers
        .filter(
            (item) =>
                String(item.device_model_id) === String(selectedModelId.value)
        )
        .map((item) => String(item.part_number_option_id));

    return props.catalog.partNumbers.filter((part) =>
        allowedIds.includes(String(part.id))
    );
});

watch(selectedBrandId, () => {
    selectedModelId.value = '';
    form.brand = '';
    form.model = '';
    form.storage = '';
    form.color = '';
    form.part_number = '';
    form.manufacturing_country = '';
    form.battery_health = '';
    form.battery_condition = '';
});

watch(selectedModelId, (value) => {
    form.storage = '';
    form.color = '';

    const model = props.catalog.models.find(
        (item) => String(item.id) === String(value)
    );

    form.model = model?.name ?? '';
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
    storage: '',
    color: '',
    part_number: '',
    manufacturing_country: '',
    sim_type: '',
    battery_health: '',
    battery_condition: '',
    condition_grade: '',
    imei: '',
    registration_status: '',
    description: '',

    seller_id: '',

    purchase_price: '',
    purchase_date: localDate,

    images: [],
});

const sellerSearch = ref('');

const filteredContacts = computed(() => {
    const normalize = (value) =>
        String(value ?? '')
            .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
            .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))
            .toLowerCase();

    const query = normalize(sellerSearch.value).trim();

    let results = !query
        ? [...props.contacts]
        : props.contacts.filter((contact) =>
              normalize(`${contact.name} ${contact.mobile ?? ''}`).includes(query)
          );

    const selected = props.contacts.find(
        (contact) => String(contact.id) === String(form.seller_id)
    );

    if (selected && !results.some((contact) => contact.id === selected.id)) {
        results.unshift(selected);
    }

    return results;
});

const selectedSeller = computed(() =>
    props.contacts.find(
        (contact) => String(contact.id) === String(form.seller_id)
    )
);

const DRAFT_KEY = 'maya_device_create_draft';

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

            sellerSearch.value = draft.sellerSearch ?? '';
        } catch (error) {
            console.error('Could not restore device draft.', error);
        }

        sessionStorage.removeItem(DRAFT_KEY);
    }

    if (props.createdContactId) {
        form.seller_id = props.createdContactId;
    }
};

const openCreateContact = () => {
    const data = form.data();

    sessionStorage.setItem(
        DRAFT_KEY,
        JSON.stringify({
            form: {
                ...data,
                images: [],
            },
            selectedBrandId: selectedBrandId.value,
            selectedModelId: selectedModelId.value,
            sellerSearch: sellerSearch.value,
        })
    );

    router.visit(
        route('contacts.create', {
            return_to: 'devices.create',
        })
    );
};

restoreDraft();

const normalizeDigits = (value) => {
    return String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));
};

const formatPrice = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    if (!digits) return '';

    return Number(digits).toLocaleString('fa-IR');
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

        if (number > 0) {
            parts.push(ones[number]);
        }
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
    if (!form.purchase_price) return '';

    const words = numberToPersianWords(form.purchase_price);

    return words ? `${words} تومان` : '';
});

const handlePurchasePrice = (event) => {
    const digits = normalizeDigits(event.target.value).replace(/\D/g, '');

    form.purchase_price = digits;
    event.target.value = formatPrice(digits);
};

const handleImages = (event) => {
    form.images = Array.from(event.target.files);
};

const selectBrand = () => {
    const brand = props.catalog.brands.find(
        (item) => String(item.id) === String(selectedBrandId.value)
    );

    form.brand = brand?.name ?? '';
};

const submit = () => {
    form.post(route('devices.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="ثبت دستگاه | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mh-page-inner-narrow">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="mh-kicker">
                            MAYA HAMRAH
                        </p>
                        <h1 class="mh-title">
                            ثبت دستگاه جدید
                        </h1>
                        <p class="mh-subtitle">
                            مشخصات گوشی و اطلاعات خرید را ثبت کنید.
                        </p>
                    </div>

                    <Link
                        :href="route('dashboard')"
                        class="mh-secondary"
                    >
                        بازگشت
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Device -->
                    <section
                        class="mh-surface sm:!p-7"
                    >
                        <div class="mb-6">
                            <h2 class="text-lg font-black">
                                مشخصات دستگاه
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                اطلاعات اصلی و وضعیت فنی گوشی
                            </p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    برند *
                                </label>
                                <select
                                    v-model="selectedBrandId"
                                    @change="selectBrand"
                                    class="mh-input"
                                >
                                    <option value="">انتخاب برند</option>
                                    <option
                                        v-for="brand in catalog.brands"
                                        :key="brand.id"
                                        :value="brand.id"
                                    >
                                        {{ brand.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.brand"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.brand }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    مدل *
                                </label>
                                <select
                                    v-model="selectedModelId"
                                    :disabled="!selectedBrandId"
                                    class="mh-input disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">انتخاب مدل</option>
                                    <option
                                        v-for="model in filteredModels"
                                        :key="model.id"
                                        :value="model.id"
                                    >
                                        {{ model.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.model"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.model }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    حافظه
                                </label>
                                <select
                                    v-model="form.storage"
                                    :disabled="!selectedModelId"
                                    class="mh-input disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">انتخاب حافظه</option>
                                    <option
                                        v-for="storage in filteredStorages"
                                        :key="storage.id"
                                        :value="storage.name"
                                    >
                                        {{ storage.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    رنگ
                                </label>
                                <select
                                    v-model="form.color"
                                    :disabled="!selectedModelId"
                                    class="mh-input disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">انتخاب رنگ</option>
                                    <option
                                        v-for="color in filteredColors"
                                        :key="color.id"
                                        :value="color.name"
                                    >
                                        {{ colorLabel(color.name) }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="isSamsung">
                                <label class="mb-2 block text-sm font-bold">
                                    کشور سازنده
                                </label>
                                <select
                                    v-model="form.manufacturing_country"
                                    :disabled="!selectedModelId"
                                    class="mh-input disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">انتخاب کشور سازنده</option>
                                    <option
                                        v-for="country in manufacturingCountryOptions"
                                        :key="country.value"
                                        :value="country.value"
                                    >
                                        {{ country.label }}
                                    </option>
                                </select>
                            </div>

                            <div v-else>
                                <label class="mb-2 block text-sm font-bold">
                                    پارت نامبر
                                </label>
                                <select
                                    v-model="form.part_number"
                                    :disabled="!selectedModelId || !filteredPartNumbers.length"
                                    class="mh-input disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">
                                        {{ filteredPartNumbers.length ? 'انتخاب پارت نامبر' : 'پارت نامبر ندارد' }}
                                    </option>
                                    <option
                                        v-for="part in filteredPartNumbers"
                                        :key="part.id"
                                        :value="part.name"
                                    >
                                        {{ part.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    نوع سیم‌کارت
                                </label>
                                <select
                                    v-model="form.sim_type"
                                    class="mh-input"
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="single">تک‌سیم</option>
                                    <option value="dual">دو‌سیم</option>
                                </select>
                            </div>

                            <div v-if="isSamsung">
                                <label class="mb-2 block text-sm font-bold">
                                    وضعیت باتری
                                </label>
                                <select
                                    v-model="form.battery_condition"
                                    class="mh-input"
                                >
                                    <option value="">انتخاب وضعیت باتری</option>
                                    <option
                                        v-for="option in samsungBatteryConditionOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div v-else>
                                <label class="mb-2 block text-sm font-bold">
                                    سلامت باتری
                                </label>
                                <div class="relative">
                                    <input
                                        :value="form.battery_health"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="مثلاً ۸۹ یا 89"
                                        class="mh-input pl-12"
                                        @input="form.battery_health = normalizeDigits($event.target.value).replace(/\D/g, '').slice(0, 3)"
                                    />
                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"
                                    >
                                        %
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    تمیزی دستگاه
                                </label>
                                <select
                                    v-model="form.condition_grade"
                                    class="mh-input"
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="A+">A+ | در حد نو</option>
                                    <option value="A">A | بسیار تمیز</option>
                                    <option value="B">B | تمیز</option>
                                    <option value="C">C | خط و خش‌دار</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    IMEI
                                </label>
                                <input
                                    :value="form.imei"
                                    @input="form.imei = normalizeDigits($event.target.value).replace(/\D/g, '').slice(0, 15)"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="15"
                                    placeholder="IMEI پانزده رقمی"
                                    dir="ltr"
                                    class="mh-input text-left"
                                />
                                <p
                                    v-if="form.errors.imei"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.imei }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    وضعیت رجیستری
                                </label>
                                <select
                                    v-model="form.registration_status"
                                    class="mh-input"
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="registered">{{ registrationStatusLabel('registered') }}</option>
                                    <option value="unregistered">{{ registrationStatusLabel('unregistered') }}</option>
                                    <option value="unknown">نامشخص</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-bold">
                                توضیحات
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="توضیحات تکمیلی درباره دستگاه..."
                                class="mh-input"
                            ></textarea>
                        </div>
                    </section>

                    <!-- Purchase -->
                    <section
                        class="mh-surface sm:!p-7"
                    >
                        <h2 class="text-lg font-black">
                            اطلاعات خرید
                        </h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                انتخاب فروشنده *
                            </label>

                            <input
                                v-model="sellerSearch"
                                type="text"
                                placeholder="جستجو با نام یا شماره موبایل..."
                                class="mh-input mb-3"
                            />

                            <select
                                v-model="form.seller_id"
                                class="mh-input"
                            >
                                <option value="">انتخاب شخص</option>

                                <option
                                    v-for="contact in filteredContacts"
                                    :key="contact.id"
                                    :value="contact.id"
                                >
                                    {{ contact.name }} — {{ contact.mobile }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.seller_id"
                                class="mt-2 text-xs text-red-500"
                            >
                                {{ form.errors.seller_id }}
                            </p>

                            <div
                                v-if="selectedSeller"
                                class="mh-accent-soft mt-3 rounded-2xl p-4"
                            >
                                <p class="font-black">
                                    {{ selectedSeller.name }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500" dir="ltr">
                                    {{ selectedSeller.mobile }}
                                </p>
                            </div>

                            <div class="mt-3 text-sm">
                                شخص موردنظر در لیست نیست؟
                                <button
                                    type="button"
                                    class="font-bold mh-accent-text"
                                    @click="openCreateContact"
                                >
                                    افزودن شخص جدید
                                </button>
                            </div>
                        </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold">
                                    قیمت خرید *
                                </label>
                                <input
                                    :value="formatPrice(form.purchase_price)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="مثلاً ۱۲۲,۰۰۰,۰۰۰"
                                    @input="handlePurchasePrice"
                                    class="mh-input"
                                />

                                <p
                                    v-if="purchasePriceWords"
                                    class="mt-2 text-sm font-bold leading-6 mh-accent-text"
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
                                    color="#ff6d76"
                                    input-class="mh-input text-right"
                                    placeholder="انتخاب تاریخ خرید"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Images -->
                    <section
                        class="mh-surface sm:!p-7"
                    >
                        <h2 class="text-lg font-black">
                            تصاویر دستگاه
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            حداکثر ۱۰ عکس، هر عکس تا ۵ مگابایت
                        </p>

                        <label
                            class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-[24px] border-2 border-dashed border-[#ffcbd0] bg-[#fff5f5] px-4 py-10 text-center transition hover:border-[#ff9299] dark:border-[#ff6d76]/20 dark:bg-[#ff6d76]/[0.06]"
                        >
                            <span class="text-3xl">＋</span>
                            <span class="mt-2 font-black">
                                انتخاب تصاویر
                            </span>
                            <span class="mt-1 text-xs text-slate-500">
                                تصویر اول به‌عنوان عکس اصلی در نظر گرفته می‌شود
                            </span>

                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                class="hidden"
                                @change="handleImages"
                            />
                        </label>

                        <p
                            v-if="form.images.length"
                            class="mt-3 text-sm font-bold mh-accent-text"
                        >
                            {{ form.images.length }} تصویر انتخاب شده
                        </p>

                        <p
                            v-if="form.errors.images"
                            class="mt-2 text-xs text-red-500"
                        >
                            {{ form.errors.images }}
                        </p>
                    </section>

                    <div
                        class="flex flex-col-reverse gap-3 pb-8 sm:flex-row sm:justify-start"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mh-primary px-8 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ form.processing ? 'در حال ثبت...' : 'ثبت دستگاه' }}
                        </button>

                        <Link
                            :href="route('dashboard')"
                            class="mh-secondary px-8"
                        >
                            انصراف
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
