@extends('backend.layouts.external')
@section('title')
    Forget Password
@endsection
@section('main-content')
    <div class="custom-login">
        <!-- Demo content-->
        <div class="custom-login-box">

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
                        src="{{ asset('backpanel/assets/images/brand-logos/desktop-logo.png') }}" class="desktop-logo ht-40"
                        alt="logo">
                </a>
            </div>
            <div class="card-sigin">
                <div class="main-signup-header">
                    <h6 class="fw-medium mb-4 fs-17">Forgot Password</h6>
                    <form action="{{ route('admin.checkuser') }}" method="POST" id="login-form" autocomplete="off">
                        @csrf
                        @if (empty($email))
                            <div class="form-group mb-3">
                                <label class="form-label">Email</label>
                                <input class="form-control" placeholder="Enter your email" type="text" name="email"
                                    id="email">
                                @error('email')
                                    {{ $message }}
                                @enderror
                            </div>
                        @endif
                        <div class="form-group mb-3">

                            <a href="{{ route('admin.login') }}"class="form-group col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                <label class="form-check-label ">
                                    Back to login
                                </label>
                            </a>
                        </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block w-100">Submit</button>
                </form>
            </div>
        </div>
    </div><!-- End -->
@endsection
@section('scripts')
    <script>
        $("#login-form").validate({
            rules: {
                email: {
                    required: true
                },
                password: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "Please enter email"
                }
            },
        });
    </script>
@endsection
