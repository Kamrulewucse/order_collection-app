@extends('layouts.app')
@section('title',$pageTitle)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->role == 'SR')
                        <a href="{{ route('requisition-order.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">Create New <i class="fa fa-plus"></i></a>
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
                                <th class="text-center">Document</th>
                                <th class="text-center">SR</th>
                                <th class="text-center">Client</th>
                                <th class="text-center">Client Type</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Advance</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="7" class="text-center">Total</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th colspan="2"></th>
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
            $(document).on('click', '.zoom-gallery', function (e) {
                e.preventDefault();
                $(this).magnificPopup({
                    delegate: 'a',
                    type: 'image',
                    closeOnContentClick: false,
                    closeBtnInside: false,
                    mainClass: 'mfp-with-zoom mfp-img-mobile',
                    allowHTMLInTemplate: true,
                    image: {
                        verticalFit: true,
                        titleSrc: function(item) {
                            return item.el.attr('title') + ' &middot; <a class="image-source-link" href="'+item.el.attr('data-source')+'" target="_blank">image source</a>';
                        }
                    },
                    gallery: {
                        enabled: true
                    },
                    zoom: {
                        enabled: true,
                        duration: 300, // don't foget to change the duration also in CSS
                        opener: function(element) {
                            return element.find('img');
                        }
                    }
                }).magnificPopup('open');

            });
            $('body').on('click', '.approved', function () {
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
                            url: "{{ route('requisition-order.approved',['requisitionOrder'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE',id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Approved!',
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
                    url: "{{ route('requisition-order.datatable') }}",
                    data: function (d) {
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
                    {data: 'document', name: 'document'},
                    {data: 'sr_name', name: 'sr.name'},
                    {data: 'client_name', name: 'client.name'},
                    {data: 'client_type', name: 'client.client_type'},
                    {data: 'total', name: 'total'},
                    {data: 'advance', name: 'advance'},
                    {data: 'status', name: 'status'},
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
                   var pageTotalTotal = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
                       return intVal(a) + intVal(b);
                   }, 0);

                   var pageTotalPaid = api.column(8, { page: 'current' }).data().reduce(function (a, b) {
                       return intVal(a) + intVal(b);
                   }, 0);

                   // Update footer
                   $(api.column(7).footer()).html(jsNumberFormat(pageTotalTotal.toFixed(2)));
                   $(api.column(8).footer()).html(jsNumberFormat(pageTotalPaid.toFixed(2)));
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
