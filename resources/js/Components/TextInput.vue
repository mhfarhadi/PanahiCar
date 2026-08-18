<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-2xl border-0 bg-[#f4f4f5] px-4 py-3.5 text-sm text-neutral-900 outline-none ring-0 placeholder:text-neutral-400 focus:ring-2 focus:ring-black/10 dark:bg-[#1c1c1e] dark:text-neutral-100"
        v-model="model"
        ref="input"
    />
</template>
