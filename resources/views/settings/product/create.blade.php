@extends('layouts.app')
@section('title','Product Create')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Product Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('product.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Product Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name') }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('type') ? 'has-error' :'' }}">
                            <label for="type" class="col-sm-2 col-form-label">Type <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="type" id="type" class="form-control select2">
                                    <option value="">Select Type</option>
                                    <option value="1" {{ old('type')=='1'?'selected':'' }}>Raw Item</option>
                                    <option value="2" {{ old('type')=='2'?'selected':'' }}>Finished Goods</option>
                                </select>
                                @error('type')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('category') ? 'has-error' :'' }}">
                            <label for="category" class="col-sm-2 col-form-label">Category <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="category" id="category" class="form-control select2">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option {{ old('category') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('sub_category') ? 'has-error' :'' }}">
                            <label for="sub_category" class="col-sm-2 col-form-label">Sub Category <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="sub_category_id" id="sub_category" class="form-control select2">
                                    <option value="">Select Sub Category</option>
                                </select>
                                @error('sub_category')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('unit') ? 'has-error' :'' }}">
                            <label for="unit" class="col-sm-2 col-form-label">Unit <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="unit" id="unit" class="form-control select2">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option {{ old('unit') == $unit->id ? 'selected' : '' }} value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('purchase_price') ? 'has-error' :'' }}">
                            <label for="purchase_price" class="col-sm-2 col-form-label">Purchase Price <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" step="any" value="{{ old('purchase_price',0) }}" name="purchase_price" class="form-control" id="purchase_price" placeholder="Enter Purchase Price">
                                @error('purchase_price')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('selling_price') ? 'has-error' :'' }}">
                            <label for="selling_price" class="col-sm-2 col-form-label">Selling Price <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" step="any" value="{{ old('selling_price',0) }}" name="selling_price" class="form-control" id="selling_price" placeholder="Enter Selling Price">
                                @error('selling_price')
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
                        <a href="{{ route('product.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
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
    $(document).ready(function () {
        const oldSubcategoryId = "{{ old('sub_category') }}";
        const oldCategoryId = "{{ old('category') }}";

        function loadSubcategories(categoryId, selectedSubcategoryId = null) {
            if (categoryId) {
                $.ajax({
                    url: "{{ route('get.subcategories', ':id') }}".replace(':id', categoryId),
                    type: 'GET',
                    success: function (data) {
                        $('#sub_category').empty().append('<option value="">Select Subcategory</option>');

                        if (data.length > 0) {
                            data.forEach(function (sub_category) {
                                $('#sub_category').append(
                                    `<option value="${sub_category.id}" ${
                                        selectedSubcategoryId == sub_category.id ? 'selected' : ''
                                    }>${sub_category.name}</option>`
                                );
                            });
                        } else {
                            alert('No subcategories available for the selected category.');
                        }
                    },
                    error: function () {
                        alert('Failed to load subcategories.');
                    }
                });
            }
        }

        // Load subcategories if old category is selected
        if (oldCategoryId) {
            loadSubcategories(oldCategoryId, oldSubcategoryId);
        }

        // Handle category change
        $('#category').change(function () {
            const categoryId = $(this).val();
            loadSubcategories(categoryId);
        });
    });
</script>
@endsection

