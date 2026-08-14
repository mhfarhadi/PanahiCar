<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { toJalaali } from 'jalaali-js';
import { colorLabel } from '@/Utils/deviceLabels';


const props = defineProps({
    catalog: {
        type: Object,
        default: () => ({
            brands: [],
            models: [],
            storages: [],
            colors: [],
            modelStorages: [],
            modelColors: [],
        }),
    },
});

const selectedBrandId = ref('');
const selectedModelId = ref('');

const catalogOptionText = (item) =>
    String(item?.name ?? item?.label ?? item?.value ?? '');

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

const guaranteeType = ref('check');

const bankOptions = [
    'بانک ملی ایران',
    'بانک سپه',
    'بانک ملت',
    'بانک تجارت',
    'بانک صادرات ایران',
    'بانک رفاه کارگران',
    'بانک کشاورزی',
    'بانک مسکن',
    'بانک صنعت و معدن',
    'بانک توسعه صادرات ایران',
    'بانک توسعه تعاون',
    'پست بانک ایران',
    'بانک اقتصاد نوین',
    'بانک پارسیان',
    'بانک پاسارگاد',
    'بانک سامان',
    'بانک سینا',
    'بانک شهر',
    'بانک دی',
    'بانک گردشگری',
    'بانک ایران زمین',
    'بانک خاورمیانه',
    'بانک کارآفرین',
    'بانک سرمایه',
    'بانک قرض‌الحسنه مهر ایران',
    'بانک قرض‌الحسنه رسالت',
];

const seller = ref({
    shop_name: '',
    name: '',
    national_id: '',
    mobile: '',
    address: '',
});

const buyer = ref({
    name: '',
    national_id: '',
    mobile: '',
    address: '',
    job: '',
});

const device = ref({
    brand: '',
    model: '',
    storage: '',
    color: '',
    imei: '',
});

watch(selectedBrandId, (value) => {
    const brand = props.catalog.brands.find(
        (item) => String(item.id) === String(value)
    );

    device.value.brand = brand?.name ?? '';
    selectedModelId.value = '';
    device.value.model = '';
    device.value.storage = '';
    device.value.color = '';
});

watch(selectedModelId, (value) => {
    const model = props.catalog.models.find(
        (item) => String(item.id) === String(value)
    );

    device.value.model = model?.name ?? '';
    device.value.storage = '';
    device.value.color = '';
});

const sale = ref({
    sale_date: '',
    sale_price: '',
    down_payment: '',
    monthly_profit_rate: '6.5',
});

const accessories = ref([
    { title: '', description: '' },
]);

const installments = ref([
    {
        due_date: '',
        amount: '',
        bank_name: '',
        check_number: '',
        sayad_id: '',
        account_holder: '',
        sayad_confirmed: false,
    },
]);

const goldItems = ref([
    {
        type: '',
        weight: '',
        karat: '18',
        description: '',
    },
]);

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const formatPriceInput = (value) => {
    const digits = normalizeDigits(value).replace(/\D/g, '');

    return digits ? Number(digits).toLocaleString('fa-IR') : '';
};

const toPersianDigits = (value) =>
    String(value ?? '').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);


const setLocalizedNumberField = (target, field, event, allowDecimal = false) => {
    let value = normalizeDigits(event.target.value);

    if (allowDecimal) {
        value = value
            .replace(/[^0-9.]/g, '')
            .replace(/(\..*)\./g, '$1');
    } else {
        value = value.replace(/\D/g, '');
    }

    target[field] = value;
    event.target.value = toPersianDigits(value);
};

const formatPrintDate = (value) => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) return '....................';

    const jalali = toJalaali(
        Number(match[1]),
        Number(match[2]),
        Number(match[3])
    );

    return toPersianDigits(
        `${jalali.jy}/${String(jalali.jm).padStart(2, '0')}/${String(jalali.jd).padStart(2, '0')}`
    );
};

const setMoneyField = (target, field, event) => {
    target[field] = normalizeDigits(event.target.value).replace(/\D/g, '');
};

const addAccessory = () => {
    accessories.value.push({ title: '', description: '' });
};

const removeAccessory = (index) => {
    if (accessories.value.length === 1) {
        accessories.value[0] = { title: '', description: '' };
        return;
    }

    accessories.value.splice(index, 1);
};

const addInstallment = () => {
    installments.value.push({
        due_date: '',
        amount: '',
        bank_name: '',
        check_number: '',
        sayad_id: '',
        account_holder: '',
        sayad_confirmed: false,
    });
};

const removeInstallment = (index) => {
    if (installments.value.length === 1) return;

    installments.value.splice(index, 1);
};

const addGoldItem = () => {
    goldItems.value.push({
        type: '',
        weight: '',
        karat: '18',
        description: '',
    });
};

const removeGoldItem = (index) => {
    if (goldItems.value.length === 1) return;

    goldItems.value.splice(index, 1);
};

const installmentTotal = computed(() =>
    installments.value.reduce(
        (sum, installment) => sum + Number(installment.amount || 0),
        0
    )
);

const contractTotal = computed(
    () => Number(sale.value.down_payment || 0) + installmentTotal.value
);

const printPreview = () => {
    window.print();
};
</script>

