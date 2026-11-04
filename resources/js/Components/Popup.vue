<script setup>

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
});


</script>

<template>
   <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-2xl max-w-md w-11/12">
            <div class="text-xl font-semibold mb-6 text-gray-900">{{ props.title }}</div>
            <slot />
        </div>
    </div>
</template>

