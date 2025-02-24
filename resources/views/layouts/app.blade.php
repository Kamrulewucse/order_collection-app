<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>
    @include('layouts.partial.__favicon')
    @include('layouts.partial.__styles')
    @yield('style')
</head>
<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed  {{ auth()->user()->theme_mode == 1 ? ' ' : 'dark-mode' }}">
<div class="wrapper">
    <!-- Navbar -->
@include('layouts.partial.__navbar')
<!-- /.navbar -->
    <!-- Main Sidebar Container -->
@include('layouts.partial.__main_sidebar')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="position: relative;">
        <div id="preloader">
            <div id="loader"></div>
        </div>
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    @if(Route::currentRouteName() == 'dashboard')
                        <div class="col-8 col-md-10">
                            <h1 class="m-0">@yield('title')</h1>
                        </div>
                        <div class="col-4 col-md-2">
                            <form action="{{ route('dashboard') }}" id="dashboard_year_form" method="get">
                                <div id="report-range" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                                <input type="hidden" name="start_date" id="start_date" value="">
                                <input type="hidden" name="end_date" id="end_date" value="">
                            </form>
                        </div>
                    @else
                        <div class="col-sm-12">
                            <h1 class="m-0">@yield('title')</h1>
                        </div>
                    @endif
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Main Footer -->
    @include('layouts.partial.__footer')
</div>
<!-- ./wrapper -->
@include('layouts.partial.__media_files')
<!-- REQUIRED SCRIPTS -->
@include('layouts.partial.__scripts')
<script>
    function isInWebView() {
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;
        return /wv|WebView/i.test(userAgent); // Check for WebView
    }
    document.addEventListener("DOMContentLoaded", function () {
        if (isInWebView()) {
            console.log("Running in WebView, skipping geolocation tracking.");
            return;
        }
        if (!navigator.geolocation) {
            alert("Geolocation not supported");
            return;
        }

        navigator.permissions.query({ name: 'geolocation' }).then(function (result) {
            if (result.state === 'granted') {
                startTracking();
            } else if (result.state === 'prompt') {
                alert("Please allow location access to track your movement.");
                startTracking();
            } else {
                alert("Location access is denied. Please enable location access in your browser settings.");
            }
        });

        function startTracking() {
            const watchId = navigator.geolocation.watchPosition(
                (position) => {
                    if (position.coords.accuracy > 30) {
                        console.log("Low accuracy, skipping update");
                        return;
                    }
                    console.log("Updated Location:", position.coords.latitude, position.coords.longitude);
                    console.log("Accuracy:", position.coords.accuracy);

                    // Send to Laravel
                    fetch("{{ route('tracking.update_location') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        })
                    })
                    .then(response => response.json())
                    .then(data => console.log("Server Response:", data))
                    .catch(error => console.error("Error updating location:", error));
                },
                showError,
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 10000
                }
            );
        }

        function showError(error) {
            console.warn("Error getting location:", error.message);
            if (error.code === error.PERMISSION_DENIED) {
                alert("You need to allow location access.");
            }
        }
    });

    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon("{{ route('tracking.set_offline') }}", JSON.stringify({
            _token: "{{ csrf_token() }}",
        }));
    });

    function updateLocation(lat, lon, accuracy){
        let authId = '{{ auth()->user()->id }}';
        alert(accuracy);
        alert(lat);
        alert(lon);
    }
</script>
@yield('script')
<!-- AdminLTE App -->
<script src="{{ asset('themes/backend/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
