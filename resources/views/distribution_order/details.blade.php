@extends('layouts.app')
@section('title',$pageTitle)
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
                                <th class="text-center">Sale Quantity</th>
                                <th class="text-center">Return Quantity</th>
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
                                    <td class="text-center">{{ number_format($item->sale_quantity) }}</td>
                                    <td class="text-center">{{ number_format($item->return_quantity) }}</td>
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
                                    <th class="text-center">{{ number_format($distributionOrder->distributionOrderItems->sum('sale_quantity')) }}</th>
                                    <th class="text-center">{{ number_format($distributionOrder->distributionOrderItems->sum('return_quantity')) }}</th>
                                    @endif
                                    <th class="text-right">{{ number_format($totalPurchasePrice,2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="{{ $distributionOrder->type == 1 ? 9 : 6 }}">Payment</th>
                                    <th class="text-center"></th>
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
                                <th colspan="9">Customer Bill Receipt</th>
                            </tr>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Total Amount</th>
                                <th class="text-center">Paid Amount</th>
                                <th class="text-center">Due Amount</th>
                                <th class="text-center">Payment Status</th>
                                <th class="text-center extra-remove">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($distributionOrder->saleOrders as $saleOrder)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ $saleOrder->order_no }}</td>
                                    <td>{{ $distributionOrder->company->name ?? '' }}</td>
                                    <td>{{ $saleOrder->customer->name ?? '' }}</td>
                                    <td class="text-right">{{ number_format($saleOrder->total,2) }}</td>
                                    <td class="text-right">{{ number_format($saleOrder->paid,2) }}</td>
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
                                    <td class="text-center extra-remove">
                                        @if($distributionOrder->close_status == 1 && $saleOrder->due > 0)
                                            <a role="button" data-id="{{ $saleOrder->id }}" data-order_no="{{ $saleOrder->order_no }}" data-total="{{ $saleOrder->total }}" data-due="{{ $saleOrder->due }}" data-paid="{{ $saleOrder->paid }}" class="btn btn-success bg-gradient-success btn-sm customer-pay">Make Payment</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th class="text-right" colspan="4">Total</th>
                                <th class="text-right">{{ number_format($distributionOrder->saleOrders->sum('total'),2) }}</th>
                                <th class="text-right">{{ number_format($distributionOrder->saleOrders->sum('paid'),2) }}</th>
                                <th class="text-right">{{ number_format($distributionOrder->saleOrders->sum('due'),2) }}</th>
                                <th></th>
                                <th class="extra-remove"></th>
                            </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-pay" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Customer Payment Receive</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-dsr-payment-form" method="POST" action="{{ route('distribution.dsr_payment',['type'=>request('type')]) }}">
                        @csrf
                        <input type="hidden" id="order_id" name="order_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-has-error">
                                    <label for="order_no">Sale Order No. <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="order_no" name="order_no" class="form-control">
                                    <span id="order_no-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-has-error">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="date" name="date" value="{{ date('d-m-Y') }}" class="form-control date-picker">
                                    <span id="date-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-has-error">
                                    <label for="payment_mode">{{ request('type') ==  1 ? 'Receipt' : 'Payment' }} Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="payment_mode" class="form-control select2">
                                        <option value="">Select Payment Mode</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                        @endforeach
                                    </select>
                                    <span id="payment_mode-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-6" style="display: none" id="if_payment_bank_mode">
                                <div class="form-group form-group-has-error">
                                    <label for="cheque_no">Cheque No. <span class="text-danger">*</span></label>
                                    <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Enter Cheque No.">
                                    <span id="cheque_no-error" class="help-block error-message"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="text" readonly id="total" class="form-control" name="total">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <label for="payment">{{ request('type') == 1 ? 'Receive Amount' : 'Payment Amount' }} <span class="text-danger">*</span></label>
                                    <input type="number"  id="payment" class="form-control" name="payment">
                                    <span id="payment-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <input type="hidden" id="due_hidden" name="due_hidden">
                                    <label for="due">Due</label>
                                    <input type="text" readonly id="due" class="form-control" name="due">
                                    <span id="due-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="notes">Notes</label>
                                    <input type="text" id="notes" class="form-control" name="notes"
                                           placeholder="Enter Notes">
                                    <span id="notes-error" class="help-block error-message"></span>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" id="dsr-payment-btn" class="btn btn-primary">Save</button>
                </div>
            </div>

        </div>
    </div>

@endsection
@section('script')
    <script>

        $(function (){
            $('body').on('click', '.customer-pay', function () {
                let orderId = $(this).data('id');
                $("#order_no").val($(this).data('order_no'));
                $("#total").val($(this).data('total'));
                $("#due").val($(this).data('due'));
                $("#due_hidden").val($(this).data('due'));
                $("#order_id").val(orderId);
                $('#modal-pay').modal('show');
            })

            $('#payment_mode').change(function (){
                let paymentMode = $(this).val();
                var valuesArray = paymentMode.split('|'); // Split the selected value into an array
                var id = valuesArray[0];
                var mode = valuesArray[1];
                if (paymentMode != '' && mode == 1){
                    $("#if_payment_bank_mode").show();
                }else{
                    $("#if_payment_bank_mode").hide();
                }
            })
            $('#dsr-payment-btn').click(function() {
                preloaderToggle(true);
                // Create a FormData object
                var formData = new FormData(document.getElementById('modal-dsr-payment-form'));
                $.ajax({
                    type: 'POST',
                    url: $('#modal-dsr-payment-form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        preloaderToggle(false);
                        if (response.status){
                            ajaxSuccessMessage(response.message)
                            window.location.href = response.redirect_url;
                        }else{
                            console.log(response.message);
                            $(document).Toasts('create', {
                                icon: 'fas fa-envelope fa-lg',
                                class: 'bg-warning',
                                title: 'Error',
                                autohide: true,
                                delay: 2000,
                                body: response.message
                            })
                            // Play the notification sound
                            let notificationSound = document.getElementById('notification-error-audio');
                            if (notificationSound) {
                                notificationSound.play();
                            }
                        }
                    },
                    error: function(xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            $(document).Toasts('create', {
                                icon: 'fas fa-envelope fa-lg',
                                class: 'bg-warning',
                                title: 'Error',
                                autohide: true,
                                delay: 2000,
                                body: 'Please fill up validate required fields.'
                            })
                            // Play the notification sound
                            let notificationSound = document.getElementById('notification-error-audio');
                            if (notificationSound) {
                                notificationSound.play();
                            }
                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text('');
                            $('.form-group-has-error').removeClass('has-error');

                            // Update error messages for each field
                            $.each(errors, function(field, errorMessage) {
                                $('#'+field+'-error').text(errorMessage[0]);
                                $('#'+field+'-error').closest('.form-group-has-error').addClass('has-error')
                            });
                        }
                    }
                });
            });

            $('body').on('keyup', '#payment', function() {
                calculate();
            });
        })
        function calculate() {

            let total = parseFloat($('#total').val());
            total = (isNaN(total) || total < 0) ? 0 : total;

            let due_hidden = parseFloat($('#due_hidden').val());
            due_hidden = (isNaN(due_hidden) || due_hidden < 0) ? 0 : due_hidden;

            let payment = parseFloat($('#payment').val());
            payment = (isNaN(payment) || payment < 0) ? 0 : payment;

            $("#due").val(Math.ceil(due_hidden - payment).toFixed(2));
        }

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
