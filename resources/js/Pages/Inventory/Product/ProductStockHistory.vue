<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductSearch from '@/Components/ProductSearch.vue';
import baseDataTable from '@/Components/baseDataTable.vue';
import Select from '@/Components/Select.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    product: {
        type: Object,
        default: null
    },
    selectedVariation: {
        type: Object,
        default: null
    },
    summary: {
        type: Object,
        default: () => ({
            qty_in: { total: 0 },
            qty_out: { total: 0 },
            current_stock: 0
        })
    }
});

const selectedVariationId = ref(null);

// Initialize selected variation from props
onMounted(() => {
    if (props.selectedVariation) {
        selectedVariationId.value = props.selectedVariation.id;
    }
});

const handleProductSelect = (selectedProduct) => {
    // Check if a variation was selected
    if (selectedProduct._isSelectedVariation && selectedProduct._parent) {
        // Navigate to product with variation_id
        router.visit(route('product.stockHistory', {
            id: selectedProduct._parent.id,
            variation_id: selectedProduct.id
        }));
    } else {
        // Navigate to product without variation
        router.visit(route('product.stockHistory', selectedProduct.id));
    }
};

const handleVariationChange = () => {
    if (!props.product) return;
    
    if (selectedVariationId.value) {
        // Navigate with variation_id
        router.visit(route('product.stockHistory', {
            id: props.product.id,
            variation_id: selectedVariationId.value
        }));
    } else {
        // Navigate without variation (show main product)
        router.visit(route('product.stockHistory', props.product.id));
    }
};

// Compute the DataTable AJAX URL with variation_id if selected
const dataTableUrl = computed(() => {
    if (!props.product) return '';
    
    let url = route('product.stockHistoryApi', props.product.id);
    
    if (selectedVariationId.value) {
        url += `?variation_id=${selectedVariationId.value}`;
    }
    
    return url;
});

// Display name for current selection
const displayName = computed(() => {
    if (!props.product) return '';
    
    if (props.selectedVariation) {
        return `${props.product.name} - ${props.selectedVariation.var_name}`;
    }
    
    return props.product.name;
});

// Display SKU for current selection
const displaySku = computed(() => {
    if (props.selectedVariation) {
        return props.selectedVariation.var_sku;
    }
    
    return props.product?.barcode || '';
});

// Format variations for Select component (expects id and name properties)
const variationOptions = computed(() => {
    if (!props.product?.variations) return [];
    
    // Add "All (Main Product)" option
    const options = [
        { id: null, name: 'All (Main Product)' }
    ];
    
    // Add variations with formatted names
    props.product.variations.forEach(variation => {
        options.push({
            id: variation.id,
            name: `${variation.var_name} (${variation.var_sku})`
        });
    });
    
    return options;
});

const columns = [
    { data: 'date', title: 'Date' },
    { data: 'type', title: 'Type' },
    { data: 'qty_change', title: 'Change', 
      render: (data, type, row) => {
         // data is string like "-5" or "+100" or int
         const val = parseFloat(data);
         // Format with sign if missing? API sends formatted string '+10', '-5'.
         // But parseFloat handles it.
         let color = 'text-gray-600';
         if (val > 0) color = 'text-green-600';
         if (val < 0) color = 'text-red-600';
         
         return `<span class="font-bold ${color}">${data}</span>`;
      }
    },
    { data: 'new_qty', title: 'New Qty', className: 'font-mono font-bold' },
    { data: 'reference_no', title: 'Reference' },
    { data: 'info', title: 'Customer/Supplier' }
];

</script>

<template>
    <Head title="Product Stock History" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Header & Search -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Product Stock History
                    </h2>
                    <div class="w-full md:w-96">
                        <!-- ProductSearch passes entire product object on select -->
                        <ProductSearch 
                            :products="products"
                            @select="handleProductSelect"
                            placeholder="Search product for history..."
                        />
                    </div>
                </div>

                <div v-if="product" class="space-y-6">
                    <!-- Product Info Header -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-start border-b pb-4 mb-4">
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ displayName }} 
                                </h3>
                                <p class="text-gray-500">SKU: {{ displaySku }}</p>
                            </div>
                            
                            <!-- Variation Selector -->
                            <div v-if="product.variations && product.variations.length > 0" class="ml-4">
                                <InputLabel for="variation-select" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-filter mr-1"></i>
                                    Filter by Variation
                                </InputLabel>
                                <Select 
                                    id="variation-select"
                                    v-model="selectedVariationId"
                                    @update:modelValue="handleVariationChange"
                                    :options="variationOptions"
                                    class="min-w-[250px]"
                                />
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- Qty In Card -->
                            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border border-emerald-200 shadow-sm transition hover:shadow-md">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-emerald-800 font-semibold opacity-80 uppercase text-xs tracking-wider">Total Qty In</span>
                                    <div class="bg-white bg-opacity-60 p-2 rounded-full shadow-sm">
                                        <i class="fas fa-arrow-down text-emerald-600"></i>
                                    </div>
                                </div>
                                <div class="text-3xl font-bold text-emerald-700 mb-4">{{ summary.qty_in.total }}</div>
                                <div class="space-y-2 text-sm text-emerald-800 border-t border-emerald-200 pt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Purchases</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_in.purchase }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Opening Stock</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_in.opening }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Sale Returns</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_in.sale_return }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Transfers In</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_in.transfer_in }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Qty Out Card -->
                            <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-xl p-5 border border-rose-200 shadow-sm transition hover:shadow-md">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-rose-800 font-semibold opacity-80 uppercase text-xs tracking-wider">Total Qty Out</span>
                                    <div class="bg-white bg-opacity-60 p-2 rounded-full shadow-sm">
                                        <i class="fas fa-arrow-up text-rose-600"></i>
                                    </div>
                                </div>
                                <div class="text-3xl font-bold text-rose-700 mb-4">{{ summary.qty_out.total }}</div>
                                <div class="space-y-2 text-sm text-rose-800 border-t border-rose-200 pt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Sold</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_out.sold }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Purchase Returns</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_out.purchase_return }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Adjustments</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_out.adjustment }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="opacity-75">Transfers Out</span>
                                        <span class="font-semibold bg-white bg-opacity-40 px-2 rounded">{{ summary.qty_out.transfer_out }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Stock Card -->
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-md flex flex-col justify-between hover:shadow-lg transition">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-gray-500 font-semibold uppercase text-xs tracking-wider">Current Stock</span>
                                        <div class="bg-blue-50 p-2 rounded-full">
                                            <i class="fas fa-cube text-blue-500"></i>
                                        </div>
                                    </div>
                                    <div class="text-5xl font-extrabold text-blue-600 tracking-tight">{{ summary.current_stock }}</div>
                                </div>
                                <div class="mt-4 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Calculated from history
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History DataTable -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-bold text-gray-800">
                                <i class="fas fa-history mr-2 text-blue-500"></i>
                                Transaction History
                            </h4>
                        </div>
                        
                        <div class="overflow-hidden">
                             <baseDataTable 
                                :key="dataTableUrl"
                                :columns="columns"
                                :ajaxUrl="dataTableUrl"
                                :theadClass="'text-xs'"
                                class="text-sm"
                            />
                        </div>
                    </div>
                </div>
                
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-16 text-center">
                    <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                        <i class="fas fa-search text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Select a Product</h3>
                    <p class="text-gray-500">Use the search bar above to view stock history for a specific product.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
