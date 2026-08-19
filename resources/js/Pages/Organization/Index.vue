<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { pageTitle } from '@/Utils/brand';

const props = defineProps({
    locations: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    roleLabels: { type: Object, default: () => ({}) },
});

const editingUserId = ref(null);

const locationForm = useForm({
    name: '',
    code: '',
});

const editLocationForms = ref({});

props.locations.forEach((location) => {
    editLocationForms.value[location.id] = {
        name: location.name,
        code: location.code,
        is_active: location.is_active,
    };
});

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role: props.roles[0]?.value || 'staff',
    location_id: props.locations.find((item) => item.is_active)?.id || '',
});

const editUserForm = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
    location_id: '',
    is_active: true,
});

const activeLocations = computed(() => props.locations.filter((location) => location.is_active));

const submitLocation = () => {
    locationForm.post(route('organization.locations.store'), {
        preserveScroll: true,
        onSuccess: () => locationForm.reset(),
    });
};

const updateLocation = (locationId) => {
    router.patch(route('organization.locations.update', locationId), editLocationForms.value[locationId], {
        preserveScroll: true,
    });
};

const submitUser = () => {
    userForm.post(route('organization.users.store'), {
        preserveScroll: true,
        onSuccess: () => userForm.reset('name', 'email', 'password'),
    });
};

const startEditUser = (user) => {
    editingUserId.value = user.id;
    editUserForm.clearErrors();
    editUserForm.name = user.name;
    editUserForm.email = user.email;
    editUserForm.password = '';
    editUserForm.role = user.role;
    editUserForm.location_id = user.location_id || '';
    editUserForm.is_active = user.is_active;
};

const cancelEditUser = () => {
    editingUserId.value = null;
};

const submitEditUser = () => {
    editUserForm.patch(route('organization.users.update', editingUserId.value), {
        preserveScroll: true,
        onSuccess: () => {
            editingUserId.value = null;
            editUserForm.reset('password');
        },
    });
};

const roleBadgeClass = (role) => {
    const map = {
        super_admin: 'bg-violet-100 text-violet-800',
        manager: 'bg-emerald-100 text-emerald-800',
        sales: 'bg-amber-100 text-amber-900',
        inventory: 'bg-sky-100 text-sky-800',
        accountant: 'bg-pink-100 text-pink-800',
        viewer: 'bg-slate-100 text-slate-700',
    };

    return map[role] || 'bg-slate-100 text-slate-700';
};
</script>

