<script setup>
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BRAND_EN, BRAND_FA } from '@/Utils/brand';

defineProps({
    title: { type: String, default: 'امکانات' },
    subtitle: { type: String, default: '' },
    home: { type: Boolean, default: false },
});

const page = usePage();
const loggedIn = computed(() => Boolean(page.props.auth?.user));
const homeHref = computed(() => (loggedIn.value ? route('dashboard') : route('cars.landing')));
const moreMenuOpen = ref(false);

const primaryTabs = [
    { label: 'اقساط', href: route('features.installments.index'), pattern: 'features.installments.*', tone: 'mint', icon: 'M5 7h14v11H5V7Zm2 3h10M8 13h5' },
    { label: 'برآورد', href: route('features.price-estimates.index'), pattern: 'features.price-estimates.*', tone: 'sky', icon: 'M4 19V11M10 19V5M16 19v-9' },
    { label: 'طلا', href: route('features.gold-collateral.index'), pattern: 'features.gold-collateral.*', tone: 'sun', icon: 'm7 9 2-5h6l2 5 3 9H4l3-9Z' },
    { label: 'بیشتر', pattern: 'more', tone: 'more', icon: 'M6 12h.01M12 12h.01M18 12h.01', isMore: true },
];

const moreMenuItems = [
    { label: 'قرارداد', href: route('features.contracts.index'), pattern: 'features.contracts.*', icon: 'M7 3h7l4 4v14H7V3Zm7 0v5h4' },
    { label: 'می‌خوام', href: route('features.wanted.index'), pattern: 'features.wanted.index', icon: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM12 8v8M8 12h8' },
    { label: 'می‌خوان', href: route('features.wanted-market.index'), pattern: 'features.wanted-market.*', icon: 'M4 8h16l-1-4H5L4 8Zm1 0v12h14V8' },
    { label: 'چک', href: route('features.check-printer.index'), pattern: 'features.check-printer.*', icon: 'M7 9V4h10v5M7 17H5a2 2 0 0 1-2-2v-4h18v4a2 2 0 0 1-2 2h-2' },
];

const desktopNav = [
    ...primaryTabs.filter((item) => !item.isMore),
    ...moreMenuItems,
];

const tabToneClass = (tone, active) => {
    if (!active) return '';
    const map = {
        mint: 'bg-gradient-to-br from-green-300 to-green-400 text-green-950',
        sky: 'bg-gradient-to-br from-sky-300 to-blue-400 text-blue-950',
        sun: 'bg-gradient-to-br from-yellow-300 to-amber-400 text-amber-950',
        more: 'bg-gradient-to-br from-slate-300 to-slate-400 text-slate-900',
    };
    return map[tone] || 'bg-slate-900 text-white';
};

const isActive = (item) => route().current(item.pattern);
const isMoreActive = computed(() => moreMenuItems.some((item) => isActive(item)));
const closeMoreMenu = () => { moreMenuOpen.value = false; };
const toggleMoreMenu = () => { moreMenuOpen.value = !moreMenuOpen.value; };
</script>

<template>
    <div dir="rtl" class="ph-features-shell text-slate-900 dark:text-slate-100">
        <header class="ph-header !static lg:sticky">
            <div class="flex items-center gap-2 lg:absolute lg:right-4">
                <ThemeToggle />
                <Link :href="homeHref" class="am-icon-btn !h-10 !w-10 lg:hidden">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M11 6l-6 6 6 6" />
                </svg>
                </Link>
            </div>
            <div class="text-center">
                <p class="text-[16px] font-black tracking-tight">{{ title }}</p>
                <p v-if="subtitle" class="text-[11px] text-slate-400">{{ subtitle }}</p>
                <p v-else class="text-[10px] font-semibold text-slate-400">{{ BRAND_FA }} · {{ BRAND_EN }}</p>
            </div>
            <Link
                :href="route('features.index')"
                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-black text-white ph-brand-mark lg:hidden"
            >
                پ
            </Link>
            <Link
                :href="homeHref"
                class="absolute right-4 hidden rounded-full bg-slate-900 px-3 py-2 text-[11px] font-black text-white lg:inline-flex"
            >
                سایت اصلی
            </Link>
        </header>

        <nav class="mx-auto hidden max-w-3xl flex-wrap gap-2 px-4 pb-3 lg:flex">
            <Link
                v-for="tool in desktopNav"
                :key="tool.label"
                :href="tool.href"
                class="rounded-full px-4 py-2 text-[11px] font-bold shadow-sm"
                :class="isActive(tool) ? 'bg-slate-900 text-white' : 'bg-white/90 text-slate-500'"
            >
                {{ tool.label }}
            </Link>
        </nav>

        <main class="mx-auto max-w-md px-5 pb-[calc(6.8rem+env(safe-area-inset-bottom))] lg:pb-10">
            <slot />
        </main>

        <nav class="ph-mobile-bar lg:hidden" aria-label="امکانات">
            <div class="flex items-stretch justify-around">
                <Link
                    :href="homeHref"
                    class="ph-mobile-tab ph-mobile-tab--home"
                    @click="closeMoreMenu"
                >
                    <span class="ph-mobile-tab__icon bg-slate-900 text-white">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 11 12 4l8 7v9H4v-9Z" />
                        </svg>
                    </span>
                    سایت
                </Link>

                <template v-for="tab in primaryTabs" :key="tab.label">
                    <button
                        v-if="tab.isMore"
                        type="button"
                        class="ph-mobile-tab"
                        :class="[`ph-mobile-tab--${tab.tone}`, { 'is-active': isMoreActive || moreMenuOpen }]"
                        @click="toggleMoreMenu"
                    >
                        <span class="ph-mobile-tab__icon" :class="tabToneClass(tab.tone, isMoreActive || moreMenuOpen)">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path :d="tab.icon" />
                            </svg>
                        </span>
                        {{ tab.label }}
                    </button>
                    <Link
                        v-else
                        :href="tab.href"
                        class="ph-mobile-tab"
                        :class="[`ph-mobile-tab--${tab.tone}`, { 'is-active': isActive(tab) }]"
                        @click="closeMoreMenu"
                    >
                        <span class="ph-mobile-tab__icon" :class="tabToneClass(tab.tone, isActive(tab))">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path :d="tab.icon" />
                            </svg>
                        </span>
                        {{ tab.label }}
                    </Link>
                </template>
            </div>
        </nav>

        <div v-if="moreMenuOpen" class="fixed inset-0 z-[55] bg-slate-950/20 lg:hidden" @click="closeMoreMenu" />

        <div
            v-if="moreMenuOpen"
            class="fixed inset-x-4 bottom-[calc(5.75rem+env(safe-area-inset-bottom))] z-[60] rounded-[32px] border border-white/90 bg-white/95 p-3 shadow-[0_24px_60px_rgba(15,23,42,0.12)] backdrop-blur dark:border-white/10 dark:bg-slate-900/95 lg:hidden"
        >
            <Link
                v-for="item in moreMenuItems"
                :key="item.label"
                :href="item.href"
                class="mb-1 flex items-center gap-3 rounded-[22px] px-3 py-3 text-sm font-semibold last:mb-0"
                :class="isActive(item) ? 'bg-slate-900 text-white' : 'text-slate-700 dark:text-slate-200'"
                @click="closeMoreMenu"
            >
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path :d="item.icon" />
                </svg>
                {{ item.label }}
            </Link>
        </div>
    </div>
</template>
