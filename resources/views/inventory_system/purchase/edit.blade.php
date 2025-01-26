@extends('layouts.app')
@section('title','Purchase Edit')
@section('style')
    <style>
        .table td, .table th {
            padding: 6px;
            vertical-align: middle;
        }

    </style>
@endsection
@section('content')

    <!-- form start -->
    <form enctype="multipart/form-data" action="{{ route('purchase.update',['purchase'=>$purchase->id]) }}" class="form-horizontal" method="post">
        @csrf
        @method('PUT')
        <div class="row">
        <!-- left column -->
        <div class="col-md-9">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Product Information </h3>
                </div>
                <div class="card-header">
                    <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('company') ? 'has-error' :'' }}">
                                    <label for="company">Company <span
                                            class="text-danger">*</span></label>
                                    <select  class="form-control select2" disabled id="company" name="company">
                                        <option value="">Search Name of Company</option>
                                        @foreach($clients as $supplier)
                                            <option {{ request('company',$purchase->supplier_id) == $supplier->id ? 'selected' : ''  }} value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="company" id="" value="{{$purchase->supplier_id}}">
                                    @error('company')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                     </div>
                </div>
                <div class="card-header">
                    <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="select_product">Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_product" name="select_product">
                                        <option value="">Search Name of Product</option>
                                        @foreach($products as $product)
                                            <option {{ old('product') == $product->id ? 'selected' : ''  }} value="{{ $product->id }}">Brand: {{ $product->brand->name ?? '' }} - {{ $product->name }} - Code:{{ $product->code }} - {{ $product->unit->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_quantity">Qty <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_quantity" placeholder="Qty">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_unit_price">Unit Price <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_unit_price" placeholder="Unit Price">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="add_selling_price">Selling Unit Price <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_selling_price" placeholder="Selling Unit Price">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button type="button" style="margin-top: 31px;" id="add_new_btn" class="btn btn-primary bg-gradient-primary btn-sm btn-block"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                </div>

                <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                   <thead>
                                       <tr>
                                           <th class="text-center" width="5%">S/L</th>
                                           <th class="text-center">Item Details</th>
                                           <th class="text-center" width="10%">Qty</th>
                                           <th class="text-center" width="15%">Unit Price</th>
                                           <th class="text-center" width="18%">Selling Unit Price</th>
                                           <th class="text-center" width="20%">Total Purchase Price</th>
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
                                                    <td class="text-left {{ $errors->has('product_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <span class="product_name">{{ old('product_name_val.'.$loop->index) }}</span>
                                                        <input type="hidden" value="{{ old('purchase_item_id.'.$loop->index) }}" name="purchase_item_id[]">
                                                        <input type="hidden" name="product_name_val[]" value="{{ old('product_name_val.'.$loop->index) }}" class="product_name_val">
                                                        <input type="hidden" name="product_id[]" value="{{ old('product_id.'.$loop->index) }}" class="product_id">
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
                                                    <td class="text-right">
                                                        <div class="form-group mb-0  {{ $errors->has('product_selling_unit_price.'.$loop->index) ? 'has-error' :'' }}">
                                                            <input type="number" step="any" value="{{ old('product_selling_unit_price.'.$loop->index) }}" class="form-control text-right product_selling_unit_price" name="product_selling_unit_price[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-right total-purchase-cost"></td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
                                                </tr>
                                            @endforeach
                                            @else
                                            @foreach($purchase->purchaseItems as $purchaseItem)
                                                <tr class="product-item">
                                                    <td class="text-center">
                                                        <span class="product_sl"></span>
                                                    </td>
                                                    <td class="text-left">
                                                        <span class="product_name">{{ $purchaseItem->product->name ?? '' }}</span>
                                                        <input type="hidden" value="{{ $purchaseItem->id }}" name="purchase_item_id[]">
                                                        <input type="hidden" value="{{ $purchaseItem->product->name ?? '' }}" name="product_name_val[]" class="product_name_val">
                                                        <input type="hidden" value="{{ $purchaseItem->product_id }}" name="product_id[]" class="product_id">
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0">
                                                            <input type="number" value="{{ $purchaseItem->quantity }}" step="any" class="form-control text-right product_qty" name="product_qty[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0">
                                                            <input type="number" value="{{ $purchaseItem->product_unit_price }}" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0">
                                                            <input type="number" value="{{ $purchaseItem->product_selling_unit_price }}" step="any" class="form-control text-right product_selling_unit_price" name="product_selling_unit_price[]">
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
                                            <th colspan="2" class="text-right">Total</th>
                                            <th  class="text-right" id="total_quantity"></th>
                                            <th  class="text-right" id="total_unit_price"></th>
                                            <th  class="text-right" id="total_selling_unit_price"></th>
                                            <th  class="text-right" id="total_purchase_price"></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('product.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->

            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-3">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Other Information</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                <label for="date">Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" value="{{ old('date',\Carbon\Carbon::parse($purchase->date)->format('d-m-Y')) }}" name="date" id="date" class="form-control date-picker" placeholder="Enter date">
                                @error('date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                <label for="notes">Notes</label>
                                <input type="text" value="{{ old('notes',$purchase->notes) }}" name="notes" id="notes" class="form-control" placeholder="Enter Notes">
                                @error('notes')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer"></div>
                <!-- /.card-footer -->
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
    </form>

    <template id="template-product">
        <tr class="product-item">
            <td class="text-center">
                <span class="product_sl"></span>
            </td>
            <td class="text-left">
              <span class="product_name"></span>
                <input type="hidden" name="product_name_val[]" class="product_name_val">
                <input type="hidden" name="product_id[]" class="product_id">
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
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_selling_unit_price" name="product_selling_unit_price[]">
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
            $('#company').on('change', function() {
                // Get the selected company ID
                var companyId = $(this).val();

                // Construct the new URL
                var currentUrl = window.location.href.split('?')[0]; // Get the base URL without query parameters
                var newUrl = currentUrl + '?company=' + companyId;

                // Update the browser's URL and reload the page
                window.location.href = newUrl;
            });


            calculate();
            var productIds = [];

            $( ".product_id" ).each(function( index ) {
                if ($(this).val() != '') {
                    productIds.push($(this).val());
                }
            });
            $('body').on('keypress', '#add_quantity,#add_unit_price,#add_selling_price', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });
            $('body').on('change', '#select_product', function (e) {
                let selectProductId = $(this).val();
                $('#add_unit_price').val(' ');
                if (selectProductId != ''){
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_details') }}",
                        data: {id : selectProductId }
                    }).done(function (response) {
                        $('#add_unit_price').val(response.purchase_price);
                        $('#add_selling_price').val(response.selling_price);
                    });
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectProduct = $('#select_product').val();
                let selectProductName = $("#select_product option:selected").text();
                let addQuantity = $('#add_quantity').val();
                let addUnitPrice = $('#add_unit_price').val();
                let addSellingUnitPrice = $('#add_selling_price').val();

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
                if (addSellingUnitPrice == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type product selling unit price !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if($.inArray(selectProduct, productIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectProductName+ ' already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectProduct != '' && addQuantity != '' && addUnitPrice != '' && addSellingUnitPrice != '') {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.hide();
                    item.closest('tr').find('.product_name').text($("#select_product option:selected").text());
                    item.closest('tr').find('.product_name_val').val($("#select_product option:selected").text());
                    item.closest('tr').find('.product_id').val($("#select_product option:selected").val());
                    item.closest('tr').find('.product_qty').val(addQuantity);
                    item.closest('tr').find('.product_unit_price').val(addUnitPrice);
                    item.closest('tr').find('.product_selling_unit_price').val(addSellingUnitPrice);
                    productIds.push(selectProduct);
                    item.show();
                    calculate();
                    $('#select_product').val(null).trigger('change');
                    $('#add_quantity').val('');
                    $('#add_unit_price').val('');
                    $('#add_selling_price').val('');
                }
                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var product_id = $(this).closest('tr').find('.product_id').val();

                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.product-item').remove();
                calculate();
                productIds = $.grep(productIds, function(value) {
                    return value != product_id;
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

                let product_selling_unit_price = parseFloat($('.product_selling_unit_price:eq('+i+')').val());
                product_selling_unit_price = (isNaN(product_selling_unit_price) || product_selling_unit_price < 0) ? 0 : product_selling_unit_price;


                $('.total-purchase-cost:eq('+i+')').text((product_qty * product_unit_price).toFixed(2) );
                total_quantity += product_qty;
                total_selling_price += product_qty * product_selling_unit_price;
                total_purchase_price += product_qty * product_unit_price;
            });

            $('#total_quantity').text(total_quantity);
            $('#total_selling_unit_price').text(total_selling_price.toFixed(2));
            $('#total_purchase_price').text(total_purchase_price.toFixed(2));

            if (rows.length > 0){
                $("#footer_area").show();
            }else{
                $("#footer_area").hide();
            }
        }
    </script>
@endsection
