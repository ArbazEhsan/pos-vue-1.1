<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class OnboardController extends Controller
{
    /**
     * Display the onboarding form.
     */
    public function index()
    {
        if (BusinessSetting::exists() || User::exists()) {
            return redirect()->route('login');
        }

        return Inertia::render('Onboard/Index');
    }

    /**
     * Store the onboarding data.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'account_type' => $request->designation,
                'type' => 'Admin',
            ]);

            // Store business information (you may need to create a Business model and migration)
            // This is a placeholder - adjust according to your database structure
            $businessData = [
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'business_type' => $request->business_type,
                // 'address' => $request->business_address ?? null,
                // 'city' => $request->business_city ?? null,
                // 'state' => $request->business_state ?? null,
                // 'zip' => $request->business_zip ?? null,
                // 'country' => $request->business_country ?? null,
                'phone' => $request->business_phone,
                'email' => $request->business_email,
                // 'tax_id' => $request->tax_id ?? null,
                // 'registration_number' => $request->registration_number ?? null,
            ];

            // Store user preferences
            // $preferencesData = [
            //     'user_id' => $user->id,
            //     'designation' => $request->designation,
            //     'department' => $request->department ?? null,
            //     'currency' => $request->currency,
            //     'timezone' => $request->timezone,
            //     'fiscal_year_start' => $request->fiscal_year_start ?? '01',
            //     'date_format' => $request->date_format ?? 'MM/DD/YYYY',
            //     'time_format' => $request->time_format ?? '12',
            // ];

            // You can store these in separate tables or in a settings table
            // For now, we'll use the session or you can create appropriate models
            
            // Example: Store in a business_settings table
            // BusinessSetting::create($businessData);
            // UserPreference::create($preferencesData);
            BusinessSetting::create($businessData);

            DB::commit();

            // Redirect to login page with success message
            return redirect()->route('login')->with('success', 'Account created successfully! Please login to continue.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'An error occurred during registration. Please try again.' . $e->getMessage()
            ])->withInput();
        }
    }
}
