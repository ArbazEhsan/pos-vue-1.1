<script setup>
import { computed } from 'vue';
import Tooltip from '@/Components/Tooltip.vue';

const model = defineModel({
    type: [Boolean, Array],
    required: true,
});

defineProps({
    value: {
        default: null,
    },
    label: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    TooltipText: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <label class="flex items-center p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition shadow-sm">
        <div class="flex items-center h-5 mt-0.5">
            <input 
                type="checkbox" 
                :value="value" 
                v-model="model" 
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition duration-150 ease-in-out cursor-pointer" 
            />
        </div>
        <div class="ml-3 flex-1">
            <div class="flex items-center">
                <span class="block text-sm font-medium text-gray-900 group-hover:text-indigo-700 transition-colors">{{ label }}</span>
                <Tooltip v-if="TooltipText" class="ml-2">
                    <span v-html="TooltipText"></span>
                </Tooltip>
            </div>
            <p v-if="description" class="mt-1 text-xs text-gray-500">{{ description }}</p>
        </div>
    </label>
</template>
