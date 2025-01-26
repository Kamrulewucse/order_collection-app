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
    <form action="{{ route('distribution.customer_sale_entry',['distributionOrder'=>$distributionOrder->id,'type'=>request('type')]) }}" method="post">
        @csrf
        <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                   <div class="card-title">Customer Sales Entry Add</div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="select_customer">Customer <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="select_customer" name="select_customer">
                                    <option value="">Search Name of Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="select_product">Product <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="select_product" name="select_product">
                                    <option value="">Search Name of Product</option>
                                    @foreach($products as $product)
                                        <option  value="{{ $product->id }}">Brand: {{ $product->brand->name ?? '' }} - {{ $product->name }} - Code:{{ $product->code }} - {{ $product->unit->name ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="add_quantity">Sale Qty <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" id="add_quantity" placeholder="Qty">
                                <span id="stock_quantity"></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="add_unit_price">Unit Price <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" id="add_unit_price" placeholder="Unit Price">
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
                                    <th class="text-center">Item Details</th>
                                    <th class="text-center" width="12%">Sale Qty <span class="text-danger">*</span></th>
                                    <th class="text-center" width="15%">Unit Price <span class="text-danger">*</span></th>
                                    <th class="text-center" width="15%">Total Price</th>
                                    <th class="text-center" width="5%"></th>
                                </tr>
                                </thead>
                                <tbody id="product-container">
                                @if (old('product_id') != null && sizeof(old('product_id')) > 0)
                                    @foreach(old('product_id') as $key => $item)
                                        <tr class="product-item">
                                            <td class="text-center">
                                                <span class="product_sl">{{ ++$key }}</span>
                                            </td>
                                            <td class="text-left {{ $errors->has('customer_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                <span class="customer_name">{{ old('customer_name_val.'.$loop->index) }}</span>
                                                <input type="hidden" name="customer_name_val[]" value="{{ old('customer_name_val.'.$loop->index) }}" class="customer_name_val">
                                                <input type="hidden" name="customer_id[]" value="{{ old('customer_id.'.$loop->index) }}" class="customer_id">
                                            </td>
                                            <td class="text-left {{ $errors->has('product_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                <span class="product_name">{{ old('product_name_val.'.$loop->index) }}</span>
                                                <input type="hidden" name="product_name_val[]" value="{{ old('product_name_val.'.$loop->index) }}" class="product_name_val">
                                                <input type="hidden" name="product_id[]" value="{{ old('product_id.'.$loop->index) }}" class="product_id">
                                                <input type="hidden" name="customer_product_id[]" value="{{ old('customer_product_id.'.$loop->index) }}" class="customer_product_id">
                                            </td>
                                            <td class="text-right">
                                                <div class="form-group mb-0  {{ $errors->has('product_qty.'.$loop->index) ? 'has-error' :'' }}">
                                                    <input type="number" step="any" value="{{ old('product_qty.'.$loop->index) }}" class="form-control text-right product_qty" name="product_qty[]">
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="form-group mb-0  {{ $errors->has('product_unit_price.'.$loop->index) ? 'has-error' :'' }}">
                                                    <input type="number" step="any" value="{{ old('product_unit_price.'.$loop->index) }}" class="form-control text-right product_unit_price" name="product_unit_price[]">
                                                </div>
                                            </td>
                                            <td class="text-right total-purchase-cost"></td>
                                            <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                                <tfoot>
                                <tr style="{{ (old('product_id') != null && sizeof(old('product_id'))) > 0 ? 'display: revert' : 'display: none' }}" id="footer_area">
                                    <th colspan="3" class="text-right">Total</th>
                                    <th  class="text-right" id="total_quantity"></th>
                                    <th  class="text-right" id="total_unit_price"></th>
                                    <th  class="text-right" id="total_purchase_price"></th>
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


    <template id="template-product">
        <tr class="product-item">
            <td class="text-center">
                <span class="product_sl"></span>
            </td>
            <td class="text-left">
                <span class="customer_name"></span>
                <input type="hidden" name="customer_name_val[]" class="customer_name_val">
                <input type="hidden" name="customer_id[]" class="customer_id">
            </td>
            <td class="text-left">
                <span class="product_name"></span>
                <input type="hidden" name="product_name_val[]" class="product_name_val">
                <input type="hidden" name="product_id[]" class="product_id">
                <input type="hidden" name="customer_product_id[]" class="customer_product_id">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_qty" name="product_qty[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]">
                </div>
            </td>
            <td class="text-right total-purchase-cost"></td>
            <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
        </tr>
    </template>
@endsection
@section('script')
    <script>
        $(function (){
            calculate();
            var productIds = [];

            $( ".customer_product_id" ).each(function( index ) {
                if ($(this).val() != '') {
                    productIds.push($(this).val());
                }
            });
            $('body').on('keypress', '#add_quantity,#add_unit_price', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectCustomer = $('#select_customer').val();
                let selectProduct = $('#select_product').val();

                let selectCustomerProduct = selectCustomer+'-'+selectProduct;

                let selectCustomerName = $("#select_customer option:selected").text();
                let selectProductName = $("#select_product option:selected").text();
                let addQuantity = $('#add_quantity').val();
                let addUnitPrice = $('#add_unit_price').val();
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
                if (selectProduct == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select product !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addQuantity == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type product quantity !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addUnitPrice == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type product unit price !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if($.inArray(selectCustomerProduct, productIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectCustomerName+' - '+selectProductName+ ' already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectCustomer != '' && selectProduct != '' && addQuantity != '' && addUnitPrice != '') {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.hide();

                    item.closest('tr').find('.customer_name').text($("#select_customer option:selected").text());
                    item.closest('tr').find('.customer_name_val').val($("#select_customer option:selected").text());
                    item.closest('tr').find('.customer_id').val($("#select_customer option:selected").val());

                    item.closest('tr').find('.product_name').text($("#select_product option:selected").text());
                    item.closest('tr').find('.product_name_val').val($("#select_product option:selected").text());
                    item.closest('tr').find('.product_id').val($("#select_product option:selected").val());
                    item.closest('tr').find('.customer_product_id').val(selectCustomerProduct);
                    item.closest('tr').find('.product_qty').val(addQuantity);
                    item.closest('tr').find('.product_unit_price').val(addUnitPrice);
                    productIds.push(selectCustomerProduct);
                    item.show();
                    calculate();
                    $('#add_quantity').val(' ');
                    $('#add_unit_price').val(' ');
                }
                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var customer_product_id = $(this).closest('tr').find('.customer_product_id').val();

                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.product-item').remove();
                calculate();
                productIds = $.grep(productIds, function(value) {
                    return value != customer_product_id;
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
            var total_quantity = 0;
            var total_selling_price = 0;
            var total_purchase_price = 0;
            $('.product-item').each(function(i, obj) {

                let product_qty = parseFloat($('.product_qty:eq('+i+')').val());
                product_qty = (isNaN(product_qty) || product_qty < 0) ? 0 : product_qty;

                let product_unit_price = parseFloat($('.product_unit_price:eq('+i+')').val());
                product_unit_price = (isNaN(product_unit_price) || product_unit_price < 0) ? 0 : product_unit_price;

                $('.total-purchase-cost:eq('+i+')').text((product_qty * product_unit_price).toFixed(2) );
                total_quantity += product_qty;
                total_purchase_price += product_qty * product_unit_price;
            });

            $('#total_quantity').text(total_quantity);
            $('#total_purchase_price').text(total_purchase_price.toFixed(2));

            if (rows.length > 0){
                $("#footer_area").show();
            }else{
                $("#footer_area").hide();
            }
        }
    </script>
@endsection
