<?php

namespace App\Http\Controllers;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Exports\GuestPoliceReport;
use App\Models\AccountGroup;
use App\Models\AccountHead;
use App\Models\Booking;
use App\Models\GuestExpenseLog;
use App\Models\Hotel;
use App\Models\InventoryLog;
use App\Models\PurchaseOrder;
use App\Models\Room;
use App\Models\SaleOrder;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounts\Entities\AccountHeadType;
use SakibRahaman\DecimalToWords\DecimalToWords;
use Yajra\DataTables\Facades\DataTables;


use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function exportHtmlContent(Request $request){
        $request->validate([
            'htmlContent' => 'required',
        ]);

        $htmlContent = $request->input('htmlContent');
        $title = $request->input('title');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Load the HTML content using DOMDocument
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<html><body>' . $htmlContent . '</body></html>');
        libxml_clear_errors();

        // Extract the table
        $table = $dom->getElementsByTagName('table')->item(0);
        if (!$table) {
            return response()->json(['error' => 'No table found in the HTML content.'], 400);
        }

        $rows = $table->getElementsByTagName('tr');

        $columns = $rows->item(0)->getElementsByTagName('th');
        $tableColumns = count($columns);
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($tableColumns);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', 'Date: ' . $startDate . ' to ' . $endDate);

        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getFont()->setBold(true);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->mergeCells('A2:' . $lastColumn . '2');

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $sheet->getRowDimension(4)->setRowHeight(10);

        $cellMap = [];
        $rowIndex = 4;

        foreach ($rows as $row) {
            $columns = $row->childNodes;
            $colIndex = 1;

            foreach ($columns as $column) {
                if ($column->nodeName !== 'td' && $column->nodeName !== 'th') {
                    continue;
                }

                while (isset($cellMap[$rowIndex][$colIndex])) {
                    $colIndex++;
                }

                $cellValue = trim($column->textContent);

                // Check if the value is numeric (after removing commas and spaces)
                if (preg_match('/^[0-9,.-]+$/', $cellValue)) {
                    // Remove commas and sanitize the number
                    $sanitizedValue = preg_replace('/[^0-9.-]/', '', $cellValue); // Remove all non-numeric characters except dot and minus

                    // Ensure that Excel treats this as a numeric value explicitly
                    $sheet->setCellValueExplicitByColumnAndRow(
                        $colIndex,
                        $rowIndex,
                        (float)$sanitizedValue,  // Convert to float for proper numeric handling
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC  // Explicitly set as numeric
                    );
                } else {
                    // If it's not numeric, treat it as text
                    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $cellValue);
                }

                // Handle colspan and rowspan
                $colspan = $column->getAttribute('colspan') ?: 1;
                $rowspan = $column->getAttribute('rowspan') ?: 1;

                if ($colspan > 1 || $rowspan > 1) {
                    $sheet->mergeCellsByColumnAndRow(
                        $colIndex,
                        $rowIndex,
                        $colIndex + $colspan - 1,
                        $rowIndex + $rowspan - 1
                    );
                }

                for ($r = $rowIndex; $r < $rowIndex + $rowspan; $r++) {
                    for ($c = $colIndex; $c < $colIndex + $colspan; $c++) {
                        $cellMap[$r][$c] = true;
                    }
                }

                $colIndex += $colspan;
            }

            $rowIndex++;
        }

        $fileName = $title.'_with_spans.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
    public function booking(Request $request)
    {
        $bookings = [];
        $datesInRange = [];
        $hotels = Hotel::where('status', 1)->orderBy('sort')->get();
        $rooms = Room::where('status', 1)->orderBy('sort')->get();
        if ($request->start_date != '' && $request->end_date != '') {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            // Check if both $startDate and $endDate are valid instances of Carbon
            if ($startDate instanceof Carbon && $endDate instanceof Carbon) {
                // Iterate through the date range
                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    $datesInRange[] = $date->toDateString(); // Add each date to the array
                }
            }
        }

        return view('report.booking', compact('bookings',
            'hotels', 'datesInRange', 'rooms'));
    }

    public function checkIn(Request $request)
    {
        $bookings = [];
        $datesInRange = [];
        $hotels = Hotel::where('status', 1)->orderBy('sort')->get();
        $rooms = Room::where('status', 1)->orderBy('sort')->get();
        if ($request->start_date != '' && $request->end_date != '') {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            // Check if both $startDate and $endDate are valid instances of Carbon
            if ($startDate instanceof Carbon && $endDate instanceof Carbon) {
                // Iterate through the date range
                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    $datesInRange[] = $date->toDateString(); // Add each date to the array
                }
            }
        }

        return view('report.check_in', compact('bookings',
            'hotels', 'datesInRange', 'rooms'));
    }

    public function dailyGuest(Request $request)
    {

        $checkedIns = [];
        if ($request->guest_type != '') {
            $query = Booking::where('booking_type', 2);
            if ($request->guest_type == 1) {
                $query->whereNull('guest_passport_no');
            } elseif ($request->guest_type == 2) {
                $query->whereNotNull('guest_passport_no');
            }

            $checkedIns = $query->whereNull('checkout_at')
                ->get();
        }


        return view('report.daily_guest', compact('checkedIns'));
    }

    public function salesReport(Request $request)
    {
        $customers = Client::where('type', 3)->get();
        $logs = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $logQuery = SaleOrder::whereBetween('date', [
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
            if ($request->customer != '') {
                $logQuery->where('customer_id', $request->customer);
            }

            $logs = $logQuery->with('customer', 'customer.company', 'vouchers')
                ->orderBy('date')
                ->get();

        }

        return view('report.sales_report', compact('logs', 'customers'));
    }

    public function inventoryInReport(Request $request)
    {
        $logs = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $logs = InventoryLog::where('type', 1)
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ])
                ->where('quantity', '>', 0)
                ->orderBy('date')
                ->get();

        }

        return view('report.inventory_in', compact('logs'));
    }

    public function inventoryOutReport(Request $request)
    {
        $logs = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $logs = InventoryLog::where('type', 2)
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ])
                ->where('quantity', '>', 0)
                ->orderBy('date')
                ->get();

        }

        return view('report.inventory_out', compact('logs'));
    }

    public function salesVsPayments(Request $request)
    {
        $companies = Client::where('status', 1)->where('type', 1)->get();
        $logCompanies = [];

        if ($request->search != '') {
            $logCompanyQuery = Client::where('status', 1)->where('type', 1);
            if ($request->company != '') {
                $logCompanyQuery->where('id', $request->company);
            }
            $logCompanies = $logCompanyQuery->with([
                'purchaseOrders'=>function($query) use ($request){
                    $query->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ]);
                }, 'distributionOrders'=>function($query) use ($request){
                    $query->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ]);
                },
                'inventories' => function($query) {
                    $query->selectRaw('company_id, SUM(average_purchase_unit_price * quantity) as physical_stock')
                        ->groupBy('company_id');
                },
                'vouchers'=>function($query) use ($request){
                    $query->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ]);
                },
            ])->get();
        }

        return view('report.sales_vs_payment', compact('companies',
            'logCompanies'));
    }
    public function cashAndStock(Request $request)
    {
        $companies = Client::where('status', 1)
            ->where('type', 1)
            ->get();
        $logCompanies = [];
        if ($request->search != '') {
            $logCompanyQuery = Client::where('status', 1)->where('type', 1);
            if ($request->company != '') {
                $logCompanyQuery->where('id', $request->company);
            }

            $logCompanies = $logCompanyQuery->with([
                'purchaseOrders'=>function($query) use ($request){
                    $query->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ]);
                },
                'distributionOrders',
                'vouchers'=>function($query) use ($request){
                    $query->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ]);
                },
                'transactions'=>function($query) use ($request){
                    $query->whereBetween('date',[
                        Carbon::parse($request->start_date)->format('Y-m-d'),
                        Carbon::parse($request->end_date)->format('Y-m-d'),
                    ])->where('account_head_id',113);
                },
            ])->get();
        }


        return view('report.cash_and_stocks', compact('companies',
            'logCompanies'));
    }
    public function paymentVsProductReceived(Request $request)
    {
        $companies = Client::where('status', 1)
            ->where('type', 1)
            ->get();
        $logCompanies = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $request['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $request['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $logCompanyQuery = Client::where('status', 1)->where('type', 1);
            if ($request->company != '') {
                $logCompanyQuery->where('id', $request->company);
            }
            $logCompanies = $logCompanyQuery
                ->with(['purchaseOrders' => function ($query) use ($request) {
                    if ($request->start_date && $request->end_date) {
                        $query->whereBetween('date', [$request->start_date, $request->end_date]);
                    }
                }])->with(['vouchers' => function ($voucherQuery) use ($request) {
                    $voucherQuery->whereBetween('date', [$request->start_date, $request->end_date]);
                }])->get();
        }

        return view('report.payment_vs_product_received', compact(
            'companies', 'logCompanies'));
    }
    public function salesDue(Request $request)
    {
        $customers = Client::where('status', 1)
            ->where('type',3)
            ->get();
        $orders = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $request['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $request['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $orderQuery = SaleOrder::query();
            if ($request->customer != '') {
                $orderQuery->where('customer_id', $request->customer);
            }
             if ($request->start_date && $request->end_date) {
                 $orderQuery->whereBetween('date', [$request->start_date, $request->end_date]);
             }
                $orders = $orderQuery->with('customer','customer.company')->get();
        }

        return view('report.sales_due', compact(
            'customers', 'orders'));
    }



    public function receiptAndPayment(Request $request)
    {
        $logs = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $logs = Transaction::whereIn('voucher_type',
                [
                    VoucherType::$PAYMENT_VOUCHER,
                    VoucherType::$COLLECTION_VOUCHER
                ])
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ])
                ->whereColumn('payment_account_head_id', '!=', 'account_head_id')
                ->orderBy('date')
                ->get();

        }

        return view('report.receipt_and_payment', compact('logs'));
    }

    public function dailyCollection()
    {
        $totalCollection = Voucher::where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
            //->whereNotNull('booking_id')
            ->where('date', date('Y-m-d'))
            ->sum('amount');
        $totalSettled = Voucher::where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
            //->whereNotNull('booking_id')
            ->where('date', date('Y-m-d'))
            ->where('collection_receive_status', 1)
            ->sum('amount');
        $totalBankDeposit = Voucher::where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
            //->whereNotNull('booking_id')
            ->where('date', date('Y-m-d'))
            ->where('collection_receive_status', 2)
            ->sum('amount');

        $paymentModes = AccountHead::where('payment_mode', '>', 0)
            ->where('id', '!=', 101)
            ->orderByRaw("CASE WHEN id IN (1,5) THEN 0 ELSE 1 END, id ASC")
            ->get();

        return view('report.daily_collection', compact('totalCollection',
            'totalSettled', 'totalBankDeposit', 'paymentModes'));
    }

    public function dailyCollectionDataTable()
    {
        $query = Voucher::with('guestExpenseLog', 'guestExpenseLog.room', 'booking', 'paymentAccountHead')
            //->whereNotNull('booking_id')
            ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER);


        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [
                Carbon::parse(request('start_date'))->format('Y-m-d'),
                Carbon::parse(request('end_date'))->format('Y-m-d')
            ]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Voucher $voucher) {
                $btn = '';
                if (auth()->user()->can('daily_collection_approved')) {
                    if ($voucher->collection_receive_status == 0) {
                        $btn .= '<div class="icheck-warning float-left " style="margin-top:0!important;margin-bottom: 0 !important;"><input type="checkbox" value="' . $voucher->id . '" name="collection_voucher_id[]" class="daily_collection_approved_check" id="for' . $voucher->id . '"><label for="for' . $voucher->id . '"></label></div>';
                        $btn .= '<a role="button"  data-status="1" role="button" data-id="' . $voucher->id . '" class="btn btn-success bg-gradient-success btn-xs btn-collected">Settled</a>';
                        $btn .= ' <a role="button"  data-status="2" role="button" data-id="' . $voucher->id . '" class="btn btn-info bg-gradient-info btn-xs btn-collected">Bank Deposit</a>';
                    }
                }
                return $btn;
            })
            ->addColumn('edit_date', function (Voucher $voucher) {
                return Carbon::parse($voucher->date)->format('d-M-Y');
            })
            ->addColumn('room_no', function (Voucher $voucher) {
                if ($voucher->guestExpenseLog && $voucher->guestExpenseLog->room) {
                    return '<a href="' . route('check-in.edit', ['check_in' => $voucher->booking_id]) . '">' . ($voucher->guestExpenseLog->room->room_no ?? '') . '</a>';
                } else {
                    return '-';
                }
            })
            ->addColumn('card_no', function (Voucher $voucher) {
                if ($voucher->booking) {
                    if ($voucher->booking->booking_type == 2) {
                        return '<a href="' . route('check-in.edit', ['check_in' => $voucher->booking_id]) . '">' . ($voucher->booking->order_no ?? '') . '</a>';
                    } else {
                        return '<a href="' . route('booking.details', ['booking' => $voucher->booking_id]) . '">' . ($voucher->booking->order_no ?? '') . '</a>';
                    }
                } else {
                    return '-';
                }
            })
            ->addColumn('payment_mode', function (Voucher $voucher) {
                return $voucher->paymentAccountHead->name ?? '';
            })
            ->addColumn('particulars', function (Voucher $voucher) {
                if ($voucher->booking) {
                    if ($voucher->booking->booking_type == 1) {
                        return 'Advance';
                    } else {
                        $remarks = '';
                        if ($voucher->guestExpenseLog && $voucher->guestExpenseLog->type) {
                            if ($voucher->guestExpenseLog->type == 2 || $voucher->guestExpenseLog->type == 3) {
                                if ($voucher->guestExpenseLog->remarks != '') {
                                    $remarks = '(' . $voucher->guestExpenseLog->remarks . ')';
                                }
                            }
                        }
                        return ($voucher->guestExpenseLog->particulars ?? '') . ' ' . $remarks;
                    }
                } else {
                    $accountHeads = '<ul style="margin: 0;padding-left: 0;list-style: none">';
                    foreach ($voucher->transactions->where('account_head_id', '!=', $voucher->payment_account_head_id) as $transaction) {
                        $accountHeads .= '<li>' . ($transaction->accountHead->name ?? '') . '</li>';
                    }
                    $accountHeads .= '</ul>';
                    return $accountHeads;
                }
            })
            ->editColumn('collection_receive_status', function (Voucher $voucher) {
                if ($voucher->collection_receive_status == 1) {
                    return 'Settled';
                } elseif ($voucher->collection_receive_status == 2) {
                    return 'Bank Deposit';
                }
            })
            ->addColumn('status', function (Voucher $voucher) {
                if ($voucher->booking) {
                    if ($voucher->booking->booking_type == 1) {
                        return '<span class="badge badge-warning">Booked</span>';
                    } else {
                        if ($voucher->booking->status == 0) {
                            return '<span class="badge badge-success">Running</span>';
                        } elseif ($voucher->booking->status == 1) {
                            return '<span class="badge badge-danger">Checkout</span>';
                        }
                    }
                } else {
                    return '-';
                }
            })
            ->rawColumns(['action', 'status', 'room_no', 'card_no', 'particulars'])
            ->toJson();
    }

    public function dailyCollectionApproved(Request $request)
    {
        try {

            if (!auth()->user()->hasPermissionTo('daily_collection_approved')) {
                abort(403, 'Unauthorized');
            }
            // Approved
            $voucher = Voucher::where('id', $request->id)->first();
            $voucher->collection_receive_status = $request->status;//1=Selected,2=Bank Deposit
            $voucher->save();
            // Return a JSON success response
            return response()->json(['success' => true, 'message' => 'Payment received successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success' => false, 'message' => 'Not found: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }

    public function dailyCollectionApprovedSelected(Request $request)
    {

        try {

            if (!auth()->user()->hasPermissionTo('daily_collection_approved')) {
                abort(403, 'Unauthorized');
            }
            // Approved
            $vouchers = Voucher::whereIn('id', $request->collection_voucher_id)
                ->whereNotIn('collection_receive_status', [1, 2])
                ->get();
            foreach ($vouchers as $voucher) {
                $voucher->collection_receive_status = $request->confirm_status;//1=Selected,2=Bank Deposit
                $voucher->save();
            }

            // Return a JSON success response
            return redirect()->route('report.daily_collection')
                ->with('success', 'Payment received successfully');
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return redirect()->route('report.daily_collection')
                ->with('error', 'Payment received is not successfully: ' . $e->getMessage());
        }
    }

    public function ledger(Request $request)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));

        $accountHeadsList = AccountHead::orderBy('code')->get();

        $accountHeads = [];
        if ($request->start_date != '') {
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));
            $query = AccountHead::query();
            if ($request->account_head != '') {
                $query->where('id', $request->account_head);
            }
            $query->with([
                'openingTransactions' => function ($query) use ($previousDay) {
                    $query->whereDate('date', '<=', $previousDay)
                        ->select(DB::raw('account_head_id,
                            SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                            SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                        ->groupBy('account_head_id');
                }, 'transactions' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date')
                        ->orderBy('voucher_no');
                },
            ]);

            $accountHeads = $query->orderBy('code')
                ->get();
        }


        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }


        return view('report.ledger', compact('accountHeads',
            'currentDate', 'accountHeadsList'));
    }

    public function trialBalance(Request $request)
    {
        $accountHeadsList = AccountHead::orderBy('code')->get();

        $accountHeads = [];

        $filterFinancialYear = null;
        if ($request->start_date != '' && $request->end_date != '') {
            $query = AccountHead::query();
            if ($request->account_head != '') {
                $query->where('id', $request->account_head);
            }

            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');
            $accountHeads = $query
                ->with(['openingTransactions' => function ($query) use ($previousDay) {
                    $query->whereDate('date', '<=', $previousDay)
                        ->select(DB::raw('account_head_id,transaction_type,
                        SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                        SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                        ->groupBy('account_head_id', 'transaction_type');
                }])
                ->with(['transactions' => function ($query) use ($start, $end) {
                    $query->whereBetween('date', [$start, $end])
                        ->select(DB::raw('account_head_id,
                         SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS debit,
                         SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS credit'))
                        ->groupBy('account_head_id');
                }])
                ->orderBy('code')
                ->get();

        }

        $in_word = new DecimalToWords();

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        return view('report.trial_balance', compact('currentDate', 'accountHeads', 'in_word',
            'accountHeadsList', 'filterFinancialYear'));
    }

    public function incomeStatement()
    {
        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }


        $revenueType = AccountGroup::where('id', 2)
            ->first();


        $vatExpense = AccountGroup::where('id', 31)
            ->first();
        $salesReturnsAndAllowances = AccountGroup::where('id', 26)
            ->first();


        $costType = AccountGroup::where('id', 29)
            ->first();

        $administrativeExpense = AccountGroup::where('id', 30)
            ->first();
        $sellingDistributionExpenses = AccountGroup::where('id', 32)
            ->first();
        $financialExpenses = AccountGroup::where('id', 33)
            ->first();
        $otherIncome = AccountGroup::where('id', 34)
            ->first();
        $incomeTaxExpense = AccountGroup::where('id', 35)
            ->first();
        return view('report.income_statement', compact('currentDate',
            'revenueType', 'vatExpense', 'costType',
            'administrativeExpense', 'sellingDistributionExpenses',
            'financialExpenses', 'otherIncome', 'incomeTaxExpense', 'salesReturnsAndAllowances'));
    }

    public function balanceSheet()
    {
        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }


        $assets = AccountGroup::where('account_group_id', 3)
            ->with('accountGroups')
            ->get();
        $liabilities = AccountGroup::where('account_group_id', 5)
            ->with('accountGroups')
            ->get();


        $equities = AccountGroup::where('id', 4)
            ->with('accountGroups')
            ->get();

        return view('report.balance_sheet', compact('assets', 'liabilities',
            'equities', 'currentDate'));
    }
}
