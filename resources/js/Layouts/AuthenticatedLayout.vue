<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const mobileMenuOpen = ref(false);

const navigation = [
    {
        label: 'داشبورد',
        href: route('dashboard'),
        pattern: 'dashboard',
        icon: '⌂',
    },
    {
        label: 'موجودی',
        href: route('devices.index'),
        pattern: 'devices.*',
        icon: '▣',
    },
    {
        label: 'ثبت دستگاه',
        href: route('devices.create'),
        pattern: 'devices.create',
        icon: '+',
    },
    {
        label: 'گوشی‌های اعلامی',
        href: route('announced-devices.index'),
        pattern: 'announced-devices.*',
        icon: '◇',
    },
    {
        label: 'فروش گوشی',
        href: route('devices.index', { mode: 'sell' }),
        pattern: '__sell__',
        icon: '＋',
    },
    {
        label: 'فروش‌ها',
        href: route('sales.index'),
        pattern: 'sales.*',
        icon: '✓',
    },
    {
        label: 'اشخاص',
        href: route('contacts.index'),
        pattern: 'contacts.*',
        icon: '◉',
    },
    {
        label: 'تنظیمات',
        href: route('settings.index'),
        pattern: 'settings.*',
        icon: '⚙',
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
    <div dir="rtl" class="min-h-screen bg-slate-100 dark:bg-slate-950">
        <!-- Desktop top navigation -->
        <nav
            class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <!-- Brand -->
                    <div class="flex min-w-0 items-center gap-3">
                        <Link
                            :href="route('dashboard')"
                            class="flex shrink-0 items-center gap-2"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-600 text-white"
                            >
                                <ApplicationLogo
                                    class="h-6 w-6 fill-current text-white"
                                />
                            </div>

                            <div class="hidden xl:block">
                                <div class="text-sm font-black text-slate-900 dark:text-white">
                                    مایاهمراه
                                </div>
                                <div class="text-[10px] font-medium text-slate-400">
                                    مدیریت خرید و فروش
                                </div>
                            </div>
                        </Link>

                        <!-- Desktop links -->
                        <div
                            class="hidden items-center gap-1 lg:flex"
                        >
                            <Link
                                v-for="item in navigation"
                                :key="item.label"
                                :href="item.href"
                                class="rounded-xl px-3 py-2 text-sm font-bold transition"
                                :class="
                                    isActive(item)
                                        ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300'
                                        : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                                "
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <!-- Desktop user -->
                    <div class="hidden items-center lg:flex">
                        <Dropdown align="left" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 transition hover:border-violet-300 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                >
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-100 text-sm font-black text-violet-700 dark:bg-violet-950 dark:text-violet-300"
                                    >
                                        {{ $page.props.auth.user.name?.slice(0, 1) }}
                                    </span>

                                    <span class="max-w-32 truncate">
                                        {{ $page.props.auth.user.name }}
                                    </span>

                                    <span class="text-xs text-slate-400">⌄</span>
                                </button>
                            </template>

                            <template #content>
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
                    </div>

                    <!-- Mobile hamburger -->
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-xl text-slate-600 lg:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                        @click="mobileMenuOpen = true"
                        aria-label="باز کردن منو"
                    >
                        ☰
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm lg:hidden"
                @click="closeMobileMenu"
            />
        </Transition>

        <!-- Mobile side drawer -->
        <Transition
            enter-active-class="transition-transform duration-200 ease-out"
            leave-active-class="transition-transform duration-200 ease-in"
            enter-from-class="translate-x-full"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="mobileMenuOpen"
                class="fixed inset-y-0 right-0 z-[60] flex w-[85%] max-w-sm flex-col bg-white shadow-2xl lg:hidden dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800"
                >
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3"
                        @click="closeMobileMenu"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-600 text-white"
                        >
                            <ApplicationLogo
                                class="h-6 w-6 fill-current text-white"
                            />
                        </div>

                        <div>
                            <div class="font-black text-slate-900 dark:text-white">
                                مایاهمراه
                            </div>
                            <div class="text-xs text-slate-400">
                                منوی دسترسی
                            </div>
                        </div>
                    </Link>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-lg text-slate-500 dark:bg-slate-800 dark:text-slate-300"
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
                            class="flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm font-bold transition"
                            :class="
                                isActive(item)
                                    ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="closeMobileMenu"
                        >
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-base shadow-sm dark:bg-slate-800"
                            >
                                {{ item.icon }}
                            </span>

                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </div>

                <div
                    class="border-t border-slate-100 p-4 dark:border-slate-800"
                >
                    <div
                        class="mb-3 rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"
                    >
                        <div class="font-black text-slate-900 dark:text-white">
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
                            class="rounded-xl bg-slate-100 px-3 py-2.5 text-center text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            @click="closeMobileMenu"
                        >
                            پروفایل
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="rounded-xl bg-red-50 px-3 py-2.5 text-center text-sm font-bold text-red-600 dark:bg-red-950/40"
                        >
                            خروج
                        </Link>
                    </div>
                </div>
            </aside>
        </Transition>

        <!-- Optional page heading -->
        <header
            v-if="$slots.header"
            class="border-b border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page content -->
        <main>
            <slot />
        </main>
    </div>
</template>
