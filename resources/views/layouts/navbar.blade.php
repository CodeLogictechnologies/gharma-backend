<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <!-- <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input type="text" class="form-control border-0 shadow-none"
                    placeholder="Search..." aria-label="Search..." />
            </div>
        </div> -->
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @php
                $authUser    = Auth::user();
                $authProfile = DB::table('profiles')->where('user_id', $authUser->id)->first();
                $navAvatar   = (!empty($authProfile->image))
                                ? asset('storage/profiles/' . $authProfile->image)
                                : asset('no-user.jpg');
            @endphp

            <!-- Low Stock Notifications -->
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-bell bx-sm"></i>
                    <span id="lowStockBadge"
                        class="badge bg-danger rounded-pill position-absolute d-none"
                        style="top: 2px; right: 2px; font-size: 0.65rem;">0</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0" style="min-width: 300px;">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h6 class="mb-0 me-auto">Low Stock Alerts</h6>
                        </div>
                    </li>
                    <li class="dropdown-menu-list" style="max-height: 320px; overflow-y: auto;">
                        <ul class="list-unstyled m-0" id="lowStockList">
                            <li class="dropdown-item text-center text-muted py-3">No low stock alerts</li>
                        </ul>
                    </li>
                    <li class="dropdown-menu-footer border-top">
                        <a href="{{ route('inventory') }}" class="dropdown-item d-flex justify-content-center p-2">
                            View Stock
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ Low Stock Notifications -->

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

<script>
    (function () {
        function csrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function renderLowStockAlerts(data) {
            var badge = document.getElementById('lowStockBadge');
            var list  = document.getElementById('lowStockList');
            var count = data.count || 0;

            if (!badge || !list) {
                return;
            }

            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : String(count);
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

            list.innerHTML = '';

            if (!data.items || data.items.length === 0) {
                var empty = document.createElement('li');
                empty.className = 'dropdown-item text-center text-muted py-3';
                empty.textContent = 'No low stock alerts';
                list.appendChild(empty);
                return;
            }

            data.items.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'dropdown-item';

                var wrap = document.createElement('div');
                wrap.className = 'd-flex flex-column';

                var title = document.createElement('span');
                title.className = 'fw-semibold';
                title.textContent = item.title;

                var attr = document.createElement('small');
                attr.className = 'text-muted';
                attr.textContent = item.attribute;

                var stock = document.createElement('small');
                stock.className = 'text-danger';
                stock.textContent = 'Remaining: ' + item.remaining + ' (Threshold: ' + item.threshold + ')';

                wrap.appendChild(title);
                wrap.appendChild(attr);
                wrap.appendChild(stock);
                li.appendChild(wrap);
                list.appendChild(li);
            });
        }

        function loadLowStockAlerts() {
            fetch('{{ route('inventory.low-stock-alerts') }}', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                }
            })
                .then(function (res) { return res.json(); })
                .then(renderLowStockAlerts)
                .catch(function () {});
        }

        window.refreshLowStockAlerts = loadLowStockAlerts;

        function urlBase64ToUint8Array(base64String) {
            var padding = '='.repeat((4 - base64String.length % 4) % 4);
            var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            var rawData = window.atob(base64);
            var outputArray = new Uint8Array(rawData.length);
            for (var i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        function setupPushNotifications() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                return;
            }

            navigator.serviceWorker.register('/sw.js').then(function (registration) {
                navigator.serviceWorker.addEventListener('message', function (event) {
                    if (event.data && event.data.type === 'low-stock-refresh') {
                        loadLowStockAlerts();
                    }
                });

                if (Notification.permission === 'denied') {
                    return;
                }

                return Notification.requestPermission().then(function (permission) {
                    if (permission !== 'granted') {
                        return;
                    }

                    return fetch('{{ route('push.vapid-public-key') }}', {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (!data.publicKey) {
                                return;
                            }

                            return registration.pushManager.getSubscription().then(function (existing) {
                                if (existing) {
                                    return existing;
                                }

                                return registration.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: urlBase64ToUint8Array(data.publicKey)
                                });
                            });
                        })
                        .then(function (subscription) {
                            if (!subscription) {
                                return;
                            }

                            return fetch('{{ route('push.subscribe') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken(),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(subscription)
                            });
                        });
                });
            }).catch(function () {});
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadLowStockAlerts();
            setupPushNotifications();
        });
    })();
</script>