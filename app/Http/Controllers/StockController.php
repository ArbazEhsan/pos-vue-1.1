<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Stock;
use App\Models\Company;
use App\Models\Manufactureds;
use App\Models\Recipe;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockController extends Controller
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

        return Inertia::render('Inventory/Stock/Stock', [
            'products' => $products,
        ]);
	}

    public function getStockDetails()
    {
        $product = Product::with(['category', 'brand', 'variations'])->where('consumable', true)->latest()->get();
        return response()->json($product);
    }

    public function fillCategory2()
    {
        $result='';

        $count = Category::count();

        if ($count>0) 
        {
            $result = Category::where(['status'=>1,'id'=>8])->orderBy('name')->get();
        }

        return Inertia::render('Inventory/Stock/CreateStock', [
            'categories' => $result,
        ]);
    }

    public function store2(Request $request)
    {
        $request->validate([
            'pro_name'        => 'required|max:255',
            'pro_short_code'  => 'required|max:255|unique:products,barcode',
            'pro_category'    => 'required|max:255',
            'pro_pur_price'   => 'required|max:255',
            'pro_w_price'     => 'required|max:255',
            'pro_r_price'     => 'required|max:255',
            'pro_qty'         => 'required|max:255',
        ]);

    	$post = new Product;
    	$post->name = $request->pro_name;
    	$post->barcode = $request->pro_short_code;
    	$post->category_id = $request->pro_category;
    	$post->purchase = $request->pro_pur_price;
    	$post->whole_sale = $request->pro_w_price;
    	$post->retail = $request->pro_r_price;
    	$post->min_qty = $request->pro_qty;
        $post->qty = $request->pro_qty;
    	$post->type = 'stock';
        $post->user_id = Auth::id();

    	$post->save();

    	return redirect('/viewStock')->with('message','Stock Added Successfully');
    }

    public function edit2($id)
    {
        $cat='';
    	$result = Product::find($id)->first();
        $data = Category::where(['status'=>1,'id'=>'8'])->orderBy('name')->get();

        return Inertia::render('Inventory/Stock/EditStock', [
            'products' => $result,
            'categories' => $data,
        ]);
    }

    public function update2(Request $request)
    {
        $request->validate([
            'pro_name' => 'required|max:255',
            'pro_short_code' => 'required|max:255',
            'pro_category' => 'required|max:255',
            'pro_pur_price' => 'required|max:255',
            'pro_w_price' => 'required|max:255',
            'pro_r_price' => 'required|max:255',
            'pro_qty' => 'required|max:255',
        ]);
        
    	$id = $request->pro_id;
    	$post = Product::find($id);
    	$post->name = $request->pro_name;
    	$post->barcode = $request->pro_short_code;
    	$post->category_id = $request->pro_category;
    	$post->purchase = $request->pro_pur_price;
    	$post->whole_sale = $request->pro_w_price;
    	$post->retail = $request->pro_r_price;
    	$post->min_qty = $request->pro_qty;
        $post->user_id = Auth::id();

    	$post->save();
        return redirect('/viewStock')->with('message','Stock Updated Successfully');
    }

    public function viewStock()
    {
        return view('product.viewStockOpening');
    }

    public function searchStock(Request $request)
    {
        $barcode = $request->barcode;
        $name = $request->pname;
        if (empty($barcode) && empty($name)) {
            $result = Product::all();
            $data = "";
            $count = 0;
            foreach ($result as $value) {
                $count++;
                $data .= '<tr>
                    <td>'.$count.'</td>'.'
                    <td>'.$value->barcode.'</td>'.'
                    <td>'.$value->name.'</td>'.'
                    <td><input type="number" name="qty[]" class="form-control col-md-6" placeholder="Enter Qty" value="'.$value->qty.'" autocomplete="false" autofocus="true"><input type="hidden" name="id[]" class="form-control col-md-6" value="'.$value->id.'"></td>'.'
                </tr>';

            }

        } else {
            $result = Product::where([
                ['barcode',$barcode],
                ['name',$name]
            ])
            ->orWhere('barcode',$barcode)
            ->orWhere('name',$name)->get();
            $data = "";
            $count = 0;
            foreach ($result as $value) {
                $count++;
                $data .= '<tr>
                    <td>'.$count.'</td>'.'
                    <td>'.$value->barcode.'</td>'.'
                    <td>'.$value->name.'</td>'.'
                    <td><input type="number" name="qty[]" class="form-control col-md-6" placeholder="Enter Qty" value="'.$value->qty.'" autocomplete="false" autofocus="true"><input type="hidden" name="id[]" class="form-control col-md-6" value="'.$value->id.'"></td>'.'
                </tr>';

            }
        }
        echo $data;
    }

    public function updateStock(Request $request)
    {
        $id = $request->id;
        $qty = $request->qty;
        $uid = Auth::id();

        for ($i=0; $i<count($id) ; $i++) {
            if (!empty($qty[$i])) {
                $result = Product::whereId($id[$i])->update(['qty'=>$qty[$i],'user_id'=>$uid]);
             }  
        }
        return "Stock Updated Successfully";
    }
}
