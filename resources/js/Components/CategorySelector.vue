<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    categories: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Select Category'
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const activePath = ref([]); // Items currently hovered/active in the columns
const selectedPath = ref([]); // The confirmed selection path

// Helper to find path to a specific id (for initial loading)
function findPath(categories, id, path = []) {
    for (const cat of categories) {
        if (cat.id === id) return [...path, cat];
        if (cat.children) {
            const result = findPath(cat.children, id, [...path, cat]);
            if (result) return result; 
        }
    }
    return null;
}

watch(() => props.modelValue, (newVal) => {
    // Initialize selectedPath based on modelValue
    if (newVal && props.categories.length) {
        const path = findPath(props.categories, newVal);
        if (path) selectedPath.value = path;
    } else {
        selectedPath.value = [];
    }
}, { immediate: true, deep: true });

const displayText = computed(() => {
    if (selectedPath.value.length > 0) {
        return selectedPath.value.map(c => c.name).join(' - ');
    }
    return props.placeholder;
});

// Columns to display
const columns = computed(() => {
    const cols = [];
    // Col 0: Root categories
    cols.push(props.categories);
    
    // Subsequent cols based on activePath
    for (const item of activePath.value) {
        if (item.children && item.children.length > 0) {
            cols.push(item.children);
        }
    }
    return cols;
});

function handleMouseEnter(item, depth) {
    // When hovering an item at depth D, update active path
    const newPath = activePath.value.slice(0, depth);
    newPath.push(item);
    activePath.value = newPath;
}

function handleClick(item) {
    // If item is a leaf (no children), select it.
    if (!item.children || item.children.length === 0) {
        selectItem(item);
    }
    // If it has children, the hover handles showing them. 
    // We could optionally focus/expand, but hover does that.
}

function selectItem(item) {
    selectedPath.value = [...activePath.value];
    emit('update:modelValue', item.id);
    isOpen.value = false;
}

function toggleDropdown() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        // Reset active path to start clean or maybe to selection?
        // Let's reset to roots unless we want to show current selection
        if (selectedPath.value.length) {
            // Pre-fill active path with selected path but exclude the last one if leaf?
            // Or just show all.
            activePath.value = [...selectedPath.value]; 
            // Note: If we just set activePath, the columns will open.
            // But we need to handle if the selection is deep.
        } else {
            activePath.value = [];
        }
    }
}

function close() {
    isOpen.value = false;
}

const container = ref(null);
function handleClickOutside(event) {
    if (container.value && !container.value.contains(event.target)) {
        close();
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative w-full" ref="container">
        <!-- Input Trigger -->
        <div 
            @click="toggleDropdown"
            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2 cursor-pointer bg-white border flex justify-between items-center"
        >
            <span :class="{'text-gray-500': !selectedPath.length, 'text-gray-900': selectedPath.length}">{{ displayText }}</span>
            <span class="ml-2 text-gray-400">
                <i class="fas" :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </span>
        </div>

        <!-- Dropdown Box -->
        <div v-show="isOpen" class="absolute z-50 mt-1 bg-white border border-gray-200 shadow-xl rounded-md flex overflow-hidden w-auto transition-all duration-200 ease-out" style="min-height: 300px; max-height: 400px; min-width: 200px;">
            <div 
                v-for="(colItems, colIndex) in columns" 
                :key="colIndex"
                class="w-56 overflow-y-auto border-r border-gray-100 last:border-r-0 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
            >
                <div 
                    v-for="item in colItems" 
                    :key="item.id"
                    @mouseenter="handleMouseEnter(item, colIndex)"
                    @click="handleClick(item)"
                    class="px-4 py-2 cursor-pointer flex justify-between items-center text-sm transition-colors duration-150"
                    :class="{
                        'text-indigo-600 font-medium bg-indigo-50': activePath[colIndex] && activePath[colIndex].id === item.id,
                        'text-gray-700 hover:bg-gray-50': !activePath[colIndex] || activePath[colIndex].id !== item.id
                    }"
                >
                    <span class="truncate pr-2">{{ item.name }}</span>
                    <i v-if="item.children && item.children.length" class="fas fa-chevron-right text-xs text-gray-400"></i>
                </div>
                <div v-if="colItems.length === 0" class="p-4 text-gray-400 text-sm italic text-center">
                    No items per instructions
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for webkit */
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #d1d5db; 
    border-radius: 3px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #9ca3af; 
}
</style>
