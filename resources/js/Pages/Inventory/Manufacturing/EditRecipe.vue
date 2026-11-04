<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TabNavigation from '@/Components/TabNavigation.vue';
import ProductSearch from '@/Components/ProductSearch.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Tooltip from '@/Components/Tooltip.vue';
import TextArea from '@/Components/TextArea.vue';
import Button from '@/Components/Button.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    recipe: {
        type: Object,
        required: true
    }
});

// Convert products prop to array if needed
const productsArray = computed(() => {
    if (Array.isArray(props.products)) return props.products;
    if (typeof props.products === 'object' && props.products !== null) {
        return Object.values(props.products);
    }
    return [];
});

const activeTab = ref('recipe');

const tabs = [
    { id: 'recipe', label: 'Recipe', icon: 'fas fa-book-open' }
];

const form = useForm({
    recipe_name: props.recipe.name || '',
    ingredients: props.recipe.ingredients.map(ing => ({
        id: ing.id, // Keep track of existing ingredient ID
        product_id: ing.product_id,
        variation_id: ing.variation_id,
        name: ing.product.name + (ing.variation ? ` - ${ing.variation.name}` : ''), 
        quantity: parseFloat(ing.quantity),
        wastage_percent: parseFloat(ing.wastage_percent),
        unit_price: parseFloat(ing.unit_price),
        price: parseFloat(ing.total_price),
        final_quantity: parseFloat(ing.quantity) - (parseFloat(ing.quantity) * parseFloat(ing.wastage_percent) / 100)
    })),
    wastage_percentage: parseFloat(props.recipe.wastage_percent) || 0,
    total_output_quantity: parseFloat(props.recipe.total_output_quantity) || 0,
    production_cost: parseFloat(props.recipe.production_cost) || 0,
    recipe_instructions: props.recipe.instructions || '',
});

// Add ingredient to table
const addIngredient = (product) => {
    const existingIndex = form.ingredients.findIndex(
        i => i.product_id === product.id && !i.variation_id
    );

    if (existingIndex !== -1) {
        // Increase quantity if already exists
        form.ingredients[existingIndex].quantity += 1;
    } else {
        const purchasePrice = parseFloat(product.purchase) || 0;
        
        form.ingredients.push({
            product_id: product.id,
            variation_id: product._isSelectedVariation ? product.id : null,
            name: product._isSelectedVariation 
                ? (product._parent.name + ' - ' + product._parent.variation_name + ' - ' + product.var_name + '(' + product.var_sku + ')') 
                : (product.name + '(' + product.barcode + ')'),
            quantity: 1,
            wastage_percent: 0,
            unit_price: purchasePrice,
            price: purchasePrice,
            final_quantity: 1,
        });
    }
    
    calculateTotals();
};

// Remove ingredient
const removeIngredient = (index) => {
    form.ingredients.splice(index, 1);
    calculateTotals();
};

// Calculate final quantity based on wastage
const calculateFinalQuantity = (ingredient) => {
    const wastage = parseFloat(ingredient.wastage_percent) || 0;
    const quantity = parseFloat(ingredient.quantity) || 0;
    ingredient.final_quantity = quantity - (quantity * wastage / 100);
    ingredient.price = ingredient.final_quantity * ingredient.unit_price;
    calculateTotals();
};

// Watch quantity changes
watch(() => form.ingredients, (ingredients) => {
    ingredients.forEach(ingredient => {
        calculateFinalQuantity(ingredient);
    });
}, { deep: true });

// Calculate totals
const calculateTotals = () => {
    const totalIngredientCost = form.ingredients.reduce((sum, item) => {
        return sum + (parseFloat(item.price) || 0);
    }, 0);
    
    form.production_cost = totalIngredientCost;
};

// Computed properties for display
const totalIngredientCost = computed(() => {
    return form.ingredients.reduce((sum, item) => {
        return sum + (parseFloat(item.price) || 0);
    }, 0).toFixed(2);
});

const totalWastageAmount = computed(() => {
    return form.ingredients.reduce((sum, item) => {
        const wastage = parseFloat(item.wastage_percent) || 0;
        const quantity = parseFloat(item.quantity) || 0;
        const unitPrice = parseFloat(item.unit_price) || 0;
        const wastageQty = quantity * wastage / 100;
        return sum + (wastageQty * unitPrice);
    }, 0).toFixed(2);
});

const submit = () => {
    form.post(route('manufacturing.recipe.update', props.recipe.id), {
        onSuccess: () => {},
        onError: (errors) => console.log(errors)
    });
};

const isFormValid = computed(() => {
    return !!(
        form.recipe_name && 
        form.wastage_percentage && 
        form.total_output_quantity &&
        form.production_cost
    );
});
</script>

