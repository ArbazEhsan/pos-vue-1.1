<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Sale;
use App\Models\Counter;
use App\Models\PCounter;
use App\Models\Company;
use App\Models\Ledger;
use App\Models\Tran;
use App\Models\Purchase;
use App\Models\Manufactureds;
use App\Models\Recipe;
use App\Models\ProductCombo;
use App\Models\Register;
use App\Models\BusinessSetting;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function saleForm()
    {
    	$data = "";
    	$result = Account::where(['type'=>'Customer','status'=>'1'])->get();
        $product = Product::with('category:id,name')->get();

    	foreach ($result as $value) {
    		$data .= '<option value="'.$value->id.'">'.$value->name.'</option>';
    	}
    	return view('invoice.saleForm')->with('account',$data);
    }

    public function saleForm2(Request $request)
    {
    	$result = Account::where(['type'=>'Customer','status'=>'1'])->get();

        $categories = Category::where('status',1)
                    ->where('id','!=','8')
                    ->orderBy('name')->get();
        
        $brands = Brand::select('id','name')->where('status',1)->orderBy('name')->get();

        if($request->search)
        {

            // $product = Product::where('products.type','category')
            //                 ->where('products.name','like','%'.$request->search.'%')
            //                 ->join('categories','category_id','=','categories.id')
            //                 ->select('products.*','categories.name as cat_name')
            //                 ->get();

            $product = Product::with('category:id,name')
                    ->where('type','category')
                    ->where('name','like','%'.$request->search.'%')
                    ->get();
        }
        elseif($request->cat_id == 'all')
        {
            $product = Product::with('category:id,name', 'variations')->get();
        }
        else 
        {
            $product = Product::with('category:id,name', 'variations')
                    ->where('category_id', $request->cat_id)
                    ->get();
        }
    	
        return Inertia::render('Sale/Index', [
            'accounts' => $result,
            'categories' => $categories,
            'brands' => $brands,
            'products' => $product,
        ]);
    }

    // public function saleForm3()
    // {
    //     $result = Account::where(['type'=>'Customer','status'=>'1'])->get();
    //     $categories = Category::where('status',1)
    //                             ->where('id','!=','8')
    //                             ->orderBy('name')
    //                             ->get();

    //     return Inertia::render('Sale/Index', [
    //         'accounts' => $result,
    //         'categories' => $categories,
    //     ]);
    // }

    public function saleForm3()
    {
        $customers = Account::where(['type'=>'Customer','status'=>'1'])->get();
        $products = Product::with('variations')->select('id', 'name', 'retail', 'purchase', 'whole_sale', 'qty', 'barcode', 'variation_name')->get(); // Fetch necessary fields

        $result = Counter::select('id')->latest()->first();
        $newInvoiceId = $result ? $result->id + 1 : 1;

        $businessType = BusinessSetting::value('business_type');

        return Inertia::render('Sale/AdminSale', [
            'customers' => $customers,
            'products' => $products,
            'invoice_id' => $newInvoiceId,
            'business_type' => $businessType,
        ]);
    }

    public function saveSaleInvoice(Request $request)
    {
        $post = new Counter;
    
        // Assign values
        $post->day = $request->sale_day;
        $post->user_id = Auth::id();
        $post->bilty_no = $request->bilty_no;
        $post->ref = $request->reference;
        $post->cust_id = $request->cust_id;
        $post->gross = $request->gross;
        $post->InvDiscount = $request->discount;
        $post->InvProfit = $request->profit;
        $post->received = $request->received;
        $post->remaining = $request->remaining;
        $post->finalVal = $request->final_value;
        $post->order_type = $request->order_type;
        $post->table_no = $request->table_no;
        $post->return = $request->return;
        $post->save();
    
        // Insert new sales records (Return Items)
        foreach ($request->pros as $item) {
            Sale::create([
                'sale_no' => $post->id,
                'day' => $request->sale_day,
                'barcode' => $item['id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'final' => $item['item_total'],
                'type' => 'Sale'
            ]);
    
            // Update product / variation / combo quantity
            $variation = ProductVariation::where('var_sku', $item['id'])->first();
            if ($variation) {
                $variation->decrement('var_qty', $item['qty']);
                if ($variation->product_id) {
                    Product::where('id', $variation->product_id)->decrement('qty', $item['qty']);
                }
            } else {
                $product = Product::with('combos')->where('barcode', $item['id'])->first();
                if ($product) {
                    $product->decrement('qty', $item['qty']);

                    // If combo product, decrement each combo component item
                    if ($product->type == 'combo' || $product->combos->isNotEmpty()) {
                        foreach ($product->combos as $comboItem) {
                            $comboDeductQty = ($comboItem->quantity ?? 1) * $item['qty'];
                            if ($comboItem->combo_variation_id) {
                                ProductVariation::where('id', $comboItem->combo_variation_id)
                                    ->decrement('var_qty', $comboDeductQty);
                            }
                            if ($comboItem->combo_product_id) {
                                Product::where('id', $comboItem->combo_product_id)
                                    ->decrement('qty', $comboDeductQty);
                            }
                        }
                    }
                }
            }

            $manufacture = Manufactureds::with('recipes')->where('product_id', $item['id'])->first();
            if ($manufacture && $manufacture->recipes->isNotEmpty() && $manufacture->subtract_qty_stage == 'Sale') {
                foreach ($manufacture->recipes as $rec) {
                    $product = Product::find($rec->product_id);
                    $product->decrement('qty', $rec->qty * $qty[$key]);
                }
            }
        }
    
        // Transaction table update/create
        $post2 = Tran::create([
            'invoice_id' => $post->id, 
            'day' => $request->sale_day,
            'user_id' => Auth::id(),
            'account_id' => $request->cust_id,
            'amount' => $request->final_value,
            'bill_no' => 0,
            'remarks' => $request->reference,
            'type' => "SV",
        ]);
    
        // Ledger table: Credit the Customer for the Return Value
        Ledger::create([
            'trans_id' => $post2->id, 
            'account_id' => $request->cust_id, 
            'type' => "SV",
            'dr' => $request->final_value,
            'day' => $request->sale_day,
        ]);
    
        // Handle received amount (Refund Paid to Customer): Debit the Customer
        if ($request->received > 0) {
            Ledger::create([
                'trans_id' => $post2->id, 
                'account_id' => $request->cust_id, 
                'type' => "SV",
                'cr' => $request->received,
                'day' => $request->sale_day,
            ]);
        }
    
        return response()->json([
            'message' => 'Invoice saved successfully',
            'invoice_id' => $post->id
        ]);
    }

    public function saleForm4()
    {
        $date = Carbon::today()->toDateString();
        
        $result2 = Counter::where('counters.order_type', 'Dine_In')
        ->where('counters.day', $date)
        ->where('counters.remaining', '!=', '0')
        ->get(['id', 'table_no', 'day', 'finalVal']);
        
        if($result2->isEmpty()){
            return response()->json(['data' => '404']);
        }
        return response()->json($result2);
    }

    public function saleForm5($counterId, $from = null)
    {
        $query = Sale::where('sale_no', $counterId)
            ->join('products', 'sales.barcode', '=', 'products.barcode')
            ->join('counters', 'sales.sale_no', '=', 'counters.id')
            ->select(
                'sales.barcode',
                'sales.qty',
                'sales.price',
                'products.name',
                'counters.inv_status'
            );
        if($from == 'return')
        {
            $result = $query->where('counters.inv_status', '!=', 'Returned');
        }   
        
        $result = $query->get();
        return $result;
    }

    public function saleReturn($id)
    {
        $customers = Account::where(['type'=>'Customer','status'=>'1'])->get();
        $products = Product::with('variations')->select('id', 'name', 'retail', 'purchase', 'whole_sale', 'qty', 'barcode', 'variation_name')->get(); // Fetch necessary fields
        $businessType = BusinessSetting::value('business_type');

        $invoice = Counter::where('counters.id',$id)
            ->with(['sales' => function($q) {
                $q->select('sale_no', 'barcode', 'qty', 'price', 'final', 'type');
            }, 'sales.product:id,barcode,name', 'sales.variation:id,product_id,var_name,var_sku',
                'sales.variation.product:id,name,variation_name,category_id',]) // Assuming product relationship exists on Sale model or via barcode
            ->first();

        return Inertia::render('InvoiceReturn/SaleReturn', [
            'customers' => $customers,
            'products' => $products,
            'invoice_data' => $invoice,
            'invoice_id' => $id,
            'business_type' => $businessType
        ]);
    }

    public function purchaseReturn($id)
    {
        $customers = Account::where(['type'=>'Vendor','status'=>'1'])->get();
        $products = Product::select('id', 'name', 'retail', 'purchase', 'whole_sale', 'qty', 'barcode')->get(); // Fetch necessary fields

        $invoice = PCounter::where('pcounters.id',$id)
            ->with(['purchases' => function($q) {
                $q->select('pur_no', 'barcode', 'qty', 'price', 'final', 'type');
            }, 'purchases.product:barcode,name,id']) // Assuming product relationship exists on Sale model or via barcode
            ->first();

        return Inertia::render('InvoiceReturn/PurchaseReturn', [
            'customers' => $customers,
            'products' => $products,
            'invoice_data' => $invoice,
            'invoice_id' => $id
        ]);
    }

    public function purchaseForm2()
    {
        $customers = Account::where(['type'=>'Vendor','status'=>'1'])->get();
        // $products = Product::select('id', 'name', 'retail', 'purchase', 'whole_sale', 'qty', 'barcode')->get(); // Fetch necessary fields
        $products = Product::with('variations')->select('id', 'name', 'retail', 'purchase', 'whole_sale', 'qty', 'barcode', 'variation_name')->get(); // Fetch necessary fields

        $result = PCounter::select('id')->latest()->first();
        $newInvoiceId = $result ? $result->id + 1 : 1;

        return Inertia::render('Sale/AdminPurchase', [
            'customers' => $customers,
            'products' => $products,
            'invoice_id' => $newInvoiceId,
        ]);
    }

    public function savePurchaseInvoice(Request $request)
    {
        $post = new PCounter;
    
        // Assign values
        $post->day = $request->sale_day;
        $post->user_id = Auth::id();
        $post->bilty_no = $request->bilty_no;
        $post->ref = $request->reference;
        $post->cust_id = $request->cust_id;
        $post->gross = $request->gross;
        $post->InvDiscount = $request->discount;
        $post->InvProfit = $request->profit;
        $post->received = $request->received;
        $post->remaining = $request->remaining;
        $post->finalVal = $request->final_value;
        $post->save();
    
        // Insert new sales records (Return Items)
        foreach ($request->pros as $item) {
            Purchase::create([
                'pur_no' => $post->id,
                'day' => $request->sale_day,
                'barcode' => $item['id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'final' => $item['item_total'],
                'type' => 'Purchase'
            ]);
    
            // Update product / variation / combo quantity (Increment for purchase)
            $variation = ProductVariation::where('var_sku', $item['id'])->first();
            if ($variation) {
                $variation->increment('var_qty', $item['qty']);
                if ($variation->product_id) {
                    Product::where('id', $variation->product_id)->increment('qty', $item['qty']);
                }
            } else {
                $product = Product::with('combos')->where('barcode', $item['id'])->first();
                if ($product) {
                    $product->increment('qty', $item['qty']);

                    // If combo product, increment each combo component item
                    if ($product->type == 'combo' || $product->combos->isNotEmpty()) {
                        foreach ($product->combos as $comboItem) {
                            $comboReturnQty = ($comboItem->quantity ?? 1) * $item['qty'];
                            if ($comboItem->combo_variation_id) {
                                ProductVariation::where('id', $comboItem->combo_variation_id)
                                    ->increment('var_qty', $comboReturnQty);
                            }
                            if ($comboItem->combo_product_id) {
                                Product::where('id', $comboItem->combo_product_id)
                                    ->increment('qty', $comboReturnQty);
                            }
                        }
                    }
                }
            }
        }
    
        // Transaction table update/create
        $post2 = Tran::create([
            'invoice_id' => $post->id, 
            'day' => $request->sale_day,
            'user_id' => Auth::id(),
            'account_id' => $request->cust_id,
            'amount' => $request->final_value,
            'bill_no' => 0,
            'remarks' => $request->reference,
            'type' => "PV",
        ]);
    
        // Ledger table: Credit the Customer for the Return Value
        Ledger::create([
            'trans_id' => $post2->id, 
            'account_id' => $request->cust_id, 
            'type' => "PV",
            'cr' => $request->final_value,
            'day' => $request->sale_day,
        ]);
    
        // Handle received amount (Refund Paid to Customer): Debit the Customer
        if ($request->received > 0) {
            Ledger::create([
                'trans_id' => $post2->id, 
                'account_id' => $request->cust_id, 
                'type' => "PV",
                'dr' => $request->received,
                'day' => $request->sale_day,
            ]);
        }
    
        return response()->json([
            'message' => 'Invoice saved successfully',
            'invoice_id' => $post->id
        ]);
    }

    public function purchaseForm()
    {
        $data = "";
        $result = Account::where(['type'=>'Vendor','status'=>'1'])->get();
        foreach ($result as $value) {
            $data .= '<option value="'.$value->id.'">'.$value->name.'</option>';
        }
        return view('invoice.purchaseForm')->with('account',$data);
    }

    public function getAllProduct($barcode,$name,$rowNum,$from)
    {
    	$data="";$count=$price=0;
    	if ($name=='all' && $barcode=='0') {
    		$result = Product::where(['status'=>'1','type'=>'category'])->get();
            foreach ($result as $value) {
                $price=0;
                if ($from == 'sale') {
                    $price=$value->retail;
                }
                else {
                    $price=$value->purchase;
                }
            $count++;
            $data .= '<tr>
                    <td><a href="#" onClick="addFromModal('.$value->id.','.$rowNum.')" data-dismiss="modal"><i class="now-ui-icons ui-1_simple-add"></i></a></td>
                    <td>'.$count.'</td>'.'
                    <td>'.$value->barcode.'</td>'.'
                    <td>'.$value->name.'</td>'.'
                    <td>'.$value->purchase.'</td>'.'
                    <td>'.$value->whole_sale.'</td>'.'
                    <td>'.$price.'</td></tr>';
            }
            echo $data;
    	}
    	else{
    		// $result = Product::where('name','like', '%'.$name.'%')
      //       ->orWhere('barcode','like', '%'.$barcode.'%')->get();
            $result = Product::where(['barcode'=>$barcode,'status'=>1])->get();
            if ($from == 'sale') {
                $price=$result[0]->retail;
            }
            else {
                $price=$result[0]->purchase;
            }
            echo $result[0]->id.','.$result[0]->barcode.','.$result[0]->name.','.$price;
    	}
    	
    }

	public function getDetail($id,$from)
    {
        $price=0;
    	$result = Product::whereIn('id',[$id])->get();
        if ($from == 'sale') {
            $price=$result[0]->retail;
        }
        else {
            $price=$result[0]->purchase;
        }
    	echo $result[0]->id.','.$result[0]->barcode.','.$result[0]->name.','.$price;
    }       

    public function saveInvc(Request $request,$e,$f,$g,$h,$from)
    {
        $post = new Counter;
        $post->day = $request->sale_day;
        $post->user_id = Auth::id();
        $post->bilty_no = $request->bilty_no;
        $post->ref = $request->reference;
        $post->cust_id = $request->cust_name;
        $post->InvDiscount = $request->discount;
        $post->InvProfit = $request->profit;
        $post->received = $request->received;
        $post->remaining = $request->remaining;
        $post->finalVal = $request->final_value;
        $post->order_type = $request->order_type;
        $post->table_no = $request->table_no;
        $post->return = 0;
        $post->save();

        $posts = new Sale;
        $barcode = json_decode($e, TRUE);
        $qty = json_decode($f, TRUE);
        $price = json_decode($g, TRUE);
        $final = json_decode($h, TRUE);
        foreach ($barcode as $key => $value) {
            $a = 0;
            $data = array(
                'sale_no'=>$post->id,'day'=>$request->sale_day,
                'barcode'=>$barcode[$key],'qty'=>$qty[$key],
                'price'=>$price[$key],'final'=>$final[$key],
                'type'=>'Sale'
            );
            Sale::create($data);
            $a = Product::where('barcode',$barcode[$key])->first();
            $a->decrement('qty', $qty[$key]);
            // $a = $a->qty - $qty[$key];
            // Product::where('barcode',$barcode[$key])
            // ->update(['qty'=>$a]);

            $manufacture = Manufactureds::with('recipes')->where('product_id', $a->id)->first();
            if ($manufacture && $manufacture->recipes->isNotEmpty() && $manufacture->subtract_qty_stage == 'Sale') {
                foreach ($manufacture->recipes as $rec) {
                    $product = Product::find($rec->product_id);
                    $product->decrement('qty', $rec->qty * $qty[$key]);
                }
            }
        }

        /* trans query */
        $post2 = new Tran;
        $post2->day = $request->sale_day;
        $post2->user_id = Auth::id();
        $post2->account_id = $request->cust_name;
        $post2->invoice_id = $post->id;
        $post2->amount = $request->final_value;
        $post2->bill_no = $request->bilty_no;
        $post2->remarks = $request->reference;
        $post2->type = "SV";
        $post2->status = 1;
        $post2->save();

        /* ledger query*/
        $post3 = new Ledger;
        $post3->dr = $request->final_value;
        $post3->day = $request->sale_day;
        $post3->account_id = $request->cust_name;
        $post3->trans_id = $post2->id;
        $post3->type = "SV";
        $post3->status = 1;
        $post3->save();

        $received = $request->received;
        if ($received>0) {
            $post4 = new Ledger;
            $post4->cr = $received;
            $post4->day = $request->sale_day;
            $post4->account_id = $request->cust_name;
            $post4->trans_id = $post2->id;
            $post4->type = "SV";
            $post4->status = 1;
            $post4->save();
        }

        echo $post->id;

    }

    public function saveInvc2(Request $request)
    {
        // Check if hid is set, then find the existing Counter record, otherwise create a new one
        $post = Counter::find($request->hid) ?? new Counter();
    
        // Assign values
        $post->day = $request->sale_day;
        $post->user_id = Auth::id();
        $post->cust_id = $request->cust_id;
        $post->bilty_no = 0;
        $post->ref = '';
        $post->order_type = $request->order_type;
        $post->table_no = $request->table_no;
        $post->InvDiscount = $request->discount;
        $post->InvProfit = 0;
        $post->received = $request->received;
        $post->remaining = $request->remaining;
        $post->finalVal = $request->gross;
        $post->return = $request->return;
        $post->tax = $request->order_tax;
        $post->inv_status = $request->inv_status;
        $post->save(); // Save or update
    
        // Delete old sales records if updating
        if ($request->hid) {
            Sale::where('sale_no', $post->id)->delete();
        }
    
        // Insert new sales records
        foreach ($request->pros as $item) {
            Sale::create([
                'sale_no' => $post->id,
                'day' => $request->sale_day,
                'barcode' => $item['id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'final' => $item['item_total'],
                'type' => 'Sale'
            ]);
    
            // Update product / variation / combo quantity
            if($request->inv_status == 'cash' || $request->inv_status == 'credit' || $request->inv_status == 'card'){
                $variation = ProductVariation::where('var_sku', $item['id'])->first();
                if ($variation) {
                    $variation->decrement('var_qty', $item['qty']);
                    if ($variation->product_id) {
                        Product::where('id', $variation->product_id)->decrement('qty', $item['qty']);
                    }
                } else {
                    $product = Product::with('combos')->where('barcode', $item['id'])->first();
                    if ($product) {
                        $product->decrement('qty', $item['qty']);

                        // If combo product, decrement each combo component item
                        if ($product->type == 'combo' || $product->combos->isNotEmpty()) {
                            foreach ($product->combos as $comboItem) {
                                $comboDeductQty = ($comboItem->quantity ?? 1) * $item['qty'];
                                if ($comboItem->combo_variation_id) {
                                    ProductVariation::where('id', $comboItem->combo_variation_id)
                                        ->decrement('var_qty', $comboDeductQty);
                                }
                                if ($comboItem->combo_product_id) {
                                    Product::where('id', $comboItem->combo_product_id)
                                        ->decrement('qty', $comboDeductQty);
                                }
                            }
                        }
                    }
                }
            }
        }
    
        // Transaction table update/create
        $post2 = Tran::updateOrCreate(
            ['invoice_id' => $post->id], 
            [
                'day' => $request->sale_day,
                'user_id' => Auth::id(),
                'account_id' => $request->cust_id,
                'amount' => $request->gross,
                'bill_no' => 0,
                'remarks' => '',
                'type' => "SV",
                'status' => 1
            ]
        );
    
        // Ledger table update/create
        Ledger::updateOrCreate(
            ['trans_id' => $post2->id, 'account_id' => $request->cust_id, 'type' => "SV"],
            [
                'dr' => $request->gross,
                'day' => $request->sale_day,
                'status' => 1
            ]
        );
    
        // Handle received amount ledger entry
        if ($request->received > 0) {
            Ledger::updateOrCreate(
                ['trans_id' => $post2->id, 'account_id' => $request->cust_id, 'type' => "SV"],
                [
                    'cr' => $request->received,
                    'day' => $request->sale_day,
                    'status' => 1
                ]
            );
        }
    
        return response()->json([
            'id' => $post->id,
        ]);
    }

    public function saveSaleReturn(Request $request)
    {
        $post = new Counter;
    
        // Assign values
        $post->day = $request->sale_day;
        $post->user_id = Auth::id();
        $post->bilty_no = $request->bilty_no;
        $post->ref = $request->reference . ($request->credit_note_issued ? ' | ' . $request->credit_note_issued : '');
        $post->cust_id = $request->cust_id;
        $post->gross = $request->gross;
        $post->InvDiscount = $request->discount;
        $post->InvProfit = $request->profit;
        $post->received = $request->received;
        $post->remaining = $request->remaining;
        $post->finalVal = ($request->final_value ? $request->final_value : $request->gross);
        $post->order_type = $request->order_type;
        $post->table_no = $request->table_no;
        $post->return = $request->return;
        $post->original_sale_id = $request->hid;
        $post->inv_status = 'Credit_Note_Issued';
        $post->save();
    
        // Update the original invoice status
        // if ($request->hid) {
        //     Counter::find($request->hid)->update(['inv_status' => 'Returned']);
        // }
        if ($request->filled('hid')) {
            // Single query approach (more efficient)
            Counter::where('id', $request->hid)->update(['inv_status' => 'Returned']);
        }
    
        // Insert new sales records (Return Items)
        foreach ($request->pros as $item) {
            Sale::create([
                'sale_no' => $post->id,
                'day' => $request->sale_day,
                'barcode' => $item['id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'final' => $item['item_total'],
                'type' => 'Return'
            ]);
    
            // Update product / variation / combo quantity (Increment for return)
            $variation = ProductVariation::where('var_sku', $item['id'])->first();
            if ($variation) {
                $variation->increment('var_qty', $item['qty']);
                if ($variation->product_id) {
                    Product::where('id', $variation->product_id)->increment('qty', $item['qty']);
                }
            } else {
                $product = Product::with('combos')->where('barcode', $item['id'])->first();
                if ($product) {
                    $product->increment('qty', $item['qty']);

                    // If combo product, increment each combo component item
                    if ($product->type == 'combo' || $product->combos->isNotEmpty()) {
                        foreach ($product->combos as $comboItem) {
                            $comboReturnQty = ($comboItem->quantity ?? 1) * $item['qty'];
                            if ($comboItem->combo_variation_id) {
                                ProductVariation::where('id', $comboItem->combo_variation_id)
                                    ->increment('var_qty', $comboReturnQty);
                            }
                            if ($comboItem->combo_product_id) {
                                Product::where('id', $comboItem->combo_product_id)
                                    ->increment('qty', $comboReturnQty);
                            }
                        }
                    }
                }
            }
        }
    
        // Transaction table update/create
        $post2 = Tran::create([
            'invoice_id' => $post->id, 
            'day' => $request->sale_day,
            'user_id' => Auth::id(),
            'account_id' => $request->cust_id,
            'amount' => ($request->final_value ? $request->final_value : $request->gross),
            'bill_no' => 0,
            'remarks' => $request->reference . ($request->credit_note_issued ? ' - ' . $request->credit_note_issued : ''),
            'type' => "SRV",
        ]);
    
        // Ledger table: Credit the Customer for the Return Value
        Ledger::create([
            'trans_id' => $post2->id, 
            'account_id' => $request->cust_id, 
            'type' => "SRV",
            'cr' => ($request->final_value ? $request->final_value : $request->gross),
            'day' => $request->sale_day,
        ]);
    
        // Handle received amount (Refund Paid to Customer): Debit the Customer
        if ($request->received > 0) {
            Ledger::create([
                'trans_id' => $post2->id, 
                'account_id' => $request->cust_id, 
                'type' => "SRV",
                'dr' => $request->received,
                'day' => $request->sale_day,
            ]);
        }
    
        return response()->json([
            'message' => 'Invoice saved successfully',
            'invoice_id' => $post->id,
            'id' => $post->id
        ]);
    }

    public function savePurchaseReturn(Request $request)
    {

        $post = new PCounter;
    
        // Assign values
        $post->day = $request->sale_day;
        $post->user_id = Auth::id();
        $post->bilty_no = $request->bilty_no;
        $post->ref = $request->reference . ($request->credit_note_issued ? ' | ' . $request->credit_note_issued : '');
        $post->cust_id = $request->cust_id;
        $post->gross = $request->gross;
        $post->InvDiscount = $request->discount;
        $post->InvProfit = $request->profit;
        $post->received = $request->received;
        $post->remaining = $request->remaining;
        $post->finalVal = $request->final_value;
        $post->original_purchase_id = $request->hid;
        $post->inv_status = 'Debit_Note_Issued';
        $post->save();
    
        // Update the original invoice status
        if ($request->filled('hid')) {
            PCounter::where('id', $request->hid)->update(['inv_status' => 'Returned']);
        }
    
        // Insert new sales records (Return Items)
        foreach ($request->pros as $item) {
            Purchase::create([
                'pur_no' => $post->id,
                'day' => $request->sale_day,
                'barcode' => $item['id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'final' => $item['item_total'],
                'type' => 'Return'
            ]);
    
            // Update product quantity (Increment for return)
            Product::where('barcode', $item['id'])
                ->decrement('qty', $item['qty']);
        }
    
        // Transaction table update/create
        $post2 = Tran::create([
            'invoice_id' => $post->id, 
            'day' => $request->sale_day,
            'user_id' => Auth::id(),
            'account_id' => $request->cust_id,
            'amount' => $request->final_value,
            'bill_no' => 0,
            'remarks' => $request->reference . ($request->credit_note_issued ? ' - ' . $request->credit_note_issued : ''),
            'type' => "PRV",
        ]);
    
        // Ledger table: Debit the Vendor for the Return Value
        Ledger::create([
            'trans_id' => $post2->id, 
            'account_id' => $request->cust_id, 
            'type' => "PRV",
            'dr' => $request->final_value,
            'day' => $request->sale_day,
        ]);
    
        // Handle received amount (Refund Paid by Vendor): Credit the Vendor
        if ($request->received > 0) {
            Ledger::create([
                'trans_id' => $post2->id, 
                'account_id' => $request->cust_id, 
                'type' => "PRV",
                'cr' => $request->received,
                'day' => $request->sale_day,
            ]);
        }
    
        return response()->json([
            'message' => 'Invoice saved successfully',
            'invoice_id' => $post->id
        ]);
    }

    public function savePInvc(Request $request,$e,$f,$g,$h,$from)
    {
        $post = new PCounter;
        $post->day = $request->sale_day;
        $post->user_id = Auth::id();
        $post->bilty_no = $request->bilty_no;
        $post->ref = $request->reference;
        $post->cust_id = $request->cust_name;
        $post->InvDiscount = $request->discount;
        $post->InvProfit = $request->profit;
        $post->received = $request->received;
        $post->remaining = $request->remaining;
        $post->finalVal = $request->final_value;
        $post->save();

        $posts = new Purchase;
        $barcode = json_decode($e, TRUE);
        $qty = json_decode($f, TRUE);
        $price = json_decode($g, TRUE);
        $final = json_decode($h, TRUE);
        foreach ($barcode as $key => $value) {
            $data = array(
                'pur_no'=>$post->id,'day'=>$request->sale_day,
                'barcode'=>$barcode[$key],'qty'=>$qty[$key],'price'=>$price[$key],
                'final'=>$final[$key],'type'=>'purchase'
            );
            Purchase::insert($data);
            $a = Product::where('barcode',$barcode[$key])->pluck('qty');
            $a = $a[0]+$qty[$key];
            Product::where('barcode',$barcode[$key])
            ->update(['qty'=>$a]);
        }

        /* trans query */
        $post2 = new Tran;
        $post2->day = $request->sale_day;
        $post2->user_id = Auth::id();
        $post2->account_id = $request->cust_name;
        $post2->invoice_id = $post->id;
        $post2->amount = $request->final_value;
        $post2->bill_no = $request->bilty_no;
        $post2->remarks = $request->reference;
        $post2->type = "PV";
        $post2->status = 1;
        $post2->save();

        /* ledger query*/
        $post3 = new Ledger;
        $post3->cr = $request->final_value;
        $post3->day = $request->sale_day;
        $post3->account_id = $request->cust_name;
        $post3->trans_id = $post2->id;
        $post3->type = "PV";
        $post3->status = 1;
        $post3->save();

        $received = $request->received;
        if ($received>0) {
            $post4 = new Ledger;
            $post4->dr = $received;
            $post4->day = $request->sale_day;
            $post4->account_id = $request->cust_name;
            $post4->trans_id = $post2->id;
            $post4->type = "PV";
            $post4->status = 1;
            $post4->save();
        }

        echo $post->id;

    }

    public function sPrint($value)
    {
        $result = Counter::where('id', $value)
        ->select([
            'counters.*',
            'counters.created_at as time',
        ])
        ->with([
            'sales',
            'user' => function ($query) {
                $query->select('users.id', 'users.name as user');
            },

            'account' => function ($query) {
                $query->select('accounts.id', 'accounts.name as cust_name');
            },
            'sales.product' => function ($query) {
                $query->select('products.barcode', 'products.name');
            },
            'sales.variation' => function ($query) {
                $query->select('product_variations.id', 'product_variations.product_id', 'product_variations.var_name', 'product_variations.var_sku');
            },
            'sales.variation.product' => function ($query) {
                $query->select('products.id', 'products.name', 'products.variation_name');
            },
        ])
        ->first();
        // $company = Company::first();
        $company = BusinessSetting::select('business_name','address','phone','logo_path','business_type')->first();

        return view('invoice.salePrint')->with(['invoice'=>$result,'company'=>$company]);
    }

    public function pPrint($value)
    {
        $invoice = PCounter::with([
            'purchases',
            'user' => function ($query) {
                $query->select('users.id', 'users.name as user');
            },

            'account' => function ($query) {
                $query->select('accounts.id', 'accounts.name as cust_name');
            },
            'purchases.product' => function ($query) {
                $query->select('products.barcode', 'products.name');
            },
            ])->findOrFail($value);
        // $company = Company::first();
        $company = BusinessSetting::select('business_name','address','phone','logo_path')->first();

        return view('invoice.purchasePrint', compact('invoice', 'company'));
    }

    public function getCustomerDetails($id)
    {
        // Last 7 invoices
        $invoices = Counter::where(['cust_id' => $id])
            ->whereIn('inv_status', ['cash', 'Original'])
            ->orderBy('id', 'desc')
            ->take(7)
            ->get(['id', 'day', 'finalVal']);

        // Balance Calculation
        // Balance = Sum(Dr) - Sum(Cr)
        $debit = Ledger::where('account_id', $id)->whereYear('created_at', Carbon::now()->year)->sum('dr');
        $credit = Ledger::where('account_id', $id)->whereYear('created_at', Carbon::now()->year)->sum('cr');
        $balance = $debit - $credit;

        return response()->json([
            'invoices' => $invoices,
            'balance' => $balance
        ]);
    }

    public function getVendorDetails($id)
    {
        // Last 7 invoices
        $invoices = PCounter::where(['cust_id' => $id, 'inv_status' => 'Original'])
            ->orderBy('id', 'desc')
            ->take(7)
            ->get(['id', 'day', 'finalVal']);

        // Balance Calculation
        // Balance = Sum(Dr) - Sum(Cr)
        $debit = Ledger::where('account_id', $id)->sum('dr');
        $credit = Ledger::where('account_id', $id)->sum('cr');
        $balance = $debit - $credit;

        return response()->json([
            'invoices' => $invoices,
            'balance' => $balance
        ]);
    }

    public function sInvoice(Request $request)
    {
        $query = Counter::with('user:id');

        if ($request->search) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        // Apply date filter if provided
        if (!empty($request->start_date) && !empty($request->end_date)) 
        {
            $query->whereBetween('day', [$request->start_date, $request->end_date]);
        }
        else 
        {
            $query->whereYear('day', now()->year);
        }

        $invoices = $query->latest()->paginate(10)->appends($request->all());

        return Inertia::render('Sale/viewInvoice', [
            'from' => 'Sale',
            'url'=>'salePrint',
            'url2'=>'saleReturn',
            'invoices' => $invoices,
            'filters' => $request->only(['start_date', 'end_date', 'search'])
        ]);
    }

    public function viewSaleReturn(Request $request)
    {
        $query = Counter::where('inv_status', 'Credit_Note_Issued')->with('user:id');

        if ($request->search) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        // Apply date filter if provided
        if (!empty($request->start_date) && !empty($request->end_date)) 
        {
            $query->whereBetween('day', [$request->start_date, $request->end_date]);
        }
        else 
        {
            $query->whereYear('day', now()->year);
        }

        $invoices = $query->latest()->paginate(10)->appends($request->all());

        return Inertia::render('InvoiceReturn/viewInvoice', [
            'from' => 'Sale',
            'url'=>'salePrint',
            'url2'=>'saleReturn',
            'invoices' => $invoices,
            'filters' => $request->only(['start_date', 'end_date', 'search'])
        ]);
    }

    public function pInvoice(Request $request)
    {
        $query = PCounter::with('user:id');

        if ($request->search) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        // Apply date filter if provided
        if (!empty($request->start_date) && !empty($request->end_date)) 
        {
            $query->whereBetween('day', [$request->start_date, $request->end_date]);
        }
        else 
        {
            $query->whereYear('day', now()->year);
        }

        $invoices = $query->latest()->paginate(10)->appends($request->all());

        return Inertia::render('Sale/viewInvoice', [
            'from' => 'Purchase',
            'url' => 'purPrint',
            'url2' => 'purchaseReturn',
            'invoices' => $invoices,
            'filters' => $request->only(['start_date', 'end_date', 'search'])
        ]);
    }

    public function viewPurchaseReturn(Request $request)
    {
        $query = PCounter::where('inv_status', 'Debit_Note_Issued')->with('user:id');

        if ($request->search) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        if (!empty($request->start_date) && !empty($request->end_date)) 
        {
            $query->whereBetween('day', [$request->start_date, $request->end_date]);
        }
        else 
        {
            $query->whereYear('day', now()->year);
        }

        $invoices = $query->latest()->paginate(10)->appends($request->all());

        return Inertia::render('InvoiceReturn/viewInvoice', [
            'from' => 'Purchase',
            'url' => 'purPrint',
            'url2' => 'purchaseReturn',
            'invoices' => $invoices,
            'filters' => $request->only(['start_date', 'end_date', 'search'])
        ]);
    }

    public function getSInvoice(Request $request)
    {
        $query = Counter::with('user:id');

        if($request->from == 'Purchase')
        {
            $query = PCounter::with('user:id');
        }

        // Apply date filter if provided
        if (!empty($request->start_date) && !empty($request->end_date)) 
        {
            $query->whereBetween('day', [$request->start_date, $request->end_date]);
        }
        else 
        {
            $query->whereYear('day', now()->year);
        }

        // Fetch latest invoices
        $invoices = $query->latest()->get();

        return response()->json(['data' => $invoices]);
    }


    ///////// working on converting sale invoice to vue
    public function createSale()
    {
        $accounts = Account::where(['type'=>'Customer','status'=>'1'])->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return Inertia::render('Sale/oldSale', [
            'customers' => $accounts,
            'products' => $products,
        ]);
    }

    public function getRegisterDetails()
    {
        $userId = Auth::id();
        $date = Carbon::today()->toDateString();
        
        // 1. Total Sales (Gross Sales from Transactions)
        $totalSales = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->where('type', 'SV')
            ->sum('amount');

        // 2. Refunds
        $totalRefund = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->where('type', 'SRV')
            ->sum('amount');

        // 3. Expenses (Cash Out)
        $totalExpense = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->where('type', 'CO')
            ->sum('amount');

        // 4. Received Payment (Cash/Card In)
        // We get this from Ledger where we CREDITED the Customer (Customer Paid) linked to SV transactions
        $totalReceived = Ledger::whereDate('day', $date)
            ->where('type', 'SV')
            ->whereHas('transaction', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->sum('cr');
            
        // 5. Credit Sales (Sales made - Amount Received)
        // If Total Sales > Total Received, the difference is Credit
        $creditSales = max(0, $totalSales - $totalReceived);
        
        // 6. Products Sold Details
        $productsSold = Sale::whereDate('sales.day', $date)
            ->where('sales.type', 'Sale')
            ->join('counters', 'sales.sale_no', '=', 'counters.id')
            ->where('counters.user_id', $userId)
            ->join('products', 'sales.barcode', '=', 'products.barcode')
            ->select('products.name', DB::raw('sum(sales.qty) as qty'), DB::raw('sum(sales.final) as total'))
            ->groupBy('products.name')
            ->get();
            
        // 7. Brand Details
        $brandsSold = Sale::whereDate('sales.day', $date)
            ->where('sales.type', 'Sale')
            ->join('counters', 'sales.sale_no', '=', 'counters.id')
            ->where('counters.user_id', $userId)
            ->join('products', 'sales.barcode', '=', 'products.barcode')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select(DB::raw('COALESCE(brands.name, "Unbranded") as name'), DB::raw('sum(sales.qty) as qty'), DB::raw('sum(sales.final) as total'))
            ->groupBy('brands.name')
            ->get();
            
        // 8. Start Time (First transaction of the day)
        $firstTrans = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->first();

        // 9. Open Register (First transaction of the day)
        $openRegister = Register::whereDate('opening_time', $date)
            ->select('opening_amount', 'opening_time', 'status')
            ->where('user_id', $userId)
            ->first();
            
        // $startTime = $firstTrans ? $firstTrans->created_at->toDateTimeString() : Carbon::now()->startOfDay()->toDateTimeString();
        // $startTime = $firstTrans ? $firstTrans->created_at->format('m/d/Y h:i:s A') : Carbon::now()->startOfDay()->format('m/d/Y h:i:s A');
            $startTime = $openRegister->opening_time;

        return response()->json([
            'start_time' => $startTime,
            'end_time' => Carbon::now()->toDateTimeString(),
            'total_sales' => round($totalSales, 2),
            'total_refund' => round($totalRefund, 2),
            'total_payment' => round($totalReceived, 2),
            'credit_sales' => round($creditSales, 2),
            'total_expense' => round($totalExpense, 2),
            'total_cash' => round($totalReceived - $totalExpense - $totalRefund, 2), // Simplistic cash drawer calc
            'total_card_slips' => 0, // Mock for now as backend distinction missing
            'total_cheques' => 0,    // Mock
            'payment_methods' => [
                ['method' => 'Cash', 'sell' => round($totalReceived, 2), 'expense' => round($totalExpense, 2)],
                ['method' => 'Card', 'sell' => 0, 'expense' => 0],
                ['method' => 'Cheque', 'sell' => 0, 'expense' => 0],
            ],
            'product_details' => $productsSold,
            'brand_details' => $brandsSold,
             'user' => [
                'name' => Auth::user()->name ?? 'Unknown',
                'email' => Auth::user()->email ?? '',
                'location' => 'Main Store' 
            ]
        ]);
    }

    public function checkRegisterStatus()
    {
        $userId = Auth::id();
        $date = Carbon::today()->toDateString();
    
        // 1. Open Register (First transaction of the day)
        $openRegister = Register::whereDate('opening_time', $date)
            ->select('opening_amount', 'opening_time', 'status')
            ->where('user_id', $userId)
            ->first();
        
        if($openRegister){
            return response()->json(['success' => true, 'status' => $openRegister->status]);
        }

        return response()->json(['success' => true, 'status' => '']);
    }

    public function insertRegister(Request $request)
    {
        $register = Register::create([
            'user_id' => Auth::id(),
            'opening_time' => Carbon::now()->toDateTimeString(),
            'opening_amount' => $request->amount,
            'status' => 'open',
        ]);

        return response()->json(['success' => true, 'id' => $register->id]);
    }

    public function updateRegister(Request $request)
    {
        $userId = Auth::id();
        $date = Carbon::today()->toDateString();
        
        // 1. Total Sales (Gross Sales from Transactions)
        $totalSales = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->where('type', 'SV')
            ->sum('amount');

        // 2. Refunds
        $totalRefund = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->where('type', 'SRV')
            ->sum('amount');

        // 3. Expenses (Cash Out)
        $totalExpense = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->where('type', 'CO')
            ->sum('amount');

        // 4. Received Payment (Cash/Card In)
        $totalReceived = Ledger::whereDate('day', $date)
            ->where('type', 'SV')
            ->whereHas('transaction', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->sum('cr');
            
        // 5. Credit Sales
        $creditSales = max(0, $totalSales - $totalReceived);
            
        // 6. Start Time
        $firstTrans = Tran::whereDate('day', $date)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->first();
            
        // $startTime = $firstTrans ? $firstTrans->created_at : Carbon::now()->startOfDay();

        $register = Register::whereDate('opening_time', $date)
            ->where('user_id', $userId)
            ->first();

        if (!$register) {
            return response()->json(['success' => false, 'message' => 'Register not found'], 404);
        }

        $register->update([
            'closing_time' => Carbon::now(),
            'total_sales' => $totalSales,
            'total_refund' => $totalRefund,
            'total_payment' => $totalReceived,
            'total_expense' => $totalExpense,
            'credit_sales' => $creditSales,
            'cash_in_hand' => $totalReceived - $totalExpense - $totalRefund,
            'closing_note' => $request->closing_note,
            'status' => 'closed',
        ]);

        return response()->json(['success' => true, 'id' => $register->id]);
    }

    public function getRecentInvoiceDetails()
    {
        $date = Carbon::today()->toDateString();

        $invoices = Counter::with('account:id,name')
            ->whereDate('day', $date)
            ->where('inv_status', '!=', 'Return')
            ->select('id', 'cust_id', 'finalVal', 'inv_status')
            ->orderBy('id', 'desc')
            ->get();
        
        return response()->json([
            'invoices' => $invoices,
        ]);
    }

    public function deleteInvoice($id)
    {
        $invoice = Counter::find($id);
        if ($invoice) {
            Sale::where('sale_no', $id)->delete();
            $tran = Tran::where('invoice_id', $id)->first();
            Ledger::where('trans_id', $tran->id)->delete();
            $tran->delete();
            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully',
            ]);
        }
    }

    public function customerDisplay()
    {
        return Inertia::render('Sale/CustomerSaleDisplay');
    }
}
