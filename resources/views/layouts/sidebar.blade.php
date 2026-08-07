<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img style="width: 200px;"
                    src="{{ $orgLogo ? asset('storage/organizations/' . $orgLogo) : asset('gharma ecommerce.svg') }}"
                    alt="{{ $orgName ?? 'logo' }}">
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

        <!-- Favicon -->
        <!-- <li class="menu-item {{ request()->routeIs('favicon') ? 'active' : '' }}">
            <a href="{{ route('favicon') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-image-alt"></i>
                <div>Favicon</div>
            </a>
        </li> -->

        <!-- Manager -->
        @canany(['view.organization', 'view.organization-access', 'view.organization-role', 'view.permission', 'view.role'])
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Manager View</span>
        </li>
        @endcanany

        <!-- Organization -->
        @can('view.organization')
        <li class="menu-item {{ request()->routeIs('organization') ? 'active' : '' }}">
            <a href="{{ route('organization') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-buildings"></i>
                <div>Organization</div>
            </a>
        </li>
        @endcan

        <!-- Organization Access -->
        @can('view.organization-access')
        <li class="menu-item {{ request()->routeIs('organization.access') ? 'active' : '' }}">
            <a href="{{ route('organization.access') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-lock-alt"></i>
                <div>Organization Access</div>
            </a>
        </li>
        @endcan

        <!-- Organization Roles -->
        @can('view.organization-role')
        <li class="menu-item {{ request()->routeIs('organization.role') ? 'active' : '' }}">
            <a href="{{ route('organization.role') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield"></i>
                <div>Organization Roles</div>
            </a>
        </li>
        @endcan

        {{-- Driver --}}
        <!-- Permission -->
        @can('view.permission')
        <li class="menu-item {{ request()->routeIs('permission') ? 'active' : '' }}">
            <a href="{{ route('permission') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-lock-alt"></i>
                <div>Permission</div>
            </a>
        </li>
        @endcan

        <!-- Role -->
        @can('view.role')
        <li class="menu-item {{ request()->routeIs('role') ? 'active' : '' }}">
            <a href="{{ route('role') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield"></i>
                <div>Role</div>
            </a>
        </li>
        @endcan

        <!-- Fiscal Year -->
        @can('view.fiscalyear')
        <li class="menu-item {{ request()->routeIs('fiscalyear') ? 'active' : '' }}">
            <a href="{{ route('fiscalyear') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div>Fiscal Year SetUp</div>
            </a>
        </li>
        @endcan

        <!-- Home Tab -->
        @can('view.hometab')
        <li class="menu-item {{ request()->routeIs('hometab') ? 'active' : '' }}">
            <a href="{{ route('hometab') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Home Tab</div>
            </a>
        </li>
        @endcan

        <!-- Store -->
        @can('view.store')
        <li class="menu-item {{ request()->routeIs('store') ? 'active' : '' }}">
            <a href="{{ route('store') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div>Store</div>
            </a>
        </li>
        @endcan

        @can('view.org-fiscalyear')
        <li class="menu-item {{ request()->routeIs('org-fiscalyear.*') ? 'active' : '' }}">
            <a href="{{ route('org-fiscalyear.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div>Org Fiscal Years</div>
            </a>
        </li>
        @endcan

        <!-- Vendor -->
        @can('view.vendor')
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
        @endcan


        <!-- Category / Brand / Item -->
        @canany(['view.category', 'view.brand', 'view.unit', 'view.item', 'view.variation-attribute'])
        <li
            class="menu-item {{ request()->routeIs('category') || request()->routeIs('brand') || request()->routeIs('unit') || request()->routeIs('item') || request()->routeIs('variation-attribute') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-category"></i>
                <div>Category / Brand / Item</div>
            </a>

            <ul class="menu-sub">
                @can('view.category')
                <li class="menu-item {{ request()->routeIs('category') ? 'active' : '' }}">
                    <a href="{{ route('category') }}" class="menu-link">
                        {{-- <i class="bx bx-category-alt me-2"></i> --}}
                        <div>Category</div>
                    </a>
                </li>
                @endcan

                @can('view.brand')
                <li class="menu-item {{ request()->routeIs('brand') ? 'active' : '' }}">
                    <a href="{{ route('brand') }}" class="menu-link">
                        {{-- <i class="bx bx-purchase-tag me-2"></i> --}}
                        <div>Brand</div>
                    </a>
                </li>
                @endcan

                @can('view.variation-attribute')
                <li class="menu-item {{ request()->routeIs('variation-attribute') ? 'active' : '' }}">
                    <a href="{{ route('variation-attribute') }}" class="menu-link">
                        {{-- <i class="bx bx-slider me-2"></i> --}}
                        <div>Variation Attributes</div>
                    </a>
                </li>
                @endcan

                @can('view.unit')
                <li class="menu-item {{ request()->routeIs('unit') ? 'active' : '' }}">
                    <a href="{{ route('unit') }}" class="menu-link">
                        {{-- <i class="bx bx-slider me-2"></i> --}}
                        <div>Unit</div>
                    </a>
                </li>
                @endcan

                @can('view.item')
                <li class="menu-item {{ request()->routeIs('item') ? 'active' : '' }}">
                    <a href="{{ route('item') }}" class="menu-link">
                        {{-- <i class="bx bx-box me-2"></i> --}}
                        <div>Item</div>
                    </a>
                </li>
                @endcan

            </ul>
        </li>
        @endcanany

        <!-- Users -->
        @can('view.user')
        <li class="menu-item {{ request()->routeIs('user') ? 'active' : '' }}">
            <a href="{{ route('user') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div>Users</div>
            </a>
        </li>
        @endcan

        <!-- Assign Drive -->
        @canany(['view.driver', 'view.assign-driver'])
        <li
            class="menu-item {{ request()->routeIs('driver') || request()->routeIs('assign.driver') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-car"></i>
                <div>Drive</div>
            </a>

            <ul class="menu-sub">

                @can('view.driver')
                <li class="menu-item {{ request()->routeIs('driver') ? 'active' : '' }}">
                    <a href="{{ route('driver') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Driver List</div>
                    </a>
                </li>
                @endcan

                @can('view.assign-driver')
                <li class="menu-item {{ request()->routeIs('assign.driver') ? 'active' : '' }}">
                    <a href="{{ route('assign.driver') }}" class="menu-link">
                        <i class="bx bx-package me-2"></i>
                        <div>Assign Drive</div>
                    </a>
                </li>
                @endcan

            </ul>
        </li>
        @endcanany



        <!-- Inventory Management -->
        @canany(['view.inventory', 'view.purchase-voucher', 'view.purchase-return'])
        <li
            class="menu-item {{ request()->routeIs('inventory') || request()->routeIs('purchase-voucher') || request()->routeIs('purchase-voucher.*') || request()->routeIs('purchase-return') || request()->routeIs('purchase-return.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div>Inventory Management</div>
            </a>

            <ul class="menu-sub">
                @can('view.inventory')
                <li class="menu-item {{ request()->routeIs('inventory') ? 'active' : '' }}">
                    <a href="{{ route('inventory') }}" class="menu-link">
                        {{-- <i class="bx bx-building-house me-2"></i> --}}
                        <div>Stock</div>
                    </a>
                </li>
                @endcan

                @can('view.purchase-voucher')
                <li
                    class="menu-item {{ request()->routeIs('purchase-voucher') || request()->routeIs('purchase-voucher.*') ? 'active' : '' }}">
                    <a href="{{ route('purchase-voucher') }}" class="menu-link">
                        {{-- <i class="bx bx-file me-2"></i> --}}
                        <div>Purchase</div>
                    </a>
                </li>
                @endcan

                @can('view.purchase-return')
                <li
                    class="menu-item {{ request()->routeIs('purchase-return') || request()->routeIs('purchase-return.*') ? 'active' : '' }}">
                    <a href="{{ route('purchase-return') }}" class="menu-link">
                        {{-- <i class="bx bx-transfer-alt me-2"></i> --}}
                        <div>Purchase Return (Dr. Note)</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        <!-- Sales Management -->
        @canany(['view.sales', 'view.sales-return'])
        <li
            class="menu-item {{ request()->routeIs('sales') || request()->routeIs('sales.*') || request()->routeIs('sales-return') || request()->routeIs('sales-return.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div>Sales</div>
            </a>

            <ul class="menu-sub">

                @can('view.sales')
                <li
                    class="menu-item {{ request()->routeIs('sales') || request()->routeIs('sales.*') ? 'active' : '' }}">
                    <a href="{{ route('sales') }}" class="menu-link">
                        {{-- <i class="bx bx-cart-alt me-2"></i> --}}
                        <div>Sales</div>
                    </a>
                </li>
                @endcan

                @can('view.sales-return')
                <li
                    class="menu-item {{ request()->routeIs('sales-return') || request()->routeIs('sales-return.*') ? 'active' : '' }}">
                    <a href="{{ route('sales-return') }}" class="menu-link">
                        {{-- <i class="bx bx-revision me-2"></i> --}}
                        <div>Sales Return (Cr. Note)</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany


        <!-- Price Management -->
        @canany(['view.retailer', 'view.wholesaler'])
        <li
            class="menu-item {{ request()->routeIs('retailer') || request()->routeIs('wholesaler') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dollar-circle"></i>
                <div>Price Management</div>
            </a>

            <ul class="menu-sub">
                @can('view.retailer')
                <li class="menu-item {{ request()->routeIs('retailer') ? 'active' : '' }}">
                    <a href="{{ route('retailer') }}" class="menu-link">
                        {{-- <i class="bx bx-user-pin me-2"></i> --}}
                        <div>Retailer</div>
                    </a>
                </li>
                @endcan

                @can('view.wholesaler')
                <li class="menu-item {{ request()->routeIs('wholesaler') ? 'active' : '' }}">
                    <a href="{{ route('wholesaler') }}" class="menu-link">
                        {{-- <i class="bx bx-network-chart me-2"></i> --}}
                        <div>Wholesaler</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        <!-- Return Refund policy -->
        @can('view.refunds-policy')
        <li class="menu-item {{ request()->routeIs('refunds.policy') ? 'active' : '' }}">
            <a href="{{ route('refunds.policy') }}" class="menu-link">
                <i class="menu-icon bx bx-shield"></i>
                <div>Return & Refund Policy</div>
            </a>
        </li>
        @endcan

        <!-- Terms and condition and Privacy Policy -->
        @can('view.terms-conditions')
        <li class="menu-item {{ request()->routeIs('terms.conditions') ? 'active' : '' }}">
            <a href="{{ route('terms.conditions') }}" class="menu-link">
                <i class="menu-icon bx bx-file-blank"></i>
                <div>Terms Conditions and Privacy Policy</div>
            </a>
        </li>
        @endcan

        <!-- Home Tab -->
        <!-- <li class="menu-item {{ request()->routeIs('hometab') ? 'active' : '' }}">
            <a href="{{ route('hometab') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Home Tab</div>
            </a>
        </li> -->

        <!-- Promotional Banners -->
        @can('view.promotion')
        <li class="menu-item {{ request()->routeIs('promotion') ? 'active' : '' }}">
            <a href="{{ route('promotion') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-purchase-tag-alt"></i>
                <div>Promotional Banners</div>
            </a>
        </li>
        @endcan


        <!-- Discount / Loyalty -->
        @canany(['view.discount', 'view.loyalty', 'view.coupon'])
        <li
            class="menu-item {{ request()->routeIs('loyalty') || request()->routeIs('discount') || request()->routeIs('coupon') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-purchase-tag"></i>
                <div>Discount / Loyalty/</div>
            </a>

            <ul class="menu-sub">

                @can('view.discount')
                <li class="menu-item {{ request()->routeIs('discount') ? 'active' : '' }}">
                    <a href="{{ route('discount') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Discount</div>
                    </a>
                </li>
                @endcan

                @can('view.loyalty')
                <li class="menu-item {{ request()->routeIs('loyalty') ? 'active' : '' }}">
                    <a href="{{ route('loyalty') }}" class="menu-link">
                        <i class="bx bx-package me-2"></i>
                        <div>Loyalty</div>
                    </a>
                </li>
                @endcan

                @can('view.coupon')
                <li class="menu-item {{ request()->routeIs('coupon') ? 'active' : '' }}">
                    <a href="{{ route('coupon') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Coupon</div>
                    </a>
                </li>
                @endcan


            </ul>
        </li>
        @endcanany


        <!-- Order -->
        @can('view.order')
        <li class="menu-item {{ request()->routeIs('order') ? 'active' : '' }}">
            <a href="{{ route('order') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div>Order</div>
            </a>
        </li>
        @endcan

        {{-- Invoice --}}
        {{-- <li class="menu-item {{ request()->routeIs('invoice') ? 'active' : '' }}">
        <a href="{{ route('invoice') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-receipt"></i>
            <div>Invoice</div>
        </a>
        </li> --}}

        <!-- Refund -->
        @can('view.refund')
        <li class="menu-item {{ request()->routeIs('refund') || request()->routeIs('refund.*') ? 'active' : '' }}">
            <a href="{{ route('refund') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-revision"></i>
                <div>Refund</div>
            </a>
        </li>
        @endcan

        <!-- Report -->
        @canany(['view.report-sales', 'view.inventory-report'])
        <li
            class="menu-item {{ request()->routeIs('report.sales') || request()->routeIs('inventory.report') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-line-chart"></i>
                <div>Report</div>
            </a>

            <ul class="menu-sub">

                @can('view.report-sales')
                <li class="menu-item {{ request()->routeIs('report.sales') ? 'active' : '' }}">
                    <a href="{{ route('report.sales') }}" class="menu-link">
                        <i class="bx bx-trending-up me-2"></i>
                        <div>Sales Report</div>
                    </a>
                </li>
                @endcan

                @can('view.inventory-report')
                <li class="menu-item {{ request()->routeIs('inventory.report') ? 'active' : '' }}">
                    <a href="{{ route('inventory.report') }}" class="menu-link">
                        <i class="bx bx-package me-2"></i>
                        <div>Inventory Report</div>
                    </a>
                </li>
                @endcan


            </ul>
        </li>
        @endcanany

        <!-- Heatmap -->
        @can('view.heatmap')
        <li class="menu-item {{ request()->routeIs('admin.heatmap.index') ? 'active' : '' }}">
            <a href="{{ route('admin.heatmap.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-map-alt"></i>
                <div>Heatmap</div>
            </a>
        </li>
        @endcan


        <!-- Notification -->
        @can('view.notification')
        <li class="menu-item {{ request()->routeIs('notification') ? 'active' : '' }}">
            <a href="{{ route('notification') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div>Notification</div>
            </a>
        </li>
        @endcan

    </ul>
</aside>