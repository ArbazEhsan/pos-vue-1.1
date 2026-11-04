<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Redirect;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && !$request->inertia()) 
        {
            // $categories = Category::select(['id', 'name', 'image_uri', 'status'])->latest()->get();
            $categories = Category::where('parent_id', null)
                        ->with('children')
                        ->get();
            return response()->json($categories);
        }

        return Inertia::render('Inventory/Category/Category');
    }



	public function store(Request $request)
    {
        $request->validate([
            'cat_id' => 'required',
            'cat_name' => 'required|max:255',
            'cat_image' => 'nullable|image|max:2048',
        ]);

        $obj = ($request->cat_id == 0) ? new Category() : Category::find($request->cat_id);

    	$obj->name = $request->cat_name;
        $obj->user_id = Auth::id();

        if ($request->hasFile('cat_image')) 
        {
            $path = $request->file('cat_image')->store('categories', 'public');
            $obj->image_uri = $path;
        }

    	$obj->save();
    	return redirect()->route('category')->with('message','Category Created Successfully!');

    }

    
    public function edit($id)
    {
    	$result = Category::find($id)->get();

        return Inertia::render('Inventory/Category/Edit', [
            'categories' => $result,
        ]);
    }

    public function addEdit($id)
    {
        return ($id == 0) ? 0 : Category::find($id);
    }


    public function update(Request $request)
    {
        $request->validate([
            'cat_name' => 'required|max:255',
        ]);
        
    	$post = Category::find($request->cat_id);
    	$post->name = $request->cat_name;
        $post->user_id = Auth::id();

    	$post->save();

    	return redirect()->route('category')->with('message','Category Updated Successfully');
    }


    public function delete($id)
    {
    	Category::destroy($id);
    	return redirect()->route('category')->with('message','Category Deleted Successfully!');
    }


    public function status($status, $id)
    {
        $post = Category::findOrFail($id);
        $newStatus = $status ? 0 : 1;
        $uid = Auth::id();

        $post->update(['status' => $newStatus, 'user_id' => $uid]);
        Product::where('category_id', $id)->update(['status' => $newStatus, 'user_id' => $uid]);

        return back()->with('message', 'Status Updated Successfully!');
    }


}
