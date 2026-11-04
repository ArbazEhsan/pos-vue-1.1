<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax;
use App\Models\TaxGroup;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    // View Tax Page
    public function index()
    {
        $tax = Tax::all();
        $taxGroup = TaxGroup::all();
        return Inertia::render('Settings/Tax/Tax',[
            'tax' => $tax,
            'taxGroup' => $taxGroup,
        ]);
    }

    // Get Taxes for DataTable
    public function getTaxes()
    {
        $taxes = Tax::latest()->get();
        return response()->json($taxes);
    }

    // Get Tax Groups for DataTable
    public function getTaxGroups()
    {
        $taxGroups = TaxGroup::latest()->get()->map(function ($group) {
            $taxDetails = Tax::whereIn('id', $group->tax_ids ?? [])->get();
            $group->tax_details = $taxDetails;
            $group->total_rate = $taxDetails->sum('rate');
            return $group;
        });
        
        return response()->json($taxGroups);
    }

    // Store Tax
    public function storeTax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_group_only' => 'boolean',
        ]);

        Tax::create([
            'name' => $request->name,
            'rate' => $request->rate,
            'is_group_only' => $request->is_group_only ?? false,
            'status' => true,
        ]);

        return redirect()->back()->with('message', 'Tax created successfully!');
    }

    // Update Tax
    public function updateTax(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:taxes,id',
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_group_only' => 'boolean',
        ]);

        $tax = Tax::findOrFail($request->id);
        $tax->update([
            'name' => $request->name,
            'rate' => $request->rate,
            'is_group_only' => $request->is_group_only ?? false,
        ]);

        return redirect()->back()->with('message', 'Tax updated successfully!');
    }

    // Delete Tax
    public function deleteTax($id)
    {
        $tax = Tax::findOrFail($id);
        
        // Check if tax is used in any tax group
        $usedInGroups = TaxGroup::whereJsonContains('tax_ids', (string)$id)->exists();
        
        if ($usedInGroups) {
            return redirect()->back()->with('error', 'Cannot delete tax. It is used in tax groups.');
        }
        
        $tax->delete();
        return redirect()->back()->with('message', 'Tax deleted successfully!');
    }

    // Toggle Tax Status
    public function toggleTaxStatus($id)
    {
        $tax = Tax::findOrFail($id);
        $tax->status = !$tax->status;
        $tax->save();

        return redirect()->back()->with('message', 'Tax status updated successfully!');
    }

    // Store Tax Group
    public function storeTaxGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tax_ids' => 'required|array|min:1',
            'tax_ids.*' => 'exists:taxes,id',
        ]);

        TaxGroup::create([
            'name' => $request->name,
            'tax_ids' => $request->tax_ids,
            'status' => true,
        ]);

        return redirect()->back()->with('message', 'Tax Group created successfully!');
    }

    // Update Tax Group
    public function updateTaxGroup(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:tax_groups,id',
            'name' => 'required|string|max:255',
            'tax_ids' => 'required|array|min:1',
            'tax_ids.*' => 'exists:taxes,id',
        ]);

        $taxGroup = TaxGroup::findOrFail($request->id);
        $taxGroup->update([
            'name' => $request->name,
            'tax_ids' => $request->tax_ids,
        ]);

        return redirect()->back()->with('message', 'Tax Group updated successfully!');
    }

    // Delete Tax Group
    public function deleteTaxGroup($id)
    {
        $taxGroup = TaxGroup::findOrFail($id);
        $taxGroup->delete();

        return redirect()->back()->with('message', 'Tax Group deleted successfully!');
    }

    // Toggle Tax Group Status
    public function toggleTaxGroupStatus($id)
    {
        $taxGroup = TaxGroup::findOrFail($id);
        $taxGroup->status = !$taxGroup->status;
        $taxGroup->save();

        return redirect()->back()->with('message', 'Tax Group status updated successfully!');
    }    
}
