<?php

namespace App\Http\Controllers;

use App\Enumeration\VoucherType;
use App\Models\AccountHead;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\Voucher;
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

                $revenue = Transaction::whereIn('voucher_type', [VoucherType::$COLLECTION_VOUCHER])
                    ->whereYear('date',$searchYear)
                    ->whereMonth('date',$monthIndex)
                    ->whereColumn('payment_account_head_id', '!=', 'account_head_id')
                    ->sum('amount');
                $expenses = Transaction::whereIn('voucher_type', [VoucherType::$PAYMENT_VOUCHER])
                    ->whereYear('date',$searchYear)
                    ->whereMonth('date',$monthIndex)
                    ->whereColumn('payment_account_head_id', '!=', 'account_head_id')
                    ->sum('amount');

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

            $totalCollection = Voucher::where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                //->whereNotNull('booking_id')
                ->where('date',date('Y-m-d'))
                ->sum('amount');
            $totalSettled = Voucher::where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                //->whereNotNull('booking_id')
                ->where('date',date('Y-m-d'))
                ->where('collection_receive_status',1)
                ->sum('amount');
            $totalBankDeposit = Voucher::where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                //->whereNotNull('booking_id')
                ->where('date',date('Y-m-d'))
                ->where('collection_receive_status',2)
                ->sum('amount');

            $paymentModes = AccountHead::where('payment_mode','>',0)
                ->where('id','!=',101)
                ->orderByRaw("CASE WHEN id IN (1,5) THEN 0 ELSE 1 END, id ASC")
                ->get();

            return view('dashboard',compact('revenueExpenses',
                'profits','totalCollection',
                'totalSettled','totalBankDeposit','paymentModes'));
        }else{
            return view('blank_dashboard');
        }

   }
}
