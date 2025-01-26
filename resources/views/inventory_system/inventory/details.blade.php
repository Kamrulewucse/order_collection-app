@extends('layouts.app')
@section('title')
   Bin Card:  Brand:
   @if($product)
    {{ $product->brand->name ?? '' }} - Product: {{ $product->name ?? '' }} - Code: {{ $product->code ?? '' }} - Unit:{{ $product->unit->name ?? '' }}
    @endif
@endsection
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
                                    <label for="product">Product</label>
                                    <select name="product" class="form-control select2" id="product">
                                        <option value="">All Product</option>
                                        @foreach($searchProducts as $searchProduct)
                                            <option {{ request('product',($product->id ?? ''))  == $searchProduct->id ? 'selected' : ''}} value="{{ $searchProduct->id }}"> Brand:{{ $searchProduct->brand->name ?? '' }} - Product:{{ $searchProduct->name }} - Code:{{ $searchProduct->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search"
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
                               <th class="text-center">Date</th>
                                @if(!($product->id ?? ''))
                                    <th class="text-center">Product Name</th>
                                @endif
                               <th class="text-center">Type</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Supplier/DSR</th>
                                <th class="text-center">P.O</th>
                                <th class="text-center">D.O</th>
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
        $(function () {

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventory_log.datatable') }}",
                    data: function (d) {
                        d.product_id = '{{ ($product->id ?? '') }}'
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'date', name: 'date',className:'text-center'},
                    @if(!($product->id ?? ''))
                        {data: 'product_name', name: 'product.name'},
                    @endif
                    {data: 'type', name: 'type'},
                    {data: 'quantity', name: 'quantity',className:'text-right'},
                    {data: 'unit_price', name: 'unit_price',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },className:'text-right'
                    },
                    {data: 'supplier_name', name: 'supplier.name'},
                    {data: 'purchase_order', name: 'purchase_order'},
                    {data: 'distribution_order', name: 'distribution_order'},

                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
        });
    </script>
@endsection
