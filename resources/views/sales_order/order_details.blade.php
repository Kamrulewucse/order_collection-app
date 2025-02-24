@extends('layouts.app')
@section('title', $pageTitle)
@section('style')
    <style>
        .table-bordered td,
        .table-bordered th {
            padding: 2px;
        }

        .customer-area-border {
            border: 1.5px solid #000 !important;
            ;
            padding: 3px;
            min-height: 128px;
            margin-top: 10px;
            margin-bottom: 50px;
        }

        .customer-area-border p {
            font-size: 20px !important;
        }

        @media print {

            .table-bordered td,
            .table-bordered th {
                font-size: 18px !important;
            }

            @page {
                size: auto;
                margin: .5in !important;
            }

            .signature-area {
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
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')"
                        class="btn btn-primary bg-gradient-primary btn-sm">Print <i class="fa fa-print"></i></a>
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
                                    <img height="80px" width="150" src="{{ asset('img/logo.png') }}" alt="">
                                </h1>
                            </div>
                            <div class="col-8 text-right">
                                <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
                                <h5 class="m-0">{{ config('app.address') }}</h5>
                                <h5 class="m-0">{{ config('app.contact') }}</h5>
                                <h5>{{ config('app.email') }}</h5>

                            </div>
                        </div>
                        <div class="row pt-3 pb-3">
                            <div class="col-4">
                                <h5 class="text-center m-0">SR: {{ $saleOrder->sr->name ?? '' }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">ORDER NO. {{ $saleOrder->order_no }}</h5>
                                <h6 class="text-center m-0">Client: {{ $saleOrder->client->name }}</h6>
                                <h6 class="text-center m-0">Client Type: {{ $saleOrder->client->client_type }}</h6>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">DATE:
                                    {{ \Carbon\Carbon::parse($saleOrder->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center" width="3%">S/L</th>
                                    <th class="text-center">Product</th>
                                    <th class="text-center" width="10%">Unit Price</th>
                                    <th class="text-center" width="12%">Delivery Qty</th>
                                    <th class="text-center" width="10%">Damage Qty</th>
                                    <th class="text-center" width="14%">Final Sales Qty</th>
                                    <th class="text-center" width="10%">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalPurchasePrice = 0;
                                @endphp
                                @foreach ($saleOrder->saleOrderItems as $item)
                                    <tr class="product-item">
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-right">{{ number_format($item->selling_unit_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->sr_sale_quantity) }}</td>
                                        <td class="text-right">
                                            {{ number_format($item->damage_quantity) }}
                                            <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                            <input type="hidden" name="damage_quantity[]" class="damage_quantity"
                                                value="{{ $item->damage_quantity }}">
                                            <input type="hidden" name="sr_sale_quantity[]" class="sr_sale_quantity"
                                                value="{{ $item->sr_sale_quantity }}">
                                            <input type="hidden" name="selling_unit_price[]" class="selling_unit_price"
                                                value="{{ $item->selling_unit_price }}">
                                        </td>
                                        <td class="text-right total-sale-Qty">{{ number_format($item->sale_quantity) }}
                                        </td>
                                        <td class="text-right total-sale">
                                            {{ number_format($item->selling_unit_price * $item->sale_quantity, 2) }}
                                        </td>
                                    </tr>
                                    @php
                                        $totalPurchasePrice +=
                                            $item->purchase_unit_price * $item->sale_quantity;
                                    @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-right" colspan="6">Total</th>
                                    <th class="text-right" id="total-amount"> {{ number_format($saleOrder->total, 2) }}
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="6">Paid</th>
                                    <th class="text-right"> {{ number_format($saleOrder->paid, 2) }}</th>
                                    <input type="hidden" id="paid" value="{{ $saleOrder->paid }}">
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="6">Due</th>
                                    <th class="text-right" id="due"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        calculate();
        $(function() {
            calculate();

            var customerIds = [];

            $(".customer_id").each(function(index) {
                if ($(this).val() != '') {
                    customerIds.push($(this).val());
                }
            });

            $('body').on('keyup', 'input[type="number"], #paid', function() {
                calculate();
            });
            $('body').on('change', 'input[type="number"], #paid', function() {
                calculate();
            });
        })

        function calculate() {
            var rows = $("#product-container .customer-item");
            var total_selling_quantity = 0;
            var total_damage_quantity = 0;
            var total_sale_price = 0;
            var paid = $('#paid').val();
            if (paid == '' || paid < 0 || !$.isNumeric(paid))
                paid = 0;

            $('.product-item').each(function(i, obj) {
                let damage_quantity = parseFloat($('.damage_quantity:eq(' + i + ')').val());
                damage_quantity = (isNaN(damage_quantity) || damage_quantity < 0) ? 0 : damage_quantity;

                let sr_sale_quantity = parseFloat($('.sr_sale_quantity:eq(' + i + ')').val());
                sr_sale_quantity = (isNaN(sr_sale_quantity) || sr_sale_quantity < 0) ? 0 :
                    sr_sale_quantity;


                let selling_unit_price = parseFloat($('.selling_unit_price:eq(' + i + ')').val());
                selling_unit_price = (isNaN(selling_unit_price) || selling_unit_price < 0) ? 0 : selling_unit_price;

                let selling_quantity = sr_sale_quantity - damage_quantity;

                $('.total-sale-Qty:eq(' + i + ')').text((selling_quantity).toFixed(2));
                $('.total-sale:eq(' + i + ')').text((selling_quantity * selling_unit_price).toFixed(2));

                total_damage_quantity += damage_quantity;
                total_selling_quantity += selling_quantity;

                total_sale_price += selling_quantity * selling_unit_price;
            });

            var due = parseFloat(total_sale_price) - parseFloat(paid);

            $('#total_selling_quantity').text(total_selling_quantity);
            $('#total_damage_quantity').text(total_damage_quantity);
            $('#total_sale_price').text(total_sale_price.toFixed(2));
            $('#total-amount').text(total_sale_price.toFixed(2));
            $('#due').html(due.toFixed(2));

        }
        var APP_URL = '{!! url()->full() !!}';

        function getprintChallan(print) {
            document.title = "{{ $saleOrder->order_no }}_{{ $pageTitle }}_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
