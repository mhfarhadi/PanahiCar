<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const mobileMenuOpen = ref(false);

const THEME_KEY = 'maya_theme';
const isDark = ref(document.documentElement.classList.contains('dark'));

const setTheme = (value) => {
    localStorage.setItem(THEME_KEY, value);
    document.documentElement.classList.toggle('dark', value === 'dark');
    isDark.value = value === 'dark';
};

const navigation = [
    {
        label: 'داشبورد',
        href: route('dashboard'),
        pattern: 'dashboard',
        icon: 'M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z',
    },
    {
        label: 'موجودی',
        href: route('devices.index'),
        pattern: 'devices.*',
        icon: 'M4 7h16v13H4V7Zm2 0 2-3h8l2 3M8 11h8M8 15h5',
    },
    {
        label: 'ثبت دستگاه',
        href: route('devices.create'),
        pattern: 'devices.create',
        icon: 'M12 5v14M5 12h14',
    },
    {
        label: 'گوشی‌های اعلامی',
        href: route('announced-devices.index'),
        pattern: 'announced-devices.*',
        icon: 'M12 3l8 9-8 9-8-9 8-9Z',
    },
    {
        label: 'فروش گوشی',
        href: route('devices.index', { mode: 'sell' }),
        pattern: '__sell__',
        icon: 'M4 12h14m-5-5 5 5-5 5',
    },
    {
        label: 'فروش‌ها',
        href: route('sales.index'),
        pattern: 'sales.*',
        icon: 'M5 12l4 4L19 6',
    },
    {
        label: 'اشخاص',
        href: route('contacts.index'),
        pattern: 'contacts.*',
        icon: 'M9 11a4 4 0 100-8 4 4 0 000 8Zm8 10a6 6 0 00-12 0m13-10a3 3 0 100-6m1 9a5 5 0 00-4-5',
    },
    {
        label: 'برآورد قیمت',
        href: route('price-estimates.index'),
        pattern: 'price-estimates.*',
        icon: 'M11 18a7 7 0 100-14 7 7 0 000 14Zm5 0-4-4',
    },
    {
        label: 'تنظیمات',
        href: route('settings.index'),
        pattern: 'settings.*',
        icon: 'M12 8a4 4 0 100 8 4 4 0 000-8Zm0-5v2m0 14v2M3 12h2m14 0h2M5.6 5.6 7 7m10 10 1.4 1.4m0-12.8L17 7M7 17l-1.4 1.4',
    },
];

const isActive = (item) => {
    if (item.pattern === '__sell__') {
        return route().current('devices.index')
            && new URLSearchParams(window.location.search).get('mode') === 'sell';
    }

    if (item.pattern === 'devices.*') {
        return route().current('devices.*')
            && new URLSearchParams(window.location.search).get('mode') !== 'sell';
    }

    return route().current(item.pattern);
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};
</script>

