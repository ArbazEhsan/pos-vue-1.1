<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserDetail;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserRightsController extends Controller
{

    public function viewUser()
    {
        $result = User::with('userDetail:id,user_id,password')->where('type','Employee')->orderByDesc('id')->get();
        return Inertia::render('UserRight/Index', [
            'data' => $result,
        ]);
    }

    public function userRights($uid)
    {
        $permission = Permission::where('user_id', $uid)->first();
        $user = User::where('id', $uid)->first();
        return Inertia::render('UserRight/SetPermission', [
            'permission' => $permission,
            'user' => $user,
        ]);
    }

    public function viewUForm()
    {
        return Inertia::render('UserRight/Create');
    }

    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'account_type' => $request->acc_type,
            'type' => 'Employee', 
        ]);

        $userDetail = UserDetail::create([
            'user_id' => $user->id,
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'account_type' => $request->acc_type,
            'password' => $request->password,
            'date_of_birth' => $request->dob,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status,
            'mobile' => $request->mobile_no,
            'permanent_address' => $request->permanent_address,
            'current_address' => $request->current_address,
            'bank_account_holder_name' => $request->account_holder_name,
            'bank_account_no' => $request->account_no,
            'bank_name' => $request->bank_name,
            'bank_branch_name' => $request->branch_name,
            'bank_identifier_code' => $request->bic,
            'basic_salary' => $request->basic_salary,
            'salary_frequency' => $request->salary_frequency,
        ]);
        
        return redirect('/viewProfile')->with('message','User Created Successfully!');
        
    }

    public function edit($uid)
    {
        $user = User::where('id', $uid)->with('userDetail')->first();
        return Inertia::render('UserRight/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = User::where('id', $request->id)->update([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'account_type' => $request->acc_type,
            'type' => 'Employee', 
        ]);

        // UserDetail::where('user_id', $request->id)->updateOrCreate([
        //     'first_name' => $request->first_name,
        //     'last_name' => $request->last_name,
        //     'account_type' => $request->acc_type,
        //     'password' => $request->password,
        //     'date_of_birth' => $request->dob,
        //     'gender' => $request->gender,
        //     'marital_status' => $request->marital_status,
        //     'mobile' => $request->mobile_no,
        //     'permanent_address' => $request->permanent_address,
        //     'current_address' => $request->current_address,
        //     'bank_account_holder_name' => $request->account_holder_name,
        //     'bank_account_no' => $request->account_no,
        //     'bank_name' => $request->bank_name,
        //     'bank_branch_name' => $request->branch_name,
        //     'bank_identifier_code' => $request->bic,
        //     'basic_salary' => $request->basic_salary,
        //     'salary_frequency' => $request->salary_frequency,
        // ]);
        UserDetail::updateOrCreate(
            ['user_id' => $request->id], // 1st Array: The "WHERE" condition (Search criteria)
            [                             // 2nd Array: The "UPDATE" data (Values to set)
                'name' => $request->first_name . ' ' . $request->last_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'account_type' => $request->acc_type,
                'password' => $request->password, // Note: Ensure you hash this if it's a real password!
                'date_of_birth' => $request->dob,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'mobile' => $request->mobile_no,
                'permanent_address' => $request->permanent_address,
                'current_address' => $request->current_address,
                'bank_account_holder_name' => $request->account_holder_name,
                'bank_account_no' => $request->account_no,
                'bank_name' => $request->bank_name,
                'bank_branch_name' => $request->branch_name,
                'bank_identifier_code' => $request->bic,
                'basic_salary' => $request->basic_salary,
                'salary_frequency' => $request->salary_frequency,
            ]
        );

        return redirect('/viewProfile')->with('message','User Updated Successfully!');
        
    }

    public function setPermission(Request $request)
    {
        $user = User::find($request->id);

        $permission = Permission::updateOrCreate(
            [
                'user_id' => $user->id
            ],
            [
                'pages' => $request->permissions, 
            ],
        );

        return redirect('/viewProfile')->with('message','Permission Set Successfully!');

    }

    public function deleteUser($id)
    {
        $result = User::destroy($id);
        return redirect()->back()->with('message','User Deleted Successfully!');
    }

    public function saveUserRights(Request $request)
    {
        $uid = $request->userID;
        $none = $request->pagenone;
        $vedit = $request->pageve;
        $add = $request->pageadd;
        $admin = $request->pageadmin;

        if(empty($none)!=1)
        {
            $this->splitandPass($none,$uid);
        }
        if (empty($vedit)!=1) 
        {
            $this->splitandPass($vedit,$uid);
        }
        if (empty($add)!=1) 
        {
            $this->splitandPass($add,$uid);
        }
        if (empty($admin)!=1) 
        {
            $this->splitandPass($admin,$uid);
        }
        
        echo "Inserted";
        
    }
    
    public function splitandPass($pages,$uid)
    {
        for ($i=0;$i<count($pages);$i++) { 
            $page = explode("/", $pages[$i]);
            $this->InsertorUpdate($page[0],$page[1],$uid);
        }
    }
    
    public function InsertorUpdate($page,$value,$uid)
    {   
        $count = Permission::where(['pages'=>$page,'user_id'=>$uid])->count();
        if ($count > 0) {
            Permission::where(['pages'=>$page,'user_id'=>$uid])->update(['user_id'=>$uid,'permission'=>$value]);
        }
        elseif ($count == 0) {
            Permission::insert(['user_id'=>$uid,'permission'=>$value,'pages'=>$page]);
        }
    }

}
