@extends('layouts.main')
@section('title', 'My Profile')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- ── Profile Photo Card ─────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-body text-center py-5">

                    <div class="position-relative d-inline-block mb-3">
                        @if (!empty($userData->image))
                            <img src="{{ asset('storage/profiles/' . $userData->image) }}"
                                 id="avatarPreview"
                                 class="rounded-circle"
                                 style="width:120px;height:120px;object-fit:cover;border:3px solid #696cff;">
                        @else
                            <img src="{{ asset('no-user.jpg') }}"
                                 id="avatarPreview"
                                 class="rounded-circle"
                                 style="width:120px;height:120px;object-fit:cover;border:3px solid #696cff;">
                        @endif

                        {{-- Camera icon --}}
                        <label for="avatarInput"
                               class="position-absolute bottom-0 end-0 bg-primary rounded-circle d-flex align-items-center justify-content-center"
                               style="width:32px;height:32px;cursor:pointer;border:2px solid #fff;"
                               title="Change photo">
                            <i class="bx bx-camera text-white" style="font-size:14px;"></i>
                        </label>
                        <input type="file" id="avatarInput" accept="image/jpeg,image/jpg,image/png" style="display:none;">
                    </div>

                    <h5 class="mb-1">{{ $userData->name ?? 'Admin' }}</h5>
                    <p class="text-muted mb-0">{{ $userData->email ?? '' }}</p>

                    {{-- Save/Cancel buttons (hidden until image picked) --}}
                    <div id="uploadActions" class="mt-3" style="display:none;">
                        <button type="button" class="btn btn-primary btn-sm me-2" id="saveAvatarBtn">
                            <i class="bx bx-upload me-1"></i> Save Photo
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelAvatarBtn">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Profile Info Card ──────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name"
                                       value="{{ $userData->name ?? '' }}" placeholder="Enter name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email"
                                       value="{{ $userData->email ?? '' }}" placeholder="Enter email">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address"
                                       value="{{ $userData->address ?? '' }}" placeholder="Enter address">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="saveProfileBtn">
                            <i class="bx bx-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Change Password Card ───────────────────────────── --}}
            <!-- <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="passwordForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="current_password"
                                       id="current_password" placeholder="Enter current password">
                                <span class="input-group-text toggle-pass" data-target="current_password" style="cursor:pointer;">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password"
                                       id="new_password" placeholder="Enter new password">
                                <span class="input-group-text toggle-pass" data-target="new_password" style="cursor:pointer;">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="confirm_password"
                                       id="confirm_password" placeholder="Confirm new password">
                                <span class="input-group-text toggle-pass" data-target="confirm_password" style="cursor:pointer;">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-warning" id="savePasswordBtn">
                            <i class="bx bx-lock me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div> -->

        </div>
    </div>
</div>
@endsection

@section('main-scripts')
<script>
$(document).ready(function () {

    var originalAvatar = $('#avatarPreview').attr('src');

    /* ── Image preview ─────────────────────────────────────────── */
    $('#avatarInput').on('change', function () {
        var file = this.files[0];
        if (!file) return;

        if (!['image/jpeg','image/jpg','image/png'].includes(file.type)) {
            showNotification('Only JPG and PNG files are allowed.', 'error');
            $(this).val(''); return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showNotification('Image must be under 2MB.', 'error');
            $(this).val(''); return;
        }

        $('#avatarPreview').attr('src', URL.createObjectURL(file));
        $('#uploadActions').show();
    });

    /* ── Cancel photo change ───────────────────────────────────── */
    $('#cancelAvatarBtn').on('click', function () {
        $('#avatarPreview').attr('src', originalAvatar);
        $('#avatarInput').val('');
        $('#uploadActions').hide();
    });

    /* ── Save photo ────────────────────────────────────────────── */
    $('#saveAvatarBtn').on('click', function () {
        var file = $('#avatarInput')[0].files[0];
        if (!file) { showNotification('Please select an image first.', 'error'); return; }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Saving...');

        var formData = new FormData();
        formData.append('image', file);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("admin.updateprofileimage") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.type === 'success') {
                    showNotification(result.message, 'success');
                    // Reload after short delay so navbar + profile both show new image from DB
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(result.message, 'error');
                    $('#avatarPreview').attr('src', originalAvatar);
                    $('#uploadActions').hide();
                    $('#avatarInput').val('');
                }
            },
            error: function () {
                showNotification('Upload failed. Please try again.', 'error');
                $('#avatarPreview').attr('src', originalAvatar);
                $('#uploadActions').hide();
                $('#avatarInput').val('');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i> Save Photo');
            }
        });
    });

    /* ── Save profile info ─────────────────────────────────────── */
    $('#saveProfileBtn').on('click', function () {
        var name    = $('[name="name"]').val().trim();
        var email   = $('[name="email"]').val().trim();
        var address = $('[name="address"]').val().trim();

        if (!name)  { showNotification('Name is required.', 'error'); return; }
        if (!email) { showNotification('Email is required.', 'error'); return; }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Saving...');

        $.ajax({
            url: '{{ route("admin.updateprofile") }}',
            type: 'POST',
            data: { name: name, email: email, address: address, _token: '{{ csrf_token() }}' },
            success: function (response) {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                showNotification(result.message, result.type);
            },
            error: function () { showNotification('Something went wrong.', 'error'); },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save Changes');
            }
        });
    });

    /* ── Change password ───────────────────────────────────────── */
    $('#savePasswordBtn').on('click', function () {
        var current = $('#current_password').val().trim();
        var newPass  = $('#new_password').val().trim();
        var confirm  = $('#confirm_password').val().trim();

        if (!current) { showNotification('Current password is required.', 'error'); return; }
        if (!newPass)  { showNotification('New password is required.', 'error'); return; }
        if (!confirm)  { showNotification('Confirm password is required.', 'error'); return; }
        if (newPass !== confirm) { showNotification('Passwords do not match.', 'error'); return; }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Updating...');

        $.ajax({
            url: '{{ route("admin.update") }}',
            type: 'POST',
            data: {
                current_password: current,
                password: newPass,
                confirm_password: confirm,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                showNotification(result.message, result.type);
                if (result.type === 'success') $('#passwordForm')[0].reset();
            },
            error: function () { showNotification('Something went wrong.', 'error'); },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bx bx-lock me-1"></i> Update Password');
            }
        });
    });

    /* ── Password show/hide toggle ─────────────────────────────── */
    $(document).on('click', '.toggle-pass', function () {
        var $input = $('#' + $(this).data('target'));
        var $icon  = $(this).find('i');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('bx-hide').addClass('bx-show');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('bx-show').addClass('bx-hide');
        }
    });

});
</script>
@endsection