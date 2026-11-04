<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Button from '@/Components/Button.vue';
import ProductSearch from '@/Components/ProductSearch.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Select from '@/Components/Select.vue';
import axios from 'axios';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    product: {
        type: Object,
        default: null,
    },
    businessName: {
        type: String,
        default: 'Business Name'
    }
});

onMounted(() => {
    // Apply user default barcode measurements (30 labels A4, 25mm barcode)
    applyBarcodePreset('30_a4');
    if (props.product) {
        addProduct(props.product);
    }
});

const selectedProducts = ref([]);
const previewProduct = ref(null);

// Checkbox selection state
const checkedRows = ref(new Set());
const bulkLabelCount = ref(1);
const bulkPackagingDate = ref(new Date().toISOString().split('T')[0]);

// Unique key for a product row
const rowKey = (product) => {
    return product.variationId ? `${product.id}-${product.variationId}` : `${product.id}`;
};

// Computed: are all rows selected?
const isAllSelected = computed(() => {
    return selectedProducts.value.length > 0 && checkedRows.value.size === selectedProducts.value.length;
});

// Computed: is the header checkbox indeterminate?
const isIndeterminate = computed(() => {
    return checkedRows.value.size > 0 && checkedRows.value.size < selectedProducts.value.length;
});

// Computed: selected count
const selectedCount = computed(() => checkedRows.value.size);

// Toggle select all
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        checkedRows.value = new Set();
    } else {
        const allKeys = new Set();
        selectedProducts.value.forEach(p => allKeys.add(rowKey(p)));
        checkedRows.value = allKeys;
    }
};

// Toggle single row
const toggleRow = (product) => {
    const key = rowKey(product);
    const newSet = new Set(checkedRows.value);
    if (newSet.has(key)) {
        newSet.delete(key);
    } else {
        newSet.add(key);
    }
    checkedRows.value = newSet;
};

// Check if row is checked
const isRowChecked = (product) => {
    return checkedRows.value.has(rowKey(product));
};

// Apply bulk label count to all checked rows
const applyBulkLabels = () => {
    selectedProducts.value.forEach(p => {
        if (isRowChecked(p)) {
            p.labels = bulkLabelCount.value;
        }
    });
};

// Apply bulk packaging date to all checked rows
const applyBulkDate = () => {
    selectedProducts.value.forEach(p => {
        if (isRowChecked(p)) {
            p.packagingDate = bulkPackagingDate.value;
        }
    });
};

// Remove all checked rows
const removeSelected = () => {
    selectedProducts.value = selectedProducts.value.filter(p => !isRowChecked(p));
    checkedRows.value = new Set();
    if (previewProduct.value && !selectedProducts.value.includes(previewProduct.value)) {
        previewProduct.value = selectedProducts.value.length > 0 ? selectedProducts.value[0] : null;
    }
};

// Add product to the table
const addProduct = (product) => {
    // Check if specifically selected a variation from search
    if (product._isSelectedVariation) {
        const parent = product._parent;
        const exists = selectedProducts.value.find(p => p.id === parent.id && p.variationId === product.id);

        if (!exists) {
            selectedProducts.value.push({
                id: parent.id,
                variationId: product.id,
                name: parent.name,
                variationName: product.var_name || product.name,
                barcode: product.var_sku || product.barcode || parent.barcode,
                retail: product.var_retail_price || product.retail || parent.retail,
                image: product.var_image ? '/storage/' + product.var_image : (parent.image_uri1 ? '/storage/' + parent.image_uri1 : null),
                labels: 1,
                packagingDate: new Date().toISOString().split('T')[0]
            });
        }

        // Update preview
        if (!previewProduct.value && selectedProducts.value.length > 0) {
            previewProduct.value = selectedProducts.value[selectedProducts.value.length - 1];
        } else if (previewProduct.value && previewProduct.value.id === parent.id && previewProduct.value.variationId === product.id) {
            // Already previewing?
        } else {
            previewProduct.value = selectedProducts.value.find(p => p.id === parent.id && p.variationId === product.id);
        }
        return;
    }

    // Check if product has variations (Parent selection behavior)
    if (product.variations && product.variations.length > 0) {
        // Add all variations
        product.variations.forEach(variation => {
            // Check uniqueness
            const exists = selectedProducts.value.find(p => p.id === product.id && p.variationId === variation.id);

            if (!exists) {
                selectedProducts.value.push({
                    id: product.id,
                    variationId: variation.id,
                    name: product.name,
                    variationName: variation.var_name || variation.name,
                    barcode: variation.var_sku || variation.barcode || product.barcode,
                    retail: variation.var_retail_price || variation.retail || product.retail,
                    image: variation.var_image ? '/storage/' + variation.var_image : (product.image_uri1 ? '/storage/' + product.image_uri1 : null),
                    labels: 1,
                    packagingDate: new Date().toISOString().split('T')[0]
                });
            }
        });
    } else {
        // Add single product (no variations)
        const exists = selectedProducts.value.find(p => p.id === product.id && !p.variationId);
        if (!exists) {
            selectedProducts.value.push({
                id: product.id,
                variationId: null,
                name: product.name,
                variationName: null, // No variation
                barcode: product.barcode,
                retail: product.retail,
                image: product.image_uri1 ? '/storage/' + product.image_uri1 : null,
                labels: 1,
                packagingDate: new Date().toISOString().split('T')[0]
            });
        }
    }

    // Set as preview if first product
    if (!previewProduct.value && selectedProducts.value.length > 0) {
        previewProduct.value = selectedProducts.value[0];
    }
};

