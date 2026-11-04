<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number],
    required: true,
});

const props = defineProps({
    icon: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: '',
    },
    subText: {
        type: String,
        default: '',
    },
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
    <div class="relative mt-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="text-gray-500 sm:text-sm"><i :class="icon"></i></span>
        </div>
        <input
            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            v-model="model"
            ref="input"
            :placeholder="placeholder"
        />
    </div>
    <p class="mt-1 text-xs text-gray-500">{{ subText }}</p>
</template>
