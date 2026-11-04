<script setup>

const props = defineProps({
    modelValue: {
        type: [String, Number], // Assuming IDs are strings or numbers
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']); 

const handleChange = (event) => {
    emit('update:modelValue', event.target.value);
};

</script>

<template>
    <select
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm js-example-basic-single"
        :id="$attrs.id"
        :value="modelValue"
        @change="handleChange"
        :class="$attrs.class"
    >
        <option value="" disabled>Select an option</option> 
        <option v-for="data in options" :key="data.id" :value="data.id">
            {{ data.name }}
        </option>
    </select>
</template>
