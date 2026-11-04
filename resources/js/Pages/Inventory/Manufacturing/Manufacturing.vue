<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';
import BaseDataTable from '@/Components/baseDataTable.vue';
import TabNavigation from '@/Components/TabNavigation.vue';
import Modal2 from '@/Components/Modal2.vue';
import Table from '@/Components/Table.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    recipes: {
        type: Array,
        default: () => []
    },
    productions: {
        type: Array,
        default: () => []
    },
});

const page = usePage();
const permissions = page.props.auth.user.permission?.pages;
const user = page.props.auth.user.type;

// Active tab state - Initialize from URL param or default to 'recipe'
const urlParams = new URLSearchParams(window.location.search);
const activeTab = ref(urlParams.get('tab') || 'recipe');

// Watch activeTab to update URL query param without reloading
watch(activeTab, (newTab) => {
    const url = new URL(window.location);
    url.searchParams.set('tab', newTab);
    window.history.replaceState({}, '', url);
});

// Tab configuration
const tabs = [
    { id: 'recipe', label: 'Recipe', icon: 'fas fa-book-open' },
    { id: 'production', label: 'Production', icon: 'fas fa-industry' },
    { id: 'report', label: 'Manufacturing Report', icon: 'fas fa-chart-bar' }
];

// Recipe columns and actions
const recipeColumns = [
    { data: "name", title: "Recipe Name" },
    { data: "total_output_quantity", title: "Output Qty" },
    { data: "production_cost", title: "Total Cost" },
    { 
        data: null, 
        title: "Unit Cost",
        render: (data, type, row) => {
            const cost = parseFloat(row.production_cost) || 0;
            const qty = parseFloat(row.total_output_quantity) || 1;
            return (cost / qty).toFixed(2);
        }
    },
    { data: "status", title: "Status", name: 'product-status', slots: { default: 'column-product-status' } },
    { data: null, title: "Action", name: 'product-actions', orderable: false, className: 'text-center overflow-visible', slots: { default: 'column-product-actions' } },
];

const recipeActions = [
    { label: 'View', href: null, icon: 'fas fa-eye mr-2', action: 'view' },
    { label: 'Edit', href: 'manufacturing.recipe.edit', icon: 'fas fa-edit mr-2' },
    { label: 'Change Status', href: 'manufacturing.recipe.status', icon: 'fas fa-exchange-alt mr-2', params: (row) => [row.status, row.id] },
    { label: 'Delete', href: 'manufacturing.recipe.delete', icon: 'fas fa-trash mr-2' },
];

// Production columns and actions
const productionColumns = [
    { 
        data: "day", 
        title: "Date",
        render: (data) => data ? new Date(data).toLocaleDateString() : ''
    },
    { data: "production_number", title: "Production #" },
    { data: "product.name", title: "Product" },
    { data: "qty", title: "Quantity" },
    { data: "production_cost", title: "Total Cost" },
    { data: "status", title: "Status"},
    { data: null, title: "Action", name: 'product-actions', orderable: false, className: 'text-center overflow-visible', slots: { default: 'column-product-actions' } },
];

const productionActions = [
    { label: 'View', href: null, icon: 'fas fa-eye mr-2', action: 'view' },
    { label: 'Edit', href: 'manufacturing.production.edit', icon: 'fas fa-edit mr-2' },
    { label: 'Complete', href: 'manufacturing.production.status', icon: 'fas fa-check mr-2', params: (row) => ['completed', row.id] },
    { label: 'Cancel', href: 'manufacturing.production.status', icon: 'fas fa-times mr-2', params: (row) => ['cancelled', row.id] },
];

// Computed properties for add buttons
const canAddRecipe = computed(() => user === 'Admin' || permissions?.manufacturing_create);
const canAddProduction = computed(() => user === 'Admin' || permissions?.manufacturing_create);

// Recipe View Modal Logic
const selectedRecipe = ref(null);
const selectedProduction = ref(null);

const viewRecipe = (id) => {
    const recipe = props.recipes.find(p => p.id == id);
    selectedRecipe.value = recipe;
    $('#modal_recipe').modal('show');
};

