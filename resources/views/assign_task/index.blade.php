@extends('layouts.app')
@section('title','Task List')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin']))
                        <a href="{{ route('assign-task.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">Assign Task Create <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Date</th>
                                <th>SR/Doctor Name</th>
                                <th>Taks Details</th>
                                <th>Task Priority </th>
                                <th>Task Document</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Task Cost</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="8" style="text-align: end !important;">Total:</th>
                                    <th id="task_cost_total"></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

    <!-- Task Cost Modal -->
<div class="modal fade" id="taskCostModal" tabindex="-1" aria-labelledby="taskCostModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Task Cost</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Cost Price submit modal --}}
            <div class="modal-body">
                <form id="taskCostForm">
                    <input type="hidden" name="assign_task_id" id="assign_task_id">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File Upload</label>
                        <input type="file" class="form-control" id="file" name="file" accept="application/pdf,image/*">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
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
            ajax: '{{ route('assign_task.datatable') }}',
            pagingType: "full_numbers",
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className:'text-center'},
                {data: 'date', name: 'date'},
                {data: 'srOrDoctor', name: 'srOrDoctor.name'},
                {data: 'task_details', name: 'task_details'},
                {data: 'task_priority', name: 'task_priority'},
                {data: 'file', name: 'file', orderable: false, searchable: false},
                {data: 'notes', name: 'notes'},
                {data: 'status', name: 'status'},
                {data: 'task_cost', name: 'task_cost'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className:'text-center'},
            ],
            dom: 'lBfrtip',
            buttons: datatableButtons(),
            responsive: true,
            autoWidth: false,
            colReorder: true,

            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                var intVal = function (i) {
                    return typeof i === 'string' ?
                        parseFloat(i.replace(/[\$,]/g, '')) :
                        typeof i === 'number' ?
                        i : 0;
                };

                var total = api
                    .column(8, { search: 'applied' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                $(api.column(8).footer()).html(total.toLocaleString(undefined, { minimumFractionDigits: 2 }));
            }
        });

            $('body').on('click', '.status-btn', function () {
                let id = $(this).data('id');
                let status = $(this).data('status');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to make it " + status + "!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, '+status+' it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "post",
                            url: "{{ route('assign-task.status', ['assign_task' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                            data: { id: id,status:status }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
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

            $('body').on('click', '.task-cost-btn', function () {
                var id = $(this).data('id');
                $('#assign_task_id').val(id);
                $('#taskCostModal').modal('show');
            });

            $('#taskCostForm').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    method: 'POST',
                    url: '{{ route("assign_task_cost.store") }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message
                            }).then(() => {
                                $('#taskCostModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message,
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong while submitting the task cost.',
                        });
                    }
                });
            });

        });
    </script>
@endsection
