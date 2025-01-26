@extends('layouts.app')
@section('title','Purchase Orders Details')
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
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
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
                    @if (auth()->user()->hasPermissionTo('purchase_edit'))
                    <a href="{{ route('purchase.edit', ['purchase' => $purchase->id]) }}" class="btn btn-primary bg-gradient-primary btn-sm">Edit <i class="fa fa-edit"></i></a>
                    @endif
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
                                <h5 class="text-center m-0">Company: {{ $purchase->supplier->name ?? '' }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">ORDER NO. {{ $purchase->order_no }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">DATE: {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Purchase Unit Price</th>
                                <th class="text-center">Selling Unit Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Total Purchase Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalPurchasePrice = 0;
                            @endphp
                            @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->code ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->brand->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-right">{{ number_format($item->product_unit_price,2) }}</td>
                                    <td class="text-right">{{ number_format($item->product_selling_unit_price,2) }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->product_unit_price,2) }}</td>
                                </tr>
                                @php
                                    $totalPurchasePrice += $item->quantity * $item->product_unit_price;
                                @endphp
                            @endforeach
                                <tr>
                                    <th class="text-right" colspan="7">Total</th>
                                    <th class="text-center">{{ number_format($purchase->purchaseItems->sum('quantity')) }}</th>
                                    <th class="text-right">{{ number_format($totalPurchasePrice,2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="7">Payment</th>
                                    <th class="text-center"></th>
                                    <th class="text-right">{{ number_format($purchase->paid,2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="7">Due</th>
                                    <th class="text-center"></th>
                                    <th class="text-right">{{ number_format($purchase->due,2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row signature-area" >
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        {{ $purchase->user->name ?? '' }}<br>
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
            document.title = "{{ $purchase->order_no }}_purchase_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
