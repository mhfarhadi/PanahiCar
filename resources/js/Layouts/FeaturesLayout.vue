<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: 'امکانات' },
    subtitle: { type: String, default: '' },
    home: { type: Boolean, default: false },
});

const page = usePage();
const loggedIn = computed(() => Boolean(page.props.auth?.user));
const homeHref = computed(() => (loggedIn.value ? route('dashboard') : route('login')));
const moreMenuOpen = ref(false);

const primaryTabs = [
    { label: 'اقساط', href: route('features.installments.index'), pattern: 'features.installments.*', icon: 'M5 7h14v11H5V7Zm2 3h10M8 13h5' },
    { label: 'برآورد', href: route('features.price-estimates.index'), pattern: 'features.price-estimates.*', icon: 'M4 19V11M10 19V5M16 19v-9' },
    { label: 'طلا', href: route('features.gold-collateral.index'), pattern: 'features.gold-collateral.*', icon: 'm7 9 2-5h6l2 5 3 9H4l3-9Z' },
    { label: 'بیشتر', pattern: 'more', icon: 'M6 12h.01M12 12h.01M18 12h.01', isMore: true },
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

const isActive = (item) => route().current(item.pattern);
const isMoreActive = computed(() => moreMenuItems.some((item) => isActive(item)));
const closeMoreMenu = () => { moreMenuOpen.value = false; };
const toggleMoreMenu = () => { moreMenuOpen.value = !moreMenuOpen.value; };
</script>

<template>
    <div dir="rtl" class="am-features-shell min-h-screen bg-[#f4f4f5] text-neutral-900 dark:bg-[#0b0b0c] dark:text-neutral-100">
        <header class="sticky top-0 z-30 bg-[#f4f4f5]/90 px-4 pb-3 pt-[max(0.9rem,env(safe-area-inset-top))] backdrop-blur dark:bg-[#0b0b0c]/90">
            <div class="relative mx-auto flex max-w-3xl items-center justify-center">
                <Link
                    :href="homeHref"
                    class="absolute right-0 hidden rounded-full bg-neutral-900 px-3 py-2 text-[11px] font-black text-white lg:inline-flex"
                >
                    سایت اصلی
                </Link>
                <div class="text-center">
                    <p class="text-[16px] font-black tracking-tight">{{ title }}</p>
                    <p v-if="subtitle" class="text-[11px] text-neutral-400">{{ subtitle }}</p>
                </div>
                <Link
                    :href="route('features.index')"
                    class="absolute left-0 hidden h-10 w-10 items-center justify-center rounded-full bg-white text-sm font-black shadow-sm dark:bg-[#161618] lg:flex"
                >
                    آ
                </Link>
            </div>
        </header>

        <nav class="mx-auto hidden max-w-3xl flex-wrap gap-2 px-4 pb-3 lg:flex">
            <Link
                v-for="tool in desktopNav"
                :key="tool.label"
                :href="tool.href"
                class="rounded-full px-3 py-2 text-[11px] font-bold"
                :class="isActive(tool) ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-500 dark:bg-[#161618]'"
            >
                {{ tool.label }}
            </Link>
        </nav>

        <main class="mx-auto max-w-md px-5 pb-[calc(6.8rem+env(safe-area-inset-bottom))] lg:pb-10">
            <slot />
        </main>

        <nav
            class="fixed inset-x-4 bottom-[max(0.85rem,env(safe-area-inset-bottom))] z-50 mx-auto max-w-md rounded-[28px] bg-white px-2 py-2 shadow-[0_18px_50px_rgba(0,0,0,0.10)] dark:bg-[#161618] lg:hidden"
            aria-label="امکانات"
        >
            <div class="flex items-stretch justify-around">
                <Link
                    :href="homeHref"
                    class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-2xl py-2 text-[10px] font-semibold text-neutral-400"
                    @click="closeMoreMenu"
                >
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-900 text-white">
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
