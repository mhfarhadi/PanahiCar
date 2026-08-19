<script setup>
import { computed, ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BRAND_EN, BRAND_FA, THEME_KEY } from '@/Utils/brand';

const isDark = ref(document.documentElement.classList.contains('dark'));
const moreMenuOpen = ref(false);
const page = usePage();

const setTheme = (value) => {
    localStorage.setItem(THEME_KEY, value);
    document.documentElement.classList.toggle('dark', value === 'dark');
    isDark.value = value === 'dark';
};

const navigation = [
    { label: 'خانه', href: route('dashboard'), pattern: 'dashboard', tone: 'home', icon: 'M4 11.5 12 4l8 7.5M7 10v9h10v-9' },
    { label: 'موجودی', href: route('devices.index'), pattern: 'devices.*', tone: 'inventory', icon: 'M5 16h14l-1.4-5H6.4L5 16Zm3-8h8M7.5 16.5a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Zm9 0a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z' },
    { label: 'اعلامی', href: route('announced-devices.index'), pattern: 'announced-devices.*', tone: 'announced', icon: 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
    { label: 'فروش', href: route('sales.index'), pattern: 'sales.*', tone: 'sales', icon: 'M5 12.5 9.5 17 19 7' },
    { label: 'امکانات', href: route('features.index'), pattern: 'features.*', tone: 'features', icon: 'M4 7h7v10H4V7Zm9 0h7v4h-7V7Zm0 6h7v4h-7v-4Z' },
    { label: 'اقساط', href: route('installments.index'), pattern: 'installments.*', tone: 'installments', icon: 'M5 7h14v11H5V7Zm2 3h10M8 13h5' },
    { label: 'اشخاص', href: route('contacts.index'), pattern: 'contacts.*', tone: 'contacts', icon: 'M12 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm7 9a7 7 0 0 0-14 0' },
    { label: 'تنظیمات', href: route('settings.index'), pattern: 'settings.*', tone: 'settings', icon: 'M12 15.2A3.2 3.2 0 1 0 12 8.8a3.2 3.2 0 0 0 0 6.4ZM12 3.5v1.6M12 18.9v1.6M4.7 6.4l1.1 1.1M18.2 16.5l1.1 1.1M3.5 12h1.6M18.9 12h1.6M4.7 17.6l1.1-1.1M18.2 7.5l1.1-1.1' },
];

const mobileTabs = [
    { label: 'خانه', href: route('dashboard'), pattern: 'dashboard', tone: 'home', icon: 'M4 11.5 12 4l8 7.5M7 10v9h10v-9' },
    { label: 'موجودی', href: route('devices.index'), pattern: 'devices.*', tone: 'inventory', icon: 'M5 16h14l-1.4-5H6.4L5 16Zm3-8h8M7.5 16.5a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Zm9 0a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z' },
    { label: 'فروش', href: route('sales.index'), pattern: 'sales.*', tone: 'sales', icon: 'M5 12.5 9.5 17 19 7' },
    { label: 'امکانات', href: route('features.index'), pattern: 'features.*', tone: 'features', icon: 'M4 7h7v10H4V7Zm9 0h7v4h-7V7Zm0 6h7v4h-7v-4Z' },
    { label: 'بیشتر', pattern: 'more', tone: 'more', icon: 'M6 12h.01M12 12h.01M18 12h.01', isMore: true },
];

const moreMenuItems = [
    { label: 'اعلامی', href: route('announced-devices.index'), pattern: 'announced-devices.*', tone: 'announced', icon: 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
    { label: 'اقساط', href: route('installments.index'), pattern: 'installments.*', tone: 'installments', icon: 'M5 7h14v11H5V7Zm2 3h10M8 13h5' },
    { label: 'اشخاص', href: route('contacts.index'), pattern: 'contacts.*', tone: 'contacts', icon: 'M12 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm7 9a7 7 0 0 0-14 0' },
    { label: 'تنظیمات', href: route('settings.index'), pattern: 'settings.*', tone: 'settings', icon: 'M12 15.2A3.2 3.2 0 1 0 12 8.8a3.2 3.2 0 0 0 0 6.4ZM12 3.5v1.6M12 18.9v1.6M4.7 6.4l1.1 1.1M18.2 16.5l1.1 1.1M3.5 12h1.6M18.9 12h1.6M4.7 17.6l1.1-1.1M18.2 7.5l1.1-1.1' },
];

const isActive = (item) => route().current(item.pattern);
const isMoreActive = computed(() => moreMenuItems.some((item) => isActive(item)));
const closeMoreMenu = () => { moreMenuOpen.value = false; };
const toggleMoreMenu = () => { moreMenuOpen.value = !moreMenuOpen.value; };

const firstName = computed(() => {
    const name = page.props.auth?.user?.name || '';
    return name.split(' ')[0] || 'کاربر';
});
</script>

<template>
    <div dir="rtl" class="ph-app-shell">
        <aside class="ph-desktop-rail">
            <Link
                :href="route('dashboard')"
                class="mb-6 flex h-12 w-12 items-center justify-center rounded-full text-sm font-black text-white shadow-lg"
                style="background: linear-gradient(135deg, #86efac, #60a5fa);"
                :title="BRAND_EN"
            >
                پ
            </Link>

            <nav class="flex flex-1 flex-col items-center gap-2">
                <Link
                    v-for="item in navigation"
                    :key="item.label"
                    :href="item.href"
                    class="ph-nav-item group"
                    :class="[`ph-nav-item--${item.tone}`, { 'is-active': isActive(item) }]"
                >
                    <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="item.icon" />
                    </svg>
                    <span class="pointer-events-none absolute right-[calc(100%+12px)] z-50 whitespace-nowrap rounded-full bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-slate-900 dark:text-slate-100">
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

            <button type="button" class="am-icon-btn mb-3" @click="setTheme(isDark ? 'light' : 'dark')">
                {{ isDark ? '☀' : '☾' }}
            </button>

            <Dropdown align="left" width="48" placement="top">
                <template #trigger>
                    <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">
                        {{ $page.props.auth.user.name?.slice(0, 1) }}
                    </button>
                </template>
                <template #content>
                    <DropdownLink :href="route('profile.edit')">پروفایل</DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">خروج</DropdownLink>
                </template>
            </Dropdown>
        </aside>

        <div class="lg:pr-[108px]">
            <header class="ph-header">
                <div>
                    <p class="text-[11px] font-medium text-slate-400">سلام، {{ firstName }}</p>
                    <p class="text-[17px] font-black tracking-tight">{{ BRAND_FA }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="am-icon-btn" @click="setTheme(isDark ? 'light' : 'dark')">
                        {{ isDark ? '☀' : '☾' }}
                    </button>
                    <Dropdown align="left" width="48">
                        <template #trigger>
                            <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">
                                {{ $page.props.auth.user.name?.slice(0, 1) }}
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">پروفایل</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">خروج</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main class="pb-[calc(6.5rem+env(safe-area-inset-bottom))] lg:pb-8">
                <slot />
            </main>
        </div>

        <nav class="ph-mobile-bar lg:hidden" aria-label="ناوبری اصلی">
            <div class="flex items-stretch justify-around">
                <template v-for="tab in mobileTabs" :key="tab.label">
                    <button
                        v-if="tab.isMore"
                        type="button"
                        class="ph-mobile-tab"
                        :class="[`ph-mobile-tab--${tab.tone}`, { 'is-active': isMoreActive || moreMenuOpen }]"
                        @click="toggleMoreMenu"
                    >
                        <span class="ph-mobile-tab__icon">
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
                        <span class="ph-mobile-tab__icon">
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
