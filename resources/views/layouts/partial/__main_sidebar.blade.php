<aside
    class="main-sidebar elevation-4  {{ auth()->user()->theme_mode == 1 ? 'sidebar-light-primary' : 'sidebar-dark-primary' }}">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}"
       class="brand-link {{ auth()->user()->theme_mode == 1 ? 'bg-white' : ' bg-dark' }} ">
        <img src="{{ asset('img/logo.png') }}" alt="Logo"
             class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text"><b>Admin</b>Panel</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-child-indent nav-flat " data-widget="treeview"
                role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                @if(auth()->user()->can('dashboard'))
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}"
                           class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>

                    </li>
                @endif

                <?php
                $subMenu = ['user.index', 'user.create', 'user.edit'];
                ?>
                @if(auth()->user()->can('user'))
                    <li class="nav-item">
                        <a href="{{ route('user.index') }}"
                           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-users-gear"></i>
                            <p>Users</p>
                        </a>
                    </li>
                @endif

                <?php
                $subMenu = [
                    'unit.index', 'unit.create', 'unit.edit',
                    'brand.index', 'brand.create', 'brand.edit',
                    'supplier.index', 'supplier.create', 'supplier.edit',
                    'product.index', 'product.create', 'product.edit'
                ];
                ?>
                @if(auth()->user()->can('purchase_settings'))
                    {{-- <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
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
                            $subSubMenu = ['supplier.index', 'supplier.create', 'supplier.edit'];
                            ?>
                            @if(auth()->user()->can('supplier'))
                                <li class="nav-item">
                                    <a href="{{ route('supplier.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Company</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['unit.index', 'unit.create', 'unit.edit'];
                            ?>
                            @if(auth()->user()->can('product_unit'))
                                <li class="nav-item">
                                    <a href="{{ route('unit.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Units</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['brand.index', 'brand.create', 'brand.edit'];
                            ?>
                            @if(auth()->user()->can('brand'))
                                <li class="nav-item">
                                    <a href="{{ route('brand.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Brands</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['product.index', 'product.create', 'product.edit'];
                            ?>
                            @if(auth()->user()->can('product'))
                                <li class="nav-item">
                                    <a href="{{ route('product.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Products</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li> --}}
                @endif
                <?php
                $subMenu = ['purchase.index', 'purchase.create', 'purchase.edit', 'purchase.details'];
                ?>
                @if(auth()->user()->can('purchase'))
                    {{-- <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-open' : '' }}">
                        <a href="#"
                           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-shopping-bag"></i>
                            <p>
                                Purchase
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['purchase.create'];
                            ?>
                            @if(auth()->user()->can('purchase_create'))
                                <li class="nav-item">
                                    <a href="{{ route('purchase.create') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="fa fa-plus nav-icon"></i>
                                        <p>Purchase Create</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['purchase.index', 'purchase.edit', 'purchase.details'];
                            ?>
                            @if(auth()->user()->can('purchase_list'))
                                <li class="nav-item">
                                    <a href="{{ route('purchase.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="fa fa-history nav-icon"></i>
                                        <p>Purchase List</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li> --}}
                @endif
                <?php
                $subMenu = ['inventory.index', 'inventory.details'];
                ?>
                @if(auth()->user()->can('inventory'))
                    {{-- <li class="nav-item">
                        <a href="{{ route('inventory.index') }}"
                           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-warehouse"></i>
                            <p>Inventory</p>
                        </a>
                    </li> --}}
                @endif

                <?php
                $subMenu = [
                    'sr.index', 'sr.create', 'sr.edit','unit.index', 'unit.create', 'unit.edit',
                    'doctor.index', 'doctor.create', 'doctor.edit','product.index', 'product.create', 'product.edit',
                    'client.index', 'client.create', 'client.edit','category.index', 'category.create', 'category.edit',
                ];
                ?>
                @if(auth()->user()->can('distribution_settings'))
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
                            $subSubMenu = ['sr.index', 'sr.create', 'sr.edit'];
                            ?>
                            @if(auth()->user()->can('dsr'))
                                <li class="nav-item">
                                    <a href="{{ route('sr.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>SR</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['doctor.index', 'doctor.create', 'doctor.edit'];
                            ?>
                            @if(auth()->user()->can('dsr'))
                                <li class="nav-item">
                                    <a href="{{ route('doctor.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Doctor</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                                $subSubMenu = ['client.index', 'client.create', 'client.edit'];
                            ?>
                            @if(auth()->user()->can('customer'))
                                <li class="nav-item">
                                    <a href="{{ route('client.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Client</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['unit.index', 'unit.create', 'unit.edit'];
                            ?>
                            @if(auth()->user()->can('product_unit'))
                                <li class="nav-item">
                                    <a href="{{ route('unit.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Units</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['category.index', 'category.create', 'category.edit'];
                            ?>
                            <li class="nav-item">
                                <a href="{{ route('category.index') }}"
                                    class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                    <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                    <p>Category</p>
                                </a>
                            </li>
                            <?php
                            $subSubMenu = ['product.index', 'product.create', 'product.edit'];
                            ?>
                            @if(auth()->user()->can('product'))
                                <li class="nav-item">
                                    <a href="{{ route('product.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                        <i class="far  {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Products</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif


                <?php
                $subMenu = ['distribution.index','distribution.create','distribution.edit',
                    'distribution.details','distribution.day_close',
                    'distribution.customer_sale_details','distribution.customer_sale_entry',
                    'distribution.customer_damage_product_entry','distribution.final_details',
                    'customer-payments'
                ];
                ?>
                @if(auth()->user()->can('distribution'))
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) && request('type') == 1 ? 'menu-open' : '' }}">
                        <a href="#"
                           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) && request('type') == 1 ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-shopping-cart"></i>
                            <p>
                                Distribution Sales
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['distribution.create'];
                            ?>
                            @if(auth()->user()->can('distribution_create'))
                                <li class="nav-item">
                                    <a href="{{ route('distribution.create',['type'=>1]) }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('type') == 1 ? 'active' : '' }}">
                                        <i class="fa fa-plus nav-icon"></i>
                                        <p>Distribution Order Create</p>
                                    </a>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['distribution.index', 'distribution.edit',
                                'distribution.details','distribution.day_close',
                                'distribution.customer_sale_details',
                                'distribution.customer_sale_entry',
                                'distribution.customer_damage_product_entry',
                                'distribution.final_details'
                                ];
                            ?>
                            @if(auth()->user()->can('distribution_list'))
                                <li class="nav-item">
                                    <a href="{{ route('distribution.index',['type'=>1]) }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('type') == 1 ? 'active' : '' }}">
                                        <i class="fa fa-history nav-icon"></i>
                                        <p>Distribution Order List</p>
                                    </a>
                                </li>

                                    <?php
                                    $subSubMenu = ['customer-payments'
                                    ];
                                    ?>
                                <li class="nav-item">
                                    <a href="{{ route('customer-payments',['type'=>1]) }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('type') == 1 ? 'active' : '' }}">
                                        <i class="fa fa-history nav-icon"></i>
                                        <p>Customer payments</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif


                <?php
                $subMenu = [
                    'account-group.index', 'account-group.create', 'account-group.edit',
                    'account-head.index', 'account-head.create', 'account-head.edit',
                    'voucher.index', 'voucher.create', 'voucher.edit', 'voucher.details',
                    'cashbook',
                ];
                ?>
                @if(auth()->user()->can('accounts'))
                    <li class="nav-item {{ in_array(Route::currentRouteName(), $subMenu) ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#"
                           class="nav-link {{ in_array(Route::currentRouteName(), $subMenu) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calculator"></i>
                            <p>
                                Accounts
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php
                            $subSubMenu = ['account-group.index', 'account-group.create', 'account-group.edit'];
                            ?>

                            @if(auth()->user()->can('account_group'))
                                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'menu-open' : '' }}">
                                    <a href="#"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active custom-third-menu-bg' : '' }}">
                                        <i class="nav-icon fa fa-list"></i>
                                        <p>
                                            Account Groups
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview custom-third-layer">
                                        <?php
                                        $subSubMenu = ['account-group.create'];
                                        ?>
                                        @if(auth()->user()->can('account_group_create'))
                                            <li class="nav-item">
                                                <a href="{{ route('account-group.create') }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                                    <i class="fa fa-plus nav-icon"></i>
                                                    <p>Add New</p>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $subSubMenu = ['account-group.index','account-group.edit'];
                                        ?>
                                        @if(auth()->user()->can('account_group'))
                                            <li class="nav-item">
                                                <a href="{{ route('account-group.index') }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
                                                    <i class="fa fa-history nav-icon"></i>
                                                    <p>Lists</p>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif


                            <?php
                            $subSubMenu = ['account-head.index', 'account-head.create', 'account-head.edit'];
                            ?>
                            @if(auth()->user()->can('payment_modes'))
                                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu)  && request('payment_mode') != 0  ? 'menu-open' : '' }}">
                                    <a href="#"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu)  && request('payment_mode') != 0  ? 'active custom-third-menu-bg' : '' }}">
                                        <i class="nav-icon fa fa-list-alt"></i>
                                        <p>
                                            Payment Modes
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview custom-third-layer">
                                        <?php
                                        $subSubMenu = ['account-head.create'];
                                        ?>
                                        @if(auth()->user()->can('payment_modes_create'))
                                            <li class="nav-item">
                                                <a href="{{ route('account-head.create',['payment_mode'=>1]) }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') != 0 ? 'active' : '' }}">
                                                    <i class="fa fa-plus nav-icon"></i>
                                                    <p>Add New</p>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $subSubMenu = ['account-head.index','account-head.edit'];
                                        ?>
                                        @if(auth()->user()->can('payment_modes'))
                                            <li class="nav-item">
                                                <a href="{{ route('account-head.index',['payment_mode'=>1]) }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') != 0 ? 'active' : '' }}">
                                                    <i class="fa fa-history nav-icon"></i>
                                                    <p>Lists</p>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                            <?php
                                $subSubMenu = ['account-head.index', 'account-head.create', 'account-head.edit'];
                            ?>
                            @if(auth()->user()->can('account_head'))
                                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0  ? 'menu-open' : '' }}">
                                    <a href="#"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0  ? 'active custom-third-menu-bg' : '' }}">
                                        <i class="nav-icon fa fa-list-ol"></i>
                                        <p>
                                            Chart of Accounts
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview custom-third-layer">
                                        <?php
                                        $subSubMenu = ['account-head.create'];
                                        ?>
                                        @if(auth()->user()->can('account_head_create'))
                                            <li class="nav-item">
                                                <a href="{{ route('account-head.create',['payment_mode'=>0]) }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0 ? 'active' : '' }}">
                                                    <i class="fa fa-plus nav-icon"></i>
                                                    <p>Add New</p>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $subSubMenu = ['account-head.index','account-head.edit'];
                                        ?>
                                       @if(auth()->user()->can('account_head'))
                                        <li class="nav-item">
                                            <a href="{{ route('account-head.index',['payment_mode'=>0]) }}"
                                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('payment_mode') == 0 ? 'active' : '' }}">
                                                <i class="fa fa-history nav-icon"></i>
                                                <p>Lists</p>
                                            </a>
                                        </li>
                                       @endif
                                    </ul>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
                            ?>
                            @if(auth()->user()->can('payment_voucher'))
                                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER  ? 'menu-open' : '' }}">
                                    <a href="#"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                                        <i class="nav-icon fa-solid fa-book"></i>
                                        <p>
                                            Payment Voucher
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview custom-third-layer">
                                        <?php
                                        $subSubMenu = ['voucher.create'];
                                        ?>
                                        @if(auth()->user()->can('payment_voucher'))
                                            <li class="nav-item">
                                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$PAYMENT_VOUCHER]) }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER ? 'active' : '' }}">
                                                    <i class="fa fa-plus nav-icon"></i>
                                                    <p>Add New</p>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $subSubMenu = ['voucher.index', 'voucher.details',
                                            'voucher.edit'];
                                        ?>
                                        <li class="nav-item">
                                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$PAYMENT_VOUCHER]) }}"
                                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER ? 'active' : '' }}">
                                                <i class="fa fa-history nav-icon"></i>
                                                <p>Lists</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
                            ?>
                            @if(auth()->user()->can('receipt_voucher'))
                                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER  ? 'menu-open' : '' }}">
                                    <a href="#"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                                        <i class="nav-icon fa-solid fa-book"></i>
                                        <p>
                                            Receipt Voucher
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview custom-third-layer">
                                        <?php
                                        $subSubMenu = ['voucher.create'];
                                        ?>
                                        @if(auth()->user()->can('receipt_voucher_create'))
                                            <li class="nav-item">
                                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$COLLECTION_VOUCHER]) }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER ? 'active' : '' }}">
                                                    <i class="fa fa-plus nav-icon"></i>
                                                    <p>Add New</p>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $subSubMenu = ['voucher.index', 'voucher.details',
                                            'voucher.edit'];
                                        ?>
                                        <li class="nav-item">
                                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$COLLECTION_VOUCHER]) }}"
                                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER ? 'active' : '' }}">
                                                <i class="fa fa-history nav-icon"></i>
                                                <p>Lists</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                            @endif
                            <?php
                            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
                            ?>
                            @if(auth()->user()->can('contra_voucher'))
                                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER  ? 'menu-open' : '' }}">
                                    <a href="#"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                                        <i class="nav-icon fa-solid fa-book"></i>
                                        <p>
                                            Contra Voucher
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview custom-third-layer">
                                        <?php
                                        $subSubMenu = ['voucher.create'];
                                        ?>
                                        @if(auth()->user()->can('contra_voucher_create'))
                                            <li class="nav-item">
                                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$CONTRA_VOUCHER]) }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER ? 'active' : '' }}">
                                                    <i class="fa fa-plus nav-icon"></i>
                                                    <p>Add New</p>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $subSubMenu = ['voucher.index', 'voucher.details',
                                            'voucher.edit'];
                                        ?>
                                        <li class="nav-item">
                                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$CONTRA_VOUCHER]) }}"
                                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$CONTRA_VOUCHER ? 'active' : '' }}">
                                                <i class="fa fa-history nav-icon"></i>
                                                <p>Lists</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                            <?php
                            $subSubMenu = ['voucher.index', 'voucher.details', 'voucher.create', 'voucher.edit'];
                            ?>
                            @if(auth()->user()->can('journal_voucher'))
                                <li class="nav-item {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER  ? 'menu-open' : '' }}">
                                    <a href="#"
                                       class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER  ? 'active custom-third-menu-bg' : '' }}">
                                        <i class="nav-icon fa-solid fa-book"></i>
                                        <p>
                                            Journal Voucher
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview custom-third-layer">
                                        <?php
                                        $subSubMenu = ['voucher.create'];
                                        ?>
                                        @if(auth()->user()->can('journal_voucher_create'))
                                            <li class="nav-item">
                                                <a href="{{ route('voucher.create',['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER]) }}"
                                                   class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER ? 'active' : '' }}">
                                                    <i class="fa fa-plus nav-icon"></i>
                                                    <p>Add New</p>
                                                </a>
                                            </li>
                                        @endif
                                        <?php
                                        $subSubMenu = ['voucher.index', 'voucher.details',
                                            'voucher.edit'];
                                        ?>
                                        <li class="nav-item">
                                            <a href="{{ route('voucher.index',['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER]) }}"
                                               class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) && request('voucher_type') == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER ? 'active' : '' }}">
                                                <i class="fa fa-history nav-icon"></i>
                                                <p>Lists</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                            @endif
                        </ul>
                    </li>
                @endif

                <?php
                $subMenu = [

                    'report.receipt_and_payment',
                    'report.sales_report',
                    'report.inventory_in',
                    'report.inventory_out',
                    'report.sales-vs-payments',
                    'report.payment-vs-product-received',
                    'report.cash-and-stock',
                    'report.sales_due',
                    'report.ledger',
                    'report.trial_balance',
                    'report.income_statement',
                    'report.balance_sheet',
                ];
                ?>
                @if(auth()->user()->can('reports'))
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
                            @if(auth()->user()->can('receipt_and_payment'))
                                <li class="nav-item">
                                    <a href="{{ route('report.sales_report') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.sales_report' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.sales_report' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Sales Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.inventory_in') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.inventory_in' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.inventory_in' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Inventory In Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.inventory_out') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.inventory_out' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.inventory_out' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Inventory Out Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.sales-vs-payments') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.sales-vs-payments' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.sales-vs-payments' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Sales Vs Payment</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.payment-vs-product-received') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.payment-vs-product-received' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.payment-vs-product-received' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Payment Vs Product Received</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.cash-and-stock') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.cash-and-stock' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.cash-and-stock' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Cash & Stock</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.sales_due') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.sales_due' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.sales_due' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Sales Pending Due Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.receipt_and_payment') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.receipt_and_payment' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.receipt_and_payment' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Receipt & Payment</p>
                                    </a>
                                </li>
                            @endif

                            @if(auth()->user()->can('ledger'))
                                <li class="nav-item">
                                    <a href="{{ route('report.ledger') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.ledger' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.ledger' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Ledger</p>
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->can('trial_balance'))
                                <li class="nav-item">
                                    <a href="{{ route('report.trial_balance') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.trial_balance' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.trial_balance' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Trial Balance</p>
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->can('income_statement'))
                                <li class="nav-item">
                                    <a href="{{ route('report.income_statement') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.income_statement' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.income_statement' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Income Statement</p>
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->can('balance_sheet'))
                                <li class="nav-item">
                                    <a href="{{ route('report.balance_sheet') }}"
                                       class="nav-link {{ Route::currentRouteName() == 'report.balance_sheet' ? 'active' : '' }}">
                                        <i class="far  {{ Route::currentRouteName() == 'report.balance_sheet' ? 'fa-check-circle' : 'fa-circle' }} nav-icon"></i>
                                        <p>Balance Sheet</p>
                                    </a>
                                </li>
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
