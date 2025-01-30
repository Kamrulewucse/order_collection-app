@extends('layouts.app')
@section('title',$pageTitle)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can($permission_create))
                        <a href="{{ route('sales-order.create',['type'=>request('type')]) }}" class="btn btn-primary bg-gradient-primary btn-sm">Create New <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="sr" class="col-form-label">SR <span
                                        class="text-danger">*</span></label>
                                <select name="sr" id="sr" class="form-control select2">
                                    <option value="">All SR</option>
                                    @foreach($srs as $sr)
                                    <option value="{{ $sr->id }}">{{ $sr->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Enter Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date') }}"
                                       name="end_date" class="form-control date-picker" id="end_date"
                                       placeholder="Enter End Date">
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
                                <th class="text-center">ID</th>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">SR</th>
                                <th class="text-center">Client</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Due</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="5" class="text-center">Total</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th colspan="3"></th>
                            </tr>
                            </tfoot>
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
            calculate();

            $('body').on('click', '.in-transit', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Sent it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "POST",
                            url: "{{ route('sales-order.in_transit_post', ['saleOrder' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE',id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Released!',
                                    response.message,
                                    'success'
                                ).then((result) => {
                                    location.reload();
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
                })

            });
            $('body').on('keyup', '#payment', function() {
                calculate();
            });

           var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sales-order.datatable') }}",
                    data: function (d) {
                        d.type = '{{ request('type')}}'
                        d.sr = $("#sr").val()
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'id', name: 'id',visible:false},
                    {data: 'order_no', name: 'order_no'},
                    {data: 'date', name: 'date'},
                    {data: 'sr_name', name: 'sr.name'},
                    {data: 'client_name', name: 'client.name'},
                    {data: 'total', name: 'total'},
                    {data: 'paid', name: 'paid'},
                    {data: 'due', name: 'due'},
                    {data: 'notes', name: 'notes'},
                        @if(request('type') == 1)
                             {data: 'status', name: 'status',className:'text-center'},
                        @endif
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[0, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
                footerCallback: function (row, data, start, end, display) {
                   var api = this.api();

                   // Helper function to sum the column values
                   var intVal = function (i) {
                       return typeof i === 'string' ?
                           i.replace(/[\$,]/g, '') * 1 :
                           typeof i === 'number' ?
                               i : 0;
                   };

                //    // Total over this page for each column
                   var pageTotalTotal = api.column(5, { page: 'current' }).data().reduce(function (a, b) {
                       return intVal(a) + intVal(b);
                   }, 0);

                   var pageTotalPaid = api.column(6, { page: 'current' }).data().reduce(function (a, b) {
                       return intVal(a) + intVal(b);
                   }, 0);

                   var pageTotalDue = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
                       return intVal(a) + intVal(b);
                   }, 0);

                   // Update footer
                   $(api.column(5).footer()).html(jsNumberFormat(pageTotalTotal.toFixed(2)));
                   $(api.column(6).footer()).html(jsNumberFormat(pageTotalPaid.toFixed(2)));
                   $(api.column(7).footer()).html(jsNumberFormat(pageTotalDue.toFixed(2)));
               }
            });
            $('#start_date,#end_date,#search_btn,#sr').change(function () {
                table.ajax.reload();
                let start_date = $("#start_date").val();
                let end_date = $("#end_date").val();
            });
        });
        function calculate() {

            let total = parseFloat($('#total').val());
            total = (isNaN(total) || total < 0) ? 0 : total;

            let due_hidden = parseFloat($('#due_hidden').val());
            due_hidden = (isNaN(due_hidden) || due_hidden < 0) ? 0 : due_hidden;

            let payment = parseFloat($('#payment').val());
            payment = (isNaN(payment) || payment < 0) ? 0 : payment;

            $("#due").val(Math.ceil(due_hidden - payment).toFixed(2));
        }
    </script>
@endsection
