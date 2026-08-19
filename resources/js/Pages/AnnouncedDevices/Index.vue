<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    colorLabel,
    formatMileage,
    formatYear,
} from '@/Utils/vehicleLabels';
import { vehiclePhoto } from '@/Utils/carPhotos';

const props = defineProps({
    devices: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    optionLabels: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');

let searchTimer = null;

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('announced-devices.index'),
            { search: search.value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300);
});

const money = (value) => {
    if (value === null || value === undefined) return '—';
    return Number(value).toLocaleString('fa-IR') + ' تومان';
};
</script>

<template>
    <Head title="خودروهای اعلامی | Panahi Car" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-black">اعلامی</h1>
                        <p class="mt-1 text-[11px] font-bold text-slate-400">
                            {{ devices.length.toLocaleString('fa-IR') }} خودرو همکاران
                        </p>
                    </div>

                    <Link
                        :href="route('announced-devices.create')"
                        class="am-btn-primary !rounded-full !px-4 !py-2 text-xs"
                    >
                        + ثبت
                    </Link>
                </div>

                <div class="mb-4">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="برند، مدل، رنگ، VIN..."
                        autocomplete="off"
                        class="am-input"
                    />
                </div>

                <div
                    v-if="!devices.length"
                    class="am-soft py-12 text-center"
                >
                    <div class="text-4xl">🚗</div>
                    <h2 class="mt-3 text-sm font-black">خودرو اعلامی نداریم</h2>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="device in devices"
                        :key="device.id"
                        class="am-card !p-3"
                    >
                        <div class="flex items-center gap-3">
                            <div class="am-thumb">
                                <img
                                    :src="vehiclePhoto(device, device.id)"
                                    :alt="`${device.brand} ${device.model}`"
                                    class="h-full w-full object-cover"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-sm font-black">
                                    {{ device.brand }} {{ device.model }}
                                </h2>
                                <p class="mt-0.5 truncate text-[11px] text-slate-400">
                                    {{ formatYear(device.model_year) }} ·
                                    {{ formatMileage(device.mileage) }} ·
                                    {{ colorLabel(device.color) }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ device.announcer_name || '—' }}
                                </p>
                            </div>

                            <div class="shrink-0 text-left">
                                <p class="text-xs font-black">{{ money(device.announced_price) }}</p>
                                <p class="mt-1 text-[10px] font-bold text-amber-600">اعلامی</p>
                            </div>
                        </div>

                        <Link
                            :href="route('announced-devices.purchase.create', device.id)"
                            class="am-btn-primary mt-3 w-full !rounded-full !py-2.5 text-xs"
                        >
                            انتقال به موجودی
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
