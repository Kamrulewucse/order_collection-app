@extends('layouts.app')
@section('title','Sales Vs Payment Report')
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
                    <form action="{{ route('report.sales-vs-payments') }}">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="company" class="col-form-label">Company <span
                                            class="text-danger">*</span></label>
                                    <select  name="company" id="company" class="form-control select2">
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>Sales Vs Payment Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ request('start_date') }} to {{ request('end_date') }}</h5>

                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Stock Value</th>
                                <th class="text-center">Sales</th>
                                <th class="text-center">Cash Collection</th>
                                <th class="text-center">Due Collection</th>
                                <th class="text-center">Current Due</th>
                                <th class="text-center">Primary</th>
                                <th class="text-center">Primary Req (with due)</th>
                                <th class="text-center">Primary Req (without due)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $stockValueSum = 0;
                                $salesSum = 0;
                                $cashCollectionSum = 0;
                                $dueCollectionSum = 0;
                                $currentDueSum = 0;
                                $primarySum = 0;
                                $primaryWithDueSum = 0;
                                $primaryWithOutDueSum = 0;
                            @endphp
                                @foreach($logCompanies as $logCompany)
                                    @php
                                            $stockValue = $logCompany->inventories->sum('physical_stock') ?? 0;;
                                            $sales = $logCompany->distributionOrders->sum('total') ?? 0;

                                            $cashCollection = $logCompany->vouchers->whereNotNull('sale_order_id')
                                                                ->where('voucher_type',\App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                                                                ->where('due_payment',0)
                                                                ->sum('amount') ?? 0;
                                            $dueCollection = $logCompany->vouchers->whereNotNull('sale_order_id')
                                                            ->where('voucher_type',\App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                                                                ->where('due_payment',1)
                                                                ->sum('amount') ?? 0;
                                            $currentDue = $sales - ($cashCollection + $dueCollection);


                                        $primary = $logCompany->purchaseOrders->sum('total') ?? 0;;
                                        $primaryWithDue = $primary - $sales;
                                        $primaryWithOutDue = $primary - $sales - $currentDue;

                                        $stockValueSum += $stockValue;
                                        $salesSum += $sales;

                                        $cashCollectionSum += $cashCollection;
                                        $dueCollectionSum += $dueCollection;
                                        $currentDueSum += $currentDue;
                                        $primarySum += $primary;
                                        $primaryWithDueSum += $primaryWithDue;
                                        $primaryWithOutDueSum += $primaryWithOutDue;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $logCompany->name ?? '' }}</td>
                                        <td class="text-right">{{ number_format($stockValue,2) }}</td>
                                        <td class="text-right">{{ number_format($sales,2) }}</td>
                                        <td class="text-right">{{ number_format($cashCollection,2) }}</td>
                                        <td class="text-right">{{ number_format($dueCollection,2) }}</td>
                                        <td class="text-right">{{ number_format($currentDue,2) }}</td>
                                        <td class="text-right">{{ number_format($primary,2) }}</td>
                                        <td class="text-right">{{ number_format($primaryWithDue,2) }}</td>
                                        <td class="text-right">{{ number_format($primaryWithOutDue,2) }}</td>
                                    </tr>
                                @endforeach
                            <tr>
                                <th class="text-center" colspan="2">Total</th>
                                <th class="text-right">{{ number_format($stockValueSum,2) }}</th>
                                <th class="text-right">{{ number_format($salesSum,2) }}</th>
                                <th class="text-right">{{ number_format($cashCollectionSum,2) }}</th>
                                <th class="text-right">{{ number_format($dueCollectionSum,2) }}</th>
                                <th class="text-right">{{ number_format($currentDueSum,2) }}</th>
                                <th class="text-right">{{ number_format($primarySum,2) }}</th>
                                <th class="text-right">{{ number_format($primaryWithDueSum,2) }}</th>
                                <th class="text-right">{{ number_format($primaryWithOutDueSum,2) }}</th>
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
                        title: "Sales Vs Payment Report",
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
                        a.download = 'sale_vs_payment_report.xlsx';
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
            document.title = "Sales Vs Payment Report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
