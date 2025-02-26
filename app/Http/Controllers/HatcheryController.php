<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\District;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class HatcheryController extends Controller
{
    public function index()
    {
        return view('settings.hatchery.index');
    }
    public function dataTable()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = Client::with('district','thana')->where('type','Hatchery'); //Farm
        }else{
            $query = Client::with('district','thana')->where('type','Hatchery')->where('parent_id',auth()->user()->client_id); //type 5 for Farm
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $hatchery) {
                $btn = '';
                $btn  .='<a href="'.route('hatchery.edit',['hatchery'=>$hatchery->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <a role="button" data-id="' . $hatchery->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->addColumn('district_name', function(Client $farm) {
                return $farm->district->name_eng ?? '';
            })
            ->addColumn('thana_name', function(Client $farm) {
                return $farm->thana->name_eng ?? '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $districts = District::where('status',1)->get();
        return view('settings.hatchery.create',compact('districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type','Farm')
            ],
            'district' =>['required'],
            'thana' =>['required'],
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $location_address = getLocationName($request->latitude, $request->longitude);
            // Create a new Client record in the database
            $validatedData['type'] = 'Hatchery';
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];
            $validatedData['location_address'] = $location_address;
            unset($validatedData['district']);
            unset($validatedData['thana']);
            Client::create($validatedData);

            // Commit the transaction
            DB::commit();
            return redirect()->route('hatchery.index')->with('success', 'Hatchery created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('hatchery.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the Hatchery: '.$e->getMessage());
        }
    }
    public function edit(Client $hatchery)
    {
        if ($hatchery->type != 'Hatchery') {
            abort(404);
        }
        try {
            $districts = District::where('status',1)->get();
            return view('settings.hatchery.edit', compact('hatchery','districts'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('hatchery.index')->with('error', 'Hatchery not found: '.$e->getMessage());
        }
    }
    public function update(Request $request, Client $hatchery)
    {
        if ($hatchery->type != 'Hatchery') {
            abort(404);
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type','Hatchery')
                    ->ignore($hatchery)
            ],
            'district' =>['required'],
            'thana' =>['required'],
            'longitude' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);
        DB::beginTransaction();

        try {
            $location_address = getLocationName($request->latitude, $request->longitude);
            $validatedData['type'] = 'Hatchery';
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];
            $validatedData['location_address'] = $location_address;
            unset($validatedData['district']);
            unset($validatedData['thana']);
            $hatchery->update($validatedData);

            DB::commit();
            return redirect()->route('hatchery.index')->with('success', 'Hatchery updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('hatchery.edit',['hatchery'=>$hatchery->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the Hatchery: '.$e->getMessage());
        }

    }
    public function destroy(Client $hatchery)
    {
        try {
            if (!auth()->user()->hasPermissionTo('customer_delete')) {
                abort(403, 'Unauthorized');
            }
//            $distributionOrder = DistributionOrder::where('dsr_id',$farm->id)->first();
//            if ($distributionOrder) {
//                // If a related Client exists, return an error message
//                return response()->json(['success' => false, 'message' => 'It has distribution order order logs. Cannot delete.'], Response::HTTP_OK);
//            }
            // Delete the Client record
            $hatchery->delete();
            return response()->json(['success'=>true,'message' => 'Hatchery deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message' => 'Hatchery not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
