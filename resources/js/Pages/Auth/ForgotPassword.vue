<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="بازیابی رمز | automaya" />

        <div class="rounded-[32px] bg-white p-6 text-right shadow-[0_18px_50px_rgba(0,0,0,0.06)] dark:bg-[#161618] sm:p-8">
            <h1 class="text-[22px] font-black tracking-tight">بازیابی رمز</h1>
            <p class="mt-2 mb-6 text-sm leading-7 text-neutral-400">
                ایمیل خود را وارد کنید تا لینک بازیابی رمز برایتان ارسال شود.
            </p>

            <p v-if="status" class="mb-5 text-sm font-semibold text-green-600">
                {{ status }}
            </p>

            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label for="email" class="mb-2 block text-[13px] font-semibold text-neutral-500">ایمیل</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        dir="ltr"
                        class="am-input text-left"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <button
                    type="submit"
                    class="am-btn-primary w-full"
                    :disabled="form.processing"
                >
                    ارسال لینک
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
