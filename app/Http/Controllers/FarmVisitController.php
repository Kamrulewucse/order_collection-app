<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FarmVisit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;

class FarmVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $farmVisits = FarmVisit::with('doctor','farm')->get();
        }else{
            $farmVisits = FarmVisit::with('doctor','farm')->where('doctor_id',auth()->user()->client_id)->get();
        }
        return view('farm_visit.index',compact('farmVisits'));
    }

    // public function dataTable()
    // {
    //     if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
    //         $query = FarmVisit::with('doctor','farm');
    //     }else{
    //         $query = FarmVisit::with('doctor','farm')->where('doctor_id',auth()->user()->client_id);
    //     }
    //     return DataTables::eloquent($query)
    //         ->addIndexColumn()
    //         ->addColumn('action', function(FarmVisit $farmVisit) {
    //             $btn = '';
    //             $btn  .='<a href="'.route('farm-visit.edit',['client'=>$farmVisit->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
    //             $btn .= ' <a role="button" data-id="' . $farmVisit->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';

    //             return $btn;
    //         })
    //         ->addColumn('doctor_name', function(FarmVisit $farmVisit) {
    //             return $farmVisit->doctor->name ?? '';
    //         })

    //         ->rawColumns(['action'])
    //         ->toJson();
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctors = [];
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $doctors = Client::where('status', 1)->where('type', 3)->get(); //type=3 is Doctor
        }else{
            $doctors = Client::where('status', 1)->where('type',3)->where('id',auth()->user()->client_id)->first(); //type=3 is Doctor
        }

        $farms = Client::where('status', 1)->where('type',5)->get(); //type=5 is farm
        return view('farm_visit.create', compact(
            'doctors','farms'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'doctor' => 'required',
            'farm' => 'required',
            'longitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $location_address = getLocationName($request->latitude, $request->longitude);
            // Create a new Client record in the database

            if ($request->employee_photo_src) {
                $data = $request->employee_photo_src;
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $imgeData = base64_decode($data);
                $image_name = "/uploads/visit/" . Uuid::uuid1()->toString() . '.png';
                $path = public_path() . $image_name;
                file_put_contents($path, $imgeData);
            }

            $farmVisit = new FarmVisit();
            $farmVisit->doctor_id = $request->doctor;
            $farmVisit->farm_id = $request->farm;
            $farmVisit->visit_time = Carbon::now();
            $farmVisit->date = Carbon::now();
            $farmVisit->latitude = $request->latitude;
            $farmVisit->longitude = $request->longitude;
            $farmVisit->location_address = $location_address;
            $farmVisit->location_image = $image_name;
            $farmVisit->reason = $request->reason;
            $farmVisit->user_id = auth()->id();
            $farmVisit->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('farm-visit.index')->with('success', 'Farm Visit created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            // Handle the error and redirect with an error message
            return redirect()->route('farm-visit.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the Customer: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FarmVisit $farmVisit)
    {
        try {
            if (file_exists(public_path($farmVisit->location_image))) {
                unlink(public_path($farmVisit->location_image)); // Delete the image file
            }
            // Delete the Client record
            $farmVisit->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Farm Visit deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Farm Visit not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
