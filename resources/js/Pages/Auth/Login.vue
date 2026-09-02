<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import PublicShell from '@/Layouts/PublicShell.vue';
import { BRAND_FA, pageTitle } from '@/Utils/brand';
import { illustration } from '@/Utils/carPhotos';
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

    <PublicShell :back-href="route('home')" back-label="صفحه اصلی" :show-brand="false">
        <div class="ph-rx-login">
            <section class="ph-rx-login__panel">
                <p class="ph-rx-kicker">ورود کارکنان</p>
                <h1 class="ph-rx-login__title">داشبورد مدیریت {{ BRAND_FA }}</h1>
                <p class="ph-rx-login__text">
                    پس از ورود به موجودی، فروش، اقساط و قراردادها دسترسی دارید.
                </p>

                <div class="ph-rx-login__highlight">
                    <img :src="illustration('showroom')" alt="" class="ph-rx-login__highlight-photo" />
                    <div>
                        <p class="ph-rx-login__highlight-label">دسترسی سریع</p>
                        <p class="ph-rx-login__highlight-value">مدیریت نمایشگاه در یک پنل</p>
                    </div>
                </div>

                <Link :href="route('features.index')" class="ph-rx-login__features-link">
                    امکانات عمومی بدون ورود
                </Link>
            </section>

            <section class="ph-rx-login__card">
                <p v-if="status" class="ph-rx-login__status">
                    {{ status }}
                </p>

                <h2 class="ph-rx-login__card-title">ورود به سیستم</h2>
                <p class="ph-rx-login__card-sub">ایمیل و رمز عبور کارکنان را وارد کنید.</p>

                <form class="ph-rx-login__form" @submit.prevent="submit">
                    <div>
                        <label for="email" class="ph-rx-field-label">ایمیل</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            dir="ltr"
                            class="ph-rx-input"
                            placeholder="email@example.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="ph-rx-field-label">رمز عبور</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            dir="ltr"
                            class="ph-rx-input"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="ph-rx-login__row">
                        <label class="ph-rx-login__remember">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            مرا به خاطر بسپار
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="ph-rx-login__forgot"
                        >
                            فراموشی رمز
                        </Link>
                    </div>

                    <button
                        type="submit"
                        class="ph-rx-btn ph-rx-btn--dark ph-rx-btn--block"
                        :disabled="form.processing"
                        :class="{ 'opacity-50': form.processing }"
                    >
                        ورود
                    </button>
                </form>
            </section>
        </div>
    </PublicShell>
</template>
