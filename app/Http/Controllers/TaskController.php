<?php

namespace App\Http\Controllers;

use App\Models\AssignTask;
use App\Models\Client;
use App\Models\Task;
use App\Models\TaskDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $srs = Client::where('type', 2)->get(); //here type=2 for SR
        return view('task.index', compact('srs'));
    }
    public function dataTable()
    {
        $query = Task::with('user');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Task $task) {
                $btn = '';
                // $btn .= '<button data-id="' . $task->id . '" role="button" class="dropdown-item task-cost-btn">Task Cost</button>';
                // $btn .= '<button data-id="' . $task->id . '" data-status="On Going" role="button" class="dropdown-item status-btn">On Going</button>';
                // $btn .= '<button data-id="' . $task->id . '" data-status="Done" role="button" class="dropdown-item status-btn">Done</button>';
                $btn .= ' <a href="'.route('task.details',['task'=>$task->id]).'" class="btn btn-info bg-gradient-info btn-sm">Details</a>';

                return $btn;
            })
            ->addColumn('user', function (Task $task) {
                return $task->user->name ?? '';
            })
            ->addColumn('task_total_cost', function (Task $task) {
                return number_format($task->task_total_cost,2);
            })
            ->editColumn('date', function (Task $task) {
                return Carbon::parse($task->date)->format('d-m-Y');
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $srs_doctors = Client::where('status', 1)->whereIn('type', [2, 3])->get(); //type=2 is SR

        return view('task.create', compact(
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
            'task_user' => 'required',
            'date' => 'required',
            'task_priority.*' => 'required|string|max:255',
            'task_details.*' => 'required|string|max:1500',
            'task_cost.*' => 'required|numeric',
            'task_date.*' => 'required|date|date_format:d-m-Y',
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $task = new Task();
            $task->user_id = $request->task_user;
            $task->date = date('Y-m-d',strtotime($request->date));
            $task->task_total_cost = array_sum($request->task_cost);
            $task->status = 1;
            $task->save();

            $task->task_no = 'TN-' . date('Ymd') . '-' . $task->id;
            $task->save();

            foreach ($request->task_details as $key => $task_details) {
                $taskDetail = new TaskDetail();
                $taskDetail->task_id = $task->id;
                $taskDetail->date = Carbon::parse($request->task_date[$key]);
                $taskDetail->task_priority = $request->task_priority[$key];
                $taskDetail->task_details = $request->task_details[$key];
                $taskDetail->task_cost = $request->task_cost[$key];
                $taskDetail->status = 1; // 1 for assign
                $taskDetail->save();
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('task.details',['task'=>$task->id])->with('success', 'Task Added successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();
            dd($e->getMessage());
            // Handle the error and redirect with an error message
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function details(Task $task){
        return view('task.details',compact('task'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
