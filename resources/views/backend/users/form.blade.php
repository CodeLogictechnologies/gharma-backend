{{-- resources/views/backend/user/form.blade.php --}}

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

    /* Select2 custom tweaks */
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
        /* background-color: #0d6efd; */
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
    <form action="{{ route('user.save') }}" method="POST" id="userForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ @$id }}">

        {{-- Name Fields --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="first_name" id="first_name"
                    placeholder="Enter first name" value="{{ @$first_name }}" required>
            </div>
            <div class="col-md-4">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" name="middle_name" id="middle_name"
                    placeholder="Enter middle name" value="{{ @$middle_name }}">
            </div>
            <div class="col-md-4">
                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Enter last name"
                    value="{{ @$last_name }}" required>
            </div>
        </div>

        {{-- Username, Email, Phone --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Enter username"
                    value="{{ @$username }}" required>
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="email" placeholder="Enter email"
                    value="{{ @$email }}" required>
            </div>
            <div class="col-md-4">
                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="phone" id="phone" placeholder="Enter phone"
                    value="{{ @$phone }}" required>
            </div>
        </div>

        {{-- Address, Gender --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="address" id="address" placeholder="Enter address"
                    value="{{ @$address }}" required>
            </div>
            <div class="col-md-4">
                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="gender" id="gender" class="form-control" required>
                    <option value="">Select Gender</option>
                    <option value="Male" @if (@$gender == 'male') selected @endif>Male</option>
                    <option value="Female" @if (@$gender == 'female') selected @endif>Female</option>
                    <option value="Other" @if (@$gender == 'other') selected @endif>Other</option>
                </select>
            </div>



            <div class="col-md-4">
                <label for="roles" class="form-label">Roles <span class="text-danger">*</span></label>
                <select name="roles[]" id="roles" class="form-control" multiple required>
                    @foreach ($rolesList as $role)
                        <option value="{{ $role->id }}" @if (!empty($userRoles) && in_array($role->id, $userRoles)) selected @endif>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
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
    <button type="button" class="btn btn-primary saveUser">
        <i class="fa fa-save"></i> {{ empty($id) ? 'Save' : 'Update' }}
    </button>
</div>

{{-- Select2 JS (must load before calling .select2()) --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {

        $('#roles').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select roles...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#userModel'),
        });

        // ── Preview image ──────────────────────────────────────────────
        $('#image').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                $('#img_preview').attr('src', URL.createObjectURL(file));
            }
        });

        // ── jQuery Validation ──────────────────────────────────────────
        $('#userForm').validate({
            ignore: [], // don't ignore hidden elements (Select2 hides the real <select>)
            rules: {
                first_name: 'required',
                last_name: 'required',
                username: 'required',
                email: 'required',
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
                email: 'Enter email',
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

        // ── Save User ──────────────────────────────────────────────────
        $('.saveUser').off('click');
        $('.saveUser').on('click', function() {
            if ($('#userForm').valid()) {
                showLoader();
                $('#userForm').ajaxSubmit(function(response) {
                    const result = JSON.parse(response);
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        hideLoader();
                        userTable.draw();
                        $('#userForm')[0].reset();
                        $('#roles').val(null).trigger('change'); // reset Select2 tags
                        $('#userModal').modal('hide');
                    } else {
                        showNotification(result.message, 'error');
                        hideLoader();
                    }
                });
            }
        });

    });
</script>
