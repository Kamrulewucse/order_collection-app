@extends('layouts.app')
@section('title','Sales Report')
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
                    <form action="{{ route('sales_report_tracking') }}">
                        <div class="row">
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('start_date',date('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('end_date',date('d-m-Y')) }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="client" class="col-form-label">Client <span
                                            class="text-danger">*</span></label>
                                    <select name="client" id="client" class="form-control select2">
                                        <option value="">All Client</option>
                                        @foreach($clients as $client)
                                        <option {{ request('client') == $client->id ? 'selected' : '' }} value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>Sales Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th style="width: 3%;">S/L</th>
                                <th style="width: 15%;">Date</th>
                                <th style="width: 12%;">SR</th>
                                <th style="width: 12%;">Client</th>
                                <th style="width: 23%;">Client Address</th>
                                <th style="width: 23%;">SR Sale Address</th>
                                <th style="width: 12%;">Order No</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalDueCollection = 0;
                            @endphp
                                @foreach($saleOrders as $saleOrder)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($saleOrder->date)->format('d-m-Y') }}</td>
                                        <td>{{ $saleOrder->sr->name ?? '' }}</td>
                                        <td>{{ $saleOrder->client->name ?? '' }}</td>
                                        <td>{{ $saleOrder->client->location_address ?? '' }}</td>
                                        <td>{{ $saleOrder->locationAddressInfo->location_address ?? '' }}</td>
                                        <td class="text-right"><a href="{{route('sales-order.day_close', ['saleOrder' => $saleOrder->id, 'type' => 1])}}" target="_blank">{{ $saleOrder->order_no }}</a></td>
                                    </tr>
                                @endforeach
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
                        title: "Sales Report",
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
                        a.download = 'sales_report.xlsx';
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
            document.title = "{{ request('start_date') }}_to_{{ request('end_date') }}_receipt_and_payment_report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
