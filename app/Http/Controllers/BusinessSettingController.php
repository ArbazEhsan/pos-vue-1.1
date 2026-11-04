<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BusinessSetting;
use App\Models\Company;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class BusinessSettingController extends Controller
{
    public function index()
    {
        $settings = BusinessSetting::first();
        
        return Inertia::render('Settings/BusinessSettings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'logo' => $request->hasFile('logo') ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120' : 'nullable',
            
            'start_date' => 'nullable|date',
            'default_profit_percent' => 'nullable|numeric',
            'currency' => 'required|string|max:10',
            'currency_symbol_placement' => 'required|in:before,after',
            'financial_year_start_month' => 'required|integer|min:1|max:12',
            'stock_accounting_method' => 'required|string',
            'transaction_edit_days' => 'required|integer|min:0',
            'currency_precision' => 'required|integer|min:0|max:4',
            'quantity_precision' => 'required|integer|min:0|max:4',

            'default_credit_limit' => 'nullable|numeric',
            'default_sale_discount' => 'nullable|numeric',
            'default_sale_tax' => 'nullable|string',
            
            'product_sku_length' => 'nullable|integer',
            'quantity_integer_part_length' => 'nullable|integer',
            'quantity_fractional_part_length' => 'nullable|integer',
            
            'mail_driver' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|string',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'nullable|email',
            
            // Allow other fields to be loosely validated or implicitly compliant
            'inline_tax_sale_purchase' => 'boolean',
            'enable_product_expiry' => 'boolean',
            'enable_brands' => 'boolean',
            'enable_category' => 'boolean',
            'enable_sub_category' => 'boolean',
            'enable_price_tax_info' => 'boolean',
            'enable_sub_units' => 'boolean',
            'enable_racks' => 'boolean',
            'enable_row' => 'boolean',
            'enable_position' => 'boolean',
            'enable_warranty' => 'boolean',
            'enable_product_image' => 'boolean',
            
            'sales_price_is_minimum_selling_price' => 'boolean',
            'allow_overselling' => 'boolean',
            'enable_sales_order' => 'boolean',
            'is_pay_term_required' => 'boolean',
            'is_commission_agent_required' => 'boolean',
            'is_payment_link_enabled' => 'boolean',
            
            'disable_multiple_pay' => 'boolean',
            'disable_draft' => 'boolean',
            'disable_express_checkout' => 'boolean',
            'disable_product_suggestion' => 'boolean',
            'disable_recent_transactions' => 'boolean',
            'disable_discount' => 'boolean',
            'disable_order_tax' => 'boolean',
            'subtotal_editable' => 'boolean',
            'disable_suspend_sale' => 'boolean',
            'enable_transaction_date' => 'boolean',
            'enable_service_staff' => 'boolean',
            'is_service_staff_required' => 'boolean',
            'disable_credit_sale_button' => 'boolean',
            'enable_weighing_scale' => 'boolean',
            'show_invoice_scheme' => 'boolean',
            'show_invoice_layout_dropdown' => 'boolean',
            'print_invoice_on_suspend' => 'boolean',
            'show_pricing_on_product_suggestion_tooltip' => 'boolean',
            
            'enable_editing_product_price_from_purchase_screen' => 'boolean',
            'enable_purchase_status' => 'boolean',
            'enable_lot_number' => 'boolean',
            'enable_purchase_order' => 'boolean',
            'enable_purchase_requisition' => 'boolean',
            'strict_check' => 'boolean',
        ]);

        $settings = BusinessSetting::firstOrNew();

        // Handle file upload
        if ($request->hasFile('logo')) {
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = $request->file('logo')->store('uploads/business_logos', 'public');
        } elseif ($request->exists('logo') && is_null($request->logo)) {
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = null;
        }

        // Mass assign all other fields
        $data = $request->except(['logo', 'logo_path']);
        $data['user_id'] = auth()->id();

        $settings->fill($data);

        $settings->save();

        $company = Company::firstOrNew();

        $company->fill([
            'name'   => $request->business_name,
            'phone1' => $request->phone,
            'phone2' => $request->phone,
            'user_id' => auth()->id(),
        ]);

        $company->save();

        return redirect()->back()->with('message', 'Business settings updated successfully.');
    }

    public function testEmail(Request $request) {
        $request->validate([
            'mail_driver' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|string',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'nullable|email',
        ]);

        $data = [
            'subject' => 'Test Email',
            'body' => 'This is a test email.',
        ];

        Mail::to($request->mail_from_address)->send(new TestEmail($data));

        return redirect()->back()->with('message', 'Test email sent successfully.');
    }
}
