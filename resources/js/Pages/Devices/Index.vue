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
const sellMode = props.filters.mode === 'sell';

let searchTimer = null;

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            route('devices.index'),
            { search: search.value, mode: sellMode ? 'sell' : undefined },
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

    return `${Number(value).toLocaleString('fa-IR')} تومان`;
};
</script>

<template>
    <Head title="موجودی خودرو | automaya" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-black">
                            {{ sellMode ? 'فروش خودرو' : 'موجودی' }}
                        </h1>
                        <p class="mt-1 text-[11px] font-bold text-slate-400">
                            {{ devices.length.toLocaleString('fa-IR') }} خودرو
                        </p>
                    </div>

                    <Link
                        v-if="!sellMode"
                        :href="route('devices.create')"
                        class="am-btn-primary !rounded-full !px-4 !py-2 text-xs"
                    >
                        + ثبت
                    </Link>
                </div>

                <div
                    v-if="sellMode"
                    class="am-accent-soft mb-4 rounded-2xl px-4 py-3 text-xs font-bold"
                >
                    با انتخاب هر خودرو به ثبت فروش می‌روید.
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
                    <h2 class="mt-3 text-sm font-black">موجودی خالی است</h2>
                </div>

                <div v-else class="space-y-3">
                    <Link
                        v-for="device in devices"
                        :key="device.id"
                        :href="sellMode
                            ? route('sales.create', device.id)
                            : route('devices.show', device.id)"
                        class="am-row"
                    >
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
                        </div>

                        <div class="shrink-0 text-left">
                            <p class="text-xs font-black">
                                {{ money(device.suggested_sale_price || device.purchase_price) }}
                            </p>
                            <p class="mt-1 text-[10px] font-bold text-slate-400">
                                {{ sellMode ? 'فروش' : 'موجود' }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
