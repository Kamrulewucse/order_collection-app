<?php

namespace App\Http\Controllers;

use App\Enumeration\TransactionType;
use App\Models\AccountGroup;
use App\Models\AccountHead;
use App\Models\Inventory;
use App\Models\Transaction;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AccountHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes' : 'account_head';
        $permissionCreate = $request->query('payment_mode') == 1 ? 'payment_modes_create' : 'account_head_create';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }

        $paymentMode = $request->payment_mode;
        if ($request->payment_mode == 0){
            $accountHeadTitle = 'Account Head';
        }else{
            $accountHeadTitle = 'Payment Mode';

        }

        return view('accounts.account_head.index',compact('paymentMode',
        'accountHeadTitle','permissionCreate'));
    }
    public function dataTable()
    {

        $query = AccountHead::with('accountGroup');
        if (request('payment_mode') == 0){
            $query->where('payment_mode',0);
        }else{
            $query->where('payment_mode','>',0);

        }
        $paymentMode = request('payment_mode');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(AccountHead $accountHead) use ($paymentMode) {
                $btn ='';
                $permissionDelete = $accountHead->payment_mode > 0 ? 'payment_modes_edit' : 'account_head_edit';
                if(auth()->user()->can($permissionDelete)) {
                    $btn.='<a href="'.route('account-head.edit',['account_head'=>$accountHead->id,'payment_mode'=>$paymentMode]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                $permissionEdit = $accountHead->payment_mode > 0 ? 'payment_modes_delete' : 'account_head_delete';
                if(auth()->user()->can($permissionEdit)) {
                    $btn .=' <a role="button" data-id="'.$accountHead->id.'" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->addColumn('account_group_name', function(AccountHead $accountHead){
                return $accountHead->accountGroup->name ?? '';

            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_create' : 'account_head_create';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }

        $paymentMode = $request->payment_mode;
        if ($request->payment_mode == 0){
            $accountHeadTitle = 'Account Head';
        }else{
            $accountHeadTitle = 'Payment Mode';

        }
        $accountGroups = AccountGroup::orderBy('name')->get();

        return view('accounts.account_head.create',compact('paymentMode',
            'accountHeadTitle','accountGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_create' : 'account_head_create';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $rules = [
            'name' =>[
                'required','max:255',
                Rule::unique('account_heads')
            ],
            'account_group'=>'required',
            'opening_balance'=>'required|numeric|min:0',
        ];
        if ($request->payment_mode == 1){
            $rules['payment_mode_type'] = 'required';
            $rules['bank_commission_percent'] = 'required|numeric|min:0';
        }
        $validatedData = $request->validate($rules);



        // Start a database transaction
        DB::beginTransaction();

        try {
            $validatedData['code'] = AccountHead::max('code') ? AccountHead::max('code') + 1 : 1001;
            $validatedData['payment_mode'] = $request->payment_mode_type == '' ? 0 : $request->payment_mode_type;
            unset($validatedData['payment_mode_type']);
            $validatedData['account_group_id'] = $request->account_group;
            unset($validatedData['account_group']); // Remove the 'account_group' key
            // Create a new Account Head record in the database
          AccountHead::create($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('account-head.index',['payment_mode'=>$request->payment_mode])->withInput()->with('success', 'Account head created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('account-head.create',['payment_mode'=>$request->payment_mode])->withInput()->with('error', 'An error occurred while creating the Account head: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountHead $account_head,Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_edit' : 'account_head_edit';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }

        $paymentMode = $request->payment_mode;
        if ($request->payment_mode == 0){
            $accountHeadTitle = 'Account Head';
        }else{
            $accountHeadTitle = 'Payment Mode';

        }
        $accountGroups = AccountGroup::orderBy('name')->get();
        try {
            // If the Account head exists, display the edit view
            return view('accounts.account_head.edit', compact('account_head','paymentMode','accountHeadTitle','accountGroups'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Account head is not found
            return redirect()->route('account-head.index')->with('error', 'Account head not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountHead $account_head)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_edit' : 'account_head_edit';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $rules = [
            'name' =>[
                'required','max:255',
                Rule::unique('account_heads')
                    ->ignore($account_head)
            ],
            'account_group'=>'required',
            'opening_balance'=>'required|numeric|min:0',
        ];
        if ($request->payment_mode == 1){
            $rules['payment_mode_type'] = 'required';
            $rules['bank_commission_percent'] = 'required|numeric|min:0';
        }
        $validatedData = $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {

            // Update the account_head record in the database
            $validatedData['account_group_id'] = $request->account_group;
            unset($validatedData['account_group']); // Remove the 'account_group' key
            unset($validatedData['payment_mode_type']);
            $account_head->update($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            // Redirect to the index page with a success message
            return redirect()->route('account-head.index',['payment_mode'=>$request->payment_mode])->withInput()->with('success', 'Account head created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('account-head.edit',['account_head'=>$account_head->id,'payment_mode'=>$request->payment_mode])->withInput()
                ->with('error', 'An error occurred while updating the Account head: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountHead $account_head)
    {

        try {
            $permission = $account_head->payment_mode > 0 ? 'payment_modes_delete' : 'account_head_delete';
            if (!auth()->user()->hasPermissionTo($permission)) {
                abort(403, 'Unauthorized');
            }

            $transaction = Transaction::where('account_head_id',$account_head->id)->first();
            if ($transaction) {
                // If a related AccountHead exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has transaction logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Account Head record
            $account_head->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Account head deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Account head not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
