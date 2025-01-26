<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\PurchaseOrder;
use App\Models\SaleOrder;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->type == 'purchase_commission'){
            $typeId = 1;
        }else{
            $typeId = 2;
        }

        return view('commission.index',compact('typeId'));
    }

    public function dataTable()
    {
        $query = Commission::with( 'supplier','customer')
            ->where('type',request('type'));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('supplier_name', function (Commission $commission) {
                return $commission->supplier->name ?? '';
            })
            ->addColumn('customer_name', function (Commission $commission) {
                return $commission->customer->name ?? '';
            })
            ->editColumn('commission_base_amount', function (Commission $commission) {
                return number_format($commission->commission_base_amount,2);
            })
            ->editColumn('commission_amount', function (Commission $commission) {
                return number_format($commission->commission_amount,2);
            })
            ->addColumn('commission_type_custom', function (Commission $commission) {
                if ($commission->commission_type == 1){
                    return $commission->commission_type.'%';
                }else{
                    return 'Flat';
                }
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->type == 'purchase_commission'){
            $typeId = 1;
            $clients = Client::where('type',1)->get();
        }else{
            $typeId = 2;
            $clients = Client::where('type',3)->get();
        }
        $orders = [];
        if ($request->start_date != '' && $request->end_date != ''){
            if ($typeId == 1){
                $orders = PurchaseOrder::where('supplier_id',$request->supplier)
                    ->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ])->orderBy('date') ->get();
            }else{
                $orders = SaleOrder::where('customer_id',$request->supplier)
                    ->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ])->orderBy('date') ->get();
            }
        }


        return view('commission.create',compact('typeId','clients',
        'orders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules =[
            'commission_type'=>'required',
            'commission_amount'=>'required|numeric|min:1',
        ];
        if ($request->commission_type == 1){
            $rules['commission_percent'] = 'required';
        }
        $request->validate($rules);

        DB::beginTransaction();
        try {

            $commission = new Commission();
            $commission->type = $request->type_id;
            $commission->supplier_id = $request->type_id == 1 ? $request->select_supplier : null;
            $commission->customer_id = $request->type_id == 2 ? $request->select_supplier : null;
            $commission->start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $commission->end_date = Carbon::parse($request->end_date)->format('Y-m-d');
            $commission->commission_type = $request->commission_type;
            $commission->commission_percent = $request->commission_type == 1 ? $request->commission_percent : 0;
            $commission->commission_base_amount = $request->commission_base_amount;
            $commission->commission_amount = $request->commission_amount;
            $commission->save();

            DB::commit();
            return redirect()->route('commission.index',['type'=>$request->type])->withInput()
                ->with('success','Commission generate successfully');
        }catch (\Exception $exception){
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error',$exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Commission $commission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Commission $commission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Commission $commission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Commission $commission)
    {
        //
    }
}