// Remove product from table
const removeProduct = (index) => {
    const removed = selectedProducts.value[index];
    // Also remove from checked set
    checkedRows.value.delete(rowKey(removed));
    checkedRows.value = new Set(checkedRows.value);
    selectedProducts.value.splice(index, 1);

    // Update preview if removed product was being previewed
    if (previewProduct.value === removed) {
        previewProduct.value = selectedProducts.value.length > 0 ? selectedProducts.value[0] : null;
    }
};

// Set preview product
const setPreview = (product) => {
    previewProduct.value = product;
};

// Add all products with their variations to the table
const addAllProducts = () => {
    if (!props.products || props.products.length === 0) {
        alert('No products available to add.');
        return;
    }

    const today = new Date().toISOString().split('T')[0];

    props.products.forEach(product => {
        if (product.variations && product.variations.length > 0) {
            product.variations.forEach(variation => {
                const exists = selectedProducts.value.find(p => p.id === product.id && p.variationId === variation.id);
                if (!exists) {
                    selectedProducts.value.push({
                        id: product.id,
                        variationId: variation.id,
                        name: product.name,
                        variationName: variation.var_name || variation.name,
                        barcode: variation.var_sku || variation.barcode || product.barcode,
                        retail: variation.var_retail_price || variation.retail || product.retail,
                        image: variation.var_image ? '/storage/' + variation.var_image : (product.image_uri1 ? '/storage/' + product.image_uri1 : null),
                        labels: 1,
                        packagingDate: today
                    });
                }
            });
        } else {
            const exists = selectedProducts.value.find(p => p.id === product.id && !p.variationId);
            if (!exists) {
                selectedProducts.value.push({
                    id: product.id,
                    variationId: null,
                    name: product.name,
                    variationName: null,
                    barcode: product.barcode,
                    retail: product.retail,
                    image: product.image_uri1 ? '/storage/' + product.image_uri1 : null,
                    labels: 1,
                    packagingDate: today
                });
            }
        }
    });

    if (!previewProduct.value && selectedProducts.value.length > 0) {
        previewProduct.value = selectedProducts.value[0];
    }
};

// Clear all products from table
const clearAllProducts = () => {
    if (confirm('Are you sure you want to remove all products from the table?')) {
        selectedProducts.value = [];
        checkedRows.value = new Set();
        previewProduct.value = null;
    }
};

