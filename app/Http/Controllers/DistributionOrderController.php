<?php

namespace App\Http\Controllers;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\AccountHead;
use App\Models\DistributionOrder;
use App\Models\DistributionOrderItem;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class DistributionOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function customerPayments(Request $request)
    {
        // $paymentModes = AccountHead::where('payment_mode','>',0)->get();
        $paymentMode = AccountHead::where('id', 1)->first();
        if ($request->type == 1) {
            $pageTitle = 'Customer Payments';
            $permission = 'distribution_list';
            $permission_create = 'distribution_create';
        } else {
            $pageTitle = 'Customer Payments';
            $permission = 'distribution_damage_product_return_list';
            $permission_create = 'distribution_damage_product_return_create';
        }
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        $companies = Client::where('status', 1)->where('type', 1)->get();
        return view('distribution_order.customer_payments', compact(
            'paymentMode',
            'pageTitle',
            'permission_create',
            'companies'
        ));
    }
    public function customerPaymentsDataTable()
    {
        $query = Client::where('status', 1)
            ->where('type', 3)
            ->whereHas('saleOrders')
            ->with('saleOrders')
            ->with('company');

        // if (request()->has('start_date') && request('end_date') != '') {
        //     $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        // }
        if (request()->has('company') && request('company') != '') {
            $query->where('company_id', request('company'));
        }
        return DataTables::eloquent($query)

            ->addColumn('action', function (Supplier $supplier) {
                $saleOrders = $supplier->saleOrders()
                    ->whereHas('distributionOrder', function ($query) {
                        $query->where('close_status', 1);
                    });
                $supplier->total = $saleOrders->sum('total') ?? 0;
                $supplier->paid = $saleOrders->sum('paid') ?? 0;
                $supplier->due = $saleOrders->sum('due') ?? 0;
                if ($supplier->due > 0) {
                    $btn = '<a role="button" data-id="' . $supplier->id . '" data-total="' . $supplier->total . '" data-due="' . $supplier->due . '"  class="dropdown-item customer-pay"><i class="fa fa-check-circle"></i> Payment</a>';
                    return dropdownMenuContainer($btn);
                }
            })
            ->addColumn('total', function (Supplier $supplier) {
                return $supplier->saleOrders()->whereHas('distributionOrder', function ($query) {
                    $query->where('close_status', 1);
                })->sum('total') ?? 0;
            })
            ->addColumn('paid', function (Supplier $supplier) {
                return $supplier->saleOrders()->whereHas('distributionOrder', function ($query) {
                    $query->where('close_status', 1);
                })->sum('paid') ?? 0;
            })
            ->addColumn('due', function (Supplier $supplier) {
                return $supplier->saleOrders()->whereHas('distributionOrder', function ($query) {
                    $query->where('close_status', 1);
                })->sum('due') ?? 0;
            })
            ->addColumn('company_name', function (Supplier $supplier) {
                return $supplier->company->name ?? '';
            })

            ->rawColumns(['action'])
            ->toJson();
    }
    public function index(Request $request)
    {
        $paymentModes = AccountHead::where('payment_mode', '>', 0)->get();
        if ($request->type == 1) {
            $pageTitle = 'Distribution Orders';
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
        $companies = Client::where('status', 1)->where('type', 1)->get();
        return view('distribution_order.index', compact(
            'paymentModes',
            'pageTitle',
            'permission_create',
            'companies'
        ));
    }

    public function distributionInvoice(DistributionOrder $distributionOrder, Request $request)
    {

        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        $distributionOrder->load('distributionOrderItems');
        if ($distributionOrder->type != 1) {
            abort('404');
        }

        $products = Product::whereIn('id', $distributionOrder->distributionOrderItems->pluck('product_id'))->get();
        $customers = Client::where('type', 3) //3=Customer
            ->where('company_id', $distributionOrder->company_id)
            ->get();
        $pageTitle = 'Distribution Invoice';

        // $paymentModes = AccountHead::where('payment_mode','>',0)->get();
        $paymentMode = AccountHead::where('id', 1)->first();

        if ($distributionOrder->close_status == 0) {
            if ($request->close_status == 1) {
                $distributionOrder->close_status = 0;
            } else {
                $distributionOrder->close_status = 1;
            }
        } else {
            $distributionOrder->hold_release = 1;
        }



        return view('distribution_order.day_close_old', compact(
            'distributionOrder',
            'pageTitle',
            'products',
            'customers',
            'paymentMode'
        ));
    }


    public function dayClosePost(DistributionOrder $distributionOrder, Request $request)
    {

        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($distributionOrder->type != 1) {
            abort('404');
        }
        if ($distributionOrder->close_status == 1) {
            abort('404');
        }

        $request->validate([
            'return_quantity.*' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $costingTotal = 0;
            $damageProductReturnTotal = 0;
            $damageProductReturnCostingTotal = 0;
            $distributionOrderItems = $distributionOrder->distributionOrderItems;

            foreach ($request->product_id as $key => $reqProduct) {
                $distributionOrderItem = DistributionOrderItem::find($request->distribution_order_item_id[$key]);
                $inventory = Inventory::where('product_id', $distributionOrderItem->product_id)->first();

                $distributionOrderItem->sale_quantity = $request->distribute_quantity[$key] - $request->return_quantity[$key];
                $distributionOrderItem->return_quantity = $request->return_quantity[$key];
                $distributionOrderItem->save();

                // if ($distributionOrderItem->return_quantity > 0) {
                //     $inventory->increment('quantity', $distributionOrderItem->return_quantity);
                //     //Inventory Log
                //     $inventoryLog = InventoryLog::where('distribution_order_id', $distributionOrder->id)
                //         ->where('product_id', $distributionOrderItem->product_id)
                //         ->first();
                //     $inventoryLog->decrement('quantity', $distributionOrderItem->return_quantity);
                // }

                $total += $distributionOrderItem->sale_quantity * $distributionOrderItem->selling_unit_price;
                $costingTotal += $distributionOrderItem->sale_quantity * ($inventory->average_purchase_unit_price ?? 0);
            }

            $distributionOrder->total = $total;
            $distributionOrder->due = $total - $distributionOrder->paid;
            $distributionOrder->save();

            $customerBillAmount = SaleOrder::where('distribution_order_id', $distributionOrder->id)
                ->sum('total') ?? 0;

           if (round($distributionOrder->total, 2) < round($customerBillAmount, 2)) {
                throw  new \Exception("Order total amount are not equal to Customer bill total amount ");
            }

            //Distribution JV Adjusting
            $vouchers = Voucher::where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                ->where('distribution_order_id', $distributionOrder->id)
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

            return redirect()
                ->route('distribution.day_close', ['distributionOrder' => $distributionOrder->id, 'type' => $distributionOrder->type])
                ->with('message', 'Distribution product adjust successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function holdReleasePost(DistributionOrder $distributionOrder, Request $request)
    {

        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($distributionOrder->type != 1) {
            abort('404');
        }
        if ($distributionOrder->close_status == 1) {
            abort('404');
        }

        DB::beginTransaction();
        try {
            $customerBillAmount = SaleOrder::where('distribution_order_id', $distributionOrder->id)->sum('total') ?? 0;
            if ($customerBillAmount == 0) {
                throw new \Exception("Customer Bill is not added!");
            }
            $distributionSaleQty = $distributionOrder->distributionOrderItems->sum('sale_quantity');

            if ($customerBillAmount > 0 && $distributionSaleQty == 0) {
                throw new \Exception('Firstly bill sale qty adjust update, because customer bill added but sale qty is 0');
            }

            //1.Journal Voucher: Revenue Recognition

            $payeeId = AccountHead::where('supplier_id', $distributionOrder->dsr_id)->first()->id ?? null;
            $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
                ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                ->max('voucher_no_group_sl') + 1;

            $voucherNo = 'JV-' . $voucherNoGroupSl;

            $voucher = new Voucher();
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $distributionOrder->total;
            $voucher->date = Carbon::now()->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->company_id = $distributionOrder->company_id;
            $voucher->distribution_order_id = $distributionOrder->id;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //DEBIT:Accounts Receivable (Asset account)
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = $payeeId; //DSR
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $distributionOrder->total;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->distribution_order_id = $distributionOrder->id;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit:Sales Revenue (Revenue account)
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = AccountHead::find(114)->id ?? 114; //Sales Revenue (Revenue account)
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $distributionOrder->total;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $distributionOrder->company_id;
            $transaction->distribution_order_id = $distributionOrder->id;
            $transaction->user_id = auth()->id();
            $transaction->save();


            //2.Journal Voucher: Cost of Goods Sold:
            $payeeId = AccountHead::where('supplier_id', $distributionOrder->dsr_id)->first()->id ?? null;
            $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
                ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                ->max('voucher_no_group_sl') + 1;

            $voucherNo = 'JV-' . $voucherNoGroupSl;

            $voucher = new Voucher();
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $distributionOrder->costing_total;
            $voucher->date = Carbon::now()->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->company_id = $distributionOrder->company_id;
            $voucher->distribution_order_id = $distributionOrder->id;
            $voucher->user_id = auth()->id();
            $voucher->save();


            //Debit: Cost of Goods Sold (Expense account)
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = AccountHead::find(2)->id ?? 2; // Cost of Goods Sold
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $distributionOrder->costing_total;;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $distributionOrder->company_id;
            $transaction->distribution_order_id = $distributionOrder->id;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit: Inventory (Asset account)
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = AccountHead::find(113)->id ?? 113; //Inventory (Asset account)
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $distributionOrder->costing_total;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $distributionOrder->company_id;
            $transaction->distribution_order_id = $distributionOrder->id;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //end here

            //start balancesheet currection
            $saleOrders = SaleOrder::where('distribution_order_id',$distributionOrder->id)->get();
            foreach ($saleOrders as $key => $saleOrder) {
                //if Any Payment
                $voucher = Voucher::where('sale_order_id', $saleOrder->id)
                ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
                ->first();

                if ($voucher && $saleOrder->paid == 0) {
                    Transaction::where('voucher_id', $voucher->id)->delete();
                    $voucher->delete();
                } else {
                    if ($saleOrder->paid > 0) {
                        $paymentModeType = $saleOrder->payment_type_id;
                        $paymentMode = $saleOrder->payment_account_head_id;

                        $payeeId = AccountHead::where('supplier_id', $saleOrder->customer_id)->first()->id ?? null;
                        if (!$voucher) {
                            $voucherNoGroupSlQuery = Transaction::withTrashed();
                            $voucherNoGroupSl = $voucherNoGroupSlQuery->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
                                ->max('voucher_no_group_sl') + 1;
                            $voucherNo = 'MR-' . $voucherNoGroupSl;

                            $voucher = new Voucher();
                            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                            $voucher->voucher_no = $voucherNo;
                        } else {
                            $voucherNoGroupSl = $voucher->voucher_no_group_sl;
                            $voucherNo = $voucher->voucher_no;
                        }
                        $voucher->payment_type_id = $paymentModeType;
                        $voucher->payment_account_head_id = $paymentMode; //Payment Mode
                        $voucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                        $voucher->amount = $saleOrder->paid;
                        $voucher->date = Carbon::now()->format('Y-m-d');
                        $voucher->account_head_payee_depositor_id = $payeeId;
                        $voucher->company_id = $saleOrder->company_id;
                        $voucher->customer_id = $saleOrder->customer_id;
                        $voucher->sale_order_id = $saleOrder->id;
                        $voucher->cheque_no = $saleOrder->cheque_no;
                        $voucher->notes = $saleOrder->notes;
                        $voucher->user_id = auth()->id();
                        $voucher->save();

                        //Debit: Cash/Bank (Asset account)
                        $transaction = Transaction::where('voucher_id', $voucher->id)->first();
                        if (!$transaction) {
                            $transaction = new Transaction();
                        }
                        $transaction->voucher_id = $voucher->id;
                        $transaction->payment_type_id = $paymentModeType;
                        $transaction->payment_account_head_id = $paymentMode; //Cash/Bank (Asset account)
                        $transaction->account_head_id = $paymentMode;
                        $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                        $transaction->transaction_type = TransactionType::$DEBIT;
                        $transaction->amount = $saleOrder->paid;
                        $transaction->date = Carbon::now()->format('Y-m-d');
                        $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                        $transaction->voucher_no = $voucherNo;
                        $transaction->account_head_payee_depositor_id = $payeeId;
                        $transaction->company_id = $saleOrder->company_id;
                        $transaction->customer_id = $saleOrder->customer_id;
                        $transaction->sale_order_id = $saleOrder->id;
                        $transaction->notes = $saleOrder->notes;
                        $transaction->user_id = auth()->id();
                        $transaction->save();

                        $firstTransaction = $transaction;

                        //Credit: Accounts Receivable (Asset account)
                        $transaction = Transaction::where('voucher_id', $voucher->id)
                            ->where('id', '!=', $firstTransaction->id)
                            ->first();
                        if (!$transaction) {
                            $transaction = new Transaction();
                        }
                        $transaction->voucher_id = $voucher->id;
                        $transaction->payment_type_id = $paymentModeType;
                        $transaction->payment_account_head_id = $paymentMode; //Accounts Receivable (Asset account)
                        $transaction->account_head_id = $payeeId; //
                        $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                        $transaction->transaction_type = TransactionType::$CREDIT;
                        $transaction->amount = $saleOrder->paid;
                        $transaction->date = Carbon::now()->format('Y-m-d');
                        $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                        $transaction->voucher_no = $voucherNo;
                        $transaction->account_head_payee_depositor_id = $payeeId;
                        $transaction->company_id = $saleOrder->company_id;
                        $transaction->customer_id = $saleOrder->customer_id;
                        $transaction->sale_order_id = $saleOrder->id;
                        $transaction->notes = $saleOrder->notes;
                        $transaction->user_id = auth()->id();
                        $transaction->save();
                    }
                }
            }

             foreach ($distributionOrder->saleOrders as $saleOrder){
                //1.Journal Voucher: DSR and Customer Adjust
                $payeeId = AccountHead::where('supplier_id', $distributionOrder->dsr_id)->first()->id ?? null;
                $customerId = AccountHead::where('supplier_id', $saleOrder->customer_id)->first()->id ?? null;

                $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
                        ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                        ->max('voucher_no_group_sl') + 1;

                $voucherNo = 'JV-' . $voucherNoGroupSl;

                $voucher = Voucher::where('voucher_type',VoucherType::$JOURNAL_VOUCHER)
                    ->where('sale_order_id',$saleOrder->id)->first();
                $voucherNew = true;
                if (!$voucher){
                    $voucher = new Voucher();
                    $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                    $voucher->voucher_no = $voucherNo;
                }else{
                    $voucherNew = false;
                    $voucherNo = $voucher->voucher_no;
                    $voucherNoGroupSl = $voucher->voucher_no_group_sl;
                }
                $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $voucher->amount = $saleOrder->total;
                $voucher->date = Carbon::now()->format('Y-m-d');
                $voucher->account_head_payee_depositor_id = $customerId;
                $voucher->company_id = $saleOrder->company_id;
                $voucher->customer_id = $saleOrder->customer_id;
                $voucher->sale_order_id = $saleOrder->id;
                $voucher->notes = $saleOrder->notes;
                $voucher->user_id = auth()->id();
                $voucher->save();

                $transaction = Transaction::where('voucher_id',$voucher->id)->first();
                //DEBIT:Accounts Receivable (Asset account)
                if ($voucherNew){
                    $transaction = new Transaction();
                }
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = $customerId;//Customer
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$DEBIT;
                $transaction->amount = $saleOrder->total;
                $transaction->date = Carbon::now()->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $customerId;
                $transaction->company_id = $saleOrder->company_id;
                $transaction->customer_id = $saleOrder->customer_id;
                $transaction->sale_order_id = $saleOrder->id;
                $transaction->notes = $saleOrder->notes;
                $transaction->user_id = auth()->id();
                $transaction->save();

                $transaction = Transaction::where('voucher_id',$voucher->id)->orderBy('id','desc')->first();
                //CREDIT:Accounts Receivable (Asset account)
                if ($voucherNew){
                    $transaction = new Transaction();
                }
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = $payeeId;
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$CREDIT;
                $transaction->amount = $saleOrder->total;
                $transaction->date = Carbon::now()->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $customerId;
                $transaction->company_id = $saleOrder->company_id;
                $transaction->customer_id = $saleOrder->customer_id;
                $transaction->sale_order_id = $saleOrder->id;
                $transaction->notes = $saleOrder->notes;
                $transaction->user_id = auth()->id();
                $transaction->save();
            }

            //end adjust balance sheet

            $total = 0;
            $costingTotal = 0;
            $damageProductReturnTotal = 0;
            $damageProductReturnCostingTotal = 0;
            $distributionOrderItems = $distributionOrder->distributionOrderItems;

            foreach ($distributionOrderItems as $key => $distributionOrderItem) {
                $inventory = Inventory::where('product_id', $distributionOrderItem->product_id)->first();

                $damageReturnQuantity = $distributionOrderItem->damage_quantity - $distributionOrderItem->damage_return_quantity;
                if ($damageReturnQuantity > 0) {

                    $inventory->increment('quantity', $damageReturnQuantity);

                    //Return Log
                    $inventoryLog = InventoryLog::where('distribution_order_id', $distributionOrder->id)
                        ->where('product_id', $distributionOrderItem->product_id)
                        ->first();
                    $inventoryLog->quantity = $damageReturnQuantity;
                    $inventoryLog->save();

                    $distributionOrderItem->increment('damage_return_quantity', $damageReturnQuantity);
                    //If any Damage product refund
                    $damageProductReturnTotal += $damageReturnQuantity * $distributionOrderItem->selling_unit_price;
                    $damageProductReturnCostingTotal += $damageReturnQuantity * $distributionOrderItem->purchase_unit_price;
                }

                $distributionOrderItem->return_quantity = $distributionOrderItem->distribute_quantity - $distributionOrderItem->sale_quantity;
                $distributionOrderItem->save();


                if ($distributionOrderItem->return_quantity > 0) {
                    $inventory->increment('quantity',$distributionOrderItem->return_quantity);
                    //Inventory Log
                    $inventoryLog = InventoryLog::where('distribution_order_id', $distributionOrderItem->distribution_order_id)
                        ->where('product_id', $distributionOrderItem->product_id)
                        ->first();
                    // $inventoryLog->quantity = $distributionOrderItem->return_quantity;
                    $inventoryLog->decrement('quantity', $distributionOrderItem->return_quantity);
                    // $inventoryLog->save();
                }

                $total += $distributionOrderItem->sale_quantity * $distributionOrderItem->selling_unit_price;
                $costingTotal += $distributionOrderItem->sale_quantity * ($inventory->average_purchase_unit_price ?? 0);
            }

            $distributionOrder->total = $total;
            $distributionOrder->due = $total - $distributionOrder->paid;
            $distributionOrder->close_status = 1; //Close
            $distributionOrder->save();

            //Distribution JV Adjusting
            $vouchers = Voucher::where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                ->where('distribution_order_id', $distributionOrder->id)
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

            //Damage Product Return JV Adjusting
            $vouchers = Voucher::where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                ->where('distribution_order_id', $distributionOrder->id)
                ->skip(2)
                ->take(2)
                ->get();
            foreach ($vouchers as $key => $voucher) {
                $voucher->amount = ($key == 0 ? $damageProductReturnTotal : $damageProductReturnCostingTotal);
                $voucher->save();
                foreach ($voucher->transactions as $transaction) {
                    $transaction->amount = ($key == 0 ? $damageProductReturnTotal : $damageProductReturnCostingTotal);
                    $transaction->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Distribution hold released successfully'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function customerSaleEntry(DistributionOrder $distributionOrder, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        $distributionOrder->load('distributionOrderItems');
        if ($distributionOrder->type != 1) {
            abort('404');
        }
        if ($distributionOrder->close_status == 1) {
            abort('404');
        }
        $products = Product::whereIn('id', $distributionOrder->distributionOrderItems->pluck('product_id'))->get();
        $customers = Client::where('type', 3) //3=Customer
            ->get();
        $pageTitle = 'Distribution Customer Bill Add: ' . $distributionOrder->order_no;

        // $paymentModes = AccountHead::where('payment_mode','>',0)->get();
        $paymentMode = AccountHead::where('id', 1)->first();

        return view('distribution_order.customer_sale_entry', compact(
            'distributionOrder',
            'pageTitle',
            'products',
            'customers',
            'paymentMode'
        ));
    }
    public function customerSaleEntryPost(DistributionOrder $distributionOrder, Request $request)
    {
// return($request->customer_id);
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($distributionOrder->type != 1) {
            abort('404');
        }
        if ($distributionOrder->close_status == 1) {
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

            if (abs(floatval($distributionOrder->total) - array_sum($request->total_amount)) > 0.0001) {
                return redirect()->back()->withInput()->with('error', 'Remaining amount not Zero(0) Or Customer sales bill total and disbursement total are not equal');
            }

            // if ($distributionOrder->total < array_sum($request->total_amount)) {
            //     $exceptionMsg = 'customer sales bill total is greater than distribution total amount';
            //     throw new \Exception($exceptionMsg);
            // }
            if($request->customer_id){
                 SaleOrder::where('distribution_order_id', $distributionOrder->id)
                ->whereNotIn('customer_id', $request->customer_id)
                ->delete();
            }

            $total = 0;
            $costingTotal = 0;
            $counter = 0;

            foreach ($request->customer_id as $key => $reqCustomerId) {
                $saleOrder = SaleOrder::where('customer_id', $request->customer_id[$counter])
                    ->where('distribution_order_id', $distributionOrder->id)
                    ->first();

                $customer = Client::find($request->customer_id[$counter]);



                if (!$saleOrder) {
                    $saleOrder = new SaleOrder();
                    $saleOrder->distribution_order_id = $distributionOrder->id;
                    $saleOrder->company_id = $distributionOrder->company_id;
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
            $distributionOrder->increment('due', $distributionOrder->paid);
            $distributionOrder->decrement('paid', $distributionOrder->paid);
            //Update
            $distributionOrder->decrement('due', array_sum($request->paid_amount));
            $distributionOrder->increment('paid', array_sum($request->paid_amount));

            // foreach ($distributionOrder->saleOrders as $saleOrder){
            //     //1.Journal Voucher: DSR and Customer Adjust
            //     $payeeId = AccountHead::where('supplier_id', $distributionOrder->dsr_id)->first()->id ?? null;
            //     $customerId = AccountHead::where('supplier_id', $saleOrder->customer_id)->first()->id ?? null;

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
            //     $voucher->account_head_payee_depositor_id = $customerId;
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
            //     $transaction->account_head_id = $customerId;//Customer
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$DEBIT;
            //     $transaction->amount = $saleOrder->total;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $customerId;
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
            //     $transaction->account_head_payee_depositor_id = $customerId;
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

    public function customerDamageProductEntry(DistributionOrder $distributionOrder, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        $distributionOrder->load('distributionOrderItems');
        if ($distributionOrder->type != 1) {
            abort('404');
        }
        if ($distributionOrder->close_status == 1) {
            abort('404');
        }

        $pageTitle = 'Customer Damage Product Entry Add: ' . $distributionOrder->order_no;

        return view('distribution_order.customer_damage_product_entry', compact(
            'distributionOrder',
            'pageTitle'
        ));
    }
    public function customerDamageProductEntryPost(DistributionOrder $distributionOrder, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('distribution_day_close')) {
            abort(403, 'Unauthorized');
        }

        if ($distributionOrder->type != 1) {
            abort('404');
        }
        if ($distributionOrder->close_status == 1) {
            abort('404');
        }

        $request->validate([
            'return_quantity.*' => 'required|numeric'
        ]);
        DB::beginTransaction();
        try {
            $total = 0;
            $costingTotal = 0;
            foreach ($request->product_id as $key => $reqProduct) {

                $distributionOrderItem = DistributionOrderItem::where('id', $request->distribution_order_item_id[$key])->first();

                if ($distributionOrderItem->damage_quantity < ($request->return_quantity[$key] ?? 0)) {
                    $errorMsg = ($distributionOrderItem->product->name) . ' return quantity is grater than delivery quantity';
                    throw new \Exception($errorMsg);
                }
                $inventory = Inventory::where('product_id', $distributionOrderItem->product_id)->first();
                $inventory->increment('quantity', $distributionOrderItem->damage_return_quantity);
                $inventoryLog = InventoryLog::where('distribution_order_id', $distributionOrderItem->distribution_order_id)
                    ->where('product_id', $distributionOrderItem->product_id)
                    ->where('type', 6)
                    ->first();
                if ($inventoryLog) {
                    $inventoryLog->delete();
                }

                $distributionOrderItem->damage_return_quantity = ($request->return_quantity[$key] ?? 0);
                $distributionOrderItem->save();

                $inventory = Inventory::where('product_id', $distributionOrderItem->product_id)->first();
                $inventory->decrement('quantity', $request->return_quantity[$key] ?? 0);
                if ($request->return_quantity[$key] ?? 0) {
                    //Inventory Log
                    $inventoryLog = new InventoryLog();
                    $inventoryLog->type = 6; // Damage product refund return
                    $inventoryLog->inventory_id = $inventory->id;
                    $inventoryLog->supplier_id = $distributionOrder->dsr_id;
                    $inventoryLog->distribution_order_id = $distributionOrderItem->distribution_order_id;
                    $inventoryLog->product_id = $distributionOrderItem->product_id;
                    $inventoryLog->quantity = $request->return_quantity[$key];
                    $inventoryLog->unit_price = $distributionOrderItem->selling_unit_price;
                    $inventoryLog->product_code = $distributionOrderItem->product->code;
                    $inventoryLog->notes = 'Damage product delivery refund return';
                    $inventoryLog->user_id = auth()->id();
                    $inventoryLog->date = Carbon::now();
                    $inventoryLog->save();
                }


                $total += ($distributionOrderItem->damage_quantity - $distributionOrderItem->damage_return_quantity) * $distributionOrderItem->selling_unit_price;
                $costingTotal += ($distributionOrderItem->damage_quantity - $distributionOrderItem->damage_return_quantity) * $distributionOrderItem->purchase_unit_price;
            }

            $vouchers = Voucher::where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
                ->where('distribution_order_id', $distributionOrder->id)
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
            return redirect()->route('distribution.details', ['distributionOrder' => $distributionOrder->id, 'type' => $distributionOrder->type])
                ->with('message', 'Customer damage product updated successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->route('distribution.customer_damage_product_entry', ['distributionOrder' => $distributionOrder->id, 'type' => $distributionOrder->type])->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function details(DistributionOrder $distributionOrder, Request $request)
    {

        $distributionOrder->load('distributionOrderItems');
        if ($request->type == 1) {
            $pageTitle = 'Distribution Receipt';
            $permission = 'distribution_list';
        } else {
            $pageTitle = 'Damage Product Return Details';
            $permission = 'distribution_damage_product_return_list';
        }
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        $paymentModes = AccountHead::where('payment_mode', '>', 0)->get();

        return view('distribution_order.details', compact(
            'distributionOrder',
            'pageTitle',
            'paymentModes'
        ));
    }
    public function finalDetails(DistributionOrder $distributionOrder, Request $request)
    {

        $distributionOrder->load('distributionOrderItems');
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

        return view('distribution_order.final_details', compact(
            'distributionOrder',
            'pageTitle'
        ));
    }
    public function customerSaleDetails(DistributionOrder $distributionOrder, Request $request)
    {

        $distributionOrder->load('distributionOrderItems');

        $pageTitle = 'Customer Sales Order Details';
        $permission = 'distribution_list';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        $saleOrders = SaleOrder::where('distribution_order_id', $distributionOrder->id)
            ->with('saleOrderItems')
            ->get();

        return view('distribution_order.customer_sale_details', compact(
            'distributionOrder',
            'pageTitle',
            'saleOrders'
        ));
    }


    public function dataTable()
    {
        $query = DistributionOrder::with('sr')
            ->where('type', \request('type'));
        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if (request()->has('company') && request('company') != '') {
            $query->where('company_id', request('company'));
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (DistributionOrder $distributionOrder) {
                $btn = '';
                if ($distributionOrder->type == 1) {
                    $permission = 'distribution_payment_receive';
                } else {
                    $permission = 'distribution_damage_product_return_payment';
                }

                if (auth()->user()->hasPermissionTo('distribution_day_close')) {
                    if ($distributionOrder->type == 1) {
                        $icon = '<i class="fa fa-info-circle"></i>';
                        if ($distributionOrder->close_status == 0) {
                            $icon = '<i class="fa fa-plus"></i>';
                        }

                        $btn .= ' <a  href="' . route('distribution.day_close', ['distributionOrder' => $distributionOrder->id, 'type' => $distributionOrder->type]) . '" class="dropdown-item">' . $icon . ' Distribution Invoice</a>';
                    }
                }
                if ($distributionOrder->type == 1) {
                    $permission = 'distribution_list';
                } else {
                    $permission = 'distribution_damage_product_return_list';
                }
                if (auth()->user()->hasPermissionTo($permission)) {
                    $text = 'Distribution Receipt';
                    $textFinal = 'Final Report';
                    // $btn .= ' <a href="' . route('distribution.details', ['distributionOrder' => $distributionOrder->id, 'type' => $distributionOrder->type]) . '" class="dropdown-item"><i class="fa fa-info-circle"></i> '.$text.'</a>';
                    $btn .= ' <a href="' . route('distribution.final_details', ['distributionOrder' => $distributionOrder->id, 'type' => $distributionOrder->type]) . '" class="dropdown-item"><i class="fa fa-info-circle"></i> ' . $textFinal . '</a>';
                }

                if ($distributionOrder->type == 1 && $distributionOrder->close_status == 0) {
                    $btn .= ' <a  data-id="' . $distributionOrder->id . '" role="button" class="dropdown-item day-close"><i class="fa fa-info-circle"></i> Hold Release</a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('dsr_name', function (DistributionOrder $distributionOrder) {
                return $distributionOrder->dsr->name ?? '';
            })
            ->addColumn('company_name', function (DistributionOrder $distributionOrder) {
                return $distributionOrder->company->name ?? '';
            })
            ->addColumn('close_status', function (DistributionOrder $distributionOrder) {
                if ($distributionOrder->close_status == 1) {
                    return '<span class="badge badge-success">Hold Released</span>';
                } else {
                    return '<span class="badge badge-warning">Hold</span>';
                }
            })
            ->rawColumns(['action', 'close_status'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $clients = Client::where('status', 1)->where('type', 2)->get();

        $companies = Client::where('status', 1)->where('type', 1)->get();
        $products = [];
        if ($request->company != '') {
            $products = Product::where('status', 1)
                ->where('supplier_id', $request->company)
                ->get();
        }

        if ($request->type == 1) {
            $pageTitle = 'Distribution Order Create';
            $permission = 'distribution_create';
        } else {
            $pageTitle = 'Damage Product Return';
            $permission = 'distribution_damage_product_return_create';
        }
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        return view('distribution_order.create', compact(
            'clients',
            'products',
            'pageTitle',
            'companies'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'product_id.*' => 'required',
            'product_qty.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $distribution_order = new DistributionOrder();
            $distribution_order->type = $request->type;
            $distribution_order->dsr_id = $request->dsr;
            $distribution_order->company_id = $request->company;
            $distribution_order->total = 0;
            $distribution_order->paid = 0;
            $distribution_order->due = 0;
            $distribution_order->costing_total = 0;
            $distribution_order->notes = $request->notes;
            $distribution_order->user_id = auth()->id();
            $distribution_order->date = Carbon::parse($request->date);
            $distribution_order->save();
            if ($request->type == 1) {
                $distribution_order->order_no = 'DIST-' . date('Ymd') . '-' . $distribution_order->id;
            } else {
                $distribution_order->order_no = 'DMG-' . date('Ymd') . '-' . $distribution_order->id;
            }
            $distribution_order->save();

            $costingTotal = 0;
            $total = 0;
            $damageProductReturnTotal = 0;
            $damageProductReturnCostingTotal = 0;
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($request->product_id[$key]);

                $inventory = Inventory::where('product_id', $product->id)->first();

                if ($request->type == 1 && $inventory->quantity < ($request->product_qty[$key] + ($request->damage_return_product_qty[$key] ?? 0))) {
                    throw new \Exception("$product->name insufficient quantity: $inventory->quantity");
                }

                $purchaseItem = new DistributionOrderItem();
                $purchaseItem->distribution_order_id = $distribution_order->id;
                $purchaseItem->product_id = $product->id;
                $purchaseItem->product_code = $product->code;
                $purchaseItem->damage_quantity = $request->damage_return_product_qty[$key] ?? 0;
                $purchaseItem->distribute_quantity = $request->product_qty[$key];
                $purchaseItem->sale_quantity = 0;
                $purchaseItem->purchase_unit_price = $inventory->average_purchase_unit_price ?? 0;
                $purchaseItem->selling_unit_price = $request->product_unit_price[$key];
                $purchaseItem->save();

                $total += ($request->product_qty[$key] * $request->product_unit_price[$key]);
                $costingTotal += ($request->product_qty[$key] ?? 0) * ($inventory->average_purchase_unit_price ?? $request->product_unit_price[$key]);
                //If any Damage product refund
                $damageProductReturnTotal += ($request->damage_return_product_qty[$key] ?? 0) * ($request->product_unit_price[$key]);
                $damageProductReturnCostingTotal += ($request->damage_return_product_qty[$key] ?? 0) * ($inventory->average_purchase_unit_price ?? $request->product_unit_price[$key]);


                if ($request->type == 1) {
                    $inventory->decrement('quantity', ($request->product_qty[$key] + ($request->damage_return_product_qty[$key] ?? 0)));
                    //Inventory Log
                    $inventoryLog = new InventoryLog();
                    $inventoryLog->type = 2; //Distribution Out
                    $inventoryLog->inventory_id = $inventory->id;
                    $inventoryLog->supplier_id = $request->dsr;
                    $inventoryLog->distribution_order_id = $distribution_order->id;
                    $inventoryLog->product_id = $product->id;
                    $inventoryLog->quantity = $request->product_qty[$key];
                    $inventoryLog->unit_price = $request->product_unit_price[$key];
                    $inventoryLog->product_code = $product->code;
                    $inventoryLog->notes = $request->notes;
                    $inventoryLog->user_id = auth()->id();
                    $inventoryLog->date = Carbon::parse($request->date);
                    $inventoryLog->save();
                    if (($request->damage_return_product_qty[$key] ?? 0) > 0) {
                        $inventoryLog = new InventoryLog();
                        $inventoryLog->type = 5; // Damage product refund
                        $inventoryLog->inventory_id = $inventory->id;
                        $inventoryLog->supplier_id = $request->dsr;
                        $inventoryLog->distribution_order_id = $distribution_order->id;
                        $inventoryLog->product_id = $product->id;
                        $inventoryLog->quantity = $request->damage_return_product_qty[$key] ?? 0;
                        $inventoryLog->unit_price = $request->product_unit_price[$key];
                        $inventoryLog->product_code = $product->code;
                        $inventoryLog->notes = $request->notes;
                        $inventoryLog->user_id = auth()->id();
                        $inventoryLog->date = Carbon::parse($request->date);
                        $inventoryLog->save();
                    }
                }
            }
            $distribution_order->total = $total;
            $distribution_order->paid = 0;
            $distribution_order->due = $total;
            $distribution_order->costing_total = $costingTotal;
            $distribution_order->save();

            // if ($request->type == 1) {
            //     //1.Journal Voucher: Revenue Recognition

            //     $payeeId = AccountHead::where('supplier_id', $distribution_order->dsr_id)->first()->id ?? null;
            //     $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
            //             ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
            //             ->max('voucher_no_group_sl') + 1;

            //     $voucherNo = 'JV-' . $voucherNoGroupSl;

            //     $voucher = new Voucher();
            //     $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $voucher->amount = $total;
            //     $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            //     $voucher->voucher_no = $voucherNo;
            //     $voucher->account_head_payee_depositor_id = $payeeId;
            //     $voucher->company_id = $request->company;
            //     $voucher->distribution_order_id = $distribution_order->id;
            //     $voucher->notes = $request->notes;
            //     $voucher->user_id = auth()->id();
            //     $voucher->save();

            //     //DEBIT:Accounts Receivable (Asset account)
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = $payeeId;//DSR
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$DEBIT;
            //     $transaction->amount = $total;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();

            //     //Credit:Sales Revenue (Revenue account)
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = AccountHead::find(114)->id ?? 114;//Sales Revenue (Revenue account)
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$CREDIT;
            //     $transaction->amount = $total;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();


            //     //2.Journal Voucher: Cost of Goods Sold:
            //     $payeeId = AccountHead::where('supplier_id', $distribution_order->dsr_id)->first()->id ?? null;
            //     $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
            //             ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
            //             ->max('voucher_no_group_sl') + 1;

            //     $voucherNo = 'JV-' . $voucherNoGroupSl;

            //     $voucher = new Voucher();
            //     $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $voucher->amount = $costingTotal;
            //     $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            //     $voucher->voucher_no = $voucherNo;
            //     $voucher->account_head_payee_depositor_id = $payeeId;
            //     $voucher->company_id = $request->company;
            //     $voucher->distribution_order_id = $distribution_order->id;
            //     $voucher->notes = $request->notes;
            //     $voucher->user_id = auth()->id();
            //     $voucher->save();


            //     //Debit: Cost of Goods Sold (Expense account)
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = AccountHead::find(2)->id ?? 2;// Cost of Goods Sold
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$DEBIT;
            //     $transaction->amount = $costingTotal;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();

            //     //Credit: Inventory (Asset account)
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = AccountHead::find(113)->id ?? 113;//Inventory (Asset account)
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$CREDIT;
            //     $transaction->amount = $costingTotal;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();


            // }
            // //Damage Product Journal
            // if ($request->type == 1 && $damageProductReturnTotal > 0) {
            //     //1. Reverse Sales Revenue Journal Entry

            //     $payeeId = AccountHead::where('supplier_id', $distribution_order->dsr_id)->first()->id ?? null;
            //     $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
            //             ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
            //             ->max('voucher_no_group_sl') + 1;

            //     $voucherNo = 'JV-' . $voucherNoGroupSl;

            //     $voucher = new Voucher();
            //     $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $voucher->amount = $damageProductReturnTotal;
            //     $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            //     $voucher->voucher_no = $voucherNo;
            //     $voucher->account_head_payee_depositor_id = $payeeId;
            //     $voucher->company_id = $request->company;
            //     $voucher->distribution_order_id = $distribution_order->id;
            //     $voucher->notes = $request->notes;
            //     $voucher->user_id = auth()->id();
            //     $voucher->save();

            //     //Debit:Sales Returns and Allowances
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = AccountHead::find(116)->id ?? 116;//Sales Returns and Allowances
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$DEBIT;
            //     $transaction->amount = $damageProductReturnTotal;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();

            //     //Credit:Accounts Receivable (Asset account)
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = $payeeId;//DSR
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$CREDIT;
            //     $transaction->amount = $damageProductReturnTotal;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();


            //     //2.Reverse COGS Journal Entry:
            //     $payeeId = AccountHead::where('supplier_id', $distribution_order->dsr_id)->first()->id ?? null;
            //     $voucherNoGroupSl = $voucherNoGroupSl = Transaction::withTrashed()
            //             ->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)
            //             ->max('voucher_no_group_sl') + 1;

            //     $voucherNo = 'JV-' . $voucherNoGroupSl;

            //     $voucher = new Voucher();
            //     $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $voucher->amount = $damageProductReturnCostingTotal;
            //     $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            //     $voucher->voucher_no = $voucherNo;
            //     $voucher->account_head_payee_depositor_id = $payeeId;
            //     $voucher->company_id = $request->company;
            //     $voucher->distribution_order_id = $distribution_order->id;
            //     $voucher->notes = $request->notes;
            //     $voucher->user_id = auth()->id();
            //     $voucher->save();


            //     //Debit: Inventory (Asset account)
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = AccountHead::find(113)->id ?? 113;//Inventory (Asset account)
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$DEBIT;
            //     $transaction->amount = $damageProductReturnCostingTotal;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();

            //     //Credit: Cost of Goods Sold (Expense account)
            //     $transaction = new Transaction();
            //     $transaction->voucher_id = $voucher->id;
            //     $transaction->account_head_id = AccountHead::find(2)->id ?? 2;// Cost of Goods Sold
            //     $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            //     $transaction->transaction_type = TransactionType::$CREDIT;
            //     $transaction->amount = $damageProductReturnCostingTotal;
            //     $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            //     $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            //     $transaction->voucher_no = $voucherNo;
            //     $transaction->account_head_payee_depositor_id = $payeeId;
            //     $transaction->company_id = $request->company;
            //     $transaction->distribution_order_id = $distribution_order->id;
            //     $transaction->notes = $request->notes;
            //     $transaction->user_id = auth()->id();
            //     $transaction->save();

            // }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('distribution.day_close', ['distributionOrder' => $distribution_order->id, 'type' => $request->type])->with('success', 'Distribution sales created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('distribution.create', ['type' => $request->type, 'company' => $request->company])->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DistributionOrder $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistributionOrder $purchaseOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistributionOrder $purchaseOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistributionOrder $purchaseOrder)
    {
        //
    }

    public function payment(Request $request)
    {

        if ($request->sales_order) {
            $saleOrder = SaleOrder::find($request->sales_order);
            $request['due_hidden'] = $saleOrder->due;
        }

        // Validate the request data
        $rules = [
            'sales_order' => 'required',
            'payment_mode' => 'required',
            'payment' => 'required|numeric|min:.01|max:' . $request->due_hidden,
            'due' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];
        $paymentMode = 0;
        $paymentModeType = 0;
        if ($request->payment_mode != '') {
            [$paymentMode, $paymentModeType] = explode('|', $request->payment_mode);
        }
        if ($paymentModeType == 1) {
            //bank
            $rules['cheque_no'] = 'nullable|max:255';
        }
        $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {
            //            if (!auth()->user()->hasPermissionTo('supplier_payment')) {
            //                abort(403, 'Unauthorized');
            //            }
            $saleOrder = SaleOrder::find($request->sales_order);
            $distributionOrder = DistributionOrder::find($saleOrder->distribution_order_id);
            if ($distributionOrder->close_status == 0) {
                throw new \Exception("This order is hold on");
            }
            $voucherNoGroupSlQuery = Transaction::withTrashed();

            $voucherNoGroupSl = $voucherNoGroupSlQuery->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
                ->max('voucher_no_group_sl') + 1;
            $voucherNo = 'MR-' . $voucherNoGroupSl;

            $payeeId = AccountHead::where('supplier_id', $saleOrder->customer_id)->first()->id ?? null;

            $voucher = new Voucher();
            $voucher->due_payment = 1; //Due Payment
            $voucher->payment_type_id = $paymentModeType;
            $voucher->payment_account_head_id = $paymentMode; //Payment Mode
            $voucher->voucher_type =  VoucherType::$COLLECTION_VOUCHER;
            $voucher->amount = $request->payment;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->company_id = $saleOrder->company_id;
            $voucher->customer_id = $saleOrder->customer_id;
            $voucher->sale_order_id = $saleOrder->id;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit: Cash/Bank (Asset account)
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode; //Cash/Bank (Asset account)
            $transaction->account_head_id = $paymentMode;
            $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $saleOrder->company_id;
            $transaction->customer_id = $saleOrder->customer_id;
            $transaction->sale_order_id = $saleOrder->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit: Accounts Receivable (Asset account)
            $transaction = new Transaction();
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode; //Accounts Receivable (Asset account)
            $transaction->account_head_id = $payeeId; //
            $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->company_id = $saleOrder->company_id;
            $transaction->customer_id = $saleOrder->customer_id;
            $transaction->sale_order_id = $saleOrder->id;
            $transaction->notes = $request->notes;
            $transaction->user_id = auth()->id();
            $transaction->save();


            //distribution Order Update
            $distributionOrder->increment('paid', $request->payment);
            $distributionOrder->decrement('due', $request->payment);
            //Sale Order Update
            $saleOrder->increment('paid', $request->payment);
            $saleOrder->decrement('due', $request->payment);


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status' => true,
                'redirect_url' => route('distribution.details', ['distributionOrder' => $distributionOrder->id, 'type' => $request->type]),
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
}
