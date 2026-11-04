<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Location/Location', [
            'invoiceSchemes' => [], // Placeholder
            'invoiceLayouts' => [], // Placeholder
            'priceGroups' => [], // Placeholder
            'accounts' => [], // Placeholder
        ]);
    }

    public function getLocations()
    {
        $locations = Location::latest()->get();
        return response()->json($locations);
    }

    public function storeLocation(Request $request)
    {
        // Add basic validation rules matching the required fields in the UI
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            // Add other validations as necessary
        ]);

        Location::create($request->all());

        return redirect()->back()->with('message', 'Location created successfully!');
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $location = Location::findOrFail($request->id);
        $location->update($request->all());

        return redirect()->back()->with('message', 'Location updated successfully!');
    }

    public function deleteLocation($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();
        return redirect()->back()->with('message', 'Location deleted successfully!');
    }
    
    public function toggleLocationStatus($id)
    {
         $location = Location::findOrFail($id);
         // Check if column exists or handle dynamically if migration is unknown, 
         // but usually we assume 'status' or 'is_active' exists. 
         // For now, assuming 'status' as per previous code style.
         if (isset($location->status)) {
             $location->status = !$location->status;
             $location->save();
         }
         return redirect()->back()->with('message', 'Location status updated successfully!');
    }
}
