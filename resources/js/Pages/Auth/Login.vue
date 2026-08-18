<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const playing = ref(false);
const ready = ref(false);
let readyTimer;

const letters = 'PANAHI CAR'.split('');

const finishIntro = () => {
    playing.value = false;
    ready.value = true;
};

onMounted(() => {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduce) {
        ready.value = true;
        return;
    }

    requestAnimationFrame(() => {
        playing.value = true;
    });

    readyTimer = window.setTimeout(finishIntro, 2800);
});

onBeforeUnmount(() => {
    window.clearTimeout(readyTimer);
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="ورود | PANAHI CAR" />

    <div
        dir="rtl"
        class="am-splash"
        :class="{ 'is-playing': playing, 'is-ready': ready }"
    >
        <div class="am-splash-glow" />

        <div class="am-splash-stack">
            <div class="am-splash-brand" aria-hidden="true">
                <p class="am-splash-word" dir="ltr">
                    <span
                        v-for="(letter, index) in letters"
                        :key="`${letter}-${index}`"
                        class="am-splash-letter"
                        :class="{ 'is-space': letter === ' ' }"
                        :style="{ '--i': index }"
                    >{{ letter === ' ' ? '\u00a0' : letter }}</span>
                </p>
                <span class="am-splash-line" />
            </div>

            <aside class="am-splash-panel">
            <div class="am-splash-card">
                <p v-if="status" class="mb-4 text-sm font-semibold text-green-600">
                    {{ status }}
                </p>

                <form class="space-y-4 text-right" @submit.prevent="submit">
                    <div>
                        <label for="email" class="mb-2 block text-[13px] font-semibold text-neutral-500">
                            ایمیل
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            dir="ltr"
                            class="am-input text-left"
                            placeholder="admin@automaya.test"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-[13px] font-semibold text-neutral-500">
                            رمز عبور
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            dir="ltr"
                            class="am-input text-left"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label class="flex items-center gap-2 text-sm text-neutral-500">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            مرا به خاطر بسپار
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-semibold text-neutral-900"
                        >
                            فراموشی رمز
                        </Link>
                    </div>

                    <button
                        type="submit"
                        class="am-btn-primary w-full"
                        :disabled="form.processing"
                        :class="{ 'opacity-40': form.processing }"
                    >
                        ورود
                    </button>
                </form>

                <Link
                    :href="route('features.index')"
                    class="am-splash-features"
                >
                    <span>ورود به امکانات</span>
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M11 6l-6 6 6 6" />
                    </svg>
                </Link>
            </div>
        </aside>
        </div>

        <button
            v-if="!ready"
            type="button"
            class="am-splash-skip"
            @click="finishIntro"
        >
            رد کردن
        </button>
    </div>
</template>
