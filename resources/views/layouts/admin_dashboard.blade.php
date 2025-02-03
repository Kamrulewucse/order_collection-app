<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>
    @include('layouts.partial.__favicon')
    @include('layouts.partial.__styles')
    <style>
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-footer, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .main-header {
            margin-left: 0px;
        }
        .card {
            box-shadow: none;
            background-color: transparent;
        }
        .module.row {
            margin-right: 200px;
            margin-left: 200px;
        }
        .o_app_icon {
            width: 100px;
            aspect-ratio: 1;
            padding: 10px;
            background-color: white;
            object-fit: cover;
            transform-origin: center bottom;
            transition: box-shadow ease-in 0.1s, transform ease-in 0.1s;
            box-shadow: var(--AppSwitcherIcon-inset-shadow, inset 0 0 0 1px rgba(0, 0, 0, 0.2)), 0 1px 1px rgba(0, 0, 0, 0.02), 0 2px 2px rgba(0, 0, 0, 0.02), 0 4px 4px rgba(0, 0, 0, 0.02), 0 8px 8px rgba(0, 0, 0, 0.02), 0 16px 16px rgba(0, 0, 0, 0.02);
            border-radius: 5px;
        }
    </style>

</head>
<body class="layout-fixed layout-navbar-fixed layout-footer-fixed text-sm sidebar-mini  {{ auth()->user()->theme_mode == 1 ? ' ' : 'dark-mode' }}">
<div class="wrapper">
    <!-- Navbar -->
@include('layouts.partial.__admin_navbar')
<!-- /.navbar -->

<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="position: relative;">
        <div id="preloader">
            <div id="loader"></div>
        </div>
        <div class="content" style="padding-top: 50px;">
            <div class="container">
                <div class="row module">
                    <div class="col-xl-3 col-md-3 col-xs-4 col-6">
                        <div class="card">
                            <div class="card-block">
                                <a href="{{ route('dashboard_dashboard') }}" id="adminDashboard">
                                    <div class="row align-items-center">
                                        <div class="col-12 text-center">
                                            <img class="o_app_icon" src="{{ asset('images/dashboard.png') }}">
                                            <p>Admin</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-3 col-xs-4 col-6">
                        <div class="card">
                            <div class="card-block">
                                <a href="{{ route('dashboard_dashboard') }}" id="srDashboard">
                                    <div class="row align-items-center">
                                        <div class="col-12 text-center">
                                            <img class="o_app_icon" src="{{ asset('images/sale.png') }}">
                                            <p>SR</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-3 col-xs-4 col-6">
                        <div class="card">
                            <div class="card-block">
                                <a href="{{ route('dashboard_dashboard') }}" id="doctorDashboard">
                                    <div class="row align-items-center">
                                        <div class="col-12 text-center">
                                            <img class="o_app_icon" src="{{ asset('images/doctor.png') }}">
                                            <p>Doctor</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Main Footer -->

 {{-- @include('layouts.partial.__footer') --}}

</div>
<!-- ./wrapper -->
@include('layouts.partial.__media_files')
<!-- REQUIRED SCRIPTS -->
@include('layouts.partial.__scripts')
@yield('script')
<script src="{{ asset('themes/backend/plugins/pace-progress/pace.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('themes/backend/dist/js/adminlte.min.js') }}"></script>
<script>
    // Implement scroll event for the specific section
    $('.notification-area-custom').scroll(function () {
        // Check if we are near the bottom of the scrollable section
        if (
            $('.notification-area-custom').scrollTop() + $('.notification-area-custom').innerHeight() >=
            $('.notification-area-custom')[0].scrollHeight - 100
        ) {
            let initSkip = 10;
            let notification_skip = parseFloat($('#notification_skip').val());
            notification_skip = (isNaN(notification_skip) || notification_skip < 0) ? 0 : notification_skip;
            let skip = notification_skip + initSkip;
            loadDataAndNotify(skip);
            $('#notification_skip').val(skip)
        }
    });
</script>
<script>
    $(document).ready(function() {
        $('#adminDashboard').on('click', function(event) {
            event.preventDefault();
            var userRole = "{{ auth()->user()->role }}";

            if (userRole === 'Admin' || userRole === 'SuperAdmin') {
                window.location.href = $(this).attr('href');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to access this page.',
                    confirmButtonText: 'OK'
                });
            }
        });
        $('#srDashboard').on('click', function(event) {
            event.preventDefault();
            var userRole = "{{ auth()->user()->role }}";

            if (userRole === 'Admin' || userRole === 'SuperAdmin'|| userRole === 'SR') {
                window.location.href = $(this).attr('href');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to access this page.',
                    confirmButtonText: 'OK'
                });
            }
        });
        $('#doctorDashboard').on('click', function(event) {
            event.preventDefault();
            var userRole = "{{ auth()->user()->role }}";

            if (userRole === 'Admin' || userRole === 'SuperAdmin'|| userRole === 'Doctor') {
                window.location.href = $(this).attr('href');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: 'You do not have permission to access this page.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
</script>
</body>
</html>
