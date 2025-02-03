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
                <form enctype="multipart/form-data" action="{{ route('farm-visit.store') }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        @if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin']))
                        <div class="form-group row {{ $errors->has('doctor') ? 'has-error' :'' }}">
                            <label for="doctor" class="col-sm-2 col-form-label">Doctor<span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="doctor" id="doctor" class="form-control select2">
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doctor)
                                    <option {{ old('doctor') == $doctor->id ? 'selected' : '' }} value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                                @error('doctor')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @else
                        <div class="form-group row {{ $errors->has('doctor') ? 'has-error' :'' }}">
                            <label for="doctor" class="col-sm-2 col-form-label">Doctor<span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" readonly value="{{ old('doctor',$doctors->name) }}" name="doctor_name" class="form-control" id="doctor_name" placeholder="Enter Name">
                                <input type="hidden" readonly value="{{ old('doctor',$doctors->id) }}" name="doctor" class="form-control" id="doctor" placeholder="Enter Name">
                                @error('farm')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif
                        <div class="form-group row {{ $errors->has('farm') ? 'has-error' :'' }}">
                            <label for="farm" class="col-sm-2 col-form-label">Farm<span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="farm" id="farm" class="form-control select2">
                                    <option value="">Select Farm</option>
                                    @foreach($farms as $farm)
                                    <option {{ old('farm') == $farm->id ? 'selected' : '' }} value="{{ $farm->id }}">{{ $farm->name }}</option>
                                    @endforeach
                                </select>
                                @error('farm')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('reason') ? 'has-error' :'' }}">
                            <label for="reason" class="col-sm-2 col-form-label">Reason</label>
                            <div class="col-sm-10">
                                <textarea type="text" rows="5" name="reason" class="form-control" id="reason" placeholder="Enter reason">{{ old('reason') }}</textarea>
                                @error('reason')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('photo') ? 'has-error' : '' }}">
                            <input type="hidden" id="employee_photo_src" name="employee_photo_src">
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                            <div class="row" style="margin-top:8px;">
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-5">
                                    <video id="video" width="100%" height="350px" autoplay></video>
                                </div>
                                <div class="col-sm-5">
                                    <canvas id="canvas" width="100%" height="350px"></canvas>
                                </div>
                                <div class="col-sm-2"></div>
                                <div class="col-sm-5 capture-button" style="display: none">
                                    <button type="button" class="btn btn-primary btn-sm" id="click-photo">Capture
                                        Photo</button>
                                </div>
                                <div class="col-sm-5 submit-button" style="display: none">
                                    <button class="btn btn-primary btn-lg">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    {{-- <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('client.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div> --}}
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
<script type="text/javascript">
    let video = document.querySelector("#video");
    let click_button = document.querySelector("#click-photo");
    let canvas = document.querySelector("#canvas");
    canvas.width = 400;
    canvas.height = 350;
    // captaure();
    // async function captaure() {
    //     let stream = await navigator.mediaDevices.getUserMedia({
    //         video: true,
    //         audio: false
    //     });
    //     video.srcObject = stream;
    // }
    openCameraAndLocation();
    async function openCameraAndLocation() {
        try {
            // Request camera access
            let stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            video.srcObject = stream;
            console.log("Camera is active.");

            // Request location access
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    $(".capture-button").show();
                    console.log("Location captured:", position.coords);
                    $("#latitude").val(position.coords.latitude);
                    $("#longitude").val(position.coords.longitude);
                    // alert(`Latitude: ${position.coords.latitude}, Longitude: ${position.coords.longitude}`);
                },
                (error) => {
                    if (error.code === error.PERMISSION_DENIED) {
                        alert("Location access is required to proceed. Please enable location permissions.");
                    } else {
                        alert("Unable to fetch location. Please check your device settings.");
                    }
                    console.error("Error getting location:", error);
                }
            );
        } catch (error) {
            if (error.name === "NotAllowedError") {
                alert("Camera access is required to proceed. Please allow camera permissions.");
            } else {
                alert("Unable to access the camera. Please check your device settings.");
            }
            console.error("Error accessing camera:", error);
        }
    }

    // Call the function to request both camera and location
    openCameraAndLocation();

    click_button.addEventListener('click', function() {
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        let image_data_url = canvas.toDataURL('image/jpeg');

        $("#img_src").attr("src", image_data_url);
        $("#employee_photo").attr("src", image_data_url);
        $("#employee_photo_src").val(image_data_url);

        $(".submit-button").show();
        console.log(image_data_url);
    });
</script>
@endsection