const viewProduction = (id) => {
    // Look up in props.productions since we now pass it from controller
    const prod = props.productions.find(p => p.id == id);
    if (prod) {
        selectedProduction.value = prod;
        $('#modal_production').modal('show');
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString();
};


const productionIngredientsColumns = [
     { label: 'Ingredient', key: 'ingredient.name' },
     { label: 'Quantity Used', key: 'quantity' },
     { label: 'Unit Cost', key: 'unit_cost' }, 
     { label: 'Total Cost', key: 'total_cost' }, 
];

const productionIngredientsRows = computed(() => {
    if (!selectedProduction.value || !selectedProduction.value.recipes) return [];
    
    return selectedProduction.value.recipes.map(ing => {
        
        const qty = parseFloat(ing.qty) || 0;
        const unitCost = parseFloat(ing.product?.purchase) || 0; // Using current purchase price
        const total = qty * unitCost;

        return {
            'ingredient.name': ing.product?.name || 'Unknown',
            'quantity': qty.toFixed(2),
            'unit_cost': unitCost.toFixed(2),
            'total_cost': total.toFixed(2)
        };
    });
});

// Report Logic
const startDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10)); // First day of current month
const endDate = ref(new Date().toISOString().slice(0, 10)); // Today

const filteredProductions = computed(() => {
    if (!props.productions) return [];
    return props.productions.filter(p => {
        const pDate = new Date(p.day || p.manufacturing_date).toISOString().slice(0, 10);
        return pDate >= startDate.value && pDate <= endDate.value;
    });
});

const reportSummary = computed(() => {
    const summary = {
        total_count: filteredProductions.value.length,
        total_qty: 0,
        total_cost: 0,
        completed_count: 0
    };

    filteredProductions.value.forEach(p => {
        summary.total_qty += parseFloat(p.quantity || 0);
        summary.total_cost += parseFloat(p.total_cost || p.production_cost || 0);
        if (p.status?.toLowerCase() === 'completed') {
            summary.completed_count++;
        }
    });

    return summary;
});

const reportColumns = [
    { label: 'Date', key: 'date' },
    { label: 'Reference', key: 'reference_no' },
    { label: 'Product', key: 'product_name' },
    { label: 'Quantity', key: 'quantity' },
    { label: 'Total Cost', key: 'total_cost' },
    { label: 'Status', key: 'status' }
];

const reportRows = computed(() => {
    return filteredProductions.value.map(p => ({
        date: formatDate(p.day || p.manufacturing_date),
        reference_no: p.production_number || p.reference_no,
        product_name: p.product?.name || 'Unknown',
        quantity: p.quantity,
        total_cost: parseFloat(p.total_cost || p.production_cost || 0).toFixed(2),
        status: p.status
    }));
});

</script>

