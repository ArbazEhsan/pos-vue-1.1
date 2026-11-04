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
use App\Models\BusinessSetting;
use App\Models\Tax;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Optimize sales aggregation using a join instead of correlated subquery
        $salesStream = DB::table('sales')
            ->select('barcode', DB::raw('SUM(qty) as sales_sum_qty'))
            ->groupBy('barcode');

        $products = Product::with(['category', 'variations', 'brand', 'combos.comboProduct', 'combos.comboVariation.product'])
            ->leftJoinSub($salesStream, 'sales_agg', function ($join) {
                $join->on('products.barcode', '=', 'sales_agg.barcode');
            })
            ->select('products.*', 'sales_agg.sales_sum_qty')
            ->latest('products.created_at')
            ->get();

        return Inertia::render('Inventory/Product/Product', [
            'products' => $products,
        ]);
    }

    public function getProductDetails(Request $request)
    {
        $query = Product::with(['category', 'brand', 'variations']);

        // Filter by consumable
        if ($request->has('consumable') && $request->consumable == 'true') {
            $query->where('consumable', 1);
        }

        // Filter by sellable
        if ($request->has('sellable') && $request->sellable == 'true') {
            $query->where('sellable', 1);
        }

        // Filter by product type
        if ($request->has('type') && in_array($request->type, ['single', 'variation', 'combo'])) {
            $query->where('type', $request->type);
        }

        $product = $query->latest()->get();
        return response()->json($product);
    }

    public function createProduct()
    {
        $result = Category::where('parent_id', null)
                ->with('children')
                ->get();
        
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        $taxes = Tax::where('status', 1)->orderBy('name')->get();

        $noneOption = (object) [
            'id' => '0',
            'name' => 'None',
        ];

        $taxes = $taxes->prepend($noneOption);

        $product = Product::with(['category', 'brand', 'variations'])->latest()->get();

        $businessSettings = BusinessSetting::select('currency')->first();

        return Inertia::render('Inventory/Product/Create', [
            'categories' => $result,
            'brands' => $brands,
            'taxes' => $taxes,
            'products' => $product,
            'businessSettings' => $businessSettings,
        ]);
    }

    public function exportProducts()
    {
        $result = Product::with('category:id,name')->get();
        $result2 = Company::all();
        return view('product.exportProduct')->with(['product'=>$result,'company'=>$result2]);
    }
   
    public function store(Request $request)
    {
        $brandId = null;
        if($request->pro_brands){
            $brand = Brand::firstOrCreate(['name' => $request->pro_brands]);
            $brandId = $brand->id;
        }
        
        $post = new Product;
        // Basic Info
        $post->name         = $request->pro_name;
        $post->barcode      = $request->pro_short_code;
        $post->category_id  = $request->pro_category;
        $post->brand_id     = $brandId;

        // Determine product type robustly
        $comboProducts = $request->input('combo_products', []);
        $hasValidCombos = is_array($comboProducts) && count(array_filter($comboProducts, fn($c) => !empty($c['product_id']))) > 0;

        $variations = $request->input('variation', []);
        $hasValidVariations = is_array($variations) && count(array_filter($variations, fn($v) => !empty($v['var_name']))) > 0;

        $post->type = $hasValidCombos ? 'combo' : ($hasValidVariations ? 'variation' : 'single');
        $post->user_id      = Auth::id();
        
        // Status & Pricing
        $post->sellable     = $request->pro_sellable;
        $post->consumable   = $request->pro_consumable;
        $post->purchase     = $request->pro_pur_price;
        $post->whole_sale   = $request->pro_w_price ?? 0;
        $post->retail       = $request->pro_r_price;
        $post->qty          = $request->pro_qty;
        $post->min_qty      = $request->pro_min_qty;
        
        // New Fields
        $post->description      = $request->pro_description;
        $post->warranty_period  = $request->pro_warrenty_period;
        $post->warranty_type    = $request->pro_warrenty_type;
        $post->expiry_date      = $request->pro_expiry_date;
        
        $post->weight       = $request->weight;
        $post->weight_unit  = $request->weight_unit;
        $post->height       = $request->height;
        $post->width        = $request->width;
        $post->length       = $request->length;
        
        $post->variation_name = $request->variation_name;
        $post->include_image  = $request->include_image ?? false;

        // Image Handling
        $post->image_uri1 = $request->hasFile('pro_image1') ? $request->file('pro_image1')->store('products', 'public') : null;
        $post->image_uri2 = $request->hasFile('pro_image2') ? $request->file('pro_image2')->store('products', 'public') : null;
        $post->image_uri3 = $request->hasFile('pro_image3') ? $request->file('pro_image3')->store('products', 'public') : null;
        $post->image_uri4 = $request->hasFile('pro_image4') ? $request->file('pro_image4')->store('products', 'public') : null;
        $post->image_uri5 = $request->hasFile('pro_image5') ? $request->file('pro_image5')->store('products', 'public') : null;

        $post->save();

        // Variation Handling
        if ($request->has('variation') && is_array($request->variation)) 
        {
            $variationsData = [];
            $variationSumQty = 0;
            foreach ($request->variation as $variation) 
            {
                // Only prepare if valid name exists
                if (!empty($variation['var_name'])) {
                    // Handle variation image
                    $varImage = '';
                    if (isset($variation['var_image']) && $variation['var_image'] instanceof \Illuminate\Http\UploadedFile) {
                        $varImage = $variation['var_image']->store('variations', 'public');
                    }
                    
                    $variationsData[] = [
                        'product_id'        => $post->id,
                        'var_name'          => $variation['var_name'],
                        'var_sku'               => $variation['var_sku'] ? $post->barcode . '-' . $variation['var_sku'] : $post->barcode . '-' . uniqid(),
                        'var_purchase_price'    => $variation['var_purchase_price'] ?? $post->purchase,
                        'var_wholesale_price'   => $variation['var_wholesale_price'] ?? $post->whole_sale,
                        'var_retail_price'         => $variation['var_price'] ?? $post->retail,
                        'var_qty'           => $variation['var_qty'] ?? 0,
                        'var_image'         => $varImage,
                    ];
                    $variationSumQty += $variation['var_qty'];
                }
            }

            // Batch insert variations
            if (!empty($variationsData)) {
                foreach (array_chunk($variationsData, 500) as $chunk) {
                    $post->variations()->createMany($chunk);
                }
                Product::where('id', $post->id)->update([
                    'qty' => $variationSumQty,
                ]);
            }
        }

        // Combo Handling
        if ($request->has('combo_products') && is_array($request->combo_products)) {
            $combosData = [];
            foreach ($request->combo_products as $combo) {
                if (isset($combo['product_id'])) {
                    $combosData[] = [
                        'product_id' => $post->id,
                        'combo_product_id' => $combo['product_id'],
                        'combo_variation_id' => $combo['variation_id'] ?? null,
                        'quantity' => $combo['qty'] ?? 1,
                        'price' => $combo['purchase_price'] ?? 0,
                    ];
                }
            }
            if (!empty($combosData)) {
                $post->combos()->createMany($combosData);
            }
        }

        return redirect('/viewProduct')->with('message','Product Added Successfully');
    }

    public function edit($id)
    {
        $cat='';
    	$result = Product::with(['variations', 'brand', 'combos.comboProduct', 'combos.comboVariation.product'])->findOrFail($id);
        $data = Category::where('parent_id', null)
                ->with('children')
                ->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        
        $taxes = Tax::where('status', 1)->orderBy('name')->get();
        $noneOption = (object) [
            'id' => '0',
            'name' => 'None',
        ];
        $taxes = $taxes->prepend($noneOption);

        $allProducts = Product::with(['category', 'brand', 'variations'])->latest()->get();

        $businessSettings = BusinessSetting::select('currency')->first();

        return Inertia::render('Inventory/Product/Edit', [
            'product' => $result, // Single product to edit
            'categories' => $data,
            'brands' => $brands,
            'taxes' => $taxes,
            'search_products' => $allProducts, // All products for combo search
            'businessSettings' => $businessSettings,
        ]);
    }

    public function update(Request $request)
    {
        // dd($request->all());
        $product = Product::find($request->pro_id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        // Handle brand
        $brandId = null;
        if($request->pro_brands){
            $brand = Brand::firstOrCreate(['name' => $request->pro_brands]);
            $brandId = $brand->id;
        }

        // Prepare update data
        $updateData = [
            'name'         => $request->pro_name,
            'barcode'      => $request->pro_short_code,
            'category_id'  => $request->pro_category,
            'brand_id'     => $brandId,

            // Determine product type
            'type'         => (function() use ($request) {
                $comboProducts = $request->input('combo_products', []);
                $hasValidCombos = is_array($comboProducts) && count(array_filter($comboProducts, fn($c) => !empty($c['product_id']))) > 0;
                $variations = $request->input('variation', []);
                $hasValidVariations = is_array($variations) && count(array_filter($variations, fn($v) => !empty($v['var_name']))) > 0;
                return $hasValidCombos ? 'combo' : ($hasValidVariations ? 'variation' : 'single');
            })(),
            'user_id'      => Auth::id(),
            
            // Status & Pricing
            'sellable'     => $request->pro_sellable,
            'consumable'   => $request->pro_consumable,
            'purchase'     => $request->pro_pur_price,
            'whole_sale'   => $request->pro_w_price ?? 0,
            'retail'       => $request->pro_r_price,
            'qty'          => $request->pro_qty,
            'min_qty'      => $request->pro_min_qty,
            
            // New Fields
            'description'      => $request->pro_description,
            'warranty_period'  => $request->pro_warrenty_period,
            'warranty_type'    => $request->pro_warrenty_type,
            'expiry_date'      => $request->pro_expiry_date,
            
            // Shipping
            'weight'       => $request->weight,
            'weight_unit'  => $request->weight_unit,
            'height'       => $request->height,
            'width'        => $request->width,
            'length'       => $request->length,
            
            'variation_name' => $request->variation_name,
            'include_image'  => $request->include_image ?? false,
        ];

        // Update images if new ones are uploaded
        if ($request->hasFile('pro_image1')) {
            $updateData['image_uri1'] = $request->file('pro_image1')->store('products', 'public');
        }
        if ($request->hasFile('pro_image2')) {
            $updateData['image_uri2'] = $request->file('pro_image2')->store('products', 'public');
        }
        if ($request->hasFile('pro_image3')) {
            $updateData['image_uri3'] = $request->file('pro_image3')->store('products', 'public');
        }
        if ($request->hasFile('pro_image4')) {
            $updateData['image_uri4'] = $request->file('pro_image4')->store('products', 'public');
        }
        if ($request->hasFile('pro_image5')) {
            $updateData['image_uri5'] = $request->file('pro_image5')->store('products', 'public');
        }

        $product->update($updateData);

        // Handle Variations
        if ($request->has('variation') && is_array($request->variation)) 
        {
            // Get existing variations to preserve images if needed
            $existingVariations = $product->variations()->get()->keyBy('var_name');
            
            // Delete existing variations
            $product->variations()->delete();
            
            $variationsData = [];
            $variationSumQty = 0;
            foreach ($request->variation as $variation) 
            {
                // Only prepare if valid name exists
                if (!empty($variation['var_name'])) {
                    // Handle variation image
                    $varImage = '';
                    
                    // Check if new image is uploaded
                    if (isset($variation['var_image']) && $variation['var_image'] instanceof \Illuminate\Http\UploadedFile) {
                        $varImage = $variation['var_image']->store('variations', 'public');
                    } 
                    // Check if it's an existing image URL (string starting with /storage/)
                    elseif (isset($variation['var_image']) && is_string($variation['var_image']) && str_starts_with($variation['var_image'], '/storage/')) {
                        // Extract the path after /storage/
                        $varImage = substr($variation['var_image'], 9);
                    }
                    // Try to preserve existing image if variation name matches
                    elseif (isset($existingVariations[$variation['var_name']])) {
                        $varImage = $existingVariations[$variation['var_name']]->var_image ?? '';
                    }
                    
                    $variationsData[] = [
                        'product_id'        => $product->id,
                        'var_name'          => $variation['var_name'],
                        'var_sku'               => $variation['var_sku'] ? $product->barcode . '-' . $variation['var_sku'] : $product->barcode . '-' . uniqid(),
                        'var_purchase_price'    => $variation['var_purchase_price'] ?? $product->purchase,
                        'var_wholesale_price'   => $variation['var_wholesale_price'] ?? $product->whole_sale,
                        'var_retail_price'         => $variation['var_price'] ?? $product->retail,
                        'var_qty'           => $variation['var_qty'] ?? 0,
                        'var_image'         => $varImage,
                    ];
                    $variationSumQty += $variation['var_qty'];
                }
            }

            // Batch insert variations
            if (!empty($variationsData)) {
                foreach (array_chunk($variationsData, 500) as $chunk) {
                    $product->variations()->createMany($chunk);
                }
                
                // Update product qty with variation sum
                Product::where('id', $product->id)->update([
                    'qty' => $variationSumQty,
                ]);
            }
        }

        // Handle Combos
        // Always delete existing combos to sync with new state (empty or populated)
        $product->combos()->delete();
        
        if ($request->filled('combo_products') && is_array($request->combo_products)) {
            $combosData = [];
            foreach ($request->combo_products as $combo) {
                if (isset($combo['product_id'])) {
                    $combosData[] = [
                        'product_id' => $product->id,
                        'combo_product_id' => $combo['product_id'],
                        'combo_variation_id' => $combo['variation_id'] ?? null,
                        'quantity' => $combo['qty'] ?? 1,
                        'price' => $combo['purchase_price'] ?? 0,
                    ];
                }
            }
            if (!empty($combosData)) {
                $product->combos()->createMany($combosData);
            }
        }

        return redirect()->route('viewProduct')->with('message', 'Product Updated Successfully');
    }
  
    public function delete($id)
    {
    	Product::destroy($id);
    	return $this->index();
    }

    public function status($status,$id)
    {
        $post = Product::find($id);
        $post->status = !$post->status;
        $post->user_id = Auth::id();
        $post->save();

        return redirect()->back()->with('message','Status Updated Successfully!');
    }


/* ----------------------------------------
   Bulk price Start
---------------------------------------- */

    public function viewBulk(Request $request)
    {
        $result = Product::where('status', 1)->with('category')->get();

        return Inertia::render('Inventory/Product/BulkPrice', [
            'data' => $result,
        ]);
    }

    public function searchBulk(Request $request)
    {
        $barcode = $request->plist;
        if (empty($barcode) && empty($name)) {
            $result = Product::where('status',1)->get();
        } else {
            $result = Product::where([
                ['id',$barcode],
                ['status',1],
            ])->get();
            
        }

        return Inertia::render('Inventory/Product/BulkPrice', [
            'data' => $result,
        ]);
    }

    public function searchBulkByFilter($filter)
    {
        $id = $filter;
        if ($id == 'product') {
           $result = Product::where([
                ['cat_id','!=',8],
                ['status','=',1]
            ])->get(); 
        }
        elseif($id == 'stock') {
           $result = Product::where(['cat_id'=>8,'status'=>1])->get();
        }
        $data = "";
        $count = 0;
        foreach ($result as $value) {
            $count++;
            $data .= '<tr>
                <td>'.$count.'</td>'.'
                <td>'.$value->barcode.'</td>'.'
                <td>'.$value->name.'</td>'.'
                <td><input type="number" name="purchase[]" class="form-control col-md-10" placeholder="Enter Purchase Price" value="'.$value->purchase.'" autocomplete="false" autofocus="true" ></td>'.'
                <td><input type="number" name="whole_sale[]" class="form-control col-md-10" placeholder="Enter Whole Sale" value="'.$value->whole_sale.'" autocomplete="false" autofocus="true" ></td>'.'
                <td><input type="number" name="retail[]" class="form-control col-md-10" placeholder="Enter Retail Price" value="'.$value->retail.'" autocomplete="false" autofocus="true" ><input type="hidden" name="id[]" class="form-control col-md-6" value="'.$value->id.'"></td>'.'
            </tr>';

        }
        echo $data;
    }

    public function updateBulk(Request $request)
    {
        $id = $request->product_id;
        $purchase = $request->product_pp;
        $whole_sale = $request->product_wp;
        $retail = $request->product_rp;
        $uid = Auth::id();
        $count = count($id);

        for ($i=0; $i<$count ; $i++) {
            
            if (isset($purchase[$i]) || isset($whole_sale[$i]) || isset($retail[$i])) {

                $product = Product::find($id[$i]);
                $product->fill([
                    'purchase' => $purchase[$i],
                    'whole_sale' => $whole_sale[$i],
                    'retail' => $retail[$i],
                    'user_id' => $uid
                ]);

                if ($product->isDirty()) {

                    $product->save();
                }
            }

        }

        return redirect()->back()->with('message','Prices Updated Successfully!');
    }

/* ----------------------------------------
   Bulk price End
---------------------------------------- */

/* ----------------------------------------
   Re-order Start
---------------------------------------- */

    public function viewReorder()
    {
        $result = Product::whereRaw('qty < min_qty')->get();
        return view('product.viewReorder')->with('product', $result);
    }
    
/* ----------------------------------------
   Re-order End
---------------------------------------- */

/* ----------------------------------------
   Countsheet Start
---------------------------------------- */
    
    public function countsheet(Request $request)
    {
        $result = Product::where('status', 1)->with('category')->get();

        return Inertia::render('Inventory/Product/CountSheet', [
            'data' => $result,
        ]);
    }

    public function searchProduct($value)
    {
        $result = Product::where('name','LIKE','%'.$value.'%')->get();
        $data = "";
        foreach ($result as $value) {
            $data .= '<option value='.$value->id.'>'.$value->name.'</option>';
        }
        echo $data;
    }

    public function searchProductFor(Request $request)
    {
        // $id = $request->plist;
        // if (empty($id)) {
        //    $result = Product::where('status',1)->paginate(10); 
        // }
        // else {
        //    $result = Product::where(['id'=>$id,'status'=>1])->paginate(10);
        // }
        // $data = "";
        // $count = 0;
        // foreach ($result as $value) {
        //     $count++;
        //     $data .= '<tr>
        //         <td>'.$count.'</td>'.'
        //         <td>'.$value->barcode.'</td>'.'
        //         <td>'.$value->name.'</td>'.'
        //         <td>'.$value->purchase.'</td>'.'
        //         <td><input type="number" name="qty[]" class="form-control col-md-10" placeholder="Enter Quantity" value="'.$value->qty.'" autocomplete="false" autofocus="true" step="0.01" style="font-weight:bold;font-size:15px;"><input type="hidden" name="id[]" class="form-control col-md-6" value="'.$value->id.'"></td>'.'
        //     </tr>';
        // }
        // echo $data;
    }

    public function searchProductByFilter($filter)
    {
        $id = $filter;
        if ($id == 'product') {
           $result = Product::where([
                ['cat_id','!=',8],
                ['status','=',1]
            ])->get(); 
        }
        elseif($id == 'stock') {
           $result = Product::where(['cat_id'=>8,'status'=>1])->get();
        }
        $data = "";
        $count = 0;
        foreach ($result as $value) {
            $count++;
            $data .= '<tr>
                <td>'.$count.'</td>'.'
                <td>'.$value->barcode.'</td>'.'
                <td>'.$value->name.'</td>'.'
                <td>'.$value->purchase.'</td>'.'
                <td><input type="number" name="qty[]" class="form-control col-md-10" placeholder="Enter Quantity" value="'.$value->qty.'" autocomplete="false" autofocus="true" step="0.01" style="font-weight:bold;font-size:15px;"><input type="hidden" name="id[]" class="form-control col-md-6" value="'.$value->id.'"></td>'.'
            </tr>';
        }
        echo $data;
    }

    public function updateProductQty(Request $request)
    {
        $ids = $request->product_id;
        $qtys = $request->product_qty;

        if (empty($ids) || empty($qtys)) {
            return redirect()->back()->with('message', 'No products to update.');
        }
        
        $uid = Auth::id();
        $day = date('y-m-d');

        // Fetch all products at once
        $products = Product::whereIn('id', $ids)->get()->keyBy('id');
        $stockData = [];

        foreach ($ids as $index => $id) { 
            if (isset($qtys[$index]) && isset($products[$id])) {
                $product = $products[$id];
                $qtyToAdd = $qtys[$index];

                // Add new qty to previous qty
                $newQuantity = $product->qty + $qtyToAdd;

                $product->qty = $newQuantity;
                $product->user_id = $uid;

                if ($product->isDirty()) {
                    $product->save();

                    $stockData[] = [
                        'day' => $day,
                        'barcode' => $id, // Storing product_id in barcode column as per previous logic
                        'qty' => $newQuantity,
                        'user_id' => $uid
                    ];
                }
            }
        }

        if (!empty($stockData)) {
            // Insert in batches to prevent "MySQL server gone away" error
            foreach (array_chunk($stockData, 500) as $chunk) {
                Stock::insert($chunk);
            }
        }

        return redirect()->back()->with('message','Quantity Updated Successfully!');
    }

/* ----------------------------------------
   Countsheet End
---------------------------------------- */

/* ----------------------------------------
   Stock Form start
---------------------------------------- */

    public function viewStockForm(Request $request)
    {
        $result = Product::with('category:id,name')->get();

        return Inertia::render('Inventory/Product/StockForm', [
            'products' => $result,
        ]);
    }

/* ----------------------------------------
   Stock Form End
---------------------------------------- */

/* ----------------------------------------
   Print Label Start
---------------------------------------- */

    public function viewPrintLabel($id = null)
    {
        $products = Product::with('variations')->where('status', 1)->orderBy('name')->get();
        // $company = Company::first('name');
        $company = BusinessSetting::first('business_name');
        
        return Inertia::render('Inventory/Product/PrintLabel', [
            'products' => $products,
            'product' => $id ? Product::with('variations')->findOrFail($id) : null,
            'businessName' => $company->business_name
        ]);
    }

    public function printLabel($productId, $quantity)
    {
        $product = Product::findOrFail($productId);
        
        return view('product.printLabel', compact('product', 'quantity'));
    }

    public function storePrintSession(\Illuminate\Http\Request $request)
    {
        session(['print_labels_data' => $request->all()]);
        return response()->json(['success' => true]);
    }

    public function printPreview()
    {
        $data = session('print_labels_data');
        
        if (!$data) {
            return redirect()->route('printLabelForm')->with('error', 'No labels to print.');
        }
        
        return view('product.printLabel', $data);
    }        
    
    /**
     * Duplicate an existing product
     * Loads product data into Create form for duplication
     */
    public function duplicateProduct($id = null)
    {
        $result = Category::where('parent_id', null)
                ->with('children')
                ->get();
        
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        
        $productToDuplicate = null;
        
        if ($id) {
            // Load the product with all its relationships
            $originalProduct = Product::with(['variations', 'brand', 'category'])->findOrFail($id);
            
            // Prepare product data for duplication
            $productToDuplicate = $originalProduct->toArray();
            
            // Clear the ID so it creates a new product
            unset($productToDuplicate['id']);
            unset($productToDuplicate['created_at']);
            unset($productToDuplicate['updated_at']);
            
            // Modify barcode to avoid duplicate
            $productToDuplicate['barcode'] = $originalProduct->barcode . '-COPY';
            
            // Clear variations IDs but keep the data
            if (isset($productToDuplicate['variations']) && is_array($productToDuplicate['variations'])) {
                foreach ($productToDuplicate['variations'] as &$variation) {
                    unset($variation['id']);
                    unset($variation['product_id']);
                    unset($variation['created_at']);
                    unset($variation['updated_at']);
                    // Modify variation SKU to avoid duplicate
                    if (isset($variation['var_sku'])) {
                        $variation['var_sku'] = $variation['var_sku'] . '-COPY';
                    }
                }
            }
        }

        return Inertia::render('Inventory/Product/Create', [
            'categories' => $result,
            'brands' => $brands,
            'productToDuplicate' => $productToDuplicate,
        ]);
    }
    
/* ----------------------------------------
   Print Label End
---------------------------------------- */

/* ----------------------------------------
   Manufacturing End
---------------------------------------- */

    /**
     * Parse HTML description, extract Base64 images, save to storage, and replace with URL.
     */
    private function processDescriptionImages($content)
    {
        if (empty($content)) {
            return $content;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        // Load HTML with UTF-8 encoding hack
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        $hasChanges = false;

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            // Check for Base64 image
            if (preg_match('/data:image\/(\w+);base64,(.+)/', $src, $matches)) {
                $imageType = $matches[1];
                $imageData = base64_decode($matches[2]);

                if ($imageData) {
                    $imageName = 'desc_' . time() . '_' . uniqid() . '.' . $imageType;
                    $path = 'products/description/' . $imageName;

                    // Save to public disk
                    Storage::disk('public')->put($path, $imageData);

                    // Replace src with public URL
                    $newSrc = Storage::url($path);
                    $img->setAttribute('src', $newSrc);
                    $hasChanges = true;
                }
            }
        }

        if ($hasChanges) {
            return $dom->saveHTML();
        }

        return $content;
    }
    /* ----------------------------------------
       Stock History Start
    ---------------------------------------- */

    public function viewStockHistory($id = null, $variationId = null)
    {
        // Fetch minimal product list for search
        $products = Product::select('id', 'name', 'barcode')->with('variations')->orderBy('name')->get();
        
        $product = null;
        $selectedVariation = null;
        $summary = [
            'qty_in' => [
                'total' => 0,
                'purchase' => 0,
                'opening' => 0,
                'sale_return' => 0,
                'transfer_in' => 0
            ],
            'qty_out' => [
                'total' => 0,
                'sold' => 0,
                'purchase_return' => 0,
                'adjustment' => 0,
                'transfer_out' => 0
            ],
            'current_stock' => 0
        ];
        
        if ($id) {
            $product = Product::with('variations')->findOrFail($id);
            
            // Check if variation_id is provided via request
            $variationId = request()->input('variation_id', $variationId);
            
            // Determine which barcode/SKU to use
            if ($variationId && $product->variations->isNotEmpty()) {
                $selectedVariation = $product->variations->firstWhere('id', $variationId);
                if ($selectedVariation) {
                    $barcode = $selectedVariation->var_sku;
                } else {
                    $barcode = $product->barcode;
                }
            } else {
                $barcode = $product->barcode;
            }

            // Optimized DB Sums
            $summary['qty_in']['purchase'] = Purchase::where('barcode', $barcode)->sum('qty');
            $summary['qty_out']['sold'] = Sale::where('barcode', $barcode)->sum('qty');
            
            // Adjustments
            $adjIn = Stock::where('barcode', $barcode)->where('qty', '>', 0)->sum('qty');
            $adjOut = Stock::where('barcode', $barcode)->where('qty', '<', 0)->sum('qty');
            
            $summary['qty_in']['opening'] = $adjIn;
            $summary['qty_out']['adjustment'] = abs($adjOut);
            
            // Totals
            $summary['qty_in']['total'] = $summary['qty_in']['purchase'] + $summary['qty_in']['opening'];
            $summary['qty_out']['total'] = $summary['qty_out']['sold'] + $summary['qty_out']['adjustment'];
            $summary['current_stock'] = $summary['qty_in']['total'] - $summary['qty_out']['total'];
        }

        return Inertia::render('Inventory/Product/ProductStockHistory', [
            'products' => $products,
            'product' => $product,
            'selectedVariation' => $selectedVariation,
            'summary' => $summary
        ]);
    }

    public function stockHistoryApi($id)
    {
        $product = Product::with('variations')->findOrFail($id);
        
        // Check if variation_id is provided
        $variationId = request()->input('variation_id');
        
        // Determine which barcode/SKU to use
        if ($variationId && $product->variations->isNotEmpty()) {
            $selectedVariation = $product->variations->firstWhere('id', $variationId);
            if ($selectedVariation) {
                $barcode = $selectedVariation->var_sku;
            } else {
                $barcode = $product->barcode;
            }
        } else {
            $barcode = $product->barcode;
        }
        
        // Calculate Current Stock for Reverse Running Balance
        $pIn = DB::table('purchases')->where('barcode', $barcode)->sum('qty');
        $sOut = DB::table('sales')->where('barcode', $barcode)->sum('qty');
        $adjIn = DB::table('stocks')->where('barcode', $barcode)->where('qty', '>', 0)->sum('qty');
        $adjOut = DB::table('stocks')->where('barcode', $barcode)->where('qty', '<', 0)->sum('qty');
        
        $running = ($pIn + $adjIn) - ($sOut + abs($adjOut));
        
        // 1. Sales
        $sales = DB::table('sales')
            ->leftJoin('counters', 'sales.sale_no', '=', 'counters.id')
            ->leftJoin('accounts', 'counters.cust_id', '=', 'accounts.id')
            ->select(
                DB::raw("'Sale' as type"),
                DB::raw("(-1 * sales.qty) as qty_val"),
                DB::raw("CONCAT('-', sales.qty) as qty_change"),
                DB::raw("COALESCE(sales.created_at, sales.day) as date_col"),
                'sales.sale_no as reference_no',
                DB::raw("COALESCE(accounts.name, 'Walk-in') as info")
            )
            ->where('sales.barcode', $barcode);

        // 2. Purchases
        $purchases = DB::table('purchases')
            ->leftJoin('pcounters', 'purchases.pur_no', '=', 'pcounters.id')
            ->leftJoin('accounts', 'pcounters.cust_id', '=', 'accounts.id')
            ->select(
                DB::raw("'Purchase' as type"),
                DB::raw("purchases.qty as qty_val"),
                DB::raw("CONCAT('+', purchases.qty) as qty_change"),
                DB::raw("COALESCE(purchases.created_at, purchases.day) as date_col"),
                'purchases.pur_no as reference_no',
                DB::raw("COALESCE(accounts.name, 'Vendor') as info")
            )
            ->where('purchases.barcode', $barcode);

        // 3. Adjustments
        $adjustments = DB::table('stocks')
            ->leftJoin('users', 'stocks.user_id', '=', 'users.id')
            ->select(
                DB::raw("'Adjustment' as type"),
                DB::raw("stocks.qty as qty_val"),
                DB::raw("IF(stocks.qty > 0, CONCAT('+', stocks.qty), stocks.qty) as qty_change"),
                DB::raw("COALESCE(stocks.created_at, stocks.day) as date_col"),
                DB::raw("CONCAT('ADJ-', stocks.id) as reference_no"),
                DB::raw("COALESCE(users.name, 'System') as info")
            )
            ->where('stocks.barcode', $barcode);
            
        // Union Query - DESCENDING
        $history = $sales->unionAll($purchases)->unionAll($adjustments)
            ->orderBy('date_col', 'desc')
            ->get();
            
        // Calculate Running Balance (Reverse Logic)
        $final = [];
        foreach ($history as $item) {
            $item->new_qty = $running;
            $item->date = $item->date_col ? date('m-d-Y H:i', strtotime($item->date_col)) : '';
            $final[] = $item;
            // Backtrack: previous balance was current - change (since change is signed)
            // Wait, balance_after = balance_before + change
            // So balance_before = balance_after - change
            $running -= $item->qty_val;
        }

        return response()->json($final);
    }

    /* ----------------------------------------
       Stock History End
    ---------------------------------------- */
}
