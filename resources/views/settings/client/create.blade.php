@extends('layouts.app')
@section('title','Client Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Client Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('client.store',['type' => 1]) }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        @if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin','Divisional Admin']))
                        <div class="form-group row {{ $errors->has('sr') ? 'has-error' :'' }}">
                            <label for="sr" class="col-sm-2 col-form-label">SR Name<span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="sr" id="sr" class="form-control select2">
                                    <option value="">Select SR</option>
                                    @foreach($srs as $sr)
                                    <option {{ old('sr') == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}" data-district_id="{{ $sr->district_id }}">{{ $sr->name }}</option>
                                    @endforeach
                                </select>
                                @error('sr')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @else
                        <div class="form-group row {{ $errors->has('sr') ? 'has-error' :'' }}">
                            <label for="sr" class="col-sm-2 col-form-label">SR Name<span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" readonly value="{{ old('sr',$srs->name) }}" name="sr_name" class="form-control" id="sr_name" placeholder="Enter Name">
                                <input type="hidden" readonly value="{{ old('sr',$srs->id) }}" name="sr" class="form-control" id="sr" placeholder="Enter Name">
                                @error('sr')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Client Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('client_type') ? 'has-error' :'' }}">
                            <label for="client_type" class="col-sm-2 col-form-label">Client Type<span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="client_type" id="client_type" class="form-control select2">
                                    <option value="">Select Type</option>
                                    <option value="Credit" {{ old('client_type') == 'Credit'?'selected':'' }}>Credit Client</option>
                                    <option value="Debit" {{ old('client_type') == 'Debit'?'selected':'' }}>Debit Client</option>
                                </select>
                                @error('client_type')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('debit_balance') ? 'has-error' :'' }}" id="debit_balance_area">
                            <label for="debit_balance" class="col-sm-2 col-form-label">Debit Balance </label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('debit_balance',0) }}" name="debit_balance" class="form-control" id="debit_balance" placeholder="Enter Debit Balance">
                                @error('debit_balance')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('shop_name') ? 'has-error' :'' }}">
                            <label for="shop_name" class="col-sm-2 col-form-label">Shop Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('shop_name') }}" name="shop_name" class="form-control" id="shop_name" placeholder="Enter Shop Name">
                                @error('shop_name')
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

                        <div class="form-group row {{ $errors->has('district') ? 'has-error' :'' }}">
                            <label for="district" class="col-sm-2 col-form-label">District <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="district" id="district" class="form-control select2">
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        @if (auth()->user()->role == 'SR')
                                            <option {{ old('district',auth()->user()->client->district_id) == $district->id ? 'selected' : '' }} value="{{ $district->id }}">{{ $district->name_eng }}</option>
                                        @else
                                            <option {{ old('district') == $district->id ? 'selected' : '' }} value="{{ $district->id }}">{{ $district->name_eng }}</option>
                                        @endif
                                        
                                    @endforeach
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
                        <div class="form-group row {{ $errors->has('latitude') ? 'has-error' :'' }}">
                            <label for="latitude" class="col-sm-2 col-form-label">Latitude <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" step="any" value="{{ old('latitude') }}" name="latitude" class="form-control" id="latitude" placeholder="Enter latitude">
                                @error('latitude')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('longitude') ? 'has-error' :'' }}">
                            <label for="longitude" class="col-sm-2 col-form-label">Longitude <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" step="any" value="{{ old('longitude') }}" name="longitude" class="form-control" id="longitude" placeholder="Enter longitude">
                                @error('longitude')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="form-group row {{ $errors->has('opening_balance') ? 'has-error' :'' }}">
                            <label for="opening_balance" class="col-sm-2 col-form-label">Opening Balance <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('opening_balance',0) }}" name="opening_balance" class="form-control" id="opening_balance" placeholder="Enter Opening Balance">
                                @error('opening_balance')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div> --}}
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
                        <a href="{{ route('client.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
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

                function loadSubcategories(districtId, selectedThanaId = null) {
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


                if (oldDistrictId) {
                    loadSubcategories(oldDistrictId, oldThanaId);
                }

                // Handle district change
                $('#district').change(function () {
                    const districtId = $(this).val();
                    loadSubcategories(districtId);
                });
                $('#district').trigger('change');

                $('#sr').change(function () {
                    const srDistrictId = $(this).find('option:selected').data('district_id');
                    
                    $('#district').val(srDistrictId).change();
                });
                $('#client_type').change(function(){
                    let client_type = $(this).val();
                    if(client_type == 'Debit'){
                        $('#debit_balance_area').show();
                    }else{
                        $('#debit_balance_area').hide();
                    }
                });
                $('#client_type').trigger('change');
            });
        })
    </script>
@endsection

