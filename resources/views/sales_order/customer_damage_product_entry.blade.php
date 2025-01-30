@extends('layouts.app')
@section('title',$pageTitle)
@section('content')
    <form
        action="{{ route('sales-order.customer_damage_product_entry',['distributionOrder'=>$distributionOrder->id,'type'=>request('type')]) }}"
        method="post">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card card-default">
                    <div class="card-header">
                        <div class="card-title">Customer Damage Product Entry</div>
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
                                    <th class="text-center">Brand</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Delivery Damage Quantity</th>
                                    <th class="text-center">Return Damage Quantity</th>
                                    <th class="text-center">Distributed Quantity</th>
                                </tr>
                                </thead>
                                <tbody id="product-container">
                                @foreach($distributionOrder->distributionOrderItems->where('damage_quantity','>',0) as $item)
                                    <tr class="product-item">
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->product->code ?? '' }}</td>
                                        <td class="text-center">{{ $item->product->brand->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                        <td class="text-right ">{{ number_format($item->damage_quantity) }}</td>
                                        <td class="text-center">
                                            <input type="hidden" name="distribution_order_item_id[]"
                                                   value="{{ $item->id }}">
                                            <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                            <input type="hidden" name="damage_quantity[]" class="damage_quantity" value="{{ $item->damage_quantity }}">
                                            <input type="number" name="return_quantity[]" step="any"
                                                   class="form-control text-right text-bold return_quantity"
                                                   value="{{ $item->damage_return_quantity }}">
                                        </td>
                                        <td class="text-right distribute_quantity">{{ number_format($item->damage_quantity - $item->damage_return_quantity) }}</td>

                                    </tr>
                                @endforeach
                                <tr>
                                    <th class="text-right" colspan="5">Total</th>
                                    <th class="text-right">{{ number_format($distributionOrder->distributionOrderItems->sum('damage_quantity')) }}</th>
                                    <th class="text-right" id="total_return_quantity">{{ number_format($distributionOrder->distributionOrderItems->sum('damage_return_quantity')) }}</th>
                                    <th class="text-right" id="total_distribution_quantity">{{ number_format($distributionOrder->distributionOrderItems->sum('damage_quantity') - $distributionOrder->distributionOrderItems->sum('damage_return_quantity')) }}</th>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary bg-gradient-primary">Update</button>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
    </form>
@endsection
@section('script')
    <script>
        calculate();
        $(function () {
            $('body').on('keyup', 'input[type="number"]', function () {
                calculate();
            });
            $('body').on('change', 'input[type="number"]', function () {
                calculate();
            });
        })

        function calculate() {
            var rows = $("#product-container .product-item");

            var total_return_quantity = 0;
            var total_distribution_quantity = 0;

            $('.product-item').each(function (i, obj) {

                let damage_quantity = parseFloat($('.damage_quantity:eq(' + i + ')').val());
                damage_quantity = (isNaN(damage_quantity) || damage_quantity < 0) ? 0 : damage_quantity;

                let return_quantity = parseFloat($('.return_quantity:eq(' + i + ')').val());
                return_quantity = (isNaN(return_quantity) || return_quantity < 0) ? 0 : return_quantity;

                $('.distribute_quantity:eq(' + i + ')').text((damage_quantity - return_quantity).toFixed(2));

                total_distribution_quantity += damage_quantity - return_quantity;
                total_return_quantity += return_quantity;

            });

            $('#total_return_quantity').text(total_return_quantity);
            $('#total_distribution_quantity').text(total_distribution_quantity);

        }


    </script>
@endsection
