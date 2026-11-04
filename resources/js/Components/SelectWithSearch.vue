<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    options: {
        type: Array,
        required: true,
        // Expected format: [{ value: '1', label: 'Option 1' }, ...]
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    searchPlaceholder: {
        type: String,
        default: 'Search...'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    clearable: {
        type: Boolean,
        default: true
    },
    valueKey: {
        type: String,
        default: 'value'
    },
    labelKey: {
        type: String,
        default: 'label'
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);

// Computed properties
const selectedOption = computed(() => {
    return props.options.find(option => option[props.valueKey] == props.modelValue);
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) {
        return props.options;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(option => {
        const label = option[props.labelKey]?.toString().toLowerCase() || '';
        return label.includes(query);
    });
});

const displayText = computed(() => {
    return selectedOption.value ? selectedOption.value[props.labelKey] : props.placeholder;
});

// Methods
const toggleDropdown = () => {
    if (props.disabled) return;
    
    isOpen.value = !isOpen.value;
    
    if (isOpen.value) {
        setTimeout(() => {
            searchInputRef.value?.focus();
        }, 50);
    } else {
        searchQuery.value = '';
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option[props.valueKey]);
    emit('change', option[props.valueKey]);
    isOpen.value = false;
    searchQuery.value = '';
};

const clearSelection = (event) => {
    event.stopPropagation();
    emit('update:modelValue', '');
    emit('change', '');
    searchQuery.value = '';
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Watch for external changes
watch(() => props.modelValue, () => {
    searchQuery.value = '';
});
</script>

<template>
    <div ref="dropdownRef" class="relative w-full">
        <!-- Select Button -->
        <button
            type="button"
            @click="toggleDropdown"
            :disabled="disabled"
            class="w-full px-3 py-2 text-left bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-between"
            :class="{
                'border-indigo-500 ring-2 ring-indigo-100': isOpen,
                'hover:border-gray-400': !disabled && !isOpen
            }"
        >
            <span 
                class="block truncate text-sm"
                :class="selectedOption ? 'text-gray-900' : 'text-gray-400'"
            >
                {{ displayText }}
            </span>
            
            <div class="flex items-center gap-1">
                <!-- Clear Button -->
                <button
                    v-if="clearable && selectedOption"
                    type="button"
                    @click="clearSelection"
                    class="p-0.5 hover:bg-gray-200 rounded transition-colors duration-150"
                >
                    <i class="fas fa-times text-gray-400 hover:text-gray-600 text-xs"></i>
                </button>
                
                <!-- Dropdown Arrow -->
                <i 
                    class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                ></i>
            </div>
        </button>

        <!-- Dropdown Menu -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="isOpen"
                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-hidden"
            >
                <!-- Search Input -->
                <div class="p-2 border-b border-gray-200 bg-gray-50">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @click.stop
                        />
                    </div>
                </div>

                <!-- Options List -->
                <div class="overflow-y-auto max-h-48">
                    <div
                        v-for="option in filteredOptions"
                        :key="option[valueKey]"
                        @click="selectOption(option)"
                        class="px-3 py-2 text-sm cursor-pointer transition-colors duration-150 flex items-center justify-between"
                        :class="{
                            'bg-indigo-600 text-white': option[valueKey] === modelValue,
                            'hover:bg-gray-100 text-gray-900': option[valueKey] !== modelValue
                        }"
                    >
                        <span class="block truncate">{{ option[labelKey] }}</span>
                        <i 
                            v-if="option[valueKey] === modelValue"
                            class="fas fa-check text-xs ml-2"
                        ></i>
                    </div>

                    <!-- No Results -->
                    <div
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-4 text-sm text-gray-500 text-center"
                    >
                        <i class="fas fa-search text-2xl mb-2 opacity-30"></i>
                        <p>No results found</p>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Custom scrollbar for options list */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
