<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class FarmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.farm.index');
    }
    public function dataTable()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = Client::with('district','thana')->where('type','Farm'); //Farm
        }else{
            $query = Client::with('district','thana')->where('type','Farm')->where('doctor_id',auth()->user()->client_id); //type 5 for Farm
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $farm) {
                $btn = '';
                $btn  .='<a href="'.route('farm.edit',['farm'=>$farm->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <a role="button" data-id="' . $farm->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->addColumn('doctor_name', function(Client $farm) {
                return $farm->parent->name ?? '';
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
        $doctors = [];
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $doctors = Client::where('type','Farm')->where('status',1)->get(); //type 3 for Doctor
        }else{
            $doctors = Client::where('type','Farm')->where('id',auth()->user()->client_id)->first(); //type 3 for doctor
        }
        $districts = District::where('status',1)->get();
        return view('settings.farm.create',compact('doctors','districts'));
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
            'shop_name' =>[
                'required','max:255',
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type','Farm')
            ],
            'doctor' =>['required'],
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
            $validatedData['type'] = 'Farm';
            $validatedData['parent_id'] = $validatedData['doctor'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];
            $validatedData['location_address'] = $location_address;
            unset($validatedData['doctor']);
            unset($validatedData['district']);
            unset($validatedData['thana']);
            $farm = Client::create($validatedData);
            // Create/Update User
            // $user = User::where('role','Farm')->where('client_id',$farm->id)->first();
            // if (!$user){
            //     $user = new User();
            // }
            // $user->role = 'Farm';
            // $user->client_id = $farm->id;
            // $user->name = $request->name;
            // $user->username  = $request->mobile_no;
            // $user->email = $request->email;
            // $user->mobile_no = $request->mobile_no;
            // $user->password = bcrypt('12345678');
            // $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('farm.index')->with('success', 'Farm created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            // Handle the error and redirect with an error message
            return redirect()->route('farm.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the Farm: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $farm)
    {
        if ($farm->type != 'Farm') {
            abort(404);
        }

        try {
            $doctors = [];
            if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
                $doctors = Client::where('type','Doctor')->where('status',1)->get(); //type 3 for Doctor
            }else{
                $doctors = Client::where('type','Doctor')->where('id',auth()->user()->client_id)->first(); //type 3 for doctor
            }

            $districts = District::where('status',1)->get();
            return view('settings.farm.edit', compact('farm','doctors','districts'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Client is not found
            return redirect()->route('farm.index')->with('error', 'Client not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $farm)
    {
        if ($farm->type != 'Farm') {
            abort(404);
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'shop_name' =>[
                'required','max:255',
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type','Farm')
                    ->ignore($farm)
            ],
            'doctor' =>['required'],
            'district' =>['required'],
            'thana' =>['required'],
            'longitude' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            //Get addresss from latitude and longitude
            $location_address = getLocationName($request->latitude, $request->longitude);
            // Update the Client record in the database
            $validatedData['type'] = 'Farm';
            $validatedData['parent_id'] = $validatedData['doctor'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];
            $validatedData['location_address'] = $location_address;
            unset($validatedData['doctor']);
            unset($validatedData['district']);
            unset($validatedData['thana']);
            $farm->update($validatedData);

            // $user = User::where('role','Farm')->where('client_id',$farm->id)->first();
            // if (!$user){
            //     $user = new User();
            // }
            // $user->role = 'Farm';
            // $user->client_id = $farm->id;
            // $user->name = $request->name;
            // $user->username  = $request->mobile_no;
            // $user->email = $request->email;
            // $user->mobile_no = $request->mobile_no;
            // $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('farm.index')->with('success', 'Farm updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('farm.edit',['farm'=>$farm->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the Farm: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $farm)
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
            $farm->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Farm deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Farm not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