<template>
    <Head title="Manufacturing" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-industry"></i> Manufacturing
            </h1>
            
            <!-- Dynamic Add Button based on active tab -->
            <div>
                <Link 
                    v-if="activeTab === 'recipe' && canAddRecipe"
                    :href="route('manufacturing.recipe.create')" 
                    class="btn btn-primary"
                >
                    <i class="fa fa-plus mr-1 text-sm"></i> New Recipe
                </Link>
                <Link 
                    v-if="activeTab === 'production' && canAddProduction"
                    :href="route('manufacturing.production.create')" 
                    class="btn btn-primary"
                >
                    <i class="fa fa-plus mr-1 text-sm"></i> New Production
                </Link>
            </div>
        </div>

        <!-- Tab Navigation and Content -->
        <TabNavigation v-model="activeTab" :tabs="tabs">
            <!-- Recipe Tab Content -->
            <template #recipe>
                <BaseDataTable 
                    :ajaxUrl="'/manufacturing/getRecipes'"
                    :columns="recipeColumns"
                    :permission="permissions?.manufacturing_update"
                    :userType="user"
                    :moreActions="recipeActions"
                    :theadClass="'text-xs'"
                    class="text-sm"
                    :viewModal="true"
                    @view="viewRecipe"
                />
            </template>

            <!-- Production Tab Content -->
            <template #production>
                <BaseDataTable 
                    :ajaxUrl="'/manufacturing/getProductions'"
                    :columns="productionColumns"
                    :permission="permissions?.manufacturing_update"
                    :userType="user"
                    :moreActions="productionActions"
                    :theadClass="'text-xs'"
                    class="text-sm"
                    :viewModal="true"
                    @view="viewProduction"
                >
                    <template #column-product-actions="props">
                         <!-- Custom action slot content if needed, or rely on BaseDataTable default -->
                    </template>
                </BaseDataTable>
            </template>

            <!-- Settings Tab Content -->
            <template #settings>
                <div class="p-4">
                    <h5 class="font-semibold text-gray-700 mb-4">
                        <i class="fas fa-cog mr-2 text-gray-500"></i>
                        Manufacturing Settings
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h6 class="font-medium text-gray-700 mb-3">General Settings</h6>
                                <p class="text-sm text-gray-500">Configure general manufacturing options and preferences.</p>
                                <!-- Add settings form here -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h6 class="font-medium text-gray-700 mb-3">Production Settings</h6>
                                <p class="text-sm text-gray-500">Set up production workflow and automation rules.</p>
                                <!-- Add settings form here -->
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Manufacturing Report Tab Content -->
            <template #report>
                <div class="p-4">
                    <h5 class="font-semibold text-gray-700 mb-4">
                        <i class="fas fa-chart-bar mr-2 text-gray-500"></i>
                        Manufacturing Report
                    </h5>
                    
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <InputLabel for="start_date" value="Start Date" required />
                            <TextInput id="start_date" type="date" v-model="startDate" class="w-full" />
                        </div>
                        <div class="col-md-3">
                            <InputLabel for="end_date" value="End Date" required />
                            <TextInput id="end_date" type="date" v-model="endDate" class="w-full" />
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                         <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Productions</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ reportSummary.total_count }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Cost</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ reportSummary.total_cost.toFixed(2) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Quantity</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ reportSummary.total_qty.toFixed(2) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Completed</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ reportSummary.completed_count }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Table -->
                    <div class="row">
                        <div class="col-12">
                             <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Detailed Report</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <Table 
                                            class="table table-bordered" 
                                            :columns="reportColumns" 
                                            :rows="reportRows"
                                            :theadClass="'bg-gray-50 text-xs uppercase'"
                                        />
                                        <div v-if="reportRows.length === 0" class="text-center py-4 text-gray-500">
                                            No records found for the selected period.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
