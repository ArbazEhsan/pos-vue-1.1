<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tran;
use App\Models\Ledger;
use App\Models\Account;
use App\Models\Counter;
use App\Models\PCounter;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Stock;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function cashinReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
    	// return view('report.cashinReport')->with(['company'=>$result,'uname'=>$uname]);
        return Inertia::render('Report/Cashin', [
            'companies' => $result,
            'username' => $uname,
        ]);
    }

    public function cashoutReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
    	return view('report.cashoutReport')->with(['company'=>$result,'uname'=>$uname]);
    }

    public function receivableReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
    	return view('report.receivableReport')->with(['company'=>$result,'uname'=>$uname]);
    }

    public function dayendReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
        return view('report.dayendReport')->with(['company'=>$result,'uname'=>$uname]);
    }

    public function dayendDetailReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
        return view('report.dayendDetailReport')->with(['company'=>$result,'uname'=>$uname]);
    }

    public function payableReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
        return view('report.payableReport')->with(['company'=>$result,'uname'=>$uname]);
    }

    public function stockReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
        return view('report.stockReport')->with(['company'=>$result,'uname'=>$uname]);
    }

    public function stockTrack()
    {   
        $uname = Auth::User()->name;
        $result = Stock::join('products','products.id','=','stocks.barcode')
            ->join('users','users.id','=','stocks.user_id')
            ->select('products.name','users.name as u_name','stocks.*')
            ->paginate(20);
        $result2 = Company::pluck('name');
        return view('report.stockTrackingReport')->with(['product'=>$result,'company'=>$result2,'uname'=>$uname]);
    }
    
    public function palReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
        return view('report.palReport')->with(['company'=>$result,'uname'=>$uname]);
    }
    
    public function getCashinReport(Request $request)
    {
    	$from = $request->from_;
    	$to = $request->to;

        $query = Tran::whereNotIn('type', ['PV', 'CO'])
            ->with('account');

    	if(date('Y', strtotime($from))=='1970' && date('Y', strtotime($to))=='1970'){
            // No date filter
    	}
    	else {
    		$query->whereBetween('day',[$from,$to]);
            $from = date("d/m/Y", strtotime($from));
            $to = date("d/m/Y", strtotime($to));
    	}
    	
        $result = $query->paginate(50)->appends($request->all());

        $uname = Auth::User()->name;
        $company = Company::pluck('name');
        
        return Inertia::render('Report/Cashin', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function getCashoutReport(Request $request)
    {
    	$from = $request->from_;
    	$to = $request->to;

        $query = Tran::whereNotIn('type', ['SV', 'CR'])
            ->with('account');

    	if(date('Y', strtotime($from))=='1970' && date('Y', strtotime($to))=='1970'){
            // No date filter
    	}
    	else {
    		$query->whereBetween('day',[$from,$to]);
            $from = date("d/m/Y", strtotime($from));
            $to = date("d/m/Y", strtotime($to));
    	}
    	
        $result = $query->paginate(50)->appends($request->all());

        $uname = Auth::User()->name;
        $company = Company::pluck('name');
        
        return Inertia::render('Report/Cashout', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function getReceivableReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;
        
        // Build the base query with eager loading
        $query = Account::query()->with(['ledgers' => function($q) use ($from, $to) {
            if(date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970') {
                $q->whereBetween('day', [$from, $to]);
            }
        }]);

        // Format dates
        if(date('Y', strtotime($from))=='1970' && date('Y', strtotime($to))=='1970'){
            $formattedFrom = "All";
            $formattedTo = "All";
        } else {
            $formattedFrom = date("d/m/Y", strtotime($from));
            $formattedTo = date("d/m/Y", strtotime($to));
        }

        // Use withSum for efficient aggregation
        if(date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970') {
            $query->withSum(['ledgers as dr_sum' => function($q) use ($from, $to) {
                $q->whereBetween('day', [$from, $to]);
            }], 'dr')
            ->withSum(['ledgers as cr_sum' => function($q) use ($from, $to) {
                $q->whereBetween('day', [$from, $to]);
            }], 'cr');
        } else {
            $query->withSum('ledgers as dr_sum', 'dr')
                  ->withSum('ledgers as cr_sum', 'cr');
        }

        // Get paginated results
        $result = $query->paginate(50)->appends($request->all());

        // Transform and filter for receivables (Dr > Cr)
        $result->getCollection()->transform(function ($account) {
            $dr = $account->dr_sum ?? 0;
            $cr = $account->cr_sum ?? 0;
            $bal = $dr - $cr;
            
            return [
                'title' => $account->name,
                'bal' => $bal,
                'is_receivable' => $bal > 0
            ];
        })->filter(function($item) {
            return $item['is_receivable'];
        })->values();

        $uname = Auth::User()->name;
        $company = Company::pluck('name');

        return Inertia::render('Report/Receivable', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $formattedFrom,
            'to' => $formattedTo,
        ]);
    }

    public function getPayableReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;
        
        // Build the base query with eager loading
        $query = Account::query()->with(['ledgers' => function($q) use ($from, $to) {
            if(date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970') {
                $q->whereBetween('day', [$from, $to]);
            }
        }]);

        // Format dates
        if(date('Y', strtotime($from))=='1970' && date('Y', strtotime($to))=='1970'){
            $formattedFrom = "All";
            $formattedTo = "All";
        } else {
            $formattedFrom = date("d/m/Y", strtotime($from));
            $formattedTo = date("d/m/Y", strtotime($to));
        }

        // Use withSum for efficient aggregation
        if(date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970') {
            $query->withSum(['ledgers as dr_sum' => function($q) use ($from, $to) {
                $q->whereBetween('day', [$from, $to]);
            }], 'dr')
            ->withSum(['ledgers as cr_sum' => function($q) use ($from, $to) {
                $q->whereBetween('day', [$from, $to]);
            }], 'cr');
        } else {
            $query->withSum('ledgers as dr_sum', 'dr')
                  ->withSum('ledgers as cr_sum', 'cr');
        }

        // Get paginated results
        $result = $query->paginate(50)->appends($request->all());

        // Transform and filter for payables (Cr > Dr)
        $result->getCollection()->transform(function ($account) {
            $dr = $account->dr_sum ?? 0;
            $cr = $account->cr_sum ?? 0;
            $bal = $cr - $dr;
            
            return [
                'title' => $account->name,
                'bal' => $bal,
                'is_payable' => $bal > 0
            ];
        })->filter(function($item) {
            return $item['is_payable'];
        })->values();

        $uname = Auth::User()->name;
        $company = Company::pluck('name');

        return Inertia::render('Report/Payable', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $formattedFrom,
            'to' => $formattedTo,
        ]);
    }

    
    public function getStockReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;

        // Format dates
        if(date('Y', strtotime($from))=='1970' && date('Y', strtotime($to))=='1970'){
            $formattedFrom = "All";
            $formattedTo = "All";
        } else {
            $formattedFrom = date("d/m/Y", strtotime($from));
            $formattedTo = date("d/m/Y", strtotime($to));
        }

        $soldQuery = Sale::query()
            ->select('barcode', DB::raw('SUM(qty) as sold'))
            ->groupBy('barcode');

        if (date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970') {
            $soldQuery->whereBetween('day', [$from, $to]);
        }

        $soldByBarcode = $soldQuery->pluck('sold', 'barcode');

        $products = Product::query()
            ->with(['category', 'variations'])
            ->orderBy('name')
            ->get();

        $rows = collect();

        foreach ($products as $product) {
            $catName = $product->category->name ?? 'N/A';

            if ($product->variations->isNotEmpty()) {
                foreach ($product->variations->sortBy('var_name', SORT_NATURAL) as $variation) {
                    $variationLabel = $product->variation_name && $product->variation_name !== 'Single'
                        ? $product->variation_name . ': ' . $variation->var_name
                        : $variation->var_name;

                    $rows->push([
                        'cat_name' => $catName,
                        'pro_name' => $product->name . ' (' . $variationLabel . ')',
                        'sold' => (float) ($soldByBarcode[$variation->var_sku] ?? 0),
                        'remain' => $variation->var_qty,
                    ]);
                }
            } else {
                $rows->push([
                    'cat_name' => $catName,
                    'pro_name' => $product->name,
                    'sold' => (float) ($soldByBarcode[$product->barcode] ?? 0),
                    'remain' => $product->qty,
                ]);
            }
        }

        $perPage = 50;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $result = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $uname = Auth::User()->name;
        $company = Company::pluck('name');

        return Inertia::render('Report/Stock', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $formattedFrom,
            'to' => $formattedTo,
        ]);
    }

    public function getStockTrackReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;

        // Format dates
        if(date('Y', strtotime($from))=='1970' && date('Y', strtotime($to))=='1970'){
            $formattedFrom = "All";
            $formattedTo = "All";
        } else {
            $formattedFrom = date("d/m/Y", strtotime($from));
            $formattedTo = date("d/m/Y", strtotime($to));
        }

        // Build optimized query using model relationships
        $query = Stock::query()
            ->with(['product', 'user']); // Eager load relationships

        // Apply date filter if provided
        if(date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970') {
            $query->whereBetween('day', [$from, $to]);
        }

        // Get paginated results
        $result = $query->orderBy('day', 'desc')
            ->paginate(50)
            ->appends($request->all());

        // Transform the data to include related information
        $result->getCollection()->transform(function ($stock) {
            return [
                'day' => $stock->day,
                'product_name' => $stock->product->name ?? 'N/A',
                'user_name' => $stock->user->name ?? 'N/A',
                'qty' => $stock->qty,
            ];
        });

        $uname = Auth::User()->name;
        $company = Company::pluck('name');

        return Inertia::render('Report/StockTracking', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $formattedFrom,
            'to' => $formattedTo,
        ]);
    }
    
    public function getDayendReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;
        
        $sale = 0;
        $cashin = 0;
        $cashout = 0;
        $credit = 0;

        $hasDates = date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970';

        if ($hasDates) {
            // Optimize Tran queries into one
            $tranSums = Tran::whereBetween('day', [$from, $to])
                ->whereIn('type', ['SV', 'CR', 'CO'])
                ->selectRaw('type, sum(amount) as total')
                ->groupBy('type')
                ->pluck('total', 'type');

            $sale = $tranSums['SV'] ?? 0;
            $cashin = $tranSums['CR'] ?? 0;
            $cashout = $tranSums['CO'] ?? 0;

            // Credit query
            $credit = Counter::whereBetween('day', [$from, $to])
                ->sum('remaining');

            $formattedFrom = date("d/m/Y", strtotime($from));
            $formattedTo = date("d/m/Y", strtotime($to));
        } else {
            $formattedFrom = "";
            $formattedTo = "";
        }
        
        $data = [[
            'sale' => $sale,
            'cashin' => $cashin,
            'credit' => $credit,
            'cashout' => $cashout,
        ]];

        $uname = Auth::User()->name;
        $company = Company::pluck('name');

        return Inertia::render('Report/DayEnd', [
            'data' => $data,
            'username' => $uname,
            'company' => $company,
            'from' => $formattedFrom,
            'to' => $formattedTo,
        ]);
    }

    public function getDayendDetailReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;
        
        $hasDates = date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970';

        if ($hasDates) {
            // Sales
            $sales = Sale::with(['counter.account'])
                ->whereBetween('day', [$from, $to])
                ->paginate(20, ['*'], 'page_sales')
                ->appends($request->all());

            $sales->getCollection()->transform(function ($sale) {
                return [
                    'day' => $sale->day,
                    'sale_no' => $sale->sale_no,
                    'barcode' => $sale->barcode,
                    'name' => $sale->counter->account->name ?? 'N/A',
                    'ref' => $sale->counter->ref ?? '',
                    'finalVal' => $sale->counter->finalVal ?? 0,
                    'received' => $sale->counter->received ?? 0,
                    'remaining' => $sale->counter->remaining ?? 0,
                ];
            });

            // Purchases
            $purchases = Purchase::with(['pCounter.account'])
                ->whereHas('pCounter', function($q) use ($from, $to) {
                    $q->whereBetween('day', [$from, $to]);
                })
                ->paginate(20, ['*'], 'page_purchases')
                ->appends($request->all());

            $purchases->getCollection()->transform(function ($pur) {
                return [
                    'day' => $pur->pCounter->day ?? '',
                    'pur_no' => $pur->pur_no,
                    'barcode' => $pur->barcode,
                    'name' => $pur->pCounter->account->name ?? 'N/A',
                    'ref' => $pur->pCounter->ref ?? '',
                    'finalVal' => $pur->pCounter->finalVal ?? 0,
                    'received' => $pur->pCounter->received ?? 0,
                    'remaining' => $pur->pCounter->remaining ?? 0,
                ];
            });

            // Cash In
            $cashIn = Tran::with('account')
                ->where('type', 'CR')
                ->whereBetween('day', [$from, $to])
                ->orderBy('day', 'desc')
                ->paginate(20, ['*'], 'page_cashin')
                ->appends($request->all());
            
            $cashIn->getCollection()->transform(function ($tran) {
                return [
                    'day' => $tran->day,
                    'id' => $tran->id,
                    'name' => $tran->account->name ?? 'N/A',
                    'remarks' => $tran->remarks,
                    'amount' => $tran->amount,
                ];
            });

            // Cash Out
            $cashOut = Tran::with('account')
                ->where('type', 'CO')
                ->whereBetween('day', [$from, $to])
                ->orderBy('day', 'desc')
                ->paginate(20, ['*'], 'page_cashout')
                ->appends($request->all());

            $cashOut->getCollection()->transform(function ($tran) {
                return [
                    'day' => $tran->day,
                    'id' => $tran->id,
                    'name' => $tran->account->name ?? 'N/A',
                    'remarks' => $tran->remarks,
                    'amount' => $tran->amount,
                ];
            });

            $formattedFrom = date("d/m/Y", strtotime($from));
            $formattedTo = date("d/m/Y", strtotime($to));

        } else {
            $sales = [];
            $purchases = [];
            $cashIn = [];
            $cashOut = [];
            $formattedFrom = "";
            $formattedTo = "";
        }

        $uname = Auth::User()->name;
        $company = Company::pluck('name');

        return Inertia::render('Report/DayEndDetail', [
            'data' => $sales,
            'data2' => $purchases,
            'data3' => $cashIn,
            'data4' => $cashOut,
            'username' => $uname,
            'company' => $company,
            'from' => $formattedFrom,
            'to' => $formattedTo,
        ]);
    }

    public function getpalReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;

        // Check if we should return the view or the data
        $shouldFetchData = $request->has('fetch_data');

        if (!$shouldFetchData) {
            $uname = Auth::User()->name;
            $company = Company::pluck('name');

            if(date('Y', strtotime($from))=='1970' && date('Y', strtotime($to))=='1970'){
                $formattedFrom = "All";
                $formattedTo = "All";
            } else {
                $formattedFrom = date("d/m/Y", strtotime($from));
                $formattedTo = date("d/m/Y", strtotime($to));
            }

            return Inertia::render('Report/ProfitLoss', [
                'data' => [],
                'data2' => [],
                'data3' => [],
                'username' => $uname,
                'company' => $company,
                'from' => $formattedFrom,
                'to' => $formattedTo,
                'total_sale_qty' => 0,
                'total_sale_final' => 0,
                'total_pur_qty' => 0,
                'total_pur_final' => 0,
                'gross_profit' => 0,
                'operating_expense' => 0,
                'operating_income' => 0,
                'should_fetch_data' => true, // Signal to frontend to fetch data
                'filters' => $request->all(), // Pass filters back so frontend knows what to query
            ]);
        }
        
        $hasDates = date('Y', strtotime($from)) != '1970' && date('Y', strtotime($to)) != '1970';
        $step = $request->step;

        if ($step === 'sales') {
            $query = $this->palLinesQuery('sales')
                ->select(
                    'cat_name',
                    'pro_name',
                    DB::raw('SUM(qty) as total_qty'),
                    DB::raw('SUM(final) as total_amount')
                );

            if ($hasDates) {
                $query->whereBetween('day', [$from, $to]);
            }

            $results = $query->groupBy('cat_name', 'pro_name')->get();
            
            
            // Group results in PHP to match expected structure
            $grouped = $results->groupBy('cat_name');
            $data = [];
            $totalSQty = 0;
            $totalSFinal = 0;

            foreach ($grouped as $catName => $items) {
                $catQty = $items->sum('total_qty');
                $catAmnt = $items->sum('total_amount');
                
                $totalSQty += $catQty;
                $totalSFinal += $catAmnt;

                $saleProds = $items->map(function($item) {
                    return [
                        'pro_name' => $item->pro_name,
                        'qty' => $item->total_qty,
                        'amount' => $item->total_amount,
                    ];
                });

                $data[] = [
                    'cat_name' => $catName,
                    'products' => $saleProds,
                    'total_qty' => $catQty,
                    'total_amount' => $catAmnt
                ];
            }
            return response()->json([
                'data' => $data,
                'total_sale_qty' => $totalSQty,
                'total_sale_final' => $totalSFinal,
            ]);
        }

        if ($step === 'purchases') {
            $query = $this->palLinesQuery('purchases')
                ->select(
                    'cat_name',
                    'pro_name',
                    DB::raw('SUM(qty) as total_qty'),
                    DB::raw('SUM(final) as total_amount')
                );

            if ($hasDates) {
                $query->whereBetween('day', [$from, $to]);
            }

            $results = $query->groupBy('cat_name', 'pro_name')->get();
            
            $grouped = $results->groupBy('cat_name');

            $data2 = [];
            $totalPQty = 0;
            $totalPFinal = 0;

            foreach ($grouped as $catName => $items) {
                $catQty = $items->sum('total_qty');
                $catAmnt = $items->sum('total_amount');
                
                $totalPQty += $catQty;
                $totalPFinal += $catAmnt;

                $purProds = $items->map(function($item) {
                    return [
                        'pro_name' => $item->pro_name,
                        'qty' => $item->total_qty,
                        'amount' => $item->total_amount,
                    ];
                });

                $data2[] = [
                    'cat_name' => $catName,
                    'products' => $purProds,
                    'total_qty' => $catQty,
                    'total_amount' => $catAmnt
                ];
            }

            return response()->json([
                'data2' => $data2,
                'total_pur_qty' => $totalPQty,
                'total_pur_final' => $totalPFinal,
            ]);
        }

        if ($step === 'expenses') {
             // Expenses
            $query = DB::table('trans')
                ->join('accounts', 'trans.account_id', '=', 'accounts.id')
                ->where('accounts.type', 'Expense')
                ->select(
                    'accounts.name as pro_name',
                    DB::raw('SUM(trans.amount) as tran_sum_amnt')
                );
            
            if ($hasDates) {
                $query->whereBetween('trans.day', [$from, $to]);
            }

            $expenses = $query->groupBy('accounts.name')->get();

            $data3 = $expenses->map(function($item) {
                return [
                    'pro_name' => $item->pro_name,
                    'tran_sum_amnt' => $item->tran_sum_amnt ?? 0,
                ];
            });

            $totalTFinal = $expenses->sum('tran_sum_amnt');

            return response()->json([
                'data3' => $data3,
                'operating_expense' => $totalTFinal,
            ]);
        }
        
        return response()->json(['error' => 'Invalid step'], 400);
    }

    /**
     * Resolve sale/purchase lines for both single products (barcode)
     * and variations (var_sku stored in the line barcode column).
     */
    private function palLinesQuery(string $table)
    {
        $inner = DB::table($table)
            ->leftJoin('products as p_direct', $table . '.barcode', '=', 'p_direct.barcode')
            ->leftJoin('product_variations', $table . '.barcode', '=', 'product_variations.var_sku')
            ->leftJoin('products as p_var', 'product_variations.product_id', '=', 'p_var.id')
            ->leftJoin('categories as c_direct', 'p_direct.category_id', '=', 'c_direct.id')
            ->leftJoin('categories as c_var', 'p_var.category_id', '=', 'c_var.id')
            ->whereRaw('COALESCE(p_var.id, p_direct.id) IS NOT NULL')
            ->whereRaw("COALESCE(c_var.name, c_direct.name) != 'Stock'")
            ->select(
                DB::raw($this->palCategoryNameSql() . ' as cat_name'),
                DB::raw($this->palProductNameSql() . ' as pro_name'),
                $table . '.qty',
                $table . '.final',
                $table . '.day'
            );

        return DB::query()->fromSub($inner, 'pal_lines');
    }

    private function palCategoryNameSql(): string
    {
        return 'COALESCE(c_var.name, c_direct.name)';
    }

    private function palProductNameSql(): string
    {
        return "CASE
            WHEN product_variations.id IS NOT NULL THEN CONCAT(
                COALESCE(p_var.name, p_direct.name, ''),
                ' (',
                COALESCE(NULLIF(p_var.variation_name, ''), 'Variation'),
                ': ',
                product_variations.var_name,
                ')'
            )
            ELSE p_direct.name
        END";
    }

    public function productWiseSaleReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
        $products = Product::select('id', 'name')->get();
        return Inertia::render('Report/ProductWiseSale', [
            'company' => $result,
            'username' => $uname,
            'products' => $products
        ]);
    }

    public function getProductWiseSaleReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;
        $productId = $request->product_id;
        $uname = Auth::User()->name;
        $company = Company::pluck('name');
        $products = Product::select('id', 'name')->get();

        $query = Sale::with(['product', 'variation.product']);

        if ($productId) {
            $query->where(function ($q) use ($productId) {
                $q->whereHas('product', function ($sub) use ($productId) {
                    $sub->where('id', $productId);
                })->orWhereHas('variation.product', function ($sub) use ($productId) {
                    $sub->where('id', $productId);
                });
            });
        }

        if (date('Y', strtotime($from)) == '1970' && date('Y', strtotime($to)) == '1970') {
             $from = "All";
             $to = "All";
        } else {
            $query->whereBetween('day', [$from, $to]);
            $from = date("d/m/Y", strtotime($from));
            $to = date("d/m/Y", strtotime($to));
        }

        $result = $query->paginate(50)->appends($request->all());

        return Inertia::render('Report/ProductWiseSale', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'products' => $products,
            'filters' => $request->all(['from_', 'to', 'product_id']),
        ]);
    }

    public function brandWiseSaleReport()
    {
        $result = Company::pluck('name');
        $uname = Auth::User()->name;
        $brands = Brand::select('id', 'name')->get();
        return Inertia::render('Report/BrandWiseSale', [
            'company' => $result,
            'username' => $uname,
            'brands' => $brands
        ]);
    }

    public function getBrandWiseSaleReport(Request $request)
    {
        $from = $request->from_;
        $to = $request->to;
        $brandId = $request->brand_id;
        $uname = Auth::User()->name;
        $company = Company::pluck('name');
        $brands = Brand::select('id', 'name')->get();

        $query = Sale::with(['product.brand', 'variation.product.brand']);

        if ($brandId) {
            $query->where(function ($q) use ($brandId) {
                $q->whereHas('product', function ($sub) use ($brandId) {
                    $sub->where('brand_id', $brandId);
                })->orWhereHas('variation.product', function ($sub) use ($brandId) {
                    $sub->where('brand_id', $brandId);
                });
            });
        }

        if (date('Y', strtotime($from)) == '1970' && date('Y', strtotime($to)) == '1970') {
             $from = "All";
             $to = "All";
        } else {
            $query->whereBetween('day', [$from, $to]);
            $from = date("d/m/Y", strtotime($from));
            $to = date("d/m/Y", strtotime($to));
        }

        $result = $query->paginate(50)->appends($request->all());

        return Inertia::render('Report/BrandWiseSale', [
            'data' => $result,
            'username' => $uname,
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'brands' => $brands,
            'filters' => $request->all(['from_', 'to', 'brand_id']),
        ]);
    }
}
