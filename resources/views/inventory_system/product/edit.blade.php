@extends('layouts.app')
@section('title','Product Edit')
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
                <form enctype="multipart/form-data" action="{{ route('product.update',['product'=>$product->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('category') ? 'has-error' :'' }}">
                            <label for="category" class="col-sm-2 col-form-label">Category <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="category" id="category" class="form-control select2">
                                    <option value="">Select Brand</option>
                                    @foreach($categories as $category)
                                        <option {{ old('category',$product->category_id) == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category')
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
                                        <option {{ old('unit',$product->unit_id) == $unit->id ? 'selected' : '' }} value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name',$product->name) }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('purchase_price') ? 'has-error' :'' }}">
                            <label for="purchase_price" class="col-sm-2 col-form-label">Purchase Price <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" step="any" value="{{ old('purchase_price',$product->purchase_price) }}" name="purchase_price" class="form-control" id="purchase_price" placeholder="Enter Purchase Price">
                                @error('purchase_price')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('selling_price') ? 'has-error' :'' }}">
                            <label for="selling_price" class="col-sm-2 col-form-label">Selling Price <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" step="any" value="{{ old('selling_price',$product->selling_price) }}" name="selling_price" class="form-control" id="selling_price" placeholder="Enter Selling Price">
                                @error('selling_price')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label class="col-sm-2 col-form-label">Status <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="icheck-success d-inline pull-right">
                                    <input  type="radio" id="active" name="status" value="1" {{ old('status',$product->status) == '1' ? 'checked' : '' }}>
                                    <label for="active">
                                        Active
                                    </label>
                                </div>
                                <div class="icheck-danger d-inline pull-right">
                                    <input type="radio" id="inactive" name="status" value="0" {{ old('status',$product->status) == '0' ? 'checked' : '' }}>
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
