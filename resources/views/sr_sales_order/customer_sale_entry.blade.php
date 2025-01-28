@extends('layouts.app')
@section('title',$pageTitle)

@section('content')
    <form action="{{ route('sr-sales.customer_sale_entry',['distributionOrder'=>$distributionOrder->id,'type'=>request('type')]) }}" method="post">
        @csrf
        <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                   <div class="card-title">Customer Sales Bill Add</div>
                </div>
                <div class="card-header">
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
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center" width="5%">S/L</th>
                                    <th class="text-center">Customer</th>
                                    <th class="text-center" width="15%">Total Amount <span class="text-danger">*</span></th>
                                    <th class="text-center" width="15%">Paid Amount <span class="text-danger">*</span></th>
                                    <th class="text-center" width="15%">Due Amount <span class="text-danger">*</span></th>
                                    <th class="text-center" width="5%"></th>
                                </tr>
                                </thead>
                                <tbody id="customer-container">
                                @if (old('customer_id') != null && sizeof(old('customer_id')) > 0)
                                    @foreach(old('customer_id') as $key => $item)
                                        <tr class="product-item">
                                            <td class="text-center">
                                                <span class="product_sl">{{ ++$key }}</span>
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
                                            <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                                <tfoot>
                                <tr class="footer_area" style="{{ (old('customer_id') != null && sizeof(old('customer_id'))) > 0 ? 'display: revert' : 'display: none' }}">
                                    <th colspan="2" class="text-right">Total</th>
                                    <th  class="text-right" id="total_total_amount"></th>
                                    <th  class="text-right" id="total_paid"></th>
                                    <th  class="text-right" id="total_due"></th>
                                    <th></th>
                                </tr>
                                <tr class="footer_area" style="{{ (old('customer_id') != null && sizeof(old('customer_id'))) > 0 ? 'display: revert' : 'display: none' }}">
                                    <th colspan="2" class="text-right">Receipt Mode</th>
                                     <th colspan="3" class="text-right">
                                            <div class="form-group {{ $errors->has('payment_mode') ? 'has-error' :'' }}">
                                                <select name="payment_mode" id="payment_mode" class="form-control select2">
                                                    <option value="">Select Receipt Mode</option>
                                                    @foreach($paymentModes as $paymentMode)
                                                        <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                                    @endforeach
                                                </select>
                                                @error('payment_mode')
                                                <span class="help-block text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                </div>
            </div>
        </div>
    </div>
    </form>


    <template id="customer-product">
        <tr class="product-item">
            <td class="text-center">
                <span class="product_sl"></span>
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

        $(function (){

            calculate();
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
                    var html = $('#customer-product').html();
                    var itemHtml = $(html);
                    $('#customer-container').prepend(itemHtml);
                    var item = $('.product-item').first();
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
                    $('#add_total_amount').val(' ');
                    $('#add_paid_amount').val(' ');
                }
                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var customer_id = $(this).closest('tr').find('.customer_id').val();
                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.product-item').remove();
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
            // Assuming you want to start the product_sl value from 1
            var productSl = 1;

            // Select all the table rows with the class .product-item
            var rows = $("#product-container .product-item");
            // Iterate over each row and update the product_sl value
            rows.each(function() {
                // Find the .product_sl element within the current row
                var productSlElement = $(this).find('.product_sl');
                // Update the text of the .product_sl element with the product_sl value
                productSlElement.text(productSl);
                // Increment the product_sl value for the next iteration
                productSl++;
            });
            var total_total_amount = 0;
            var total_paid = 0;
            var total_due = 0;
            $('.product-item').each(function(i, obj) {

                let total_amount = parseFloat($('.total_amount:eq('+i+')').val());
                total_amount = (isNaN(total_amount) || total_amount < 0) ? 0 : total_amount;

                let paid_amount = parseFloat($('.paid_amount:eq('+i+')').val());
                paid_amount = (isNaN(paid_amount) || paid_amount < 0) ? 0 : paid_amount;

                $('.due_amount:eq('+i+')').val((total_amount - paid_amount).toFixed(2) );
                total_total_amount += total_amount;
                total_paid += paid_amount;
                total_due += total_amount - paid_amount;
            });

            $('#total_total_amount').text(total_total_amount.toFixed(2));
            $('#total_paid').text(total_paid.toFixed(2));
            $('#total_due').text(total_due.toFixed(2));

            if (rows.length > 0){
                $(".footer_area").show();
            }else{
                $(".footer_area").hide();
            }
        }
    </script>
@endsection