<template>
    <div
        dir="rtl"
        class="min-h-screen bg-[#e8ecf2] text-slate-900 transition-colors dark:bg-[#070a10] dark:text-slate-100 lg:py-5 lg:pl-5 lg:pr-[116px]"
    >
        <!-- Desktop navigation rail -->
        <aside
            class="fixed inset-y-5 right-5 z-40 hidden w-[76px] flex-col items-center rounded-[28px] border border-white/70 bg-white/90 py-4 shadow-[0_24px_70px_rgba(32,41,58,0.10)] backdrop-blur-xl dark:border-white/5 dark:bg-[#11151d]/95 dark:shadow-none lg:flex"
        >
            <Link
                :href="route('dashboard')"
                class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-[#ff6d76] text-white shadow-[0_10px_24px_rgba(255,109,118,0.28)]"
                aria-label="مایاهمراه"
            >
                <ApplicationLogo class="h-6 w-6 fill-current text-white" />
            </Link>

            <div class="mb-3 h-px w-8 bg-slate-100 dark:bg-white/10" />

            <nav class="flex flex-1 flex-col items-center gap-1.5">
                <Link
                    v-for="item in navigation"
                    :key="item.label"
                    :href="item.href"
                    class="group relative flex h-11 w-11 items-center justify-center rounded-2xl transition duration-200"
                    :class="
                        isActive(item)
                            ? 'bg-[#fff0f1] text-[#ff5f6b] dark:bg-[#ff6d76]/15 dark:text-[#ff8189]'
                            : 'text-slate-400 hover:bg-slate-50 hover:text-slate-700 dark:text-slate-500 dark:hover:bg-white/5 dark:hover:text-slate-200'
                    "
                >
                    <span
                        v-if="isActive(item)"
                        class="absolute -right-[17px] h-5 w-1 rounded-l-full bg-[#ff6d76]"
                    />

                    <svg
                        viewBox="0 0 24 24"
                        class="h-[19px] w-[19px]"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path :d="item.icon" />
                    </svg>

                    <span
                        class="pointer-events-none absolute right-[calc(100%+12px)] z-50 whitespace-nowrap rounded-xl bg-slate-900 px-3 py-2 text-[11px] font-bold text-white opacity-0 shadow-lg transition group-hover:opacity-100 dark:bg-white dark:text-slate-900"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

            <!-- Quick theme switch -->
            <div
                class="mb-3 flex w-[58px] items-center justify-center gap-1 rounded-2xl bg-[#f3f5f8] p-1 dark:bg-white/5"
                aria-label="حالت نمایش"
            >
                <button
                    type="button"
                    class="flex h-6 w-6 items-center justify-center rounded-xl transition duration-200"
                    :class="
                        !isDark
                            ? 'bg-white text-[#e8a72f] shadow-[0_3px_10px_rgba(35,45,65,0.08)] dark:bg-white/10'
                            : 'text-slate-400 hover:text-[#e8a72f]'
                    "
                    title="حالت روشن"
                    aria-label="حالت روشن"
                    @click="setTheme('light')"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-[14px] w-[14px]"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="3.5" />
                        <path d="M12 2.5v2M12 19.5v2M4.5 12h-2M21.5 12h-2M5.3 5.3l1.4 1.4M17.3 17.3l1.4 1.4M18.7 5.3l-1.4 1.4M6.7 17.3l-1.4 1.4" />
                    </svg>
                </button>

                <button
                    type="button"
                    class="flex h-6 w-6 items-center justify-center rounded-xl transition duration-200"
                    :class="
                        isDark
                            ? 'bg-[#222934] text-[#9db7ff] shadow-[0_3px_10px_rgba(0,0,0,0.18)]'
                            : 'text-slate-400 hover:text-[#728bc7]'
                    "
                    title="حالت تاریک"
                    aria-label="حالت تاریک"
                    @click="setTheme('dark')"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-[14px] w-[14px]"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M20 15.2A8 8 0 118.8 4a6.5 6.5 0 0011.2 11.2Z" />
                    </svg>
                </button>
            </div>

            <div class="mb-3 h-px w-8 bg-slate-100 dark:bg-white/10" />

            <Dropdown align="left" width="48">
                <template #trigger>
                    <button
                        type="button"
                        class="mt-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f1f4f8] text-sm font-black text-slate-700 transition hover:bg-[#e8edf3] dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10"
                        :title="$page.props.auth.user.name"
                    >
                        {{ $page.props.auth.user.name?.slice(0, 1) }}
                    </button>
                </template>

                <template #content>
                    <div class="border-b border-slate-100 px-4 py-3 text-right dark:border-slate-700">
                        <div class="truncate text-sm font-black text-slate-800 dark:text-slate-100">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="mt-1 truncate text-[10px] text-slate-400" dir="ltr">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>

                    <DropdownLink :href="route('profile.edit')">
                        پروفایل
                    </DropdownLink>

                    <DropdownLink
                        :href="route('logout')"
                        method="post"
                        as="button"
                    >
                        خروج
                    </DropdownLink>
                </template>
            </Dropdown>
        </aside>

        <!-- Main application surface -->
        <div
            class="min-h-screen overflow-hidden bg-[#fbfbfa] dark:bg-[#0d1118] lg:min-h-[calc(100vh-40px)] lg:rounded-[34px] lg:border lg:border-white/80 lg:shadow-[0_28px_90px_rgba(35,45,65,0.10)] dark:lg:border-white/5 dark:lg:shadow-none"
        >
            <!-- Mobile top bar -->
            <header
                class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-slate-100/80 bg-[#fbfbfa]/90 px-4 backdrop-blur-xl dark:border-white/5 dark:bg-[#0d1118]/90 lg:hidden"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#ff6d76] text-white shadow-[0_8px_20px_rgba(255,109,118,0.25)]"
                    >
                        <ApplicationLogo class="h-5 w-5 fill-current" />
                    </div>

                    <div>
                        <div class="text-sm font-black">مایاهمراه</div>
                        <div class="text-[10px] text-slate-400">مدیریت خرید و فروش</div>
                    </div>
                </Link>

                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center gap-1 rounded-2xl bg-white p-1 shadow-[0_5px_18px_rgba(30,40,60,0.05)] dark:bg-white/5"
                    >
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-xl transition"
                            :class="
                                !isDark
                                    ? 'bg-[#fff7e6] text-[#e8a72f] dark:bg-white/10'
                                    : 'text-slate-400'
                            "
                            aria-label="حالت روشن"
                            title="حالت روشن"
                            @click="setTheme('light')"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="3.5" />
                                <path d="M12 2.5v2M12 19.5v2M4.5 12h-2M21.5 12h-2M5.3 5.3l1.4 1.4M17.3 17.3l1.4 1.4M18.7 5.3l-1.4 1.4M6.7 17.3l-1.4 1.4" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-xl transition"
                            :class="
                                isDark
                                    ? 'bg-[#242b36] text-[#a7bcff]'
                                    : 'text-slate-400'
                            "
                            aria-label="حالت تاریک"
                            title="حالت تاریک"
                            @click="setTheme('dark')"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M20 15.2A8 8 0 118.8 4a6.5 6.5 0 0011.2 11.2Z" />
                            </svg>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-[0_5px_18px_rgba(30,40,60,0.06)] dark:bg-white/5 dark:text-slate-300"
                        @click="mobileMenuOpen = true"
                        aria-label="باز کردن منو"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        >
                            <path d="M5 7h14M5 12h14M5 17h14" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Optional page heading -->
            <header
                v-if="$slots.header"
                class="border-b border-slate-100 bg-white/50 px-4 py-5 dark:border-white/5 dark:bg-white/[0.02] sm:px-6 lg:px-8"
            >
                <slot name="header" />
            </header>

            <main>
                <slot />
            </main>
        </div>

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-50 bg-slate-950/45 backdrop-blur-sm lg:hidden"
                @click="closeMobileMenu"
            />
        </Transition>

        <!-- Mobile drawer -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            leave-active-class="transition-transform duration-200 ease-in"
            enter-from-class="translate-x-full"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="mobileMenuOpen"
                class="fixed inset-y-0 right-0 z-[60] flex w-[86%] max-w-sm flex-col bg-[#fbfbfa] shadow-2xl dark:bg-[#11151d] lg:hidden"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-5 py-5 dark:border-white/5"
                >
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3"
                        @click="closeMobileMenu"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#ff6d76] text-white"
                        >
                            <ApplicationLogo class="h-6 w-6 fill-current" />
                        </div>

                        <div>
                            <div class="font-black">مایاهمراه</div>
                            <div class="mt-0.5 text-[11px] text-slate-400">
                                منوی دسترسی
                            </div>
                        </div>
                    </Link>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-lg text-slate-500 dark:bg-white/5 dark:text-slate-300"
                        @click="closeMobileMenu"
                        aria-label="بستن منو"
                    >
                        ×
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <div class="space-y-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.label"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold transition"
                            :class="
                                isActive(item)
                                    ? 'bg-[#fff0f1] text-[#ff5f6b] dark:bg-[#ff6d76]/15 dark:text-[#ff8189]'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5'
                            "
                            @click="closeMobileMenu"
                        >
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-[0_4px_14px_rgba(30,40,60,0.05)] dark:bg-white/5"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-[18px] w-[18px]"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path :d="item.icon" />
                                </svg>
                            </span>

                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </div>

                <div class="border-t border-slate-100 p-4 dark:border-white/5">
                    <div
                        class="mb-3 rounded-2xl bg-[#f1f4f8] p-4 dark:bg-white/5"
                    >
                        <div class="font-black">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div
                            class="mt-1 truncate text-xs text-slate-400"
                            dir="ltr"
                        >
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <Link
                            :href="route('profile.edit')"
                            class="rounded-xl bg-slate-100 px-3 py-2.5 text-center text-sm font-bold text-slate-600 dark:bg-white/5 dark:text-slate-300"
                            @click="closeMobileMenu"
                        >
                            پروفایل
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="rounded-xl bg-red-50 px-3 py-2.5 text-center text-sm font-bold text-red-600 dark:bg-red-950/30"
                        >
                            خروج
                        </Link>
                    </div>
                </div>
            </aside>
        </Transition>
    </div>
</template>
