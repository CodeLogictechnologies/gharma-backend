<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="/assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="erp.png">

    <style>
        /* ── Loader ─────────────────────────────────────────────── */
        #global-loader {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 99999;
            align-items: center;
            justify-content: center;
        }
        #global-loader-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        /* ── Notification ────────────────────────────────────────── */
        #global-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999999;
            background: #fff;
            border-radius: 10px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #333;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            min-width: 260px;
            max-width: 380px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(60px); }
            to   { opacity: 1; transform: translateX(0); }
        }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="/assets/css/datatable.css" />

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Helpers & Config (must be in head) -->
    <script src="/assets/vendor/js/helpers.js"></script>
    <script src="/assets/js/config.js"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Layout page -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('layouts.navbar')

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    @yield('content')
                    @include('layouts.footer')
                    <div class="content-backdrop fade"></div>
                </div>

            </div>
            <!-- / Layout page -->

        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- ── Core JS (order matters!) ────────────────────────────── -->
    <script src="/assets/vendor/libs/jquery/jquery.js"></script>

    <!-- Bootstrap Bundle ONCE (has Popper inside) — remove local bootstrap.js to avoid conflict -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="/assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/dashboards-analytics.js"></script>

    <!-- DataTables -->
    <script src="{{ asset('assets/vendor/libs/data-tables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/data-tables/jquery.dataTables.columnFilter.js') }}"></script>

    <!-- jQuery Validate & Form -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>

    <!-- SortableJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        /* ── Loader ─────────────────────────────────────────────── */
        function showLoader() {
            if ($('#global-loader').length === 0) {
                $('body').append(`
                    <div id="global-loader">
                        <div id="global-loader-inner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>`);
            }
            $('#global-loader').fadeIn(200);
        }
        function hideLoader() {
            $('#global-loader').fadeOut(200);
        }

        /* ── Notification ────────────────────────────────────────── */
        function showNotification(message, type = 'success') {
            $('#global-notification').remove();
            var icons  = { success:'bx-check-circle', error:'bx-x-circle', warning:'bx-error', info:'bx-info-circle' };
            var colors = { success:'#28a745', error:'#dc3545', warning:'#ffc107', info:'#17a2b8' };
            var icon  = icons[type]  || icons.info;
            var color = colors[type] || colors.info;
            $('body').append(`
                <div id="global-notification">
                    <i class='bx ${icon}' style="font-size:20px;color:${color};"></i>
                    <span>${message}</span>
                </div>`);
            setTimeout(function () {
                $('#global-notification').fadeOut(400, function () { $(this).remove(); });
            }, 3000);
        }
    </script>

    @yield('main-scripts')

</body>
</html>