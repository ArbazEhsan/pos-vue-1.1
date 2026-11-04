<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    msg: {
        type: String,
        required: true,
    },
    duration: {
        type: Number,
        default: 3000,
    },
});

const show = ref(false);

const closeToast = () => {
    show.value = false;
};

onMounted(() => {
    // Show with slight delay for smooth animation
    setTimeout(() => {
        show.value = true;
    }, 10);

    // Auto-hide after duration
    setTimeout(() => {
        closeToast();
    }, props.duration);
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-200 ease-out"
        leave-active-class="transition-all duration-200 ease-in"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="show"
            class="fixed top-8 right-8 z-[9999] flex items-center gap-3 px-4 py-3 bg-white text-gray-800 rounded-lg shadow-lg border border-gray-200"
            role="alert"
            style="min-width: 300px; max-width: 500px;"
        >
            <!-- Message -->
            <div class="flex-1 text-sm">
                {{ msg }}
            </div>

            <!-- Close Button -->
            <button
                @click="closeToast"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors duration-150"
                aria-label="Close"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </Transition>
</template>

<style scoped>
[role="alert"] {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
