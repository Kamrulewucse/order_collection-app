@extends('layouts.app')
@section('title','Farm Visit')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('farm-visit.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">Create Farm Visit <i class="fa fa-plus"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Farm Name</th>
                                <th>Doctor Name</th>
                                <th>Visit Date</th>
                                <th>Visit Time</th>
                                <th>Reason</th>
                                @if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin']))
                                <th>Visit Location</th>
                                @endif
                                <th>Image</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($farmVisits as $farmVisit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $farmVisit->farm->name }}</td>
                                    <td>{{ $farmVisit->doctor->name }}</td>
                                    <td>{{ date('d-m-Y',strtotime($farmVisit->visit_time)) }}</td>
                                    <td>{{ date('h:i:s A',strtotime($farmVisit->visit_time)) }}</td>
                                    @if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin']))
                                    <td>{{ $farmVisit->location_address ?? '' }}</td>
                                    @endif
                                    <td>{{ $farmVisit->reason }}</td>
                                    <td><a href="{{asset($farmVisit->location_image)}}" target="_blank">
                                        <img src="{{asset($farmVisit->location_image)}}" width="60" alt="Image"/>
                                    </a></td>
                                    <td>
                                        <a role="button" data-id="{{$farmVisit->id }}" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
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

            $('#table').DataTable();
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
                            url: "{{ route('farm-visit.destroy', ['farm_visit' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
