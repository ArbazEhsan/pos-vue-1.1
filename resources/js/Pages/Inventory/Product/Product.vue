<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, defineProps, ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';
import Modal2 from '@/Components/Modal2.vue';
import Table from '@/Components/Table.vue';
import BaseDataTable from '@/Components/baseDataTable.vue';
import FilterAccordion from '@/Components/FilterAccordion.vue';


const props = defineProps({
    products: {
        type: Array,
    },
});

const page = usePage()
const permissions = page.props.auth.user.permission?.pages;
const user = page.props.auth.user.type;

// const dataTableColumns = () => {

//       return [
//           { data: null, render: (data, type, row, meta) => meta.row + 1, orderable: false },
//           {
//               data: "image_uri1",
//               render: function (data, type, row) {
//                   const imagePath = data ? `/storage/${data}` : '/assets/img/placeholder2.png';
//                   return `<div class="">
//                           <img src="${imagePath}" alt="Category Image" class="" style="width: 44px; height: 44px;">   
//                       </div>`;
//               },
//               orderable: false,
//           },
//           { data: "name", render: (data, type, row) => `<div>${data}</div><div class="mt-1" style="font-size:11px; font-weight:bold">(SKU:${row.barcode})</div>` },
//           { data: "purchase" },
//           { data: "whole_sale" },
//           { data: "retail" },
//           { data: "variation_name", defaultContent: "Single" },
//           { data: "category.name", defaultContent: "N/A" },
//           { data: "brand", defaultContent: "N/A" },
//           { data: "qty" },
//           {
//               data: "status",
//               render: (data) => `
//                   <span class="py-1 px-1 rounded text-white text-xs ${data === 0 ? 'bg-red-500' : 'bg-green-500'}">
//                       ${data === 1 ? "Active" : "Unactive"}
//                   </span>
//               `,
//               orderable: false,
//           },
//           {
//               data: "id",
//               render: (data, type, row) => {
//                   let btns = `<button class="mr-2 text-blue-600 hover:text-blue-800 view-btn" data-id="${data}" title="View Details"><i class="fas fa-eye"></i></button>`;

//                   // Use the user/permissions defined in the outer scope
//                   if (user.type == 'Admin' || permissions?.product_update) {
//                        btns += `
//                           <a href="/editProduct/${data}" class="mr-2"><i class="fas fa-edit"></i></a>
//                           <a href="/statusProduct/${row.status}/${data}" class="ml-2"><i class="fas fa-exchange-alt"></i></a>
//                       `;
//                   }
//                   return btns;
//               },
//               orderable: false,
//           },
//       ];
// }

const selectedProduct = ref(null);
const filterType = ref('all');

const filterOptions = [
    { id: 'all', name: 'All Products' },
    { id: 'consumable', name: 'Consumable Only' },
    { id: 'sellable', name: 'Sellable Only' },
    { id: 'single', name: 'Single Products' },
    { id: 'variation', name: 'Variation Products' },
    { id: 'combo', name: 'Combo Products' },
];

const ajaxUrl = computed(() => {
    let url = '/getProductDetails';
    const params = [];
    
    if (filterType.value === 'consumable') {
        params.push('consumable=true');
    } else if (filterType.value === 'sellable') {
        params.push('sellable=true');
    } else if (filterType.value !== 'all') {
        params.push(`type=${filterType.value}`);
    }
    
    return url + (params.length > 0 ? '?' + params.join('&') : '');
});

const viewProduct = (id) => {
    console.log(id);
    // Find product in props.products (Since controller passes them to Inertia)
    // or try finding in DataTable data if props are stale (though here they load together/AJAX)
    // We prefer props.products as it's cleaner to access
    if (props.products) {
        const product = props.products.find(p => p.id == id);
        if (product) {
            // Calculate computed stats
            // Note: sales_sum_qty comes from backend specific query optimization
            const totalSold = product.sales_sum_qty || 0;
            const stockValue = (parseFloat(product.qty) * parseFloat(product.purchase)).toFixed(2);
            
            // Enrich object slightly for display
            product.total_unit_sold = totalSold;
            product.current_stock_value = stockValue;

            selectedProduct.value = product;
            $('#modal_product').modal('show');
        }
    }
};

