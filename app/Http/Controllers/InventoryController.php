<?php

namespace App\Http\Controllers;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\AccountHead;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    public function index()
    {
        $companies = Client::where('type',1)->get();
        return view('inventory_system.inventory.index',compact('companies'));
    }

    public function details(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('inventory_log')) {
            abort(403, 'Unauthorized');
        }
        $product = Product::find($request->product);
        $searchProducts = Product::all();

        return view('inventory_system.inventory.details',compact('product',
        'searchProducts'));
    }
    public function utilizeProduct(Request $request)
    {
        $rules = [
            'inventory_id' => 'required',
        ];
        if ($request->inventory_id != ''){
            $inventory = Inventory::find($request->inventory_id);
          $rules['utilize_quantity'] = 'required|numeric|min:0.01|max:'.$inventory->quantity;
        }

        $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {
            if (!auth()->user()->hasPermissionTo('stock_utilized')) {
                abort(403, 'Unauthorized');
            }
            //Stock Product Utilize
            $inventory = Inventory::find($request->inventory_id);
            if ($inventory){

                $utilizeQuantity = $request->utilize_quantity;
                if ($utilizeQuantity > 0){
                    //if stock available
                    $utilizeAmount = $utilizeQuantity * $inventory->average_purchase_unit_price;

                    //Stock Decrement
                    $inventory->decrement('quantity',$utilizeQuantity);

                    //Inventory Log
                    $inventoryLog = new InventoryLog();
                    $inventoryLog->type = 3;//Utilized stock product
                    $inventoryLog->inventory_id = $inventory->id;
                    $inventoryLog->product_id = $inventory->id;
                    $inventoryLog->quantity = $utilizeQuantity;
                    $inventoryLog->unit_price = $inventory->average_purchase_unit_price;
                    $inventoryLog->product_code = $inventory->product->code ?? '';
                    $inventoryLog->notes = 'Utilize stock product';
                    $inventoryLog->user_id = auth()->id();
                    $inventoryLog->date = Carbon::now()->format('Y-m-d');
                    $inventoryLog->save();


                    //Create JV
                    $voucherNoGroupSl = Transaction::withTrashed()->max('voucher_no_group_sl') + 1;
                    $voucherNo = 'JV-' . $voucherNoGroupSl;

                    $voucher = new Voucher();
                    $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $voucher->amount = $utilizeAmount;
                    $voucher->date = Carbon::now()->format('Y-m-d');
                    $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                    $voucher->voucher_no = $voucherNo;
                    $voucher->inventory_log_id = $inventoryLog->id;
                    $voucher->notes = 'Utilize stock product';
                    $voucher->user_id = auth()->id();
                    $voucher->save();

                    //Debit
                    $transaction = new Transaction();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = AccountHead::find(100)->id ?? 100;//Utilize purchase product
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$DEBIT;
                    $transaction->amount = $utilizeAmount;
                    $transaction->date = Carbon::now()->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = 'Utilize stock product';
                    $transaction->user_id = auth()->id();
                    $transaction->save();

                    //Credit
                    $transaction = new Transaction();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = AccountHead::find(2)->id ?? 2;//Purchase product
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$CREDIT;
                    $transaction->amount = $utilizeAmount;
                    $transaction->date = Carbon::now()->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = 'Utilize stock product';
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                }
            }


            $message = 'Utilized successful';


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status'=>true,
                'message'=>$message,
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return response()->json([
                'status'=>false,
                'message'=>'An error occurred while Creating: '.$e->getMessage(),
            ]);
        }
    }

    public function dataTable()
    {
        $query = Inventory::with('product','product.supplier','product.unit','product.brand');

        if (\request('company_id') != '') {
            $companyId = \request('company_id');
            $query->whereHas('product.supplier', function($query) use ($companyId) {
                $query->where('id', $companyId);
            });
        }
        return DataTables::eloquent($query)
            ->addColumn('action', function(Inventory $inventory) {
                $btn = '';
                if (auth()->user()->hasPermissionTo('stock_utilized')) {
                    if ($inventory->quantity > 0){
                        //$btn .= '<button data-id="'.$inventory->id.'" class="btn btn-warning bg-gradient-warning btn-sm btn-utilize">Utilize</button>';
                    }
                }
                if (auth()->user()->hasPermissionTo('inventory_log')) {
                    $btn .= ' <a href="' . route('inventory.details', ['product' => $inventory->product_id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-info-circle"></i>  Bin Card</a>';
                }
                return $btn;
            })
            ->addColumn('supplier_name', function(Inventory $inventory) {
                return $inventory->product->supplier->name ?? '';
            })
            ->addColumn('product_name', function(Inventory $inventory) {
                return $inventory->product->name ?? '';
            })
            ->addColumn('brand_name', function(Inventory $inventory) {
                return $inventory->product->brand->name ?? '';
            })
            ->addColumn('unit_name', function(Inventory $inventory) {
                return $inventory->product->unit->name ?? '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }
    public function inventoryLogDataTable()
    {
        $query = InventoryLog::with('supplier','purchaseOrder','distributionOrder','product');
        if (request()->has('product_id') && request('product_id') != ''){
            $query->where('product_id',request('product_id'));
        }
        return DataTables::eloquent($query)
            ->addColumn('purchase_order', function(InventoryLog $inventoryLog) {
                if ($inventoryLog->purchaseOrder){
                    return '<a href="'.route('purchase.details',['purchase'=>$inventoryLog->purchase_order_id]).'" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-info-circle"></i> '.($inventoryLog->purchaseOrder->order_no ?? '').'</a>';
                }
            })
            ->addColumn('distribution_order', function(InventoryLog $inventoryLog) {
                if ($inventoryLog->distributionOrder){
                    return '<a href="'.route('distribution.details',['distributionOrder'=>$inventoryLog->distribution_order_id,'type'=>$inventoryLog->distributionOrder->type]).'" class="btn btn-warning bg-gradient-warning btn-sm"><i class="fa fa-info-circle"></i> '.($inventoryLog->distributionOrder->order_no ?? '').'</a>';
                }
            })

            ->addColumn('type', function(InventoryLog $inventoryLog) {
                if ($inventoryLog->type == 1){
                    return '<span class="badge badge-primary">Purchase In</span>';
                }elseif ($inventoryLog->type == 2) {
                    return '<span class="badge badge-warning">Distribution Out</span>';
                }elseif ($inventoryLog->type == 3){
                    return '<span class="badge badge-info">Utilize Stock</span>';
                }elseif ($inventoryLog->type == 4){
                    return '<span class="badge badge-success">Distribution Delivery Return</span>';
                }elseif ($inventoryLog->type == 5){
                    return '<span class="badge badge-success">Damage Product Refund</span>';
                }
            })
            ->addColumn('supplier_name', function(InventoryLog $inventoryLog) {
                return $inventoryLog->supplier->name ?? '';
            })
            ->addColumn('product_name', function(InventoryLog $inventoryLog) {
                return $inventoryLog->product->name ?? '';
            })
            ->rawColumns(['purchase_order','type','distribution_order'])
            ->toJson();
    }
}
