<?php

namespace App\Http\Controllers;

use App\Models\AccountHead;
use App\Models\Channel;
use App\Models\DistributionOrder;
use App\Models\DivisionalUser;
use App\Models\Network;
use App\Models\PurchaseOrder;
use App\Models\Client;
use App\Models\District;
use App\Models\SaleOrder;
use App\Models\User;
use Exception;
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
        $query = Client::where('type','SR');//SR
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $client) {
                $btn = '';
                $btn  .='<a href="'.route('sr.edit',['sr'=>$client->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <a role="button" data-id="' . $client->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->addColumn('district_name', function(Client $client) {
                return $client->district->name_eng ?? '';
            })
            ->addColumn('divisional_admin', function(Client $client) {
                return $client->parent->name ?? '';
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
        $divisionalUsers = Client::where('type','Divisional Admin')->where('status',1)->get();
        $districts = District::where('status',1)->get();
        return view('settings.sr.create',compact('districts','divisionalUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
            ],
            'email' =>[
                'required','max:255',
                Rule::unique('clients')
            ],
            'divisional_user_id' =>['required'],
            'division' =>['required'],
            'district' =>['required'],
            'thana' =>['required'],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Client record in the database
            $validatedData['type'] = 'SR';
            $validatedData['parent_id'] = $validatedData['divisional_user_id'];
            $validatedData['division_id'] = $validatedData['division'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];

            unset($validatedData['divisional_user_id']);
            unset($validatedData['division']);
            unset($validatedData['district']);
            unset($validatedData['thana']);
            // dd($validatedData);
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
            $user->password = bcrypt('12345678');
            $user->save();

            // Commit the transaction
            DB::commit();
            return redirect()->route('sr.index')->with('success', 'SR created successfully');
        } catch (Exception $exception) {
            dd($exception->getMessage());
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while creating the SR: ' . $exception->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $sr)
    {
        if ($sr->type != 'SR') {
            abort(404);
        }
        $divisionalUsers = Client::where('type','Divisional Admin')->where('status',1)->get();
        $districts = District::where('status',1)->get();
        try {
            // If the Client exists, display the edit view
            return view('settings.sr.edit', compact('sr','districts','divisionalUsers'));
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
        if ($sr->type != 'SR') {
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
                    ->ignore($sr)
            ],
            'email' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->ignore($sr)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'divisional_user_id' =>['required'],
            'division' =>['required'],
            'district' =>['required'],
            'thana' =>['required'],
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Client record in the database
            $validatedData['type'] = 'SR';
            $validatedData['parent_id'] = $validatedData['divisional_user_id'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['division_id'] = $validatedData['division'];
            $validatedData['thana_id'] = $validatedData['thana'];

            unset($validatedData['divisional_user_id']);
            unset($validatedData['division']);
            unset($validatedData['district']);
            unset($validatedData['thana']);

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
            $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('sr.index')->with('success', 'SR updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            dd($e->getMessage());
            // Handle the error and redirect with an error message
            return redirect()->route('sr.edit',['sr'=>$sr->id])
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
            $saleOrder = SaleOrder::where('sr_id',$sr->id)->exists();
            if($saleOrder){
                return response()->json(['success'=>false,'message' => 'This SR cannot be deleted because it is associated with an existing Sale Order.'], Response::HTTP_OK);
            }else{
                User::where('client_id',$sr->id)->delete();
                // Delete the Client record
                $sr->delete();
            }

            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'SR deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'SR not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
