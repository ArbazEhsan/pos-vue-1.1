<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // This logic runs only after the user is authenticated by the 'auth' middleware
        if (Auth::user()->type == 'Admin' || data_get(Auth::user(), 'permission.pages.dashboard_view')) {
            // Assuming you have a DashboardController
            return app(DashboardController::class)->index($request);
            // Alternatively, return a view directly:
            // return view('dashboard.admin'); 
        } else {
            return Inertia::render('Inventory/CheckPoint');
        }
    }
}
