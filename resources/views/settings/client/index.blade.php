@extends('layouts.app')
@section('title','Clients')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('client.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">Create Client <i class="fa fa-plus"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Name</th>
                                <th>Client Type</th>
                                <th>Shop Name</th>
                                <th>Mobile No.</th>
                                <th>SR</th>
                                <th>District</th>
                                <th>Thana</th>
                                <th>Address</th>
                                <th>Credit Balance</th>
                                <th>Debit Balance</th>
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
        $(function () {

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('client.datatable') }}',
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'client_type', name: 'client_type'},
                    {data: 'shop_name', name: 'shop_name'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {data: 'sr_name', name: 'sr.name'},
                    {data: 'district_name', name: 'district.name_eng'},
                    {data: 'thana_name', name: 'thana.name_eng'},
                    {data: 'address', name: 'address'},
                    {
                        data: 'balance',
                        name: 'balance',
                        render: function (data, type, row) {
                            // Format the opening_balance as a number with commas and 2 decimal places
                            return parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2});

                        }
                    },
                    {
                        data: 'debit_balance',
                        name: 'debit_balance',
                        render: function (data, type, row) {
                            // Format the opening_balance as a number with commas and 2 decimal places
                            return parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2});

                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data, type, row) {
                            if (data === 1) {
                                return '<span class="badge badge-success">Active</span>';
                            } else if (data === 0) {
                                return '<span class="badge badge-danger">Inactive</span>';
                            }
                            return data;
                        }
                    },
                    {data: 'action', name: 'action', orderable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('body').on('click', '.btn-delete', function () {
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
                            url: "{{ route('client.destroy', ['client' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                            data: { id: id }
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
        });
    </script>
@endsection
