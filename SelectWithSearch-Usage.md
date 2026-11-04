# SelectWithSearch Component Usage Guide

## Overview
A custom Vue 3 component that provides a searchable dropdown select similar to Select2, built with Tailwind CSS.

## Features
- ✅ Search functionality with real-time filtering
- ✅ Keyboard navigation support
- ✅ Clear button (optional)
- ✅ Smooth animations
- ✅ Click outside to close
- ✅ v-model support
- ✅ Customizable placeholder
- ✅ Fully styled with Tailwind CSS

## Basic Usage

```vue
<script setup>
import SelectWithSearch from '@/Components/SelectWithSearch.vue';
import { ref, computed } from 'vue';

const selectedCustomer = ref('');

// Format your data
const customers = [
    { id: 1, name: 'John Doe' },
    { id: 2, name: 'Jane Smith' },
    { id: 3, name: 'Bob Johnson' }
];

const customerOptions = computed(() => {
    return customers.map(customer => ({
        value: customer.id,
        label: customer.name
    }));
});
</script>

<template>
    <SelectWithSearch
        v-model="selectedCustomer"
        :options="customerOptions"
        placeholder="Select a customer"
        search-placeholder="Search customers..."
    />
</template>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | String/Number | `''` | The selected value (use with v-model) |
| `options` | Array | Required | Array of options with `value` and `label` keys |
| `placeholder` | String | `'Select an option'` | Placeholder text when nothing is selected |
| `searchPlaceholder` | String | `'Search...'` | Placeholder for search input |
| `disabled` | Boolean | `false` | Disable the select |
| `clearable` | Boolean | `true` | Show clear button when option is selected |
| `valueKey` | String | `'value'` | Key to use for option value |
| `labelKey` | String | `'label'` | Key to use for option label |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | value | Emitted when selection changes |
| `change` | value | Emitted when selection changes (same as update:modelValue) |

## Examples

### Example 1: Simple Select
```vue
<SelectWithSearch
    v-model="selectedValue"
    :options="[
        { value: '1', label: 'Option 1' },
        { value: '2', label: 'Option 2' },
        { value: '3', label: 'Option 3' }
    ]"
/>
```

### Example 2: With Custom Keys
```vue
<SelectWithSearch
    v-model="selectedProduct"
    :options="products"
    value-key="id"
    label-key="name"
    placeholder="Select a product"
/>
```

### Example 3: Disabled State
```vue
<SelectWithSearch
    v-model="selectedValue"
    :options="options"
    :disabled="true"
/>
```

### Example 4: Without Clear Button
```vue
<SelectWithSearch
    v-model="selectedValue"
    :options="options"
    :clearable="false"
/>
```

### Example 5: With Change Event
```vue
<SelectWithSearch
    v-model="selectedValue"
    :options="options"
    @change="handleChange"
/>

<script setup>
const handleChange = (value) => {
    console.log('Selected value:', value);
};
</script>
```

## Styling

The component uses Tailwind CSS classes and can be customized by:

1. **Adding custom classes to the component:**
```vue
<SelectWithSearch
    v-model="selectedValue"
    :options="options"
    class="w-full max-w-md"
/>
```

2. **Modifying the component file directly** for global changes

## Data Format

Your options array should follow this format:

```javascript
const options = [
    { value: 'unique-id-1', label: 'Display Text 1' },
    { value: 'unique-id-2', label: 'Display Text 2' },
    // ...
];
```

Or if using custom keys:

```javascript
const options = [
    { id: 1, name: 'Product 1' },
    { id: 2, name: 'Product 2' },
];

// Then use:
// value-key="id"
// label-key="name"
```

## Tips

1. **Large datasets**: The component filters on the client side, so it works well with up to a few hundred options. For larger datasets, consider server-side filtering.

2. **Accessibility**: The component includes proper ARIA attributes and keyboard navigation.

3. **Mobile friendly**: Works well on touch devices with proper touch event handling.

4. **Performance**: Uses Vue 3's Composition API and computed properties for optimal performance.
