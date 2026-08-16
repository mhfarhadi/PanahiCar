<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'انتخاب کنید',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const root = ref(null);
const open = ref(false);

const selectedOption = computed(() =>
    props.options.find(
        (item) => String(item.value) === String(props.modelValue)
    )
);

const toggle = () => {
    if (!props.disabled) {
        open.value = !open.value;
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option.value);
    open.value = false;
};

const closeOnOutsideClick = (event) => {
    if (root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeOnOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeOnOutsideClick);
});
</script>

<template>
    <div ref="root" class="maya-select" :class="{ 'is-disabled': disabled }">
        <button
            type="button"
            class="maya-select-trigger"
            :disabled="disabled"
            :aria-expanded="open"
            @click.stop="toggle"
        >
            <span :class="{ placeholder: !selectedOption }">
                {{ selectedOption?.label || placeholder }}
            </span>

            <svg
                viewBox="0 0 20 20"
                fill="none"
                aria-hidden="true"
                :class="{ rotated: open }"
            >
                <path
                    d="M5 7.5 10 12.5 15 7.5"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>

        <transition name="maya-select-pop">
            <div v-if="open" class="maya-select-menu">
                <button
                    v-for="option in options"
                    :key="String(option.value)"
                    type="button"
                    class="maya-select-option"
                    :class="{
                        selected:
                            String(option.value) === String(modelValue),
                    }"
                    @pointerdown.prevent.stop="selectOption(option)"
                >
                    <span>{{ option.label }}</span>
                    <span
                        v-if="String(option.value) === String(modelValue)"
                        class="check"
                    >
                        ✓
                    </span>
                </button>

                <div v-if="!options.length" class="maya-select-empty">
                    گزینه‌ای موجود نیست
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.maya-select {
    position: relative;
    width: 100%;
    font-family: 'Vazirmatn Variable', sans-serif;
}

.maya-select-trigger {
    display: flex;
    width: 100%;
    min-height: 46px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border: 1px solid #dbe3e1;
    border-radius: 14px;
    background: #fff;
    padding: 0 13px;
    color: #1d2523;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    text-align: right;
    cursor: pointer;
    transition: border-color 160ms ease, box-shadow 160ms ease;
}

.maya-select-trigger:focus {
    outline: none;
    border-color: #5d9f97;
    box-shadow: 0 0 0 4px rgba(93, 159, 151, 0.1);
}

.maya-select-trigger .placeholder {
    color: #8a9492;
}

.maya-select-trigger svg {
    width: 17px;
    flex: 0 0 17px;
    color: #77817f;
    transition: transform 160ms ease;
}

.maya-select-trigger svg.rotated {
    transform: rotate(180deg);
}

.maya-select-menu {
    position: absolute;
    z-index: 80;
    top: calc(100% + 7px);
    right: 0;
    left: 0;
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid #dfe5e3;
    border-radius: 15px;
    background: #fff;
    padding: 5px;
    box-shadow: 0 16px 38px rgba(33, 48, 48, 0.14);
    font-family: 'Vazirmatn Variable', sans-serif;
}

.maya-select-option {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    padding: 9px 10px;
    color: #303a38;
    font-family: inherit;
    font-size: 12px;
    font-weight: 650;
    text-align: right;
    cursor: pointer;
}

.maya-select-option:hover {
    background: #f1f6f4;
}

.maya-select-option.selected {
    background: #e8f3f0;
    color: #176d66;
    font-weight: 850;
}

.check {
    font-size: 12px;
    font-weight: 900;
}

.maya-select-empty {
    padding: 10px;
    color: #8a9492;
    font-size: 12px;
    text-align: center;
}

.is-disabled {
    opacity: 0.48;
}

.is-disabled .maya-select-trigger {
    cursor: not-allowed;
    background: #f3f5f4;
}

.maya-select-pop-enter-active,
.maya-select-pop-leave-active {
    transition: opacity 120ms ease, transform 120ms ease;
    transform-origin: top;
}

.maya-select-pop-enter-from,
.maya-select-pop-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(0.985);
}
</style>
