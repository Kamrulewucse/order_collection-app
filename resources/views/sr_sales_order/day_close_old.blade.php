@extends('layouts.app')
@section('title',$pageTitle)
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            padding: 2px;
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
                    @if($distributionOrder->hold_release != 1)
                        @if($distributionOrder->close_status == 1)
                            <a href="{{ route('sr-sales.day_close',['distributionOrder'=>$distributionOrder->id,'close_status'=>1,'type'=>1]) }}" class="btn btn-primary bg-gradient-primary btn-sm">Edit <i class="fa fa-edit"></i></a>
                        @else
                            <a href="{{ route('sr-sales.day_close',['distributionOrder'=>$distributionOrder->id,'type'=>1]) }}" class="btn btn-primary bg-gradient-primary btn-sm">Edit Close <i class="fa fa-edit"></i></a>
                        @endif
                            <a  data-id="{{ $distributionOrder->id }}" role="button" class="btn btn-warning bg-gradient-warning btn-sm day-close"><i class="fa fa-info-circle"></i> Hold Release</a>
                    @endif
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-primary bg-gradient-primary btn-sm">Print <i class="fa fa-print"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        <form action="{{ route('sr-sales.day_close',['distributionOrder'=>$distributionOrder->id,'type'=>request('type')]) }}" method="post">
                            @csrf
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
                                @if($distributionOrder->close_status == 0)
                                    <tr>
                                        <th colspan="11">Distribution Adjust</th>
                                    </tr>
                                @endif
                                <tr>
                                    <th class="text-center" width="3%">S/L</th>
                                    <th class="text-center">Product</th>
                                    <th class="text-center" width="10%">Unit Price</th>
                                    <th class="text-center" width="12%">Delivery Qty</th>
                                    <th class="text-center" width="10%">Return Qty</th>
                                    <th class="text-center" width="14%">Final Sales Qty</th>
                                    <th class="text-center" width="10%">Total Price</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $totalPurchasePrice = 0;
                                @endphp
                                @foreach($distributionOrder->distributionOrderItems as $item)
                                    <tr class="product-item">
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-right">{{ number_format($item->selling_unit_price,2) }}</td>
                                        <td class="text-right">{{ number_format($item->distribute_quantity) }}</td>
                                        <td class="text-right">
                                            <input type="hidden" name="distribution_order_item_id[]" value="{{ $item->id }}">
                                            <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                            @if($distributionOrder->close_status == 0)
                                                <input type="number" name="return_quantity[]" step="any" class="form-control text-right text-bold return_quantity" value="{{ $item->return_quantity }}">
                                            @else
                                                <input type="hidden" name="return_quantity[]" step="any" class="form-control text-right text-bold return_quantity" value="{{ $item->return_quantity }}">
                                               {{ number_format($item->return_quantity) }}
                                            @endif

                                            <input type="hidden" name="distribute_quantity[]"  class="distribute_quantity" value="{{ $item->distribute_quantity }}">
                                            <input type="hidden" name="selling_unit_price[]"  class="selling_unit_price" value="{{ $item->selling_unit_price }}">
                                        </td>
                                        <td class="text-right total-sale-Qty">{{ number_format($item->sale_quantity) }}</td>
                                        <td class="text-right total-sale">{{ number_format($item->sale_quantity * $item->selling_unit_price,2) }}</td>
                                    </tr>
                                    @php
                                        $totalPurchasePrice += $item->sale_quantity * $item->selling_unit_price;
                                    @endphp
                                @endforeach
                                @if($distributionOrder->close_status == 0)
                                <tr>
                                    <th class="text-right" colspan="3">Total</th>
                                    <th class="text-right">{{ number_format($distributionOrder->distributionOrderItems->sum('distribute_quantity')) }}</th>
                                    <th class="text-right" id="total_return_quantity">{{ number_format($distributionOrder->distributionOrderItems->sum('return_quantity')) }}</th>
                                    <th class="text-right" id="total_selling_quantity">{{ number_format($distributionOrder->distributionOrderItems->sum('sale_quantity')) }}</th>
                                    <th class="text-right" id="total_sale_price">{{ number_format($totalPurchasePrice,2) }}</th>
                                </tr>
                                @endif
                                </tbody>
                                    <tfoot>
                                    @if($distributionOrder->close_status == 0)
                                        <tr class="extra-remove">
                                        <th class="text-right" colspan="6"><button class="btn btn-primary bg-gradient-primary">Adjust Update</button></th>
                                        <th></th>
                                    </tr>
                                    @else
                                        <tr>
                                            <th class="text-right" colspan="6">Total</th>
                                            <th class="text-right">{{ number_format($distributionOrder->saleOrders->sum('total'),2) }}</th>
                                        </tr>
                                        <tr>
                                            <th class="text-right" colspan="6">Paid</th>
                                            <th class="text-right">{{ number_format($distributionOrder->saleOrders->sum('paid'),2) }}</th>
                                        </tr>
                                        <tr>
                                            <th class="text-right" colspan="6">Due</th>
                                            <th class="text-right">{{ number_format($distributionOrder->saleOrders->sum('due'),2) }}</th>
                                        </tr>
                                    @endif
                                    </tfoot>
                            </table>
                        </form>
                        <form action="{{ route('sr-sales.customer_sale_entry',['distributionOrder'=>$distributionOrder->id,'type'=>request('type')]) }}" method="post">
                            @csrf

                            <table id="table" class="table table-bordered mt-5">
                                    <thead>
                                    @if($distributionOrder->close_status == 0)
                                        <tr>
                                            <th colspan="6">Customer Sales Bill</th>
                                        </tr>
                                        <tr class="extra-remove">
                                            <th colspan="6">
                                                <input type="hidden" id="remaining" value="">
                                                <div class="row">

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="select_customer">Customer <span class="text-danger">*</span></label>
                                                            <select class="form-control select2" id="select_customer" name="select_customer">
                                                                <option value="">Search Name of Customer</option>
                                                                @foreach($customers as $customer)
                                                                    <option {{ old('select_customer') }} value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label for="add_total">Total Amount<span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" id="add_total_amount" placeholder="Total Amount">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label for="add_paid">Paid Amount <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control" id="add_paid_amount" placeholder="Paid Amount">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <button type="button" style="margin-top: 31px;" id="add_new_btn" class="btn btn-primary bg-gradient-primary btn-sm btn-block"><i class="fa fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th class="text-center" width="4%">S/L</th>
                                        <th class="text-center">Customer</th>
                                        <th class="text-center" width="15%">Total Amount</th>
                                        <th class="text-center" width="15%">Paid Amount</th>
                                        <th class="text-center" width="15%">Due Amount</th>
                                        @if($distributionOrder->close_status == 0)
                                        <th class="text-center extra-remove" width="5%"></th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody id="customer-container">
                                    @if (old('customer_id') != null && sizeof(old('customer_id')) > 0)
                                        @foreach(old('customer_id') as $key => $item)
                                            <tr class="customer-item">
                                                <td class="text-center">
                                                    <span class="customer-sl">{{ ++$key }}</span>
                                                </td>
                                                <td class="text-left {{ $errors->has('customer_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                    <span class="customer_name">{{ old('customer_name_val.'.$loop->index) }}</span>
                                                    <input type="hidden" name="customer_name_val[]" value="{{ old('customer_name_val.'.$loop->index) }}" class="customer_name_val">
                                                    <input type="hidden" name="customer_id[]" value="{{ old('customer_id.'.$loop->index) }}" class="customer_id">

                                                </td>
                                                <td class="text-right">
                                                    <div class="form-group mb-0  {{ $errors->has('total_amount.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any" value="{{ old('total_amount.'.$loop->index) }}" class="form-control text-right total_amount" name="total_amount[]">
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="form-group mb-0  {{ $errors->has('paid_amount.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any" value="{{ old('paid_amount.'.$loop->index) }}" class="form-control text-right paid_amount" name="paid_amount[]">
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="form-group mb-0  {{ $errors->has('due_amount.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" readonly step="any" value="{{ old('due_amount.'.$loop->index) }}" class="form-control text-right due_amount" name="due_amount[]">
                                                    </div>
                                                </td>
                                                @if($distributionOrder->close_status == 0)
                                                <td class="text-center extra-remove">
                                                        <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach($distributionOrder->saleOrders as $saleOrder)
                                            <tr class="customer-item">
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="customer_name">{{ $saleOrder->customer->name ?? ''  }}</span>
                                                    <input type="hidden" name="customer_name_val[]" value="{{ $saleOrder->customer->name ?? '' }}" class="customer_name_val">
                                                    <input type="hidden" name="customer_id[]" value="{{ $saleOrder->customer_id }}" class="customer_id">
                                                </td>
                                                <td class="text-right">
                                                    @if($distributionOrder->close_status == 0)
                                                    <input type="number" name="total_amount[]" step="any" class="form-control text-right text-bold total_amount" value="{{ $saleOrder->total }}">
                                                    @else
                                                        {{ number_format($saleOrder->total,2) }}
                                                        <input type="hidden" name="total_amount[]" step="any" class="form-control text-right text-bold total_amount" value="{{ $saleOrder->total }}">
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if($distributionOrder->close_status == 0)
                                                    <input type="hidden" name="sale_order_id[]" value="{{ $saleOrder->id }}">
                                                    <input type="number" name="paid_amount[]" step="any" class="form-control text-right text-bold paid_amount" value="{{ $saleOrder->paid }}">
                                                    @else
                                                        <input type="hidden" name="paid_amount[]" step="any" class="form-control text-right text-bold paid_amount" value="{{ $saleOrder->paid }}">
                                                        {{ number_format($saleOrder->paid,2) }}
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if($distributionOrder->close_status == 0)
                                                    <input type="number" readonly name="due_amount[]" step="any" class="form-control text-right text-bold due_amount" value="{{ $saleOrder->due }}">
                                                    @else
                                                        <input type="hidden" readonly name="due_amount[]" step="any" class="form-control text-right text-bold due_amount" value="{{ $saleOrder->due }}">
                                                        {{ number_format($saleOrder->due,2) }}
                                                    @endif
                                                </td>
                                                @if($distributionOrder->close_status == 0)
                                                <td class="text-center extra-remove">
                                                        <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif

                                    <tr>
                                        <th class="text-right" colspan="2">Remaining Amount: <span id="remaining_amount">{{ $distributionOrder->total }}</span></th>
                                        <th class="text-right"  id="total_total_amount">{{ number_format($distributionOrder->saleOrders->sum('total'),2) }}</th>
                                        <th class="text-right" id="total_paid">{{ number_format($distributionOrder->saleOrders->sum('paid'),2) }}</th>
                                        <th class="text-right" id="total_due">{{ number_format($distributionOrder->saleOrders->sum('due'),2) }}</th>
                                        @if($distributionOrder->close_status == 0)
                                            <th class="extra-remove"></th>
                                        @endif
                                    </tr>
                                    </tbody>
                                    @if($distributionOrder->close_status == 0)
                                        <tfoot>
                                        <tr class="extra-remove">
                                            <th colspan="3" class="text-right">Receipt Mode</th>
                                            <th colspan="2" class="text-left">
                                                <div class="form-group {{ $errors->has('payment_mode') ? 'has-error' :'' }}">
                                                    {{-- <select name="payment_mode" style="overflow: hidden;width: max-content !important;" id="payment_mode" class="select2 form-control text-left">
                                                        <option value="">Select Receipt Mode</option>
                                                        @foreach($paymentModes as $paymentMode)
                                                            <option {{ old('payment_mode') == ($paymentMode->id.'|'.$paymentMode->payment_mode) ? 'selected' : '' }} value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                                        @endforeach
                                                    </select> --}}
                                                    <input type="text" value="{{$paymentMode->name}}- {{ $paymentMode->code }}" class="form-control" readonly>
                                                    <input type="hidden" name="payment_mode" value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}" class="form-control">
                                                    @error('payment_mode')
                                                    <span class="help-block text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </th>
                                            <th></th>
                                        </tr>
                                        <tr class="extra-remove">
                                            <th class="text-right" colspan="5"><button class="btn btn-primary bg-gradient-primary">Paid Update</button></th>
                                        </tr>
                                        </tfoot>
                                    @endif
                                </table>
                        </form>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    <template id="template-product">
        <tr class="customer-item">
            <td class="text-center">
                <span class="customer-sl"></span>
            </td>
            <td class="text-left">
                <span class="customer_name"></span>
                <input type="hidden" name="customer_name_val[]" class="customer_name_val">
                <input type="hidden" name="customer_id[]" class="customer_id">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right total_amount" name="total_amount[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right paid_amount" name="paid_amount[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" readonly step="any" class="form-control text-right due_amount" name="due_amount[]">
                </div>
            </td>
            <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
        </tr>
    </template>
@endsection
@section('script')
    <script>
        calculate();
        $(function (){
            calculate();

            $('body').on('click', '.day-close', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Hold release it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "POST",
                            url: "{{ route('sr-sales.hold_release_post', ['distributionOrder' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE',id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Released !',
                                    response.message,
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message,
                                });
                            }
                        });

                    }
                })

            });

            var customerIds = [];

            $( ".customer_id" ).each(function( index ) {
                if ($(this).val() != '') {
                    customerIds.push($(this).val());
                }
            });
            $('body').on('keypress', '#add_total_amount,#add_paid_amount', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectCustomer = $('#select_customer').val();
                let selectCustomerId= selectCustomer;
                let selectCustomerName = $("#select_customer option:selected").text();

                let addTotalAmount = parseFloat($('#add_total_amount').val());
                addTotalAmount = (isNaN(addTotalAmount) || addTotalAmount < 0) ? 0 : addTotalAmount;

                let addPaidAmount = parseFloat($('#add_paid_amount').val());
                addPaidAmount = (isNaN(addPaidAmount) || addPaidAmount < 0) ? 0 : addPaidAmount;

                let remaining_amount = parseFloat($('#remaining').val());
                remaining_amount = (isNaN(remaining_amount) || remaining_amount < 0) ? 0 : remaining_amount;

                if(remaining_amount < addTotalAmount){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Remaining amount ('+remaining_amount+') exceeds total amount ('+addTotalAmount+')',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectCustomer == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select customer !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addTotalAmount == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, enter total amount !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if($.inArray(selectCustomerId, customerIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectCustomerName+ ' already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectCustomer != '' && addTotalAmount != '') {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#customer-container').prepend(itemHtml);
                    var item = $('.customer-item').first();
                    item.hide();

                    item.closest('tr').find('.customer_name').text($("#select_customer option:selected").text());
                    item.closest('tr').find('.customer_name_val').val($("#select_customer option:selected").text());
                    item.closest('tr').find('.customer_id').val($("#select_customer option:selected").val());
                    item.closest('tr').find('.total_amount').val(addTotalAmount);
                    item.closest('tr').find('.paid_amount').val(addPaidAmount);
                    item.closest('tr').find('.due_amount').val(addTotalAmount - addPaidAmount);
                    customerIds.push(selectCustomerId);
                    item.show();
                    calculate();
                    $('#select_customer').val(null).trigger('change');
                    $('#add_total_amount').val(' ');
                    $('#add_paid_amount').val(' ');
                }
                return false; // prevent the button click from happening
            });
            $('body').on('click', '.btn-remove', function () {
                var customer_id = $(this).closest('tr').find('.customer_id').val();
                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.customer-item').remove();
                calculate();
                customerIds = $.grep(customerIds, function(value) {
                    return value != customer_id;
                });
            });
            $('body').on('keyup', 'input[type="number"]', function() {
                calculate();
            });
            $('body').on('change', 'input[type="number"]', function() {
                calculate();
            });
        })
        function calculate(){
            var rows = $("#product-container .customer-item");
            var total_selling_quantity = 0;
            var total_return_quantity = 0;
            var total_sale_price = 0;
            $('.product-item').each(function(i, obj) {
                let return_quantity = parseFloat($('.return_quantity:eq('+i+')').val());
                return_quantity = (isNaN(return_quantity) || return_quantity < 0) ? 0 : return_quantity;

                let distribute_quantity = parseFloat($('.distribute_quantity:eq('+i+')').val());
                distribute_quantity = (isNaN(distribute_quantity) || distribute_quantity < 0) ? 0 : distribute_quantity;


                let selling_unit_price = parseFloat($('.selling_unit_price:eq('+i+')').val());
                selling_unit_price = (isNaN(selling_unit_price) || selling_unit_price < 0) ? 0 : selling_unit_price;

                let selling_quantity = distribute_quantity - return_quantity;

                $('.total-sale-Qty:eq('+i+')').text((selling_quantity).toFixed(2));
                $('.total-sale:eq('+i+')').text((selling_quantity * selling_unit_price).toFixed(2));

                total_return_quantity += return_quantity;
                total_selling_quantity += selling_quantity;

                total_sale_price += selling_quantity * selling_unit_price;
            });

            $('#total_selling_quantity').text(total_selling_quantity);
            $('#total_return_quantity').text(total_return_quantity);
            $('#total_sale_price').text(total_sale_price.toFixed(2));


            var productSl = 1;

            // Select all the table rows with the class .product-item
            var customerRows = $("#customer-container .customer-item");
            // Iterate over each row and update the customer-sl value
            customerRows.each(function() {
                // Find the .customer-sl element within the current row
                var productSlElement = $(this).find('.customer-sl');
                // Update the text of the .customer-sl element with the customer-sl value
                productSlElement.text(productSl);
                // Increment the customer-sl value for the next iteration
                productSl++;
            });
            var total_total_amount = 0;
            var total_paid = 0;
            var total_due = 0;
            $('.customer-item').each(function(i, obj) {

                let total_amount = parseFloat($('.total_amount:eq('+i+')').val());
                total_amount = (isNaN(total_amount) || total_amount < 0) ? 0 : total_amount;

                let paid_amount = parseFloat($('.paid_amount:eq('+i+')').val());
                paid_amount = (isNaN(paid_amount) || paid_amount < 0) ? 0 : paid_amount;

                $('.due_amount:eq('+i+')').val((total_amount - paid_amount).toFixed(2) );
                total_total_amount += total_amount;
                total_paid += paid_amount;
                total_due += total_amount - paid_amount;
            });
            let remaining_amount = total_sale_price - total_total_amount;

            $('#total_total_amount').text(total_total_amount.toFixed(2));
            $('#total_paid').text(total_paid.toFixed(2));
            $('#total_due').text(total_due.toFixed(2));
            $('#remaining_amount').text(remaining_amount.toFixed(2));
            $('#remaining').val(remaining_amount.toFixed(2));

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
