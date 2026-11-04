<script setup>
import { onMounted, onBeforeUnmount, ref, watch, computed, nextTick } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    options: {
        type: Array,
        default: () => [],
    },
});

const model = defineModel({
    type: [Object, String, Number],
    required: true,
});

const input = ref(null);

const emit = defineEmits(['change']); // declare event

const dataOptions = computed(() => {
    return props.options.length > 0 ? props.options : props.products;
});

function initSelect2() {
    $(input.value).select2({
        placeholder: "-- Select --",
        allowClear: true,
        width: '100%',
    }).on('select2:select', function (e) {
        const selectedId = $(this).val();
        
        // Set flag to prevent watch from triggering
        isSelectingFromSelect2 = true;
        
        if (typeof model.value === 'object' && model.value !== null) {
            // Find the complete product/option object from the array
            const fullObject = dataOptions.value.find(item => item.id == selectedId);
            
            if (fullObject) {
                // Emit the complete object with all properties
                model.value = { ...fullObject, qty: model.value?.qty ?? '' };
            } else {
                // Fallback to basic object
                model.value = {
                    id: selectedId,
                    name: e.params.data.text,
                    qty: model.value?.qty ?? '',
                };
            }
        } else {
            model.value = selectedId;
        }
        emit('change', model.value); // fire event to parent
    }).on('select2:clear', function () {
        // Set flag to prevent watch from triggering
        isSelectingFromSelect2 = true;
        
        if (typeof model.value === 'object' && model.value !== null) {
            model.value = { id: '', name: '', qty: model.value?.qty ?? '' };
        } else {
            model.value = '';
        }
        emit('change', model.value); // fire event to parent
    });

    // focus on search input when open
    $(document).on('select2:open', () => {
        let searchField = document.querySelector('.select2-container--open .select2-search__field');
        if (searchField) {
            searchField.focus();
        }
    });
}

onMounted(() => {
    initSelect2();
    // Set initial value
    let initialValue = '';
    if (typeof model.value === 'object' && model.value !== null) {
        initialValue = model.value.id;
    } else {
        initialValue = model.value;
    }
    
    if (initialValue) {
        $(input.value).val(initialValue).trigger('change');
    }
});

// Keep Select2 updated if Vue model changes externally (not from Select2 itself)
let isSelectingFromSelect2 = false;

watch(dataOptions, () => {
    nextTick(() => {
        $(input.value).trigger('change');
    });
}, { deep: true });

watch(() => model.value, (newValue) => {
    // Skip if the change came from Select2 itself
    if (isSelectingFromSelect2) {
        isSelectingFromSelect2 = false;
        return;
    }
    
    let val = '';
    if (typeof newValue === 'object' && newValue !== null) {
        val = newValue.id;
    } else {
        val = newValue;
    }

    if (val != $(input.value).val()) {
        $(input.value).val(val || '').trigger('change');
    }
}, { deep: true });

onBeforeUnmount(() => {
    if (input.value) {
        $(input.value).select2('destroy');
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <select
        ref="input"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm js-example-basic-single"
    >
        <option></option>
        <option v-for="data in dataOptions" :key="data.id" :value="data.id">
            {{ data.name }}
        </option>
    </select>
</template>

<style>
.select2-container .select2-selection--single {
    height: 42px !important;
    border: 1px solid #d1d5db !important; /* border-gray-300 */
    border-radius: 0.375rem !important; /* rounded-md */
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important; /* shadow-sm */
    display: flex !important;
    align-items: center !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 42px !important;
    padding-left: 12px !important;
    color: #111827 !important; /* text-gray-900 */
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
}

/* Focus state */
.select2-container--open .select2-selection--single {
    border-color: #6366f1 !important; /* border-indigo-500 */
    box-shadow: 0 0 0 1px #6366f1 !important; /* ring-indigo-500 */
}
</style>
