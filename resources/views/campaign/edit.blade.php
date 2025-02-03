@extends('layouts.app')
@section('title','Campaign Update')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Campaign Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('campaign.update',['campaign'=>$campaign->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Campaign Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name',$campaign->name) }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('campaign_type') ? 'has-error' :'' }}">
                            <label for="campaign_type" class="col-sm-2 col-form-label">Campaign Type <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="campaign_type" class="form-control select2" id="campaign_type">
                                    <option value="">Select Campaign Type</option>
                                    <option value="1" {{ old('campaign_type',$campaign->campaign_type) == '1' ? 'selected' : '' }}>Online</option>
                                    <option value="2" {{ old('campaign_type',$campaign->campaign_type) == '2' ? 'selected' : '' }}>Offline</option>
                                </select>
                                @error('campaign_type')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('location') ? 'has-error' :'' }}">
                            <label for="location" class="col-sm-2 col-form-label">Location</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('location',$campaign->location) }}" name="location" class="form-control" id="location" placeholder="Enter location">
                                @error('location')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('campaign_cost') ? 'has-error' :'' }}">
                            <label for="campaign_cost" class="col-sm-2 col-form-label">Campaign Cost</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('campaign_cost',$campaign->campaign_cost) }}" name="campaign_cost" class="form-control" id="campaign_cost" placeholder="0.00">
                                @error('campaign_cost')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('start_date') ? 'has-error' :'' }}">
                            <label for="start_date" class="col-sm-2 col-form-label">Start date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" autocomplete="off" value="{{ old('start_date',\Carbon\Carbon::parse($campaign->start_date)->format('d-m-Y')) }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Enter Start Date">
                                @error('start_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('end_date') ? 'has-error' :'' }}">
                            <label for="end_date" class="col-sm-2 col-form-label">End date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" autocomplete="off" value="{{ old('end_date',\Carbon\Carbon::parse($campaign->end_date)->format('d-m-Y')) }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="Enter End Date">
                                @error('end_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('details') ? 'has-error' :'' }}">
                            <label for="details" class="col-sm-2 col-form-label">Details</label>
                            <div class="col-sm-10">
                                <textarea type="text" name="details" class="form-control" id="details" placeholder="Enter details">{{ old('details',$campaign->details) }}</textarea>
                                @error('details')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Existing Images</label>
                            <div class="col-sm-10">
                                @foreach ($campaign->images as $image)
                                    <div class="form-row align-items-center mb-2 existing-image-row" data-id="{{ $image->id }}">
                                        <div class="col-md-4">
                                            <input type="text" name="existing_file_names[{{ $image->id }}]" value="{{ $image->file_name }}" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <img src="{{ asset($image->file_path) }}" alt="Image" width="100">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-existing-image" data-id="{{ $image->id }}">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="images" class="col-sm-2 col-form-label">Upload Images</label>
                            <div class="col-sm-10">
                                <div id="image-upload-fields">
                                    <div class="form-row align-items-center mb-2">
                                        <div class="col-md-4">
                                            <input type="text" name="file_names[]" class="form-control" placeholder="Enter file name">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="file" name="images[]" class="form-control-file">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-image-field" style="display: none;">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="add-more-images" class="btn btn-success bg-gradient-success btn-sm mt-2">Add More</button>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('campaign.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
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
        $('#add-more-images').on('click', function() {
            const newField = `
                <div class="form-row align-items-center mb-2">
                    <div class="col-md-4">
                        <input type="text" name="file_names[]" class="form-control" placeholder="Enter file name">
                    </div>
                    <div class="col-md-6">
                        <input type="file" name="images[]" class="form-control-file">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-image-field">Remove</button>
                    </div>
                </div>
            `;
            $('#image-upload-fields').append(newField);
        });

        // Handle removing the image upload field
        $(document).on('click', '.remove-image-field', function() {
            $(this).closest('.form-row').remove();
        });
        $(document).on('click', '.remove-existing-image', function() {
            const imageId = $(this).data('id');
            $(this).closest('.existing-image-row').hide();
            $('<input>').attr({
                type: 'hidden',
                name: 'deleted_images[]',
                value: imageId
            }).appendTo('form');
        });
    });
</script>
@endsection