// Predefined Barcode & Sheet Layout Presets
const barcodePresets = {
    '30_a4': {
        id: '30_a4',
        name: '30 Labels (3 × 10, A4 - Munawar Sweets / 25mm Barcode)',
        sheetSize: 'A4',
        labelsPerSheet: 30,
        labelSize: '2.6x1',
        columns: 3,
        showBusinessName: true,
        businessNameSize: 10,
        showProductName: true,
        productNameSize: 12,
        showVariation: true,
        variationSize: 10,
        showPrice: true,
        priceSize: 14,
        showPackagingDate: false,
        packagingDateSize: 10,
        barcodeHeight: 25,
        barcodeWidth: 1,
        barcodeFontSize: 16
    },
    '20_a4': {
        id: '20_a4',
        name: '20 Labels (4 × 5, A4 Sheet)',
        sheetSize: 'A4',
        labelsPerSheet: 20,
        labelSize: '2x1',
        columns: 4,
        showBusinessName: true,
        businessNameSize: 10,
        showProductName: true,
        productNameSize: 13,
        showVariation: true,
        variationSize: 11,
        showPrice: true,
        priceSize: 15,
        showPackagingDate: false,
        packagingDateSize: 10,
        barcodeHeight: 30,
        barcodeWidth: 1,
        barcodeFontSize: 16
    },
    '32_a4': {
        id: '32_a4',
        name: '32 Labels (4 × 8, A4 Sheet)',
        sheetSize: 'A4',
        labelsPerSheet: 32,
        labelSize: '1.5x1',
        columns: 4,
        showBusinessName: true,
        businessNameSize: 9,
        showProductName: true,
        productNameSize: 11,
        showVariation: true,
        variationSize: 10,
        showPrice: true,
        priceSize: 13,
        showPackagingDate: false,
        packagingDateSize: 9,
        barcodeHeight: 22,
        barcodeWidth: 1,
        barcodeFontSize: 14
    },
    'thermal_standard': {
        id: 'thermal_standard',
        name: 'Single Thermal Label (2" × 1")',
        sheetSize: 'A4',
        labelsPerSheet: 1,
        labelSize: '2x1',
        columns: 1,
        showBusinessName: true,
        businessNameSize: 10,
        showProductName: true,
        productNameSize: 12,
        showVariation: true,
        variationSize: 11,
        showPrice: true,
        priceSize: 14,
        showPackagingDate: false,
        packagingDateSize: 10,
        barcodeHeight: 25,
        barcodeWidth: 1,
        barcodeFontSize: 16
    }
};

const presetOptions = [
    { id: '30_a4', name: '30 Labels (3 × 10, A4 - Munawar Sweets / 25mm)' },
    { id: '20_a4', name: '20 Labels (4 × 5, A4 Sheet)' },
    { id: '32_a4', name: '32 Labels (4 × 8, A4 Sheet)' },
    { id: 'thermal_standard', name: 'Single Thermal Label (2" × 1")' },
    { id: 'custom', name: 'Custom Configuration' }
];

const selectedPreset = ref('30_a4');

// Method to apply barcode and layout measurements
const applyBarcodePreset = (presetId) => {
    selectedPreset.value = presetId;
    const preset = barcodePresets[presetId];
    if (!preset) return;

    labelSettings.value.labelsPerSheet = preset.labelsPerSheet;
    labelSettings.value.sheetSize = preset.sheetSize;
    labelSettings.value.labelSize = preset.labelSize;
    labelSettings.value.columns = preset.columns;
    labelSettings.value.showBusinessName = preset.showBusinessName;
    labelSettings.value.businessNameSize = preset.businessNameSize;
    labelSettings.value.showProductName = preset.showProductName;
    labelSettings.value.productNameSize = preset.productNameSize;
    labelSettings.value.showVariation = preset.showVariation;
    labelSettings.value.variationSize = preset.variationSize;
    labelSettings.value.showPrice = preset.showPrice;
    labelSettings.value.priceSize = preset.priceSize;
    labelSettings.value.showPackagingDate = preset.showPackagingDate;
    labelSettings.value.packagingDateSize = preset.packagingDateSize;
    labelSettings.value.barcodeHeight = preset.barcodeHeight;
    labelSettings.value.barcodeWidth = preset.barcodeWidth;
    labelSettings.value.barcodeFontSize = preset.barcodeFontSize;
};

// When user changes labels per sheet dropdown
const onLabelsPerSheetChange = (val) => {
    const num = Number(val);
    labelSettings.value.labelsPerSheet = num;
    if (num === 30) {
        applyBarcodePreset('30_a4');
    } else if (num === 20) {
        applyBarcodePreset('20_a4');
    } else if (num === 32) {
        applyBarcodePreset('32_a4');
    } else {
        selectedPreset.value = 'custom';
    }
};

// When user changes label size dropdown
const onLabelSizeChange = (val) => {
    labelSettings.value.labelSize = val;
    if (val === '2.6x1') {
        applyBarcodePreset('30_a4');
    } else {
        selectedPreset.value = 'custom';
    }
};

// Label content & size settings
const labelSettings = ref({
    showProductName: true,
    showVariation: true,
    showPrice: true,
    showBusinessName: true,
    showPackagingDate: false,

    productNameSize: 12,
    variationSize: 10,
    priceSize: 14,
    businessNameSize: 10,
    packagingDateSize: 10,

    // Barcode measurements
    barcodeHeight: 25,
    barcodeWidth: 1,
    barcodeFontSize: 16,

    priceType: 'inc',

    labelsPerSheet: 30,
    sheetSize: 'A4',
    labelSize: '2.6x1',
    columns: 3
});