<template>
    <Head :title="pageTitle('سازمان')" />

    <AuthenticatedLayout>
        <div dir="rtl" class="am-page !pt-3">
            <div class="am-page-inner-narrow !max-w-xl">
                <div class="mb-5">
                    <h1 class="text-xl font-black">سازمان</h1>
                    <p class="mt-1 text-[11px] font-bold text-slate-400">
                        شعب، کاربران و سطح دسترسی — فقط مدیر کل
                    </p>
                </div>

                <section class="am-card mb-4">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-black">شعب</h2>
                        <span class="text-[11px] text-slate-400">{{ locations.length.toLocaleString('fa-IR') }} شعبه</span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="location in locations"
                            :key="location.id"
                            class="rounded-[24px] border border-white/80 bg-white/70 p-4"
                        >
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black">{{ location.name }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">
                                        کد {{ location.code }} · {{ location.users_count.toLocaleString('fa-IR') }} کاربر ·
                                        {{ location.devices_count.toLocaleString('fa-IR') }} خودرو
                                    </p>
                                </div>
                                <span
                                    class="rounded-full px-3 py-1 text-[10px] font-bold"
                                    :class="location.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                                >
                                    {{ location.is_active ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-3">
                                <input v-model="editLocationForms[location.id].name" class="am-input" placeholder="نام شعبه" />
                                <input v-model="editLocationForms[location.id].code" class="am-input text-left" dir="ltr" placeholder="code" />
                                <label class="flex items-center gap-2 rounded-[22px] bg-slate-50 px-4 py-3 text-sm">
                                    <input v-model="editLocationForms[location.id].is_active" type="checkbox" />
                                    فعال
                                </label>
                            </div>

                            <button type="button" class="am-btn-secondary mt-3 !px-4 !py-2 text-xs" @click="updateLocation(location.id)">
                                ذخیره شعبه
                            </button>
                        </div>
                    </div>

                    <form class="mt-4 grid gap-2 border-t border-slate-100 pt-4 sm:grid-cols-3" @submit.prevent="submitLocation">
                        <input v-model="locationForm.name" class="am-input" placeholder="نام شعبه جدید" required />
                        <input v-model="locationForm.code" class="am-input text-left" dir="ltr" placeholder="branch-code" required />
                        <button type="submit" class="am-btn-primary" :disabled="locationForm.processing">
                            + افزودن شعبه
                        </button>
                    </form>
                </section>

                <section class="am-card mb-4">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-black">کاربران</h2>
                        <span class="text-[11px] text-slate-400">{{ users.length.toLocaleString('fa-IR') }} نفر</span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="user in users"
                            :key="user.id"
                            class="rounded-[24px] border border-white/80 bg-white/70 p-4"
                        >
                            <template v-if="editingUserId !== user.id">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-black">{{ user.name }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400" dir="ltr">{{ user.email }}</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="rounded-full px-3 py-1 text-[10px] font-bold" :class="roleBadgeClass(user.role)">
                                                {{ user.role_label }}
                                            </span>
                                            <span v-if="user.location_name" class="rounded-full bg-sky-100 px-3 py-1 text-[10px] font-bold text-sky-800">
                                                {{ user.location_name }}
                                            </span>
                                            <span
                                                class="rounded-full px-3 py-1 text-[10px] font-bold"
                                                :class="user.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700'"
                                            >
                                                {{ user.is_active ? 'فعال' : 'غیرفعال' }}
                                            </span>
                                        </div>
                                    </div>
                                    <button
                                        v-if="user.role !== 'super_admin'"
                                        type="button"
                                        class="am-btn-secondary !px-3 !py-2 text-xs"
                                        @click="startEditUser(user)"
                                    >
                                        ویرایش
                                    </button>
                                </div>
                            </template>

                            <form v-else class="space-y-3" @submit.prevent="submitEditUser">
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <input v-model="editUserForm.name" class="am-input" required />
                                    <input v-model="editUserForm.email" class="am-input text-left" dir="ltr" required />
                                    <input v-model="editUserForm.password" type="password" class="am-input text-left" dir="ltr" placeholder="رمز جدید (اختیاری)" />
                                    <select v-model="editUserForm.role" class="am-input">
                                        <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                                    </select>
                                    <select v-model="editUserForm.location_id" class="am-input sm:col-span-2">
                                        <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                    </select>
                                </div>
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input v-model="editUserForm.is_active" type="checkbox" />
                                    حساب فعال
                                </label>
                                <div class="flex gap-2">
                                    <button type="submit" class="am-btn-primary !px-4 !py-2 text-xs" :disabled="editUserForm.processing">ذخیره</button>
                                    <button type="button" class="am-btn-secondary !px-4 !py-2 text-xs" @click="cancelEditUser">انصراف</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <section class="am-card">
                    <h2 class="mb-4 text-sm font-black">کاربر جدید</h2>

                    <form class="grid gap-3" @submit.prevent="submitUser">
                        <input v-model="userForm.name" class="am-input" placeholder="نام" required />
                        <input v-model="userForm.email" class="am-input text-left" dir="ltr" placeholder="email" required />
                        <input v-model="userForm.password" type="password" class="am-input text-left" dir="ltr" placeholder="رمز عبور" required />
                        <select v-model="userForm.role" class="am-input">
                            <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                        </select>
                        <select v-model="userForm.location_id" class="am-input" required>
                            <option disabled value="">انتخاب شعبه</option>
                            <option v-for="location in activeLocations" :key="location.id" :value="location.id">{{ location.name }}</option>
                        </select>
                        <button type="submit" class="am-btn-primary" :disabled="userForm.processing">
                            ایجاد کاربر
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
