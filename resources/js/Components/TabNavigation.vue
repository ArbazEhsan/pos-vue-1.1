<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    tabs: {
        type: Array,
        required: true,
        validator: (tabs) => {
            return tabs.every(tab => tab.id && tab.label);
        }
    },
    modelValue: {
        type: String,
        default: null
    },
    defaultTab: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'tab-changed']);

// Initialize active tab
const internalActiveTab = ref(props.modelValue || props.defaultTab || props.tabs[0]?.id);

// Computed active tab (supports v-model)
const activeTab = computed({
    get() {
        return props.modelValue !== null ? props.modelValue : internalActiveTab.value;
    },
    set(value) {
        internalActiveTab.value = value;
        emit('update:modelValue', value);
        emit('tab-changed', value);
    }
});

// Change tab
const changeTab = (tabId) => {
    activeTab.value = tabId;
};
</script>

<template>
    <div class="tab-navigation-wrapper text-sm py-1">
        <!-- Horizontal Tab Navigation -->
        <div class="card mb-4">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs mb-0 px-3" role="tablist">
                    <li 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        class="nav-item"
                        role="presentation"
                    >
                        <button
                            @click="changeTab(tab.id)"
                            :class="[
                                'nav-link',
                                'px-4',
                                'py-3',
                                'd-flex',
                                'align-items-center',
                                'gap-2',
                                'transition',
                                { 'active': activeTab === tab.id }
                            ]"
                            type="button"
                            role="tab"
                            :aria-selected="activeTab === tab.id"
                        >
                            <i v-if="tab.icon" :class="[tab.icon, 'text-sm']"></i>
                            <span>{{ tab.label }}</span>
                            <span v-if="tab.badge" class="badge bg-primary ms-1">{{ tab.badge }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Dynamic slot content based on active tab -->
                        <div v-for="tab in tabs" :key="`content-${tab.id}`">
                            <div v-show="activeTab === tab.id">
                                <slot :name="tab.id" :tab="tab">
                                    <div class="text-center text-gray-500 py-5">
                                        <p>No content available for {{ tab.label }}</p>
                                    </div>
                                </slot>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #374151;
    background-color: #f9fafb;
}

.nav-tabs .nav-link.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
    background-color: transparent;
}

.nav-tabs {
    border-bottom: 1px solid #e5e7eb;
}

.gap-2 {
    gap: 0.5rem;
}

.transition {
    transition: all 0.15s ease-in-out;
}
</style>