<template>
    <Head title="فرم قرارداد فروش" />

    <div dir="rtl" class="features-page print:bg-white print:p-0">
        <div class="page-shell contract-screen">
            <div class="shell-background"></div>

            <header class="topbar">
                <div class="topbar-left">
                    <Link
                        :href="route('features.index')"
                        class="circle-btn"
                        title="بازگشت به امکانات"
                    >
                        ←
                    </Link>

                    <div class="title-wrap">
                        <strong>فرم قرارداد</strong>
                        <small>Installment Contract</small>
                    </div>
                </div>

                <div class="topbar-right">
                    <span class="avatar-badge">M</span>

                    <button
                        type="button"
                        class="circle-btn print-action"
                        title="چاپ A4"
                        @click="printPreview"
                    >
                        ⎙
                    </button>
                </div>
            </header>

            <main class="main-grid">
                <section class="hero-card">
                    <div class="hero-upper">
                        <small class="hero-kicker">
                            ابزار عمومی مایاهمراه
                        </small>

                        <div class="hero-metric">
                            <strong>قرارداد</strong>
                            <strong>فروش اقساطی</strong>
                        </div>

                        <div class="hero-meta">
                            <div>
                                <span>نوع فروش</span>
                                <strong>اقساطی</strong>
                            </div>

                            <div>
                                <span>ضمانت</span>
                                <strong>چک یا طلا</strong>
                            </div>

                            <div>
                                <span>خروجی</span>
                                <strong>آماده چاپ A4</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="content-grid">
                <div class="space-y-6">
                    <section class="rounded-[30px] border border-white/80 bg-gradient-to-br from-white to-[#ecfaf7] p-5 shadow-[0_16px_45px_rgba(46,112,109,0.08)] sm:p-6">
                        <h2 class="inline-flex items-center rounded-xl bg-white/70 px-3 py-2 text-lg font-black shadow-sm">اطلاعات فروشنده</h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <label class="space-y-2">
                                <span class="text-sm font-bold">نام فروشگاه *</span>
                                <input
                                    v-model="seller.shop_name"
                                    type="text"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">نام و نام خانوادگی *</span>
                                <input
                                    v-model="seller.name"
                                    type="text"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">کد ملی *</span>
                                <input
                                    :value="toPersianDigits(seller.national_id)"
                                    @input="setLocalizedNumberField(seller, 'national_id', $event)"
                                    type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">موبایل *</span>
                                <input
                                    :value="toPersianDigits(seller.mobile)"
                                    @input="setLocalizedNumberField(seller, 'mobile', $event)"
                                    type="text"
                                    inputmode="tel"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2 sm:col-span-2">
                                <span class="text-sm font-bold">آدرس *</span>
                                <textarea
                                    v-model="seller.address"
                                    rows="2"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-[30px] border border-white/80 bg-gradient-to-br from-white to-[#f3efff] p-5 shadow-[0_16px_45px_rgba(93,78,130,0.08)] sm:p-6">
                        <h2 class="inline-flex items-center rounded-xl bg-white/70 px-3 py-2 text-lg font-black shadow-sm">اطلاعات خریدار</h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <label class="space-y-2">
                                <span class="text-sm font-bold">نام و نام خانوادگی *</span>
                                <input
                                    v-model="buyer.name"
                                    type="text"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">کد ملی *</span>
                                <input
                                    :value="toPersianDigits(buyer.national_id)"
                                    @input="setLocalizedNumberField(buyer, 'national_id', $event)"
                                    type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">موبایل *</span>
                                <input
                                    :value="toPersianDigits(buyer.mobile)"
                                    @input="setLocalizedNumberField(buyer, 'mobile', $event)"
                                    type="text"
                                    inputmode="tel"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">شغل *</span>
                                <input
                                    v-model="buyer.job"
                                    type="text"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>

                            <label class="space-y-2 sm:col-span-2">
                                <span class="text-sm font-bold">آدرس *</span>
                                <textarea
                                    v-model="buyer.address"
                                    rows="2"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-[30px] border border-white/80 bg-gradient-to-br from-white to-[#fff4ed] p-5 shadow-[0_16px_45px_rgba(134,92,60,0.07)] sm:p-6">
                        <h2 class="inline-flex items-center rounded-xl bg-white/70 px-3 py-2 text-lg font-black shadow-sm">موبایل فروخته‌شده</h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <label class="space-y-2">
                                <span class="text-sm font-bold">برند *</span>
                                <select
                                    v-model="selectedBrandId"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
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
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">مدل *</span>
                                <select
                                    v-model="selectedModelId"
                                    :disabled="!selectedBrandId"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 focus:border-[#69b9b5] focus:ring-[#69b9b5]/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                >
                                    <option value="">
                                        {{ selectedBrandId ? 'انتخاب مدل' : 'ابتدا برند را انتخاب کنید' }}
                                    </option>
                                    <option
                                        v-for="model in filteredModels"
                                        :key="model.id"
                                        :value="model.id"
                                    >
                                        {{ model.name }}
                                    </option>
                                </select>
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">حافظه</span>
                                <select
                                    v-model="device.storage"
                                    :disabled="!selectedModelId"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 focus:border-[#69b9b5] focus:ring-[#69b9b5]/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                >
                                    <option value="">
                                        {{ selectedModelId ? 'انتخاب حافظه' : 'ابتدا مدل را انتخاب کنید' }}
                                    </option>
                                    <option
                                        v-for="storage in filteredStorages"
                                        :key="storage.id"
                                        :value="catalogOptionText(storage)"
                                    >
                                        {{ catalogOptionText(storage) }}
                                    </option>
                                </select>
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">رنگ</span>
                                <select
                                    v-model="device.color"
                                    :disabled="!selectedModelId"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 focus:border-[#69b9b5] focus:ring-[#69b9b5]/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                >
                                    <option value="">
                                        {{ selectedModelId ? 'انتخاب رنگ' : 'ابتدا مدل را انتخاب کنید' }}
                                    </option>
                                    <option
                                        v-for="color in filteredColors"
                                        :key="color.id"
                                        :value="catalogOptionText(color)"
                                    >
                                        {{ colorLabel(catalogOptionText(color)) }}
                                    </option>
                                </select>
                            </label>

                            <label class="space-y-2 sm:col-span-2">
                                <span class="text-sm font-bold">IMEI *</span>
                                <input
                                    :value="toPersianDigits(device.imei)"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="15"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                    @input="setLocalizedNumberField(device, 'imei', $event)"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-[30px] border border-white/80 bg-gradient-to-br from-white to-[#edf9fb] p-5 shadow-[0_16px_45px_rgba(54,102,116,0.07)] sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="inline-flex items-center rounded-xl bg-white/70 px-3 py-2 text-lg font-black shadow-sm">لوازم جانبی</h2>
                                <p class="mt-1 text-xs text-slate-400">
                                    در صورت وجود، ردیف اضافه کن
                                </p>
                            </div>

                            <button
                                type="button"
                                class="rounded-xl bg-[#e7f5f4] px-3 py-2 text-xs font-black text-[#247b79]"
                                @click="addAccessory"
                            >
                                + افزودن
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div
                                v-for="(item, index) in accessories"
                                :key="index"
                                class="grid gap-3 rounded-2xl border border-white/80 bg-white/65 p-3 shadow-sm sm:grid-cols-[1fr_1.5fr_auto]"
                            >
                                <input
                                    v-model="item.title"
                                    type="text"
                                    placeholder="مثلاً شارژر"
                                    class="rounded-xl border-[#dbe9e8] bg-white/90 focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />

                                <input
                                    v-model="item.description"
                                    type="text"
                                    placeholder="مشخصات"
                                    class="rounded-xl border-[#dbe9e8] bg-white/90 focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                />

                                <button
                                    type="button"
                                    class="rounded-xl px-3 text-sm font-black text-red-500"
                                    @click="removeAccessory(index)"
                                >
                                    حذف
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[30px] border border-white/80 bg-gradient-to-br from-white to-[#f4f0ff] p-5 shadow-[0_16px_45px_rgba(91,75,128,0.07)] sm:p-6">
                        <h2 class="inline-flex items-center rounded-xl bg-white/70 px-3 py-2 text-lg font-black shadow-sm">اطلاعات مالی معامله</h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-3">
                            <label class="space-y-2">
                                <span class="text-sm font-bold">تاریخ فروش *</span>

                                <Vue3PersianDatetimePicker
                                    v-model="sale.sale_date"
                                    format="YYYY-MM-DD"
                                    display-format="jYYYY/jMM/jDD"
                                    type="date"
                                    convert-numbers
                                    auto-submit
                                    custom-input=".contract-sale-date-input"
                                />

                                <div class="contract-date-wrap">
                                    <input
                                        type="text"
                                        class="contract-sale-date-input contract-date-input"
                                        placeholder="انتخاب تاریخ شمسی"
                                        readonly
                                    />
                                    <span class="contract-date-icon">⌄</span>
                                </div>
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">قیمت فروش *</span>
                                <input
                                    :value="formatPriceInput(sale.sale_price)"
                                    type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                    @input="setMoneyField(sale, 'sale_price', $event)"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">پیش‌پرداخت *</span>
                                <input
                                    :value="formatPriceInput(sale.down_payment)"
                                    type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                    @input="setMoneyField(sale, 'down_payment', $event)"
                                />
                            </label>

                            <label class="space-y-2">
                                <span class="text-sm font-bold">نرخ سود ماهانه *</span>
                                <div class="relative">
                                    <input
                                        :value="toPersianDigits(sale.monthly_profit_rate)"
                                    @input="setLocalizedNumberField(sale, 'monthly_profit_rate', $event, true)"
                                        type="text"
                                        inputmode="decimal"
                                        class="w-full rounded-2xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20 pl-10"
                                    />
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-slate-400">
                                        ٪
                                    </span>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section class="rounded-[30px] border border-white/80 bg-gradient-to-br from-white to-[#fff8e9] p-5 shadow-[0_16px_45px_rgba(125,100,43,0.07)] sm:p-6">
                        <h2 class="inline-flex items-center rounded-xl bg-white/70 px-3 py-2 text-lg font-black shadow-sm">نوع ضمانت</h2>

                        <div class="guarantee-selector">
                            <button
                                type="button"
                                class="guarantee-choice"
                                :class="{ 'is-selected is-check': guaranteeType === 'check' }"
                                @click="guaranteeType = 'check'"
                            >
                                <div class="guarantee-choice-top">
                                    <span class="guarantee-mark">✓</span>
                                    <span v-if="guaranteeType === 'check'" class="selected-pill">
                                        انتخاب شده
                                    </span>
                                </div>

                                <strong>چک‌های اقساط</strong>
                                <small>برای هر قسط مشخصات چک و صیاد ثبت می‌شود</small>
                            </button>

                            <button
                                type="button"
                                class="guarantee-choice"
                                :class="{ 'is-selected is-gold': guaranteeType === 'gold' }"
                                @click="guaranteeType = 'gold'"
                            >
                                <div class="guarantee-choice-top">
                                    <span class="guarantee-mark">◆</span>
                                    <span v-if="guaranteeType === 'gold'" class="selected-pill">
                                        انتخاب شده
                                    </span>
                                </div>

                                <strong>ضمانت طلا</strong>
                                <small>اقساط بدون چک و با طلای امانی تضمین می‌شود</small>
                            </button>
                        </div>

                        <div v-if="guaranteeType === 'gold'" class="mt-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-black">مشخصات طلای امانی</h3>
                                    <p class="mt-1 text-xs text-slate-400">
                                        وزن، عیار و نوع طلا الزامی است
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-xl bg-[#fff5d9] px-3 py-2 text-xs font-black text-amber-700"
                                    @click="addGoldItem"
                                >
                                    + طلا
                                </button>
                            </div>

                            <div class="mt-4 space-y-3">
                                <div
                                    v-for="(gold, index) in goldItems"
                                    :key="index"
                                    class="grid gap-3 rounded-2xl bg-[#fffaf0] p-4 sm:grid-cols-2"
                                >
                                    <label class="space-y-2">
                                        <span class="text-xs font-bold">نوع طلا *</span>
                                        <input
                                            v-model="gold.type"
                                            type="text"
                                            placeholder="النگو، گردنبند، گوشواره..."
                                            class="w-full rounded-xl border-amber-100 bg-white"
                                        />
                                    </label>

                                    <label class="space-y-2">
                                        <span class="text-xs font-bold">وزن (گرم) *</span>
                                        <input
                                            :value="toPersianDigits(gold.weight)"
                                    @input="setLocalizedNumberField(gold, 'weight', $event, true)"
                                            type="text"
                                            inputmode="decimal"
                                            class="w-full rounded-xl border-amber-100 bg-white"
                                        />
                                    </label>

                                    <label class="space-y-2">
                                        <span class="text-xs font-bold">عیار *</span>
                                        <input
                                            :value="toPersianDigits(gold.karat)"
                                    @input="setLocalizedNumberField(gold, 'karat', $event)"
                                            type="text"
                                            inputmode="numeric"
                                            class="w-full rounded-xl border-amber-100 bg-white"
                                        />
                                    </label>

                                    <label class="space-y-2">
                                        <span class="text-xs font-bold">توضیحات ظاهری</span>
                                        <input
                                            v-model="gold.description"
                                            type="text"
                                            placeholder="اختیاری"
                                            class="w-full rounded-xl border-amber-100 bg-white"
                                        />
                                    </label>

                                    <button
                                        v-if="goldItems.length > 1"
                                        type="button"
                                        class="text-right text-xs font-black text-red-500 sm:col-span-2"
                                        @click="removeGoldItem(index)"
                                    >
                                        حذف این مورد
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[30px] border border-white/80 bg-gradient-to-br from-white to-[#eaf9f6] p-5 shadow-[0_16px_45px_rgba(49,105,101,0.08)] sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="inline-flex items-center rounded-xl bg-white/70 px-3 py-2 text-lg font-black shadow-sm">برنامه اقساط</h2>
                                <p class="mt-1 text-xs text-slate-400">
                                    در هر دو نوع ضمانت ثبت می‌شود
                                </p>
                            </div>

                            <button
                                type="button"
                                class="rounded-xl bg-[#e7f5f4] px-3 py-2 text-xs font-black text-[#247b79]"
                                @click="addInstallment"
                            >
                                + قسط
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div
                                v-for="(installment, index) in installments"
                                :key="index"
                                class="rounded-2xl border border-white/90 bg-white/60 p-4 shadow-sm backdrop-blur"
                            >
                                <div class="mb-3 flex items-center justify-between">
                                    <strong>قسط {{ toPersianDigits(index + 1) }}</strong>

                                    <button
                                        v-if="installments.length > 1"
                                        type="button"
                                        class="text-xs font-black text-red-500"
                                        @click="removeInstallment(index)"
                                    >
                                        حذف
                                    </button>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="space-y-2">
                                        <span class="text-xs font-bold">تاریخ سررسید *</span>

                                        <Vue3PersianDatetimePicker
                                            v-model="installment.due_date"
                                            format="YYYY-MM-DD"
                                            display-format="jYYYY/jMM/jDD"
                                            type="date"
                                            convert-numbers
                                            auto-submit
                                            :custom-input="`.contract-due-date-${index}`"
                                        />

                                        <div class="contract-date-wrap">
                                            <input
                                                type="text"
                                                :class="[
                                                    'contract-date-input',
                                                    `contract-due-date-${index}`,
                                                ]"
                                                placeholder="انتخاب سررسید شمسی"
                                                readonly
                                            />
                                            <span class="contract-date-icon">⌄</span>
                                        </div>
                                    </label>

                                    <label class="space-y-2">
                                        <span class="text-xs font-bold">مبلغ قسط *</span>
                                        <input
                                            :value="formatPriceInput(installment.amount)"
                                            type="text"
                                            inputmode="numeric"
                                            class="w-full rounded-xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                            @input="setMoneyField(installment, 'amount', $event)"
                                        />
                                    </label>

                                    <template v-if="guaranteeType === 'check'">
                                        <label class="space-y-2">
                                            <span class="text-xs font-bold">بانک *</span>
                                            <select
                                                v-model="installment.bank_name"
                                                class="w-full rounded-xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                            >
                                                <option value="">انتخاب بانک</option>
                                                <option
                                                    v-for="bank in bankOptions"
                                                    :key="bank"
                                                    :value="bank"
                                                >
                                                    {{ bank }}
                                                </option>
                                            </select>
                                        </label>

                                        <label class="space-y-2">
                                            <span class="text-xs font-bold">شماره چک *</span>
                                            <input
                                                :value="toPersianDigits(installment.check_number)"
                                    @input="setLocalizedNumberField(installment, 'check_number', $event)"
                                                type="text"
                                                class="w-full rounded-xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                            />
                                        </label>

                                        <label class="space-y-2">
                                            <span class="text-xs font-bold">شناسه صیادی *</span>
                                            <input
                                                :value="toPersianDigits(installment.sayad_id)"
                                    @input="setLocalizedNumberField(installment, 'sayad_id', $event)"
                                                type="text"
                                                inputmode="numeric"
                                                maxlength="16"
                                                class="w-full rounded-xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                            />
                                        </label>

                                        <label class="space-y-2">
                                            <span class="text-xs font-bold">نام صاحب حساب *</span>
                                            <input
                                                v-model="installment.account_holder"
                                                type="text"
                                                class="w-full rounded-xl border-[#dbe9e8] bg-white/80 shadow-[inset_0_1px_2px_rgba(42,79,80,0.03)] focus:border-[#69b9b5] focus:ring-[#69b9b5]/20"
                                            />
                                        </label>

                                        <label class="flex items-start gap-3 rounded-xl bg-emerald-50 p-3 sm:col-span-2">
                                            <input
                                                v-model="installment.sayad_confirmed"
                                                type="checkbox"
                                                class="mt-1 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"
                                            />
                                            <span class="text-xs font-bold leading-6 text-emerald-800">
                                                تأیید می‌کنم این چک در سامانه صیاد به نام فروشنده ثبت و توسط فروشنده تأیید شده است.
                                            </span>
                                        </label>
                                    </template>



                                </div>
                            </div>
                        </div>
                    </section>

                </div>

                <aside class="xl:sticky xl:top-5 xl:self-start">
                    <div
                        class="overflow-hidden rounded-[32px] border border-white/90 bg-gradient-to-br from-white via-[#f4fbfa] to-[#f1ecff] p-5 shadow-[0_24px_70px_rgba(51,88,93,0.14)] sm:p-6"
                    >
                        <p class="text-xs font-black text-[#4da9a7]">
                            پیش‌نمایش اولیه
                        </p>

                        <h2 class="mt-2 text-xl font-black">
                            قرارداد فروش اقساطی موبایل
                        </h2>

                        <div class="mt-5 space-y-3 text-sm">
                            <div class="rounded-2xl border border-white/80 bg-white/70 p-4 shadow-sm backdrop-blur">
                                <span class="text-slate-400">فروشنده</span>
                                <p class="mt-1 font-black">
                                    {{ seller.shop_name || seller.name || '—' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/80 bg-white/70 p-4 shadow-sm backdrop-blur">
                                <span class="text-slate-400">خریدار</span>
                                <p class="mt-1 font-black">
                                    {{ buyer.name || '—' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/80 bg-white/70 p-4 shadow-sm backdrop-blur">
                                <span class="text-slate-400">کالا</span>
                                <p class="mt-1 font-black">
                                    {{ [device.brand, device.model, device.storage].filter(Boolean).join(' ') || '—' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-[#d9f0ec] bg-gradient-to-br from-[#e8f8f5] to-white p-4 shadow-sm">
                                    <span class="text-xs text-slate-500">
                                        جمع اقساط
                                    </span>
                                    <p class="mt-1 font-black">
                                        {{ formatPriceInput(installmentTotal) }}
                                        تومان
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-[#d9f0ec] bg-gradient-to-br from-[#e8f8f5] to-white p-4 shadow-sm">
                                    <span class="text-xs text-slate-500">
                                        جمع قرارداد
                                    </span>
                                    <p class="mt-1 font-black">
                                        {{ formatPriceInput(contractTotal) }}
                                        تومان
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-[#f4e8bf] bg-gradient-to-br from-[#fff7df] to-white p-4 shadow-sm">
                                <span class="text-slate-400">ضمانت</span>
                                <p class="mt-1 font-black">
                                    {{
                                        guaranteeType === 'check'
                                            ? 'چک‌های اقساط'
                                            : 'طلای امانی'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-slate-100 p-4">
                            <p class="font-black">شرایط اصلی قرارداد</p>
                            <ul class="mt-3 space-y-2 text-xs leading-6 text-slate-500">
                                <li>
                                    • وجه التزام تأخیر هر قسط از فردای سررسید همان قسط و فقط روی مبلغ همان قسط محاسبه می‌شود.
                                </li>
                                <li>
                                    • فرمول: مبلغ قسط × نرخ ماهانه {{ toPersianDigits(sale.monthly_profit_rate) || '—' }}٪ × روزهای تأخیر ÷ ۳۰.
                                </li>
                                <li>
                                    • فروشنده تا ۱۰ روز پس از سررسید برای پرداخت منتظر می‌ماند؛ خسارت این مدت نیز از فردای سررسید محاسبه می‌شود.
                                </li>
                                <li>
                                    • اگر موعد قسط بعدی برسد و قسط قبلی تسویه نشده باشد، کل مانده بدهی یکجا قابل مطالبه است.
                                </li>
                                <li>
                                    • خسارت هر قسط، حتی پس از حال‌شدن کل بدهی، فقط از سررسید اصلی همان قسط محاسبه می‌شود.
                                </li>
                            </ul>
                        </div>

                    </div>
                </aside>
                </div>
            </main>
        </div>

        <div class="contract-print">
            <article class="print-contract">
                <header class="print-header">
                    <div class="print-header-rule"></div>

                    <h1>قرارداد فروش اقساطی تلفن همراه</h1>

                    <div class="print-header-meta">
                        <span>
                            تاریخ قرارداد:
                            <strong>{{ formatPrintDate(sale.sale_date) }}</strong>
                        </span>

                        <span>
                            نوع تضمین:
                            <strong>
                                {{ guaranteeType === 'check' ? 'چک‌های اقساط' : 'طلای امانی' }}
                            </strong>
                        </span>
                    </div>
                </header>

                <section class="print-section">
                    <h2>۱. مشخصات طرفین</h2>

                    <div class="print-party-grid">
                        <div class="print-party">
                            <h3>فروشنده</h3>

                            <dl>
                                <div>
                                    <dt>نام و نام خانوادگی</dt>
                                    <dd>{{ seller.name || '....................' }}</dd>
                                </div>

                                <div>
                                    <dt>نام فروشگاه</dt>
                                    <dd>{{ seller.shop_name || '....................' }}</dd>
                                </div>

                                <div>
                                    <dt>کد ملی</dt>
                                    <dd>{{ toPersianDigits(seller.national_id) || '....................' }}</dd>
                                </div>

                                <div>
                                    <dt>موبایل</dt>
                                    <dd>{{ toPersianDigits(seller.mobile) || '....................' }}</dd>
                                </div>

                                <div class="full">
                                    <dt>نشانی</dt>
                                    <dd>{{ seller.address || '................................................................' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="print-party">
                            <h3>خریدار</h3>

                            <dl>
                                <div>
                                    <dt>نام و نام خانوادگی</dt>
                                    <dd>{{ buyer.name || '....................' }}</dd>
                                </div>

                                <div>
                                    <dt>کد ملی</dt>
                                    <dd>{{ toPersianDigits(buyer.national_id) || '....................' }}</dd>
                                </div>

                                <div>
                                    <dt>موبایل</dt>
                                    <dd>{{ toPersianDigits(buyer.mobile) || '....................' }}</dd>
                                </div>

                                <div>
                                    <dt>شغل</dt>
                                    <dd>{{ buyer.job || '....................' }}</dd>
                                </div>

                                <div class="full">
                                    <dt>نشانی</dt>
                                    <dd>{{ buyer.address || '................................................................' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section class="print-section">
                    <h2>۲. موضوع قرارداد و مشخصات کالا</h2>

                    <table class="print-table print-device-table">
                        <tbody>
                            <tr>
                                <th>برند</th>
                                <td>{{ device.brand || '—' }}</td>

                                <th>مدل</th>
                                <td>{{ device.model || '—' }}</td>

                                <th>حافظه</th>
                                <td>{{ device.storage || '—' }}</td>
                            </tr>

                            <tr>
                                <th>رنگ</th>
                                <td>{{ device.color ? colorLabel(device.color) : '—' }}</td>

                                <th>IMEI</th>
                                <td colspan="3" dir="ltr">
                                    {{ toPersianDigits(device.imei) || '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        v-if="accessories.some(item => item.title)"
                        class="print-accessories"
                    >
                        <strong>لوازم جانبی:</strong>

                        <span
                            v-for="(item, index) in accessories.filter(item => item.title)"
                            :key="`print-accessory-${index}`"
                        >
                            {{ item.title }}
                            <template v-if="item.description">
                                ({{ item.description }})
                            </template>
                            <template
                                v-if="index < accessories.filter(item => item.title).length - 1"
                            >
                                ،
                            </template>
                        </span>
                    </div>
                </section>

                <section class="print-section">
                    <h2>۳. مبلغ قرارداد و برنامه پرداخت</h2>

                    <div class="print-finance-grid">
                        <div>
                            <span>قیمت فروش</span>
                            <strong>
                                {{ formatPriceInput(sale.sale_price) || '—' }}
                                تومان
                            </strong>
                        </div>

                        <div>
                            <span>پیش‌پرداخت</span>
                            <strong>
                                {{ formatPriceInput(sale.down_payment) || '—' }}
                                تومان
                            </strong>
                        </div>

                        <div>
                            <span>نرخ سود ماهانه</span>
                            <strong>{{ toPersianDigits(sale.monthly_profit_rate) || '—' }}٪</strong>
                        </div>

                        <div>
                            <span>جمع اقساط</span>
                            <strong>
                                {{ formatPriceInput(installmentTotal) || '—' }}
                                تومان
                            </strong>
                        </div>
                    </div>

                    <table class="print-table print-installment-table">
                        <thead>
                            <tr>
                                <th>ردیف</th>
                                <th>سررسید</th>
                                <th>مبلغ قسط (تومان)</th>

                                <template v-if="guaranteeType === 'check'">
                                    <th>بانک</th>
                                    <th>شماره چک</th>
                                    <th>شناسه صیادی</th>
                                </template>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(installment, index) in installments"
                                :key="`print-installment-${index}`"
                            >
                                <td>{{ toPersianDigits(index + 1) }}</td>
                                <td>{{ formatPrintDate(installment.due_date) }}</td>
                                <td>{{ formatPriceInput(installment.amount) || '—' }}</td>

                                <template v-if="guaranteeType === 'check'">
                                    <td>{{ installment.bank_name || '—' }}</td>
                                    <td>{{ toPersianDigits(installment.check_number) || '—' }}</td>
                                    <td dir="ltr">{{ toPersianDigits(installment.sayad_id) || '—' }}</td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section
                    v-if="guaranteeType === 'gold'"
                    class="print-section print-keep-together"
                >
                    <h2>۴. مشخصات طلای موضوع تضمین</h2>

                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>ردیف</th>
                                <th>نوع طلا</th>
                                <th>وزن (گرم)</th>
                                <th>عیار</th>
                                <th>توضیحات ظاهری</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(gold, index) in goldItems"
                                :key="`print-gold-${index}`"
                            >
                                <td>{{ toPersianDigits(index + 1) }}</td>
                                <td>{{ gold.type || '—' }}</td>
                                <td>{{ toPersianDigits(gold.weight) || '—' }}</td>
                                <td>{{ toPersianDigits(gold.karat) || '—' }}</td>
                                <td>{{ gold.description || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="print-note">
                        خریدار اقرار می‌نماید طلای فوق متعلق به شخص وی بوده و
                        نسبت به آن هیچ حق، مالکیت، وثیقه یا ادعای شخص ثالثی
                        وجود ندارد.
                    </p>
                </section>

                <section class="print-section print-terms">
                    <h2>
                        {{ guaranteeType === 'gold' ? '۵' : '۴' }}.
                        شرایط و تعهدات طرفین
                    </h2>

                    <ol>
                        <li>
                            خریدار اقرار می‌کند کالا و لوازم مندرج در این
                            قرارداد را با مشخصات فوق تحویل گرفته است.
                        </li>

                        <li>
                            در صورت عدم پرداخت هر قسط در سررسید، از روز بعد
                            از همان سررسید تا زمان تسویه، وجه التزام روزانه
                            فقط بر مبنای مبلغ همان قسط و با فرمول «مبلغ قسط ×
                            نرخ سود ماهانه {{ toPersianDigits(sale.monthly_profit_rate) || '—' }}٪ ÷ ۳۰»
                            محاسبه می‌شود.
                        </li>

                        <li>
                            مهلت ده‌روزه فروشنده برای پیگیری و انتظار قبل از
                            اقدام قانونی، مانع محاسبه وجه التزام از فردای
                            سررسید نخواهد بود.
                        </li>

                        <li>
                            اگر موعد قسط بعدی فرا برسد و قسط قبلی همچنان
                            تسویه نشده باشد، تمام مانده بدهی خریدار حال و
                            یکجا قابل مطالبه است؛ با این حال خسارت هر قسط فقط
                            از تاریخ سررسید اصلی همان قسط محاسبه خواهد شد.
                        </li>

                        <li>
                            انتقال مالکیت دستگاه در سامانه همتا تا تسویه کامل
                            آخرین تعهد مالی انجام نمی‌شود. در صورت تحقق تخلف
                            پرداخت و عدم رفع آن، فروشنده حق دارد مطابق مفاد
                            این قرارداد و مقررات قانونی حق فسخ و استرداد
                            دستگاه را مطالبه نماید.
                        </li>

                        <li>
                            در صورت استرداد دستگاه پس از اعمال حق فسخ، مبالغ
                            پرداخت‌شده پس از کسر هزینه استفاده، افت قیمت،
                            خسارت‌های واردشده و هزینه‌های واقعی و قابل اثبات
                            بین طرفین تسویه خواهد شد.
                        </li>

                        <li v-if="guaranteeType === 'check'">
                            چک‌های مندرج در جدول فوق، ابزار پرداخت همین
                            قرارداد هستند و چک ضمانت جداگانه‌ای دریافت نشده
                            است. خریدار تأیید می‌کند چک‌ها در سامانه صیاد به
                            نام فروشنده ثبت شده‌اند.
                        </li>

                        <li v-else>
                            طلای موضوع تضمین تا تسویه کامل آخرین قسط نزد
                            فروشنده نگهداری می‌شود. چنانچه موعد قسط بعدی
                            فرا برسد و قسط قبلی تسویه نشده باشد، خریدار به
                            فروشنده اختیار می‌دهد در حدود قوانین برای وصول
                            مطالبات نسبت به فروش طلای تضمین اقدام نماید؛
                            مازاد حاصل از فروش به خریدار مسترد و کسری احتمالی
                            همچنان بر عهده خریدار خواهد بود.
                        </li>
                    </ol>
                </section>

                <section class="print-acceptance print-keep-together">
                    <p>
                        این قرارداد پس از مطالعه کامل، با اراده و رضایت
                        طرفین تنظیم و امضا گردید و طرفین صحت اطلاعات و قبول
                        مفاد آن را تأیید می‌نمایند.
                    </p>

                    <div class="print-signatures">
                        <div>
                            <strong>فروشنده</strong>
                            <span>نام و امضا</span>

                            <div class="signature-line">
                                {{ seller.name || '....................' }}
                            </div>
                        </div>

                        <div>
                            <strong>خریدار</strong>
                            <span>نام، امضا و اثر انگشت</span>

                            <div class="signature-line">
                                {{ buyer.name || '....................' }}
                            </div>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </div>
</template>

<style scoped>
.features-page {
    min-height: 100dvh;
    padding: 24px;
    background: #8e8e8e;
}

.page-shell {
    position: relative;
    width: min(1240px, 100%);
    margin: 0 auto;
    overflow: hidden;
    border-radius: 42px;
    background: linear-gradient(135deg, #b7efe4 0%, #edf2fb 100%);
    box-shadow:
        0 28px 80px rgba(37, 46, 61, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.55);
}

.shell-background {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 10% 12%, rgba(255, 255, 255, 0.45), transparent 22%),
        radial-gradient(circle at 86% 14%, rgba(255, 255, 255, 0.32), transparent 18%),
        radial-gradient(circle at 92% 90%, rgba(255, 170, 150, 0.18), transparent 14%);
    opacity: 0.8;
}

.topbar {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28px 28px 10px;
}

.topbar-left,
.topbar-right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.circle-btn {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    border: 0;
    background: rgba(255, 255, 255, 0.82);
    color: #161616;
    font-size: 21px;
    box-shadow: 0 8px 24px rgba(60, 73, 91, 0.08);
    transition:
        transform 180ms ease,
        box-shadow 180ms ease,
        background 180ms ease;
}

.circle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(60, 73, 91, 0.13);
}

.print-action {
    cursor: pointer;
    font-size: 23px;
}

.avatar-badge {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: linear-gradient(135deg, #ffb19b 0%, #ff8ba7 100%);
    color: #fff;
    font-size: 20px;
    font-weight: 900;
    box-shadow: 0 10px 24px rgba(255, 140, 160, 0.24);
}

.title-wrap {
    display: flex;
    flex-direction: column;
}

.title-wrap strong {
    font-size: 34px;
    font-weight: 900;
    color: #101215;
    letter-spacing: -0.04em;
}

.title-wrap small {
    margin-top: 3px;
    color: #6a717a;
    font-size: 13px;
    font-weight: 600;
}

.main-grid {
    position: relative;
    z-index: 1;
    padding: 8px 28px 28px;
}

.hero-card {
    padding: 26px;
    border-radius: 34px;
    background: rgba(255, 255, 255, 0.38);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.65),
        0 16px 44px rgba(60, 73, 91, 0.08);
    backdrop-filter: blur(10px);
}

.hero-upper {
    display: grid;
    gap: 18px;
}

.hero-kicker {
    color: #66707a;
    font-size: 13px;
    font-weight: 700;
}

.hero-metric {
    display: flex;
    align-items: baseline;
    gap: 14px;
    flex-wrap: wrap;
}

.hero-metric span {
    font-size: clamp(42px, 6vw, 72px);
    line-height: 0.95;
    font-weight: 300;
    color: #0f1115;
    letter-spacing: -0.05em;
}

.hero-metric strong {
    font-size: clamp(24px, 3vw, 34px);
    font-weight: 900;
    color: #0f1115;
}

.hero-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.hero-meta > div {
    padding: 15px 16px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.44);
}

.hero-meta span {
    display: block;
    color: #737d87;
    font-size: 11px;
    font-weight: 700;
}

.hero-meta strong {
    display: block;
    margin-top: 4px;
    color: #15181c;
    font-size: 14px;
    font-weight: 900;
}

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.85fr);
    gap: 24px;
    margin-top: 24px;
}

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 920px) {
    .features-page {
        padding: 16px;
    }

    .page-shell {
        border-radius: 34px;
    }

    .topbar {
        padding: 22px 20px 10px;
    }

    .main-grid {
        padding: 8px 20px 20px;
    }

    .title-wrap strong {
        font-size: 28px;
    }

    .hero-meta {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .features-page {
        padding: 0;
        background: linear-gradient(135deg, #b7efe4 0%, #edf2fb 100%);
    }

    .page-shell {
        border-radius: 0;
        box-shadow: none;
    }

    .topbar {
        padding: 18px 16px 8px;
    }

    .main-grid {
        padding: 8px 14px 16px;
    }

    .circle-btn,
    .avatar-badge {
        width: 48px;
        height: 48px;
    }

    .title-wrap strong {
        font-size: 24px;
    }

    .hero-card {
        padding: 20px;
        border-radius: 28px;
    }
}


/* Contract UI refinement */
.page-shell {
    width: min(1080px, 100%);
    background: linear-gradient(145deg, #dff1ee 0%, #edf1f7 52%, #eeeaf6 100%);
}

.hero-card {
    border: 1px solid rgba(255,255,255,.88);
    background: rgba(255,255,255,.64);
    box-shadow: 0 14px 36px rgba(44,58,61,.06);
    backdrop-filter: blur(8px);
}

.content-grid {
    grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
    gap: 18px;
}

.content-grid > .space-y-6 {
    display: grid;
    gap: 14px;
}

.content-grid > .space-y-6 > section {
    margin: 0 !important;
    padding: 21px !important;
    border: 1px solid #e1e6e5 !important;
    border-radius: 22px !important;
    background: rgba(250,251,251,.96) !important;
    box-shadow: 0 7px 22px rgba(42,55,58,.045) !important;
}

.content-grid > .space-y-6 > section h2 {
    padding: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #182120;
    font-size: 17px;
}

.content-grid input:not([type="checkbox"]),
.content-grid select,
.content-grid textarea,
.contract-date-input {
    min-height: 46px;
    border: 1px solid #dbe2e0 !important;
    border-radius: 13px !important;
    background: #fff !important;
    box-shadow: none !important;
    color: #192220;
}

.content-grid input:not([type="checkbox"]):focus,
.content-grid select:focus,
.content-grid textarea:focus {
    border-color: #609f98 !important;
    box-shadow: 0 0 0 4px rgba(96,159,152,.11) !important;
}

/* Persian date fields */
.contract-date-wrap {
    position: relative;
}

.contract-date-input {
    width: 100%;
    padding: 0 14px 0 44px;
    cursor: pointer;
    text-align: right;
}

.contract-date-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    border-radius: 9px;
    background: #edf4f2;
    color: #3d7771;
    pointer-events: none;
}

/* Guarantee choices */
.guarantee-selector {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 18px;
}

.guarantee-choice {
    min-height: 138px;
    padding: 16px;
    border: 1px solid #d8dfdd;
    border-radius: 18px;
    background: #f5f7f6;
    color: #26302e;
    text-align: right;
    cursor: pointer;
    transition: 160ms ease;
}

.guarantee-choice:hover {
    transform: translateY(-1px);
    border-color: #b9c6c3;
    background: #fafbfb;
}

.guarantee-choice-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 30px;
}

.guarantee-mark {
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    background: #e5ebe9;
    color: #697572;
    font-weight: 900;
}

.guarantee-choice strong {
    display: block;
    margin-top: 15px;
    font-size: 16px;
    font-weight: 900;
}

.guarantee-choice small {
    display: block;
    margin-top: 6px;
    color: #74807d;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.8;
}

.guarantee-choice.is-selected {
    transform: translateY(-2px);
    color: #fff;
}

.guarantee-choice.is-selected .guarantee-mark {
    background: rgba(255,255,255,.18);
    color: #fff;
}

.guarantee-choice.is-selected small {
    color: rgba(255,255,255,.82);
}

.guarantee-choice.is-check {
    border-color: #176d66;
    background: #176d66;
    box-shadow: 0 12px 26px rgba(23,109,102,.20);
}

.guarantee-choice.is-gold {
    border-color: #ad7b20;
    background: #ad7b20;
    box-shadow: 0 12px 26px rgba(173,123,32,.20);
}

.selected-pill {
    padding: 5px 9px;
    border-radius: 999px;
    background: rgba(255,255,255,.18);
    font-size: 10px;
    font-weight: 900;
}

/* Keep inner installment cards quiet */
.content-grid .backdrop-blur {
    border: 1px solid #e3e8e7 !important;
    background: #f7f9f8 !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
}

.content-grid aside > div {
    border: 1px solid #dfe5e3 !important;
    border-radius: 22px !important;
    background: #fafbfa !important;
    box-shadow: 0 10px 28px rgba(39,53,55,.06) !important;
}

:deep(.vpd-container) {
    direction: rtl;
    font-family: inherit;
}

:deep(.vpd-content) {
    border-radius: 18px;
    overflow: hidden;
}

@media (max-width: 700px) {
    .guarantee-selector {
        grid-template-columns: 1fr;
    }

    .content-grid > .space-y-6 > section {
        padding: 17px !important;
    }
}



/* Formal A4 contract — independent from web UI */
.print-contract {
    direction: rtl;
    width: 190mm;
    min-height: 277mm;
    margin: 0 auto;
    padding: 8mm 9mm 10mm;
    background: #fff;
    color: #111;
    font-family: Vazirmatn, Tahoma, sans-serif;
    font-size: 9.4pt;
    line-height: 1.72;
}

.print-header {
    margin-bottom: 5mm;
    text-align: center;
}

.print-header-rule {
    height: 1.5px;
    margin-bottom: 3mm;
    background: #111;
}

.print-header h1 {
    margin: 0;
    font-size: 15pt;
    font-weight: 900;
    line-height: 1.4;
}

.print-header-meta {
    display: flex;
    justify-content: center;
    gap: 14mm;
    margin-top: 2.5mm;
    font-size: 8.7pt;
}

.print-section {
    margin-top: 4mm;
}

.print-section h2 {
    margin: 0 0 2mm;
    padding-bottom: 1mm;
    border-bottom: 0.6px solid #777;
    font-size: 10.2pt;
    font-weight: 900;
}

.print-party-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4mm;
}

.print-party {
    border: 0.7px solid #777;
}

.print-party h3 {
    margin: 0;
    padding: 1.5mm 2mm;
    border-bottom: 0.7px solid #777;
    background: #f2f2f2;
    font-size: 9.2pt;
    font-weight: 900;
}

.print-party dl {
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin: 0;
}

.print-party dl > div {
    display: grid;
    grid-template-columns: 32mm 1fr;
    min-height: 8mm;
    border-bottom: 0.45px solid #bbb;
}

.print-party dl > div:nth-last-child(1) {
    border-bottom: 0;
}

.print-party dl > div.full {
    grid-column: 1 / -1;
}

.print-party dt,
.print-party dd {
    margin: 0;
    padding: 1.2mm 1.6mm;
}

.print-party dt {
    border-left: 0.45px solid #bbb;
    color: #333;
    font-size: 8.2pt;
    font-weight: 700;
}

.print-party dd {
    font-weight: 700;
}

.print-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    font-size: 8.2pt;
}

.print-table th,
.print-table td {
    padding: 1.35mm 1.5mm;
    border: 0.65px solid #777;
    vertical-align: middle;
    text-align: center;
}

.print-table th {
    background: #f2f2f2;
    font-weight: 900;
}

.print-device-table th {
    width: 13%;
}

.print-device-table td {
    text-align: right;
}

.print-accessories {
    margin-top: 2mm;
    padding: 1.5mm 2mm;
    border: 0.6px solid #aaa;
}

.print-finance-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    margin-bottom: 2mm;
    border: 0.65px solid #777;
}

.print-finance-grid > div {
    padding: 1.5mm 2mm;
    border-left: 0.45px solid #aaa;
}

.print-finance-grid > div:last-child {
    border-left: 0;
}

.print-finance-grid span {
    display: block;
    margin-bottom: 0.5mm;
    color: #444;
    font-size: 7.8pt;
}

.print-finance-grid strong {
    display: block;
    font-size: 8.8pt;
}

.print-note {
    margin: 2mm 0 0;
    font-size: 8.4pt;
    text-align: justify;
}

.print-terms ol {
    margin: 0;
    padding: 0 5mm 0 0;
}

.print-terms li {
    margin-bottom: 1.25mm;
    padding-right: 1mm;
    text-align: justify;
}

.print-acceptance {
    margin-top: 5mm;
    padding-top: 3mm;
    border-top: 1px solid #111;
}

.print-acceptance > p {
    margin: 0;
    text-align: justify;
}

.print-signatures {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22mm;
    margin-top: 10mm;
    text-align: center;
}

.print-signatures strong,
.print-signatures span {
    display: block;
}

.print-signatures span {
    margin-top: 1mm;
    color: #555;
    font-size: 8pt;
}

.signature-line {
    min-height: 18mm;
    margin-top: 2mm;
    padding-top: 13mm;
    border-bottom: 0.7px solid #111;
    font-size: 8.5pt;
}

.print-keep-together {
    break-inside: avoid;
    page-break-inside: avoid;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .features-page {
        min-height: auto !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .print-contract {
        width: auto;
        min-height: 0;
        margin: 0;
        padding: 0;
    }

    .print-section,
    .print-party,
    .print-table,
    .print-finance-grid,
    .print-acceptance {
        box-shadow: none !important;
        filter: none !important;
    }

    * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}



/* Hard print mode switch */
.contract-print {
    display: none;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    .contract-screen {
        display: none !important;
    }

    .contract-print {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .features-page {
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    .print-contract {
        display: block !important;
        width: 100% !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    :global(html),
    :global(body) {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }
}



/* Final A4 / RTL refinements */
.print-contract,
.print-contract * {
    box-sizing: border-box;
}

.print-contract {
    direction: rtl;
    width: 100%;
    max-width: 100%;
    overflow: visible;
    font-family: "Vazirmatn", Tahoma, sans-serif;
    text-align: right;
}

.print-header,
.print-header-meta,
.print-section,
.print-party-grid,
.print-party,
.print-party dl,
.print-party dl > div,
.print-finance-grid,
.print-table,
.print-accessories,
.print-terms,
.print-acceptance {
    direction: rtl;
}

.print-party-grid {
    width: 100%;
    max-width: 100%;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 3mm;
    align-items: start;
}

.print-party {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
}

.print-party dl {
    display: block;
    width: 100%;
}

.print-party dl > div,
.print-party dl > div.full {
    display: grid;
    grid-template-columns: 27mm minmax(0, 1fr);
    width: 100%;
    min-width: 0;
    grid-column: auto;
}

.print-party dt {
    min-width: 0;
    border-right: 0;
    border-left: 0.45px solid #bbb;
    text-align: right;
    white-space: normal;
}

.print-party dd {
    min-width: 0;
    max-width: 100%;
    text-align: right;
    overflow-wrap: anywhere;
    word-break: normal;
}

.print-table {
    direction: rtl;
    width: 100%;
    max-width: 100%;
}

.print-table th,
.print-table td {
    direction: rtl;
}

.print-device-table td {
    text-align: right;
}

.print-finance-grid {
    width: 100%;
    max-width: 100%;
}

.print-signatures {
    direction: rtl;
}

@media print {
    .contract-print,
    .print-contract {
        width: 100% !important;
        max-width: 100% !important;
        overflow: visible !important;
    }

    .print-party-grid,
    .print-table,
    .print-finance-grid {
        max-width: 100% !important;
    }
}

</style>
