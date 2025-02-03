<?php

namespace App\Http\Controllers;

use App\Models\SaleOrder;
use App\Models\Client;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounts\Entities\AccountHeadType;
use SakibRahaman\DecimalToWords\DecimalToWords;
use Yajra\DataTables\Facades\DataTables;

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

    public function salesReport(Request $request)
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $clients = Client::where('type', 4); // type = 4 is for clinet
            $logQuery = SaleOrder::query();
        }else{
            $clients = Client::where('type', 4)->where('sr_id',auth()->user()->client_id)->get(); //type=4 is Client
            $logQuery = SaleOrder::where('sr_id',auth()->user()->client_id);
        }

        $clients = Client::where('type', 4)->get(); // type = 4 is for clinet


        $logs = [];

        if ($request->start_date != '' && $request->end_date != '') {
            $logQuery->whereBetween('date', [
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);

            if ($request->client != '') {
                $logQuery->where('client_id', $request->client);
            }

            $logs = $logQuery->with('client','sr')
                ->orderBy('date')
                ->get();

        }

        return view('report.sales_report', compact('logs', 'clients'));
    }
    public function salesTrackingReport(Request $request)
    {
        if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
            $clients = Client::where('type', 4); // type = 4 is for clinet
            $saleOrder = SaleOrder::query();
        }else{
            $clients = Client::where('type', 4)->where('sr_id',auth()->user()->client_id)->get(); //type=4 is Client
            $saleOrder = SaleOrder::where('sr_id',auth()->user()->client_id);
        }
        $clients = Client::where('type', 4)->get(); // type = 4 is for clinet

        $saleOrders = [];

        if ($request->start_date != '' && $request->end_date != '') {
            $saleOrder->whereBetween('date', [
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);

            if ($request->client != '') {
                $saleOrder->where('client_id', $request->client);
            }

            $saleOrders = $saleOrder->with('client','sr','locationAddressInfo')
                ->orderBy('date')
                ->get();

        }

        return view('report.sales_tracking', compact('saleOrders', 'clients'));
    }
}
