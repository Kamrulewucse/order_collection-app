@extends('layouts.app')
@section('title','Task List')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin','Divisional Admin']))
                        <a href="{{ route('task.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">Task Create <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Task Created Date</th>
                                <th>User Name</th>
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
                ajax: '{{ route('task.datatable') }}',
                pagingType: "full_numbers",
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className:'text-center'},
                    {data: 'date', name: 'date'},
                    {data: 'user', name: 'user.name'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className:'text-center'},
                ],
                dom: 'lBfrtip',
                buttons: datatableButtons(),
                responsive: true,
                autoWidth: false,
                colReorder: true,
            });

        });
    </script>
@endsection
