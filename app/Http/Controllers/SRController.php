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

class SRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.sr.index');
    }
    public function dataTable()
    {
        $query = Client::where('type',2);//DSR
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $client) {
                $btn = '';
                if(auth()->user()->can('dsr_edit')){
                    $btn  .='<a href="'.route('sr.edit',['sr'=>$client->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
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
        return view('settings.sr.create');
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
                ->where('type',2)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',2)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
                    ->where('type',2)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Client record in the database
            $validatedData['type'] = 2;
            $sr = Client::create($validatedData);
            // Create/Update User
            $user = User::where('role','SR')->where('client_id',$sr->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'SR';
            $user->client_id = $sr->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->password = bcrypt($request->password);
            $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('sr.index')->with('success', 'SR created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('sr.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the SR: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $sr)
    {
        if (!auth()->user()->hasPermissionTo('dsr_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($sr->type != 2) {
            abort(404);
        }
        try {
            // If the Client exists, display the edit view
            return view('settings.sr.edit', compact('sr'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Client is not found
            return redirect()->route('sr.index')->with('error', 'SR not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $sr)
    {
        if (!auth()->user()->hasPermissionTo('dsr_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($sr->type != 2) {
            abort(404);
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',2)
                    ->ignore($sr)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->where('type',2)
                    ->ignore($sr)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
                    ->where('type',2)
                    ->ignore($sr)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Client record in the database
            $validatedData['type'] = 2;
            $sr->update($validatedData);

            // Create/Update User
            $user = User::where('role','SR')->where('client_id',$sr->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'SR';
            $user->client_id = $sr->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->password = bcrypt($request->password);
            $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('sr.index')->with('success', 'SR updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('sr.edit',['dsr'=>$sr->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the SR: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $sr)
    {
        try {
            // Delete the Client record
            $sr->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'SR deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'SR not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
