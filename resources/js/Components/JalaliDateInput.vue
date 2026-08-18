<script setup>
import { computed } from 'vue';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import { syncPickerDate } from '@/Utils/featuresForm';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'تاریخ' },
    inputClass: { type: String, default: 'feature-date-input' },
});

const emit = defineEmits(['update:modelValue']);

const selector = computed(() => `.${props.inputClass}`);
</script>

<template>
    <div>
        <Vue3PersianDatetimePicker
            :model-value="modelValue"
            format="YYYY-MM-DD"
            display-format="jYYYY/jMM/jDD"
            type="date"
            convert-numbers
            auto-submit
            :custom-input="selector"
            @update:model-value="emit('update:modelValue', $event)"
            @change="(value) => emit('update:modelValue', syncPickerDate(modelValue, value))"
        />
        <input
            type="text"
            :class="['am-input', inputClass]"
            :placeholder="placeholder"
            readonly
        />
    </div>
</template>
