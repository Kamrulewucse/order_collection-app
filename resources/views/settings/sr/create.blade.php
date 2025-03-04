@extends('layouts.app')
@section('title','SR Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">SR Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('sr.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('mobile_no') ? 'has-error' :'' }}">
                            <label for="mobile_no" class="col-sm-2 col-form-label">Mobile No. <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('mobile_no') }}" name="mobile_no" class="form-control" id="mobile_no" placeholder="Enter Mobile No.">
                                @error('mobile_no')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('email') ? 'has-error' :'' }}">
                            <label for="email" class="col-sm-2 col-form-label">Email  <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="email" value="{{ old('email') }}" name="email" class="form-control" id="email" placeholder="Enter Email">
                                @error('email')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('divisional_user_id') ? 'has-error' :'' }}">
                            <label for="divisional_user_id" class="col-sm-2 col-form-label">Divisional Head <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-control select2" name="divisional_user_id" id="divisional_admin">
                                    <option value="">Select Option</option>
                                    @foreach ($divisionalUsers as $divisionalUser)
                                        <option value="{{ $divisionalUser->id }}" data-division_id="{{ $divisionalUser->division_id }}">{{ $divisionalUser->name }} - ({{ $divisionalUser->division->name_eng??'' }})</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="division" id="division">
                                @error('divisional_user_id')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('district') ? 'has-error' :'' }}">
                            <label for="district" class="col-sm-2 col-form-label">District <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="district" id="district" class="form-control select2">
                                    <option value="">Select District</option>
                                    {{-- @foreach($districts as $district)
                                    <option {{ old('district') == $district->id ? 'selected' : '' }} value="{{ $district->id }}">{{ $district->name_eng }}</option>
                                    @endforeach --}}
                                </select>
                                @error('district')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('thana') ? 'has-error' :'' }}">
                            <label for="thana" class="col-sm-2 col-form-label">Thana <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="thana" id="thana" class="form-control select2">
                                    <option value="">Select Thana</option>
                                </select>
                                @error('thana')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('address') ? 'has-error' :'' }}">
                            <label for="address" class="col-sm-2 col-form-label">Address</label>
                            <div class="col-sm-10">
                                <textarea  name="address" rows="2" class="form-control" id="address" placeholder="Enter address">{{ old('address') }}</textarea>
                                @error('address')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label class="col-sm-2 col-form-label">Status <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="icheck-success d-inline pull-right">
                                    <input checked type="radio" id="active" name="status" value="1" {{ old('status') == '1' ? 'checked' : '' }}>
                                    <label for="active">
                                        Active
                                    </label>
                                </div>
                                <div class="icheck-danger d-inline pull-right">
                                    <input type="radio" id="inactive" name="status" value="0" {{ old('status') == '0' ? 'checked' : '' }}>
                                    <label for="inactive">
                                        Inactive
                                    </label>
                                </div>
                                @error('status')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('sr.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection
@section('script')
    <script>
        $(function (){
            $(document).ready(function () {
                const oldThanaId = "{{ old('thana') }}";
                const oldDistrictId = "{{ old('district') }}";
                const oldDivisionId = "{{ old('division') }}";

                function loadDistricts(divisionId, selectedDistrictId = null) {
                    if (divisionId) {
                        $.ajax({
                            url: "{{ route('get.districts', ':divisionId') }}".replace(':divisionId', divisionId),
                            type: 'GET',
                            success: function (data) {
                                $('#thana').empty().append('<option value="">Select District</option>');

                                if (data.length > 0) {
                                    data.forEach(function (district) {
                                        $('#district').append(
                                            `<option value="${district.id}" ${ selectedDistrictId == district.id ? 'selected' : '' }>${district.name_eng}</option>`
                                        );
                                    });
                                } else {
                                    alert('No districts available for the selected division.');
                                }
                            },
                            error: function () {
                                alert('Failed to load Districts.');
                            }
                        });
                    } else {
                        $('#district').empty().append('<option value="">Select District</option>');
                    }
                }
                function loadThanas(districtId, selectedThanaId = null) {
                    if (districtId) {
                        $.ajax({
                            url: "{{ route('get.thanas', ':districtId') }}".replace(':districtId', districtId),
                            type: 'GET',
                            success: function (data) {
                                $('#thana').empty().append('<option value="">Select Thana</option>');

                                if (data.length > 0) {
                                    data.forEach(function (thana) {
                                        $('#thana').append(
                                            `<option value="${thana.id}" ${ selectedThanaId == thana.id ? 'selected' : '' }>${thana.name_eng}</option>`
                                        );
                                    });
                                } else {
                                    alert('No thanas available for the selected district.');
                                }
                            },
                            error: function () {
                                alert('Failed to load thanas.');
                            }
                        });
                    } else {
                        // If no district is selected, clear the thana field
                        $('#thana').empty().append('<option value="">Select Thana</option>');
                    }
                }


                if (oldDivisionId) {
                    loadDistricts(oldDistrictId, oldThanaId);
                }
                if (oldDistrictId) {
                    loadThanas(oldDistrictId, oldThanaId);
                }

                // Handle district change
                $('#divisional_admin').change(function () {
                    const divisionId = $(this).find('option:selected').data('division_id');
                    // alert(divisionId);
                    $('#district').empty().append('<option value="">Select District</option>');
                    $('#division').val(divisionId);
                    loadDistricts(divisionId);
                });
                // Handle district change
                $('#district').change(function () {
                    const districtId = $(this).val();
                    loadThanas(districtId);
                });
            });
        })
    </script>
@endsection
