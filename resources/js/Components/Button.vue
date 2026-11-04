<script setup>

const props = defineProps({
    className: String,
    titleName: String,
    shouldStop: Boolean
});

const emit = defineEmits(['click']);

const onClick = (event) => {
    if (props.shouldStop) {
        event.stopPropagation();
    }
    emit('click', event);
};

</script>
<template>
    <button 
        @click="onClick" 
        :class="className"
        :title="titleName"
    >
        <slot />
    </button>
</template>
