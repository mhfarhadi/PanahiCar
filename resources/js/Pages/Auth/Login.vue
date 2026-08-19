<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import PublicShell from '@/Layouts/PublicShell.vue';
import { pageTitle } from '@/Utils/brand';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

const submit = () => {
    form.post(route('login'), {
        preserveScroll: false,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="pageTitle('ورود')" />

    <PublicShell :back-href="route('cars.landing')" back-label="بخش خودرو">
        <div class="ph-login-page">
            <div class="ph-login-page__intro">
                <p class="text-[11px] font-bold text-slate-400">ورود کارکنان · بخش خودرو</p>
                <h1 class="mt-2 text-2xl font-black">ورود به سیستم</h1>
                <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                    پس از ورود به داشبورد مدیریت نمایشگاه دسترسی دارید.
                </p>
            </div>

            <div class="ph-login-page__card am-card">
                <p v-if="status" class="mb-4 text-sm font-semibold text-green-600 dark:text-green-400">
                    {{ status }}
                </p>

                <form class="space-y-4 text-right" @submit.prevent="submit">
                    <div>
                        <label for="email" class="mb-2 block text-[13px] font-semibold text-slate-500">
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
                            placeholder="email@example.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-[13px] font-semibold text-slate-500">
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
                        <label class="flex items-center gap-2 text-sm text-slate-500">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            مرا به خاطر بسپار
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
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
                    class="mt-5 flex items-center justify-center gap-2 text-sm font-bold text-slate-500 transition hover:text-slate-800 dark:hover:text-slate-200"
                >
                    امکانات عمومی بدون ورود
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M11 6l-6 6 6 6" />
                    </svg>
                </Link>
            </div>
        </div>
    </PublicShell>
</template>
