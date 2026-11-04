<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ledger;
use App\Models\Account;
use App\Models\Company;
use App\Models\Tran;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\BusinessSetting;
use Inertia\Inertia;

class LedgerController extends Controller
{
    public function checkPoint()
 	{
 	   	return view('ledger.checkPoint');
 	} 

     public function ledgerForm()
 	{

 		$result = Account::where('type','=','Customer')->orderBy('name')->get();
 		$result2 = Account::where('type','=','Vendor')->orderBy('name')->get();

        return Inertia::render('Ledger/Create', [
            'cust_accounts' => $result,
            'vend_accounts' => $result2,
        ]);
 	} 

 	public function custForm()
 	{
 		$data = "";
 		$result = Account::orderBy('name')->pluck('name','id');
 		foreach ($result as $key => $value) {
 			$data .= '<option value="'.$key.'">'.$value.'</option>';
 		}
 	 	return view('ledger.custForm')->with('customer',$data);
 	} 

 	public function venForm()
 	{
 		$data = "";
 		$result = Account::orderBy('name')->pluck('name','id');
 		foreach ($result as $key => $value) {
 			$data .= '<option value="'.$key.'">'.$value.'</option>';
 		}
 	 	return view('ledger.venForm')->with('vendor',$data);
 	}

 	public function Ledger(Request $request)
 	{
        
 		$from = $request->from;
 		$to = $request->to;
 		$id = $request->acc_type;
        $source = $request->source;

 		$cust = Account::find($id);
 		$company = BusinessSetting::first();
        $checkLedger = Ledger::where('account_id', $id)->exists();
   

        if($checkLedger)
        {
            // If the date is invalid, retrieve all records
            if (empty($from) || empty($to) || strtotime($from) === false || strtotime($to) === false) 
            {
                $result = Ledger::where('account_id', $id)
                    ->with('transaction')
                    ->get();

                $from = "All";
                $to = "All";

                $opb = Ledger::where('account_id', $id)->oldest()->first();

                $debit = 0;
                $cr = 0;
                $Bal = 0;
                $text = '';

                if ($opb) 
                {
                    if (empty($opb->cr)) 
                    {
                        $Bal = $opb->dr;
                        $debit = $opb->dr - $cr;
                        $text = 'Receivable';
                    } 
                    else 
                    {
                        $cr = $opb->cr;
                        $Bal = $debit - $cr;
                        $text = 'Payable';
                    }
                }

                $opBal = [
                    'bal' => $Bal,
                    'text' => $text,
                ];
            }
            else 
            {
                $dr = Ledger::where('account_id', $id)
                    ->where('day', '<', $from)
                    ->sum('dr');
                
                $cr = Ledger::where('account_id', $id)
                    ->where('day', '<', $from)
                    ->sum('cr');

                $Bal = $dr - $cr;

                // Handle pagination offset for opening balance
                $page = $request->input('page', 1);
                $perPage = 50;
                $offset = ($page - 1) * $perPage;

                if ($offset > 0) {
                    $previousPageDr = Ledger::where('account_id', $id)
                        ->whereBetween('day', [$from, $to])
                        ->orderBy('day')
                        ->orderBy('id')
                        ->take($offset)
                        ->sum('dr');
                    
                    $previousPageCr = Ledger::where('account_id', $id)
                        ->whereBetween('day', [$from, $to])
                        ->orderBy('day')
                        ->orderBy('id')
                        ->take($offset)
                        ->sum('cr');
                    
                    $Bal += ($previousPageDr - $previousPageCr);
                }

                $text = ($Bal > 0) ? 'Receivable' : 'Payable';

                $opBal = [
                    'bal' => $Bal,
                    'text' => $text
                ];

                // Fetch ledger records with related transactions
                $result = Ledger::where('account_id', $id)
                    ->whereBetween('day', [$from, $to])
                    ->with('transaction')
                    ->orderBy('day')
                    ->orderBy('id')
                    ->paginate($perPage)
                    ->appends($request->all());

                $from = date('d/m/Y', strtotime($from));
                $to = date('d/m/Y', strtotime($to));

            }

            $remarks101 = [];
            $remarks102 = '';

            foreach ($result as $value) 
            {
                $tran = $value->transaction;

                if (!$tran) {
                    $remarks101[] = 'Transaction details missing';
                    $remarks102 = '';
                    continue;
                }

                if ($value->type == 'SV' || $value->type == 'SRV') 
                {
                    $result2 = Sale::where('sale_no', $tran->invoice_id)
                        ->with('product', 'variation.product')
                        ->get();
                    // dd($result2);
                    if ($result2->isNotEmpty()) {
                        foreach ($result2 as $value2) 
                        {
                            // $productName = $value2->product ? $value2->product->name : $value2->product->variation ? $value2->product->variation->var_name : 'Unknown Product';
                            $productName = $value2->product?->name ?? ($value2->variation?->product?->name. ' ('.$value2->variation?->product?->variation_name .":". $value2->variation?->var_name.")") ?? 'Unknown Product';


                            $remarks102 .= $productName . '-' . $value2->qty . '@' . $value2->price . ', ';
                        }
                        $remarks101[] = $remarks102;
                    } else {
                        $remarks101[] = 'Bill#.' . $tran->bill_no . '/' . $tran->remarks;
                    }
                    $remarks102 = '';
                } 
                elseif ($value->type == 'PV' || $value->type == 'PRV') 
                {
                    $result2 = Purchase::where('pur_no', $tran->invoice_id)
                        ->with('product', 'variation.product')
                        ->get();

                    if ($result2->isNotEmpty()) {
                        foreach ($result2 as $value2) 
                        {
                            // $productName = $value2->product ? $value2->product->name : 'Unknown Product';
                            $productName = $value2->product?->name ?? ($value2->variation?->product?->name. ' ('.$value2->variation?->product?->variation_name .":". $value2->variation?->var_name.")") ?? 'Unknown Product';

                            $remarks102 .= $productName . '-' . $value2->qty . '@' . $value2->price . ', ';
                        }
                        $remarks101[] = $remarks102;
                    } else {
                        $remarks101[] = 'Bill#.' . $tran->bill_no . '/' . $tran->remarks;
                    }
                    $remarks102 = '';
                }
                else 
                {
                    $remarks101[] = 'Bill#.' . $tran->bill_no . '/' . $tran->remarks;
                }
            }

            return Inertia::render('Ledger/Index', [
                'ledger'=>$result, 
                'cust'=>$cust, 
                'to'=>$to, 
                'from'=>$from, 
                'company'=>$company, 
                'opb'=>$opBal,
                'source'=>$source,
                'remarks101'=>$remarks101
            ]);
 		 
        }
        else 
        {
            return "<center>Please make any Transaction or Invoice to Generate Ledger<br><a href='#' onClick='javascript:history.go(-1)'>Click here to Go Back</a></center>";
        }
 	}

 	public function printTrans($f,$id)
 	{
 		if ($f=='i') {
 			$txt = "Cashin";
 		} elseif($f=='o') {
 			$txt = "Cashout";
 		}
 		$company = Company::all();
 		$result = Tran::where([
 			['bill_no', $id],
 			['type', $f]
 		])->get();
 		$account = Account::find($result[0]->cust_id);
 		return view('ledger.print')->with(['trans'=> $result, 'company'=> $company, 'from'=> $txt, 'account'=>$account->name]);
 	}
}
