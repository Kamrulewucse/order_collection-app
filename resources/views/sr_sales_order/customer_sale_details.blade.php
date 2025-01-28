@extends('layouts.app')
@section('title',$pageTitle)
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            padding: 2px;
        }
        .table-bordered.sale-order-table td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }
        .table.sale-order-table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;;
        }
        .customer-area-border {
            border: 1.5px solid #000 !important;;
            padding: 3px;
            min-height: 128px;
            margin-top: 10px;
            margin-bottom: 50px;
        }
        .customer-area-border p {
            font-size: 20px !important;
        }

        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
            @page {
                size: auto;
                margin: .5in !important;
            }
            .signature-area{
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-primary bg-gradient-primary btn-sm">Print <i class="fa fa-print"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
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
                            <div class="col-6">
                                <div class="customer-area-border">
                                    <p class="text-left m-0">DSR: {{ $distributionOrder->dsr->name ?? '' }}</p>
                                    <p class="text-left m-0">Mobile No. {{ $distributionOrder->dsr->mobile_no ?? '' }}</p>
                                    <p class="text-left m-0">Email: {{ $distributionOrder->dsr->email ?? '' }}</p>
                                    <p class="text-left m-0">Address: {{ $distributionOrder->dsr->address ?? '' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="customer-area-border">
                                    <p class="text-left m-0">Order No: {{ $distributionOrder->order_no }}</p>
                                    <p class="text-left m-0">Date: {{ \Carbon\Carbon::parse($distributionOrder->date)->format('d-m-Y') }}</p>
                                    <p class="text-left m-0">Notes: {{ $distributionOrder->notes }}</p>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered sale-order-table">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Total Amount</th>
                                <th class="text-center">Paid Amount</th>
                                <th class="text-center">Due Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($saleOrders as $saleOrder)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ $saleOrder->order_no }}</td>
                                    <td>{{ $saleOrder->customer->name ?? '' }}</td>
                                    <td class="text-right">{{ number_format($saleOrder->total,2) }}</td>
                                    <td class="text-right">{{ number_format($saleOrder->paid) }}</td>
                                    <td class="text-right">{{ number_format($saleOrder->due) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th class="text-right" colspan="3">Total</th>
                                <th class="text-right">{{ number_format($saleOrders->sum('total')) }}</th>
                                <th class="text-right">{{ number_format($saleOrders->sum('paid')) }}</th>
                                <th class="text-right">{{ number_format($saleOrders->sum('due')) }}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';
        function getprintChallan(print) {
            document.title = "{{ $pageTitle }}_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
