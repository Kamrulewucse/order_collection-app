<?php

namespace App\Http\Controllers;

use App\Enumeration\VoucherType;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->hasPermissionTo('dashboard')) {
            $currentYear = date('Y');
            $revenueExpenses = [];
            $profits = [];
            $topRoomsList = [];
            if ($request->dashboard_year != ''){
                $searchYear = $request->dashboard_year;
            }else{
                $searchYear = date('Y');
            }
            for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++){

                $revenue = Transaction::sum('amount');
                $expenses = Transaction::sum('amount');

                array_push($revenueExpenses,[
                    'month'=>  numberToMonthName($monthIndex),
                    'revenue'=>  $revenue,
                    'expenses'=>  $expenses,
                ]);
                array_push($profits,[
                    'month'=>  numberToMonthName($monthIndex),
                    'profit'=>  $revenue - $expenses,
                ]);

            }


            return view('dashboard',compact('revenueExpenses',
                'profits'));
        }else{
            return view('blank_dashboard');
        }

   }
}
