<script setup>
import { ref, watch, useAttrs } from 'vue';

defineProps({
    options: {
        type: Array,
        required: true,
    },
    listId: {
        type: String,
        required: true,
    },
});

defineOptions({
    inheritAttrs: false,
});

const model = defineModel({
    type: [String, Number],
    required: false,
});

const input = ref(null);
const errorMessage = ref('');
const attrs = useAttrs();

// 1. Internal Validation Rules Logic
const validate = (val) => {
    const value = val?.toString().trim() || '';

    // Check 'required' attribute
    if (attrs.required !== undefined && attrs.required !== false && !value) {
        errorMessage.value = 'This field is required.';
        return false;
    }

    // Check 'type' specific rules (e.g., email)
    if (attrs.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            errorMessage.value = 'Please enter a valid email address.';
            return false;
        }
    }

    // Custom internal rules (e.g., minlength if passed)
    if (attrs.minlength && value.length < attrs.minlength) {
        errorMessage.value = `Minimum ${attrs.minlength} characters required.`;
        return false;
    }

    errorMessage.value = '';
    return true;
};

// 2. Trigger validation on changes
watch(model, (newVal) => validate(newVal));


defineExpose({
    validate: () => validate(model.value) 
});
</script>

<template>
    <div class="flex flex-col w-full">
        <input
            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            v-model="model"
            ref="input"
            :list="listId"
            @blur="validate(model)"
            @focus="$event.target.select()"
        />
        
        <datalist :id="listId">
            <option v-for="option in options" :key="option.id || option.name" :value="option.name" />
        </datalist>

        <p v-if="errorMessage" class="mt-1 text-xs text-red-600 italic">
            {{ errorMessage }}
        </p>
    </div>
</template>
