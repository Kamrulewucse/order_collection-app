<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Client;
use App\Models\LocationAddressInfo;
use App\Models\SalePayment;
use App\Models\Transaction;
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
        }
        $srs = Client::where('type',2)->get(); //here type=2 for SR
        return view('sales_order.index', compact(
            'pageTitle',
            'srs'
        ));
    }
    public function dataTable()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = SaleOrder::with('sr','client')->where('type', \request('type'));
        }else{
            $query = SaleOrder::with('sr','client')->where('sr_id',auth()->user()->client_id)->where('type', \request('type'));
        }


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

                if ($saleOrder->status == 1 && in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])) {
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
        $srs = [];
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $srs = Client::where('status', 1)->where('type', 2)->get(); //type=2 is SR
            $clients = Client::where('status', 1)->where('type', 4)->get(); //type=4 is Client
        }else{
            $srs = Client::where('status', 1)->where('type',2)->where('id',auth()->user()->client_id)->first(); //type 2 for SR
            $clients = Client::where('status', 1)->where('type', 4)->where('sr_id',auth()->user()->client_id)->get(); //type=4 is Client
        }

        $products = [];

        $products = Product::where('status', 1)->get();


        if ($request->type == 1) {
            $pageTitle = 'SR Order Create';
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
        if ($saleOrder->status != 2) {
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

            $saleOrder->total = $total;
            $saleOrder->paid = $request->paid;
            $saleOrder->due = $total - $saleOrder->paid;
            $saleOrder->status = 3;
            $saleOrder->save();

            if($request->paid > 0){
                $salePayment = new SalePayment();
                $salePayment->sr_id = $saleOrder->sr_id;
                $salePayment->sale_order_id = $saleOrder->id;
                $salePayment->client_id = $saleOrder->client_id;
                $salePayment->received_type = 1; //Nagad payment
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
                $transaction->amount = $saleOrder->paid;
                $transaction->sr_id = $saleOrder->sr_id;
                $transaction->client_id = $saleOrder->client_id;
                $transaction->date = Carbon::now()->format('Y-m-d');
                $transaction->sale_payment_id = $salePayment->id;
                $salePayment->sale_order_id = $saleOrder->id;
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

        DB::beginTransaction();
        try {

            $saleOrder->status= 2;
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
            $salePayment->received_type = 2; //due payment
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
            $salePayment->sale_order_id = $saleOrder->id;
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
                'redirect_url' => route('sales-order.details', ['salePayment' => $salePayment->id,'type' => 1]),
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
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $clients = Client::where('type', 4)->get(); //type=4 for client
            $srs = Client::where('type', 2)->get(); //type=2 for SR
        }else{
            $clients = Client::where('type', 4)->where('sr_id',auth()->user()->client_id)->get(); //type=4 for client
        }
        return view('sales_order.customer_payments', compact(
            'clients','srs'
        ));
    }
    public function customerPaymentsDataTable()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = Client::where('type', 4) // type=4 for client
            ->whereHas('saleOrders')
            ->with('saleOrders')
            ->with('sr');
        }else{
            $query = Client::where('type', 4) // type=4 for client
            ->where('sr_id',auth()->user()->client_id)
            ->whereHas('saleOrders')
            ->with('saleOrders')
            ->with('sr');
        }


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

        if (request()->has('sr') && request('sr') != '') {
            $query->where('sr_id', request('sr'));
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

            ->addColumn('sr_name', function (Client $client) {
                return $client->sr->name ?? 0;
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
