@extends('backend.layouts.external')

@section('title')
    Change Password
@endsection
@section('main-content')
    <div class="custom-login">
        <div class="custom-login-box">
            <div class="card-sigin">
                @if (Session::has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ Session::get('error') }}
                    </div>
                @endif
                @if (Session::has('success'))
                    <div class="alert alert-success" role="alert">
                        {{ Session::get('success') }}
                    </div>
                @endif
                <div class="mb-5 d-flex">
                    <a href="#" class="header-logo"><img
                            src="{{ asset('backpanel/assets/images/brand-logos/desktop-logo.png') }}"
                            class="desktop-logo ht-40" alt="logo">
                        <img src="{{ asset('backpanel/assets/images/brand-logos/desktop-white.png') }}"
                            class="desktop-white ht-40" alt="logo">
                    </a>
                </div>
                <div class="card-sigin">
                    <div class="main-signup-header">
                        <h6 class="fw-medium mb-4 fs-17">Forgot Password</h6>
                        <form action="{{ route('admin.updatepassword') }}" method="POST" id="login-form"
                            autocomplete="off">
                            @csrf

                            <div class="form-group mb-3">
                                <input type="hidden" name="id" id="id" value={{ session('id') }}>
                                <label class="form-label">New Password</label>
                                <input class="form-control" placeholder="Enter new password" type="password" name="password"
                                    id="password">
                                @error('password')
                                    {{ $message }}
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input class="form-control" placeholder="Enter confirm password" type="password"
                                    name="confirm_password" id="confirm_password">
                                @error('confirm_password')
                                    {{ $message }}
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <div class="row ms-0">
                                    <div class="form-check col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <input class="form-check-input" type="checkbox" value="" id="show-password">
                                        <label class="form-check-label" for="show-password">
                                            Show Password
                                        </label>
                                    </div>
                                    <a
                                        href="{{ route('admin.login') }}"class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                        <label class="form-check-label ">
                                            Back to login
                                        </label>
                                    </a>
                                </div>
                            </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block w-100">Submit</button>
                    </form>

                </div>
            </div>
        </div>

    </div><!-- End -->
@endsection
@section('scripts')
    <script>
        $("#login-form").validate({
            rules: {

                password: {
                    required: true
                },
                confirm_password: {
                    required: true
                }
            },
            messages: {
                password: {
                    required: "Please enter password"
                },
                confirm_password: {
                    required: "Please enter confirm password"
                },
            },
        });

        // Show password-start
        $('#show-password').off('change');
        $('#show-password').on('change', function() {
            var passwordField = $('#password');
            var confirmPasswordField = $('#confirm_password');
            if (passwordField.prop('type') == 'password') {
                passwordField.prop('type', 'text');
            } else {
                passwordField.prop('type', 'password');
            }
            if (confirmPasswordField.prop('type') == 'password') {
                confirmPasswordField.prop('type', 'text');
            } else {
                confirmPasswordField.prop('type', 'password');
            }
        });
        // Show password-end
    </script>
@endsection
