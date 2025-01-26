@extends('layouts.app')
@section('title',$pageTitle)
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
    <form enctype="multipart/form-data" action="{{ route('distribution.store',['type'=>request('type')]) }}" class="form-horizontal" method="post">
        @csrf
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
                                <select class="form-control select2" id="company" name="company">
                                    <option value="">Search Name of Company</option>
                                    @foreach($companies as $company)
                                        <option {{ request('company') == $company->id ? 'selected' : ''  }} value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('company')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-header">
                   <div class="row">

                       <div class="col-md-6">
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
                       @if(request('type') == 3)
                       <div class="col-md-3">
                           <div class="form-group">
                               <label for="add_damage_return_quantity">Damage Return Qty</label>
                               <input type="number" step="any" class="form-control" id="add_damage_return_quantity" placeholder="Qty">
                           </div>
                       </div>
                       @endif
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
                <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                   <thead>
                                       <tr>
                                           <th class="text-center" width="5%">S/L</th>
                                           <th class="text-center">Item Details</th>
                                           @if(request('type') == 3)
                                           <th class="text-center" width="12%">Damage Return Qty</th>
                                           @endif
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
                                                    <td class="text-left {{ $errors->has('product_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <span class="product_name">{{ old('product_name_val.'.$loop->index) }}</span>
                                                        <input type="hidden" name="product_name_val[]" value="{{ old('product_name_val.'.$loop->index) }}" class="product_name_val">
                                                        <input type="hidden" name="product_id[]" value="{{ old('product_id.'.$loop->index) }}" class="product_id">
                                                    </td>
                                                    @if(request('type') == 3)
                                                    <td class="text-right">
                                                        <div class="form-group mb-0  {{ $errors->has('damage_return_product_qty.'.$loop->index) ? 'has-error' :'' }}">
                                                            <input type="number" step="any" value="{{ old('damage_return_product_qty.'.$loop->index) }}" class="form-control text-right damage_return_product_qty" name="damage_return_product_qty[]">
                                                        </div>
                                                    </td>
                                                    @endif
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
                                            <th colspan="2" class="text-right">Total</th>
                                            @if(request('type') == 3)
                                            <th  class="text-right" id="total_damage_return_quantity"></th>
                                            @endif
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
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('distribution.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
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
                            <div class="form-group {{ $errors->has('sr') ? 'has-error' :'' }}">
                                <label for="dsr">DSR <span
                                        class="text-danger">*</span></label>
                                <select class="form-control select2" id="dsr" name="dsr">
                                    <option value="">Search Name of DSR</option>
                                    @foreach($clients as $supplier)
                                        <option {{ old('sr') == $supplier->id ? 'selected' : ''  }} value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('sr')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                <label for="date">Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" value="{{ old('date',date('d-m-Y')) }}" name="date" id="date" class="form-control date-picker" placeholder="Enter date">
                                @error('date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                <label for="notes">Notes</label>
                                <input type="text" value="{{ old('notes') }}" name="notes" id="notes" class="form-control" placeholder="Enter Notes">
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
            @if(request('type') == 3)
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right damage_return_product_qty" name="damage_return_product_qty[]">
                </div>
            </td>
            @endif
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
            $('#company').on('change', function() {
                // Get the selected company ID
                var companyId = $(this).val();

                // Construct the new URL while preserving existing query parameters
                var currentUrl = new URL(window.location.href);
                var searchParams = currentUrl.searchParams;
                searchParams.set('company', companyId);

                // Update the browser's URL and reload the page
                window.location.href = currentUrl.origin + currentUrl.pathname + '?' + searchParams.toString();
            });
            calculate();
            var productIds = [];

            $( ".product_id" ).each(function( index ) {
                if ($(this).val() != '') {
                    productIds.push($(this).val());
                }
            });

            $('body').on('keypress', '#add_damage_return_quantity,#add_quantity,#add_unit_price', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            $('body').on('change', '#select_product', function (e) {
                let product_id = $(this).val();
                let distribution_type = '{{ request('type') }}';
                $('#stock_quantity').html(' ');
                $('#add_unit_price').val(' ');
                if (product_id != '' && distribution_type == 1){
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_stock_info') }}",
                        data: {product_id : product_id }
                    }).done(function (response) {
                        if (response.inventory && response.inventory.quantity > 0){
                            $('#add_unit_price').val(response.inventory.selling_price ?? '');
                            $('#stock_quantity').html('<span class="text-success"><b>In Stock: </b>'+response.inventory.quantity+'</span>');
                        }else{
                            $('#stock_quantity').html('<span class="text-danger"><b>Out Stock</b></span>');

                        }
                    });
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectProduct = $('#select_product').val();
                let selectProductName = $("#select_product option:selected").text();
                let addDamageReturnQuantity = $('#add_damage_return_quantity').val();
                let addQuantity = $('#add_quantity').val();
                let addUnitPrice = $('#add_unit_price').val();
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

                if (selectProduct != '' && addQuantity != '' && addUnitPrice != '') {
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
                    item.closest('tr').find('.damage_return_product_qty').val(addDamageReturnQuantity);
                    item.closest('tr').find('.product_qty').val(addQuantity);
                    item.closest('tr').find('.product_unit_price').val(addUnitPrice);
                    productIds.push(selectProduct);
                    item.show();
                    calculate();
                    $('#select_product').val(null).trigger('change');
                    $('#add_damage_return_quantity').val('');
                    $('#add_quantity').val(' ');
                    $('#add_unit_price').val(' ');
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
            var total_damage_return_quantity = 0;
            var total_quantity = 0;
            var total_selling_price = 0;
            var total_purchase_price = 0;
            $('.product-item').each(function(i, obj) {

                let damage_return_product_qty = parseFloat($('.damage_return_product_qty:eq('+i+')').val());
                damage_return_product_qty = (isNaN(damage_return_product_qty) || damage_return_product_qty < 0) ? 0 : damage_return_product_qty;

                let product_qty = parseFloat($('.product_qty:eq('+i+')').val());
                product_qty = (isNaN(product_qty) || product_qty < 0) ? 0 : product_qty;

                let product_unit_price = parseFloat($('.product_unit_price:eq('+i+')').val());
                product_unit_price = (isNaN(product_unit_price) || product_unit_price < 0) ? 0 : product_unit_price;

                $('.total-purchase-cost:eq('+i+')').text((product_qty * product_unit_price).toFixed(2) );
                total_damage_return_quantity += damage_return_product_qty;
                total_quantity += product_qty;
                total_purchase_price += product_qty * product_unit_price;
            });

            $('#total_damage_return_quantity').text(total_damage_return_quantity);
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
