@extends('layouts.app')
@section('title','Payment Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }
        .table thead th {
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
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
        }
        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
            img {
                display: block !important;
                max-width: 100% !important;
                height: auto !important;
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
                    {{-- <a href="{{ route('sales.ient-primary btn-sm">SM Print <i class="fa fa-print"></i></a> --}}
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        <div class="row">
                            <div class="col-2">
                                <img height="70px" src="{{env('LOGO_URL')}}" alt="">
                            </div>
                            <div class="col-8 text-center">
                                <h4 class="text-center m-3"><b style="border:2px solid black;padding:5px; border-radius:3%;">PAYMENT RECEIPT</b></h4>
                            </div>
                            <div class="col-2">
                                <b>Date : </b>  {{ \Carbon\Carbon::parse($salePayment->date)->format('d-M-Y') }} <br>
                                <b>No  &nbsp;&nbsp;&nbsp;&nbsp;: </b> {{ $salePayment->id + 1000 }} <br>
                            </div>
                        </div>

                    <div class="row" style="margin-top: 20px">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="20%">
                                            From
                                    </th>
                                    <td>{{ $salePayment->client->name ?? '' }}</td>
                                    <th width="10%">Amount</th>
                                    <td width="15%">৳{{ number_format($salePayment->amount, 2) }}</td>
                                </tr>

                                <tr>
                                    <th>Amount (In Word)</th>
                                    <td colspan="3">{{ $salePayment->amount_in_word }}</td>
                                </tr>

                                <tr>
                                    <th>For Payment of</th>
                                    <td colspan="3">Order No. {{ $salePayment->saleOrder->order_no ?? '' }}</td>
                                </tr>

                                <tr>
                                    <th>Received By</th>
                                    <td colspan="3">
                                        {{$salePayment->user->name ?? ''}}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Note</th>
                                    <td colspan="3">{{ $salePayment->note }}</td>
                                </tr>
                            </table>
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
            document.title = "payment details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
        function myFunction() {
            /* Get the text field */
            var copyText = document.getElementById("myInput");
            /* Select the text field */
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            /* Copy the text inside the text field */
            navigator.clipboard.writeText(copyText.value);

            /* Alert the copied text */
            alert("Copied link: " + copyText.value);
        }
    </script>
@endsection
