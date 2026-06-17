<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img style="width: 200px;" src="{{ asset('gharma ecommerce.svg') }}" alt="logo"> </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <!-- Favicon -->
        <li class="menu-item {{ request()->routeIs('favicon') ? 'active' : '' }}">
            <a href="{{ route('favicon') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-image-alt"></i>
                <div>Favicon</div>
            </a>
        </li>

        <!-- Manager -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Manager View</span>
        </li>

        <!-- Organization -->
        <li class="menu-item {{ request()->routeIs('organization') ? 'active' : '' }}">
            <a href="{{ route('organization') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-buildings"></i>
                <div>Organization</div>
            </a>
        </li>

        <!-- Organization Access -->
        <li class="menu-item {{ request()->routeIs('organization.access') ? 'active' : '' }}">
            <a href="{{ route('organization.access') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-lock-alt"></i>
                <div>Organization Access</div>
            </a>
        </li>


        <!-- Organization Roles -->
        <li class="menu-item {{ request()->routeIs('organization.access') ? 'active' : '' }}">
            <a href="{{ route('organization.role') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield"></i>
                <div>Organization Roles</div>
            </a>
        </li>

        {{-- Driver --}}
        <!-- Permission -->
        <li class="menu-item {{ request()->routeIs('permission') ? 'active' : '' }}">
            <a href="{{ route('permission') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-lock-alt"></i>
                <div>Permission</div>
            </a>
        </li>

        <!-- Role -->
        <li class="menu-item {{ request()->routeIs('role') ? 'active' : '' }}">
            <a href="{{ route('role') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield"></i>
                <div>Role</div>
            </a>
        </li>

        <!-- Home Tab -->
        <li class="menu-item {{ request()->routeIs('hometab') ? 'active' : '' }}">
            <a href="{{ route('hometab') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Home Tab</div>
            </a>
        </li>
        <!-- Store -->
        <li class="menu-item {{ request()->routeIs('store') ? 'active' : '' }}">
            <a href="{{ route('store') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div>Store</div>
            </a>
        </li>

        <!-- Category / Brand / Item -->
        <li
            class="menu-item {{ request()->routeIs('category') || request()->routeIs('brand') || request()->routeIs('item') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-category"></i>
                <div>Category / Brand / Item</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('category') ? 'active' : '' }}">
                    <a href="{{ route('category') }}" class="menu-link">
                        <i class="bx bx-category-alt me-2"></i>
                        <div>Category</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('brand') ? 'active' : '' }}">
                    <a href="{{ route('brand') }}" class="menu-link">
                        <i class="bx bx-purchase-tag me-2"></i>
                        <div>Brand</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('item') ? 'active' : '' }}">
                    <a href="{{ route('item') }}" class="menu-link">
                        <i class="bx bx-box me-2"></i>
                        <div>Item</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Users -->
        <li class="menu-item {{ request()->routeIs('user') ? 'active' : '' }}">
            <a href="{{ route('user') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div>Users</div>
            </a>
        </li>

        <!-- Assign Drive -->
        <li
            class="menu-item {{ request()->routeIs('driver') || request()->routeIs('assign.driver') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-car"></i>
                <div>Drive</div>
            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('driver') ? 'active' : '' }}">
                    <a href="{{ route('driver') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Driver List</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('assign.driver') ? 'active' : '' }}">
                    <a href="{{ route('assign.driver') }}" class="menu-link">
                        <i class="bx bx-package me-2"></i>
                        <div>Assign Drive</div>
                    </a>
                </li>


            </ul>
        </li>


        <!-- Inventory -->
        <li class="menu-item {{ request()->routeIs('inventory') ? 'active' : '' }}">
            <a href="{{ route('inventory') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div>Inventory</div>
            </a>
        </li>

        <!-- Vendor -->
        <li class="menu-item {{ request()->routeIs('vendor.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div>Vendor</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('vendor.info') ? 'active' : '' }}">
                    <a href="{{ route('vendor.info') }}" class="menu-link">
                        <i class="bx bx-id-card me-2"></i>
                        <div>Vendor Info</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Price Management -->
        <li
            class="menu-item {{ request()->routeIs('retailer') || request()->routeIs('wholesaler') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dollar-circle"></i>
                <div>Price Management</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('retailer') ? 'active' : '' }}">
                    <a href="{{ route('retailer') }}" class="menu-link">
                        <i class="bx bx-user-pin me-2"></i>
                        <div>Retailer</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('wholesaler') ? 'active' : '' }}">
                    <a href="{{ route('wholesaler') }}" class="menu-link">
                        <i class="bx bx-network-chart me-2"></i>
                        <div>Wholesaler</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Return Refund poilcy -->
         <li class="menu-item">
    <a href="{{ route('refunds.policy') }}" class="menu-link">
        <i class="menu-icon bx bx-shield"></i>
        <div>Return & Refund Policy</div>
    </a>
</li>


        <!-- Home Tab -->
        <!-- <li class="menu-item {{ request()->routeIs('hometab') ? 'active' : '' }}">
            <a href="{{ route('hometab') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Home Tab</div>
            </a>
        </li> -->


        <!-- Discount / Loyalty -->
        <li
            class="menu-item {{ request()->routeIs('loyalty') || request()->routeIs('discount') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-purchase-tag"></i>
                <div>Discount / Loyalty/</div>
            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('discount') ? 'active' : '' }}">
                    <a href="{{ route('discount') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Discount</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('loyalty') ? 'active' : '' }}">
                    <a href="{{ route('loyalty') }}" class="menu-link">
                        <i class="bx bx-package me-2"></i>
                        <div>Loyalty</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('coupon') ? 'active' : '' }}">
                    <a href="{{ route('coupon') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Coupon</div>
                    </a>
                </li>


            </ul>
        </li>


        <!-- Order -->
        <li class="menu-item {{ request()->routeIs('order') ? 'active' : '' }}">
            <a href="{{ route('order') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div>Order</div>
            </a>
        </li>

        {{-- Invoice --}}
        <li class="menu-item {{ request()->routeIs('invoice') ? 'active' : '' }}">
            <a href="{{ route('invoice') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-receipt"></i>
                <div>Invoice</div>
            </a>
        </li>



        <!-- Refund -->
        <li class="menu-item {{ request()->routeIs('refund') ? 'active' : '' }}">
            <a href="{{ route('refund') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-revision"></i>
                <div>Refund</div>
            </a>
        </li>

        <!-- Report -->
        <li
            class="menu-item {{ request()->routeIs('report.sales') || request()->routeIs('inventory.report') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-line-chart"></i>
                <div>Report</div>
            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('report.sales') ? 'active' : '' }}">
                    <a href="{{ route('report.sales') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Sales Report</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('inventory.report') ? 'active' : '' }}">
                    <a href="{{ route('inventory.report') }}" class="menu-link">
                        <i class="bx bx-package me-2"></i>
                        <div>Inventory Report</div>
                    </a>
                </li>


            </ul>
        </li>

        <!-- Heatmap -->
        <li class="menu-item {{ request()->routeIs('admin.heatmap.index') ? 'active' : '' }}">
            <a href="{{ route('admin.heatmap.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-map-alt"></i>
                <div>Heatmap</div>
            </a>
        </li>


        <!-- Notification -->
        <li class="menu-item {{ request()->routeIs('notification') ? 'active' : '' }}">
            <a href="{{ route('notification') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div>Notification</div>
            </a>
        </li>

    </ul>
</aside>