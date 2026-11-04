<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Tran;
use App\Models\Account;
use App\Models\Ledger;
use App\Models\Company;
use Inertia\Inertia;

class TransController extends Controller
{
    public function menuTran($num, Request $request)
	{
		// return view('trans.viewTrans');
        // if($request->search){
        //     $result = Tran::where('trans.bill_no','like','%'.$request->search.'%')
        //                         ->join('accounts','account_id','=','accounts.id')
        //                         ->select('trans.*','accounts.name as cust_name')
        //                         ->paginate($num);
        // }
        // else {
        //     $result = Tran::join('accounts','account_id','=','accounts.id')
        //                     ->select('trans.*','accounts.name as cust_name')
        //                     ->paginate($num);
        // }

        // return Inertia::render('Trans/CheckPoint');
	}

	/* TransIn methods start */
    public function indexTransIn($num, Request $request)
	{
        if($request->search){
            // $result = Tran::where('trans.bill_no','like','%'.$request->search.'%')
            //                     ->where('trans.type','CR')
            //                     ->join('accounts','account_id','=','accounts.id')
            //                     ->select('trans.*','accounts.name as cust_name')
            //                     ->paginate($num);
            $result = Tran::with('account')->where('type','CR')->where('bill_no','like','%'.$request->search.'%')->paginate($num);
        }
        else {
            // $result = Tran::where('trans.type','CR')
            //                     ->join('accounts','account_id','=','accounts.id')
            //                     ->select('trans.*','accounts.name as cust_name')
            //                     ->paginate($num);
            $result = Tran::with('account')->where('type','CR')->paginate($num);
        }

        return Inertia::render('Trans/Cashin/Index', [
            'transactions' => $result,
        ]);

        // $result = Tran::join('accounts','account_id','=','accounts.id')->where('trans.type','CR')
        //     ->select('trans.*','accounts.name as cust_name')->get();
        // return view('trans.viewTransIn')->with('transin', $result);
	}

    public function fillTransIn()
    {
        // $result='';
        // $count = Account::count();
        // if ($count>0) {
        //     $data = Account::where('status','1')->orderBy('name')->get();
        //     $result = $data;
        //     foreach ($data as $value) {
        //         $result .= '<option value="'.$value->id.'">'.$value->name.' - '.$value->type.'</option>';
        //     }
        // }
        // else {
        //     $result = '<option value="0">No Customer Add</option>';
        // }
        // return view('trans.addTransIn')->with('customer', $result);
        $result = Account::where('status','1')->orderBy('name')->get();
        return Inertia::render('Trans/Cashin/Create', [
            'accounts' => $result,
        ]);
    }

    public function storeTransIn(Request $request)
    {
        $request->validate([
            'cashin_day' => 'required|max:255',
            'cashin_cust_id' => 'required|max:255',
            'cashin_amount' => 'required|max:255',
        ]);

        /* trans query */
        $post = new Tran;
        $post->day = $request->cashin_day;
        $post->user_id = Auth::id();
        $post->account_id = $request->cashin_cust_id;
        $post->amount = $request->cashin_amount;
        $post->bill_no = $request->cashin_bill_no;
        $post->remarks = $request->cashin_remarks;
        $post->type = "CR";
        $post->status = 1;
        $post->save();

        /* ledger query*/
        $post2 = new Ledger;
        $post2->cr = $request->cashin_amount;
        $post2->day = $request->cashin_day;
        $post2->account_id = $request->cashin_cust_id;
        $post2->trans_id = $post->id;
        $post2->type = "CR";
        $post2->status = 1;
        $post2->save();

    	// $post = new Tran;
    	// $post->day = $request->day;
    	// $post->cust_id = $request->cust_id;
    	// $post->amount = $request->amount;
    	// $post->remarks = $request->remarks;
    	// $post->bill_no = $request->bill_no;
    	// $post->type = "i";
    	// $post->status = 1;

    	// $post->save();
        // $id = $post->id;
        // $cust_name = Account::where('id',$request->cust_id)->pluck('name');

        // $post = new Ledger;
        // $post->cr = $request->amount;
        // $post->day = $request->day;
        // $post->invoice_no = $id;
        // $post->type = 'i';
        // $post->account = $cust_name[0];
        // $post->remarks = $request->remarks;
        // $post->status = 1;

        // $post->save();
    	return redirect('/viewTransIn/10')->with('message','Data Saved Successfully!');

    }

