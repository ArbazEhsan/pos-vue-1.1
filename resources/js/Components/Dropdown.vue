<script setup>
import { computed, onMounted, onUnmounted, ref, nextTick } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-white',
    },
});

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    document.addEventListener('scroll', updatePosition, true);
    window.addEventListener('resize', updatePosition);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.removeEventListener('scroll', updatePosition, true);
    window.removeEventListener('resize', updatePosition);
});

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

const open = ref(false);
const triggerRef = ref(null);
const dropdownStyle = ref({});

const updatePosition = () => {
    if (!open.value || !triggerRef.value) return;
    const rect = triggerRef.value.getBoundingClientRect();
    const style = {
        position: 'fixed',
        top: `${rect.bottom + 8}px`,
        zIndex: 9999,
    };
    if (props.align === 'right') {
        style.right = `${window.innerWidth - rect.right}px`;
    } else {
        style.left = `${rect.left}px`;
    }
    dropdownStyle.value = style;
};

const toggleOpen = () => {
    open.value = !open.value;
    if (open.value) {
        nextTick(updatePosition);
    }
};
</script>

<template>
    <div class="relative" ref="triggerRef">
        <div @click="toggleOpen">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div v-show="open" class="fixed inset-0 z-[9998]" @click="open = false"></div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-show="open"
                    :style="dropdownStyle"
                    :class="widthClass"
                    class="rounded-md shadow-lg"
                    @click="open = false"
                >
                    <div class="rounded-md ring-1 ring-black ring-opacity-5" :class="contentClasses">
                        <slot name="content" />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