<template>
    <Head title="Create Recipe" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-book-open"></i> Add Recipe
            </h1>
            <Link :href="route('manufacturingView')" class="btn btn-secondary">
                <i class="fa fa-arrow-left mr-1 text-sm"></i> Back
            </Link>
        </div>

        <!-- Tab Navigation -->
        <TabNavigation v-model="activeTab" :tabs="tabs">
            <template #recipe>
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- Recipe Name -->
                        <div class="col-md-6 mb-4">
                            <InputLabel for="recipe_name" value="Recipe Name" required />
                            <TextInput
                                id="recipe_name"
                                v-model="form.recipe_name"
                                class="w-full"
                                placeholder="Enter recipe name"
                                required
                            />
                        </div>
                    </div>

                    <!-- Product Search -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <InputLabel value="Add Ingredients" required />
                            <ProductSearch 
                                :products="productsArray"
                                @select="addIngredient"
                                placeholder="Search ingredients by name or barcode..."
                            />
                        </div>
                    </div>

                    <!-- Ingredients Table -->
                    <div class="row mb-4" v-if="form.ingredients.length > 0">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="text-sm font-semibold" width="40%">Ingredients</th>
                                            <th class="text-sm font-semibold" width="10%">Quantity <span class="text-red-500">*</span></th>
                                            <th class="text-sm font-semibold" width="15%">
                                                Wastage %
                                                <Tooltip text="Percentage of ingredient lost during preparation" />
                                            </th>
                                            <th class="text-sm font-semibold" width="12%">Final Qty</th>
                                            <th class="text-sm font-semibold" width="12%">Unit Price</th>
                                            <th class="text-sm font-semibold" width="12%">Price</th>
                                            <th class="text-sm font-semibold text-center" width="8%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(ingredient, index) in form.ingredients" :key="index">
                                            <td class="text-sm">{{ ingredient.name }}</td>
                                            <td>
                                                <TextInput 
                                                    type="number"
                                                    v-model.number="ingredient.quantity"
                                                    @input="calculateFinalQuantity(ingredient)"
                                                    class="text-xs w-full"
                                                    min="0.01"
                                                    step="0.01"
                                                    required
                                                />
                                            </td>
                                            <td>
                                                <TextInput 
                                                    type="number"
                                                    v-model.number="ingredient.wastage_percent"
                                                    @input="calculateFinalQuantity(ingredient)"
                                                    class="text-xs w-full"
                                                    min="0"
                                                    max="100"
                                                    step="0.1"
                                                    required
                                                />
                                            </td>
                                            <td>
                                                <TextInput 
                                                    type="number"
                                                    v-model.number="ingredient.final_quantity"
                                                    class="text-xs w-full"
                                                    readonly
                                                    required
                                                />
                                            </td>
                                            <td>
                                                <TextInput 
                                                    type="number"
                                                    v-model.number="ingredient.unit_price"
                                                    class="text-xs w-full"
                                                    readonly
                                                    required
                                                />
                                            </td>
                                            <td>
                                                <TextInput 
                                                    type="number"
                                                    v-model.number="ingredient.price"
                                                    class="text-xs w-full"
                                                    readonly
                                                    required
                                                />
                                            </td>
                                            <td class="text-center">
                                                <Button
                                                    @click="removeIngredient(index)"
                                                    :className="'text-red-500 mt-2 hover:text-red-700 transition'"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Cost Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="font-medium">Total Ingredient Cost:</span>
                                        <span class="font-bold">{{ totalIngredientCost }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="font-medium">Total Wastage Amount:</span>
                                        <span class="font-bold text-danger">{{ totalWastageAmount }}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="font-medium text-lg">Production Cost:</span>
                                        <span class="font-bold text-lg text-success">{{ totalIngredientCost }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Production Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <InputLabel for="total_output_quantity" value="Total Output Quantity" required />
                                        <TextInput
                                            id="total_output_quantity"
                                            v-model.number="form.total_output_quantity"
                                            type="number"
                                            class="mt-1 block w-full"
                                            min="0"
                                            step="0.01"
                                            placeholder="Expected output quantity"
                                            required
                                        />
                                    </div>
                                    <div class="mb-3">
                                        <InputLabel for="wastage_percentage" required>
                                            Overall Wastage % <Tooltip text="Percentage of total output quantity wasted during production"/>
                                        </InputLabel>
                                        <div class="d-flex align-items-center">
                                            <TextInput
                                                id="wastage_percentage"
                                                v-model.number="form.wastage_percentage"
                                                type="number"
                                                class="mt-1 block w-full"
                                                min="0"
                                                max="100"
                                                step="0.1"
                                                placeholder="0"
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recipe Instructions -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <InputLabel for="recipe_instructions" value="Recipe Instructions" />
                            <!-- <RichTextEditor
                                v-model="form.recipe_instructions"
                                placeholder="Enter detailed recipe instructions..."
                            /> -->
                            <TextArea
                                id="recipe_instructions" 
                                v-model="form.recipe_instructions" 
                                placeholder="Type text or paste a link"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row">
                        <div class="col-md-12">
                            <PrimaryButton 
                                type="submit"
                                class="btn btn-primary px-5 py-2" 
                                :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Save Recipe
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </template>
        </TabNavigation>
    </AuthenticatedLayout>
</template>

<style scoped>
.table-responsive {
    overflow-x: auto;
}

.font-medium {
    font-weight: 500;
}

.font-bold {
    font-weight: 700;
}

.text-lg {
    font-size: 1.125rem;
}
</style>
