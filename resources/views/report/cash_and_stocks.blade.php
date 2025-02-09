@extends('layouts.app')
@section('title','Cash & Stocks Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 2px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.cash-and-stock') }}">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="company" class="col-form-label">Company <span
                                            class="text-danger">*</span></label>
                                    <select name="company" id="company" class="form-control select2">
                                        <option value="">All Company</option>
                                        @foreach($companies as $company)
                                            <option {{ request('company') == $company->id ? 'selected' : '' }} value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" required value="{{ request('start_date') }}" name="start_date" id="start_date" class="form-control date-picker" placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" required value="{{ request('end_date') }}" name="end_date" id="end_date" class="form-control date-picker" placeholder="Enter End Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -4px;" type="submit" id="search_btn" name="search"
                                           class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if(request('search') != '')
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i></a>
                    <a role="button" id="btn-export" class="btn btn-primary bg-gradient-indigo btn-sm"><i class="fas fa-file-excel"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive-md" id="printArea">
                        <div class="row" style="border-bottom: 1.5px solid #000;">
                            <div class="col-4">
                                <h1 class="text-left m-0" style="font-size: 45px !important;font-weight: bold">
                                    <img height="110px" src="{{ asset('img/logo.png') }}" alt="">
                                </h1>
                            </div>
                            <div class="col-8 text-right">
                                <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
                                <h5 class="m-0">{{ config('app.address') }}</h5>
                                <h5 class="m-0">{{ config('app.contact') }}</h5>
                                <h5>{{ config('app.email') }}</h5>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Cash & Stocks Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ request('start_date') }} to {{ request('end_date') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">CASH ON HAND</th>
                                <th class="text-center">Due Collection</th>
                                <th class="text-center">Physical Stock</th>
                                <th class="text-center">Due</th>
                                <th class="text-center">Company Due</th>
                                <th class="text-center">Total Asset</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $paidSum = 0;
                                $dueCollectionSum = 0;
                                $dueSum = 0;
                                $purchaseDueSum = 0;
                                $physicalStockSum = 0;
                                $assetSum = 0;
                            @endphp
                                @foreach($logCompanies as $logCompany)
                                    @php


                                        $paid = $logCompany->vouchers->whereNotNull('sale_order_id')->where('voucher_type',\App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                                                                ->where('due_payment',0)
                                                                ->sum('amount') ?? 0;
                                        $dueCollection = $logCompany->vouchers->whereNotNull('sale_order_id')->where('voucher_type',\App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                                                                ->where('due_payment',1)
                                                                ->sum('amount') ?? 0;
                                        $due = ($logCompany->distributionOrders->sum('total') ?? 0) - ($paid + $dueCollection);

                                        $purchaseDue = ($logCompany->purchaseOrders->sum('total') ?? 0) - $logCompany->vouchers->where('voucher_type',\App\Enumeration\VoucherType::$PAYMENT_VOUCHER)
                                                                        ->whereNotNull('purchase_order_id')->sum('amount') ?? 0;

                                        $transactions = $logCompany->transactions;
                                        $physicalStock = ($transactions->where('transaction_type',\App\Enumeration\TransactionType::$DEBIT)
                                        ->sum('amount') ?? 0) - ($transactions->where('transaction_type',\App\Enumeration\TransactionType::$CREDIT)
                                        ->sum('amount') ?? 0);

                                        $paidSum += $paid;
                                        $dueCollectionSum += $dueCollection;
                                        $dueSum += $due;
                                        $purchaseDueSum += $purchaseDue;
                                        $physicalStockSum += $physicalStock;
                                        $asset = $paid + $dueCollection + $due + $physicalStock - $purchaseDue;
                                        $assetSum += $asset;


                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $logCompany->name ?? '' }}</td>
                                        <td class="text-right">{{ number_format($paid,2) }}</td>
                                        <td class="text-right">{{ number_format($dueCollection,2) }}</td>
                                        <td class="text-right">{{ number_format($physicalStock,2) }}</td>
                                        <td class="text-right">{{ number_format($due,2) }}</td>
                                        <td class="text-right">{{ number_format($purchaseDue,2) }}</td>
                                        <td class="text-right">{{ number_format($asset,2) }}</td>
                                    </tr>
                                @endforeach
                            <tr>
                                <th colspan="2"></th>
                                <th colspan="2" class="text-right">{{ number_format($paidSum + $dueCollectionSum,2) }}</th>
                                <th colspan="4"></th>
                            </tr>
                            <tr>
                                <th class="text-center" colspan="2">Total</th>
                                <th class="text-right">{{ number_format($paidSum,2) }}</th>
                                <th class="text-right">{{ number_format($dueCollectionSum,2) }}</th>
                                <th class="text-right">{{ number_format($physicalStockSum,2) }}</th>
                                <th class="text-right">{{ number_format($dueSum,2) }}</th>
                                <th class="text-right">{{ number_format($purchaseDueSum,2) }}</th>
                                <th class="text-right">{{ number_format($assetSum,2) }}</th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        $(function(){
            $('#btn-export').click(function(){
                //alert('Hasan');
                var htmlContent = $('#printArea').html();

                $.ajax({
                    url: "{{ route('export.html_content') }}",
                    type: "POST",
                    data: {
                        title: "Cash & Stocks Report",
                        start_date: "{{ request('start_date') }}",
                        end_date: "{{ request('end_date') }}",
                        htmlContent: htmlContent,
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (response) {
                        var url = window.URL.createObjectURL(response);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'cash_and_stocks_report.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    },
                    error: function (xhr) {
                        console.error("An error occurred: " + xhr.responseText);
                    }
                });
            });
        });

        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "Cash-Stocks-Report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
