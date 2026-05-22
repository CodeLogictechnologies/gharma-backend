<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input type="text" class="form-control border-0 shadow-none"
                    placeholder="Search..." aria-label="Search..." />
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @php
                $authUser    = Auth::user();
                $authProfile = DB::table('profiles')->where('user_id', $authUser->id)->first();
                $navAvatar   = (!empty($authProfile->image))
                                ? asset('storage/profiles/' . $authProfile->image)
                                : asset('no-user.jpg');
            @endphp

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ $navAvatar }}"
                             alt="{{ $authUser->name }}"
                             class="w-px-40 h-auto rounded-circle navbar-avatar"
                             style="width:40px;height:40px;object-fit:cover;" />
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <!-- User Info -->
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ $navAvatar }}"
                                             alt="{{ $authUser->name }}"
                                             class="w-px-40 h-auto rounded-circle navbar-avatar"
                                             style="width:40px;height:40px;object-fit:cover;" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ $authUser->name ?? 'Admin' }}</span>
                                    <small class="text-muted">{{ $authUser->email ?? '' }}</small>
                                </div>
                            </div>
                        </a>
                    </li>

                    <li><div class="dropdown-divider"></div></li>

                    <!-- My Profile -->
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>

                    <li><div class="dropdown-divider"></div></li>

                    <!-- Logout -->
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>