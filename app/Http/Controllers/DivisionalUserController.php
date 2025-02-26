<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Division;
use App\Models\DivisionalUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class DivisionalUserController extends Controller
{
    public function index()
    {
        return view('divisional_user.index');
    }
    public function dataTable()
    {
        $query = Client::where('type','Divisional Admin');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Client $divisionalUser) {
                $btn = '';
                $btn .= '<a href="' . route('divisional-user.edit', ['divisional_user' => $divisionalUser->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                // $btn .= ' <a role="button" data-id="' . $user->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                return $btn;
            })
            ->addColumn('user_id',function(Client $divisionalUser){
                return $divisionalUser->user->name??'';
            })
            ->addColumn('division',function(Client $divisionalUser){
                return $divisionalUser->division->name_eng??'';
            })
            ->addColumn('status', function(Client $divisionalUser) {
              if ($divisionalUser->status == 1)
                  return '<span class="badge badge-success">Active</span>';
              else
                  return '<span class="badge badge-danger">Inactive</span>';

            })
            ->rawColumns(['action','status'])
            ->toJson();
    }
    public function create(){
        $admins = User::where('role','Admin')->get();
        $divisions = Division::all();
        return view('divisional_user.create',compact('divisions','admins'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'user' =>['required'],
            'division' =>['required'],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
            ],
            'mobile_no' =>[
                'nullable','max:255',
                Rule::unique('clients')
            ],
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {

            // Create a new Divisional User record in the database
            $divisionalUser = new Client();
            $divisionalUser->type = 'Divisional Admin';
            $divisionalUser->parent_id = $request->user;
            $divisionalUser->division_id = $request->division;
            $divisionalUser->name = $request->name;
            $divisionalUser->email = $request->email;
            $divisionalUser->mobile_no = $request->mobile_no;
            $divisionalUser->address = $request->address;
            $divisionalUser->status = $request->status;
            $divisionalUser->save();

            $user = User::where('role','Divisional Admin')->where('client_id',$divisionalUser->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'Divisional Admin';
            $user->client_id = $divisionalUser->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->password = bcrypt('12345678');
            $user->save();

            // $user->syncPermissions($request->permission);
            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('divisional-user.index')->with('success', 'Divisional User created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('divisional-user.create')->with('error', 'An error occurred while creating the divisional user : '.$e->getMessage());
        }
   }

    public function edit(Client $divisionalUser){
        $admins = User::where('role','Admin')->get();
        $divisions = Division::all();
        return view('divisional_user.edit',compact('divisionalUser','divisions','admins'));
    }

    public function update(Client $divisionalUser,Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'division' =>['required'],
            'email' =>[
                'nullable','max:255',
                Rule::unique('clients')
                    ->ignore($divisionalUser)
            ],
            'mobile_no' =>[
                'nullable','max:255',
                Rule::unique('clients')
                    ->ignore($divisionalUser)
            ],
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {

            // Create a new Divisional User record in the database
            $divisionalUser->parent_id = $request->user;
            $divisionalUser->division_id = $request->division;
            $divisionalUser->name = $request->name;
            $divisionalUser->email = $request->email;
            $divisionalUser->mobile_no = $request->mobile_no;
            $divisionalUser->address = $request->address;
            $divisionalUser->status = $request->status;
            $divisionalUser->save();

            $user = User::where('role','Divisional Admin')->where('client_id',$divisionalUser->id)->first();
            if (!$user){
                $user = new User();
            }
            $user->role = 'Divisional Admin';
            $user->client_id = $divisionalUser->id;
            $user->name = $request->name;
            $user->username  = $request->mobile_no;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile_no;
            $user->password = bcrypt('12345678');
            $user->save();
            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('divisional-user.index')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('divisional-user.edit',['divisional_user'=>$divisionalUser->id])->withInput()->with('error', 'An error occurred while updating the user : '.$e->getMessage());
        }
   }

    public function destroy(Client $divisionalUser)
    {
        try {
            // Delete the User record
            $divisionalUser->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Divisional User deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Divisional User not found: '.$e->getMessage()], Response::HTTP_OK);
        }
   }
}
