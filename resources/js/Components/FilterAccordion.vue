<script setup>
import { ref } from 'vue';
import Select from '@/Components/Select.vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: 'all'
    },
    filterOptions: {
        type: Array,
        required: true
    },
    title: {
        type: String,
        default: 'Filters'
    },
    showClearButton: {
        type: Boolean,
        default: true
    },
    defaultValue: {
        type: String,
        default: 'all'
    }
});

const emit = defineEmits(['update:modelValue']);

const filterOpen = ref(false);

const updateFilter = (value) => {
    emit('update:modelValue', value);
};

const clearFilters = () => {
    emit('update:modelValue', props.defaultValue);
};

const isFilterActive = () => {
    return props.modelValue !== props.defaultValue;
};
</script>

<template>
    <div class="mb-4">
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <!-- Accordion Header -->
            <button 
                @click="filterOpen = !filterOpen"
                class="w-full flex items-center justify-between px-4 py-2 bg-gray-50 hover:bg-gray-100 transition-colors duration-150"
            >
                <div class="flex items-center space-x-2">
                    <i class="fas fa-filter text-blue-600"></i>
                    <span class="font-semibold text-gray-700">{{ title }}</span>
                    <span v-if="isFilterActive()" class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        Active
                    </span>
                </div>
                <i :class="['fas fa-chevron-down transition-transform duration-200', { 'rotate-180': filterOpen }]"></i>
            </button>
            
            <!-- Accordion Content -->
            <transition 
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-96 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="max-h-96 opacity-100"
                leave-to-class="max-h-0 opacity-0"
            >
                <div v-show="filterOpen" class="border-t border-gray-200 bg-white p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filter Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-layer-group mr-1 text-gray-400"></i>
                                Product Type
                            </label>
                            <Select
                                :modelValue="modelValue"
                                @update:modelValue="updateFilter"
                                :options="filterOptions"
                                class="text-xs"
                            />
                        </div>
                        
                        <!-- Slot for additional filters -->
                        <slot name="additional-filters"></slot>
                        
                        <!-- Clear Button -->
                        <div class="md:col-span-2 flex items-end" v-if="!$slots['additional-filters']">
                            <button 
                                v-if="showClearButton && isFilterActive()"
                                @click="clearFilters"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-150"
                            >
                                <i class="fas fa-times mr-1"></i>
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
/* Ensure smooth height transitions */
.max-h-0 {
    max-height: 0;
    overflow: hidden;
}

.max-h-96 {
    max-height: 24rem;
    overflow: visible;
}
</style>