const labelsPerSheetOptions = [
    { id: 20, name: '20 Labels (4 × 5)' },
    { id: 30, name: '30 Labels (3 × 10, A4)' },
    { id: 32, name: '32 Labels (4 × 8)' },
    { id: 40, name: '40 Labels (4 × 10)' },
    { id: 50, name: '50 Labels (5 × 10)' }
];

const sheetSizeOptions = [
    { id: 'A4', name: 'A4' },
    { id: 'Letter', name: 'Letter' },
    { id: 'Legal', name: 'Legal' }
];

const labelSizeOptions = [
    { id: '2.6x1', name: '2.6" x 1" (30 per A4 Sheet)', width: '2.6in', height: '1in' },
    { id: '2x1', name: '2" x 1" Standard', width: '2in', height: '1in' },
    { id: '1.5x1', name: '1.5" x 1"', width: '1.5in', height: '1in' },
    { id: '1.25x0.5', name: '1.25" x 0.5"', width: '1.25in', height: '0.5in' },
    { id: 'custom', name: 'Custom Size', width: '2in', height: '1in' }
];

const priceTypeOptions = [
    { id: 'inc', name: 'Inc Tax' },
    { id: 'exc', name: 'Exc Tax' }
];

// Get current label dimensions
const currentLabelSize = computed(() => {
    return labelSizeOptions.find(opt => opt.id === labelSettings.value.labelSize) || labelSizeOptions[0];
});

// Total labels count
const totalLabels = computed(() => {
    return selectedProducts.value.reduce((sum, p) => sum + p.labels, 0);
});

// Print labels
const printLabels = async () => {
    if (selectedProducts.value.length === 0) {
        alert('Please select at least one product.');
        return;
    }

    try {
        const payload = {
            items: selectedProducts.value,
            settings: labelSettings.value,
            businessName: props.businessName,
            dimensions: {
                width: currentLabelSize.value.width,
                height: currentLabelSize.value.height
            }
        };

        const response = await axios.post('/product/store-print-session', payload);

        if (response.data.success) {
            window.open('/product/print-preview', '_blank');
        } else {
            alert('Failed to prepare print data.');
        }
    } catch (error) {
        console.error('Print error:', error);
        alert('An error occurred while preparing the print job.');
    }
};

</script>

