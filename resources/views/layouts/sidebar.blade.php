<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img style="width: 200px;" src="logo.png" alt="logo">
            </span>
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

        <!-- Role -->
        <li class="menu-item {{ request()->routeIs('role') ? 'active' : '' }}">
            <a href="{{ route('role') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield"></i>
                <div>Role</div>
            </a>
        </li>

        <!-- Permission -->
        <li class="menu-item {{ request()->routeIs('permission') ? 'active' : '' }}">
            <a href="{{ route('permission') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-lock-alt"></i>
                <div>Permission</div>
            </a>
        </li>

        <!-- Users -->
        <li class="menu-item {{ request()->routeIs('user') ? 'active' : '' }}">
            <a href="{{ route('user') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div>Users</div>
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

        <!-- Order -->
        <li class="menu-item {{ request()->routeIs('order') ? 'active' : '' }}">
            <a href="{{ route('order') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div>Order</div>
            </a>
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

                <li class="menu-item {{ request()->routeIs('vendor.info') ? 'active' : '' }}">
                    <a href="{{ route('vendor.info') }}" class="menu-link">
                        <i class="bx bx-package me-2"></i>
                        <div>Product</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Notification -->
        <li class="menu-item {{ request()->routeIs('notification') ? 'active' : '' }}">
            <a href="{{ route('notification') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div>Notification</div>
            </a>
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

    </ul>
</aside>
