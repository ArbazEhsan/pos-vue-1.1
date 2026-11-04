<script setup>

// Define the props that the parent component can pass in
defineProps({
  id: {
    type: String,
    required: true,
    default: 'textarea-id'
  },
  modelValue: { // This is essential for v-model in Vue 3
    type: [String, Number],
    default: '',
  },
  rows: {
    type: Number,
    default: 3,
  },
  placeholder: {
    type: String,
    default: 'Enter details...',
  },
  error: { // Optional prop for displaying validation errors
    type: String,
    default: '',
  }
});

// Define the event that emits the updated value back to the parent
defineEmits(['update:modelValue']);
</script>

<template>
  <div class="mb-4">

    <!-- The actual textarea element -->
    <textarea
      :id="id"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :rows="rows"
      :placeholder="placeholder"
      class="ml-[-10px] block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
    ></textarea>

    <!-- Optional error message display -->
    <p v-if="error" class="mt-1 text-xs text-red-600">
      {{ error }}
    </p>
  </div>
</template>