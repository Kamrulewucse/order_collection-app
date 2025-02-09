@extends('layouts.app')
@section('title','Inventory In Report')
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
                    <form action="{{ route('report.inventory_in') }}">
                        <div class="row">
                            <div class="col-12 col-md-5">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('start_date',date('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('end_date',date('d-m-Y')) }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>Inventory In Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalPrice = 0;
                            @endphp
                                @foreach($logs as $log)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($log->date)->format('d-m-Y') }}</td>
                                        <td>{{ $log->supplier->name ?? '' }}</td>
                                        <td>{{ $log->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $log->product->code ?? '' }}</td>
                                        <td class="text-center">{{ $log->product->brand->name ?? '' }}</td>
                                        <td class="text-center">{{ $log->product->unit->name ?? '' }}</td>
                                        <td class="text-right">{{ number_format($log->unit_price,2) }}</td>
                                        <td class="text-right">{{ number_format($log->quantity) }}</td>
                                        <td class="text-right">{{ number_format($log->quantity * $log->unit_price,2) }}</td>
                                    </tr>
                                    @php
                                        $totalPrice += $log->quantity * $log->unit_price;
                                    @endphp
                                @endforeach
                            <tr>
                                <td class="text-center" colspan="8">Total</td>
                                <td class="text-right">{{ number_format($logs->sum('quantity')) }}</td>
                                <td class="text-right">{{ number_format($totalPrice,2) }}</td>
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
                        title: "Inventory In Report",
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
                        a.download = 'inventory_in_report.xlsx';
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