<template>

    <Head title="Print Product Labels" />
    <AuthenticatedLayout>
        <!-- Modern Page Header -->
        <div class="mb-6">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h2 mb-2 text-gray-900 font-weight-bold">
                        <i class="fas fa-tags text-primary me-2"></i>
                        Print Product Labels
                    </h1>
                    <p class="text-gray-600 mb-0">Search products, customize labels, and print</p>
                </div>
                <div class="text-right">
                    <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                        <i class="fas fa-box me-1"></i>
                        {{ selectedProducts.length }} Product(s)
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column - Configuration -->
            <div class="col-lg-7">
                <!-- Modern Search Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-gradient-primary text-white border-0 py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-search me-2"></i>
                                Search Products
                            </h5>
                            <button @click="addAllProducts" type="button"
                                class="btn btn-sm btn-light text-primary fw-semibold shadow-sm rounded-pill px-3">
                                <i class="fas fa-boxes me-1"></i>
                                Add All Products (with Variations)
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <ProductSearch :products="products" @select="addProduct" />
                    </div>
                </div>

                <!-- Selected Products Table -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-list me-2 text-primary"></i>
                                    Selected Products
                                </h5>
                                <span v-if="selectedCount > 0"
                                    class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill ms-2">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ selectedCount }} selected
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button @click="addAllProducts" type="button"
                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none">
                                    <i class="fas fa-plus-circle me-1"></i> Add All Products
                                </button>
                                <button v-if="selectedProducts.length > 0" @click="clearAllProducts" type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none">
                                    <i class="fas fa-times me-1"></i> Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Action Bar -->
                    <div v-if="selectedCount > 0" class="bulk-action-bar">
                        <div class="d-flex align-items-center flex-wrap gap-3">
                            <div class="bulk-action-label">
                                <i class="fas fa-layer-group me-1"></i>
                                Bulk Actions
                            </div>

                            <!-- Bulk Label Count -->
                            <div class="bulk-action-group">
                                <label class="bulk-input-label">Labels</label>
                                <div class="input-group input-group-sm bulk-input-group">
                                    <TextInput type="number" v-model.number="bulkLabelCount" min="1"
                                        class="form-control form-control-sm" style="width: 70px;" />
                                    <button class="btn btn-sm btn-primary" @click="applyBulkLabels"
                                        title="Apply to selected">
                                        <i class="fas fa-check"></i> Apply
                                    </button>
                                </div>
                            </div>

                            <!-- Bulk Packaging Date -->
                            <div class="bulk-action-group">
                                <label class="bulk-input-label">Pkg Date</label>
                                <div class="input-group input-group-sm bulk-input-group">
                                    <TextInput type="date" v-model="bulkPackagingDate"
                                        class="form-control form-control-sm" />
                                    <button class="btn btn-sm btn-primary" @click="applyBulkDate"
                                        title="Apply to selected">
                                        <i class="fas fa-check"></i> Apply
                                    </button>
                                </div>
                            </div>

                            <!-- Bulk Delete -->
                            <button class="btn btn-sm btn-outline-danger ms-auto" @click="removeSelected">
                                <i class="fas fa-trash-alt me-1"></i> Remove Selected
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 modern-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 py-3" style="width: 48px;">
                                            <div class="custom-check pb-2" @click.stop>
                                                <input type="checkbox" :checked="isAllSelected"
                                                    :indeterminate="isIndeterminate" @change="toggleSelectAll"
                                                    class="form-check-input cursor-pointer" id="selectAll" />
                                            </div>
                                        </th>
                                        <th class="px-3 py-3">Product</th>
                                        <th class="px-3 py-3" style="width: 120px;">Labels</th>
                                        <th class="px-3 py-3" style="width: 180px;">Pkg Date</th>
                                        <th class="px-3 py-3 text-center" style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="selectedProducts.length === 0">
                                        <td colspan="5" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p class="mb-0">No products selected</p>
                                                <small class="text-muted">Search and add products above</small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="(product, index) in selectedProducts"
                                        :key="product.variationId ? `${product.id}-${product.variationId}` : product.id"
                                        @click="setPreview(product)"
                                        :class="{ 'table-active': previewProduct === product, 'row-checked': isRowChecked(product) }"
                                        style="cursor: pointer;">
                                        <td class="ps-4 py-3">
                                            <div class="custom-check" @click.stop>
                                                <input type="checkbox" :checked="isRowChecked(product)"
                                                    @change="toggleRow(product)"
                                                    class="form-check-input cursor-pointer" />
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="product-icon me-3">
                                                    <img v-if="product.image" :src="product.image" alt="Product"
                                                        class="w-100 h-100"
                                                        style="object-fit: cover; border-radius: 8px;">
                                                    <i v-else class="fas fa-cube"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-gray-900">
                                                        {{ product.name }}
                                                        <span v-if="product.variationName"
                                                            class="text-muted fw-normal small ms-1">
                                                            ({{ product.variationName }})
                                                        </span>
                                                    </div>
                                                    <div class="text-sm text-muted">
                                                        <i class="fas fa-barcode me-1"></i>{{ product.barcode }}
                                                    </div>
                                                    <div class="text-sm text-primary fw-semibold">Rs. {{ product.retail
                                                    }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <TextInput type="number" v-model.number="product.labels" min="1"
                                                class="form-control form-control-sm" @click.stop />
                                        </td>
                                        <td class="px-3 py-3">
                                            <TextInput type="date" v-model="product.packagingDate"
                                                class="form-control form-control-sm" @click.stop />
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <Button @click="removeProduct(index)"
                                                className="btn btn-sm btn-outline-danger rounded-circle"
                                                titleName="Remove" :shouldStop="true">
                                                <i class="fas fa-trash-alt"></i>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Label Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-sliders-h me-2 text-primary"></i>
                            Label Content & Size
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Product Name -->
                            <div class="col-md-6">
                                <div class="setting-item h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <Checkbox v-model:checked="labelSettings.showProductName"
                                            id="showProductName" />
                                        <InputLabel for="showProductName" value="Product Name"
                                            class="form-check-label fw-semibold mb-0 ml-2" />
                                    </div>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text">Size</span>
                                        <TextInput type="number" v-model.number="labelSettings.productNameSize" min="8"
                                            max="24" :disabled="!labelSettings.showProductName" class="flex-1" />
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Variation -->
                            <div class="col-md-6">
                                <div class="setting-item h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <Checkbox v-model:checked="labelSettings.showVariation" id="showVariation" />
                                        <InputLabel for="showVariation" value="Variation"
                                            class="form-check-label fw-semibold mb-0 ml-2" />
                                    </div>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text">Size</span>
                                        <TextInput type="number" v-model.number="labelSettings.variationSize" min="8"
                                            max="24" :disabled="!labelSettings.showVariation" class="flex-1" />
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="col-md-6">
                                <div class="setting-item h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <Checkbox v-model:checked="labelSettings.showPrice" id="showPrice" />
                                        <InputLabel for="showPrice" value="Price"
                                            class="form-check-label fw-semibold mb-0 ml-2" />
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <Select v-model="labelSettings.priceType" :options="priceTypeOptions"
                                                :disabled="!labelSettings.showPrice" class="w-100" />
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm flex-nowrap">
                                                <TextInput type="number" v-model.number="labelSettings.priceSize"
                                                    min="8" max="24" :disabled="!labelSettings.showPrice"
                                                    class="flex-1" />
                                                <span class="input-group-text">px</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Name -->
                            <div class="col-md-6">
                                <div class="setting-item h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <Checkbox v-model:checked="labelSettings.showBusinessName"
                                            id="showBusinessName" />
                                        <InputLabel for="showBusinessName" value="Business Name"
                                            class="form-check-label fw-semibold mb-0 ml-2" />
                                    </div>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text">Size</span>
                                        <TextInput type="number" v-model.number="labelSettings.businessNameSize" min="8"
                                            max="24" :disabled="!labelSettings.showBusinessName" class="flex-1" />
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Packaging Date -->
                            <div class="col-md-6">
                                <div class="setting-item h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <Checkbox v-model:checked="labelSettings.showPackagingDate"
                                            id="showPackagingDate" />
                                        <InputLabel for="showPackagingDate" value="Packaging Date"
                                            class="form-check-label fw-semibold mb-0 ml-2" />
                                    </div>
                                    <div class="input-group input-group-sm flex-nowrap">
                                        <span class="input-group-text">Size</span>
                                        <TextInput type="number" v-model.number="labelSettings.packagingDateSize"
                                            min="8" max="24" :disabled="!labelSettings.showPackagingDate"
                                            class="flex-1" />
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barcode Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-cog me-2 text-primary"></i>
                            Sheet & Barcode Settings
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Preset / Template Selection -->
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <InputLabel for="barcodePreset" class="fw-bold text-primary mb-0">
                                        <i class="fas fa-magic me-1"></i> Barcode Preset / Template
                                    </InputLabel>
                                    <span class="badge bg-success-subtle text-success small">Recommended</span>
                                </div>
                                <Select v-model="selectedPreset" id="barcodePreset" :options="presetOptions"
                                    @update:modelValue="applyBarcodePreset" class="w-100" />
                            </div>

                            <div class="col-md-4">
                                <InputLabel for="labelsPerSheet">Labels Per Sheet</InputLabel>
                                <Select :modelValue="labelSettings.labelsPerSheet" id="labelsPerSheet"
                                    :options="labelsPerSheetOptions" @update:modelValue="onLabelsPerSheetChange" />
                            </div>

                            <div class="col-md-4">
                                <InputLabel for="sheetSize">Sheet Size</InputLabel>
                                <Select v-model="labelSettings.sheetSize" id="sheetSize" :options="sheetSizeOptions" />
                            </div>

                            <div class="col-md-4">
                                <InputLabel for="labelSize">Label Size</InputLabel>
                                <Select :modelValue="labelSettings.labelSize" id="labelSize" :options="labelSizeOptions"
                                    @update:modelValue="onLabelSizeChange" />
                            </div>

                            <!-- Barcode Dimension Fine Tuning -->
                            <div class="col-md-4">
                                <InputLabel for="barcodeHeight">Barcode Height</InputLabel>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <TextInput type="number" v-model.number="labelSettings.barcodeHeight" min="15"
                                        max="60" id="barcodeHeight" class="flex-1" />
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <InputLabel for="barcodeWidth">Bar Width</InputLabel>
                                <TextInput type="number" v-model.number="labelSettings.barcodeWidth" min="1" max="4"
                                    step="0.5" id="barcodeWidth" class="form-control form-control-sm" />
                            </div>

                            <div class="col-md-4">
                                <InputLabel for="barcodeFontSize">Barcode Font Size</InputLabel>
                                <div class="input-group input-group-sm flex-nowrap">
                                    <TextInput type="number" v-model.number="labelSettings.barcodeFontSize" min="8"
                                        max="24" id="barcodeFontSize" class="flex-1" />
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Live Preview -->
            <div class="col-lg-5">
                <div class="sticky-preview">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-gradient-primary text-white border-0 py-3">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-eye me-2"></i>
                                Live Preview
                            </h5>
                        </div>
                        <div class="card-body p-4 bg-light">
                            <!-- Preview Info -->
                            <div v-if="previewProduct" class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <small class="text-muted">Previewing:</small>
                                    <span class="badge bg-info text-white">{{ currentLabelSize.width }} × {{
                                        currentLabelSize.height }}</span>
                                </div>
                                <div class="fw-semibold text-gray-900">{{ previewProduct.name }}</div>
                            </div>

                            <!-- Label Preview -->
                            <div class="preview-container">
                                <div v-if="!previewProduct" class="preview-empty">
                                    <i class="fas fa-tag fa-3x mb-3"></i>
                                    <p class="mb-0">Select a product to preview</p>
                                </div>

                                <div v-else class="label-preview" :class="'label-' + labelSettings.labelSize">
                                    <!-- Business Name -->
                                    <div v-if="labelSettings.showBusinessName" class="label-business"
                                        :style="{ fontSize: labelSettings.businessNameSize + 'px' }">
                                        {{ businessName }}
                                    </div>

                                    <!-- Product Name -->
                                    <div v-if="labelSettings.showProductName" class="label-product-name"
                                        :style="{ fontSize: labelSettings.productNameSize + 'px' }">
                                        {{ previewProduct.name }}
                                    </div>

                                    <!-- Variation -->
                                    <div v-if="labelSettings.showVariation && previewProduct.variationName"
                                        class="label-variation"
                                        :style="{ fontSize: labelSettings.variationSize + 'px' }">
                                        {{ previewProduct.variationName }}
                                    </div>

                                    <!-- Barcode -->
                                    <div class="label-barcode">
                                        <svg viewBox="0 0 100 30" class="barcode-svg"
                                            :style="{ height: (labelSettings.barcodeHeight || 25) + 'px' }">
                                            <rect x="2" y="0" width="2" height="30" fill="#000" />
                                            <rect x="6" y="0" width="1" height="30" fill="#000" />
                                            <rect x="9" y="0" width="3" height="30" fill="#000" />
                                            <rect x="14" y="0" width="1" height="30" fill="#000" />
                                            <rect x="17" y="0" width="2" height="30" fill="#000" />
                                            <rect x="21" y="0" width="1" height="30" fill="#000" />
                                            <rect x="24" y="0" width="3" height="30" fill="#000" />
                                            <rect x="29" y="0" width="2" height="30" fill="#000" />
                                            <rect x="33" y="0" width="1" height="30" fill="#000" />
                                            <rect x="36" y="0" width="2" height="30" fill="#000" />
                                            <rect x="40" y="0" width="3" height="30" fill="#000" />
                                            <rect x="45" y="0" width="1" height="30" fill="#000" />
                                            <rect x="48" y="0" width="2" height="30" fill="#000" />
                                            <rect x="52" y="0" width="1" height="30" fill="#000" />
                                            <rect x="55" y="0" width="3" height="30" fill="#000" />
                                            <rect x="60" y="0" width="2" height="30" fill="#000" />
                                            <rect x="64" y="0" width="1" height="30" fill="#000" />
                                            <rect x="67" y="0" width="2" height="30" fill="#000" />
                                            <rect x="71" y="0" width="3" height="30" fill="#000" />
                                            <rect x="76" y="0" width="1" height="30" fill="#000" />
                                            <rect x="79" y="0" width="2" height="30" fill="#000" />
                                            <rect x="83" y="0" width="1" height="30" fill="#000" />
                                            <rect x="86" y="0" width="3" height="30" fill="#000" />
                                            <rect x="91" y="0" width="2" height="30" fill="#000" />
                                            <rect x="95" y="0" width="1" height="30" fill="#000" />
                                        </svg>
                                        <div class="barcode-text"
                                            :style="{ fontSize: (labelSettings.barcodeFontSize || 16) + 'px' }">
                                            {{ previewProduct.barcode }}
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div v-if="labelSettings.showPrice" class="label-price"
                                        :style="{ fontSize: labelSettings.priceSize + 'px' }">
                                        Rs. {{ previewProduct.retail }}
                                        <span class="price-type">({{ labelSettings.priceType === 'inc' ? 'Inc' : 'Exc'
                                        }} Tax)</span>
                                    </div>

                                    <!-- Packaging Date -->
                                    <div v-if="labelSettings.showPackagingDate" class="label-date"
                                        :style="{ fontSize: labelSettings.packagingDateSize + 'px' }">
                                        Pkg: {{ previewProduct.packagingDate }}
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Note -->
                            <div class="mt-3 p-3 bg-white rounded border border-info">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle text-info me-2 mt-1"></i>
                                    <small class="text-muted">
                                        This is a live preview. Changes to settings update in real-time.
                                        Click on products in the table to preview different items.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Print Button -->
                    <div class="mt-4">
                        <Button @click="printLabels" :className="'btn btn-primary btn-lg w-100 py-3 shadow-sm'"
                            :disabled="selectedProducts.length === 0">
                            <i class="fas fa-print me-2"></i>
                            Print {{ totalLabels }} Label{{ totalLabels !== 1 ? 's' : '' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Modern Gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Card header rounded corners */
.card .card-header.bg-gradient-primary {
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

/* Modern Table */
.modern-table tbody tr {
    transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: #f9fafb;
}

.modern-table tbody tr.row-checked {
    background-color: #667eea0d;
    border-left: 3px solid #667eea;
}

.modern-table tbody tr.row-checked:hover {
    background-color: #667eea18;
}

.product-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
}

/* Checkbox Styling */
.custom-check {
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-check .form-check-input {
    width: 18px;
    height: 18px;
    margin: 0;
    cursor: pointer;
    border: 2px solid #c4c9d4;
    border-radius: 4px;
    transition: all 0.15s ease-in-out;
}

.custom-check .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.custom-check .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.cursor-pointer {
    cursor: pointer;
}

/* Bulk Action Bar */
.bulk-action-bar {
    background: linear-gradient(135deg, #667eea08 0%, #764ba208 100%);
    border-bottom: 1px solid #e5e7eb;
    border-top: 1px solid #667eea30;
    padding: 12px 20px;
    animation: slideDown 0.25s ease-out;
}

.bulk-action-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #667eea;
    white-space: nowrap;
    padding: 4px 12px;
    background: #667eea15;
    border-radius: 6px;
}

.bulk-action-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.bulk-input-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #6b7280;
    white-space: nowrap;
    margin-bottom: 0;
}

.bulk-input-group {
    min-width: 150px;
}

.bulk-input-group .btn-primary {
    background: #667eea;
    border-color: #667eea;
    white-space: nowrap;
}

.bulk-input-group .btn-primary:hover {
    background: #5a6fd6;
    border-color: #5a6fd6;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    to {
        opacity: 1;
        max-height: 80px;
        padding-top: 12px;
        padding-bottom: 12px;
    }
}

/* Empty State */
.empty-state {
    color: #9ca3af;
}

.empty-state i {
    color: #d1d5db;
}

/* Setting Items */
.setting-item {
    padding: 16px;
    background: #f9fafb;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.setting-item:hover {
    border-color: #667eea;
    background: white;
}

/* Add padding to input group labels */
.setting-item .input-group-text {
    padding: 0.375rem 0.75rem;
    min-width: 50px;
}

/* Sticky Preview */
.sticky-preview {
    position: sticky;
    top: 100px;
}

/* Preview Container */
.preview-container {
    background: white;
    border-radius: 12px;
    padding: 24px;
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #e5e7eb;
}

.preview-empty {
    text-align: center;
    color: #9ca3af;
}

.preview-empty i {
    color: #d1d5db;
}

/* Label Preview */
.label-preview {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 300px;
}

.label-small {
    max-width: 200px;
}

.label-standard {
    max-width: 250px;
}

.label-medium {
    max-width: 300px;
}

.label-large {
    max-width: 350px;
}

.label-business {
    text-align: center;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.label-product-name {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
    line-height: 1.3;
}

.label-variation {
    color: #6b7280;
    margin-bottom: 12px;
}

.label-barcode {
    margin: 16px 0;
    text-align: center;
}

.barcode-svg {
    width: 100%;
    height: 40px;
    margin-bottom: 4px;
}

.barcode-text {
    font-size: 18px;
    font-family: 'Courier New', monospace;
    color: #374151;
    letter-spacing: 2px;
    font-weight: bold;
}

.label-price {
    font-weight: 700;
    color: #667eea;
    text-align: center;
    margin: 12px 0;
}

.price-type {
    font-size: 0.75em;
    font-weight: 400;
    color: #6b7280;
}

.label-date {
    text-align: center;
    color: #6b7280;
    margin-top: 8px;
}

/* Responsive */
@media (max-width: 991px) {
    .sticky-preview {
        position: relative;
        top: 0;
    }
}

@media (max-width: 768px) {
    .bulk-action-bar {
        padding: 10px 12px;
    }

    .bulk-action-bar .d-flex {
        gap: 8px !important;
    }

    .bulk-action-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
