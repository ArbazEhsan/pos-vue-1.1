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
    production: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    reference_no: props.production.reference_no || '',
    manufacturing_date: props.production.manufacturing_date || new Date().toISOString().slice(0, 16),
    manufacturing_product: props.production.product_id,
    recipe_id: props.production.recipe_id,
    quantity: parseFloat(props.production.quantity) || 1,
    status: props.production.status || 'completed',
    ingredients: [],
    wasted_quantity: parseFloat(props.production.wasted_quantity) || 0,
    production_cost: parseFloat(props.production.production_cost) || 0,
    total_cost: parseFloat(props.production.total_cost) || 0,
});

// Initial Load of Ingredients from Recipe or Existing Production
// We should preferably load from the recipe logic again to allow re-calculation if quantity changes,
// BUT we also want to preserve what was stored if it's custom.
// For simplicity in this logic, we will trigger loadIngredients based on current recipe/qty, 
// basically "Applying Recipe" to current state.
// If the user wants to see exactly what was saved, we might map `props.production.recipes`.
// Let's rely on the recipe logic because `storeProduction` re-generates from recipe structure essentially,
// but let's try to map the EXISTING ingredients first if recipe matches.

const loadInitialIngredients = () => {
    if (!props.production.recipes) return;
    
    // Map existing ingredients from production record
    form.ingredients = props.production.recipes.map(ing => {
        // We need to find the base recipe ingredient to get the base quantity if we want to support dynamic updates.
        // This makes editing a bit complex if we want to allow "Quantity" change to auto-update ingredients.
        // To allow auto-update, we need the link to the RecipeHeader Ingredient.
        // We can find it in props.recipes.
        
        const recipe = props.recipes.find(r => r.id == form.recipe_id);
        let baseQty = 0;
        let recipeOutput = 1;

        if (recipe && recipe.ingredients) {
            const recipeIng = recipe.ingredients.find(ri => ri.product_id == ing.product_id);
            if (recipeIng) {
                baseQty = parseFloat(recipeIng.quantity);
                recipeOutput = parseFloat(recipe.total_output_quantity);
            }
        }
        
        // If not found in recipe (maybe recipe changed), we default to current values which means it won't scale well?
        // Or we just fetch current recipe data completely?
        // If we just fetch recipe data, we lose any custom manual changes (if allowed).
        // Since `CreateProduction` calculates from Recipe, `Edit` should probably do the same to ensure consistency.
        // EXCEPT we want to show what was there.
        // Let's default to "Re-calculate from Recipe based on current Qty" approach as it is safer for data integrity vs recipe.
        // But to pass validation, we need the array.
        
        // Let's actually just call `loadIngredients` from the recipe if available, 
        // effectively "Resetting" the ingredients list to the Recipe standard for the edit.
        // This is usually preferred unless we support custom ad-hoc ingredients.
        
        return {
             product_id: ing.product_id,
            variation_id: ing.variation_id,
            name: ing.product ? ing.product.name : 'Unknown Product',
            base_quantity: baseQty, // Set this so updates work
            recipe_output: recipeOutput,
            quantity: parseFloat(ing.qty),
            unit_cost: parseFloat(ing.product?.purchase || 0),
            total_cost: parseFloat(ing.qty) * parseFloat(ing.product?.purchase || 0),
        };
    });
    
    // If ingredients are empty (e.g. legacy), try loading from recipe
    if (form.ingredients.length === 0) {
        const recipe = props.recipes.find(r => r.id == form.recipe_id);
        if (recipe) loadIngredients(recipe);
    }
    
    // Force calculate totals
    calculateTotals();
};

