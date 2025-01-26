<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title'); ?> | <?php echo e(config('app.name', 'Laravel')); ?></title>
    <?php echo $__env->make('layouts.partial.__favicon', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('layouts.partial.__styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('style'); ?>
</head>
<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed  <?php echo e(auth()->user()->theme_mode == 1 ? ' ' : 'dark-mode'); ?>">
<div class="wrapper">
    <!-- Navbar -->
<?php echo $__env->make('layouts.partial.__navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- /.navbar -->
    <!-- Main Sidebar Container -->
<?php echo $__env->make('layouts.partial.__main_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="position: relative;">
        <div id="preloader">
            <div id="loader"></div>
        </div>
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <?php if(Route::currentRouteName() == 'dashboard'): ?>
                        <div class="col-8 col-md-10">
                            <h1 class="m-0"><?php echo $__env->yieldContent('title'); ?></h1>
                        </div>

                        <?php if(auth()->user()->can('dashboard')): ?>
                        <div class="col-4 col-md-2">
                            <form action="<?php echo e(route('dashboard')); ?>" id="dashboard_year_form" method="get">
                                <div class="form-group mb-0">
                                    <select name="dashboard_year" class="form-control select2" id="dashboard_year">
                                        <?php for($i = 2023;$i <= date('Y');$i++): ?>
                                            <option <?php echo e(request('dashboard_year',date('Y')) == $i ? 'selected' : ''); ?> value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="col-sm-12">
                            <h1 class="m-0"><?php echo $__env->yieldContent('title'); ?></h1>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <?php echo $__env->yieldContent('content'); ?>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Main Footer -->
    <?php echo $__env->make('layouts.partial.__footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<!-- ./wrapper -->
<?php echo $__env->make('layouts.partial.__media_files', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- REQUIRED SCRIPTS -->
<?php echo $__env->make('layouts.partial.__scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->yieldContent('script'); ?>
<!-- AdminLTE App -->
<script src="<?php echo e(asset('themes/backend/dist/js/adminlte.min.js')); ?>"></script>
</body>
</html>
<?php /**PATH D:\wamp64\www\order-collection\resources\views/layouts/app.blade.php ENDPATH**/ ?>