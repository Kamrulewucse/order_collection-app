@extends('layouts.app')
@section('title','Divisional Head Create')
@section('style')
    <style>
        ul{
            list-style: none;
        }
        label:not(.form-check-label):not(.custom-file-label) {
            font-weight: normal;
        }
        label.child-checkbox-label {
            font-size: 18px;
        }
        label.grandchild-checkbox-label {
            font-size: 16px;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Divisional Head Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('divisional-user.store') }}" class="form-horizontal" method="post">
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
                        <div class="form-group row {{ $errors->has('user') ? 'has-error' :'' }}">
                            <label for="user" class="col-sm-2 col-form-label">Head of Department(Admin) <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-control select2" name="user">
                                    <option value="">Select Option</option>
                                    @foreach ($admins as $admin)
                                        <option value="{{ $admin->id }}" {{ old('user') == $admin->id?'selected':'' }}>{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                                @error('user')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('division') ? 'has-error' :'' }}">
                            <label for="division" class="col-sm-2 col-form-label">Division <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-control select2" name="division">
                                    <option value="">Select Option</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name_eng }}</option>
                                    @endforeach
                                </select>
                                @error('division')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('email') ? 'has-error' :'' }}">
                            <label for="email" class="col-sm-2 col-form-label">Email</label>
                            <div class="col-sm-10">
                                <input type="email" value="{{ old('email') }}" name="email" class="form-control" id="email" placeholder="Enter Email">
                                @error('email')
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
                        <div class="form-group row {{ $errors->has('address') ? 'has-error' :'' }}">
                            <label for="address" class="col-sm-2 col-form-label">Address </label>
                            <div class="col-sm-10">
                                <textarea name="address" class="form-control" id="address">{{ old('address') }}</textarea>
                                @error('address')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label class="col-sm-2 col-form-label">Status <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="icheck-success d-inline">
                                    <input checked type="radio" id="active" name="status" value="1" {{ old('status') == '1' ? 'checked' : '' }}>
                                    <label for="active">
                                        Active
                                    </label>
                                </div>

                                <div class="icheck-danger d-inline">
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
                        <button type="submit" class="btn btn-primary bg-gradient-primary">Save</button>
                        <a href="{{ route('divisional-user.index') }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
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


        $(document).ready(function() {
            function updateParentCheckboxes() {
                $('.parent-checkbox').each(function() {
                    var $this = $(this);
                    var $childCheckboxes = $this.closest('td').find('.child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox');
                    var checkedChildCheckboxes = $childCheckboxes.filter(':checked');

                    if (checkedChildCheckboxes.length > 0) {
                        $this.prop('checked', true);
                    }
                });
            }

            $('.check-all-checkbox').change(function() {
                var isChecked = $(this).prop('checked');
                $('.parent-checkbox, .child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox').prop('checked', isChecked);
                updateParentCheckboxes();
            });

            $('.child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox').change(function() {
                updateParentCheckboxes();
            });

            $('.parent-checkbox').change(function() {
                var $this = $(this);
                $this.siblings('ul').find('.child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox').prop('checked', this.checked);
            });

            updateParentCheckboxes();
        });

    </script>
@endsection
