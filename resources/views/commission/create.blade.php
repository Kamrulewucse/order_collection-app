@extends('layouts.app')
@section('title')
    {{ ucfirst(str_replace('_',' ',request('type')) ) }} Calculate
@endsection

@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('commission.create',['type'=>request('type')]) }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="hidden" name="type" value="{{ request('type') }}">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('start_date')}}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="supplier">{{ $typeId == 1 ? 'Company' : 'Customer' }} <span
                                            class="text-danger">*</span></label>
                                    <select name="supplier" id="supplier" class="form-control select2">
                                        <option value="">Select {{ $typeId == 1 ? 'Company' : 'Customer' }}</option>
                                        @foreach($clients as $supplier)
                                            <option {{ request('supplier') == $supplier->id ? 'selected' : '' }} value="{{ $supplier->id }}">{{ $supplier->name }} - {{ $supplier->mobile_no }}</option>
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
                    </div>

                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
    @if(count($orders) > 0)
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Order Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('commission.store',['type'=>request('type'),'type_id'=>$typeId]) }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Order No.</th>
                                    <th width="25%" class="text-center">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($order->date)->format('d-m-Y') }}</td>
                                    <td class="text-center">{{ $order->order_no }}</td>
                                    <td class="text-right">{{ number_format($order->total,2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-right" colspan="3">Total</th>
                                    <th class="text-right">{{ number_format($orders->sum('total'),2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="3">Commission Type</th>
                                    <th class="text-right">
                                        <input type="hidden" id="commission_base_amount" name="commission_base_amount" value="{{ $orders->sum('total') }}">
                                        <input type="hidden"  name="start_date" value="{{ request('start_date') }}">
                                        <input type="hidden"  name="end_date" value="{{ request('end_date') }}">
                                        <input type="hidden"  name="select_supplier" value="{{ request('supplier') }}">
                                        <div class="form-group mb-0">
                                            <select name="commission_type" style="width: 100% !important;" class="form-control" id="commission_type">
                                                <option {{ old('commission_type') == 1 ? 'selected' : '' }} value="1">Percent</option>
                                                <option {{ old('commission_type') == 1 ? 'selected' : '' }} value="2">Flat</option>
                                            </select>
                                            @error('commission_type')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </th>
                                </tr>
                                <tr class="commission_percent_area">
                                    <th class="text-right" colspan="3">Commission(%)</th>
                                    <th class="text-right">
                                        <div class="form-group mb-0">
                                            <input type="number" class="form-control" name="commission_percent" id="commission_percent">
                                            @error('commission_percent')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="3">Commission Amount</th>
                                    <th class="text-right">
                                        <div class="form-group mb-0">
                                            <input type="number" readonly class="form-control" name="commission_amount" id="commission_amount">
                                            @error('commission_amount')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('commission.index',['type'=>request('type')]) }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
    @else
        @if(request('start_date') != '')
        <div class="row">
            <div class="col-12">
                <h2 class="text-center text-danger">Not Found</h2>
            </div>
        </div>
        @endif
    @endif
@endsection

@section('script')
    <script>
        $(function (){
            calculate();
            $("#commission_type").change(function (){
                let type = $(this).val();
                if (type == 1){
                    $('.commission_percent_area').show();
                    $('#commission_amount').attr('readonly',true);
                }else{
                    $('.commission_percent_area').hide();
                    $('#commission_amount').attr('readonly',false);
                }
                calculate();

            })
            $("#commission_type").trigger('change');

            $('body').on('keyup', '#commission_percent', function() {
                calculate();
            });
        })
        function calculate(){

            let commission_base_amount = parseFloat($('#commission_base_amount').val());
            commission_base_amount = (isNaN(commission_base_amount) || commission_base_amount < 0) ? 0 : commission_base_amount;

            let commission_percent = parseFloat($('#commission_percent').val());
            commission_percent = (isNaN(commission_percent) || commission_percent < 0) ? 0 : commission_percent;
            let commission_type = parseFloat($('#commission_type').val());

            if (commission_type == 1){
                let commissionAmount = commission_base_amount * (commission_percent / 100);
                $("#commission_amount").val(commissionAmount);
            }else{
                $("#commission_amount").val(' ');
            }

        }
    </script>
@endsection
