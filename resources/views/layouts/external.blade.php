<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> Gharma  </title>
    <meta name="Description" content="Bootstrap Responsive Admin Web Dashboard HTML5 Template">
    <meta name="Author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('backpanel/assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

    <!-- Main Theme Js -->
    <script src="{{ asset('backpanel/assets/js/authentication-main.js') }}"></script>

    <!-- Bootstrap Css -->
    <link id="style" href="{{ asset('backpanel/assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Style Css -->
    <link href="{{ asset('backpanel/assets/css/styles.min.css') }}" rel="stylesheet">

    <!-- Icons Css -->
    <link href="{{ asset('backpanel/assets/css/icons.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/fontawesome.min.css"
        integrity="sha512-d0olNN35C6VLiulAobxYHZiXJmq+vl+BGIgAxQtD5+kqudro/xNMvv2yIHAciGHpExsIbKX3iLg+0B6d0k4+ZA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<style>
    label {
        font-weight: 500;
        color: #454545;
    }

    .error {
        color: red !important;
    }

    /*error message-end*/

    .required-field {
        color: red;
    }

    #loading {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: none;
        z-index: 999;
    }

    .custom-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px;
        background-color: #28A745;
        /* Green for success */
        color: #fff;
        border-radius: 5px;
        display: none;
        z-index: 9999;
    }

    .custom-login {
        position: relative;
        width: 100%;
        height: 100vh;
        /* background: linear-gradient(to right, rgb(18 42 56 / 60%), rgba(18, 42, 56, 1)), url(/bglogin.jpeg); */
        background: linear-gradient(to right, rgb(18 42 56 / 60%), rgba(18, 42, 56, 1));
        /* background: linear-gradient(rgb(18 42 56 / 60%), rgba(18, 42, 56, 1)), url(/bglogin.jpeg); */

        background-repeat: no-repeat;
        background-size: cover;
        background-position: center bottom;
    }

    .custom-login-box {
        width: 24%;
        background: rgba(255, 255, 255, 0.6);
        background-repeat: no-repeat;
        border-radius: 12px;
        box-shadow: rgba(0, 0, 0, .45) 0 2px 10px;
        position: absolute;
        padding: 2rem;
        right: 3rem;
        top: 50%;
        /* left: 50%; */
        right: 5rem;
        /* transform: translate(-50%, -50%); */
        transform: translateY(-50%);
    }

        /* custom-login-start */

    .custom-login-login {
        width: 100%;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #185cab;
        position: relative;
        overflow: hidden;
    }

    .custom-login-box-login {
        width: 30%;
        background-color: white;
        background-repeat: no-repeat;
        border-radius: 8px;
        box-shadow: rgba(0, 0, 0, .45) 0 2px 10px;
        position: absolute;
        padding: 2.5rem;
        top: 50%;
        transform: translateY(-50%);
    }

    .register-link {
        color: #185CAB;
        text-decoration: none;
        border-bottom: 1px solid #185CAB;
    }

    .register-link:hover {
        color: #134785;
        border-bottom-color: #134785;
    }

    .login-bg-img {
        height: 100vh;
        object-fit: cover;
        margin: 75px 150px 75px 150px;

    }

    .input-field {
        display: flex;
        align-items: center;
        background-color: #F5F5F5;
        border-radius: 4px;
        padding: 0 10px;
        margin-bottom: 15px;
        position: relative;
    }

    .input-field input {
        background-color: transparent;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        width: 100%;
        padding: 10px;
    }

    .input-field.error {
        border: 1px solid red !important;
        border-radius: 4px;
    }

    .custom-login-box-login input[type="text"]:focus,
    .custom-login-box-login input[type="password"]:focus {
        background-color: #F5F5F5;
        border: none;
        box-shadow: none;
    }



    @media (max-width: 992px) {

        .custom-login-box-login {
            width: 70%;
            max-width: 100%;
            padding: 1.5rem;
        }
    }

    @media (max-width: 768px) {


        .custom-login-login {
            padding: 1rem;
        }

        .custom-login-box-login {
            width: 90%;

            max-width: 100%;
            padding: 1.5rem;
        }
    }

    /* custom-login-end */

</style>

{{-- <body> --}}


<body class="h-100">
    <div id="customNotification" class="custom-notification"></div>
    {{-- <div class="authincation h-100">
        <div class="container h-100"> --}}

    @yield('main-content')
    {{-- </div>
    </div> --}}


    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>


    <!-- Bootstrap JS        -->
    <script src="{{ asset('backpanel/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Show Password JS -->
    <script src="{{ asset('backpanel/assets/js/show-password.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.3/jquery.validate.min.js"></script>
    <script src="https://nepalidatepicker.sajanmaharjan.com.np/nepali.datepicker/js/nepali.datepicker.v4.0.1.min.js"
        type="text/javascript"></script>
    <!-- Include jQuery Validation plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <script>
        $('.alert').delay(3000).fadeOut();
    </script>
    <script>
        var baseurl = '{{ url('/') }}';
        var token = "<?= csrf_token() ?>";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script>
        function showLoader() {
            $('#loadingOverlay').show();
        }

        function hideLoader() {
            $('#loadingOverlay').hide();
        }

        function showNotification(message, type) {
            var notification = document.getElementById('customNotification');
            notification.textContent = message;

            if (type === 'success') {
                notification.style.backgroundColor = '#28a745'; // Green for success
            } else if (type === 'error') {
                notification.style.backgroundColor = '#dc3545'; // Red for error
            }

            // Show the notification
            notification.style.display = 'block';

            // Hide the notification after 3 seconds (adjust as needed)
            setTimeout(function() {
                notification.style.display = 'none';
            }, 2000);
        }
    </script>

    @yield('scripts')
</body>

</html>
