@extends('layouts.app')
@section('title', 'Products')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('product.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">Create Product
                        <i class="fa fa-plus"></i></a>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="product" class="col-form-label">Client <span
                                        class="text-danger">*</span></label>
                                <select name="product" id="product" class="form-control select2">
                                    <option value="">All Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="category" class="col-form-label">Category <span
                                        class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-control select2">
                                    <option value="">All Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="sub_category" class="col-form-label">Sub Category <span
                                        class="text-danger">*</span></label>
                                <select name="sub_category" id="sub_category" class="form-control select2">
                                    <option value="">All Sub Category</option>
                                    @foreach ($sub_categories as $sub_category)
                                        <option value="{{ $sub_category->id }}">{{ $sub_category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input style="margin-top: -4px;" type="button" id="search_btn" name="search"
                                    class="btn btn-primary bg-gradient-primary form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S/L</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Sub Category</th>
                                    <th>Unit</th>
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
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
        $(function() {
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('product.datatable') }}",
                    data: function(d) {
                        d.product = $("#product").val();
                        d.category = $("#category").val();
                        d.sub_category = $("#sub_category").val();
                    }
                },
                pagingType: "full_numbers",
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'category_name',
                        name: 'category.name'
                    },
                    {
                        data: 'sub_category_name',
                        name: 'sub_category.name'
                    },
                    {
                        data: 'unit_name',
                        name: 'unit.name'
                    },
                    {
                        data: 'purchase_price',
                        name: 'purchase_price'
                    },
                    {
                        data: 'selling_price',
                        name: 'selling_price'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data) {
                            return data === 1 ?
                                '<span class="badge badge-success">Active</span>' :
                                '<span class="badge badge-danger">Inactive</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                dom: 'lBfrtip',
                buttons: datatableButtons(),
                responsive: true,
                autoWidth: false,
                colReorder: true
            });

            // 🔑 Attach the event outside DataTable initialization
            $('#search_btn').on('click', function() {
                table.ajax.reload(); // Trigger table reload on Search button click
            });

            $('#product, #category, #sub_category').on('change', function() {
                table.ajax.reload(); // Trigger table reload on dropdown change
            });

            // Delete functionality
            $('body').on('click', '.btn-delete', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "DELETE",
                            url: "{{ route('product.destroy', ['product' => 'REPLACE_WITH_ID_HERE']) }}"
                                .replace('REPLACE_WITH_ID_HERE', id),
                            data: {
                                id: id
                            }
                        }).done(function(response) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success').then(
                                () => {
                                        table.ajax
                                    .reload(); // Reload table after deletion
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
                });
            });
        });
    </script>
@endsection
