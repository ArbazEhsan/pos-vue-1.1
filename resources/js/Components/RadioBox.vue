<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [String, Number, Boolean, Object],
        required: true,
    },
    value: {
        default: null,
    },
    label: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        required: true, // radios need a group name
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <label class="inline-flex items-center space-x-2 cursor-pointer mr-4 border border-gray-300 rounded-md px-3 py-2">
        <input
            type="radio"
            :name="name"
            :value="value"
            v-model="proxyChecked"
            class="form-radio h-5 w-5 text-blue-600"
        />
        <span>{{ label }}</span>
    </label>
</template>
