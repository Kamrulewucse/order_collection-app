@extends('layouts.app')
@section('title','Inventory')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-header">
                    <form action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company">Company</label>
                                    <select name="company" class="form-control select2" id="company">
                                        <option value="">All Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" id="search" name="search"
                                           class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Avg. Purchase Price</th>
                                <th class="text-center">Last Purchase Price</th>
                                <th class="text-center">Selling Price</th>
{{--                                <th class="text-center">Action</th>--}}
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-utilize" data-backdrop="static" >
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Product Utilize Information</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="product-utilize-form" method="POST"
                          action="{{ route('inventory.utilize_product') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" name="inventory_id" id="inventory_id">
                                <div class="form-group">
                                    <label for="utilize_quantity">Utilize Quantity <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="utilize_quantity" id="utilize_quantity" class="form-control" placeholder="Enter Utilize Quantity">
                                    <span class="text-danger error-message" id="utilize_quantity-error"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                    <button type="button" id="btn-utilize-save" class="btn btn-primary bg-gradient-primary">Utilize</button>
                </div>
            </div>

        </div>

    </div>
@endsection
@section('script')
    <script>
        $(function () {
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventory.datatable') }}",
                    data: function (d) {
                        d.company_id = $("#company").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'id', name: 'id',className:'text-center'},
                    {data: 'product_name', name: 'product.name'},
                    {data: 'product_code', name: 'product_code',className:'text-center'},
                    {data: 'supplier_name', name: 'product.supplier.name'},
                    {data: 'brand_name', name: 'product.brand.name'},
                    {data: 'unit_name', name: 'product.unit.name',className:'text-center'},
                    {data: 'quantity', name: 'quantity',className:'text-right'},
                    {data: 'average_purchase_unit_price', name: 'average_purchase_unit_price',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },className:'text-right'
                    },
                    {data: 'last_purchase_unit_price', name: 'last_purchase_unit_price',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },className:'text-right'
                    },
                    {data: 'selling_price', name: 'selling_price',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },className:'text-right'
                    },
                    // {data: 'action', name: 'action',className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

            $("#company").change(function (){
                table.ajax.reload();
            })
            $("#search").click(function (){
                table.ajax.reload();
            })
            $('body').on('click', '.btn-utilize', function () {
                let inventoryId = $(this).data('id');
                $("#inventory_id").val(inventoryId);
                $('.error-message').text(' ');
                $("#modal-utilize").modal('show');

            })
            $('#btn-utilize-save').click(function () {
                preloaderToggle(true);
                $('.error-message').text(' ');
                $.ajax({
                    type: 'POST',
                    url: $('#product-utilize-form').attr('action'),
                    data: $('#product-utilize-form').serialize(),
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            $("#modal-utilize").modal('hide');
                            Swal.fire({
                                position: "top",
                                icon: "success",
                                title: "Utilize product ",
                                showConfirmButton: false,
                                timer: 1500,
                                customClass: {
                                    popup: 'swal2-popup-centered' // Adding a custom class for positioning
                                }
                            });
                            setTimeout(function() {
                                location.reload()
                            }, 2000); // 2000 milliseconds = 2 seconds

                        } else {
                            ajaxErrorMessage(response.message);
                        }
                    },
                    error: function (xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            ajaxWarningMessage('Please fill up validate required fields.');
                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text(' ');
                            // Update error messages for each field
                            $.each(errors, function (field, errorMessage) {
                                $('#' + field + '-error').text(errorMessage[0]);
                            });
                        }
                    }
                });
            });

        });
    </script>
@endsection
