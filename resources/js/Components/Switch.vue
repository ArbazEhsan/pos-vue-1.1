<script setup>
import { computed } from 'vue';

const props = defineProps({
    // v-model binds to modelValue (true/false)
    modelValue: {
        type: Boolean,
        default: false,
    },
    // Optional props for ID/Label association
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

// Use a computed property to handle the internal state of the checkbox
const checkedState = computed({
    get: () => props.modelValue,
    set: (newValue) => {
        // When the checkbox changes, emit the new value back to the parent
        emit('update:modelValue', newValue);
    }
});
</script>

<template>
    <label :for="id" class="relative inline-flex items-center cursor-pointer">
        <input 
            :id="id"
            type="checkbox" 
            class="sr-only peer" 
            v-model="checkedState"
        >
        <!-- The visual toggle div using your Tailwind CSS classes -->
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
        
        <!-- Optional label text -->
        <span v-if="label" class="ms-3 text-sm font-medium text-gray-900">
            {{ label }}
        </span>
    </label>
</template>
