<?php

namespace App\Http\Controllers;

use App\Models\ChickProduction;
use App\Models\ChickProductionItem;
use App\Models\Product;
use App\Models\Client;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Ramsey\Uuid\Uuid;

class ChickProductionController extends Controller
{

    public function index(Request $request)
    {
        $pageTitle = 'Chick Productions';

        $managers = Client::where('type','Hatchery_Manager')->where('status',1)->get();
        return view('chick_production.index', compact(
            'pageTitle',
            'managers'
        ));
    }
    public function dataTable()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = ChickProduction::with('hatchery','hatcheryManager');
        }elseif(in_array(auth()->user()->role, ['Divisional Admin'])){
            $query = ChickProduction::with('hatchery','hatcheryManager')->where('divisional_user_id',auth()->user()->client_id);
        }else{
            $query = ChickProduction::with('hatchery','hatcheryManager')->where('sr_id',auth()->user()->client_id);
        }

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if (request()->has('sr') && request('sr') != '') {
            $query->where('sr_id', request('sr'));
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (ChickProduction $chickProduction) {
                $btn = '';
                $icon = '<i class="fa fa-info-circle"></i>';
                if ($chickProduction->status == 1) {
                    $icon = '<i class="fa fa-plus"></i>';
                }
                $btn .= ' <a  href="' . route('chick-production.details', ['chickProduction' => $chickProduction->id]) . '" class="dropdown-item">' . $icon . ' Invoice</a>';
                // if ($chickProduction->status == 1 && $chickProduction->d_status != 1 && $chickProduction->divisional_user_id == auth()->user()->client_id) {
                //     $btn .= ' <a  data-id="' . $chickProduction->id . '" role="button" class="dropdown-item approved"><i class="fa fa-info-circle"></i> Approved</a>';
                // }
                // if ($chickProduction->status == 1 && $chickProduction->d_status == 1 && $chickProduction->h_status != 1 && auth()->user()->role == 'Admin') {
                //     $btn .= ' <a  data-id="' . $chickProduction->id . '" role="button" class="dropdown-item approved"><i class="fa fa-info-circle"></i> Approved</a>';
                // }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('hatchery_name', function (ChickProduction $chickProduction) {
                return $chickProduction->hatchery->name ?? '';
            })
            ->addColumn('hatchery_manager', function (ChickProduction $chickProduction) {
                return $chickProduction->hatcheryManager->name ?? '';
            })
            ->addColumn('total_loss_product', function (ChickProduction $chickProduction) {
                return $chickProduction->total_raw_product - $chickProduction->total_finished_product;
            })
            ->addColumn('document',function(ChickProduction $chickProduction){
                $preview_html = '';
                $preview_html .= '<div class="zoom-gallery">';
                $preview_html .= '<a class="image_preview" title="'. $chickProduction->order_no .'" data-source="'. asset(file_exists($chickProduction->document) ? $chickProduction->document : 'img/no-image.webp') .'" href="'. asset(file_exists($chickProduction->document) ? $chickProduction->document : 'img/no-image.webp') .'"><img height="50px" src="'.asset(file_exists($chickProduction->document) ? $chickProduction->document : 'img/no-image.webp').'"></a>';
                $preview_html .= '</div>';
                return $preview_html;
            })
            ->addColumn('status', function (ChickProduction $chickProduction) {
                $preview_html = '';
                // $preview_html .= '<ul style="padding-left: 0;list-style: none">';
                // if($chickProduction->h_status == 1){
                //     $preview_html .= '<li><i style="color: #1ac343;" class="fas fa-check-double"></i> Head of Department Approved</li>';
                // }else{
                //     $preview_html .= '<li><i style="color:rgb(190, 21, 21);" class="fas fa-times"></i> Head of Department Approved</li>';
                // }
                // if($chickProduction->d_status == 1){
                //     $preview_html .= '<li><i style="color: #1ac343;" class="fas fa-check-double"></i> Division Head Approved</li>';
                // }else{
                //     $preview_html .= '<li><i style="color:rgb(190, 21, 21);" class="fas fa-times"></i> Division Head Approved</li>';
                // }
                // if($chickProduction->status == 1){
                //     $preview_html .= '<li><i style="color: #1ac343;" class="fas fa-check-double"></i> SR Approved</li>';
                // }else{
                //     $preview_html .= '<li><i style="color:rgb(190, 21, 21);" class="fas fa-times"></i> SR Approved</li>';
                // }
                // $preview_html .= '</li>';
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
            $managers = Client::where('type','Hatchery_Manager')->where('status',1)->get();
        }else{
            $managers = Client::where('type','Hatchery_Manager')->where('status',1)->where('id',auth()->user()->client_id)->get();
        }

        $products = [];

        $raw_products = Product::where('type',1)->where('status', 1)->get();
        $finished_products = Product::where('type',2)->where('status', 1)->get();

        $pageTitle = 'Chick Production Create (Estimation)';

        return view('chick_production.create', compact(
            'managers',
            'raw_products',
            'finished_products',
            'pageTitle',
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
            'hatchery_manager' => 'required',
            'raw_product_id.*' => 'required',
            'finished_product_id.*' => 'required',
            'raw_product_qty.*' => 'required|numeric',
            'finished_product_qty.*' => 'required|numeric',
            'date' => 'required|date',
            'production_date' => 'required|date',
            'document' => 'nullable',
            'notes' => 'nullable|string|max:255',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $hatcheryManager = Client::find($request->hatchery_manager);
            $chickProduction = new ChickProduction();
            $chickProduction->hatchery_id = $hatcheryManager->parent_id;
            $chickProduction->hatchery_manager_id = $hatcheryManager->id;
            $chickProduction->total_raw_product = 0;
            $chickProduction->total_finished_product = 0;
            $chickProduction->notes = $request->notes;
            $chickProduction->date = Carbon::parse($request->date);
            $chickProduction->production_date = Carbon::parse($request->production_date);
            $chickProduction->save();

            $document = null;
            if ($request->hasFile('document')){
                // Upload Image
                $file = $request->file('document');
                $filename = Uuid::uuid1()->toString() . '.' . $file->extension();
                $destinationPath = 'uploads/production/document';
                // $file->move(public_path($destinationPath), $filename);
                $file->move($destinationPath, $filename);
                $document = 'uploads/production/document/' . $filename;
            }
            $chickProduction->document =  $document;
            $chickProduction->order_no = 'CPO-' . date('Ymd') . '-' . $chickProduction->id;
            $chickProduction->save();

            $totalRawProduct = 0;
            $totalFinishedProduct = 0;
            foreach ($request->raw_product_id as $key => $productId) {
                $rawProduct = Product::find($request->raw_product_id[$key]);
                $finishedProduct = Product::find($request->finished_product_id[$key]);

                $chickProductionItem = new ChickProductionItem();
                $chickProductionItem->chick_production_id = $chickProduction->id;
                $chickProductionItem->raw_product_id = $rawProduct->id;
                $chickProductionItem->finished_product_id = $finishedProduct->id;
                $chickProductionItem->raw_product_qty = $request->raw_product_qty[$key];
                $chickProductionItem->loss_product_percentage = $request->loss_product_percentage[$key];
                $chickProductionItem->finished_product_qty = $request->finished_product_qty[$key];
                $chickProductionItem->save();

                $totalRawProduct += $request->raw_product_qty[$key];
                $totalFinishedProduct += $request->raw_product_qty[$key] - (($request->raw_product_qty[$key]*$request->loss_product_percentage[$key])/100);

            }
            $chickProduction->total_raw_product = $totalRawProduct;
            $chickProduction->total_finished_product = $totalFinishedProduct;
            $chickProduction->save();

            DB::commit();

            return redirect()->route('chick-production.details', ['chickProduction' => $chickProduction->id])->with('success', 'Production created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
            return redirect()->route('chick-production.create')->withInput()->with('error', $e->getMessage());
        }
    }

    public function chickProductionDetails(ChickProduction $chickProduction, Request $request)
    {
        $chickProduction->load('chickProductionItems');
        // dd('hi');
        $clients = Client::where('type','Client')->get();
        $pageTitle = 'Chick Production Invoice';

        return view('chick_production.details', compact(
            'chickProduction',
            'pageTitle',
            'clients'
        ));
    }

    public function requisitionApproved(ChickProduction $chickProduction,Request $request){
        try{
            // dd(auth()->user());
            if(auth()->user()->role == 'Divisional Admin'){
                $chickProduction->d_status = 1;
            }
            if(auth()->user()->role == 'Admin'){
                $chickProduction->department_head_id = auth()->user()->id;
                $chickProduction->h_status = 1;

                $client = Client::find($chickProduction->client_id);
                $client->balance += $chickProduction->advance;
                $client->save();
            }
            $chickProduction->save();

            return response()->json(['success'=>true,'message'=>'']);
        }catch(Exception $exception){
            return response()->json(['success'=>false,'error'=>$exception->getMessage()]);
        }
    }
    public function requisitionMakeOrder(ChickProduction $chickProduction){
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin', 'Divisional Admin'])){
            $srs = Client::where('status', 1)->where('type', 'SR')->get(); //type=2 is SR
            $clients = Client::where('status', 1)->where('type', 'Client')->get(); //type=4 is Client
        }else{
            $srs = Client::where('status', 1)->where('type','SR')->where('id',auth()->user()->client_id)->first(); //type 2 for SR
            $clients = Client::where('status', 1)->where('type', 'Client')->where('sr_id',auth()->user()->client_id)->get(); //type=4 is Client
        }
        $products = Product::where('status', 1)->get();

        $pageTitle = 'Make Order from Requisition';
        return view('requisition_order.make_order',compact('chickProduction','pageTitle','srs','clients','products'));
    }
}
