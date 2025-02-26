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

class HatcheryManagerController extends Controller
{
    public function index()
    {
        return view('settings.hatchery_manager.index');
    }
    public function dataTable()
    {
        $query = Client::where('type','Hatchery_Manager');//Doctor
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $hatchery_manager) {
                $btn = '';
                $btn  .='<a href="'.route('hatchery-manager.edit',['hatchery_manager'=>$hatchery_manager->id]).'" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <a role="button" data-id="' . $hatchery_manager->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->addColumn('hatchery', function(Client $hatchery_manager) {
                return $hatchery_manager->parent->name ?? '';
            })
            ->addColumn('district_name', function(Client $hatchery_manager) {
                return $hatchery_manager->district->name_eng ?? '';
            })
            ->addColumn('thana_name', function(Client $hatchery_manager) {
                return $hatchery_manager->thana->name_eng ?? '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $hatcheries = Client::where('type','Hatchery')->where('status',1)->get();
        $districts = District::where('status',1)->get();
        return view('settings.hatchery_manager.create',compact('districts','hatcheries'));
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
            'hatchery' =>['required'],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
            ],
            'email' =>[
                'required','max:255',
                Rule::unique('clients')
            ],
            'district' =>['required'],
            'thana' =>['required'],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'designation' => 'nullable', // Make 'designation' nullable
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Client record in the database
            $validatedData['type'] = 'Hatchery_Manager';
            $validatedData['parent_id'] = $validatedData['hatchery'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];

            unset($validatedData['hatchery']);
            unset($validatedData['district']);
            unset($validatedData['thana']);


            $hatcheryManager = Client::create($validatedData);
            // Create/Update User
            $user = User::where('role','Hatchery_Manager')->where('client_id',$hatcheryManager->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'Hatchery_Manager';
            $user->client_id = $hatcheryManager->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->password = bcrypt('12345678');
            $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('hatchery-manager.index')->with('success', 'Hatchery Manager created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('hatchery-manager.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the hatchery manager: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $hatchery_manager)
    {
        if ($hatchery_manager->type != 'Hatchery_Manager') {
            abort(404);
        }
        $hatcheries = Client::where('type','Hatchery')->where('status',1)->get();
        $districts = District::where('status',1)->get();
        try {
            // If the Client exists, display the edit view
            return view('settings.hatchery_manager.edit', compact('hatchery_manager','districts','hatcheries'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Client is not found
            return redirect()->route('hatchery-manager.index')->with('error', 'Hatchery Manager not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $hatchery_manager)
    {
        if ($hatchery_manager->type != 'Hatchery_Manager') {
            abort(404);
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'hatchery' =>['required'],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->ignore($hatchery_manager)
            ],
            'email' =>[
                'required','max:255',
                Rule::unique('clients')
                    ->ignore($hatchery_manager)
            ],
            'district' =>['required'],
            'thana' =>['required'],
            'designation' => 'nullable', // Make 'designation' nullable
            'address' => 'nullable|string|max:255', // Make 'address' nullable
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Client record in the database
            $validatedData['type'] = 'Hatchery_Manager';
            $validatedData['parent_id'] = $validatedData['hatchery'];
            $validatedData['district_id'] = $validatedData['district'];
            $validatedData['thana_id'] = $validatedData['thana'];
            
            unset($validatedData['hatchery']);
            unset($validatedData['district']);
            unset($validatedData['thana']);

            $hatchery_manager->update($validatedData);

            // Create/Update User
            $user = User::where('role','Hatchery_Manager')->where('client_id',$hatchery_manager->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'Hatchery_Manager';
            $user->client_id = $hatchery_manager->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('hatchery-manager.index')->with('success', 'Doctor updated successfully');
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollback();

            return redirect()->route('hatchery-manager.edit',['hatchery_manager'=>$hatchery_manager->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the hatchery-manager: '.$e->getMessage());
        }

    }

    public function destroy(Client $hatchery_manager)
    {
        try {
            User::where('client_id',$hatchery_manager->id)->delete();
            $hatchery_manager->delete();
            return response()->json(['success'=>true,'message' => 'Hatchery Manager deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Hatchery Manager not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