const productColumns = [
    { data: null, title: 'Sr#', name: 'index', orderable: false, slots: { default: 'column-index' } },
    { data: "image_uri1", title: "Image", name: 'product-image', orderable: false, slots: { default: 'column-product-image' } },
    { data: "name", title: "Name", name: 'product-name', slots: { default: 'column-product-name' } },
    { data: "purchase", title: "P.P"},
    { data: "whole_sale", title: "W.P" },
    { data: "retail", title: "R.P" },
    { data: "type", title: "Type" },
    { data: "category.name", title: "Cat" },
    { data: "brand.name", title: "Brand" },
    { data: "qty", title: "Qty" },
    { data: "status", title: "Status", name: 'product-status', slots: { default: 'column-product-status' } },
    { data: null, title: "Action", name: 'product-actions', orderable: false, className: 'text-center overflow-visible', slots: { default: 'column-product-actions' } },
];

const variationColumns = [
    { label: "Variations", key: "Variations" },
    { label: "SKU", key: "SKU" },
    { label: "Purchase", key: "Purchase" },
    { label: "Whole Sale", key: "Whole Sale" },
    { label: "Retail", key: "Retail" },
];

const variationRows = computed(() => {
    if (!selectedProduct.value || !selectedProduct.value.variations) return [];
    return selectedProduct.value.variations.map(v => ({
        "Variations": selectedProduct.value.variation_name + ' - ' + v.var_name,
        "SKU": v.var_sku,
        "Purchase": v.var_purchase_price,
        "Whole Sale": v.var_wholesale_price,
        "Retail": v.var_retail_price,
    }));
});

const comboColumns = [
    { label: "Combo/Bundle", key: "Combo" },
    { label: "SKU", key: "SKU" },
    { label: "Price", key: "Price" },
    { label: "Qty", key: "Qty" },
];

const comboRows = computed(() => {
    if (!selectedProduct.value || !selectedProduct.value.combos) return [];
    return selectedProduct.value.combos.map(c => {
        let name = 'Unknown';
        let sku = '';
        
        if (c.combo_variation) {
            const pName = c.combo_variation.product?.name || '';
            const vType = c.combo_variation.product?.variation_name || '';
            const vName = c.combo_variation.var_name || '';
            name = `${pName} - ${vType} - ${vName}`;
            sku = c.combo_variation.var_sku;
        } else if (c.combo_product) {
            name = c.combo_product.name;
            sku = c.combo_product.barcode;
        }

        return {
            "Combo": name,
            "SKU": sku,
            "Price": parseFloat(c.price).toFixed(2),
            "Qty": parseFloat(c.quantity),
        };
    });
});

const stockColumns = [
    { label: "SKU", key: "SKU" },
    { label: "Product", key: "Product" },
    { label: "Retail", key: "Retail" },
    { label: "Current Stock", key: "Current Stock" },
    { label: "Current Stock Value", key: "Current Stock Value" },
    { label: "Total Unit Sold", key: "Total Unit Sold" },
];

const stockRows = computed(() => {
    if (!selectedProduct.value) return [];
    
    if (selectedProduct.value.variation_name && selectedProduct.value.variation_name !== 'Single' && selectedProduct.value.variations?.length > 0) {
        return selectedProduct.value.variations.map(v => ({
            "SKU": v.var_sku || v.sku, // Handle different casing if needed
            "Product": selectedProduct.value.name + ' - ' + selectedProduct.value.variation_name + ' - ' + v.var_name,
            "Retail": v.var_price || v.var_retail_price,
            "Current Stock": v.var_qty + ' ' + selectedProduct.value.weight_unit,
            "Current Stock Value": ((parseFloat(v.var_qty) || 0) * (parseFloat(v.var_purchase_price) || 0)).toFixed(2),
            "Total Unit Sold": "N/A" // Track variation sales if data available, else N/A
        }));
    }

    // Default Single Product
    return [{
        "SKU": selectedProduct.value.barcode,
        "Product": selectedProduct.value.name,
        "Retail": selectedProduct.value.retail,
        "Current Stock": selectedProduct.value.qty,
        "Current Stock Value": selectedProduct.value.current_stock_value,
        "Total Unit Sold": selectedProduct.value.total_unit_sold
    }];
});

const moreActions = [
    { label: 'View', href: null, icon: 'fas fa-eye mr-2', action: 'view' },
    { label: 'Edit', href: 'editProduct', icon: 'fas fa-edit mr-2' },
    { label: 'Labels', href: 'printLabelForm', icon: 'fas fa-barcode mr-2' },
    { label: 'Change Status', href: 'statusProduct', icon: 'fas fa-exchange-alt mr-2', params: (row) => [row.status, row.id] },
    { label: 'Stock History', href: 'product.stockHistory', icon: 'fas fa-history mr-2' },
    { label: 'Duplicate', href: 'product.duplicateProduct', icon: 'fas fa-copy mr-2' },
];

