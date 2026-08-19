<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { toJalaali } from 'jalaali-js';
import FeaturesLayout from '@/Layouts/FeaturesLayout.vue';
import { showroomPhoto } from '@/Utils/carPhotos';
import { formatMoney, formatCount, toPersianDigits } from '@/Utils/featuresForm';
import { colorLabel } from '@/Utils/vehicleLabels';

const props = defineProps({
    requests: { type: Object, default: () => ({ data: [] }) },
    filters: { type: Object, default: () => ({}) },
    brands: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({ total: 0, recent: 0 }) },
});

const search = ref(props.filters.q || '');
const brand = ref(props.filters.brand || '');
const contact = ref(null);
const contactError = ref('');
const expandedId = ref(null);

const applyFilters = () => {
    router.get(route('features.wanted-market.index'), {
        q: search.value || undefined,
        brand: brand.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const formatJalali = (value) => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})/);

    if (!match) return '—';

    const jalali = toJalaali(Number(match[1]), Number(match[2]), Number(match[3]));

    return toPersianDigits(
        `${jalali.jy}/${String(jalali.jm).padStart(2, '0')}/${String(jalali.jd).padStart(2, '0')}`
    );
};

const toggleDetails = (id) => {
    expandedId.value = expandedId.value === id ? null : id;
    contact.value = null;
    contactError.value = '';
};

const revealContact = async (id) => {
    contactError.value = '';
    contact.value = null;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(route('features.wanted-market.contact', id), {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': token || '',
            },
            credentials: 'same-origin',
        });
        const data = await response.json();
        if (!response.ok) {
            contactError.value = data.message || 'شماره در دسترس نیست.';
            return;
        }
        contact.value = data.contact;
    } catch {
        contactError.value = 'ارتباط برقرار نشد.';
    }
};
</script>

<template>
    <Head title="چیا می‌خوان؟ | Panahi Car" />

    <FeaturesLayout title="چیا می‌خوان؟" subtitle="تقاضای بازار همکاران">
        <div class="relative mb-5 overflow-hidden rounded-[28px]">
            <img :src="showroomPhoto(10)" alt="" class="h-36 w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div class="absolute bottom-4 right-4 text-white">
                <p class="text-[11px] text-white/70">بازار تقاضا</p>
                <h1 class="text-xl font-black">خودروهایی که می‌خواهند</h1>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-2.5">
            <div class="rounded-[22px] bg-white p-3 dark:bg-[#161618]">
                <p class="text-[10px] text-neutral-400">کل درخواست‌ها</p>
                <p class="mt-1 text-lg font-black">{{ formatCount(summary.total) }}</p>
            </div>
            <div class="rounded-[22px] bg-white p-3 dark:bg-[#161618]">
                <p class="text-[10px] text-neutral-400">۲۴ ساعت اخیر</p>
                <p class="mt-1 text-lg font-black">{{ formatCount(summary.recent) }}</p>
            </div>
        </div>

        <div class="mb-4 flex gap-2">
            <input v-model="search" class="am-input flex-1" placeholder="جستجو..." @keyup.enter="applyFilters" />
            <select v-model="brand" class="am-input !w-32" @change="applyFilters">
                <option value="">همه برندها</option>
                <option v-for="item in brands" :key="item" :value="item">{{ item }}</option>
            </select>
        </div>

        <p v-if="contact" class="mb-3 rounded-[22px] bg-emerald-50 p-4 text-sm font-bold text-emerald-900">
            {{ contact.requester_name }} · {{ contact.mobile }}
        </p>
        <p v-if="contactError" class="mb-3 text-sm text-red-500">{{ contactError }}</p>

        <div v-if="!requests.data?.length" class="am-soft py-10 text-center text-sm text-neutral-400">
            هنوز درخواستی ثبت نشده.
        </div>

        <div class="space-y-3">
            <button
                v-for="item in requests.data"
                :key="item.id"
                type="button"
                class="w-full rounded-[24px] bg-white p-4 text-right shadow-[0_10px_24px_rgba(0,0,0,0.04)] dark:bg-[#161618]"
                @click="toggleDetails(item.id)"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-black">{{ item.brand }} {{ item.model }}</p>
                        <p class="mt-1 text-[11px] text-neutral-400">
                            برای دیدن ریز درخواست بزنید
                        </p>
                    </div>
                    <p class="text-xs font-black">{{ formatMoney(item.max_price) }}</p>
                </div>

                <div v-if="expandedId === item.id" class="mt-4 space-y-2 border-t border-neutral-100 pt-3 text-sm dark:border-white/10">
                    <div v-if="item.requester_name" class="flex justify-between gap-3">
                        <span class="text-neutral-400">درخواست‌کننده</span>
                        <span class="font-bold">{{ item.requester_name }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-neutral-400">سال</span>
                        <span class="font-bold">{{ item.model_year ? toPersianDigits(item.model_year) : '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-neutral-400">رنگ</span>
                        <span class="font-bold">{{ colorLabel(item.color) }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-neutral-400">بدنه</span>
                        <span class="font-bold">{{ item.body_condition_label || '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-neutral-400">سقف قیمت</span>
                        <span class="font-bold">{{ formatMoney(item.max_price) }}</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-neutral-400">تاریخ درخواست</span>
                        <span class="font-bold">{{ formatJalali(item.created_at) }}</span>
                    </div>
                    <p v-if="item.description" class="rounded-2xl bg-[#f4f4f5] p-3 text-xs leading-6 text-neutral-600 dark:bg-white/5 dark:text-neutral-300">
                        {{ item.description }}
                    </p>
                    <p v-else class="text-xs text-neutral-400">توضیحی ثبت نشده است.</p>
                    <span
                        v-if="item.can_reveal_contact"
                        class="mt-1 inline-flex rounded-full bg-neutral-900 px-4 py-2 text-xs font-bold text-white"
                        @click.stop="revealContact(item.id)"
                    >
                        نمایش تماس
                    </span>
                </div>
            </button>
        </div>

        <div v-if="requests.next_page_url || requests.prev_page_url" class="mt-4 flex justify-between text-xs font-bold">
            <Link v-if="requests.prev_page_url" :href="requests.prev_page_url">قبلی</Link>
            <Link v-if="requests.next_page_url" :href="requests.next_page_url">بعدی</Link>
        </div>
    </FeaturesLayout>
</template>
