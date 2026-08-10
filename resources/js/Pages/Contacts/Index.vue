<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    contacts: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');

let timer = null;

watch(search, (value) => {
    clearTimeout(timer);

    timer = setTimeout(() => {
        router.get(
            route('contacts.index'),
            { search: value },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300);
});
</script>

<template>
    <Head title="اشخاص | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8"
        >
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-violet-600">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">اشخاص</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            همکاران، فروشندگان و خریداران
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            :href="route('contacts.create')"
                            class="rounded-2xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-700"
                        >
                            + افزودن شخص
                        </Link>

                        <Link
                            :href="route('dashboard')"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            بازگشت
                        </Link>
                    </div>
                </div>

                <div class="mb-5">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="جستجو با نام یا شماره موبایل..."
                        class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-slate-800 dark:bg-slate-900"
                    />
                </div>

                <div
                    v-if="!contacts.length"
                    class="rounded-[30px] bg-white p-12 text-center shadow-sm dark:bg-slate-900"
                >
                    <div class="text-5xl">👤</div>
                    <h2 class="mt-4 text-lg font-black">شخصی پیدا نشد</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        هنوز شخصی ثبت نشده یا نتیجه‌ای برای جستجو وجود ندارد.
                    </p>
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="contact in contacts"
                        :key="contact.id"
                        class="rounded-[26px] bg-white p-5 shadow-sm dark:bg-slate-900"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-xl dark:bg-violet-950/40"
                            >
                                👤
                            </div>

                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-base font-black">
                                    {{ contact.name }}
                                </h2>

                                <a
                                    :href="`tel:${contact.mobile}`"
                                    class="mt-1 inline-block text-sm font-bold text-violet-600"
                                    dir="ltr"
                                >
                                    {{ contact.mobile }}
                                </a>

                                <p
                                    v-if="contact.phone"
                                    class="mt-1 text-xs text-slate-400"
                                    dir="ltr"
                                >
                                    {{ contact.phone }}
                                </p>
                            </div>
                        </div>

                        <p
                            v-if="contact.description"
                            class="mt-4 line-clamp-3 text-sm leading-7 text-slate-500 dark:text-slate-400"
                        >
                            {{ contact.description }}
                        </p>

                        <div class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-400 dark:border-slate-800">
                            شناسه شخص:
                            <span class="font-bold text-slate-600 dark:text-slate-300">
                                #{{ contact.id }}
                            </span>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
