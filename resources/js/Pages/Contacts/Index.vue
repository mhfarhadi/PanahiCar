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
const type = ref(props.filters.type || '');
const view = ref(props.filters.view || 'active');

let timer = null;

const loadContacts = () => {
    router.get(
        route('contacts.index'),
        {
            search: search.value,
            type: type.value,
            view: view.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

watch(search, () => {
    clearTimeout(timer);

    timer = setTimeout(() => {
        loadContacts();
    }, 300);
});

watch(type, () => {
    loadContacts();
});

watch(view, () => {
    loadContacts();
});
</script>

<template>
    <Head title="اشخاص | مایاهمراه" />

    <AuthenticatedLayout>
        <div
            dir="rtl"
            class="mh-page"
        >
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-[#ff6570]">مایاهمراه</p>
                        <h1 class="mt-1 text-2xl font-black">اشخاص</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            همکاران، فروشندگان و خریداران
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            :href="route('contacts.create')"
                            class="rounded-2xl bg-[#ff6d76] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#f45f6a]"
                        >
                            + افزودن شخص
                        </Link>

                        <Link
                            :href="route('dashboard')"
                            class="rounded-2xl border border-slate-200/60 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-white/5 dark:bg-white/[0.035] dark:text-slate-300"
                        >
                            بازگشت
                        </Link>
                    </div>
                </div>

                <div class="mb-5 flex gap-2">
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-sm font-bold transition"
                        :class="
                            view === 'active'
                                ? 'bg-[#ff6d76] text-white'
                                : 'bg-white text-slate-600 dark:bg-white/[0.035] dark:text-slate-300'
                        "
                        @click="view = 'active'"
                    >
                        فعال‌ها
                    </button>

                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-sm font-bold transition"
                        :class="
                            view === 'archived'
                                ? 'bg-[#ff6d76] text-white'
                                : 'bg-white text-slate-600 dark:bg-white/[0.035] dark:text-slate-300'
                        "
                        @click="view = 'archived'"
                    >
                        آرشیوشده‌ها
                    </button>
                </div>

                <div class="mb-5 space-y-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-xl px-4 py-2 text-sm font-bold transition"
                            :class="
                                type === ''
                                    ? 'bg-[#ff6d76] text-white'
                                    : 'bg-white text-slate-600 dark:bg-white/[0.035] dark:text-slate-300'
                            "
                            @click="type = ''"
                        >
                            همه
                        </button>

                        <button
                            type="button"
                            class="rounded-xl px-4 py-2 text-sm font-bold transition"
                            :class="
                                type === 'colleague'
                                    ? 'bg-[#ff6d76] text-white'
                                    : 'bg-white text-slate-600 dark:bg-white/[0.035] dark:text-slate-300'
                            "
                            @click="type = 'colleague'"
                        >
                            همکارها
                        </button>

                        <button
                            type="button"
                            class="rounded-xl px-4 py-2 text-sm font-bold transition"
                            :class="
                                type === 'individual'
                                    ? 'bg-[#ff6d76] text-white'
                                    : 'bg-white text-slate-600 dark:bg-white/[0.035] dark:text-slate-300'
                            "
                            @click="type = 'individual'"
                        >
                            اشخاص عادی
                        </button>
                    </div>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="جستجو با نام یا شماره موبایل..."
                        class="w-full rounded-2xl border-slate-200/60 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#ff6d76] focus:ring-[#ff6d76]/30 dark:border-white/5 dark:bg-white/[0.035]"
                    />
                </div>

                <div
                    v-if="!contacts.length"
                    class="rounded-[30px] bg-white p-12 text-center shadow-sm dark:bg-white/[0.035]"
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
                        class="cursor-pointer transition hover:-translate-y-1 hover:shadow-md rounded-[26px] bg-white p-5 shadow-sm dark:bg-white/[0.035]"
                        @click="router.visit(route('contacts.show', contact.id))"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#fff0f1] text-xl dark:bg-[#ff6d76]/[0.10]"
                            >
                                <img
                                    v-if="contact.avatar_path"
                                    :src="`/storage/${contact.avatar_path}`"
                                    :alt="contact.name"
                                    class="h-full w-full object-cover"
                                />

                                <span v-else>👤</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <h2 class="truncate text-base font-black">
                                    {{ contact.name }}
                                </h2>

                                <a
                                    :href="`tel:${contact.mobile}`"
                                    class="mt-1 inline-block text-sm font-bold text-[#ff6570]"
                                    @click.stop
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

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-400 dark:border-white/5">
                            <span
                                class="rounded-lg px-2 py-1 font-bold"
                                :class="
                                    contact.contact_type === 'colleague'
                                        ? 'bg-[#fff0f1] text-[#d85e68] dark:bg-[#ff6d76]/[0.08] dark:text-[#ff9299]'
                                        : 'bg-[#f1f3f5] text-slate-600 dark:bg-white/[0.06] dark:text-slate-300'
                                "
                            >
                                {{ contact.contact_type === 'colleague' ? 'همکار' : 'شخص عادی' }}
                            </span>

                            <span>
                                شناسه شخص:
                            <span class="font-bold text-slate-600 dark:text-slate-300">
                                #{{ contact.id }}
                            </span>
                            </span>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
