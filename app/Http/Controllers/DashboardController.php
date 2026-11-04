<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Account;
use App\Models\Manufactureds;
use App\Models\Sale;
use App\Models\Counter;
use App\Models\Ledger;
use App\Models\Purchase;
use App\Models\PCounter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get date ranges
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // 1. Sales Statistics (Single Query)
        $salesStats = Sale::selectRaw("
            COALESCE(SUM(CASE WHEN DATE(day) = ? THEN final ELSE 0 END), 0) as today_sales,
            COALESCE(SUM(CASE WHEN DATE(day) >= ? THEN final ELSE 0 END), 0) as month_sales,
            COALESCE(SUM(CASE WHEN day BETWEEN ? AND ? THEN final ELSE 0 END), 0) as last_month_sales,
            COALESCE(SUM(final), 0) as total_sales
        ", [
            $today->toDateString(), 
            $thisMonth->toDateString(), 
            $lastMonth->toDateString(), 
            $lastMonthEnd->toDateString()
        ])->first();

        // Calculate sales growth
        $salesGrowth = $salesStats->last_month_sales > 0 
            ? round((($salesStats->month_sales - $salesStats->last_month_sales) / $salesStats->last_month_sales) * 100, 1)
            : 0;

        // 2. Purchase Statistics (Single Query)
        $purchaseStats = Purchase::selectRaw("
            COALESCE(SUM(CASE WHEN DATE(day) = ? THEN final ELSE 0 END), 0) as today_purchases,
            COALESCE(SUM(CASE WHEN DATE(day) >= ? THEN final ELSE 0 END), 0) as month_purchases,
            COALESCE(SUM(CASE WHEN day BETWEEN ? AND ? THEN final ELSE 0 END), 0) as last_month_purchases,
            COALESCE(SUM(final), 0) as total_purchases
        ", [
            $today->toDateString(), 
            $thisMonth->toDateString(), 
            $lastMonth->toDateString(), 
            $lastMonthEnd->toDateString()
        ])->first();

        // Calculate purchase growth
        $purchaseGrowth = $purchaseStats->last_month_purchases > 0 
            ? round((($purchaseStats->month_purchases - $purchaseStats->last_month_purchases) / $purchaseStats->last_month_purchases) * 100, 1)
            : 0;

        // 3. Product Statistics (Single Query)
        $productStats = Product::selectRaw("
            COUNT(CASE WHEN type = 'category' THEN 1 END) as total,
            COUNT(CASE WHEN qty < min_qty THEN 1 END) as low_stock,
            COUNT(CASE WHEN qty <= 0 THEN 1 END) as out_of_stock
        ")->first();

        // 4. Customer/Vendor Statistics (Single Query)
        $accountStats = Account::selectRaw("
            COUNT(CASE WHEN type = 'customer' THEN 1 END) as customers,
            COUNT(CASE WHEN type = 'vendor' THEN 1 END) as vendors
        ")->first();

        // 5. Manufacturing Statistics (Single Query)
        $manufacturingStats = Manufactureds::selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN DATE(day) >= ? THEN 1 END) as this_month
        ", [$thisMonth->toDateString()])->first();

        // 6. Cash Flow (Single Query with Join)
        // Optimizes 6 separate queries + fetching IDs into one efficient join aggregation
        $cashFlowStats = Ledger::join('accounts', 'ledgers.account_id', '=', 'accounts.id')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN accounts.type = 'customer' THEN dr ELSE 0 END), 0) as customer_dr,
                COALESCE(SUM(CASE WHEN accounts.type = 'customer' THEN cr ELSE 0 END), 0) as customer_cr,
                COALESCE(SUM(CASE WHEN accounts.type = 'vendor' THEN dr ELSE 0 END), 0) as vendor_dr,
                COALESCE(SUM(CASE WHEN accounts.type = 'vendor' THEN cr ELSE 0 END), 0) as vendor_cr
            ")->first();

        $totalReceivables = $cashFlowStats->customer_dr - $cashFlowStats->customer_cr;
        $totalPayables = $cashFlowStats->vendor_cr - $cashFlowStats->vendor_dr;

        // Recent Purchases (Last 7 days)
        $recentPurchases = Purchase::selectRaw('DATE(day) as date, COUNT(*) as count, SUM(final) as total')
            ->whereDate('day', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Top Purchased Products (This Month)
        $topPurchasedProducts = Purchase::with('product:id,name,barcode')
            ->selectRaw('barcode, SUM(qty) as total_qty, SUM(qty * price) as total_purchases')
            ->whereDate('day', '>=', $thisMonth)
            ->groupBy('barcode')
            ->orderByDesc('total_purchases')
            ->limit(5)
            ->get()
            ->map(function ($purchase) {
                return [
                    'name' => $purchase->product->name ?? 'Unknown Product',
                    'total_qty' => $purchase->total_qty,
                    'total_purchases' => $purchase->total_purchases,
                ];
            });

        // Recent Purchase Invoices
        $recentPurchaseInvoices = PCounter::with('account:id,name')
            ->select('id', 'day', 'finalVal', 'cust_id')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($pcounter) {
                return [
                    'id' => $pcounter->id,
                    'day' => $pcounter->day,
                    'finalVal' => $pcounter->finalVal,
                    'vendor_name' => $pcounter->account->name ?? 'Unknown Vendor',
                ];
            });

        // Recent Sales (Last 7 days)
        $recentSales = Sale::selectRaw('DATE(day) as date, COUNT(*) as count, SUM(final) as total')
            ->whereDate('day', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Top Selling Products (This Month)
        $topProducts = Sale::with('product:id,name,barcode')
            ->selectRaw('barcode, SUM(qty) as total_qty, SUM(qty * price) as total_sales')
            ->whereDate('day', '>=', $thisMonth)
            ->groupBy('barcode')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'name' => $sale->product->name ?? 'Unknown Product',
                    'total_qty' => $sale->total_qty,
                    'total_sales' => $sale->total_sales,
                ];
            });

        // Recent Invoices
        $recentInvoices = Counter::with('account:id,name')
            ->select('id', 'day', 'finalVal', 'order_type', 'cust_id')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($counter) {
                return [
                    'id' => $counter->id,
                    'day' => $counter->day,
                    'finalVal' => $counter->finalVal,
                    'order_type' => $counter->order_type,
                    'customer_name' => $counter->account->name ?? 'Walk-in Customer',
                ];
            });

        // Low Stock Alert
        $lowStockItems = Product::whereColumn('qty', '<', 'min_qty')
            ->where('qty', '>', 0)
            ->select('id', 'name', 'barcode', 'qty', 'min_qty')
            ->orderBy('qty', 'asc')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'sales' => [
                    'today' => $salesStats->today_sales,
                    'month' => $salesStats->month_sales,
                    'total' => $salesStats->total_sales,
                    'growth' => $salesGrowth,
                ],
                'purchases' => [
                    'today' => $purchaseStats->today_purchases,
                    'month' => $purchaseStats->month_purchases,
                    'total' => $purchaseStats->total_purchases,
                    'growth' => $purchaseGrowth,
                ],
                'products' => [
                    'total' => $productStats->total,
                    'lowStock' => $productStats->low_stock,
                    'outOfStock' => $productStats->out_of_stock,
                ],
                'customers' => [
                    'total' => $accountStats->customers,
                    'vendors' => $accountStats->vendors,
                ],
                'manufacturing' => [
                    'total' => $manufacturingStats->total,
                    'thisMonth' => $manufacturingStats->this_month,
                ],
                'cashFlow' => [
                    'receivables' => $totalReceivables ?? 0,
                    'payables' => $totalPayables ?? 0,
                ],
            ],
            'charts' => [
                'recentSales' => $recentSales,
                'recentPurchases' => $recentPurchases,
                'topProducts' => $topProducts,
                'topPurchasedProducts' => $topPurchasedProducts,
            ],
            'recentInvoices' => $recentInvoices,
            'recentPurchaseInvoices' => $recentPurchaseInvoices,
            'lowStockItems' => $lowStockItems,
        ]);
    }
}