</TabNavigation>

        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />

        <!-- Recipe View Modal -->
        <Modal2 id="modal_recipe" :Class="'sm:max-w-4xl'" :title="selectedRecipe?.name || 'Recipe Details'">
            <div v-if="selectedRecipe" class="container-fluid">
                <!-- Header Info -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                            <div>
                                <h6 class="mb-1 text-primary font-bold">Recipe Information</h6>
                                <div class="text-sm text-gray-600">
                                    <span class="mr-3"><i class="fas fa-user mr-1"></i> Created by: {{ selectedRecipe.user?.name || 'Unknown' }}</span>
                                    <span><i class="fas fa-calendar mr-1"></i> Date: {{ formatDate(selectedRecipe.created_at) }}</span>
                                </div>
                            </div>
                            <span :class="{'text-white badge bg-success': selectedRecipe.status, 'text-whitebadge bg-danger': !selectedRecipe.status}">
                                {{ selectedRecipe.status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row mb-4">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Output Qty</div>
                            <div class="h5 font-bold text-gray-800 mb-0">{{ selectedRecipe.total_output_quantity }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Wastage %</div>
                            <div class="h5 font-bold text-gray-800 mb-0">{{ selectedRecipe.wastage_percent }}%</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Total Cost</div>
                            <div class="h5 font-bold text-green-600 mb-0">{{ parseFloat(selectedRecipe.production_cost).toFixed(2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Unit Cost</div>
                            <div class="h5 font-bold text-blue-600 mb-0">
                                {{ (parseFloat(selectedRecipe.production_cost) / (parseFloat(selectedRecipe.total_output_quantity) || 1)).toFixed(2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="row mb-4" v-if="selectedRecipe.instructions">
                    <div class="col-12">
                        <h6 class="font-bold text-gray-700 mb-2 border-bottom pb-2">Instructions</h6>
                        <div class="bg-gray-50 p-3 rounded text-sm text-gray-700" v-html="selectedRecipe.instructions"></div>
                    </div>
                </div>

                <!-- Ingredients Table -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="font-bold text-gray-700 mb-3 border-bottom pb-2">Ingredients</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th>Product</th>
                                        <th>Variation</th>
                                        <th class="text-right">Quantity</th>
                                        <th class="text-right">Wastage %</th>
                                        <th class="text-right">Unit Cost</th>
                                        <th class="text-right">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ing in selectedRecipe.ingredients" :key="ing.id">
                                        <td>{{ ing.product?.name }}</td>
                                        <td>{{ ing.variation?.name || '-' }}</td>
                                        <td class="text-right">{{ ing.quantity }}</td>
                                        <td class="text-right">{{ ing.wastage_percent }}%</td>
                                        <td class="text-right">{{ parseFloat(ing.unit_price).toFixed(2) }}</td>
                                        <td class="text-right font-bold">{{ parseFloat(ing.total_price).toFixed(2) }}</td>
                                    </tr>
                                    <tr v-if="!selectedRecipe.ingredients || selectedRecipe.ingredients.length === 0">
                                        <td colspan="6" class="text-center py-3 text-gray-500">No ingredients found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </Modal2>

        <!-- Production View Modal -->
        <Modal2 id="modal_production" :Class="'sm:max-w-4xl'" :title="'Production Details - ' + (selectedProduction?.production_number || '')">
            <div v-if="selectedProduction" class="container-fluid">
                <!-- Header Info -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                            <div>
                                <h6 class="mb-1 text-primary font-bold">Production Information</h6>
                                <div class="text-sm text-gray-600">
                                    <span class="mr-3">
                                        <i class="fas fa-box mr-1"></i> Product: 
                                        <strong>{{ selectedProduction.product?.name || 'Unknown' }}</strong>
                                    </span>
                                    <span class="mr-3">
                                        <i class="fas fa-hashtag mr-1"></i> Ref: {{ selectedProduction.production_number }}
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar mr-1"></i> Date: {{ formatDate(selectedProduction.day) }}
                                    </span>
                                </div>
                            </div>
                            <span :class="{'badge bg-success': (selectedProduction.status == 'completed' || selectedProduction.status == 'Completed'), 'badge bg-warning': (selectedProduction.status == 'pending' || selectedProduction.status == 'Pending'), 'badge bg-danger': (selectedProduction.status == 'cancelled' || selectedProduction.status == 'Cancelled')}" class="text-white">
                                {{ selectedProduction.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row mb-4">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Produced Qty</div>
                            <div class="h5 font-bold text-gray-800 mb-0">{{ selectedProduction.quantity }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Wasted Qty</div>
                            <div class="h5 font-bold text-gray-800 mb-0">{{ selectedProduction.wasted_quantity || 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Production Cost</div>
                            <div class="h5 font-bold text-green-600 mb-0">{{ parseFloat(selectedProduction.production_cost || 0).toFixed(2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="text-xs text-uppercase text-gray-500 font-bold mb-1">Total Cost</div>
                            <div class="h5 font-bold text-blue-600 mb-0">
                                {{ parseFloat(selectedProduction.total_cost || selectedProduction.production_cost || 0).toFixed(2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ingredients Table -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="font-bold text-gray-700 mb-3 border-bottom pb-2">Ingredients Used</h6>
                        <div class="table-responsive">
                            <Table 
                                class="table-xs"
                                :theadClass="'text-xs'"
                                :columns="productionIngredientsColumns"
                                :rows="productionIngredientsRows"
                            />
                            
                                <!-- <tbody>
                                    <tr v-for="ing in selectedProduction.recipes" :key="ing.id">
                                        <td>{{ ing.product?.name || 'Unknown Item' }}</td>
                                        <td class="text-right">{{ ing.qty }}</td>
                                    </tr>
                                    <tr v-if="!selectedProduction.recipes || selectedProduction.recipes.length === 0">
                                        <td colspan="2" class="text-center py-3 text-gray-500">No ingredients recorded.</td>
                                    </tr>
                                </tbody>
                            </table> -->
                        </div>
                    </div>
                </div>
            </div>
        </Modal2>
    </AuthenticatedLayout>
</template>

