<?php

namespace App\Http\Controllers;

use App\Models\AccountHead;
use App\Models\Channel;
use App\Models\DistributionOrder;
use App\Models\Network;
use App\Models\PurchaseOrder;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.doctor.index');
    }
    public function dataTable()
    {
        $query = Client::where('type',3);//Doctor
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $client) {
                $btn = '';
                if(auth()->user()->can('dsr_edit')){
                    $btn  .='<a href="'.route('doctor.edit',['doctor'=>$client->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if(auth()->user()->can('dsr_delete')) {
                    $btn .= ' <a role="button" data-id="' . $client->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('dsr_create')) {
            abort(403, 'Unauthorized');
        }
        return view('settings.doctor.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('dsr_create')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('clients')
                ->where('type',3)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',3)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
                    ->where('type',3)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Client record in the database
            $validatedData['type'] = 3;
            $doctor = Client::create($validatedData);
            // Create/Update User
            $user = User::where('role','Doctor')->where('client_id',$doctor->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'Doctor';
            $user->client_id = $doctor->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->password = bcrypt($request->password);
            $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('doctor.index')->with('success', 'Doctor created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('doctor.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the Doctor: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $doctor)
    {
        if (!auth()->user()->hasPermissionTo('dsr_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($doctor->type != 3) {
            abort(404);
        }
        try {
            // If the Client exists, display the edit view
            return view('settings.doctor.edit', compact('doctor'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Client is not found
            return redirect()->route('doctor.index')->with('error', 'Doctor not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $doctor)
    {
        if (!auth()->user()->hasPermissionTo('dsr_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($doctor->type != 3) {
            abort(404);
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',3)
                    ->ignore($doctor)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',3)
                    ->ignore($doctor)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
                    ->where('type',3)
                    ->ignore($doctor)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Client record in the database
            $validatedData['type'] = 3;
            $doctor->update($validatedData);

            // Create/Update User
            $user = User::where('role','Doctor')->where('client_id',$doctor->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'Doctor';
            $user->client_id = $doctor->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->password = bcrypt($request->password);
            $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('doctor.index')->with('success', 'Doctor updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('doctor.edit',['doctor'=>$doctor->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the Doctor: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $doctor)
    {
        try {
            $doctor->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Doctor deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Doctor not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
