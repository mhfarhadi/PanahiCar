<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { useAppearance } from '@/Composables/useAppearance';
import { publicAsset } from '@/Utils/publicAsset';
import { Head, Link } from '@inertiajs/vue3';

const {
    colorScheme,
    fontFamily,
    pendingFont,
    themeMode,
    fontApplied,
    hasPendingFont,
    colorPalettes,
    fontOptions,
    themeModes,
    setThemeMode,
    setColorScheme,
    selectFont,
    applySelectedFont,
} = useAppearance();
</script>

<template>
    <Head title="تنظیمات | Panahi Car" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5">
                    <h1 class="text-xl font-black">تنظیمات</h1>
                    <p class="mt-1 text-[11px] font-bold text-slate-400">
                        اشخاص و ظاهر برنامه
                    </p>
                </div>

                <div class="space-y-3">
                    <Link
                        v-if="$page.props.auth.user?.permissions?.includes('org.manage')"
                        :href="route('organization.index')"
                        class="am-row"
                    >
                        <div class="am-thumb !h-12 !w-12 text-lg">🏢</div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black">سازمان</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                شعب، کاربران و سطح دسترسی
                            </p>
                        </div>
                    </Link>

                    <Link
                        :href="route('features.index')"
                        class="am-row"
                    >
                        <div class="am-thumb !h-12 !w-12 overflow-hidden">
                            <img :src="publicAsset('/images/illustrations/illustration-showroom.png')" class="h-full w-full object-cover" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black">امکانات</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                اقساط، برآورد قیمت، قرارداد و بازار همکاران
                            </p>
                        </div>
                    </Link>

                    <Link
                        :href="route('contacts.index')"
                        class="am-row"
                    >
                        <div class="am-thumb !h-12 !w-12 text-lg">👤</div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black">اشخاص</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                همکاران، مشتریان و اشخاص عادی
                            </p>
                        </div>
                    </Link>

                    <div class="am-card !p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-black">حالت نمایش</h2>
                                <p class="mt-0.5 text-[11px] text-slate-400">روشن، تاریک یا مطابق سیستم</p>
                            </div>
                            <ThemeToggle />
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="mode in themeModes"
                                :key="mode.id"
                                type="button"
                                class="am-chip"
                                :class="{ 'am-chip-on': themeMode === mode.id }"
                                @click="setThemeMode(mode.id)"
                            >
                                {{ mode.label }}
                            </button>
                        </div>
                    </div>

                    <div class="am-card !p-4">
                        <h2 class="text-sm font-black">رنگ برنامه</h2>
                        <p class="mt-0.5 text-[11px] text-slate-400">پس‌زمینه و رنگ‌های اصلی</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button
                                v-for="palette in colorPalettes"
                                :key="palette.id"
                                type="button"
                                class="ph-settings-swatch"
                                :class="{ 'is-active': colorScheme === palette.id }"
                                :title="palette.label"
                                @click="setColorScheme(palette.id)"
                            >
                                <span
                                    class="absolute inset-0"
                                    :style="{ background: `linear-gradient(135deg, ${palette.swatch[0]}, ${palette.swatch[1]})` }"
                                />
                            </button>
                        </div>
                        <p class="mt-3 text-[11px] font-bold text-slate-500">
                            {{ colorPalettes.find((item) => item.id === colorScheme)?.label }}
                        </p>
                    </div>

                    <div class="am-card !p-4">
                        <h2 class="text-sm font-black">فونت</h2>
                        <p class="mt-0.5 text-[11px] text-slate-400">
                            یک فونت انتخاب کنید، سپس دکمه اعمال را بزنید
                        </p>
                        <div class="mt-4 space-y-2">
                            <button
                                v-for="font in fontOptions"
                                :key="font.id"
                                type="button"
                                class="ph-settings-font-card w-full"
                                :class="{ 'is-active': pendingFont === font.id }"
                                :style="{ fontFamily: font.family }"
                                @click="selectFont(font.id)"
                            >
                                <p class="text-base font-black leading-8">{{ font.sample }}</p>
                                <p class="mt-1 text-[11px] text-slate-400">{{ font.label }}</p>
                            </button>
                        </div>

                        <div class="mt-4 space-y-2">
                            <button
                                type="button"
                                class="am-btn-primary w-full"
                                :disabled="!hasPendingFont"
                                :class="{ 'opacity-50': !hasPendingFont }"
                                @click="applySelectedFont"
                            >
                                اعمال فونت
                            </button>
                            <p v-if="fontApplied" class="text-center text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                فونت «{{ fontOptions.find((item) => item.id === fontFamily)?.label }}» اعمال شد
                            </p>
                            <p v-else-if="hasPendingFont" class="text-center text-[11px] text-slate-400">
                                فونت انتخاب‌شده: {{ fontOptions.find((item) => item.id === pendingFont)?.label }}
                            </p>
                            <p v-else class="text-center text-[11px] text-slate-400">
                                فونت فعلی: {{ fontOptions.find((item) => item.id === fontFamily)?.label }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
