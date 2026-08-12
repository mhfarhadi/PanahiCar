<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    entityType: {
        type: String,
        required: true,
    },
    entityId: {
        type: [Number, String],
        required: true,
    },
    notes: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'یادداشت‌ها',
    },
    emptyText: {
        type: String,
        default: 'هنوز یادداشتی ثبت نشده است.',
    },
});

const form = useForm({
    entity_type: props.entityType,
    entity_id: props.entityId,
    body: '',
});

const submit = () => {
    if (!form.body.trim()) return;

    form.post(route('entity-notes.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
};

const persianDateTime = (value) => {
    if (!value) return '—';

    const date = new Date(value);

    return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};
</script>

<template>
    <section class="rounded-[30px] bg-white p-5 shadow-sm dark:bg-slate-900 sm:p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black">{{ title }}</h2>
                <p class="mt-1 text-xs text-slate-400">
                    یادداشت جدید به تاریخچه اضافه می‌شود و یادداشت‌های قبلی باقی می‌مانند.
                </p>
            </div>

            <span
                v-if="notes.length"
                class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300"
            >
                {{ notes.length.toLocaleString('fa-IR') }} یادداشت
            </span>
        </div>

        <form class="mt-5" @submit.prevent="submit">
            <textarea
                v-model="form.body"
                rows="3"
                maxlength="10000"
                placeholder="یادداشت جدید را بنویسید..."
                class="w-full resize-y rounded-2xl border-slate-200 bg-slate-50 text-sm leading-7 shadow-none focus:border-violet-400 focus:ring-violet-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
            />

            <div class="mt-3 flex items-center justify-between gap-3">
                <p
                    v-if="form.errors.body"
                    class="text-xs font-bold text-rose-600"
                >
                    {{ form.errors.body }}
                </p>

                <span v-else></span>

                <button
                    type="submit"
                    :disabled="form.processing || !form.body.trim()"
                    class="rounded-2xl bg-violet-600 px-5 py-2.5 text-sm font-black text-white transition hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ form.processing ? 'در حال ثبت...' : 'افزودن یادداشت' }}
                </button>
            </div>
        </form>

        <div
            v-if="!notes.length"
            class="mt-5 rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-500 dark:bg-slate-950"
        >
            {{ emptyText }}
        </div>

        <div v-else class="mt-5 space-y-3">
            <article
                v-for="note in notes"
                :key="note.id"
                class="rounded-2xl border border-slate-100 p-4 dark:border-slate-800"
            >
                <p class="whitespace-pre-wrap text-sm leading-7 text-slate-700 dark:text-slate-200">
                    {{ note.body }}
                </p>

                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                    <span class="font-bold text-slate-500 dark:text-slate-300">
                        {{ note.author_name || 'کاربر سابق / نامشخص' }}
                    </span>
                    <span>•</span>
                    <span>{{ persianDateTime(note.created_at) }}</span>
                </div>
            </article>
        </div>
    </section>
</template>
