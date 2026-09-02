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
const homeHref = computed(() => (loggedIn.value ? route('dashboard') : route('home')));
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
    <div dir="rtl" class="ph-rx-features">
        <header class="ph-rx-features__header">
            <div class="ph-rx-features__header-start">
                <ThemeToggle />
                <Link :href="homeHref" class="ph-rx-ghost-btn lg:hidden">
                    صفحه اصلی
                </Link>
            </div>

            <div class="ph-rx-features__header-center">
                <p class="ph-rx-features__title">{{ title }}</p>
                <p class="ph-rx-features__subtitle">
                    {{ subtitle || `${BRAND_FA} · ${BRAND_EN}` }}
                </p>
            </div>

            <Link :href="homeHref" class="ph-rx-btn ph-rx-btn--dark ph-rx-btn--sm hidden lg:inline-flex">
                {{ loggedIn ? 'داشبورد' : 'صفحه اصلی' }}
            </Link>
        </header>

        <nav class="ph-rx-features__nav hidden lg:flex">
            <Link
                v-for="tool in desktopNav"
                :key="tool.label"
                :href="tool.href"
                class="ph-rx-pill"
                :class="{ 'is-active': isActive(tool) }"
            >
                {{ tool.label }}
            </Link>
        </nav>

        <main class="ph-rx-features__main">
            <slot />
        </main>

        <nav class="ph-rx-features__bar lg:hidden" aria-label="امکانات">
            <Link :href="homeHref" class="ph-rx-features__tab" @click="closeMoreMenu">
                <span class="ph-rx-features__tab-icon is-home">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 11 12 4l8 7v9H4v-9Z" />
                    </svg>
                </span>
                خانه
            </Link>

            <template v-for="tab in primaryTabs" :key="tab.label">
                <button
                    v-if="tab.isMore"
                    type="button"
                    class="ph-rx-features__tab"
                    :class="{ 'is-active': isMoreActive || moreMenuOpen }"
                    @click="toggleMoreMenu"
                >
                    <span class="ph-rx-features__tab-icon">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="tab.icon" />
                        </svg>
                    </span>
                    {{ tab.label }}
                </button>
                <Link
                    v-else
                    :href="tab.href"
                    class="ph-rx-features__tab"
                    :class="{ 'is-active': isActive(tab) }"
                    @click="closeMoreMenu"
                >
                    <span class="ph-rx-features__tab-icon">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="tab.icon" />
                        </svg>
                    </span>
                    {{ tab.label }}
                </Link>
            </template>
        </nav>

        <div v-if="moreMenuOpen" class="ph-rx-features__overlay lg:hidden" @click="closeMoreMenu" />

        <div v-if="moreMenuOpen" class="ph-rx-features__sheet lg:hidden">
            <Link
                v-for="item in moreMenuItems"
                :key="item.label"
                :href="item.href"
                class="ph-rx-features__sheet-item"
                :class="{ 'is-active': isActive(item) }"
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
