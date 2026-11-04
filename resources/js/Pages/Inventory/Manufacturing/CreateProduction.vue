<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Select from '@/Components/Select.vue';
import Select2 from '@/Components/Select2.vue';
import Table from '@/Components/Table.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    recipes: {
        type: Array,
        default: () => []
    },
    products: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    reference_no: '',
    manufacturing_date: new Date().toISOString().slice(0, 16), // Current datetime
    manufacturing_product: '',
    recipe_id: '',
    quantity: 1,
    status: 'completed', // default status
    ingredients: [],
    wasted_quantity: 0,
    production_cost: 0,
    total_cost: 0,
});

// Watch for Recipe Selection
watch(() => form.recipe_id, (newVal) => {
    if (!newVal) {
        form.ingredients = [];
        return;
    }
    
    // Use == to allow string/number comparison as Select returns string value
    const recipe = props.recipes.find(r => r.id == newVal);
    if (recipe) {
        loadIngredients(recipe);
    }
});

// Watch for Quantity Change
watch(() => form.quantity, () => {
    if (form.recipe_id) {
        const recipe = props.recipes.find(r => r.id == form.recipe_id);
        if (recipe) updateIngredientQuantities(recipe);
    }
});

const loadIngredients = (recipe) => {
    // recipe.ingredients should be available from the eager load in controller
    if (!recipe.ingredients) return;

    form.ingredients = recipe.ingredients.map(ing => {
        // Calculate based on initial quantity
        const baseQty = parseFloat(ing.quantity) || 0;
        const recipeOutput = parseFloat(recipe.total_output_quantity) || 1;
        const outputQty = parseFloat(form.quantity) || 0;
        
        // If recipe makes 10 items with 1kg flour
        // ingredient.quantity = 1kg
        // recipe.total_output_quantity = 10
        // We want to make 20 items.
        // Factor = 20 / 10 = 2.
        // Required = 1 * 2 = 2kg.
        
        const requiredQty = (baseQty / recipeOutput) * outputQty;
        // Cost estimation - assuming unit_price is on ingredient or needs to be fetched from product
        // The RecipeIngredient model usually has cost snapshot, but we should probably use CURRENT product cost.
        // The prop `ingredients` has `product`.
        const unitCost = ing.product ? (parseFloat(ing.product.purchase) || 0) : 0; // Assuming purchase price is key
        
        return {
            product_id: ing.product_id,
            variation_id: ing.variation_id,
            name: ing.product ? ing.product.name : 'Unknown Product',
            base_quantity: baseQty,
            recipe_output: recipeOutput,
            quantity: requiredQty, // This is the calculated required quantity
            unit_cost: unitCost,
            total_cost: requiredQty * unitCost,
            wasted_qty: 0 // Row level wastage if needed, user asked for global wasted quantity though
        };
    });
    
    calculateTotals();
};

const updateIngredientQuantities = (recipe) => {
    const recipeOutput = parseFloat(recipe.total_output_quantity) || 1;
    const outputQty = parseFloat(form.quantity) || 0;

    form.ingredients.forEach(ing => {
        const requiredQty = (ing.base_quantity / recipeOutput) * outputQty;
        ing.quantity = requiredQty;
        ing.total_cost = requiredQty * ing.unit_cost;
    });
    
    calculateTotals();
};

const calculateTotals = () => {
    const totalIngCost = form.ingredients.reduce((sum, item) => sum + item.total_cost, 0);
    form.production_cost = totalIngCost.toFixed(2);
    
    // Total cost might include additional overhead or wastage cost
    // If wasted quantity is just a number of items wasted from the final output:
    // It doesn't necessarily add COST (the cost is already incurred in ingredients), 
    // but it increases the cost-per-unit of the remaining items.
    // However, if "Total Cost" field is for editable final cost:
    form.total_cost = totalIngCost.toFixed(2); 
};

// Calculate on wastage change or manual cost override?
// User asked for fields for Production Cost and Total Cost. 
// I will treat them as calculated but editable OR just calculated.
// Usually Production Cost = Material Cost.

const toastMessage = ref(null);
const showToast = ref(false);

const displayToast = (msg) => {
    toastMessage.value = msg;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
        toastMessage.value = null;
    }, 12000);
};

const submit = () => {
    form.post(route('manufacturing.production.store'), {
        onSuccess: (page) => {
             // Inertia might share flash messages via page props, but we will use the generic success message here 
             // OR check page.props.flash.message if avail.
             const flashMsg = page.props.flash?.message || 'Production created successfully';
             displayToast(flashMsg);
             form.reset();
        },
        onError: (errors) => {
            // Flatten errors object values to a single string or pick the first one
            const errorMsg = Object.values(errors).flat().join('\n') || 'An error occurred';
            displayToast(errorMsg);
        }
    });
};


const ingredientColumns = [
    { label: 'Ingredient Name', key: 'ingredient.name' },
    { label: 'Unit Cost', key: 'unit_cost' },
    { label: 'Required Qty', key: 'required_qty' },
    { label: 'Total Cost', key: 'total_cost' },
];

