<aside
    class="main-sidebar elevation-4  {{ auth()->user()->theme_mode == 1 ? 'sidebar-light-primary' : 'sidebar-dark-primary' }}">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}"
        class="brand-link {{ auth()->user()->theme_mode == 1 ? 'bg-white' : ' bg-dark' }} ">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="brand-image img-circle elevation-3"
            style="width:100px;height:50px;border-radius: 10%;">
        <span class="brand-text"><b>Admin</b>Panel</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-child-indent nav-flat " data-widget="treeview"
                role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php
                $subMenu = ['user.index', 'user.create', 'user.edit'];
                ?>
                @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin']))
                    <li class="nav-item">
                        <a href="{{ route('user.index') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-users-gear"></i>
                            <p>Users</p>
                        </a>
                    </li>
                @endif
                <?php
                $subMenu = ['divisional-user.index', 'divisional-user.create', 'divisional-user.edit'];
                ?>
                @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin']))
                    <li class="nav-item">
                        <a href="{{ route('divisional-user.index') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-users-gear"></i>
                            <p>Divisional Head</p>
                        </a>
                    </li>
                @endif
                @if (in_array(auth()->user()->role, ['Admin', 'SuperAdmin', 'Divisional Admin']))
                    <?php
                    $subMenu = ['tracking.live_location','tracking.location_history'];
                    ?>
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bullseye"></i>
                            <p>
                                Monitoring
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['tracking.live_location'];
                            ?>
                            <li class="nav-item">
                                <a href="{{ route('tracking.live_location') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i
                                        class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Live Location</p>
                                </a>
                            </li>
                            <?php
                            $subSubMenu = ['tracking.location_history'];
                            ?>
                            <li class="nav-item">
                                <a href="{{ route('tracking.location_history') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i
                                        class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Location History</p>
                                </a>
                            </li>
                            
                        </ul>
                    </li>
                @endif
                <?php
                $subMenu = ['unit.index', 'unit.create', 'unit.edit', 'product.index', 'product.create', 'product.edit', 'category.index', 'category.create', 'category.edit', 'sub-category.index', 'sub-category.create', 'sub-category.edit'];
                ?>
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Settings
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php
                        $subSubMenu = ['unit.index', 'unit.create', 'unit.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('unit.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                <i
                                    class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                <p>Units</p>
                            </a>
                        </li>
                        <?php
                        $subSubMenu = ['category.index', 'category.create', 'category.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('category.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                <i
                                    class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                <p>Category</p>
                            </a>
                        </li>
                        <?php
                        $subSubMenu = ['sub-category.index', 'sub-category.create', 'sub-category.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('sub-category.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                <i
                                    class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                <p>Sub Category</p>
                            </a>
                        </li>
                        <?php
                        $subSubMenu = ['product.index', 'product.create', 'product.edit'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('product.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                <i
                                    class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                <p>Products</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php
                $subMenu = ['sr.index', 'sr.create', 'sr.edit', 'doctor.index', 'doctor.create', 'doctor.edit'];
                ?>
                @if (in_array(auth()->user()->role, ['Admin', 'SuperAdmin', 'Divisional Admin']))
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                SR & Doctor
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['sr.index', 'sr.create', 'sr.edit'];
                            ?>
                            <li class="nav-item">
                                <a href="{{ route('sr.index') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i
                                        class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>SR</p>
                                </a>
                            </li>
                            <?php
                            $subSubMenu = ['doctor.index', 'doctor.create', 'doctor.edit'];
                            ?>

                            <li class="nav-item">
                                <a href="{{ route('doctor.index') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i
                                        class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Doctor</p>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif
                <?php
                $subMenu = ['farm.index', 'farm.create', 'farm.edit', 'farm-visit.index', 'farm-visit.create', 'farm-visit.edit'];
                ?>
                @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin', 'Doctor']))
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-md"></i>
                            <p>
                                Doctor Panel
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['farm.index', 'farm.create', 'farm.edit'];
                            ?>
                            @if (in_array(auth()->user()->role, ['Doctor', 'Admin','Divisional Admin', 'SuperAdmin']))
                                <li class="nav-item">
                                    <a href="{{ route('farm.index') }}"
                                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i
                                            class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Farm</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['farm-visit.index', 'farm-visit.create', 'farm-visit.edit'];
                            ?>
                            @if (in_array(auth()->user()->role, ['Doctor', 'Admin','Divisional Admin', 'SuperAdmin']))
                                <li class="nav-item">
                                    <a href="{{ route('farm-visit.index') }}"
                                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i
                                            class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Farm Visit</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                <?php
                $subMenu = [
                    'sales-order.index', 'sales-order.create', 'sales-order.edit', 'sales-order.details', 'sales-order.day_close', 'sales-order.customer_sale_details', 'sales-order.customer_sale_entry', 
                    'sales-order.customer_damage_product_entry', 'sales-order.final_details', 'client-payments', 'sales-order.details', 'client.index', 'client.create', 'client.edit',
                    'requisition-order.index', 'requisition-order.create', 'requisition-order.edit', 'requisition-order.details', 'requisition-order.customer_sale_details', 'requisition-order.customer_sale_entry', 'requisition-order.final_details',
                    'requisition-order.make_order','sales-order.invoice'
                ];
                ?>
                @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin', 'SR']))
                    <li
                        class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-shopping-cart"></i>
                            <p>
                                Order Collection
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['client.index', 'client.create', 'client.edit'];
                            ?>
                            @if (in_array(auth()->user()->role, ['SR', 'Admin','Divisional Admin', 'SuperAdmin']))
                                <li class="nav-item">
                                    <a href="{{ route('client.index') }}"
                                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i
                                            class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Client List</p>
                                    </a>
                                </li>
                                <?php
                                $subSubMenu = ['requisition-order.index', 'requisition-order.create', 'requisition-order.edit', 'requisition-order.details', 'requisition-order.customer_sale_details', 'requisition-order.customer_sale_entry', 'requisition-order.final_details'];
                                ?>
                                <li class="nav-item">
                                    <a href="{{ route('requisition-order.index') }}"
                                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="fa fa-history nav-icon"></i>
                                        <p>Requisition Order</p>
                                    </a>
                                </li>
                                <?php
                                $subSubMenu = ['sales-order.create'];
                                ?>
                                {{-- <li class="nav-item">
                                    <a href="{{ route('sales-order.create') }}"
                                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="fa fa-plus nav-icon"></i>
                                        <p>Order Create</p>
                                    </a>
                                </li> --}}
                                <?php
                                $subSubMenu = ['sales-order.index', 'sales-order.edit', 'sales-order.day_close', 'sales-order.customer_sale_details', 'sales-order.customer_sale_entry', 'sales-order.customer_damage_product_entry', 'sales-order.final_details', 'sales-order.invoice'];
                                ?>
                                <li class="nav-item">
                                    <a href="{{ route('sales-order.index') }}"
                                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="fa fa-history nav-icon"></i>
                                        <p>Order List</p>
                                    </a>
                                </li>
                                <?php
                                $subSubMenu = ['client-payments', 'sales-order.details'];
                                ?>
                                <li class="nav-item">
                                    <a href="{{ route('client-payments') }}"
                                        class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="fa fa-history nav-icon"></i>
                                        <p>Client Payment</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                <?php
                $subMenu = ['campaign.index', 'campaign.create', 'campaign.edit', 'campaign.show'];
                ?>
                {{-- @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin'])) --}}
                    <li class="nav-item">
                        <a href="{{ route('campaign.index') }}"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bullhorn"></i>
                            <p>Campaign</p>
                        </a>
                    </li>
                {{-- @endif --}}
                <?php
                $subMenu = ['leave-types.index', 'leave-types.create', 'leave-types.edit', 'leave.index', 'leave.create', 'leave.edit'];
                ?>
                @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin', 'SR']))
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>
                                Leave
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['leave-types.index', 'leave-types.create', 'leave-types.edit'];
                            ?>
                            <li class="nav-item">
                                <a href="{{ route('leave-types.index') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i
                                        class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Leave Types</p>
                                </a>
                            </li>
                            <?php
                            $subSubMenu = ['leave.index', 'leave.create', 'leave.edit'];
                            ?>
                            <li class="nav-item">
                                <a href="{{ route('leave.index') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i
                                        class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Leave</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                <?php
                $subMenu = ['task.index', 'task.create', 'task.edit','task.details'];
                ?>
                <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>
                            Task Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php
                        $subSubMenu = ['task.index', 'task.create', 'task.edit','task.details'];
                        ?>
                        <li class="nav-item">
                            <a href="{{ route('task.index') }}"
                                class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                <i
                                    class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                <p>Task List</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin', 'SR']))
                    <?php
                    $subMenu = ['report.sales_report', 'sales_report_tracking'];
                    ?>
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-receipt"></i>
                            <p>
                                Reports
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('report.sales_report') }}"
                                    class="nav-link {{ Route::currentRouteName() == 'report.sales_report' ? 'active' : '' }}">
                                    <i
                                        class="far  {{ Route::currentRouteName() == 'report.sales_report' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Sales Report</p>
                                </a>
                            </li>
                            @if (in_array(auth()->user()->role, ['Admin','Divisional Admin', 'SuperAdmin']))
                                {{-- <li class="nav-item">
                                    <a href="{{ route('sales_report_tracking') }}"
                                        class="nav-link {{ Route::currentRouteName() == 'sales_report_tracking' ? 'active' : '' }}">
                                        <i
                                            class="far  {{ Route::currentRouteName() == 'sales_report_tracking' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Tacking Report</p>
                                    </a>
                                </li> --}}
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->

</aside>
