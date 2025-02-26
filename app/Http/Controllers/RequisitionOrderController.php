<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use App\Models\RequisitionOrder;
use App\Models\RequisitionOrderItem;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Client;
use App\Models\Location;
use App\Events\LocationUpdate;
use App\Models\SalePayment;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use SakibRahaman\DecimalToWords\DecimalToWords;
use Ramsey\Uuid\Uuid;

class RequisitionOrderController extends Controller
{

    public function index(Request $request)
    {
        $pageTitle = 'Requisition Orders';

        $srs = Client::where('type','SR')->get(); //here type=2 for SR
        return view('requisition_order.index', compact(
            'pageTitle',
            'srs'
        ));
    }
    public function dataTable()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = RequisitionOrder::with('sr','client');
        }elseif(in_array(auth()->user()->role, ['Divisional Admin'])){
            $query = RequisitionOrder::with('sr','client')->where('divisional_user_id',auth()->user()->client_id);
        }else{
            $query = RequisitionOrder::with('sr','client')->where('sr_id',auth()->user()->client_id);
        }

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if (request()->has('sr') && request('sr') != '') {
            $query->where('sr_id', request('sr'));
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (RequisitionOrder $requisitionOrder) {
                $btn = '';
                $icon = '<i class="fa fa-info-circle"></i>';
                if ($requisitionOrder->status == 1) {
                    $icon = '<i class="fa fa-plus"></i>';
                }
                $btn .= ' <a  href="' . route('requisition-order.details', ['requisitionOrder' => $requisitionOrder->id]) . '" class="dropdown-item">' . $icon . ' Invoice</a>';
                if ($requisitionOrder->status == 1 && $requisitionOrder->d_status != 1 && $requisitionOrder->divisional_user_id == auth()->user()->client_id) {
                    $btn .= ' <a  data-id="' . $requisitionOrder->id . '" role="button" class="dropdown-item approved"><i class="fa fa-info-circle"></i> Approved</a>';
                }
                if ($requisitionOrder->status == 1 && $requisitionOrder->d_status == 1 && $requisitionOrder->h_status != 1 && auth()->user()->role == 'Admin') {
                    $btn .= ' <a  data-id="' . $requisitionOrder->id . '" role="button" class="dropdown-item approved"><i class="fa fa-info-circle"></i> Approved</a>';
                }
                if($requisitionOrder->status == 1 && $requisitionOrder->d_status == 1 && $requisitionOrder->h_status == 1 && auth()->user()->role == 'SR' && $requisitionOrder->order_status == 0){
                    $btn .= ' <a  href="'. route('requisition-order.make_order',['requisitionOrder'=>$requisitionOrder->id]) .' " class="dropdown-item"><i class="fa fa-info-circle"></i> Make Order</a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('sr_name', function (RequisitionOrder $requisitionOrder) {
                return $requisitionOrder->sr->name ?? '';
            })
            ->addColumn('client_name', function (RequisitionOrder $requisitionOrder) {
                return $requisitionOrder->client->name ?? '';
            })
            ->addColumn('client_type', function (RequisitionOrder $requisitionOrder) {
                return $requisitionOrder->client->client_type ?? '';
            })
            ->addColumn('document',function(RequisitionOrder $requisitionOrder){
                $preview_html = '';
                $preview_html .= '<div class="zoom-gallery">';
                $preview_html .= '<a class="image_preview" title="'. $requisitionOrder->order_no .'" data-source="'. asset(file_exists($requisitionOrder->document) ? $requisitionOrder->document : 'img/no-image.webp') .'" href="'. asset(file_exists($requisitionOrder->document) ? $requisitionOrder->document : 'img/no-image.webp') .'"><img height="50px" src="'.asset(file_exists($requisitionOrder->document) ? $requisitionOrder->document : 'img/no-image.webp').'"></a>';
                $preview_html .= '</div>';
                return $preview_html;
            })
            ->addColumn('status', function (RequisitionOrder $requisitionOrder) {
                $preview_html = '';
                $preview_html .= '<ul style="padding-left: 0;list-style: none">';
                if($requisitionOrder->h_status == 1){
                    $preview_html .= '<li><i style="color: #1ac343;" class="fas fa-check-double"></i> Head of Department Approved</li>';
                }else{
                    $preview_html .= '<li><i style="color:rgb(190, 21, 21);" class="fas fa-times"></i> Head of Department Approved</li>';
                }
                if($requisitionOrder->d_status == 1){
                    $preview_html .= '<li><i style="color: #1ac343;" class="fas fa-check-double"></i> Division Head Approved</li>';
                }else{
                    $preview_html .= '<li><i style="color:rgb(190, 21, 21);" class="fas fa-times"></i> Division Head Approved</li>';
                }
                if($requisitionOrder->status == 1){
                    $preview_html .= '<li><i style="color: #1ac343;" class="fas fa-check-double"></i> SR Approved</li>';
                }else{
                    $preview_html .= '<li><i style="color:rgb(190, 21, 21);" class="fas fa-times"></i> SR Approved</li>';
                }
                $preview_html .= '</li>';
                return $preview_html;
            })
            ->rawColumns(['action', 'status','document'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $srs = [];
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin', 'Divisional Admin'])){
            $srs = Client::where('status', 1)->where('type', 'SR')->get(); //type=2 is SR
            $clients = Client::where('status', 1)->where('type', 'Client')->get(); //type=4 is Client
        }else{
            $srs = Client::where('status', 1)->where('type','SR')->where('id',auth()->user()->client_id)->first(); //type 2 for SR
            $clients = Client::where('status', 1)->where('type', 'Client')->where('sr_id',auth()->user()->client_id)->get(); //type=4 is Client
        }

        $products = [];

        $products = Product::where('status', 1)->get();

        $pageTitle = 'Order Create';

        return view('requisition_order.create', compact(
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

        // dd(request()->all());
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
            'advance' => 'nullable',
            'document' => 'nullable',
            'notes' => 'nullable|string|max:255',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $sr = Client::find($request->sr);
            $requisitionOrder = new RequisitionOrder();
            $requisitionOrder->sr_id = $request->sr;
            $requisitionOrder->divisional_user_id = $sr->parent_id;//divisional admin
            $requisitionOrder->client_id = $request->client;
            $requisitionOrder->total = 0;
            $requisitionOrder->advance = 0;
            $requisitionOrder->costing_total = 0;
            $requisitionOrder->notes = $request->notes;
            // $requisitionOrder->created_by = auth()->id();
            $requisitionOrder->date = Carbon::parse($request->date);
            $requisitionOrder->save();
            // if ($request->type == 1) {
            //     $requisitionOrder->order_no = 'Sale-' . date('Ymd') . '-' . $requisitionOrder->id;

            //     $latitude = $request->latitude;
            //     $longitude = $request->longitude;

            //     $location_address = getLocationName($latitude, $longitude);

            //     $locationAddressInfo = new LocationAddressInfo();
            //     $locationAddressInfo->sale_order_id = $requisitionOrder->id;
            //     $locationAddressInfo->sr_id = $request->sr;
            //     $locationAddressInfo->client_id = $request->client;
            //     $locationAddressInfo->invoice_time = Carbon::now();
            //     $locationAddressInfo->location_address = $location_address;
            //     $locationAddressInfo->latitude = $request->latitude;
            //     $locationAddressInfo->longitude = $request->longitude;
            //     $locationAddressInfo->save();
            // }
            $document = null;
            if ($request->hasFile('document')){
                // Upload Image
                $file = $request->file('document');
                $filename = Uuid::uuid1()->toString() . '.' . $file->extension();
                $destinationPath = 'uploads/requisition/document';
                // $file->move(public_path($destinationPath), $filename);
                $file->move($destinationPath, $filename);
                $document = 'uploads/requisition/document/' . $filename;
            }
            $requisitionOrder->document =  $document;
            $requisitionOrder->order_no = 'RO-' . date('Ymd') . '-' . $requisitionOrder->id;
            $requisitionOrder->save();

            $costingTotal = 0;
            $total = 0;
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($request->product_id[$key]);

                $product->update([
                    'purchase_price' => $request->purchase_unit_price[$key],
                    'selling_price' => $request->selling_unit_price[$key],
                ]);
                $requisitionOrderItem = new RequisitionOrderItem();
                $requisitionOrderItem->requisition_order_id = $requisitionOrder->id;
                $requisitionOrderItem->client_id = $request->client;
                $requisitionOrderItem->sr_id = $request->sr;
                $requisitionOrderItem->product_id = $product->id;
                $requisitionOrderItem->product_code = $product->code;
                $requisitionOrderItem->damage_quantity = $request->damage_return_product_qty[$key] ?? 0;
                $requisitionOrderItem->sr_sale_quantity = $request->product_qty[$key];
                $requisitionOrderItem->sale_quantity = 0;
                $requisitionOrderItem->purchase_unit_price = $request->purchase_unit_price[$key];
                $requisitionOrderItem->selling_unit_price = $request->selling_unit_price[$key];
                $requisitionOrderItem->save();

                $total += ($request->product_qty[$key] * $request->selling_unit_price[$key]);
                $costingTotal += ($request->product_qty[$key] ?? 0) * $request->purchase_unit_price[$key];

            }
            $requisitionOrder->total = $total;
            $requisitionOrder->advance = $request->advance;
            $requisitionOrder->costing_total = $costingTotal;
            $requisitionOrder->save();


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('requisition-order.details', ['requisitionOrder' => $requisitionOrder->id])->with('success', 'Distribution requisition created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            dd($e->getMessage());
            // Handle the error and redirect with an error message
            return redirect()->route('requisition-order.create', ['company' => $request->company])->withInput()->with('error', $e->getMessage());
        }
    }

    public function requisitionDetails(RequisitionOrder $requisitionOrder, Request $request)
    {
        $requisitionOrder->load('requisitionOrderItems');
        // dd('hi');
        $products = Product::whereIn('id', $requisitionOrder->requisitionOrderItems->pluck('product_id'))->get();
        $clients = Client::where('type','Client')->get();
        $pageTitle = 'Requisition Sales Invoice';

        return view('requisition_order.requisition_details', compact(
            'requisitionOrder',
            'pageTitle',
            'products',
            'clients'
        ));
    }

    public function requisitionApproved(RequisitionOrder $requisitionOrder,Request $request){
        try{
            // dd(auth()->user());
            if(auth()->user()->role == 'Divisional Admin'){
                $requisitionOrder->d_status = 1;
            }
            if(auth()->user()->role == 'Admin'){
                $requisitionOrder->department_head_id = auth()->user()->id;
                $requisitionOrder->h_status = 1;

                $client = Client::find($requisitionOrder->client_id);
                $client->balance += $requisitionOrder->advance;
                $client->save();
            }
            $requisitionOrder->save();

            return response()->json(['success'=>true,'message'=>'']);
        }catch(Exception $exception){
            return response()->json(['success'=>false,'error'=>$exception->getMessage()]);
        }
    }
    public function requisitionMakeOrder(RequisitionOrder $requisitionOrder){
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin', 'Divisional Admin'])){
            $srs = Client::where('status', 1)->where('type', 'SR')->get(); //type=2 is SR
            $clients = Client::where('status', 1)->where('type', 'Client')->get(); //type=4 is Client
        }else{
            $srs = Client::where('status', 1)->where('type','SR')->where('id',auth()->user()->client_id)->first(); //type 2 for SR
            $clients = Client::where('status', 1)->where('type', 'Client')->where('sr_id',auth()->user()->client_id)->get(); //type=4 is Client
        }
        $products = Product::where('status', 1)->get();

        $pageTitle = 'Make Order from Requisition';
        return view('requisition_order.make_order',compact('requisitionOrder','pageTitle','srs','clients','products'));
    }
    public function requisitionMakeOrderPost(RequisitionOrder $requisitionOrder,Request $request){
        // Validate the request data
        // dd($request->all());
        $validatedData = $request->validate([
            'sr' => 'required',
            'client' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'product_id.*' => 'required',
            'damage_return_product_qty.*' => 'required|numeric|min:0',
            'product_qty.*' => 'required|numeric|min:0',
            'purchase_price.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $client = Client::find($request->client);
            $total = 0;
            foreach ($request->product_id as $key => $productId) {
                $total += (($request->product_qty[$key]-$request->damage_return_product_qty[$key]) * $request->selling_unit_price[$key]);
            }
            if($client->client_type == 'Credit'){
                if($client->balance < $total){
                    return redirect()->back()->withInput()->with('error','Insufficient Balance');
                }
            }else{
                if($client->debit_balance < $total){
                    return redirect()->back()->withInput()->with('error','Insufficient Debit Balance');
                }
            }

            $saleOrder = new SaleOrder();
            $saleOrder->department_head_id = $requisitionOrder->department_head_id;
            $saleOrder->divisional_user_id = $requisitionOrder->divisional_user_id;
            $saleOrder->type = 1;
            $saleOrder->requisition_order_id = $requisitionOrder->id;
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

            $saleOrder->order_no = 'SO-' . date('Ymd') . '-' . $saleOrder->id;
            $saleOrder->save();

            if ($request->latitude && $request->longitude) {
                $newLatitude = $request->latitude;
                $newLongitude = $request->longitude;

                $user = auth()->user();
                // Bulk update user data in one query
                $user->update([
                    'is_online' => ($user->is_online == 0) ? 1 : $user->is_online,
                    'latest_latitude' => $newLatitude,
                    'latest_longitude' => $newLongitude,
                ]);

                $currentDate = now()->format('Y-m-d');
                // $newPoint = [$newLatitude, $newLongitude];
                $newPoint = [
                    'latitude' => $newLatitude,      
                    'longitude' => $newLongitude,    
                    'order_status' => 1,      
                    'order_no' => $saleOrder->order_no  
                ];

                $location = Location::where('user_id', $user->id)
                    ->where('date', $currentDate)
                    ->first();

                $existingHistory = $location ? json_decode($location->history, true) : [];
                $existingHistory[] = $newPoint;

                Location::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $currentDate],
                    ['history' => json_encode($existingHistory)]
                );

                // Fire location update event
                event(new LocationUpdate([
                    'user_id' => $user->id,
                    'latitude' => $newLatitude,
                    'longitude' => $newLongitude,
                ]));
            }

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
                $saleOrderItem->sale_quantity = $request->product_qty[$key] - $request->damage_return_product_qty[$key];
                $saleOrderItem->purchase_unit_price = $request->purchase_unit_price[$key];
                $saleOrderItem->selling_unit_price = $request->selling_unit_price[$key];
                $saleOrderItem->save();

                $total += (($request->product_qty[$key]-$request->damage_return_product_qty[$key]) * $request->selling_unit_price[$key]);
                $costingTotal += ($request->product_qty[$key] - $request->damage_return_product_qty[$key]) * $request->purchase_unit_price[$key];

                //Inventory Log
                // $inventoryLog = new InventoryLog();
                // $inventoryLog->type = 2; //Distribution Out
                // $inventoryLog->sr_id = $request->sr;
                // $inventoryLog->client_id = $request->client;
                // $inventoryLog->sale_order_id = $saleOrder->id;
                // $inventoryLog->product_id = $product->id;
                // $inventoryLog->quantity = $request->product_qty[$key] - $request->damage_return_product_qty[$key];
                // $inventoryLog->unit_price = $request->selling_unit_price[$key];
                // $inventoryLog->product_code = $product->code;
                // $inventoryLog->notes = $request->notes;
                // $inventoryLog->user_id = auth()->id();
                // $inventoryLog->date = Carbon::parse($request->date);
                // $inventoryLog->save();
            }
            // dd($total);
            $saleOrder->total = $total;
            if($client->client_type == 'Credit'){
                $saleOrder->paid = $total;
                $saleOrder->due = 0;
                $client->balance -= $total;
                $client->save();
            }else{
                $saleOrder->paid = 0;
                $saleOrder->due = $total;
                $client->debit_balance -= $total;
                $client->save();
            }
            $saleOrder->costing_total = $costingTotal;
            $saleOrder->status = 3;
            $saleOrder->save();

            $requisitionOrder->order_status = 1;
            $requisitionOrder->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('sales-order.invoice', ['saleOrder' => $saleOrder->id])->with('success', 'Distribution sales created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
    public function finalSalePost(RequisitionOrder $requisitionOrder, Request $request)
    {
        if ($requisitionOrder->status != 2) {
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

            $requisitionOrder->total = $total;
            $requisitionOrder->paid = $request->paid;
            $requisitionOrder->due = $total - $requisitionOrder->paid;
            $requisitionOrder->status = 3;
            $requisitionOrder->save();

            if($request->paid > 0){
                $salePayment = new SalePayment();
                $salePayment->sr_id = $requisitionOrder->sr_id;
                $salePayment->sale_order_id = $requisitionOrder->id;
                $salePayment->client_id = $requisitionOrder->client_id;
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
                $transaction->amount = $requisitionOrder->paid;
                $transaction->sr_id = $requisitionOrder->sr_id;
                $transaction->client_id = $requisitionOrder->client_id;
                $transaction->date = Carbon::now()->format('Y-m-d');
                $transaction->sale_payment_id = $salePayment->id;
                $salePayment->sale_order_id = $requisitionOrder->id;
                $transaction->user_id = auth()->id();
                $transaction->save();

                Cash::first()->increment('amount',$requisitionOrder->paid);
            }


            DB::commit();

            return redirect()
                ->route('sales-order.day_close', ['requisitionOrder' => $requisitionOrder->id, 'type' => $saleOrder->type])
                ->with('message', 'Sale order confirm successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function inTransitPost(RequisitionOrder $requisitionOrder, Request $request)
    {

        DB::beginTransaction();
        try {

            $requisitionOrder->status= 2;
            $requisitionOrder->in_transit_by= auth()->id();
            $requisitionOrder->in_transit_date=Carbon::now()->format('Y-m-d');
            $requisitionOrder->save();

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
}
