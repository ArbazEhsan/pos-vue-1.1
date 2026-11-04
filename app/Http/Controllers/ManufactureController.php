<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Stock;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Company;
use App\Models\Manufactureds;
use App\Models\Recipe;
use App\Models\ProductCombo;
use App\Models\Tax;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Models\RecipeHeader;
use App\Models\RecipeIngredient;

class ManufactureController extends Controller
{
    public function index(Request $request)
    {
        $recipes = RecipeHeader::with('ingredients.product', 'ingredients.variation', 'user:id,name')->get();
        $products = Product::with('variations')->where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        
        // Fetch productions for props usage
        $productions = Manufactureds::with('product', 'product.category', 'recipes.product')
            ->latest()
            ->get()
            ->map(function ($item) {
                 // Add calculated fields if needed to match what was in getProductions or just send raw model
                 // The modal expects direct model properties mostly, but let's ensure structure match
                 $item->production_number = $item->reference_no ?? ('PRD-' . str_pad($item->id, 5, '0', STR_PAD_LEFT));
                 return $item;
            });

        return Inertia::render('Inventory/Manufacturing/Manufacturing', [
            'products' => $products,
            'categories' => $categories,
            'recipes' => $recipes,
            'productions' => $productions,
        ]);
    }

    public function getRecipes(Request $request)
    {
        $query = RecipeHeader::with('ingredients.product', 'user')
            ->latest();

        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        $recipes = $query->get();
            
        return response()->json($recipes);
    }
    
