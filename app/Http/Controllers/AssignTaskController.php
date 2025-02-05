<?php

namespace App\Http\Controllers;

use App\Models\AssignTask;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class AssignTaskController extends Controller
{
    public function index(Request $request)
    {
        $srs = Client::where('type', 2)->get(); //here type=2 for SR
        return view('assign_task.index', compact('srs'));
    }
    public function dataTable()
    {
        if (in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])) {
            $query = AssignTask::with('srOrDoctor');
        } else {
            $query = AssignTask::with('srOrDoctor')->where('sr_doctor_id', auth()->user()->client_id);
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (AssignTask $assign_task) {
                $btn = '';
                $btn .= '<button data-id="' . $assign_task->id . '" role="button" class="dropdown-item task-cost-btn">Task Cost</button>';
                $btn .= '<button data-id="' . $assign_task->id . '" data-status="On Going" role="button" class="dropdown-item status-btn">On Going</button>';
                $btn .= '<button data-id="' . $assign_task->id . '" data-status="Done" role="button" class="dropdown-item status-btn">Done</button>';

                return dropdownMenuContainer($btn);
            })
            ->addColumn('srOrDoctor', function (AssignTask $assign_task) {
                return $assign_task->srOrDoctor->name ?? '';
            })
            ->editColumn('date', function (AssignTask $assign_task) {
                return Carbon::parse($assign_task->date)->format('d-m-Y');
            })
            ->editColumn('task_cost', function (AssignTask $assign_task) {
                return number_format($assign_task->task_cost, 2);
            })
            ->addColumn('task_priority', function (AssignTask $assign_task) {
                if ($assign_task->task_priority == 'High') {
                    return '<span class="badge badge-danger">High</span>';
                } else if ($assign_task->task_priority == 'Medium') {
                    return '<span class="badge badge-primary">Medium</span>';
                } else {
                    return '<span class="badge badge-warning">Low</span>';
                }
            })
            ->addColumn('status', function (AssignTask $assign_task) {
                if ($assign_task->status == 1) {
                    return '<span class="badge badge-warning">Assign</span>';
                } else if ($assign_task->status == 2) {
                    return '<span class="badge badge-primary">On Going</span>';
                } else {
                    return '<span class="badge badge-success">Done</span>';
                }
            })
            ->addColumn('file', function (AssignTask $assign_task) {
                if ($assign_task->file_path) {
                    $fileUrl = asset($assign_task->file_path);
                    return '<a href="' . $fileUrl . '" target="_blank">View File</a>';
                } else {
                    return '<span class="text-muted">No file</span>';
                }
            })
            ->rawColumns(['action', 'status', 'task_priority','file'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $srs_doctors = Client::where('status', 1)->whereIn('type', [2, 3])->get(); //type=2 is SR


        return view('assign_task.create', compact(
            'srs_doctors',
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return($request->all());
        // Validate the request data
        $request->validate([
            'sr_doctor_id.*' => 'required',
            'task_priority.*' => 'required|string|max:255',
            'task_details.*' => 'required|string|max:1500',
            'task_date.*' => 'required|date|date_format:d-m-Y',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {

            foreach ($request->sr_doctor_id as $key => $sr_doctor_id) {
                $assignTask = new AssignTask();
                $assignTask->sr_doctor_id = $sr_doctor_id;
                $assignTask->date = Carbon::parse($request->task_date[$key]);
                $assignTask->task_priority = $request->task_priority[$key];
                $assignTask->task_details = $request->task_details[$key];
                $assignTask->status = 1; // 1 for assign
                $assignTask->assign_by =  auth()->user()->id;
                $assignTask->save();
            }


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('assign-task.index')->with('success', 'Task Assign successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('assign-task.create')->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function changeStatus(AssignTask $assign_task, Request $request)
    {

        DB::beginTransaction();
        try {
            if ($request->status == 'On Going') {
                $assign_task->status = 2;
            } else if ($request->status == 'Done') {
                $assign_task->status = 3;
            }
            $assign_task->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status Change successfully'
            ]);
        } catch (\Exception $exception) {

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function storeTaskCost(Request $request)
    {
        $request->validate([
            'assign_task_id' => 'required|exists:assign_tasks,id',
            'amount' => 'required|numeric',
            'file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $assignTask = AssignTask::find($request->assign_task_id);

            if (!$assignTask) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found!',
                ]);
            }

            $assignTask->task_cost = $request->amount;
            $assignTask->notes = $request->notes;

            if ($request->hasFile('file')) {
                if ($assignTask->file_path && file_exists(public_path($assignTask->file_path))) {
                    unlink(public_path($assignTask->file_path));
                }

                $file = $request->file('file');
                $filename = Uuid::uuid1()->toString() . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'uploads/task_file';
                $file->move(public_path($destinationPath), $filename);

                $assignTask->file_path = $destinationPath . '/' . $filename;
            }

            $assignTask->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task cost added successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding task cost.',
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
