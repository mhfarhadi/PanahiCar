<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { ref } from 'vue';

const props = defineProps({
    installments: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            open_count: 0,
            open_amount: 0,
            overdue_count: 0,
            overdue_amount: 0,
            due_soon_count: 0,
            due_soon_amount: 0,
            paid_count: 0,
            paid_amount: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: 'open',
        }),
    },
});

const search = ref(props.filters.search || '');
const selectedInstallment = ref(null);
const selectedPaidInstallment = ref(null);
const selectedReverseInstallment = ref(null);
const selectedImageAction = ref(null);
const imageActionMode = ref('remove');

const money = (value) =>
    `${Number(value || 0).toLocaleString('fa-IR')} تومان`;

const formatDate = (value) => {
    if (!value) return '—';

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(`${value}T00:00:00`));
};

const formatDateTime = (value) => {
    if (!value) return '—';

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const formatNumber = (value) =>
    Number(value || 0).toLocaleString('fa-IR');

const normalizeDigits = (value) =>
    String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
        .replace(/[٠-٩]/g, (digit) => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));

const now = new Date();

const localToday = [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, '0'),
    String(now.getDate()).padStart(2, '0'),
].join('-');

const clearanceForm = useForm({
    paid_at: '',
});

const syncPaidAt = (value) => {
    if (!value) return;

    if (typeof value.format === 'function') {
        const date = value.clone ? value.clone() : value;

        if (typeof date.locale === 'function') {
            date.locale('en');
        }

        clearanceForm.paid_at = normalizeDigits(
            date.format('YYYY-MM-DD'),
        );

        return;
    }

    clearanceForm.paid_at = normalizeDigits(String(value));
};

const openPaidModal = (item) => {
    selectedPaidInstallment.value = item;
    clearanceForm.clearErrors();
    clearanceForm.paid_at = '';
};

const closePaidModal = () => {
    if (clearanceForm.processing) return;

    selectedPaidInstallment.value = null;
    clearanceForm.reset();
    clearanceForm.clearErrors();
};

const submitInstallmentPaid = () => {
    if (!selectedPaidInstallment.value) return;

    clearanceForm.post(
        route(
            'installments.mark-paid',
            selectedPaidInstallment.value.id,
        ),
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedPaidInstallment.value = null;
                clearanceForm.reset();
            },
        },
    );
};

const reversalForm = useForm({
    reason: '',
});

const openReverseModal = (item) => {
    selectedReverseInstallment.value = item;
    reversalForm.reset();
    reversalForm.clearErrors();
};

const closeReverseModal = () => {
    if (reversalForm.processing) return;

    selectedReverseInstallment.value = null;
    reversalForm.reset();
    reversalForm.clearErrors();
};

const submitPaidReversal = () => {
    if (!selectedReverseInstallment.value) return;

    reversalForm.post(
        route(
            'installments.reverse-paid',
            selectedReverseInstallment.value.id,
        ),
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedReverseInstallment.value = null;
                reversalForm.reset();
            },
        },
    );
};

const tabs = [
    { label: 'همه', value: 'all' },
    { label: 'باز', value: 'open' },
    { label: 'معوق', value: 'overdue' },
    { label: '۷ روز آینده', value: 'due_soon' },
    { label: 'وصول‌شده', value: 'paid' },
];

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

