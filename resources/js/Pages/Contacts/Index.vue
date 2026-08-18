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
    <Head title="اشخاص | automaya" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-black">اشخاص</h1>
                        <p class="mt-1 text-[11px] font-bold text-slate-400">
                            تأمین‌کنندگان و مشتریان
                        </p>
                    </div>

                    <Link
                        :href="route('contacts.create')"
                        class="am-btn-primary !rounded-full !px-4 !py-2 text-xs"
                    >
                        + افزودن
                    </Link>
                </div>

                <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
                    <button
                        type="button"
                        class="am-chip shrink-0"
                        :class="view === 'active' ? 'am-chip-on' : ''"
                        @click="view = 'active'"
                    >
                        فعال‌ها
                    </button>
                    <button
                        type="button"
                        class="am-chip shrink-0"
                        :class="view === 'archived' ? 'am-chip-on' : ''"
                        @click="view = 'archived'"
                    >
                        آرشیو
                    </button>
                </div>

                <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
                    <button
                        type="button"
                        class="am-chip shrink-0"
                        :class="type === '' ? 'am-chip-on' : ''"
                        @click="type = ''"
                    >
                        همه
                    </button>
                    <button
                        type="button"
                        class="am-chip shrink-0"
                        :class="type === 'colleague' ? 'am-chip-on' : ''"
                        @click="type = 'colleague'"
                    >
                        همکارها
                    </button>
                    <button
                        type="button"
                        class="am-chip shrink-0"
                        :class="type === 'individual' ? 'am-chip-on' : ''"
                        @click="type = 'individual'"
                    >
                        اشخاص عادی
                    </button>
                </div>

                <input
                    v-model="search"
                    type="text"
                    placeholder="نام یا موبایل..."
                    class="am-input mb-4"
                />

                <div
                    v-if="!contacts.length"
                    class="am-soft py-12 text-center"
                >
                    <div class="text-4xl">👤</div>
                    <h2 class="mt-3 text-sm font-black">شخصی پیدا نشد</h2>
                </div>

                <div v-else class="space-y-3">
                    <article
                        v-for="contact in contacts"
                        :key="contact.id"
                        class="am-row cursor-pointer"
                        @click="router.visit(route('contacts.show', contact.id))"
                    >
                        <div class="am-thumb !h-12 !w-12">
                            <img
                                v-if="contact.avatar_path"
                                :src="`/storage/${contact.avatar_path}`"
                                :alt="contact.name"
                                class="h-full w-full object-cover"
                            />
                            <span v-else>👤</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-black">{{ contact.name }}</h2>
                            <p class="mt-0.5 text-[11px] text-slate-400" dir="ltr">
                                {{ contact.mobile }}
                            </p>
                        </div>

                        <span class="am-chip !px-3 !py-1 text-[10px]">
                            {{ contact.contact_type === 'colleague' ? 'همکار' : 'عادی' }}
                        </span>
                    </article>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
