<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\User;
use App\Models\Permission;
use Inertia\Inertia;

class CustVenController extends Controller
{
    public function checkPoint()
    {
        return view('account.checkPointCV');
    }

    public function index(Request $request)
	{
        $result = Account::orderBy('type')->get();

        return Inertia::render('Account/Index', [
            'accounts' => $result,
        ]);
	}

    public function viewCustomer(Request $request)
    {
        $result = Account::orderBy('type')->get();
        $result = Product::with('category:id,name')->get();

        return Inertia::render('Inventory/Product/Product', [
            'products' => $result,
        ]);
    }

    public function viewVendor()
    {
        $result = Account::where('type','Vendor')->get();
        return view('account.viewVendor')->with('vend', $result);
    }

	public function form()
	{
		return view('account.addCustVen');
	}

	public function store(Request $request)
    {
        $request->validate([
            'acc_name' => 'required|max:255|unique:accounts,name',
            'acc_type' => 'required|max:255',
        ]);

    	$post = new Account;
    	$post->name = $request->acc_name;
    	$post->type = $request->acc_type;
    	$post->city = $request->acc_city;
    	$post->address = $request->acc_address;
    	$post->contact_person = $request->acc_contact_per;
    	$post->mobile = $request->acc_mobile;
    	$post->tel_1 = $request->acc_tel1;
    	$post->email = $request->acc_email;
        // $post->tel_2 = $request->tel2;
        $post->user_id = Auth::id();

    	$post->save();
    	return redirect('/viewAccount')->with('success','Account Created Successfully');

    }

    public function storeApi(Request $request)
    {
    	$post = new Account;
    	$post->name = $request->acc_name;
    	$post->type = $request->acc_type;
    	$post->city = $request->acc_city;
    	$post->address = $request->acc_address;
    	$post->contact_person = $request->acc_contact_per;
    	$post->mobile = $request->acc_mobile;
    	$post->tel_1 = $request->acc_tel1;
    	$post->email = $request->acc_email;
        $post->user_id = Auth::id();

    	$post->save();
        
        $result = [];
        $result['id'] = $post->id;
        $result['name'] = $post->name;

    	return response()->json($result);

    }

    public function edit($id)
    {
    	$result = Account::where('id', $id)->get();
        
        return Inertia::render('Account/Edit', [
            'accounts' => $result,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'acc_name' => 'required|max:255',
            'acc_type' => 'required|max:255',
        ]);

    	$id = $request->acc_id;
    	$post = Account::find($id);
    	$post->name = $request->acc_name;
    	$post->type = $request->acc_type;
    	$post->city = $request->acc_city;
    	$post->address = $request->acc_address;
    	$post->contact_person = $request->acc_contact_per;
    	$post->mobile = $request->acc_mobile;
    	$post->tel_1 = $request->acc_tel1;
        $post->email = $request->acc_email;
    	// $post->tel_2 = $request->tel2;
        $post->user_id = Auth::id();

    	$post->save();
        
        return redirect('/viewAccount')->with('message','Account Updated Successfully!');
    }

    public function delete($id)
    {
    	Account::destroy($id);
    	return $this->index();
    }

    public function statusAccount($status,$id)
    {
        $post = Account::find($id);
        $post->status = !$post->status; 
        $post->user_id = Auth::id();
        
        $post->save();

        return redirect()->back()->with('message','Status Updated Successfully!');
    }

    public function statusCust($status,$id)
    {
        $post = Account::find($id);
        $post->status = !$post->status; 
        $post->user_id = Auth::id();

        $post->save();

        return $this->viewCustomer();
    }

    public function statusVen($status,$id)
    {
        $post = Account::find($id);
        $post->status = !$post->status; 
        $post->user_id = Auth::id();

        $post->save();

        return $this->viewVendor();
    }

}
