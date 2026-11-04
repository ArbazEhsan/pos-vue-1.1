<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const modules = computed(() => [
    // {
    //     title: 'Category',
    //     description: 'Manage product categories and subcategories.',
    //     route: 'category',
    //     icon: 'fas fa-tags',
    //     colorClass: 'bg-info-light text-info',
    //     permission: user.value.type === 'Admin' || user.value.permission?.pages?.category_view,
    //     isBlade: false
    // },
    // {
    //     title: 'Brands',
    //     description: 'Manage Brands.',
    //     route: 'brand',
    //     icon: 'fas fa-tags',
    //     colorClass: 'bg-info-light text-info',
    //     permission: user.value.type === 'Admin' || user.value.permission?.pages?.category_view,
    //     isBlade: false
    // },
    {
        title: 'Products',
        description: 'Add, edit, and view all products in inventory inc. Stocks',
        route: 'viewProduct',
        icon: 'fas fa-box-open',
        colorClass: 'bg-primary-light text-primary',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.product_view,
        isBlade: false
    },
    {
        title: 'Manufacturing',
        description: 'Manage finished products and manufacturing processes.',
        route: 'manufacturingView',
        icon: 'fas fa-industry',
        colorClass: 'bg-warning-light text-warning',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.manufactring_view,
        isBlade: false
    },
    {
        title: 'Sale Form',
        description: 'Create new sale invoices and manage transactions.',
        route: 'saleForm3',
        icon: 'fas fa-cash-register',
        colorClass: 'bg-success-light text-success',
        permission: user.value.type === 'Employee' && user.value.permission?.pages?.sale_invoice_create,
        isBlade: false
    },
    {
        title: 'Print Label',
        description: 'Generate and print barcodes for your products.',
        route: 'printLabelForm',
        icon: 'fas fa-barcode',
        colorClass: 'bg-dark-light text-dark',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.print_label_create,
        isBlade: false
    },
    {
        title: 'Countsheet',
        description: 'Update stock quantities via count sheets.',
        route: 'countsheet',
        icon: 'fas fa-clipboard-list',
        colorClass: 'bg-secondary-light text-secondary',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.count_sheet_update,
        isBlade: false
    },
    {
        title: 'Bulk Price',
        description: 'Update product prices in bulk efficiently.',
        route: 'viewBulk',
        icon: 'fas fa-dollar-sign',
        colorClass: 'bg-danger-light text-danger',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.bulk_price_update,
        isBlade: false
    },
    // {
    //     title: 'Stock',
    //     description: 'View and manage current stock levels.',
    //     route: 'viewStock',
    //     icon: 'fas fa-cubes',
    //     colorClass: 'bg-indigo-light text-indigo',
    //     permission: user.value.type === 'Admin' || user.value.permission?.pages?.stock_form_view,
    //     isBlade: false
    // }
]);
</script>

<template>
    <Head title="Inventory" />
    <AuthenticatedLayout>
        <div class="container-fluid py-4">
            <!-- Page Heading -->
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Inventory Management</h1>
                    <p class="text-muted mb-0">Manage your products, stock, and categories.</p>
                </div>
            </div>

            <div class="row g-4">
                <template v-for="(module, index) in modules" :key="index">
                    <div v-if="module.permission" class="col-xl-3 col-md-6 mt-3">
                        <!-- Use standard anchor for Blade routes, Link for Inertia routes -->
                        <component 
                            :is="module.isBlade ? 'a' : Link" 
                            :href="route(module.route)" 
                            class="text-decoration-none"
                        >
                            <div class="card h-100 border-0 shadow-sm hover-card">
                                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                                    <div class="icon-circle mb-3" :class="module.colorClass">
                                        <i :class="[module.icon, 'fa-2x']"></i>
                                    </div>
                                    <h5 class="card-title font-weight-bold text-gray-800 mb-2">{{ module.title }}</h5>
                                    <p class="card-text text-muted small">{{ module.description }}</p>
                                    <div class="mt-auto pt-3 font-weight-bold small" :class="module.colorClass.split(' ')[1]">
                                        Access Module <i class="fas fa-arrow-right ml-1"></i>
                                    </div>
                                </div>
                            </div>
                        </component>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
    background: #fff;
    border-radius: 1rem;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.hover-card:hover .icon-circle {
    transform: scale(1.1);
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.fw-bold {
    font-weight: 700 !important;
}

/* Color Classes */
.bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
.text-primary { color: #4e73df !important; }

.bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
.text-success { color: #1cc88a !important; }

.bg-info-light { background-color: rgba(54, 185, 204, 0.1); }
.text-info { color: #36b9cc !important; }

.bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
.text-warning { color: #f6c23e !important; }

.bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
.text-danger { color: #e74a3b !important; }

.bg-secondary-light { background-color: rgba(133, 135, 150, 0.1); }
.text-secondary { color: #858796 !important; }

.bg-dark-light { background-color: rgba(90, 92, 105, 0.1); }
.text-dark { color: #5a5c69 !important; }

.bg-indigo-light { background-color: rgba(102, 16, 242, 0.1); }
.text-indigo { color: #6610f2 !important; }
</style>
