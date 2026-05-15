{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />

<style>
    ._image {
        max-height: 160px;
        max-width: 160px;
        border: 1px solid #ccc;
    }

    .border-danger {
        border-color: red !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
        padding: 2px 6px;
        gap: 4px;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        border: none;
        color: #fff;
        border-radius: 0.25rem;
        padding: 2px 8px;
        font-size: 0.8rem;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255, 255, 255, 0.75);
        margin-right: 4px;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: #dee2e6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #0d6efd;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .select2-selection__clear {
        display: none;
    }
</style>

<div class="modal-header">
    <h1 class="modal-title fs-5">{{ empty($id) ? 'Add User' : 'Edit User' }}</h1>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    {{-- FIX: id="userForm" (was "orgForm" in the tab partial), action uses correct route --}}
    <form action="{{ route('user.save') }}" method="POST" id="userForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ @$id }}">

        {{-- Name Fields --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="first_name" id="first_name"
                    placeholder="Enter first name" value="{{ @$first_name }}">
                <div class="invalid-feedback">Enter first name</div>
            </div>
            <div class="col-md-4">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" name="middle_name" id="middle_name"
                    placeholder="Enter middle name" value="{{ @$middle_name }}">
            </div>
            <div class="col-md-4">
                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Enter last name"
                    value="{{ @$last_name }}">
                <div class="invalid-feedback">Enter last name</div>
            </div>
        </div>

        {{-- Username, Email, Phone --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Enter username"
                    value="{{ @$username }}">
                <div class="invalid-feedback">Enter username</div>
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="email" placeholder="Enter email"
                    value="{{ @$email }}">
                <div class="invalid-feedback">Enter email</div>
            </div>
            <div class="col-md-4">
                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="phone" id="phone" placeholder="Enter phone"
                    value="{{ @$phone }}">
                <div class="invalid-feedback">Enter phone</div>
            </div>
        </div>

        {{-- Address, Gender, Roles --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="address" id="address" placeholder="Enter address"
                    value="{{ @$address }}">
                <div class="invalid-feedback">Enter address</div>
            </div>
            <div class="col-md-4">
                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="gender" id="gender" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="Male" @if (@$gender == 'Male') selected @endif>Male</option>
                    <option value="Female" @if (@$gender == 'Female') selected @endif>Female</option>
                    <option value="Other" @if (@$gender == 'Other') selected @endif>Other</option>
                </select>
                <div class="invalid-feedback">Select gender</div>
            </div>
            <div class="col-md-4">
                <label for="roles" class="form-label">Roles <span class="text-danger">*</span></label>
                <select name="roles[]" id="roles" class="form-control" multiple>
                    @foreach ($rolesList as $role)
                        <option value="{{ $role->id }}" @if (!empty($userRoles) && in_array($role->id, $userRoles)) selected @endif>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Select at least one role</div>
            </div>
        </div>

        {{-- Profile Image --}}
        <div class="row mb-2">
            <div class="col-md-12">
                <label for="image" class="form-label">Profile Image</label>
                <input type="file" class="form-control" name="image" id="image" accept="image/*">
                <div class="mt-2">
                    @if (!empty($image))
                        <img src="{{ asset('storage/profiles/' . $image) }}" id="img_preview" class="_image">
                    @else
                        <img src="{{ asset('/no-image.jpg') }}" id="img_preview" class="_image">
                    @endif
                </div>
            </div>
        </div>

    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    {{-- FIX: button is type="button", not submit — disabled state is controlled manually --}}
    <button type="button" class="btn btn-primary" id="saveUser">
        <i class="fa fa-save"></i> {{ empty($id) ? 'Save' : 'Update' }}
    </button>
</div>

{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {

        /* ── Select2 init ─────────────────────────────────────────────
           FIX: dropdownParent must be '#userModel' (the actual modal id), not '#userModal'
        ──────────────────────────────────────────────────────────── */
        $('#roles').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select roles...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#userModel'), // ← FIXED (was #userModal)
        });

        /* ── Image preview ────────────────────────────────────────── */
        $('#image').on('change', function(e) {
            const file = e.target.files[0];
            if (file) $('#img_preview').attr('src', URL.createObjectURL(file));
        });

        /* ── jQuery Validation ────────────────────────────────────── */
        $('#userForm').validate({
            ignore: [], // include hidden Select2 elements
            rules: {
                first_name: 'required',
                last_name: 'required',
                username: 'required',
                email: {
                    required: true,
                    email: true
                },
                phone: 'required',
                address: 'required',
                gender: 'required',
                'roles[]': {
                    required: true,
                    minlength: 1
                }
            },
            messages: {
                first_name: 'Enter first name',
                last_name: 'Enter last name',
                username: 'Enter username',
                email: 'Enter a valid email',
                phone: 'Enter phone',
                address: 'Enter address',
                gender: 'Select gender',
                'roles[]': 'Select at least one role'
            },
            highlight: function(el) {
                $(el).addClass('border-danger');
            },
            unhighlight: function(el) {
                $(el).removeClass('border-danger');
            },
            errorPlacement: function(error, element) {
                if (element.attr('id') === 'roles') {
                    error.insertAfter(element.next('.select2-container'));
                } else {
                    error.insertAfter(element);
                }
            }
        });

        /* ── Save / Update ────────────────────────────────────────────
           FIX: uses #saveUser (not .saveUser class), calls userTable.fnDraw()
                closes '#userModel' (correct modal id)
        ──────────────────────────────────────────────────────────── */
        $('#saveUser').on('click', function() {
            if (!$('#userForm').valid()) return;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            showLoader();

            $.ajax({
                url: $('#userForm').attr('action'),
                type: 'POST',
                data: new FormData(document.getElementById('userForm')),
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    var result = typeof response === 'string' ? JSON.parse(response) :
                        response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        if (typeof userTable !== 'undefined' && userTable) {
                            userTable.fnDraw(); // FIX: fnDraw() not draw()
                        }
                        bootstrap.Modal.getInstance(document.getElementById('userModel'))
                            .hide(); // FIX: userModel
                    } else {
                        showNotification(result.message, 'error');
                        $btn.prop('disabled', false).html(
                            '<i class="fa fa-save"></i> {{ empty($id) ? 'Save' : 'Update' }}'
                        );
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    $btn.prop('disabled', false).html(
                        '<i class="fa fa-save"></i> {{ empty($id) ? 'Save' : 'Update' }}'
                    );
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            $('[name="' + field + '"]').addClass(
                                'is-invalid border-danger');
                            showNotification(messages[0], 'error');
                        });
                    } else {
                        showNotification('Something went wrong!', 'error');
                    }
                }
            });
        });

        /* ── Clear validation styles on input ────────────────────── */
        $(document).on('input change', '#userForm .form-control', function() {
            $(this).removeClass('is-invalid border-danger');
        });

    });
</script>
