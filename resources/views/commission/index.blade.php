@extends('layouts.app')
@section('title')
    {{ ucfirst(str_replace('_',' ',request('type')) ) }} Commission List
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('commission.create',['type'=>request('type')]) }}" class="btn btn-primary bg-gradient-primary btn-sm">Commission Create <i class="fa fa-plus"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                @if($typeId == 1)
                                <th>Company</th>
                                @endif
                                @if($typeId == 2)
                                    <th>Customer</th>
                                @endif
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Commission Base Amount</th>
                                <th>Commission</th>
                                <th>Commission Amount</th>
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
                    url: "{{ route('commission.datatable') }}",
                    data: function (d) {
                        d.type = '{{ $typeId }}'
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                   @if($typeId == 1)
                    {data: 'supplier_name', name: 'supplier.name'},
                    @endif
                   @if($typeId == 2)
                    {data: 'customer_name', name: 'customer.name'},
                    @endif
                    {data: 'start_date', name: 'start_date',className:'text-center'},
                    {data: 'end_date', name: 'end_date',className:'text-center'},
                    {data: 'commission_base_amount', name: 'commission_base_amount',className:'text-right'},
                    {data: 'commission_type_custom', name: 'commission_type_custom',className:'text-center'},
                    {data: 'commission_amount', name: 'commission_amount',className:'text-right'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

        });
    </script>
@endsection
