<?php

namespace App\Http\Controllers;

use App\Models\AccountHead;
use App\Models\Channel;
use App\Models\Network;
use App\Models\PurchaseOrder;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventory_system.supplier.index');
    }
    public function dataTable()
    {
        $query = Client::where('type',1);
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Supplier $supplier) {
                $btn = '';
                if(auth()->user()->can('supplier_edit')){
                    $btn  .='<a href="'.route('supplier.edit',['supplier'=>$supplier->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if(auth()->user()->can('supplier_delete')) {
                    $btn .= ' <a role="button" data-id="' . $supplier->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('supplier_create')) {
            abort(403, 'Unauthorized');
        }
        return view('inventory_system.supplier.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('supplier_create')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('clients')
                ->where('type',1)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',1)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
                    ->where('type',1)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Supplier record in the database
            $validatedData['type'] = 1;
            $supplier = Client::create($validatedData);
            // Create a new Account Head record in the database
            $accountHead = AccountHead::where('supplier_id',$supplier->id)->first();
            if (!$accountHead){
                $code = AccountHead::max('code') ? AccountHead::max('code') + 1 : 1001;
                $accountHead = new AccountHead();
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = 16;//Accounts Payable
            $accountHead->supplier_id = $supplier->id;
            $accountHead->name = $request->name;
            $accountHead->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('supplier.index')->with('success', 'Supplier created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('supplier.create')->with('error', 'An error occurred while creating the Supplier: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        if (!auth()->user()->hasPermissionTo('supplier_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($supplier->type != 1) {
            abort(404);
        }
        try {
            // If the Supplier exists, display the edit view
            return view('inventory_system.supplier.edit', compact('supplier'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Supplier is not found
            return redirect()->route('supplier.index')->with('error', 'Supplier not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        if (!auth()->user()->hasPermissionTo('supplier_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($supplier->type != 1) {
            abort(404);
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',1)
                    ->ignore($supplier)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',1)
                    ->ignore($supplier)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
                ->where('type',1)
                ->ignore($supplier)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Supplier record in the database
            $validatedData['type'] = 1;
            $supplier->update($validatedData);

            // Create a new Account Head record in the database
            $accountHead = AccountHead::where('supplier_id',$supplier->id)->first();
            if (!$accountHead){
                $code = AccountHead::max('code') ? AccountHead::max('code') + 1 : 1001;
                $accountHead = new AccountHead();
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = 16;//Accounts Payable
            $accountHead->supplier_id = $supplier->id;
            $accountHead->name = $request->name;
            $accountHead->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('supplier.index')->with('success', 'Supplier updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('supplier.edit',['supplier'=>$supplier->id])->with('error', 'An error occurred while updating the Supplier: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            if (!auth()->user()->hasPermissionTo('supplier_delete')) {
                abort(403, 'Unauthorized');
            }
            $purchase = PurchaseOrder::where('supplier_id',$supplier->id)->first();
            if ($purchase) {
                // If a related Supplier exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has purchase order logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Supplier record
            $supplier->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Supplier deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Supplier not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
