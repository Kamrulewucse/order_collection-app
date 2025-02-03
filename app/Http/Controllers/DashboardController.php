<?php

namespace App\Http\Controllers;

use App\Enumeration\VoucherType;
use App\Models\SaleOrder;
use App\Models\SalePayment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function moduleDashboard()
    {
        return view('layouts.admin_dashboard');
    }
    public function index(Request $request)
    {
        if (in_array(auth()->user()->role, ['Doctor'])) {
            return view('blank_dashboard');
        } else {
            if ($request->start_date != '' && $request->end_date != '') {
                $searchYear = Carbon::parse($request->start_date)->format('Y');
                $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
                $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            } else {
                $searchYear = date('Y');
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');
            }
            if (in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])) {

                $totals = SaleOrder::whereBetween('date', [$startDate, $endDate])
                    ->selectRaw('SUM(total) as total_sales, SUM(due) as total_dues, SUM(paid) as total_paids')
                    ->first();

                $totalDuePayment = SalePayment::whereBetween('date', [$startDate, $endDate])
                    ->where('received_type', 2)
                    ->selectRaw('SUM(amount) as total_amount')
                    ->first();

                $salesDataChat1 = SaleOrder::whereYear('date', $searchYear)
                    ->selectRaw('DATE(date) as date, SUM(total) as total_sales')
                    ->groupBy('date')
                    ->get();

                $salesDataChart2 = SaleOrder::whereBetween('date', [$startDate, $endDate])
                    ->select('date', 'total as t_amount', 'due as p_amount')
                    ->get();
            } else if (in_array(auth()->user()->role, ['SR'])) {

                $totals = SaleOrder::whereBetween('date', [$startDate, $endDate])
                    ->where('sr_id', auth()->user()->client_id)
                    ->selectRaw('SUM(total) as total_sales, SUM(due) as total_dues, SUM(paid) as total_paids')
                    ->first();

                $totalDuePayment = SalePayment::whereBetween('date', [$startDate, $endDate])
                    ->where('received_type', 2)
                    ->where('sr_id', auth()->user()->client_id)
                    ->selectRaw('SUM(amount) as total_amount')
                    ->first();

                $salesDataChat1 = SaleOrder::whereYear('date', $searchYear)
                    ->where('sr_id', auth()->user()->client_id)
                    ->selectRaw('DATE(date) as date, SUM(total) as total_sales')
                    ->groupBy('date')
                    ->get();

                $salesDataChart2 = SaleOrder::whereBetween('date', [$startDate, $endDate])
                    ->where('sr_id', auth()->user()->client_id)
                    ->select('date', 'total as t_amount', 'due as p_amount')
                    ->get();
            }

            // Extract totals from the query result
            $totalSales = $totals->total_sales ?? 0;
            $totalDues = $totals->total_dues ?? 0;
            $totalPaids = $totals->total_paids ?? 0;
            $totalDuePayment = $totalDuePayment->total_amount ?? 0;

            return view('dashboard', compact('totalSales', 'totalPaids', 'totalDues', 'totalDuePayment', 'salesDataChat1', 'salesDataChart2'));
        }
    }
}
