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
    <section class="mh-surface sm:!p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black">{{ title }}</h2>
                <p class="mt-1 text-xs text-slate-400">
                    یادداشت جدید به تاریخچه اضافه می‌شود و یادداشت‌های قبلی باقی می‌مانند.
                </p>
            </div>

            <span
                v-if="notes.length"
                class="mh-accent-soft rounded-xl px-3 py-1.5 text-xs font-black"
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
                class="mh-input resize-y leading-7"
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
                    class="mh-primary px-5 py-2.5 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ form.processing ? 'در حال ثبت...' : 'افزودن یادداشت' }}
                </button>
            </div>
        </form>

        <div
            v-if="!notes.length"
            class="mh-soft-surface mt-5 text-center text-sm text-slate-500 dark:text-slate-400"
        >
            {{ emptyText }}
        </div>

        <div v-else class="mt-5 space-y-3">
            <article
                v-for="note in notes"
                :key="note.id"
                class="mh-soft-surface"
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