// ... Same logic as CreateProduction ...
const loadIngredients = (recipe) => {
    if (!recipe.ingredients) return;

    form.ingredients = recipe.ingredients.map(ing => {
        const baseQty = parseFloat(ing.quantity) || 0;
        const recipeOutput = parseFloat(recipe.total_output_quantity) || 1;
        const outputQty = parseFloat(form.quantity) || 0;
        
        const requiredQty = (baseQty / recipeOutput) * outputQty;
        const unitCost = ing.product ? (parseFloat(ing.product.purchase) || 0) : 0; 
        
        return {
            product_id: ing.product_id,
            variation_id: ing.variation_id,
            name: ing.product ? ing.product.name : 'Unknown Product',
            base_quantity: baseQty,
            recipe_output: recipeOutput,
            quantity: requiredQty,
            unit_cost: unitCost,
            total_cost: requiredQty * unitCost,
        };
    });
    
    calculateTotals();
};

const updateIngredientQuantities = (recipe) => {
    const recipeOutput = parseFloat(recipe.total_output_quantity) || 1;
    const outputQty = parseFloat(form.quantity) || 0;

    form.ingredients.forEach(ing => {
        // If base_quantity is set (from recipe), use it. 
        // If we loaded existing production without base_qty, we might be stuck.
        // So `loadInitialIngredients` MUST try to populate base_quantity from the RecipeHeader.
        
        if (ing.base_quantity) {
             const requiredQty = (ing.base_quantity / recipeOutput) * outputQty;
             ing.quantity = requiredQty;
             ing.total_cost = requiredQty * ing.unit_cost;
        }
    });
    
    calculateTotals();
};

// Watchers
watch(() => form.recipe_id, (newVal, oldVal) => {
    if (newVal && newVal != oldVal) {
        // Only load if changed by user interaction mostly. 
        // On mount we don't want to overwrite if we have specific logic, but here we do want to enforce consistency.
        const recipe = props.recipes.find(r => r.id == newVal);
        if (recipe) {
            loadIngredients(recipe);
        }
    }
});

watch(() => form.quantity, () => {
    if (form.recipe_id) {
        const recipe = props.recipes.find(r => r.id == form.recipe_id);
        if (recipe) updateIngredientQuantities(recipe);
    }
});

const calculateTotals = () => {
    const totalIngCost = form.ingredients.reduce((sum, item) => sum + item.total_cost, 0);
    form.production_cost = totalIngCost.toFixed(2);
    // Only update total_cost if it hasn't been manually touched? 
    // Or just update it to keep it in sync for now.
    form.total_cost = totalIngCost.toFixed(2); 
};


// Toast Logic
const toastMessage = ref(null);
const showToast = ref(false);

const displayToast = (msg) => {
    toastMessage.value = msg;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
        toastMessage.value = null;
    }, 3000);
};

const submit = () => {
    form.post(route('manufacturing.production.update', props.production.id), {
        onSuccess: (page) => {
             const flashMsg = page.props.flash?.message || 'Production updated successfully';
             displayToast(flashMsg);
             // Optional: redirect logic is handled by backend
        },
        onError: (errors) => {
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

onMounted(() => {
    console.log("Recipes loaded:", props.recipes);
    form.manufacturing_product = props.production.product_id;
    loadInitialIngredients();
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
    <Head title="Edit Production" />
    <AuthenticatedLayout>
        <Toast v-if="showToast" :msg="toastMessage" />
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit"></i> Edit Production
            </h1>
            <Link :href="route('manufacturingView', {tab: 'production'})" class="btn btn-secondary">
                <i class="fa fa-arrow-left mr-1 text-sm"></i> Back
            </Link>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Production Details</h6>
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
                    </div>

                    <div class="row mb-4">
                        <!-- Product (Recipe) -->
                        <div class="col-md-6">
                            <InputLabel for="recipe_id" value="Product (Recipe)" required />
                            <Select 
                                id="recipe_id" 
                                v-model="form.recipe_id" 
                                class="w-full"
                                :options="props.recipes"
                            />
                             <small class="text-danger">Changing recipe will recalculate ingredients.</small>
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
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-right">
                             <PrimaryButton 
                                type="submit"
                                class="btn btn-primary px-5 py-2" 
                                :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}"
                            >
                                <i class="fas fa-save mr-2"></i> Update Production
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
