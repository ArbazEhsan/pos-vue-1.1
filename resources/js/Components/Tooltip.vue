<script setup>
import { ref, nextTick } from 'vue';

defineProps({
    text: {
        type: String,
        default: ''
    }
});

const isHovered = ref(false);
const triggerRef = ref(null);
const tooltipStyle = ref({});

const showTooltip = () => {
    isHovered.value = true;
    nextTick(() => {
        if (!triggerRef.value) return;
        const rect = triggerRef.value.getBoundingClientRect();
        tooltipStyle.value = {
            position: 'fixed',
            bottom: `${window.innerHeight - rect.top + 8}px`,
            left: `${rect.left + rect.width / 2}px`,
            transform: 'translateX(-50%)',
            zIndex: 9999,
        };
    });
};

const hideTooltip = () => {
    isHovered.value = false;
};
</script>

<template>
    <span
        ref="triggerRef"
        class="relative inline-block ml-2 align-middle cursor-help"
        @mouseenter="showTooltip"
        @mouseleave="hideTooltip"
    >
        <i class="fas fa-question-circle text-gray-400 hover:text-indigo-500 transition-colors text-sm"></i>

        <Teleport to="body">
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-1"
            >
                <span
                    v-if="isHovered"
                    :style="tooltipStyle"
                    class="w-max max-w-xs bg-gray-900 text-white text-xs rounded-lg shadow-xl p-3 text-center leading-relaxed pointer-events-none"
                >
                    <span class="block relative z-10">
                        <span v-if="text">{{ text }}</span>
                        <span v-else><slot /></span>
                    </span>
                    <!-- Arrow pointing down toward the trigger -->
                    <span class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></span>
                </span>
            </transition>
        </Teleport>
    </span>
</template>