onMounted(() => {
    window.props = props;
});


</script>

<template>

    <Head title="Products" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800"><i class="fa fa-shopping-basket"></i> Products </h1>
            <Link :href="$page.props.auth.user.type == 'Admin' || $page.props.auth.user.permission?.pages?.product_create ? route('addProduct') : '#'" class="btn btn-primary"><i class="fa fa-plus mr-1 text-sm"></i> New</Link>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <!-- Filters Accordion -->
                        <FilterAccordion
                            v-model="filterType"
                            :filterOptions="filterOptions"
                            title="Filters"
                        />
                        
                        <BaseDataTable 
                            :key="ajaxUrl"
                            :ajaxUrl="ajaxUrl"
                            :columns="productColumns"
                            :permission="permissions?.product_update"
                            :userType="user"
                            :moreActions="moreActions"
                            :theadClass="'text-xs'"
                            class="text-sm"
                            :viewModal="true"
                            @view="viewProduct"
                        />
                    </div>
                </div>
            </div>
        </div>

        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />

        <Modal2 id="modal_product" :Class="'sm:max-w-6xl'" :title="selectedProduct?.name || 'Product Details'">
            <div v-if="selectedProduct" class="container-fluid">
                <!-- Basic Info & Image -->
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                         <img 
                            :src="selectedProduct.image_uri1 ? `/storage/${selectedProduct.image_uri1}` : '/assets/img/placeholder2.png'" 
                            class="img-fluid rounded shadow-sm mb-2" 
                            style="max-height: 150px; object-fit: cover;"
                            alt="Product Image"
                        />
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-sm-4 mb-3">
                                <label class="fw-bold text-muted small text-uppercase">Brand</label>
                                <div class="fw-bold text-dark">{{ selectedProduct.brand?.name || 'N/A' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <label class="fw-bold text-muted small text-uppercase">Category</label>
                                <div class="fw-bold text-dark">{{ selectedProduct.category?.name }}</div>
                            </div>
                            <div class="col-sm-4">
                                <label class="fw-bold text-muted small text-uppercase">Unit</label>
                                <div>{{ selectedProduct.weight_unit || 'N/A' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <label class="fw-bold text-muted small text-uppercase">Alert Quantity</label>
                                <div class="text-danger fw-bold">{{ selectedProduct.min_qty }}</div>
                            </div>
                            <div class="col-sm-4">
                                <label class="fw-bold text-muted small text-uppercase">Expiry Date</label>
                                <div>{{ selectedProduct.expiry_date ? new Date(selectedProduct.expiry_date).toLocaleDateString('en-GB') : 'N/A' }}</div>
                            </div>
                            <div class="col-sm-4">
                                <label class="fw-bold text-muted small text-uppercase">Sellable</label>
                                <div>
                                    <span v-if="selectedProduct.sellable" class="badge bg-success text-white">Yes</span>
                                    <span v-else class="badge bg-secondary text-white">No</span>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label class="fw-bold text-muted small text-uppercase">Consumable</label>
                                <div>
                                    <span v-if="selectedProduct.consumable" class="badge bg-success text-white">Yes</span>
                                    <span v-else class="badge bg-secondary text-white">No</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Variations Table -->
                <div class="row mb-4" v-if="variationRows.length > 0">
                    <div class="col-md-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-layer-group me-2"></i> Variations
                        </h6>
                        <Table class="text-sm" :columns="variationColumns" :rows="variationRows" />
                    </div>
                </div>
                
                <!-- Combo Table -->
                <div class="row mb-4" v-if="comboRows.length > 0">
                    <div class="col-md-12">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-layer-group me-2"></i> Combo
                        </h6>
                        <Table class="text-sm" :columns="comboColumns" :rows="comboRows" />
                    </div>
                </div>

                <!-- Stock Details Table -->
                <div class="row">
                    <div class="col-md-12">
                         <h6 class="fw-bold text-success mb-3">
                            <i class="fas fa-warehouse me-2"></i> Stock Details
                        </h6>
                        <Table class="text-sm" :columns="stockColumns" :rows="stockRows" />
                    </div>
                </div>  
            </div>
        </Modal2>

    </AuthenticatedLayout>
</template>
