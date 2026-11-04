<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Company;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index()
	{
		$result = Company::all();
        return Inertia::render('Company/Index', [
            'companies' => $result,
        ]);
	}
    
	public function store(Request $request)
    {
		$request->validate([
            'comp_name' => 'required|max:255',
            'comp_p_1' => 'required|regex:/^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/',
            'comp_p_2' => 'required|regex:/^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/',
        ]);

    	$result = Company::count();
    	if ($result>0) {
    		return redirect('/viewCompany')->with('message','Company Already Exists!');
    	}
    	$post = new Company;
    	$post->name = $request->comp_name;
    	$post->phone1 = $request->comp_p_1;
    	$post->phone2 = $request->comp_p_2;
        $post->user_id = Auth::id();
        
    	$post->save();
    	return redirect('/viewCompany')->with('message','Company Created Successfully!');

    }

    public function delete($id)
    {
    	Company::destroy($id);
		return redirect()->back()->with('message','Company Deleted Successfully!');
    }
}
