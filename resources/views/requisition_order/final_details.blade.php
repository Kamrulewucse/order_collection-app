@extends('layouts.app')
@section('title',$pageTitle)
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            padding: 2px;
        }
        .table thead th {
            vertical-align: bottom;
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
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-primary bg-gradient-primary btn-sm">Print <i class="fa fa-print"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        <div class="row" style="border-bottom: 1.5px solid #000;">
                            <div class="col-12">
                                <h5 class="text-center">{{ config('app.religious_title') }}</h5>
                            </div>
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
                        <div class="row pt-3 pb-3" >
                            <div class="col-4">
                                <h5 class="text-center m-0">DSR: {{ $distributionOrder->dsr->name ?? '' }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">ORDER NO. {{ $distributionOrder->order_no }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">DATE: {{ \Carbon\Carbon::parse($distributionOrder->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th colspan="11">Distribution Receipt</th>
                            </tr>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Delivery Quantity</th>
                                <th class="text-center">Return Quantity</th>
                                <th class="text-center">Final Sales Qty</th>
                                <th class="text-center">Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalPurchasePrice = 0;
                            @endphp
                            @foreach($distributionOrder->distributionOrderItems as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->product->supplier->name ?? '' }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->code ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->brand->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-right">{{ number_format($item->selling_unit_price,2) }}</td>
                                    <td class="text-center">{{ number_format($item->distribute_quantity) }}</td>
                                    @if($distributionOrder->type == 1)
                                    <td class="text-center">{{ number_format($item->return_quantity) }}</td>
                                        <td class="text-center">{{ number_format($item->sale_quantity) }}</td>
                                    @endif
                                    <td class="text-right">{{ number_format(($item->sale_quantity == 0 ? $item->distribute_quantity : $item->sale_quantity) * $item->selling_unit_price,2) }}</td>
                                </tr>
                                @php
                                    $totalPurchasePrice += ($item->sale_quantity == 0 ? $item->distribute_quantity : $item->sale_quantity) * $item->selling_unit_price;
                                @endphp
                            @endforeach
                                <tr>
                                    <th class="text-right" colspan="7">Total</th>
                                    <th class="text-center">{{ number_format($distributionOrder->distributionOrderItems->sum('distribute_quantity')) }}</th>

                                    @if($distributionOrder->type == 1)
                                    <th class="text-center">{{ number_format($distributionOrder->distributionOrderItems->sum('return_quantity')) }}</th>
                                        <th class="text-center">{{ number_format($distributionOrder->distributionOrderItems->sum('sale_quantity')) }}</th>
                                    @endif
                                    <th class="text-right">{{ number_format($totalPurchasePrice,2) }}</th>
                                </tr>
                                @php
                                    $distQty = $distributionOrder->distributionOrderItems->sum('distribute_quantity');
                                    $returnQty = $distributionOrder->distributionOrderItems->sum('return_quantity');

                                    $returnPercent = ($returnQty / $distQty) * 100;
                                @endphp

                                <tr>
                                    <th class="text-right" colspan="{{ $distributionOrder->type == 1 ? 9 : 6 }}">Payment</th>
                                    <th class="text-center">DEL Return: {{ $returnPercent }}%</th>
                                    <th class="text-right">{{ number_format($distributionOrder->paid,2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="{{ $distributionOrder->type == 1 ? 9 : 6 }}">Due</th>
                                    <th class="text-center"></th>
                                    <th class="text-right">{{ number_format($distributionOrder->due,2) }}</th>
                                </tr>
                            </tbody>
                        </table>

                        <table class="table table-bordered sale-order-table">
                            <thead>
                            <tr>
                                <th colspan="6">Customer Bill Receipt</th>
                            </tr>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Due Amount</th>
                                <th class="text-center">Due Paid Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($distributionOrder->saleOrders as $saleOrder)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ $saleOrder->order_no }}</td>
                                    <td>{{ $distributionOrder->company->name ?? '' }}</td>
                                    <td>{{ $saleOrder->customer->name ?? '' }}</td>
                                    <td class="text-right">{{ number_format($saleOrder->due,2) }}</td>
                                    <td class="text-center">
                                        @if($saleOrder->due == 0)
                                            <span class="text-success">Full Payment</span>
                                        @else
                                            @if($saleOrder->paid > 0)
                                                <span class="text-warning">Partial Payment</span>
                                            @else
                                                <span class="text-danger">Full Due</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th class="text-right" colspan="4">Total</th>
                                <th class="text-right">{{ number_format($distributionOrder->saleOrders->sum('due'),2) }}</th>
                                <th></th>

                            </tr>
                            </tfoot>
                        </table>

                        <div class="row signature-area" >
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        <small>{{ $distributionOrder->user->name ?? '' }}</small><br>
                                         Created By
                                    </span>
                            </div>
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        <br>
                                        Checked By
                                    </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';
        function getprintChallan(print) {
            document.title = "{{ $distributionOrder->order_no }}_{{ $pageTitle }}_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