const submitSearch = () => {
    router.get(
        route('installments.index'),
        {
            search: search.value || undefined,
            status: props.filters.status || 'open',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const clearSearch = () => {
    search.value = '';

    router.get(
        route('installments.index'),
        {
            status: props.filters.status || 'open',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const statusLabel = (item) => {
    if (item.status === 'paid') {
        return item.guarantee_type === 'gold'
            ? 'پرداخت‌شده'
            : 'چک پاس شده';
    }
    if (item.is_overdue) return 'معوق';
    if (item.is_due_soon) return 'سررسید نزدیک';
    if (Number(item.paid_amount || 0) > 0) return 'پرداخت ناقص';

    return 'باز';
};

const statusClass = (item) => {
    if (item.status === 'paid') {
        return 'bg-[#eaf8ef] text-[#4b9867] dark:bg-emerald-950/30 dark:text-emerald-300';
    }

    if (item.is_overdue) {
        return 'bg-[#eff6ff] text-[#1d4ed8] dark:bg-red-950/30 dark:text-red-300';
    }

    if (item.is_due_soon) {
        return 'bg-[#fff7e7] text-[#b98225] dark:bg-amber-950/30 dark:text-amber-300';
    }

    return 'bg-[#eef4ff] text-[#6382b8] dark:bg-sky-950/30 dark:text-sky-300';
};

const overdueDays = (item) => {
    if (!item.is_overdue) return 0;

    const due = new Date(`${item.due_date}T00:00:00Z`);
    const today = new Date();
    const todayUtc = Date.UTC(
        today.getFullYear(),
        today.getMonth(),
        today.getDate(),
    );

    return Math.max(
        0,
        Math.floor((todayUtc - due.getTime()) / 86400000),
    );
};

const hasCheckDetails = (item) =>
    Boolean(
        item.check_number ||
        item.bank_name ||
        item.sayad_id ||
        item.images?.length,
    );

const checkForm = useForm({
    check_number: '',
    bank_name: '',
    sayad_id: '',
    images: [],
    note: '',
});

const openCheckModal = (item) => {
    selectedInstallment.value = item;

    checkForm.clearErrors();
    checkForm.check_number = item.check_number || '';
    checkForm.bank_name = item.bank_name || '';
    checkForm.sayad_id = item.sayad_id || '';
    checkForm.images = [];
    checkForm.note = '';
};

const closeCheckModal = () => {
    if (checkForm.processing) return;

    selectedInstallment.value = null;
    checkForm.reset();
    checkForm.clearErrors();
};

const handleCheckNumber = (event) => {
    checkForm.check_number = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 50);
};

const handleSayadId = (event) => {
    checkForm.sayad_id = normalizeDigits(event.target.value)
        .replace(/\D/g, '')
        .slice(0, 16);
};

const handleImages = (event) => {
    checkForm.images = Array.from(event.target.files || []).slice(0, 6);
};

const submitCheckDetails = () => {
    if (!selectedInstallment.value) return;

    checkForm.post(
        route(
            'installments.check-details',
            selectedInstallment.value.id,
        ),
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                selectedInstallment.value = null;
                checkForm.reset();
            },
        },
    );
};

const imageActionForm = useForm({
    reason: '',
    image: null,
});

const openImageAction = (image, mode) => {
    selectedImageAction.value = image;
    imageActionMode.value = mode;
    imageActionForm.reset();
    imageActionForm.clearErrors();
};

const closeImageAction = () => {
    if (imageActionForm.processing) return;

    selectedImageAction.value = null;
    imageActionForm.reset();
    imageActionForm.clearErrors();
};

const handleReplacementImage = (event) => {
    imageActionForm.image = event.target.files?.[0] || null;
};

const submitImageAction = () => {
    if (!selectedInstallment.value || !selectedImageAction.value) return;

    const routeName =
        imageActionMode.value === 'replace'
            ? 'installments.images.replace'
            : 'installments.images.remove';

    imageActionForm.post(
        route(routeName, {
            installment: selectedInstallment.value.id,
            image: selectedImageAction.value.id,
        }),
        {
            forceFormData: imageActionMode.value === 'replace',
            preserveScroll: true,
            onSuccess: () => {
                selectedImageAction.value = null;
                selectedInstallment.value = null;
                imageActionForm.reset();
                checkForm.reset();
            },
        },
    );
};
</script>

<template>
    <Head title="اقساط و مطالبات | automaya" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-black">اقساط</h1>
                        <p class="mt-1 text-[11px] font-bold text-slate-400">
                            سررسید و وصول مطالبات
                        </p>
                    </div>

                    <Link
                        :href="route('sales.index')"
                        class="am-btn-secondary !rounded-full !px-4 !py-2 text-xs"
                    >
                        فروش‌ها
                    </Link>
                </div>

                <div class="mb-4 grid grid-cols-2 gap-3">
                    <Link
                        :href="route('installments.index', { status: 'open', search: filters.search || undefined })"
                        class="am-card !p-4"
                    >
                        <p class="text-[10px] font-black text-slate-400">باز</p>
                        <p class="mt-2 text-sm font-black">{{ money(summary.open_amount) }}</p>
                        <p class="mt-1 text-[10px] text-slate-400">
                            {{ formatNumber(summary.open_count) }} قسط
                        </p>
                    </Link>

                    <Link
                        :href="route('installments.index', { status: 'overdue', search: filters.search || undefined })"
                        class="am-card !p-4"
                    >
                        <p class="text-[10px] font-black text-red-500">معوق</p>
                        <p class="mt-2 text-sm font-black">{{ money(summary.overdue_amount) }}</p>
                        <p class="mt-1 text-[10px] text-slate-400">
                            {{ formatNumber(summary.overdue_count) }} قسط
                        </p>
                    </Link>

                    <Link
                        :href="route('installments.index', { status: 'due_soon', search: filters.search || undefined })"
                        class="am-card !p-4"
                    >
                        <p class="text-[10px] font-black text-amber-600">۷ روز آینده</p>
                        <p class="mt-2 text-sm font-black">{{ money(summary.due_soon_amount) }}</p>
                        <p class="mt-1 text-[10px] text-slate-400">
                            {{ formatNumber(summary.due_soon_count) }} قسط
                        </p>
                    </Link>

                    <Link
                        :href="route('installments.index', { status: 'paid', search: filters.search || undefined })"
                        class="am-card !p-4"
                    >
                        <p class="text-[10px] font-black text-emerald-600">وصول‌شده</p>
                        <p class="mt-2 text-sm font-black">{{ money(summary.paid_amount) }}</p>
                        <p class="mt-1 text-[10px] text-slate-400">
                            {{ formatNumber(summary.paid_count) }} قسط
                        </p>
                    </Link>
                </div>

                <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.value"
                        :href="
                            route('installments.index', {
                                status: tab.value,
                                search: filters.search || undefined,
                            })
                        "
                        class="am-chip shrink-0"
                        :class="filters.status === tab.value ? 'am-chip-on' : ''"
                    >
                        {{ tab.label }}
                    </Link>
                </div>

                <form
                    class="mb-4 flex gap-2"
                    @submit.prevent="submitSearch"
                >
                    <input
                        v-model="search"
                        type="search"
                        class="am-input min-w-0 flex-1"
                        placeholder="نام، موبایل، مدل، VIN..."
                    />

                    <button
                        type="submit"
                        class="am-btn-primary !rounded-2xl !px-4 text-xs"
                    >
                        جست‌وجو
                    </button>
                </form>

                <div v-if="installments.length" class="space-y-3">
                    <div
                        v-for="item in installments"
                        :key="item.id"
                        class="am-card !p-4"
                    >
                            <div
                                class="grid gap-4 xl:grid-cols-[minmax(170px,1.25fr)_minmax(150px,1fr)_95px_minmax(135px,.85fr)_minmax(155px,1fr)_auto] xl:items-center"
                            >
                                <div class="min-w-0">
                                    <Link
                                        :href="route('contacts.show', item.buyer_id)"
                                        class="font-black transition hover:text-[#2563eb]"
                                    >
                                        {{ item.buyer_name }}
                                    </Link>

                                    <p class="mt-1 text-[11px] text-slate-400" dir="ltr">
                                        {{ item.buyer_mobile }}
                                    </p>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black">
                                        {{ item.brand }} {{ item.model }}
                                    </p>

                                    <p class="mt-1 truncate text-[11px] text-slate-400">
                                        <span v-if="item.model_year">{{ item.model_year }}</span>
                                        <span v-if="item.mileage !== null && item.mileage !== undefined"> · {{ Number(item.mileage).toLocaleString('fa-IR') }} km</span>
                                        <span v-if="item.vin"> · VIN {{ item.vin }}</span>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] text-slate-400">قسط</p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ formatNumber(item.installment_number) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] text-slate-400">سررسید</p>
                                    <p class="mt-1 text-sm font-black">
                                        {{ formatDate(item.due_date) }}
                                    </p>

                                    <p
                                        v-if="item.is_overdue"
                                        class="mt-1 text-[10px] font-black text-red-500"
                                    >
                                        {{ formatNumber(overdueDays(item)) }} روز گذشته
                                    </p>
                                </div>

                                <div>
                                    <template v-if="item.guarantee_type === 'gold'">
                                        <p class="text-[10px] text-slate-400">
                                            نوع ضمانت
                                        </p>

                                        <p class="mt-1 text-xs font-black text-amber-700 dark:text-amber-300">
                                            ضمانت طلا
                                        </p>

                                        <p class="mt-1 text-[10px] text-slate-400">
                                            این قسط چک ندارد
                                        </p>
                                    </template>

                                    <template v-else>
                                        <p class="text-[10px] text-slate-400">
                                            مشخصات چک
                                        </p>

                                        <template v-if="hasCheckDetails(item)">
                                            <p class="mt-1 truncate text-xs font-black">
                                                {{ item.bank_name || 'بانک نامشخص' }}
                                                <span v-if="item.check_number">
                                                    · {{ item.check_number }}
                                                </span>
                                            </p>

                                            <p class="mt-1 text-[10px] text-slate-400">
                                                <span v-if="item.sayad_id">
                                                    صیاد: {{ item.sayad_id }}
                                                </span>
                                                <span v-if="item.images?.length">
                                                    {{ item.sayad_id ? ' · ' : '' }}
                                                    {{ formatNumber(item.images.length) }} تصویر
                                                </span>
                                            </p>
                                        </template>

                                        <p v-else class="mt-1 text-xs text-slate-400">
                                            ثبت نشده
                                        </p>
                                    </template>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                                    <div class="text-left xl:ml-1">
                                        <p class="text-sm font-black">
                                            {{
                                                item.status === 'paid'
                                                    ? money(item.paid_amount)
                                                    : money(item.remaining_amount)
                                            }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-slate-400">
                                            {{
                                                item.status === 'paid'
                                                    ? `${
                                                        item.guarantee_type === 'gold'
                                                            ? 'پرداخت‌شده'
                                                            : 'پاس‌شده'
                                                    } در ${formatDate(item.paid_at)}`
                                                    : 'مانده قسط'
                                            }}
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-full px-3 py-1.5 text-[10px] font-black"
                                        :class="statusClass(item)"
                                    >
                                        {{ statusLabel(item) }}
                                    </span>

                                    <button
                                        v-if="item.guarantee_type !== 'gold'"
                                        type="button"
                                        class="rounded-full bg-neutral-900 px-3 py-2 text-[10px] font-bold text-white"
                                        @click="openCheckModal(item)"
                                    >
                                        {{
                                            hasCheckDetails(item)
                                                ? 'مشخصات چک'
                                                : 'ثبت چک'
                                        }}
                                    </button>

                                    <button
                                        v-if="item.status !== 'paid'"
                                        type="button"
                                        class="rounded-xl bg-emerald-600 px-3 py-2 text-[10px] font-black text-white transition hover:bg-emerald-700"
                                        @click="openPaidModal(item)"
                                    >
                                        {{
                                            item.guarantee_type === 'gold'
                                                ? 'ثبت وصول قسط'
                                                : 'ثبت پاس شدن'
                                        }}
                                    </button>

                                    <button
                                        v-else
                                        type="button"
                                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[10px] font-black text-amber-700 transition hover:bg-amber-100 dark:border-amber-400/10 dark:bg-amber-950/20 dark:text-amber-300"
                                        @click="openReverseModal(item)"
                                    >
                                        اصلاح وصول
                                    </button>

                                    <Link
                                        :href="route('sales.show', item.sale_id)"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-slate-400 shadow-[0_5px_14px_rgba(35,45,65,0.05)] transition hover:text-[#2563eb] dark:bg-white/5"
                                        title="مشاهده قرارداد"
                                        aria-label="مشاهده قرارداد"
                                    >
                                        ←
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="am-soft py-12 text-center"
                    >
                        <p class="text-sm font-black text-slate-500 dark:text-slate-300">
                            قسطی با این شرایط پیدا نشد.
                        </p>
                        <p class="mt-2 text-xs text-slate-400">
                            فیلتر یا عبارت جست‌وجو را تغییر دهید.
                        </p>
                    </div>
            </div>
        </div>

        <!-- Check details modal -->
        <div
            v-if="selectedInstallment"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm"
            @click.self="closeCheckModal"
        >
            <div
                dir="rtl"
                class="max-h-[92dvh] w-full max-w-3xl overflow-y-auto rounded-[30px] border border-white bg-[#fbfbfa] p-5 shadow-2xl dark:border-white/5 dark:bg-[#11151d] sm:p-6"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black text-[#2563eb]">
                            مشخصات چک
                        </p>

                        <h2 class="mt-1 text-xl font-black">
                            قسط
                            {{ formatNumber(selectedInstallment.installment_number) }}
                            · {{ selectedInstallment.buyer_name }}
                        </h2>

                        <p class="mt-2 text-xs leading-6 text-slate-400">
                            {{ selectedInstallment.brand }}
                            {{ selectedInstallment.model }}
                            · سررسید
                            {{ formatDate(selectedInstallment.due_date) }}
                            · {{ money(selectedInstallment.amount) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#f1f3f5] text-slate-500 dark:bg-white/5 dark:text-slate-300"
                        @click="closeCheckModal"
                    >
                        ×
                    </button>
                </div>

                <form class="mt-6" @submit.prevent="submitCheckDetails">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                بانک
                            </label>

                            <select
                                v-model="checkForm.bank_name"
                                class="mh-input"
                            >
                                <option value="">
                                    انتخاب بانک
                                </option>

                                <option
                                    v-for="bank in bankOptions"
                                    :key="bank"
                                    :value="bank"
                                >
                                    {{ bank }}
                                </option>
                            </select>

                            <p
                                v-if="checkForm.errors.bank_name"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ checkForm.errors.bank_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                شماره چک
                            </label>

                            <input
                                :value="checkForm.check_number"
                                type="text"
                                inputmode="numeric"
                                maxlength="50"
                                dir="ltr"
                                class="mh-input text-left"
                                placeholder="شماره چک"
                                @input="handleCheckNumber"
                            />

                            <p
                                v-if="checkForm.errors.check_number"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ checkForm.errors.check_number }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-bold">
                                شناسه صیاد
                            </label>

                            <input
                                :value="checkForm.sayad_id"
                                type="text"
                                inputmode="numeric"
                                maxlength="16"
                                dir="ltr"
                                class="mh-input text-left tracking-[0.12em]"
                                placeholder="شناسه ۱۶ رقمی صیاد"
                                @input="handleSayadId"
                            />

                            <p class="mt-2 text-[11px] text-slate-400">
                                در صورت ثبت، شناسه صیاد باید دقیقاً ۱۶ رقم باشد.
                            </p>

                            <p
                                v-if="checkForm.errors.sayad_id"
                                class="mt-2 text-xs font-bold text-red-500"
                            >
                                {{ checkForm.errors.sayad_id }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="selectedInstallment.images?.length"
                        class="mt-6"
                    >
                        <p class="mb-3 text-sm font-black">
                            تصاویر ثبت‌شده
                        </p>

                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div
                                v-for="image in selectedInstallment.images"
                                :key="image.id"
                                class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white dark:border-white/5 dark:bg-white/5"
                            >
                                <a
                                    :href="`/storage/${image.image_path}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="block"
                                >
                                    <img
                                        :src="`/storage/${image.image_path}`"
                                        alt="تصویر چک"
                                        class="h-32 w-full object-cover"
                                    />
                                </a>

                                <div class="grid grid-cols-2 gap-2 p-2">
                                    <button
                                        type="button"
                                        class="rounded-xl bg-[#eef4ff] px-2 py-2 text-[10px] font-black text-[#6382b8] transition hover:bg-[#e3edff] dark:bg-sky-950/30 dark:text-sky-300"
                                        @click="openImageAction(image, 'replace')"
                                    >
                                        جایگزینی
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-xl bg-[#eff6ff] px-2 py-2 text-[10px] font-black text-[#1d4ed8] transition hover:bg-[#dbeafe] dark:bg-red-950/25 dark:text-red-300"
                                        @click="openImageAction(image, 'remove')"
                                    >
                                        حذف
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-[11px] leading-6 text-slate-400">
                            حذف یا جایگزینی، نسخه قبلی تصویر را از سابقه مالی
                            نابود نمی‌کند؛ فایل قبلی در آرشیو باقی می‌ماند.
                        </p>
                    </div>

                    <div class="mt-6">
                        <label class="mb-2 block text-sm font-bold">
                            افزودن تصویر چک
                        </label>

                        <label
                            class="flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed border-[#93c5fd] bg-[#eff6ff] px-4 py-7 text-center transition hover:border-[#93c5fd] dark:border-[#2563eb]/20 dark:bg-[#2563eb]/[0.06]"
                        >
                            <span class="text-2xl">＋</span>
                            <span class="mt-2 text-sm font-black">
                                انتخاب تصویر
                            </span>
                            <span class="mt-1 text-[11px] text-slate-400">
                                حداکثر ۶ تصویر، هر فایل تا ۵ مگابایت
                            </span>

                            <input
                                type="file"
                                multiple
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="handleImages"
                            />
                        </label>

                        <p
                            v-if="checkForm.images.length"
                            class="mt-2 text-xs font-black text-[#2563eb]"
                        >
                            {{ formatNumber(checkForm.images.length) }}
                            تصویر جدید انتخاب شده
                        </p>

                        <p
                            v-if="checkForm.errors.images"
                            class="mt-2 text-xs font-bold text-red-500"
                        >
                            {{ checkForm.errors.images }}
                        </p>
                    </div>

                    <div class="mt-6">
                        <label class="mb-2 block text-sm font-bold">
                            یادداشت جدید
                        </label>

                        <textarea
                            v-model="checkForm.note"
                            rows="3"
                            maxlength="10000"
                            class="mh-input resize-y leading-7"
                            placeholder="مثلاً چک توسط مشتری تحویل شد، وضعیت ثبت صیاد و..."
                        />

                        <p class="mt-2 text-[11px] text-slate-400">
                            این یادداشت به تاریخچه اضافه می‌شود و یادداشت‌های قبلی حذف نمی‌شوند.
                        </p>

                        <p
                            v-if="checkForm.errors.note"
                            class="mt-2 text-xs font-bold text-red-500"
                        >
                            {{ checkForm.errors.note }}
                        </p>
                    </div>

                    <div
                        v-if="selectedInstallment.notes?.length"
                        class="mt-6 rounded-[22px] bg-[#f4f6f8] p-4 dark:bg-white/[0.035]"
                    >
                        <p class="mb-3 text-sm font-black">
                            تاریخچه یادداشت‌ها
                        </p>

                        <div class="space-y-3">
                            <div
                                v-for="note in selectedInstallment.notes"
                                :key="note.id"
                                class="rounded-2xl bg-white p-4 dark:bg-white/[0.04]"
                            >
                                <p class="whitespace-pre-wrap text-sm leading-7">
                                    {{ note.body }}
                                </p>

                                <p class="mt-2 text-[10px] text-slate-400">
                                    {{ note.author_name || 'کاربر سابق / نامشخص' }}
                                    · {{ formatDateTime(note.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-7 grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="mh-secondary"
                            :disabled="checkForm.processing"
                            @click="closeCheckModal"
                        >
                            انصراف
                        </button>

                        <button
                            type="submit"
                            class="mh-primary"
                            :disabled="checkForm.processing"
                        >
                            {{
                                checkForm.processing
                                    ? 'در حال ذخیره...'
                                    : 'ذخیره مشخصات چک'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mark paid modal -->
        <div
            v-if="selectedPaidInstallment"
            class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm"
            @click.self="closePaidModal"
        >
            <div
                dir="rtl"
                class="w-full max-w-md rounded-[30px] border border-white bg-[#fbfbfa] p-5 shadow-2xl dark:border-white/5 dark:bg-[#11151d] sm:p-6"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black text-emerald-600 dark:text-emerald-400">
                            وصول چک
                        </p>

                        <h2 class="mt-1 text-xl font-black">
                            ثبت پاس شدن چک
                        </h2>

                        <p class="mt-2 text-xs leading-6 text-slate-400">
                            قسط
                            {{ formatNumber(selectedPaidInstallment.installment_number) }}
                            · {{ selectedPaidInstallment.buyer_name }}
                            · سررسید
                            {{ formatDate(selectedPaidInstallment.due_date) }}
                        </p>

                        <p class="mt-1 text-xs font-black">
                            {{ money(selectedPaidInstallment.amount) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#f1f3f5] text-slate-500 dark:bg-white/5 dark:text-slate-300"
                        @click="closePaidModal"
                    >
                        ×
                    </button>
                </div>

                <div class="mt-6">
                    <label class="mb-2 block text-sm font-bold">
                        تاریخ واقعی پاس شدن
                    </label>

                    <Vue3PersianDatetimePicker
                        v-model="clearanceForm.paid_at"
                        :initial-value="localToday"
                        format="YYYY-MM-DD"
                        display-format="jYYYY/jMM/jDD"
                        type="date"
                        convert-numbers
                        auto-submit
                        custom-input=".receivable-paid-at-input"
                        @change="syncPaidAt"
                    />

                    <input
                        type="text"
                        class="receivable-paid-at-input mh-input text-center font-black"
                        placeholder="تاریخ پاس شدن چک"
                        readonly
                    />

                    <p
                        v-if="clearanceForm.errors.paid_at"
                        class="mt-2 text-xs font-bold text-red-500"
                    >
                        {{ clearanceForm.errors.paid_at }}
                    </p>

                    <div
                        class="mt-4 rounded-2xl bg-amber-50 p-4 text-xs leading-6 text-amber-700 dark:bg-amber-950/20 dark:text-amber-300"
                    >
                        تاریخ واقعی وصول را وارد کنید. این تاریخ در سابقه
                        پرداخت مشتری و محاسبه تأخیر چک استفاده می‌شود.
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        class="mh-secondary"
                        :disabled="clearanceForm.processing"
                        @click="closePaidModal"
                    >
                        انصراف
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-700 disabled:opacity-50"
                        :disabled="
                            clearanceForm.processing ||
                            !clearanceForm.paid_at
                        "
                        @click="submitInstallmentPaid"
                    >
                        {{
                            clearanceForm.processing
                                ? 'در حال ثبت...'
                                : 'تأیید پاس شدن'
                        }}
                    </button>
                </div>
            </div>
        </div>


        <!-- Reverse paid modal -->
        <div
            v-if="selectedReverseInstallment"
            class="fixed inset-0 z-[95] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm"
            @click.self="closeReverseModal"
        >
            <div
                dir="rtl"
                class="w-full max-w-lg rounded-[30px] border border-white bg-[#fbfbfa] p-5 shadow-2xl dark:border-white/5 dark:bg-[#11151d] sm:p-6"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black text-amber-600 dark:text-amber-400">
                            اصلاح ثبت وصول
                        </p>

                        <h2 class="mt-1 text-xl font-black">
                            برگشت پاس شدن چک
                        </h2>

                        <p class="mt-2 text-xs leading-6 text-slate-400">
                            قسط
                            {{ formatNumber(selectedReverseInstallment.installment_number) }}
                            · {{ selectedReverseInstallment.buyer_name }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#f1f3f5] text-slate-500 dark:bg-white/5 dark:text-slate-300"
                        @click="closeReverseModal"
                    >
                        ×
                    </button>
                </div>

                <div
                    class="mt-5 rounded-[20px] bg-amber-50 p-4 dark:bg-amber-950/20"
                >
                    <p class="text-xs font-black text-amber-700 dark:text-amber-300">
                        این عملیات اطلاعات قبلی را بی‌صدا حذف نمی‌کند.
                    </p>

                    <p class="mt-2 text-xs leading-6 text-amber-700/80 dark:text-amber-300/80">
                        وضعیت چک دوباره باز می‌شود، اما تاریخ و مبلغ وصول قبلی
                        همراه با دلیل این اصلاح در تاریخچه چک ثبت خواهد شد.
                    </p>

                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs">
                        <span>
                            تاریخ وصول فعلی:
                            <strong>
                                {{ formatDate(selectedReverseInstallment.paid_at) }}
                            </strong>
                        </span>

                        <span>
                            مبلغ:
                            <strong>
                                {{ money(selectedReverseInstallment.paid_amount) }}
                            </strong>
                        </span>
                    </div>
                </div>

                <form
                    class="mt-5"
                    @submit.prevent="submitPaidReversal"
                >
                    <label class="mb-2 block text-sm font-bold">
                        دلیل اصلاح
                    </label>

                    <textarea
                        v-model="reversalForm.reason"
                        rows="3"
                        maxlength="1000"
                        class="mh-input resize-y leading-7"
                        placeholder="مثلاً پاس شدن این چک اشتباهی ثبت شده بود..."
                    />

                    <p class="mt-2 text-[11px] leading-6 text-slate-400">
                        این توضیح به سابقه دائمی چک اضافه می‌شود.
                    </p>

                    <p
                        v-if="reversalForm.errors.reason"
                        class="mt-2 text-xs font-bold text-red-500"
                    >
                        {{ reversalForm.errors.reason }}
                    </p>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="mh-secondary"
                            :disabled="reversalForm.processing"
                            @click="closeReverseModal"
                        >
                            انصراف
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-black text-white transition hover:bg-amber-700 disabled:opacity-50"
                            :disabled="
                                reversalForm.processing ||
                                reversalForm.reason.trim().length < 3
                            "
                        >
                            {{
                                reversalForm.processing
                                    ? 'در حال اصلاح...'
                                    : 'تأیید اصلاح وصول'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Check image remove / replace modal -->
        <div
            v-if="selectedImageAction && selectedInstallment"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm"
            @click.self="closeImageAction"
        >
            <div
                dir="rtl"
                class="w-full max-w-lg rounded-[30px] border border-white bg-[#fbfbfa] p-5 shadow-2xl dark:border-white/5 dark:bg-[#11151d] sm:p-6"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-[11px] font-black"
                            :class="
                                imageActionMode === 'replace'
                                    ? 'text-[#6382b8] dark:text-sky-300'
                                    : 'text-[#1d4ed8] dark:text-red-300'
                            "
                        >
                            مدیریت تصویر چک
                        </p>

                        <h2 class="mt-1 text-xl font-black">
                            {{
                                imageActionMode === 'replace'
                                    ? 'جایگزینی تصویر'
                                    : 'حذف تصویر'
                            }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f1f3f5] text-slate-500 dark:bg-white/5 dark:text-slate-300"
                        @click="closeImageAction"
                    >
                        ×
                    </button>
                </div>

                <div class="mt-5 overflow-hidden rounded-[20px] bg-[#f4f6f8] dark:bg-white/[0.035]">
                    <img
                        :src="`/storage/${selectedImageAction.image_path}`"
                        alt="تصویر فعلی چک"
                        class="max-h-52 w-full object-contain"
                    />
                </div>

                <div
                    class="mt-4 rounded-2xl bg-amber-50 p-4 text-xs leading-6 text-amber-700 dark:bg-amber-950/20 dark:text-amber-300"
                >
                    نسخه فعلی تصویر حتی پس از حذف یا جایگزینی در آرشیو
                    تاریخی باقی می‌ماند و دلیل این تغییر نیز ثبت می‌شود.
                </div>

                <form
                    class="mt-5"
                    @submit.prevent="submitImageAction"
                >
                    <div v-if="imageActionMode === 'replace'">
                        <label class="mb-2 block text-sm font-bold">
                            تصویر جدید
                        </label>

                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="mh-input"
                            @change="handleReplacementImage"
                        />

                        <p
                            v-if="imageActionForm.errors.image"
                            class="mt-2 text-xs font-bold text-red-500"
                        >
                            {{ imageActionForm.errors.image }}
                        </p>
                    </div>

                    <div :class="imageActionMode === 'replace' ? 'mt-5' : ''">
                        <label class="mb-2 block text-sm font-bold">
                            دلیل
                            {{
                                imageActionMode === 'replace'
                                    ? 'جایگزینی'
                                    : 'حذف'
                            }}
                        </label>

                        <textarea
                            v-model="imageActionForm.reason"
                            rows="3"
                            maxlength="1000"
                            class="mh-input resize-y leading-7"
                            placeholder="مثلاً تصویر اشتباه ثبت شده بود..."
                        />

                        <p
                            v-if="imageActionForm.errors.reason"
                            class="mt-2 text-xs font-bold text-red-500"
                        >
                            {{ imageActionForm.errors.reason }}
                        </p>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="mh-secondary"
                            :disabled="imageActionForm.processing"
                            @click="closeImageAction"
                        >
                            انصراف
                        </button>

                        <button
                            type="submit"
                            class="mh-primary"
                            :disabled="
                                imageActionForm.processing ||
                                imageActionForm.reason.trim().length < 3 ||
                                (
                                    imageActionMode === 'replace' &&
                                    !imageActionForm.image
                                )
                            "
                        >
                            {{
                                imageActionForm.processing
                                    ? 'در حال ثبت...'
                                    : (
                                        imageActionMode === 'replace'
                                            ? 'تأیید جایگزینی'
                                            : 'تأیید حذف'
                                    )
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
