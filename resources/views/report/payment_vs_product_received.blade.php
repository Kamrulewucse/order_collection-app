@extends('layouts.app')
@section('title','Payment Vs Product Received Report')
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
                    <form action="{{ route('report.payment-vs-product-received') }}">
                        <div class="row">
                            <div class="col-12 col-md-3">
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
                                    <input required autocomplete="off" type="text" value="{{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
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
        @if(request('start_date') != '')
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>Payment Vs Product Received Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Cash Paid</th>
                                <th class="text-center">Product Received</th>
                                <th class="text-center">Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalSum = 0;
                                $paidSum = 0;
                                $dueSum = 0;
                            @endphp
                                @foreach($logCompanies as $logCompany)
                                    @php
                                        $total = $purchaseDue = $logCompany->vouchers->whereNotNull('purchase_order_id')->where('voucher_type',\App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                                            ->sum('amount') ?? 0;
                                        $paid = $logCompany->vouchers->whereNotNull('purchase_order_id')
                                                                ->where('voucher_type',\App\Enumeration\VoucherType::$PAYMENT_VOUCHER)
                                                                ->sum('amount') ?? 0;
                                        $due = $total - $paid;
                                        $totalSum += $total;
                                        $paidSum += $paid;
                                        $dueSum += $due;

                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $logCompany->name }}</td>
                                        <td class="text-right">{{ number_format($paid,2) }}</td>
                                        <td class="text-right">{{ number_format($total,2) }}</td>
                                        <td class="text-right">{{ number_format($due,2) }}</td>
                                    </tr>
                                @endforeach
                            <tr>
                                <th class="text-center" colspan="2">Total</th>
                                <th class="text-right">{{ number_format($paidSum,2) }}</th>
                                <th class="text-right">{{ number_format($totalSum,2) }}</th>
                                <th class="text-right">{{ number_format($dueSum,2) }}</th>
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
                        title: "Payment Vs Product Received Report",
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
                        a.download = 'payment_vs_product_received_report.xlsx';
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
            document.title = "{{ request('start_date') }}_to_{{ request('end_date') }}-Payment-Vs-Product-Received-Report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
