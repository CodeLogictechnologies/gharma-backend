    <!-- app-header -->
    <header class="app-header">

        <!-- Start::main-header-container -->
        <div class="main-header-container container-fluid">

            <!-- Start::header-content-left -->
            <div class="header-content-left">

                <!-- Start::header-element -->
                <div class="header-element">
                    <div class="horizontal-logo">
                        <a href="index.html" class="header-logo">
                            <img src="{{ asset('backpanel/assets/images/brand-logos/desktop-logo.png') }}" alt="logo"
                                class="desktop-logo">
                            <img src="{{ asset('backpanel/assets/images/brand-logos/toggle-logo.png') }}" alt="logo"
                                class="toggle-logo">
                            <img src="{{ asset('backpanel/assets/images/brand-logos/desktop-white.png') }}"
                                alt="logo" class="desktop-white">
                            <img src="{{ asset('backpanel/assets/images/brand-logos/toggle-white.png') }}"
                                alt="logo" class="toggle-white">
                        </a>
                    </div>
                </div>
                <!-- End::header-element -->

                <!-- Start::header-element -->
                <div class="header-element">
                    <!-- Start::header-link -->
                    <a aria-label="Hide Sidebar"
                        class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                        data-bs-toggle="sidebar" href="javascript:void(0);">
                        <i class="header-icon fe fe-align-left"></i>
                    </a>
                    <!-- End::header-link -->
                </div>
                <!-- End::header-element -->

            </div>
            <!-- End::header-content-left -->

            <!-- Start::header-content-right -->
            <div class="header-content-right">

                <div class="header-element Search-element d-block d-lg-none">
                    <!-- Start::header-link|dropdown-toggle -->
                    <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside"
                        data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" class="header-link-icon">
                            <path
                                d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z" />
                        </svg>
                    </a>
                    <!-- End::header-link|dropdown-toggle -->
                    <ul class="main-header-dropdown dropdown-menu dropdown-menu-end Search-element-dropdown"
                        data-popper-placement="none">
                        <li>
                            <div class="input-group w-100 p-2">
                                <input type="text" class="form-control" placeholder="Search...">
                                <div class="btn btn-primary">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Start::header-element -->
                <div class="header-element headerProfile-dropdown">
                    <div class="me-3">
                        <span>Welcome! </span><a
                            href="{{ route('admin.profile') }}"><label>{{ $userProfile->name }}</label></a>
                    </div>
                    <!-- Start::header-link|dropdown-toggle -->
                    <div>
                        <a href="{{ route('admin.profile') }}"> <img
                                src="@if (!empty($userProfile->image)) {{ asset('storage/profile') . '/' . $userProfile->image }} @else {{ asset('/no-user.jpg') }} @endif"
                                class="rounded-circle" alt="School Logo"width=" 37" ,height="37"></a>
                    </div>
                    <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="fa-solid fa-angle-down"></i>
                    </a>
                    <!-- End::header-link|dropdown-toggle -->
                    <ul class="main-header-dropdown dropdown-menu pt-0 header-profile-dropdown dropdown-menu-end main-profile-menu"
                        aria-labelledby="mainHeaderProfile">
                        <li>
                            <div class="main-header-profile bg-primary menu-header-content text-fixed-white">
                                <div class="my-auto">
                                    <h6 class="mb-0 lh-1 text-fixed-white">{{ $userProfile->name }}</h6><span
                                        class="fs-11 op-7 lh-1">{{ $userProfile->email }}</span>
                                </div>
                            </div>
                        </li>
                        <li><a class="dropdown-item d-flex" href="{{ route('admin.profile') }}"><i
                                    class="bx bx-user-circle fs-18 me-2 op-7"></i>Profile</a></li>

                        <li><a class="dropdown-item d-flex" href="{{ route('logout') }}"><i
                                    class="bx bx-log-out fs-18 me-2 op-7"></i>Sign Out</a></li>
                    </ul>
                </div>
                <!-- End::header-element -->

            </div>
            <!-- End::header-content-right -->

        </div>
        <!-- End::main-header-container -->

    </header>
    <!-- /app-header -->
