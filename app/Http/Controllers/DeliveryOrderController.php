<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Counter;
use App\Models\DeliveryOrder;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DeliveryOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Counter::where('order_type', 'Delivery')
            ->select('id', 'day', 'cust_id', 'finalVal', 'gross', 'received', 'remaining', 'created_at')
            ->with(['deliveryOrder.rider', 'account:id,name,contact_person,address', 'sales.product'])
            ->latest('id');

        if ($request->filled('search')) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        // Apply date filter if provided
        if ($request->filled('start_date') && $request->filled('end_date')) 
        {
            $query->whereBetween('day', [$request->start_date, $request->end_date]);
        }
        else 
        {
            $query->whereYear('day', now()->year);
        }

        $deliveryOrders = $query->paginate(10)->appends($request->all());

        $riders = UserDetail::where('account_type', 'Rider')->get();
        
        return Inertia::render('Order/DeliveryOrder', [
            'deliveryOrders' => $deliveryOrders,
            'riders' => $riders
        ]);
    }

    public function getDeliveryOrderDetails(Request $request)
    {
        $query = Counter::where('order_type', 'Delivery')
                    ->select('id', 'day', 'cust_id', 'finalVal', 'gross', 'received', 'remaining', 'created_at')
                    ->with(['deliveryOrder.rider', 'account:id,name,contact_person,address', 'sales.product']);

        $deliveryOrders = $query->latest()->get();
        return response()->json($deliveryOrders);
    }


    public function statusDeliveryOrder(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $rider_id = $request->rider_id;

        $counter = Counter::findOrFail($id);
        
        $deliveryOrder = DeliveryOrder::firstOrNew(['counter_id' => $counter->id]);
        
        $deliveryOrder->status = $status;
        $deliveryOrder->rider_id = $rider_id;
        
        $now = Carbon::now();
        
        switch ($status) {
            case 'Preparing':
                $deliveryOrder->preparing_at = $now;
                break;
            case 'Transit':
                $deliveryOrder->transit_at = $now;
                break;
            case 'Delivered':
                $deliveryOrder->delivered_at = $now;
                break;
            case 'Canceled':
                $deliveryOrder->canceled_at = $now;
                break;
            case 'Return':
                $deliveryOrder->returned_at = $now;
                break;
        }
        
        if ($request->has('rider_id')) {
            $deliveryOrder->rider_id = $request->rider_id;
        }

        if ($request->has('notes')) {
            $deliveryOrder->notes = $request->notes;
        }

        if ($status == 'Transit') {
            $deliveryOrder->preparing_due_at = $now->diffInMinutes($counter->created_at, true);
        }

        if ($status == 'Delivered') {
            $deliveryOrder->delivery_due_at = $now->diffInMinutes($deliveryOrder->preparing_at, true);
        }
        
        $deliveryOrder->save();
        
        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

}
