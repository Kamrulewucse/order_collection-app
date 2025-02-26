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
    <form enctype="multipart/form-data" action="{{ route('chick-production.store') }}" class="form-horizontal" method="post">
        @csrf
        <div class="row">
        <!-- left column -->
        <div class="col-md-9">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Chick Information </h3>
                </div>
                <div class="card-header">
                   <div class="row">

                       <div class="col-md-3">
                           <div class="form-group">
                               <label for="select_raw_product">Raw Product <span class="text-danger">*</span></label>
                               <select class="form-control select2" id="select_raw_product" name="select_raw_product">
                                   <option value="">Search Name of Product</option>
                                   @foreach($raw_products as $product)
                                       <option {{ old('product') == $product->id ? 'selected' : ''  }} value="{{ $product->id }}" data-purchase_price="{{$product->purchase_price}}" data-selling_price="{{$product->selling_price}}">{{ $product->name }} - Code:{{ $product->code }} - {{ $product->unit->name ?? '' }}</option>
                                   @endforeach
                               </select>
                           </div>
                       </div>
                       <div class="col-md-2">
                           <div class="form-group">
                               <label for="add_raw_quantity">Raw Qty <span class="text-danger">*</span></label>
                               <input type="number" step="any" class="form-control" id="add_raw_quantity" placeholder="Qty">
                           </div>
                       </div>
                       <div class="col-md-2">
                           <div class="form-group">
                               <label for="add_loss_percentage">Loss Qty(%) <span class="text-danger">*</span></label>
                               <input type="number" step="any" class="form-control" id="add_loss_percentage" placeholder="Qty(%)">
                           </div>
                       </div>
                       <div class="col-md-3">
                        <div class="form-group">
                            <label for="select_product">Finished Product <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="select_finished_product" name="select_finished_product">
                                <option value="">Search Name of Product</option>
                                @foreach($finished_products as $product)
                                    <option {{ old('product') == $product->id ? 'selected' : ''  }} value="{{ $product->id }}" data-purchase_price="{{$product->purchase_price}}" data-selling_price="{{$product->selling_price}}">{{ $product->name }} - Code:{{ $product->code }} - {{ $product->unit->name ?? '' }}</option>
                                @endforeach
                            </select>
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
                                <div class="table-responsive">
                                <table class="table table-bordered">
                                   <thead>
                                       <tr>
                                           <th class="text-center" width="5%">S/L</th>
                                           <th class="text-center">Raw Item</th>
                                           <th class="text-center">Finished Goods</th>
                                           <th class="text-center" width="12%">Raw Qty <span class="text-danger">*</span></th>
                                           <th class="text-center" width="15%">Loss Qty(%) </th>
                                           <th class="text-center" width="15%">Qty(Finished Goods) <span class="text-danger">*</span></th>
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
                                                    <td class="text-left {{ $errors->has('raw_product_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <span class="raw_product_name">{{ old('raw_product_name_val.'.$loop->index) }}</span>
                                                        <input type="hidden" name="raw_product_name_val[]" value="{{ old('raw_product_name_val.'.$loop->index) }}" class="raw_product_name_val">
                                                        <input type="hidden" name="raw_product_id[]" value="{{ old('raw_product_id.'.$loop->index) }}" class="raw_product_id">
                                                    </td>
                                                    <td class="text-left {{ $errors->has('finished_product_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <span class="finished_product_name">{{ old('finished_product_name_val.'.$loop->index) }}</span>
                                                        <input type="hidden" name="finished_product_name_val[]" value="{{ old('finished_product_name_val.'.$loop->index) }}" class="finished_product_name_val">
                                                        <input type="hidden" name="finished_product_id[]" value="{{ old('finished_product_id.'.$loop->index) }}" class="finished_product_id">
                                                    </td>
                                                    <td class="text-right {{ $errors->has('raw_product_qty.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <div class="form-group mb-0">
                                                            <input type="number" step="any" value="{{ old('raw_product_qty.'.$loop->index) }}" class="form-control text-right raw_product_qty" name="raw_product_qty[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-right {{ $errors->has('loss_product_percentage.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <div class="form-group mb-0">
                                                            <input type="number" step="any" value="{{ old('loss_product_percentage.'.$loop->index) }}" class="form-control text-right loss_product_percentage" name="loss_product_percentage[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-right {{ $errors->has('finished_product_qty.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <div class="form-group mb-0">
                                                            <input type="number" step="any" value="{{ old('finished_product_qty.'.$loop->index) }}" class="form-control text-right finished_product_qty" name="finished_product_qty[]" readonly>
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    <tfoot>
                                        <tr style="{{ (old('raw_product_id') != null && sizeof(old('raw_product_id'))) > 0 ? 'display: revert' : 'display: none' }}" id="footer_area">
                                            <th colspan="3" class="text-right">Total</th>
                                            <th class="text-right" id="total_raw_quantity"></th>
                                            <th></th>
                                            <th class="text-right" id="total_finished_quantity"></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('chick-production.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
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
                            <div class="form-group {{ $errors->has('hatchery_manager') ? 'has-error' :'' }}">
                                <label for="hatchery_manager">Hatchery Manager <span
                                        class="text-danger">*</span></label>
                                <select class="form-control select2" id="hatchery_manager" name="hatchery_manager" required>
                                    <option value="">Select Hatchery Manager</option>
                                    @foreach($managers as $manager)
                                        <option {{ old('hatchery_manager') == $manager->id ? 'selected' : ''  }} value="{{ $manager->id }}">{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                                @error('hatchery_manager')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                <label for="date">Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" value="{{ old('date',date('d-m-Y')) }}" name="date" id="date" class="form-control date-picker" placeholder="Enter date" readonly>
                                @error('date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div> 
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('production_date') ? 'has-error' :'' }}">
                                <label for="production_date">Production Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" value="{{ old('production_date',date('d-m-Y')) }}" name="production_date" id="production_date" class="form-control date-picker" placeholder="Enter date" readonly>
                                @error('production_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div> 
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('document') ? 'has-error' :'' }}">
                                <label for="document">Document(Image) </label>
                                <input type="file"  name="document" id="document" class="form-control" placeholder="Enter">
                                @error('document')
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
              <span class="raw_product_name"></span>
                <input type="hidden" name="raw_product_name_val[]" class="raw_product_name_val">
                <input type="hidden" name="raw_product_id[]" class="raw_product_id">
            </td>
            <td class="text-left">
              <span class="finished_product_name"></span>
                <input type="hidden" name="finished_product_name_val[]" class="finished_product_name_val">
                <input type="hidden" name="finished_product_id[]" class="finished_product_id">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right raw_product_qty" name="raw_product_qty[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right loss_product_percentage" name="loss_product_percentage[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right finished_product_qty" name="finished_product_qty[]" readonly>
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
            var rawProductIds = [];
            var finishedProductIds = [];

            $( ".raw_product_id" ).each(function( index ) {
                if ($(this).val() != '') {
                    rawProductIds.push($(this).val());
                }
            });
            $( ".finished_product_id" ).each(function( index ) {
                if ($(this).val() != '') {
                    finishedProductIds.push($(this).val());
                }
            });

            $('body').on('keypress', '#add_damage_return_quantity,#add_quantity,#add_unit_price', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectRawProduct = $('#select_raw_product').val();
                let selectRawProductName = $("#select_raw_product option:selected").text();

                let selectFinishedProduct = $('#select_finished_product').val();
                let selectFinishedProductName = $("#select_finished_product option:selected").text();

                let addRawQuantity = $('#add_raw_quantity').val();
                let addLossPercentage = $('#add_loss_percentage').val();
                let addFinishedQuantity = addRawQuantity - ((addRawQuantity * addLossPercentage)/100);

                if (selectRawProduct == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select raw product !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (selectFinishedProduct == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select finished product quantity !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addRawQuantity == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type raw quantity!',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addLossPercentage == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type loss percentage !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if($.inArray(selectRawProduct, rawProductIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectRawProductName+ ' already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if($.inArray(selectFinishedProduct, finishedProductIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectFinishedProductName+ ' already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectRawProduct != '' && selectFinishedProduct != '' && addRawQuantity != '' && addLossPercentage != '') {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.hide();
                    item.closest('tr').find('.raw_product_name').text($("#select_raw_product option:selected").text());
                    item.closest('tr').find('.raw_product_name_val').val($("#select_raw_product option:selected").text());
                    item.closest('tr').find('.raw_product_id').val($("#select_raw_product option:selected").val());

                    item.closest('tr').find('.finished_product_name').text($("#select_finished_product option:selected").text());
                    item.closest('tr').find('.finished_product_name_val').val($("#select_finished_product option:selected").text());
                    item.closest('tr').find('.finished_product_id').val($("#select_finished_product option:selected").val());

                    item.closest('tr').find('.raw_product_qty').val(addRawQuantity);
                    item.closest('tr').find('.loss_product_percentage').val(addLossPercentage);
                    item.closest('tr').find('.finished_product_qty').val(addFinishedQuantity);

                    rawProductIds.push(selectRawProduct);
                    finishedProductIds.push(selectFinishedProduct);
                    item.show();
                    calculate();

                    $('#select_raw_product').val(null).trigger('change');
                    $('#select_finished_product').val(null).trigger('change');
                    $('#add_raw_quantity').val(' ');
                    $('#add_loss_percentage').val(' ');
                }
                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var raw_product_id = $(this).closest('tr').find('.raw_product_id').val();
                var finished_product_id = $(this).closest('tr').find('.finished_product_id').val();

                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.product-item').remove();
                calculate();
                rawProductIds = $.grep(rawProductIds, function(value) {
                    return value != raw_product_id;
                });
                finishedProductIds = $.grep(finishedProductIds, function(value) {
                    return value != finished_product_id;
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
            var total_raw_quantity = 0;
            var total_finished_quantity = 0;

            $('.product-item').each(function(i, obj) {
                let raw_product_qty = parseFloat($('.raw_product_qty:eq('+i+')').val());
                raw_product_qty = (isNaN(raw_product_qty) || raw_product_qty < 0) ? 0 : raw_product_qty;
                
                let loss_product_percentage = parseFloat($('.loss_product_percentage:eq('+i+')').val());
                loss_product_percentage = (isNaN(loss_product_percentage) || loss_product_percentage < 0) ? 0 : loss_product_percentage;

                let finished_product_qty = raw_product_qty - ((loss_product_percentage * raw_product_qty) / 100);
                $('.finished_product_qty:eq('+i+')').val(finished_product_qty);
                
                total_raw_quantity += raw_product_qty;
                total_finished_quantity += finished_product_qty;
            });

            $('#total_raw_quantity').text(total_raw_quantity);
            $('#total_finished_quantity').text(total_finished_quantity);

            if (rows.length > 0){
                $("#footer_area").show();
            }else{
                $("#footer_area").hide();
            }
        }
    </script>
<script type="text/javascript">
    andLocation();

    async function andLocation() {
        try {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    document.getElementById("latitude").value = position.coords.latitude;
                    document.getElementById("longitude").value = position.coords.longitude;
                    
                    // Notify Flutter that location access was granted
                    if (window.LocationChannel) {
                        window.LocationChannel.postMessage("requestLocationGranted");
                    }
                },
                (error) => {
                    if (error.code === error.PERMISSION_DENIED) {
                        alert("Location access is required to proceed. Please enable location permissions.");
                        
                        // Notify Flutter that location access was denied
                        if (window.LocationChannel) {
                            window.LocationChannel.postMessage("requestLocationDenied");
                        }
                    } else {
                        alert("Unable to fetch location. Please check your device settings.");
                    }
                    console.error("Error getting location:", error);
                }
            );
        } catch (error) {
            console.error("Unexpected error:", error);
        }
    }
</script>

@endsection