    public function deleteTransIn($id)
    {
    	Tran::destroy($id);
        Ledger::where('trans_id',$id)->delete();
    	// return $this->indexTransIn();
        return redirect()->back()->with('message','Data Deleted Successfully!');
    }

    /* Trans out methods start */

    public function indexTransOut($num, Request $request)
	{
        // $result = Tran::join('accounts','account_id','=','accounts.id')->where('trans.type','CO')
        //     ->select('trans.*','accounts.name as cust_name')->get();
        // return view('trans.viewTransOut')->with('transout', $result);
        if($request->search){
            // $result = Tran::where('trans.bill_no','like','%'.$request->search.'%')
            //                     ->where('trans.type','CO')
            //                     ->join('accounts','account_id','=','accounts.id')
            //                     ->select('trans.*','accounts.name as cust_name')
            //                     ->paginate($num);
            $result = Tran::with('account')->where('type','CO')->where('bill_no','like','%'.$request->search.'%')->paginate($num);
        }
        else {
            // $result = Tran::where('trans.type','CO')
            //                     ->join('accounts','account_id','=','accounts.id')
            //                     ->select('trans.*','accounts.name as cust_name')
            //                     ->paginate($num);
            $result = Tran::with('account')->where('type','CO')->paginate($num);
        }

        return Inertia::render('Trans/Cashout/Index', [
            'transactions' => $result,
        ]);
	}

    public function fillTransOut()
    {
        // $result='';
        // $count = Account::count();
        // if ($count>0) {
        //     $data = Account::where('status','1')->orderBy('name')->get();
        //     foreach ($data as $value) {
        //         $result .= '<option value="'.$value->id.'">'.$value->name.' - '.$value->type.'</option>';
        //     }
        // }
        // else {
        //     $result = '<option value="0">No Vendor Add</option>';
        // }
        // return view('trans.addTransOut')->with('vendor', $result);
        $result = Account::where('status','1')->orderBy('name')->get();
        return Inertia::render('Trans/Cashout/Create', [
            'accounts' => $result,
        ]);
        
    }

    public function storeTransOut(Request $request)
    {
        $request->validate([
            'cashout_day' => 'required|max:255',
            'cashout_cust_id' => 'required|max:255',
            'cashout_amount' => 'required|max:255',
        ]);

        /* trans query */
        $post = new Tran;
        $post->day = $request->cashout_day;
        $post->user_id = Auth::id();
        $post->account_id = $request->cashout_cust_id;
        $post->amount = $request->cashout_amount;
        $post->bill_no = $request->cashout_bill_no;
        $post->remarks = $request->cashout_remarks;
        $post->type = "CO";
        $post->status = 1;
        $post->save();

        /* ledger query*/
        $post2 = new Ledger;
        $post2->dr = $request->cashout_amount;
        $post2->day = $request->cashout_day;
        $post2->account_id = $request->cashout_cust_id;
        $post2->trans_id = $post->id;
        $post2->type = "CO";
        $post2->status = 1;
        $post2->save();
    	// $post = new Tran;
    	// $post->day = $request->day;
    	// $post->cust_id = $request->cust_id;
    	// $post->amount = $request->amount;
    	// $post->remarks = $request->remarks;
    	// $post->bill_no = $request->bill_no;
    	// $post->type = "o";
    	// $post->status = 1;

    	// $post->save();

     //    $id = $post->id;
     //    $cust_name = Account::where('id',$request->cust_id)->pluck('name');

     //    $post = new Ledger;
     //    $post->dr = $request->amount;
     //    $post->day = $request->day;
     //    $post->invoice_no = $id;
     //    $post->type = 'o';
     //    $post->account = $cust_name[0];
     //    $post->remarks = $request->remarks;
     //    $post->status = 1;

     //    $post->save();
    	// return redirect('addTransOut')->with('success','Data Inserted Successfully');
        return redirect('/viewTransOut/10')->with('message','Data Saved Successfully!');

    }

    public function deleteTransOut($id)
    {
    	Tran::destroy($id);
        Ledger::where('trans_id',$id)->delete();
    	// return $this->indexTransOut();
        return redirect()->back()->with('message','Data Deleted Successfully!');
    }

    public function cashPrint($value)
    {
        $result = Tran::where('trans.id',$value)
            ->join('accounts','accounts.id','=','trans.account_id')
        ->select('trans.*','accounts.name as cust_name','accounts.address')->get();

        $result2 = Company::all();
        return view('trans.cashPrint')->with(['invoice'=>$result,'company'=>$result2]);
    }
}
