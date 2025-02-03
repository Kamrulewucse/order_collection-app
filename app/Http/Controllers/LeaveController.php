<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class LeaveController extends Controller
{
    public function index()
    {
        try {
            return view('leave.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function dataTable()
    {
        if (in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $query = Leave::with('sr', 'leaveType');
        }else{
            $query = Leave::with('sr', 'leaveType')->where('sr_id',auth()->user()->client_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Leave $leave) {

                $btn = '';
                if (in_array(auth()->user()->role, ['Admin', 'SuperAdmin']) && $leave->status == 'pending') {
                    $btn .= ' <a role="button" data-id="' . $leave->id . '" class="btn btn-success btn-sm btn-approved">Approved</a> ';
                }
                if ($leave->status != 'approved') {
                $btn .= '<a href="' . route('leave.edit', ['leave' => $leave->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';

                $btn .= ' <a role="button" data-id="' . $leave->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->addColumn('sr_name', function (Leave $leave) {
                return $leave->sr->name ?? '';
            })
            ->addColumn('leave_type_name', function (Leave $leave) {
                return $leave->leaveType->name ?? '';
            })
            ->addColumn('total_days', function (Leave $leave) {
                $startDate = Carbon::parse($leave->start_date);
                $endDate = Carbon::parse($leave->end_date);
                return $startDate->diffInDays($endDate) + 1; // Including the start date
            })
            ->editColumn('start_date', function (Leave $leave) {
                return Carbon::parse($leave->start_date)->format('d-m-Y');
            })
            ->editColumn('end_date', function (Leave $leave) {
                return Carbon::parse($leave->end_date)->format('d-m-Y');
            })
            ->editColumn('status', function(Leave $leave) {
                if ($leave->status == 'pending'){
                    return '<span class="badge badge-warning">Pending</span>';
                }elseif ($leave->status == 'approved'){
                    return '<span class="badge badge-success">Approved</span>';
                }else{
                    return '<span class="badge badge-danger">Rejected</span>';
                }
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }
    public function create()
    {
        $leaveTypes = LeaveType::all();
        $srs = Client::where('type', 2)->get();
        return view('leave.create', compact('leaveTypes', 'srs'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sr' => 'required|exists:clients,id',
            'leave_type' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            // 'status' => 'nullable|string|in:pending,approved,rejected',
        ]);
        try {
            $validatedData['sr_id'] = $validatedData['sr'];
            $validatedData['leave_type_id'] = $validatedData['leave_type'];
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $validatedData['create_user_id'] = auth()->id();
            $validatedData['status'] = 'pending';
            unset($validatedData['sr']);
            unset($validatedData['leave_type']);
            Leave::create($validatedData);
            return redirect()->route('leave.index')->with('success', 'Leave application submitted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to submit leave application: ' . $e->getMessage()]);
        }
    }

    public function edit(Leave $leave)
    {
        $leaveTypes = LeaveType::all();
        $srs = Client::where('type', 2)->get();
        return view('leave.edit', compact('leave', 'srs', 'leaveTypes'));
    }

    public function update(Request $request, Leave $leave)
    {
        $validatedData = $request->validate([
            'sr' => 'required|exists:clients,id',
            'leave_type' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            // 'status' => 'nullable|string|in:pending,approved,rejected',
        ]);
        try {
            $validatedData['sr_id'] = $validatedData['sr'];
            $validatedData['leave_type_id'] = $validatedData['leave_type'];
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $validatedData['alter_user_id'] = auth()->id();
            unset($validatedData['sr']);
            unset($validatedData['leave_type']);
            $leave->update($validatedData);
            return redirect()->route('leave.index')->with('success', 'Leave application updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update leave application: ' . $e->getMessage()]);
        }
    }

    public function leaveApproved(Leave $leave)
    {
        try {
            // dd($leave->id);
            $leave->status = 'approved';
            $leave->action_by = auth()->user()->id;
            $leave->save();
            return response()->json(['success' => true, 'message' => 'Leave Approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approved leave: ' . $e->getMessage()]);
        }
    }
    public function destroy(Leave $leave)
    {
        try {
            if ($leave->status == 'approved') {
                throw new \Exception('Already approved!');
            }
            $leave->delete();
            return response()->json(['success' => true, 'message' => 'Leave deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete leave: ' . $e->getMessage()]);
        }
    }
}