    public function storeRecipe(Request $request)
    {
        $request->validate([
            'recipe_name' => 'required',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'total_output_quantity' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create Recipe Header
            $recipe = RecipeHeader::create([
                'name' => $request->recipe_name,
                'instructions' => $request->recipe_instructions,
                'total_output_quantity' => $request->total_output_quantity ?? 0,
                'wastage_percent' => $request->wastage_percentage ?? 0,
                'production_cost' => $request->production_cost ?? 0,
                'user_id' => Auth::id(),
                'status' => 1
            ]);

            // Create Recipe Ingredients
            foreach ($request->ingredients as $ingredient) {
                RecipeIngredient::create([
                    'recipe_header_id' => $recipe->id,
                    'product_id' => $ingredient['product_id'],
                    'variation_id' => $ingredient['variation_id'] ?? null,
                    'quantity' => $ingredient['quantity'],
                    'wastage_percent' => $ingredient['wastage_percent'] ?? 0,
                    'unit_price' => $ingredient['unit_price'] ?? 0,
                    'total_price' => $ingredient['price'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('manufacturingView')
                ->with('message', 'Recipe Created Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create recipe: ' . $e->getMessage());
        }
    }

    public function editRecipe($id)
    {
        $recipe = RecipeHeader::with('ingredients.product', 'ingredients.variation')->findOrFail($id);
        $products = Product::with('variations')->where('status', 1)->get();
        $categories = Category::where('status', 1)->get();

        return Inertia::render('Inventory/Manufacturing/EditRecipe', [
            'products' => $products,
            'categories' => $categories,
            'recipe' => $recipe
        ]);
    }

    public function updateRecipe(Request $request, $id)
    {
        $request->validate([
            'recipe_name' => 'required|string|max:255',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'total_output_quantity' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $recipe = RecipeHeader::findOrFail($id);
            $recipe->update([
                'name' => $request->recipe_name,
                'instructions' => $request->recipe_instructions,
                'total_output_quantity' => $request->total_output_quantity ?? 0,
                'wastage_percent' => $request->wastage_percentage ?? 0,
                'production_cost' => $request->production_cost ?? 0,
            ]);

            // Get existing ingredient IDs
            $existingIngredientIds = $recipe->ingredients->pluck('id')->toArray();
            $updatedIngredientIds = [];

            foreach ($request->ingredients as $ingredientData) {
                if (isset($ingredientData['id'])) {
                    // Update existing ingredient
                    $ingredient = RecipeIngredient::findOrFail($ingredientData['id']);
                    $ingredient->update([
                        'quantity' => $ingredientData['quantity'],
                        'wastage_percent' => $ingredientData['wastage_percent'] ?? 0,
                        'unit_price' => $ingredientData['unit_price'] ?? 0,
                        'total_price' => $ingredientData['price'] ?? 0,
                    ]);
                    $updatedIngredientIds[] = $ingredientData['id'];
                } else {
                    // Create new ingredient
                    $newIngredient = RecipeIngredient::create([
                        'recipe_header_id' => $recipe->id,
                        'product_id' => $ingredientData['product_id'],
                        'variation_id' => $ingredientData['variation_id'] ?? null,
                        'quantity' => $ingredientData['quantity'],
                        'wastage_percent' => $ingredientData['wastage_percent'] ?? 0,
                        'unit_price' => $ingredientData['unit_price'] ?? 0,
                        'total_price' => $ingredientData['price'] ?? 0,
                    ]);
                }
            }

            // Delete removed ingredients
            $ingredientsToDelete = array_diff($existingIngredientIds, $updatedIngredientIds);
            RecipeIngredient::whereIn('id', $ingredientsToDelete)->delete();

            DB::commit();

            return redirect()->route('manufacturingView')
                ->with('message', 'Recipe Updated Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update recipe: ' . $e->getMessage());
        }
    }

    public function deleteRecipe($id)
    {
        try {
            $recipe = RecipeHeader::findOrFail($id);
            $recipe->delete();
            return redirect()->back()->with('message', 'Recipe Deleted Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete recipe');
        }
    }

    public function statusRecipe($status,$id)
    {
        try {
            $recipe = RecipeHeader::findOrFail($id);
            $recipe->status = !$recipe->status;
            $recipe->save();
             return redirect()->route('manufacturingView', ['tab' => 'recipe'])->with('message', 'Status Updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }

    public function getProductions(Request $request)
    {
        // Get manufacturing records with related data
        $productions = Manufactureds::with('product', 'product.category')
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'production_number' => $item->reference_no ?? ('PRD-' . str_pad($item->id, 5, '0', STR_PAD_LEFT)),
                    'product' => [
                        'name' => $item->product->name ?? 'N/A'
                    ],
                    'qty' => $item->quantity ?? 0, // The manufactured quantity
                    'production_cost' => $item->total_cost ?? ($item->production_cost ?? 0), // Use total cost if available, else production cost
                    'day' => $item->manufacturing_date ?? $item->day, // Prefer detailed date
                    'status' => ucfirst($item->status ?? 'completed'), 
                ];
            });
            
        return response()->json($productions);
    }

    public function createRecipe()
    {
        $products = Product::with('variations')->where('status', 1)->get();
        $categories = Category::where('status', 1)->get();

        return Inertia::render('Inventory/Manufacturing/CreateRecipe', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function createProduction()
    {
        $recipes = RecipeHeader::with('ingredients.product', 'ingredients.variation')->where('status', 1)->get();
        // Also passing products if needed for manual ingredient addition, though not explicitly requested
        $products = Product::with('variations')->where('status', 1)->get(); 

        return Inertia::render('Inventory/Manufacturing/CreateProduction', [
            'recipes' => $recipes,
            'products' => $products,
        ]);
    }

    public function storeProduction(Request $request)
    {
        $request->validate([
            'manufacturing_date' => 'required|date',
            'recipe_id' => 'required|exists:recipe_headers,id',
            'quantity' => 'required|numeric|min:0.01',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Find the Recipe Header
            $recipeHeader = RecipeHeader::findOrFail($request->recipe_id);

            // 2. Resolve the Target Product
            // $finishedProduct = Product::where('name', $recipeHeader->name)->first();
            $finishedProduct = Product::where('id', $request->manufacturing_product)->first();

            if (!$finishedProduct) {
                // If product not found, we could check if there is a mapping or just fail.
                // Failing with a clear message is best for data integrity.
                throw new \Exception("Product with name '{$recipeHeader->name}' not found. Please create a product with this exact name first.");
            }

            // 3. Create Manualufactured Record
            $manufacture = Manufactureds::create([
                'day' => date('Y-m-d', strtotime($request->manufacturing_date)),
                'manufacturing_date' => $request->manufacturing_date,
                'product_id' => $finishedProduct->id,
                'recipe_id' => $recipeHeader->id,
                'reference_no' => $request->reference_no,
                'quantity' => $request->quantity,
                'wasted_quantity' => $request->wasted_quantity ?? 0,
                'production_cost' => $request->production_cost ?? 0,
                'total_cost' => $request->total_cost ?? 0,
                'subtract_qty_stage' => 'Manufacturing',
                'status' => $request->status ?? 'completed',
            ]);

            // 4. Handle Ingredients and Stock Deduction
            foreach ($request->ingredients as $ingredientData) {
                // Determine unit price for cost tracking if provided?
                // The frontend sends unit_cost and total_cost in the object but we only validate product_id and quantity.
                // We can accept them if we want to store them, but Recipe model only has qty.
                
                Recipe::create([
                    'product_id' => $ingredientData['product_id'],
                    'manufactureds_id' => $manufacture->id,
                    'qty' => $ingredientData['quantity'],
                ]);

                // Deduct Inventory
                $ingredientProduct = Product::find($ingredientData['product_id']);
                if ($ingredientProduct) {
                    // Check stock
                    if ($ingredientProduct->qty < $ingredientData['quantity']) {
                        throw new \Exception("Insufficient stock for ingredient: {$ingredientProduct->name}. Available: {$ingredientProduct->qty}, Required: {$ingredientData['quantity']}");
                    }
                    $ingredientProduct->decrement('qty', $ingredientData['quantity']);
                }
            }

            // 5. Update Finished Product Stock
            if (($request->status ?? 'completed') === 'completed') {
                $finishedProduct->increment('qty', $request->quantity);
            }

            DB::commit();

            return redirect()->route('manufacturingView', ['tab' => 'production'])
                ->with('message', 'Production Recorded Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            // Use withErrors so Inertia onError picks it up
            return redirect()->back()
                ->withErrors(['error' => 'Failed to store production: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function statusProduction($status,$id)
    {
        try {
            $production = Manufactureds::findOrFail($id);
            $production->status = $status;
            $production->save();
            return redirect()->route('manufacturingView', ['tab' => 'production'])->with('message', 'Status Updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }
    

    public function editProduction($id)
    {
        // 'recipes' relation on Manufactureds points to Recipe model, which lacks 'variation' relation.
        $production = Manufactureds::with('recipes.product')->findOrFail($id);
        $recipes = RecipeHeader::with('ingredients.product', 'ingredients.variation')->where('status', 1)->get();
        $products = Product::where('status', 1)->get(); // If needed

        return Inertia::render('Inventory/Manufacturing/EditProduction', [
            'production' => $production,
            'recipes' => $recipes,
            'products' => $products,
        ]);
    }

    public function updateProduction(Request $request, $id)
    {
        $request->validate([
            'manufacturing_date' => 'required|date',
            'recipe_id' => 'required|exists:recipe_headers,id',
            'quantity' => 'required|numeric|min:0.01',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $manufacture = Manufactureds::findOrFail($id);
            
            // 1. Revert Stock Changes (Reverse previous operation)
            
            // Revert Finished Product Stock (if it was completed)
            // Note: If previous status was completed, we decrement. If pending, we didn't increment.
            // Current store logic increments if 'completed'.
            if (($manufacture->status ?? 'completed') === 'completed') {
                $oldProduct = Product::find($manufacture->product_id);
                if ($oldProduct) {
                    $oldProduct->decrement('qty', $manufacture->quantity);
                }
            }
            
            // Revert Ingredient Stock (We decremented them, now increment)
            $oldIngredients = Recipe::where('manufactureds_id', $manufacture->id)->get();
            foreach ($oldIngredients as $oldIng) {
                $prod = Product::find($oldIng->product_id);
                if ($prod) {
                    $prod->increment('qty', $oldIng->qty);
                }
            }
            
            // Delete old ingredient records
            Recipe::where('manufactureds_id', $manufacture->id)->delete();

            // ---------------------------------------------------------
            // 2. Apply New Changes (Similar to Store)
            
            $recipeHeader = RecipeHeader::findOrFail($request->recipe_id);
            $finishedProduct = Product::where('id', $request->manufacturing_product)->first();

            if (!$finishedProduct) {
                 throw new \Exception("Product with name '{$recipeHeader->name}' not found.");
            }

            // Update Manufacture Record
            $manufacture->update([
                'day' => date('Y-m-d', strtotime($request->manufacturing_date)),
                'manufacturing_date' => $request->manufacturing_date,
                'product_id' => $finishedProduct->id, // In case recipe changed
                'recipe_id' => $recipeHeader->id,
                'reference_no' => $request->reference_no,
                'quantity' => $request->quantity,
                'wasted_quantity' => $request->wasted_quantity ?? 0,
                'production_cost' => $request->production_cost ?? 0,
                'total_cost' => $request->total_cost ?? 0,
                'status' => $request->status ?? 'completed',
            ]);

            // Create New Ingredients and Deduct Stock
            foreach ($request->ingredients as $ingredientData) {
                Recipe::create([
                    'product_id' => $ingredientData['product_id'],
                    'manufactureds_id' => $manufacture->id,
                    'qty' => $ingredientData['quantity'],
                ]);

                $ingredientProduct = Product::find($ingredientData['product_id']);
                if ($ingredientProduct) {
                    if ($ingredientProduct->qty < $ingredientData['quantity']) {
                        throw new \Exception("Insufficient stock for ingredient: {$ingredientProduct->name}. Available: {$ingredientProduct->qty}, Required: {$ingredientData['quantity']}");
                    }
                    $ingredientProduct->decrement('qty', $ingredientData['quantity']);
                }
            }

            // Update Finished Product Stock (if new status is completed)
            if (($request->status ?? 'completed') === 'completed') {
                $finishedProduct->increment('qty', $request->quantity);
            }

            DB::commit();

            return redirect()->route('manufacturingView', ['tab' => 'production'])
                ->with('message', 'Production Updated Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update production: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
