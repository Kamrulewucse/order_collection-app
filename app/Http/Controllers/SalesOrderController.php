<?php

namespace App\Http\Controllers;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\AccountHead;
use App\Models\Cash;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Client;
use App\Models\LocationAddressInfo;
use App\Models\SalePayment;
use App\Models\Transaction;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use SakibRahaman\DecimalToWords\DecimalToWords;

class SalesOrderController extends Controller
{

    public function index(Request $request)
    {
        if ($request->type == 1) {
            $pageTitle = 'Sales Orders';
            $permission = 'distribution_list';
            $permission_create = 'distribution_create';
        } else {
            $pageTitle = 'Damage Product Return';
            $permission = 'distribution_damage_product_return_list';
            $permission_create = 'distribution_damage_product_return_create';
        }
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        $srs = Client::where('type',2)->get(); //here type=2 for SR
        return view('sales_order.index', compact(
            'pageTitle',
            'permission_create',
            'srs'
        ));
    }
    public function dataTable()
    {
        $query = SaleOrder::with('sr','client')
            ->where('type', \request('type'));
        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if (request()->has('sr') && request('sr') != '') {
            $query->where('sr_id', request('sr'));
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (SaleOrder $saleOrder) {
                $btn = '';
                if ($saleOrder->type == 1) {
                    $icon = '<i class="fa fa-info-circle"></i>';
                    if ($saleOrder->status == 1) {
                        $icon = '<i class="fa fa-plus"></i>';
                    }
                    $btn .= ' <a  href="' . route('sales-order.day_close', ['saleOrder' => $saleOrder->id, 'type' => $saleOrder->type]) . '" class="dropdown-item">' . $icon . ' Invoice</a>';
                }

                if ($saleOrder->status == 1) {
                    $btn .= ' <a  data-id="' . $saleOrder->id . '" role="button" class="dropdown-item in-transit"><i class="fa fa-info-circle"></i> In Transit</a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('sr_name', function (SaleOrder $saleOrder) {
                return $saleOrder->sr->name ?? '';
            })
            ->addColumn('client_name', function (SaleOrder $saleOrder) {
                return $saleOrder->client->name ?? '';
            })
            ->addColumn('status', function (SaleOrder $saleOrder) {
                if ($saleOrder->status == 1) {
                    return '<span class="badge badge-warning">Pending</span>';
                } else if ($saleOrder->status == 2) {
                    return '<span class="badge badge-primary">In Transit</span>';
                } else {
                    return '<span class="badge badge-success">Completed</span>';
                }
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $srs = Client::where('status', 1)->where('type', 2)->get(); //type=2 is SR

        $clients = Client::where('status', 1)->where('type', 4)->get(); //type=4 is Client
        $products = [];

        $products = Product::where('status', 1)->get();


        if ($request->type == 1) {
            $pageTitle = 'SR Order Create';
            $permission = 'distribution_create';
        } else {
            $pageTitle = 'Damage Product Return';
            $permission = 'distribution_damage_product_return_create';
        }
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        return view('sales_order.create', compact(
            'srs',
            'products',
            'pageTitle',
            'clients'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return($request->all());
        if ($request->type == 1) {
            $permission = 'distribution_create';
        } else {
            $permission = 'distribution_damage_product_return_create';
        }
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'sr' => 'required',
            'client' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'product_id.*' => 'required',
            'product_qty.*' => 'required|numeric',
            'purchase_price.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $saleOrder = new SaleOrder();
            $saleOrder->type = $request->type;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->client_id = $request->client;
            $saleOrder->total = 0;
            $saleOrder->paid = 0;
            $saleOrder->due = 0;
            $saleOrder->costing_total = 0;
            $saleOrder->notes = $request->notes;
            $saleOrder->created_by = auth()->id();
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->save();
            if ($request->type == 1) {
                $saleOrder->order_no = 'Sale-' . date('Ymd') . '-' . $saleOrder->id;

                $latitude = $request->latitude;
                $longitude = $request->longitude;

                $location_address = getLocationName($latitude, $longitude);

                $locationAddressInfo = new LocationAddressInfo();
                $locationAddressInfo->sale_order_id = $saleOrder->id;
                $locationAddressInfo->sr_id = $request->sr;
                $locationAddressInfo->client_id = $request->client;
                $locationAddressInfo->invoice_time = Carbon::now();
                $locationAddressInfo->location_address = $location_address;
                $locationAddressInfo->latitude = $request->latitude;
                $locationAddressInfo->longitude = $request->longitude;
                $locationAddressInfo->save();
            }
            $saleOrder->save();

            $costingTotal = 0;
            $total = 0;
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($request->product_id[$key]);

                $product->update([
                    'purchase_price' => $request->purchase_unit_price[$key],
                    'selling_price' => $request->selling_unit_price[$key],
                ]);
                $saleOrderItem = new SaleOrderItem();
                $saleOrderItem->sale_order_id = $saleOrder->id;
                $saleOrderItem->client_id = $request->client;
                $saleOrderItem->sr_id = $request->sr;
                $saleOrderItem->product_id = $product->id;
                $saleOrderItem->product_code = $product->code;
                $saleOrderItem->damage_quantity = $request->damage_return_product_qty[$key] ?? 0;
                $saleOrderItem->sr_sale_quantity = $request->product_qty[$key];
                $saleOrderItem->sale_quantity = 0;
                $saleOrderItem->purchase_unit_price = $request->purchase_unit_price[$key];
                $saleOrderItem->selling_unit_price = $request->selling_unit_price[$key];
                $saleOrderItem->save();

                $total += ($request->product_qty[$key] * $request->selling_unit_price[$key]);
                $costingTotal += ($request->product_qty[$key] ?? 0) * $request->purchase_unit_price[$key];

                if ($request->type == 1) {
                    //Inventory Log
                    $inventoryLog = new InventoryLog();
                    $inventoryLog->type = 2; //Distribution Out
                    $inventoryLog->sr_id = $request->sr;
                    $inventoryLog->client_id = $request->client;
                    $inventoryLog->sale_order_id = $saleOrder->id;
                    $inventoryLog->product_id = $product->id;
                    $inventoryLog->quantity = $request->product_qty[$key];
                    $inventoryLog->unit_price = $request->selling_unit_price[$key];
                    $inventoryLog->product_code = $product->code;
                    $inventoryLog->notes = $request->notes;
                    $inventoryLog->user_id = auth()->id();
                    $inventoryLog->date = Carbon::parse($request->date);
                    $inventoryLog->save();
                }
            }
            $saleOrder->total = $total;
            $saleOrder->paid = 0;
            $saleOrder->due = $total;
            $saleOrder->costing_total = $costingTotal;
            $saleOrder->save();


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('sales-order.day_close', ['saleOrder' => $saleOrder->id, 'type' => $request->type])->with('success', 'Distribution sales created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('sales-order.create', ['type' => $request->type, 'company' => $request->company])->withInput()->with('error', $e->getMessage());
        }
    }

    public function salesInvoice(SaleOrder $saleOrder, Request $request)
    {

        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        $saleOrder->load('saleOrderItems');
        if ($saleOrder->type != 1) {
            abort('404');
        }
        // dd('hi');
        $products = Product::whereIn('id', $saleOrder->saleOrderItems->pluck('product_id'))->get();
        $clients = Client::where('type',4)->get();
        $pageTitle = 'SR Sales Invoice';


        if ($saleOrder->status == 0) {
            if ($request->status == 1) {
                $saleOrder->status = 0;
            } else {
                $saleOrder->status = 1;
            }
        } else {
            $saleOrder->hold_release = 1;
        }



        return view('sales_order.day_close', compact(
            'saleOrder',
            'pageTitle',
            'products',
            'clients'
        ));
    }


    public function finalSalePost(SaleOrder $saleOrder, Request $request)
    {
        // return($request->all());
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($saleOrder->type != 1) {
            abort('404');
        }
        if ($saleOrder->status == 1) {
            abort('404');
        }

        $request->validate([
            'damage_quantity.*' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $costingTotal = 0;

            foreach ($request->product_id as $key => $reqProduct) {
                $saleOrderItem = SaleOrderItem::find($request->sale_order_item_id[$key]);

                $saleOrderItem->sale_quantity = $request->sr_sale_quantity[$key] - $request->damage_quantity[$key];
                $saleOrderItem->damage_quantity = $request->damage_quantity[$key];
                $saleOrderItem->save();

                $total += $saleOrderItem->sale_quantity * $saleOrderItem->selling_unit_price;
                $costingTotal += $saleOrderItem->sale_quantity * $saleOrderItem->purchase_unit_price;
            }

            $saleOrder->type = 2;
            $saleOrder->total = $total;
            $saleOrder->paid = $request->paid;
            $saleOrder->due = $total - $saleOrder->paid;
            $saleOrder->save();

            if($request->paid > 0){
                $transaction = new Transaction();
                $transaction->payment_type = 1;
                $transaction->transaction_type = 1; // 1 for sale or cash in
                $transaction->amount = $saleOrder->paid;
                $transaction->sr_id = $saleOrder->sr_id;
                $transaction->client_id = $saleOrder->client_id;
                $transaction->date = Carbon::now()->format('Y-m-d');
                $transaction->sale_order_id = $saleOrder->id;
                $transaction->user_id = auth()->id();
                $transaction->save();

                Cash::first()->increment('amount',$saleOrder->paid);
            }


            DB::commit();

            return redirect()
                ->route('sales-order.day_close', ['saleOrder' => $saleOrder->id, 'type' => $saleOrder->type])
                ->with('message', 'Sale order confirm successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function inTransitPost(SaleOrder $saleOrder, Request $request)
    {

        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($saleOrder->type != 1) {
            abort('404');
        }
        if ($saleOrder->status != 0) {
            abort('404');
        }

        DB::beginTransaction();
        try {

            $saleOrder->status=1;
            $saleOrder->in_transit_by= auth()->id();
            $saleOrder->in_transit_date=Carbon::now()->format('Y-m-d');
            $saleOrder->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order In Transit successfully'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function customerSaleEntry(SaleOrder $saleOrder, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        $saleOrder->load('distributionOrderItems');
        if ($saleOrder->type != 1) {
            abort('404');
        }
        if ($saleOrder->status == 1) {
            abort('404');
        }
        $products = Product::whereIn('id', $saleOrder->distributionOrderItems->pluck('product_id'))->get();
        $customers = Client::where('type', 3) //3=Customer
            ->get();
        $pageTitle = 'Distribution Customer Bill Add: ' . $saleOrder->order_no;

        // $paymentModes = AccountHead::where('payment_mode','>',0)->get();
        $paymentMode = AccountHead::where('id', 1)->first();

        return view('sales_order.customer_sale_entry', compact(
            'saleOrder',
            'pageTitle',
            'products',
            'customers',
            'paymentMode'
        ));
    }
    public function customerSaleEntryPost(SaleOrder $saleOrder, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($saleOrder->type != 1) {
            abort('404');
        }
        if ($saleOrder->status == 1) {
            abort('404');
        }
        $rules = [
            'total_amount.*' => 'required|numeric|min:0.01',
            'paid_amount.*' => 'required|numeric|min:0',
            'due_amount.*' => 'required|numeric|min:0',
        ];
        if (!$request->total_amount) {
            return redirect()->back()->withInput()->with('error', 'Empty data !');
        }
        if (array_sum($request->paid_amount) > 0) {
            $rules['payment_mode'] = 'required';
        }

        $request->validate($rules);

        // dd($request->payment_mode);
        DB::beginTransaction();
        try {

            if (abs(floatval($saleOrder->total) - array_sum($request->total_amount)) > 0.0001) {
                return redirect()->back()->withInput()->with('error', 'Remaining amount not Zero(0) Or Customer sales bill total and disbursement total are not equal');
            }

            // if ($saleOrder->total < array_sum($request->total_amount)) {
            //     $exceptionMsg = 'customer sales bill total is greater than distribution total amount';
            //     throw new \Exception($exceptionMsg);
            // }
            if($request->customer_id){
                 SaleOrder::where('distribution_order_id', $saleOrder->id)
                ->whereNotIn('customer_id', $request->customer_id)
                ->delete();
            }

            $total = 0;
            $costingTotal = 0;
            $counter = 0;

            foreach ($request->customer_id as $key => $reqCustomerId) {
                $saleOrder = SaleOrder::where('customer_id', $request->customer_id[$counter])
                    ->where('distribution_order_id', $saleOrder->id)
                    ->first();

                $customer = Client::find($request->customer_id[$counter]);



                if (!$saleOrder) {
                    $saleOrder = new SaleOrder();
                    $saleOrder->distribution_order_id = $saleOrder->id;
                    $saleOrder->company_id = $saleOrder->company_id;
                    $saleOrder->customer_id = $request->customer_id[$counter];
                    $saleOrder->notes = $request->notes;
                    $saleOrder->user_id = auth()->id();
                    $saleOrder->date = now();
                    $saleOrder->total = 0;
                    $saleOrder->paid = 0;
                    $saleOrder->due = 0;
                    $saleOrder->save();
                }
                $saleOrder->total = $request->total_amount[$counter];
                $saleOrder->paid = $request->paid_amount[$counter];
                $saleOrder->due = $request->due_amount[$counter];
                $saleOrder->save();

                if ($request->paid_amount[$counter] > 0){
                    if ($request->payment_mode != ''){
                        [$paymentMode, $paymentModeType] = explode('|', $request->payment_mode);
                    }
                    $saleOrder->payment_account_head_id = $paymentMode;
                    $saleOrder->payment_type_id = $paymentModeType;
                    $saleOrder->cheque_no = $request->cheque_no ?? null;
                    $saleOrder->save();
                }

                if (!$saleOrder->order_no) {
                    $saleOrder->order_no = 'Sale-' . date('Ymd') . '-' . $saleOrder->id;
                    $saleOrder->save();
                }

                //if Any Payment
                // $voucher = Voucher::where('sale_order_id', $saleOrder->id)
                //     ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
                //     ->first();
                // if ($voucher && $request->paid_amount[$counter] == 0){
                //     Transaction::where('voucher_id',$voucher->id)->delete();
                //     $voucher->delete();
                // }else{
                //     if ($request->paid_amount[$counter] > 0){
                //         $paymentMode = 0;
                //         $paymentModeType = 0;
                //         if ($request->payment_mode != ''){
                //             [$paymentMode,$paymentModeType] = explode('|',$request->payment_mode);
                //         }
                //         $payeeId = AccountHead::where('supplier_id',$saleOrder->customer_id)->first()->id ?? null;
                //         if (!$voucher){
                //             $voucherNoGroupSlQuery = Transaction::withTrashed();
                //             $voucherNoGroupSl = $voucherNoGroupSlQuery->where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                //                     ->max('voucher_no_group_sl') + 1;
                //             $voucherNo = 'MR-'.$voucherNoGroupSl;

                //             $voucher = new Voucher();
                //             $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                //             $voucher->voucher_no = $voucherNo;
                //         }else{
                //             $voucherNoGroupSl = $voucher->voucher_no_group_sl;
                //             $voucherNo = $voucher->voucher_no;
                //         }
                //         $voucher->payment_type_id = $paymentModeType;
                //         $voucher->payment_account_head_id = $paymentMode;//Payment Mode
                //         $voucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                //         $voucher->amount = $request->paid_amount[$counter];
                //         $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
                //         $voucher->account_head_payee_depositor_id = $payeeId;
                //         $voucher->company_id = $saleOrder->company_id;
                //         $voucher->customer_id = $saleOrder->customer_id;
                //         $voucher->sale_order_id = $saleOrder->id;
                //         $voucher->cheque_no = $request->cheque_no;
                //         $voucher->notes = $request->notes;
                //         $voucher->user_id = auth()->id();
                //         $voucher->save();

                //         //Debit: Cash/Bank (Asset account)
                //         $transaction = Transaction::where('voucher_id',$voucher->id)->first();
                //         if (!$transaction){
                //             $transaction = new Transaction();
                //         }
                //         $transaction->voucher_id = $voucher->id;
                //         $transaction->payment_type_id = $paymentModeType;
                //         $transaction->payment_account_head_id = $paymentMode;//Cash/Bank (Asset account)
                //         $transaction->account_head_id = $paymentMode;
                //         $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                //         $transaction->transaction_type = TransactionType::$DEBIT;
                //         $transaction->amount = $request->paid_amount[$counter];
                //         $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                //         $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                //         $transaction->voucher_no = $voucherNo;
                //         $transaction->account_head_payee_depositor_id = $payeeId;
                //         $transaction->company_id = $saleOrder->company_id;
                //         $transaction->customer_id = $saleOrder->customer_id;
                //         $transaction->sale_order_id = $saleOrder->id;
                //         $transaction->notes = $request->notes;
                //         $transaction->user_id = auth()->id();
                //         $transaction->save();

                //         $firstTransaction = $transaction;

                //         //Credit: Accounts Receivable (Asset account)
                //         $transaction = Transaction::where('voucher_id',$voucher->id)
                //             ->where('id','!=',$firstTransaction->id)
                //             ->first();
                //         if (!$transaction){
                //             $transaction = new Transaction();
                //         }
                //         $transaction->voucher_id = $voucher->id;
                //         $transaction->payment_type_id = $paymentModeType;
                //         $transaction->payment_account_head_id = $paymentMode;//Accounts Receivable (Asset account)
                //         $transaction->account_head_id = $payeeId;//
                //         $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                //         $transaction->transaction_type = TransactionType::$CREDIT;
                //         $transaction->amount = $request->paid_amount[$counter];
                //         $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                //         $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                //         $transaction->voucher_no = $voucherNo;
                //         $transaction->account_head_payee_depositor_id = $payeeId;
                //         $transaction->company_id = $saleOrder->company_id;
                //         $transaction->customer_id = $saleOrder->customer_id;
                //         $transaction->sale_order_id = $saleOrder->id;
                //         $transaction->notes = $request->notes;
                //         $transaction->user_id = auth()->id();
                //         $transaction->save();
                //     }

                // }


                $counter++;
            }

            //reset
            $saleOrder->increment('due', $saleOrder->paid);
            $saleOrder->decrement('paid', $saleOrder->paid);
            //Update
            $saleOrder->decrement('due', array_sum($request->paid_amount));
            $saleOrder->increment('paid', array_sum($request->paid_amount));

            // foreach ($saleOrder->saleOrders as $saleOrder){
            //     //1.Journal Voucher: DSR and Customer Adjust
            //     $payeeId = AccountHead::where('supplier_id', $saleOrder->dsr_id)->first()->id ?? null;
            //     $clientId = AccountHead::where('supplier_id', $saleOrder->customer_id)->first()->id ?? null;

            //     $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
            //             ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
            //             ->max('voucher_no_group_sl') + 1;

            //     $voucherNo = 'JV-' . $voucherNoGroupSl;

            //     $voucher = Voucher::where('voucher_type',VoucherType::$JOURNAL_VOUCHER)
            //         ->where('sale_order_id',$saleOrder->id)->first();
            //     $voucherNew = true;
            //     if (!$voucher){
            //         $voucher = new Voucher();
            //         $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            //         $voucher->voucher_no = $voucherNo;
            //     }else{
            //         $voucherNew = false;
            //         $voucherNo = $voucher->voucher_no;
            //         $voucherNoGroupSl = $voucher->voucher_no_group_sl;
            //     }
            //     $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $voucher->amount = $saleOrder->total;
            //     $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $voucher->account_head_payee_depositor_id = $clientId;
            //     $voucher->company_id = $saleOrder->company_id;
            //     $voucher->customer_id = $saleOrder->customer_id;
            //     $voucher->sale_order_id = $saleOrder->id;
            //     $voucher->notes = $request->notes;
            //     $voucher->user_id = auth()->id();
            //     $voucher->save();

            //     $transaction = Transaction::where('voucher_id',$voucher->id)->first();
            //     //DEBIT:Accounts Receivable (Asset account)
            //     if ($voucherNew){
            //         $transaction = new Transaction();
            //     }
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = $clientId;//Customer
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$DEBIT;
            //     $transaction->amount = $saleOrder->total;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $clientId;
            //     $transaction->company_id = $saleOrder->company_id;
            //     $transaction->customer_id = $saleOrder->customer_id;
            //     $transaction->sale_order_id = $saleOrder->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();

            //     $transaction = Transaction::where('voucher_id',$voucher->id)->orderBy('id','desc')->first();
            //     //CREDIT:Accounts Receivable (Asset account)
            //     if ($voucherNew){
            //         $transaction = new Transaction();
            //     }
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = $payeeId;
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$CREDIT;
            //     $transaction->amount = $saleOrder->total;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $clientId;
            //     $transaction->company_id = $saleOrder->company_id;
            //     $transaction->customer_id = $saleOrder->customer_id;
            //     $transaction->sale_order_id = $saleOrder->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();
            // }

            DB::commit();
            return redirect()->back()
                ->with('message', 'Customer sale bill added successfully');
        } catch (\Exception $exception) {
            dd($exception);
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function customerDamageProductEntry(SaleOrder $saleOrder, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        $saleOrder->load('distributionOrderItems');
        if ($saleOrder->type != 1) {
            abort('404');
        }
        if ($saleOrder->status == 1) {
            abort('404');
        }

        $pageTitle = 'Customer Damage Product Entry Add: ' . $saleOrder->order_no;

        return view('sales_order.customer_damage_product_entry', compact(
            'saleOrder',
            'pageTitle'
        ));
    }
    public function customerDamageProductEntryPost(SaleOrder $saleOrder, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($saleOrder->type != 1) {
            abort('404');
        }
        if ($saleOrder->status == 1) {
            abort('404');
        }

        $request->validate([
            'damage_quantity.*' => 'required|numeric'
        ]);
        DB::beginTransaction();
        try {
            $total = 0;
            $costingTotal = 0;
            foreach ($request->product_id as $key => $reqProduct) {

                $saleOrderItem = SaleOrderItem::where('id', $request->distribution_order_item_id[$key])->first();

                if ($saleOrderItem->damage_quantity < ($request->damage_quantity[$key] ?? 0)) {
                    $errorMsg = ($saleOrderItem->product->name) . ' return quantity is grater than delivery quantity';
                    throw new \Exception($errorMsg);
                }
                $inventory = Inventory::where('product_id', $saleOrderItem->product_id)->first();
                $inventory->increment('quantity', $saleOrderItem->damage_damage_quantity);
                $inventoryLog = InventoryLog::where('distribution_order_id', $saleOrderItem->distribution_order_id)
                    ->where('product_id', $saleOrderItem->product_id)
                    ->where('type', 6)
                    ->first();
                if ($inventoryLog) {
                    $inventoryLog->delete();
                }

                $saleOrderItem->damage_damage_quantity = ($request->damage_quantity[$key] ?? 0);
                $saleOrderItem->save();

                $inventory = Inventory::where('product_id', $saleOrderItem->product_id)->first();
                $inventory->decrement('quantity', $request->damage_quantity[$key] ?? 0);
                if ($request->damage_quantity[$key] ?? 0) {
                    //Inventory Log
                    $inventoryLog = new InventoryLog();
                    $inventoryLog->type = 6; // Damage product refund return
                    $inventoryLog->inventory_id = $inventory->id;
                    $inventoryLog->supplier_id = $saleOrder->dsr_id;
                    $inventoryLog->distribution_order_id = $saleOrderItem->distribution_order_id;
                    $inventoryLog->product_id = $saleOrderItem->product_id;
                    $inventoryLog->quantity = $request->damage_quantity[$key];
                    $inventoryLog->unit_price = $saleOrderItem->selling_unit_price;
                    $inventoryLog->product_code = $saleOrderItem->product->code;
                    $inventoryLog->notes = 'Damage product delivery refund return';
                    $inventoryLog->user_id = auth()->id();
                    $inventoryLog->date = Carbon::now();
                    $inventoryLog->save();
                }


                $total += ($saleOrderItem->damage_quantity - $saleOrderItem->damage_damage_quantity) * $saleOrderItem->selling_unit_price;
                $costingTotal += ($saleOrderItem->damage_quantity - $saleOrderItem->damage_damage_quantity) * $saleOrderItem->purchase_unit_price;
            }

            $vouchers = Voucher::where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                ->where('distribution_order_id', $saleOrder->id)
                ->skip(2)
                ->take(2)
                ->get();
            foreach ($vouchers as $key => $voucher) {
                $voucher->amount = ($key == 0 ? $total : $costingTotal);
                $voucher->save();
                foreach ($voucher->transactions as $transaction) {
                    $transaction->amount = ($key == 0 ? $total : $costingTotal);
                    $transaction->save();
                }
            }

            DB::commit();
            return redirect()->route('sales-order.details', ['saleOrder' => $saleOrder->id, 'type' => $saleOrder->type])
                ->with('message', 'Customer damage product updated successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->route('sales-order.customer_damage_product_entry', ['saleOrder' => $saleOrder->id, 'type' => $saleOrder->type])->withInput()
                ->with('error', $exception->getMessage());
        }
    }
    public function salePaymentDetails(SalePayment $salePayment)
    {
        $salePayment->amount_in_word = DecimalToWords::convert(
            $salePayment->amount,
            'Taka',
            'Poisa'
        );
        // dd($salePayment);
        return view('sales_order.details', compact('salePayment'));
    }
    // public function details(SaleOrder $saleOrder, Request $request)
    // {


    //     $paymentModes = AccountHead::where('payment_mode', '>', 0)->get();

    //     return view('sales_order.details', compact(
    //         'saleOrder',
    //         'pageTitle',
    //         'paymentModes'
    //     ));
    // }
    public function finalDetails(SaleOrder $saleOrder, Request $request)
    {

        $saleOrder->load('distributionOrderItems');
        if ($request->type == 1) {
            $pageTitle = 'Distribution Final Report';
            $permission = 'distribution_list';
        } else {
            $pageTitle = 'Damage Product Return Details';
            $permission = 'distribution_damage_product_return_list';
        }
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }

        return view('sales_order.final_details', compact(
            'saleOrder',
            'pageTitle'
        ));
    }
    public function payment(Request $request)
    {
        // dd($request->all());
        if ($request->sales_order) {
            $saleOrder = SaleOrder::find($request->sales_order);
            $request['due_hidden'] = $saleOrder->due;
        }

        // Validate the request data
        $rules = [
            'sales_order' => 'required',
            'payment' => 'required|numeric|min:.01|max:' . $request->due_hidden,
            'due' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];

        $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {

            $saleOrder = SaleOrder::find($request->sales_order);

            $salePayment = new SalePayment();
            $salePayment->sr_id = $saleOrder->sr_id;
            $salePayment->sale_order_id = $saleOrder->id;
            $salePayment->client_id = $saleOrder->client_id;
            $salePayment->payment_type = 1;
            $salePayment->transaction_type = 1;
            $salePayment->amount = $request->payment;
            $salePayment->date = Carbon::now()->format('Y-m-d');
            $salePayment->note = $request->notes;
            $salePayment->user_id = auth()->id();
            $salePayment->save();

            $transaction = new Transaction();
            $transaction->payment_type = 1;
            $transaction->transaction_type = 1; // 1 for sale or cash in
            $transaction->amount = $request->payment;
            $transaction->sr_id = $saleOrder->sr_id;
            $transaction->client_id = $saleOrder->client_id;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->sale_payment_id = $salePayment->id;
            $transaction->user_id = auth()->id();
            $transaction->save();

            Cash::first()->increment('amount',$request->payment);

            //Sale Order Update
            $saleOrder->increment('paid', $request->payment);
            $saleOrder->decrement('due', $request->payment);


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status' => true,
                'redirect_url' => route('sales-order.details', ['salePayment' => $salePayment->id]),
                'message' => 'Payment successful',
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the payment :' . $e->getMessage(),
            ]);
        }
    }
    public function customerPayments(Request $request)
    {

        $clients = Client::where('type', 4)->get(); //type=4 for client
        return view('sales_order.customer_payments', compact(
            'clients'
        ));
    }
    public function customerPaymentsDataTable()
    {
        $query = Client::where('status', 1)
            ->where('type', 4) // type=4 for client
            ->whereHas('saleOrders')
            ->with('saleOrders')
            ->with('sr');

        // Date filtering
        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [
                Carbon::parse(request('start_date'))->format('Y-m-d'),
                Carbon::parse(request('end_date'))->format('Y-m-d')
            ]);
        }

        // Client filtering
        if (request()->has('client') && request('client') != '') {
            $query->where('id', request('client'));
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (Client $client) {
                // Get the sale orders and calculate sums
                $saleOrders = $client->saleOrders();
                $total = $saleOrders->sum('total') ?? 0;
                $paid = $saleOrders->sum('paid') ?? 0;
                $due = $saleOrders->sum('due') ?? 0;

                // Set totals in client
                $client->total = $total;
                $client->paid = $paid;
                $client->due = $due;

                // Action button
                if ($due > 0) {
                    $btn = '<a role="button" data-id="' . $client->id . '" data-total="' . $total . '" data-due="' . $due . '" class="dropdown-item customer-pay">
                        <i class="fa fa-check-circle"></i> Payment
                    </a>';
                    return dropdownMenuContainer($btn);
                }

                return ''; // Return an empty string if no button is needed
            })

            ->addColumn('total', function (Client $client) {
                return $client->total ?? 0;
            })

            ->addColumn('paid', function (Client $client) {
                return $client->paid ?? 0;
            })

            ->addColumn('due', function (Client $client) {
                return $client->due ?? 0;
            })

            ->rawColumns(['action'])
            ->toJson();
    }


    public function customerSaleDetails(SaleOrder $saleOrder, Request $request)
    {

        $saleOrder->load('distributionOrderItems');

        $pageTitle = 'Customer Sales Order Details';
        $permission = 'distribution_list';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        $saleOrders = SaleOrder::where('distribution_order_id', $saleOrder->id)
            ->with('saleOrderItems')
            ->get();

        return view('sales_order.customer_sale_details', compact(
            'saleOrder',
            'pageTitle',
            'saleOrders'
        ));
    }


}