const ingredientRows = computed(() => {
    return form.ingredients.map(ing => ({
        'ingredient.name': ing.name,
        'unit_cost': Number(ing.unit_cost).toFixed(2),
        'required_qty': Number(ing.quantity).toFixed(2),
        'total_cost': Number(ing.total_cost).toFixed(2),
    }));
}); 

const isFormValid = computed(() => {
    return !!(
        form.manufacturing_date && 
        form.manufacturing_product && 
        form.recipe_id &&
        form.quantity &&
        form.total_cost
    );
});
</script>

<template>
    <Head title="Create Production" />
    <AuthenticatedLayout>
        <Toast v-if="showToast" :msg="toastMessage" />
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-industry"></i> Create Production
            </h1>
            <Link :href="route('manufacturingView')" class="btn btn-secondary">
                <i class="fa fa-arrow-left mr-1 text-sm"></i> Back
            </Link>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Production Details</h6>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row mb-4">
                        <!-- Reference No -->
                        <div class="col-md-4">
                            <InputLabel for="reference_no" value="Reference No" />
                            <TextInput
                                id="reference_no"
                                v-model="form.reference_no"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Enter Reference No"
                            />
                        </div>

                        <!-- Manufacturing Date -->
                        <div class="col-md-4">
                            <InputLabel for="manufacturing_date" value="Manufacturing Date & Time" required />
                            <TextInput
                                id="manufacturing_date"
                                v-model="form.manufacturing_date"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <!-- Manufacturing Product -->
                        <div class="col-md-4">
                            <InputLabel for="manufacturing_product" value="Bind Product" required />
                            <Select2
                                id="manufacturing_product"
                                v-model="form.manufacturing_product"
                                :options="products"
                                class="mt-1 block w-full"
                            />
                        </div>

                         <!-- Status -->
                         <!-- <div class="col-md-4">
                            <InputLabel for="status" value="Status" />
                             <select class="form-control mt-1" v-model="form.status">
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                             </select>
                        </div> -->
                    </div>

                    <div class="row mb-4">
                        <!-- Product (Recipe) -->
                        <div class="col-md-6">
                            <InputLabel for="recipe_id" value="Product (Recipe)" required />
                            <Select 
                                id="recipe_id" 
                                v-model="form.recipe_id" 
                                class="w-full"
                                :options="recipes"
                            />
                            <!-- Debug info -->
                            <div v-if="props.recipes.length === 0" class="text-xs text-red-500">No recipes loaded ({{ props.recipes.length }})</div>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-6">
                            <InputLabel for="quantity" value="Quantity to Produce" required />
                            <TextInput
                                id="quantity"
                                v-model.number="form.quantity"
                                type="number"
                                class="mt-1 block w-full"
                                min="0.01"
                                step="0.01"
                                required
                            />
                        </div>
                    </div>

                    <!-- Ingredients Table -->
                    <div class="row mb-4" v-if="form.ingredients.length > 0">
                        <div class="col-12">
                            <h6 class="text-gray-600 mb-4 mt-2">Required Ingredients <span class="text-red-500">*</span></h6>
                            <div class="table-responsive">
                                <Table
                                    class="text-sm"
                                    :theadClass="'text-xs'"
                                    :columns="ingredientColumns"
                                    :rows="ingredientRows"
                                    :footer="['', '', 'Total Ingredient Cost:', form.production_cost]"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Summary / Footer Fields -->
                    <div class="row mb-4 bg-gray-50 p-3 rounded" v-if="form.recipe_id">
                         <!-- Wasted Quantity -->
                        <div class="col-md-4">
                            <InputLabel for="wasted_quantity" value="Wasted Quantity" />
                            <TextInput
                                id="wasted_quantity"
                                v-model.number="form.wasted_quantity"
                                type="number"
                                class="mt-1 block w-full"
                                min="0"
                                step="any"
                            />
                        </div>

                        <!-- Production Cost -->
                        <div class="col-md-4">
                            <InputLabel for="production_cost" value="Production Cost" />
                             <TextInput
                                id="production_cost"
                                v-model.number="form.production_cost"
                                type="number"
                                class="mt-1 block w-full bg-gray-100"
                                readonly
                            />
                        </div>

                         <!-- Total Cost -->
                         <div class="col-md-4">
                            <InputLabel for="total_cost" value="Total Cost" required />
                             <TextInput
                                id="total_cost"
                                v-model.number="form.total_cost"
                                type="number"
                                class="mt-1 block w-full"
                                step="0.01"
                                required
                            />
                            <small class="text-muted">Can be adjusted if different from calculated production cost.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-right">
                             <PrimaryButton 
                                type="submit"
                                class="btn btn-primary px-5 py-2" 
                                :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}"
                            >
                                <i class="fas fa-save mr-2"></i> Save Production
                            </PrimaryButton>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.table-responsive {
    overflow-x: auto;
}
</style>
