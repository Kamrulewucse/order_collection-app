<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LeaveTypeController extends Controller
{
    public function index()
    {
        try {
            return view('leave_types.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch leave types. Please try again.');
        }
    }

    public function dataTable()
    {
        $query = LeaveType::query();
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(LeaveType $leaveType) {

                $btn = '';
                $btn .= '<a href="' . route('leave-types.edit', ['leave_type' => $leaveType->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <a role="button" data-id="' . $leaveType->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';

                return $btn;

            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create()
    {
        return view('leave_types.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'max_days' => 'required|integer|min:1',
        ]);

        try {
            $validatedData['create_user_id'] = auth()->id();
            LeaveType::create($validatedData);
            return redirect()->route('leave-types.index')->with('success', 'Leave type created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create leave type. Please try again.');
        }
    }

    public function edit(LeaveType $leave_type)
    {
        return view('leave_types.edit', compact('leave_type'));
    }

    public function update(Request $request, LeaveType $leave_type)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'max_days' => 'required|integer|min:1',
        ]);

        try {
            $validatedData['alter_user_id'] = auth()->id();
            $leave_type->update($validatedData);
            return redirect()->route('leave-types.index')->with('success', 'Leave type updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update leave type. Please try again.');
        }
    }

    public function destroy(LeaveType $leave_type)
    {
        try {
            $leave_type->delete();
            return response()->json(['success'=>true,'message'=>'Leave type deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Failed to delete leave type: ' . $e->getMessage()]);
        }
    }
}
