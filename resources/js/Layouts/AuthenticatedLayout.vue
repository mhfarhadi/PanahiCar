<script setup>
import { computed, ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const THEME_KEY = 'automaya_theme';
const isDark = ref(document.documentElement.classList.contains('dark'));
const moreMenuOpen = ref(false);
const page = usePage();

const setTheme = (value) => {
    localStorage.setItem(THEME_KEY, value);
    document.documentElement.classList.toggle('dark', value === 'dark');
    isDark.value = value === 'dark';
};

const navigation = [
    { label: 'خانه', href: route('dashboard'), pattern: 'dashboard', icon: 'M4 11.5 12 4l8 7.5M7 10v9h10v-9' },
    { label: 'موجودی', href: route('devices.index'), pattern: 'devices.*', icon: 'M5 16h14l-1.4-5H6.4L5 16Zm3-8h8M7.5 16.5a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Zm9 0a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z' },
    { label: 'اعلامی', href: route('announced-devices.index'), pattern: 'announced-devices.*', icon: 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
    { label: 'فروش', href: route('sales.index'), pattern: 'sales.*', icon: 'M5 12.5 9.5 17 19 7' },
    { label: 'امکانات', href: route('features.index'), pattern: 'features.*', icon: 'M4 7h7v10H4V7Zm9 0h7v4h-7V7Zm0 6h7v4h-7v-4Z' },
    { label: 'اقساط', href: route('installments.index'), pattern: 'installments.*', icon: 'M5 7h14v11H5V7Zm2 3h10M8 13h5' },
    { label: 'اشخاص', href: route('contacts.index'), pattern: 'contacts.*', icon: 'M12 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm7 9a7 7 0 0 0-14 0' },
    { label: 'تنظیمات', href: route('settings.index'), pattern: 'settings.*', icon: 'M12 15.2A3.2 3.2 0 1 0 12 8.8a3.2 3.2 0 0 0 0 6.4ZM12 3.5v1.6M12 18.9v1.6M4.7 6.4l1.1 1.1M18.2 16.5l1.1 1.1M3.5 12h1.6M18.9 12h1.6M4.7 17.6l1.1-1.1M18.2 7.5l1.1-1.1' },
];

const mobileTabs = [
    { label: 'خانه', href: route('dashboard'), pattern: 'dashboard', icon: 'M4 11.5 12 4l8 7.5M7 10v9h10v-9' },
    { label: 'موجودی', href: route('devices.index'), pattern: 'devices.*', icon: 'M5 16h14l-1.4-5H6.4L5 16Zm3-8h8M7.5 16.5a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Zm9 0a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0Z' },
    { label: 'فروش', href: route('sales.index'), pattern: 'sales.*', icon: 'M5 12.5 9.5 17 19 7' },
    { label: 'امکانات', href: route('features.index'), pattern: 'features.*', icon: 'M4 7h7v10H4V7Zm9 0h7v4h-7V7Zm0 6h7v4h-7v-4Z' },
    { label: 'بیشتر', pattern: 'more', icon: 'M6 12h.01M12 12h.01M18 12h.01', isMore: true },
];

const moreMenuItems = [
    { label: 'اعلامی', href: route('announced-devices.index'), pattern: 'announced-devices.*', icon: 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
    { label: 'اقساط', href: route('installments.index'), pattern: 'installments.*', icon: 'M5 7h14v11H5V7Zm2 3h10M8 13h5' },
    { label: 'اشخاص', href: route('contacts.index'), pattern: 'contacts.*', icon: 'M12 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm7 9a7 7 0 0 0-14 0' },
    { label: 'تنظیمات', href: route('settings.index'), pattern: 'settings.*', icon: 'M12 15.2A3.2 3.2 0 1 0 12 8.8a3.2 3.2 0 0 0 0 6.4ZM12 3.5v1.6M12 18.9v1.6M4.7 6.4l1.1 1.1M18.2 16.5l1.1 1.1M3.5 12h1.6M18.9 12h1.6M4.7 17.6l1.1-1.1M18.2 7.5l1.1-1.1' },
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
    <div dir="rtl" class="min-h-screen bg-[#f4f4f5] text-neutral-900 transition-colors dark:bg-[#0b0b0c] dark:text-neutral-100">
        <aside
            class="fixed inset-y-4 right-4 z-40 hidden w-[76px] flex-col items-center rounded-[28px] bg-white py-5 shadow-[0_18px_50px_rgba(0,0,0,0.06)] dark:bg-[#161618] lg:flex"
        >
            <Link
                :href="route('dashboard')"
                class="mb-6 flex h-11 w-11 items-center justify-center rounded-full bg-neutral-900 text-sm font-black text-white"
                title="automaya"
            >
                آ
            </Link>

            <nav class="flex flex-1 flex-col items-center gap-2">
                <Link
                    v-for="item in navigation"
                    :key="item.label"
                    :href="item.href"
                    class="group relative flex h-11 w-11 items-center justify-center rounded-full transition"
                    :class="isActive(item) ? 'bg-neutral-900 text-white' : 'text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/5'"
                >
                    <svg viewBox="0 0 24 24" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="item.icon" />
                    </svg>
                    <span class="pointer-events-none absolute right-[calc(100%+12px)] z-50 whitespace-nowrap rounded-full bg-neutral-900 px-3 py-1.5 text-[11px] font-semibold text-white opacity-0 shadow-lg transition group-hover:opacity-100">
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

            <button type="button" class="am-icon-btn mb-3" @click="setTheme(isDark ? 'light' : 'dark')">
                {{ isDark ? '☀' : '☾' }}
            </button>

            <Dropdown align="left" width="48" placement="top">
                <template #trigger>
                    <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-900 text-sm font-bold text-white">
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
            <header class="sticky top-0 z-40 flex items-center justify-between bg-[#f4f4f5]/90 px-5 pb-3 pt-[max(0.9rem,env(safe-area-inset-top))] backdrop-blur dark:bg-[#0b0b0c]/90 lg:hidden">
                <div>
                    <p class="text-[11px] font-medium text-neutral-400">سلام، {{ firstName }}</p>
                    <p class="text-[17px] font-black tracking-tight">automaya</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="am-icon-btn" @click="setTheme(isDark ? 'light' : 'dark')">
                        {{ isDark ? '☀' : '☾' }}
                    </button>
                    <Dropdown align="left" width="48">
                        <template #trigger>
                            <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-900 text-sm font-bold text-white">
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

        <nav
            class="fixed inset-x-4 bottom-[max(0.85rem,env(safe-area-inset-bottom))] z-50 mx-auto max-w-md rounded-[28px] bg-white px-2 py-2 shadow-[0_18px_50px_rgba(0,0,0,0.10)] dark:bg-[#161618] lg:hidden"
            aria-label="ناوبری اصلی"
        >
            <div class="flex items-stretch justify-around">
                <template v-for="tab in mobileTabs" :key="tab.label">
                    <button
                        v-if="tab.isMore"
                        type="button"
                        class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-2xl py-2 text-[10px] font-semibold"
                        :class="isMoreActive || moreMenuOpen ? 'text-neutral-900 dark:text-white' : 'text-neutral-400'"
                        @click="toggleMoreMenu"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full"
                            :class="isMoreActive || moreMenuOpen ? 'bg-neutral-900 text-white' : ''"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path :d="tab.icon" />
                            </svg>
                        </span>
                        {{ tab.label }}
                    </button>
                    <Link
                        v-else
                        :href="tab.href"
                        class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-2xl py-2 text-[10px] font-semibold"
                        :class="isActive(tab) ? 'text-neutral-900 dark:text-white' : 'text-neutral-400'"
                        @click="closeMoreMenu"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full"
                            :class="isActive(tab) ? 'bg-neutral-900 text-white' : ''"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path :d="tab.icon" />
                            </svg>
                        </span>
                        {{ tab.label }}
                    </Link>
                </template>
            </div>
        </nav>

        <div v-if="moreMenuOpen" class="fixed inset-0 z-[55] bg-neutral-950/20 lg:hidden" @click="closeMoreMenu" />

        <div
            v-if="moreMenuOpen"
            class="fixed inset-x-4 bottom-[calc(5.75rem+env(safe-area-inset-bottom))] z-[60] rounded-[28px] bg-white p-3 shadow-[0_18px_50px_rgba(0,0,0,0.10)] dark:bg-[#161618] lg:hidden"
        >
            <Link
                v-for="item in moreMenuItems"
                :key="item.label"
                :href="item.href"
                class="mb-1 flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold last:mb-0"
                :class="isActive(item) ? 'bg-neutral-900 text-white' : 'text-neutral-700 dark:text-neutral-200'"
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
