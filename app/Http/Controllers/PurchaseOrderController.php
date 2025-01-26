<?php

namespace App\Http\Controllers;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Imports\CommonExcelImport;
use App\Models\AccountHead;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $paymentModes = AccountHead::where('payment_mode','>',0)->get();   //old
        $paymentMode = AccountHead::find(1);
        $companies = Client::where('status', 1)->where('type',1)->get();
        return view('inventory_system.purchase.index',compact('paymentMode','companies'));
    }

    public function details(PurchaseOrder $purchase)
    {
        if (!auth()->user()->hasPermissionTo('purchase_list')) {
            abort(403, 'Unauthorized');
        }

        $purchase->load('purchaseItems');
        return view('inventory_system.purchase.details', compact('purchase'));
    }

    public function dataTable()
    {
        $query = PurchaseOrder::with( 'supplier');
        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if (request()->has('company') && request('company') != '') {
            $query->where('supplier_id', request('company'));
        }
        $query->orderBy('id', 'desc');
        return DataTables::eloquent($query)
            ->addColumn('action', function (PurchaseOrder $order) {
                $btn = '';
                if (auth()->user()->hasPermissionTo('purchase_payment')) {
                    if ($order->due > 0)
                        $btn .= '<a role="button" data-id="' . $order->id . '" data-total="' . $order->total . '" data-due="' . $order->due . '" data-order_no="' . $order->order_no . '" class="btn btn-primary bg-gradient-primary btn-sm supplier-pay">Pay</a>';
                }
                if (auth()->user()->hasPermissionTo('purchase_edit')) {
                    $btn .= ' <a href="' . route('purchase.edit', ['purchase' => $order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('purchase_list')) {
                    $btn .= ' <a href="' . route('purchase.details', ['purchase' => $order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-info-circle"></i></a>';
                }
                return $btn;
            })
            ->addColumn('supplier_name', function (PurchaseOrder $order) {
                return $order->supplier->name ?? '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_create')) {
            abort(403, 'Unauthorized');
        }
        $clients = Client::where('status', 1)->where('type',1)->get();
        $products = [];
        if ($request->company != ''){
            $products = Product::where('status', 1)
                ->where('supplier_id',$request->company)
                ->get();
        }

        return view('inventory_system.purchase.create', compact('clients'
            , 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_create')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            //'supplier' => 'required',
            'product_id.*' => 'required',
            'product_qty.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric',
            'product_selling_unit_price.*' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $request['supplier'] = $request->company;

            $purchase = new PurchaseOrder();
            $purchase->supplier_id = $request->supplier;
            $purchase->total = 0;
            $purchase->paid = 0;
            $purchase->due = 0;
            $purchase->notes = $request->notes;
            $purchase->user_id = auth()->id();
            $purchase->date = Carbon::parse($request->date);
            $purchase->save();
            $purchase->order_no = 'PO-' . date('Ymd') . '-' . $purchase->id;
            $purchase->save();

            $total = 0;
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($request->product_id[$key]);

                $purchaseItem = new PurchaseOrderItem();
                $purchaseItem->purchase_order_id = $purchase->id;
                $purchaseItem->product_code = $product->code;
                $purchaseItem->product_id = $product->id;
                $purchaseItem->quantity = $request->product_qty[$key];
                $purchaseItem->product_unit_price = $request->product_unit_price[$key];
                $purchaseItem->product_selling_unit_price = $request->product_selling_unit_price[$key];
                $purchaseItem->save();

                $total += ($request->product_qty[$key] * $request->product_unit_price[$key]);

                //Inventory
                $inventory = Inventory::where('product_id', $product->id)
                    ->first();
                if (!$inventory) {
                    $inventory = new Inventory();
                    $inventory->product_id = $product->id;
                    $inventory->company_id = $request->supplier;
                    $inventory->product_code = $product->code;
                    $inventory->quantity = $request->product_qty[$key];
                    $inventory->average_purchase_unit_price = $request->product_unit_price[$key];
                    $inventory->last_purchase_unit_price = $request->product_unit_price[$key];
                    $inventory->selling_price = $request->product_selling_unit_price[$key];
                    $inventory->save();
                } else {
                    $inventory->company_id = $request->supplier;
                    $inventory->last_purchase_unit_price = $request->product_unit_price[$key];
                    $inventory->selling_price = $request->product_selling_unit_price[$key];
                    $inventory->save();
                    $totalAvgPrice = ($inventory->quantity * $inventory->average_purchase_unit_price) + ($request->product_unit_price[$key] * $request->product_qty[$key]);
                    $inventory->average_purchase_unit_price = $totalAvgPrice / ($inventory->quantity + $request->product_qty[$key]);
                    $inventory->save();
                    $inventory->increment('quantity', $request->product_qty[$key]);
                }

                //Inventory Log
                $inventoryLog = new InventoryLog();
                $inventoryLog->type = 1;//purchase In
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->supplier_id = $request->supplier;
                $inventoryLog->purchase_order_id = $purchase->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->quantity = $request->product_qty[$key];
                $inventoryLog->unit_price = $request->product_unit_price[$key];
                $inventoryLog->product_code = $product->code;
                $inventoryLog->notes = $request->notes;
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();

            }
            $purchase->total = $total;
            $purchase->paid = 0;
            $purchase->due = $total;
            $purchase->save();

            //Journal Voucher
            $payeeId = AccountHead::where('supplier_id',$purchase->supplier_id)->first()->id ?? null;
            $voucherNoGroupSl =  $voucherNoGroupSl = Transaction::withTrashed()
                    ->where('voucher_type',VoucherType::$JOURNAL_VOUCHER)
                    ->max('voucher_no_group_sl') + 1;

            $voucherNo = 'JV-'.$voucherNoGroupSl;

            $voucher = new Voucher();
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $total;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->company_id = $request->supplier;
            $voucher->purchase_order_id = $purchase->id;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = AccountHead::find(113)->id ?? 113;//Assets: Increase in Inventory
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $request->supplier;
            $transaction->purchase_order_id = $purchase->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = $payeeId;//Liabilities: Increase in Accounts Payable
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $request->supplier;
            $transaction->purchase_order_id = $purchase->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('purchase.details', ['purchase' => $purchase->id])->with('success', 'Purchase created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('purchase.create',['company'=>$request->company])
                ->withInput()->with('error', 'An error occurred while creating the purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchase)
    {
        if (!auth()->user()->hasPermissionTo('purchase_edit')) {
            abort(403, 'Unauthorized');
        }
        $clients = Client::where('status', 1)->where('type',1)->get();

        $products = Product::where('status', 1)
                ->where('supplier_id',$purchase->supplier_id)
                ->get();


        return view('inventory_system.purchase.edit', compact('clients'
            , 'products','purchase'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchase)
    {

        if (!auth()->user()->hasPermissionTo('purchase_edit')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            //'supplier' => 'required',
            'product_id.*' => 'required',
            'product_qty.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric',
            'product_selling_unit_price.*' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {


            //Remove Purchase Items
            if ($request->purchase_item_id){
                $removePurchaseItems = PurchaseOrderItem::where('purchase_order_id',$purchase->id)
                    ->whereNotIn('id',$request->purchase_item_id)->get();

                foreach ($removePurchaseItems as $key => $removePurchaseItem){
                    $inventory = Inventory::where('product_id', $removePurchaseItem->product_id)
                    ->first();
                    if ($inventory->quantity < $removePurchaseItem->quantity){
                        $stockInsufficient = 'This '.($removePurchaseItem->product->name ?? '').' remove product inventory quantity is less';
                        throw new \Exception($stockInsufficient);
                    }
                }

                foreach ($removePurchaseItems as $key => $removePurchaseItem){
                    $inventory = Inventory::where('product_id', $removePurchaseItem->product_id)
                        ->first();
                    $removeInventoryLog = InventoryLog::where('purchase_order_id',$removePurchaseItem->purchase_order_id)
                        ->where('product_id',$removePurchaseItem->product_id)
                        ->first();
                    $inventory->decrement('quantity',$removePurchaseItem->quantity);

                    if ($removeInventoryLog){
                        $removeInventoryLog->delete();
                    }

                    $removePurchaseItem->delete();
                }

                $decreamentExistItems = PurchaseOrderItem::where('purchase_order_id',$purchase->id)
                ->whereIn('id',$request->purchase_item_id)->get();

                foreach ($decreamentExistItems as $key => $decreamentExistItem){
                    $product = Product::find($decreamentExistItem->product_id);
                    $inventory = Inventory::where('product_id', $decreamentExistItem->product_id)
                    ->first();
                    if ($inventory->quantity < $decreamentExistItem->quantity){
                        $stockInsufficient = 'This '.($product->name).'edit product inventory quantity is less';
                        throw new \Exception($stockInsufficient);
                    }else{
                        $inventory->decrement('quantity',$decreamentExistItem->quantity);
                    }

                }
            }

            $request['supplier'] = $request->company;

            //$purchase->supplier_id = $request->supplier;
            $purchase->total = 0;
            $purchase->paid = 0;
            $purchase->due = 0;
            $purchase->notes = $request->notes;
            $purchase->user_id = auth()->id();
            $purchase->date = Carbon::parse($request->date);
            $purchase->save();

            //Delete Firstly
            InventoryLog::where('purchase_order_id',$purchase->id)->delete();

            $total = 0;
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($request->product_id[$key]);

                $purchaseItem = PurchaseOrderItem::where('id',$request->purchase_item_id[$key] ?? null)->first();

                // $previousPurQty = $purchaseItem->quantity ?? 0;
                if (!$purchaseItem){
                    $purchaseItem = new PurchaseOrderItem();
                }
                $purchaseItem->purchase_order_id = $purchase->id;
                $purchaseItem->product_code = $product->code;
                $purchaseItem->product_id = $product->id;
                $purchaseItem->quantity = $request->product_qty[$key];
                $purchaseItem->product_unit_price = $request->product_unit_price[$key];
                $purchaseItem->product_selling_unit_price = $request->product_selling_unit_price[$key];
                $purchaseItem->save();

                $total += ($request->product_qty[$key] * $request->product_unit_price[$key]);

                //Inventory
                $inventory = Inventory::where('product_id', $product->id)
                    ->first();

                if (!$inventory) {
                    $inventory = new Inventory();
                    $inventory->company_id = $request->supplier;
                    $inventory->product_id = $product->id;
                    $inventory->product_code = $product->code;
                    $inventory->quantity = $request->product_qty[$key];
                    $inventory->average_purchase_unit_price = $request->product_unit_price[$key];
                    $inventory->last_purchase_unit_price = $request->product_unit_price[$key];
                    $inventory->selling_price = $request->product_selling_unit_price[$key];
                    $inventory->save();
                } else {
                    $inventory->company_id = $request->supplier;
                    $inventory->last_purchase_unit_price = $request->product_unit_price[$key];
                    $inventory->selling_price = $request->product_selling_unit_price[$key];
                    $inventory->save();
                    $totalAvgPrice = ($inventory->quantity * $inventory->average_purchase_unit_price) + ($request->product_unit_price[$key] * $request->product_qty[$key]);
                    $inventory->average_purchase_unit_price = $totalAvgPrice / ($inventory->quantity + $request->product_qty[$key]);
                    $inventory->save();

                    // if ($previousPurQty != $request->product_qty[$key]) {
                    //     if ($inventory->quantity < $request->product_qty[$key]){
                    //         $stockInsufficient = 'This '.($product->name).'edit product inventory quantity is less';
                    //         throw new \Exception($stockInsufficient);
                    //     }else{
                    //         $inventory->decrement('quantity',$previousPurQty);
                    //         $inventory->increment('quantity', $request->product_qty[$key]);
                    //     }

                    // }
                    $inventory->increment('quantity', $request->product_qty[$key]);

                }

                //Inventory Log

                $inventoryLog = new InventoryLog();
                $inventoryLog->type = 1;//purchase In
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->supplier_id = $request->supplier;
                $inventoryLog->purchase_order_id = $purchase->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->quantity = $request->product_qty[$key];
                $inventoryLog->unit_price = $request->product_unit_price[$key];
                $inventoryLog->product_code = $product->code;
                $inventoryLog->notes = $request->notes;
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();

            }
            $purchase->total = $total;
            $purchase->paid = 0;
            $purchase->due = $total;
            $purchase->save();

            //Journal Voucher
            $payeeId = AccountHead::where('supplier_id',$purchase->supplier_id)->first()->id ?? null;

            //Updating Voucher
            $voucher = Voucher::where('purchase_order_id',$purchase->id)
                ->where('voucher_type',VoucherType::$JOURNAL_VOUCHER)
                ->first();
            $voucherNoGroupSl =  $voucher->voucher_no_group_sl;
            $voucherNo = $voucher->voucher_no;


            $voucher->amount = $total;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->company_id = $purchase->supplier_id;
            $voucher->purchase_order_id = $purchase->id;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit
            $transaction = Transaction::where('voucher_id',$voucher->id)->first();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = AccountHead::find(113)->id ?? 113;//Assets: Increase in Inventory
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $purchase->supplier_id;
            $transaction->purchase_order_id = $purchase->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit
            $transaction = Transaction::where('voucher_id',$voucher->id)
                ->orderBy('id','desc')
                ->first();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = $payeeId;//Liabilities: Increase in Accounts Payable
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $total;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $purchase->supplier_id;
            $transaction->purchase_order_id = $purchase->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('purchase.details', ['purchase' => $purchase->id])->with('success', 'Purchase updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('purchase.edit',['purchase'=>$purchase->id,'company'=>$request->company])
                ->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        //
    }

    public function payment(Request $request)
    {

        // Validate the request data
         $rules = [
            'order_no' => 'required',
            'payment_mode' => 'required',
            'payment' => 'required|numeric|min:.01|max:' . $request->due_hidden,
            'due' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];
        $paymentMode = 0;
        $paymentModeType = 0;
         if ($request->payment_mode != ''){
             [$paymentMode,$paymentModeType] = explode('|',$request->payment_mode);
         }
       if ($paymentModeType == 1){
           //bank
           $rules['cheque_no'] = 'required|max:255';
       }
       $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {
            if (!auth()->user()->hasPermissionTo('purchase_payment')) {
                abort(403, 'Unauthorized');
            }

            $voucherNoGroupSl = Transaction::withTrashed()
                    ->where('voucher_type',VoucherType::$PAYMENT_VOUCHER)
                    ->max('voucher_no_group_sl') + 1;
            $voucherNo = 'PV-'.$voucherNoGroupSl;

            $purchaseOrder = PurchaseOrder::find($request->order_id);

            $purchaseOrder->increment('paid', $request->payment);
            $purchaseOrder->decrement('due', $request->payment);

           $payeeId = AccountHead::where('supplier_id',$purchaseOrder->supplier_id)->first()->id ?? null;

            $voucher = new Voucher();
            $voucher->payment_type_id = $paymentModeType;
            $voucher->payment_account_head_id = $paymentMode;//Payment Mode
            $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $voucher->amount = $request->payment;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->company_id = $purchaseOrder->supplier_id;
            $voucher->purchase_order_id = $purchaseOrder->id;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            $transaction->account_head_id = $payeeId;//Liabilities: Decrease in Accounts Payable
            $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $purchaseOrder->supplier_id;
            $transaction->purchase_order_id = $purchaseOrder->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            $transaction->account_head_id = $paymentMode;//Assets: Decrease in Cash/Bank
            $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $purchaseOrder->supplier_id;
            $transaction->purchase_order_id = $purchaseOrder->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status' => true,
                'redirect_url' => route('voucher.details',['voucher'=>$voucher->id]),
                'message' => 'Supplier payment successful',
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the supplier payment :' . $e->getMessage(),
            ]);
        }
    }
}
