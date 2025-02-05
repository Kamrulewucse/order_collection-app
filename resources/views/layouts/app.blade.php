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
@yield('script')
<!-- AdminLTE App -->
<script src="{{ asset('themes/backend/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
