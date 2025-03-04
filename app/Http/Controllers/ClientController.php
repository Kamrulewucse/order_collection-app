<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.client.index');
    }
    public function dataTable()
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = Client::with('sr','district','thana')->where('type',4);//Client
        }else{
            $query = Client::with('sr','district','thana')->where('type',4)->where('sr_id',auth()->user()->client_id);//Client
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $client) {
                $btn = '';
                $btn  .='<a href="'.route('client.edit',['client'=>$client->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <a role="button" data-id="' . $client->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';

                return $btn;
            })
            ->addColumn('sr_name', function(Client $client) {
                return $client->sr->name ?? '';
            })
            ->addColumn('district_name', function(Client $client) {
                return $client->district->name_eng ?? '';
            })
            ->addColumn('thana_name', function(Client $client) {
                return $client->thana->name_eng ?? '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $srs = [];
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin', 'Divisional Admin'])){
            $srs = Client::where('type',2)->where('status',1)->get(); //type 2  for SR
        }else{
            $srs = Client::where('type',2)->where('id',auth()->user()->client_id)->first(); //type 2 for SR
        }
        // dd();
        $districts = District::where('status',1)->get();
        return view('settings.client.create',compact('srs','districts'));
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
                Rule::unique('clients')
                ->where('type',4)
            ],
            'shop_name' =>[
                'required','max:255',
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',4)
            ],
            'sr' =>['required'],
            'client_type' =>['required'],
            'district' =>['required'],
            'thana' =>['required'],
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'debit_balance' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $location_address = getLocationName($request->latitude, $request->longitude);
            // Create a new Client record in the database
            $validatedData['type'] = 4;
            $validatedData['sr_id'] = $validatedData['sr'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];
            $validatedData['location_address'] = $location_address;
            unset($validatedData['sr']);
            unset($validatedData['district']);
            unset($validatedData['thana']);

            $client = Client::create($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('client.index')->with('success', 'Client created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            // Handle the error and redirect with an error message
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the Customer: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        if ($client->type != 4) {
            abort(404);
        }
        try {
            // If the Client exists, display the edit view
            $srs = [];
            if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin', 'Divisional Admin'])){
                $srs = Client::where('type',2)->where('status',1)->get(); // 2 for SR
            }else{
                $srs = Client::where('type',2)->where('id',auth()->user()->client_id)->first(); //type 2 for SR
            }
            
            $districts = District::where('status',1)->get();
            return view('settings.client.edit', compact('client','srs','districts'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Client is not found
            return redirect()->route('client.index')->with('error', 'Client not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        if ($client->type != 4) {
            abort(404);
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',4)
                    ->ignore($client)
            ],
            'shop_name' =>[
                'required','max:255',
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',4)
                    ->ignore($client)
            ],
            'sr' =>['required'],
            'client_type' =>['required'],
            'district' =>['required'],
            'thana' =>['required'],
            'longitude' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'debit_balance' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            //Get addresss from latitude and longitude
            $location_address = getLocationName($request->latitude, $request->longitude);
            // Update the Client record in the database
            $validatedData['type'] = 4;
            $validatedData['sr_id'] = $validatedData['sr'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];
            $validatedData['location_address'] = $location_address;
            unset($validatedData['sr']);
            unset($validatedData['district']);
            unset($validatedData['thana']);

            $client->update($validatedData);


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('client.index')->with('success', 'Client updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('client.edit',['client'=>$client->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the Customer: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        try {
            // Delete the Client record
            $client->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Client deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Client not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
