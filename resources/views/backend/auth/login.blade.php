<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Login | WELCOME TO GHARMA</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css" />

    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>

    <style>
        .custom-notification {
            position: fixed; top: 20px; right: 20px;
            padding: 15px; color: #fff;
            border-radius: 5px; display: none; z-index: 9999;
        }
    </style>
</head>

<body>
    <!-- Notification -->
    <div id="customNotification" class="custom-notification"></div>

    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <img style="width: 200px;" src="logo.png" alt="logo"
                                         onerror="this.style.display='none'">
                                </span>
                            </a>
                        </div>

                        <h4 class="mb-2">WELCOME TO GHARMA</h4>
                        <p class="mb-4">Please sign-in to your account and start the adventure</p>

                        @include('layouts.include.alertMessage')

                        {{-- ✅ type="submit" so Enter key works natively --}}
                        <form id="login-form" class="mb-3" action="{{ route('loginuser') }}"
                              method="POST" enctype="multipart/form-data" autocomplete="off">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email or Username</label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Enter your email or username" autofocus />
                                @error('email')
                                    <p class="text-danger small">Please input email or username.</p>
                                @enderror
                            </div>

                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Password</label>
                                    <a href="#"><small>Forgot Password?</small></a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control"
                                        name="password"
                                        placeholder="············"
                                        aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer">
                                        <i class="bx bx-hide" id="togglePassword"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <p class="text-danger small">Please input password.</p>
                                @enderror
                            </div>

                            <div class="mb-3">
                                {{-- ✅ type="submit" — Enter key triggers this --}}
                                <button class="btn btn-primary d-grid w-100" type="submit" id="signin-btn">
                                    Sign in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ ALL scripts inside body, before </body> -->

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/js/main.js"></script>

    <!-- jQuery Validate -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

    <script>
        function showNotification(message, type) {
            var n = document.getElementById('customNotification');
            n.textContent = message;
            n.style.backgroundColor = (type === 'success') ? '#28a745' : '#dc3545';
            n.style.display = 'block';
            setTimeout(function() { n.style.display = 'none'; }, 3000);
        }

        $(document).ready(function () {

            // ✅ Password toggle
            $('#togglePassword').on('click', function () {
                var input = $('#password');
                var isPassword = input.attr('type') === 'password';
                input.attr('type', isPassword ? 'text' : 'password');
                $(this).toggleClass('bx-hide bx-show');
            });

            // ✅ Validation rules
            $('#login-form').validate({
                rules: {
                    email:    { required: true },
                    password: { required: true }
                },
                messages: {
                    email:    { required: 'Please enter email or username' },
                    password: { required: 'Please enter password' }
                },
                errorClass: 'text-danger small',
                highlight:   function(el) { $(el).addClass('is-invalid'); },
                unhighlight: function(el) { $(el).removeClass('is-invalid'); },

                // ✅ submitHandler fires on valid form submit (button click OR Enter key)
                submitHandler: function(form) {
                    var $btn = $('#signin-btn');
                    $btn.prop('disabled', true)
.html('<span class="spinner-border spinner-border-sm"></span> Signing in...');
                    $.ajax({
                        url:         $(form).attr('action'),
                        type:        'POST',
                        data:        new FormData(form),
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            var res = typeof result === 'string' ? JSON.parse(result) : result;
                            if (res.type === 'success') {
                                window.location.href = res.url;
                            } else {
                                showNotification(res.message, 'error');
                                $btn.prop('disabled', false).html('Sign in');
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('Sign in');
                            if (xhr.status === 422) {
                                $.each(xhr.responseJSON.errors, function(key, val) {
                                    showNotification(val[0], 'error');
                                });
                            } else {
                                showNotification('Something went wrong!', 'error');
                            }
                        }
                    });

                    return false; // prevent normal form submit
                }
            });

        });
    </script>

</body>
</html>