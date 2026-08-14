<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
    <button type="button" class="btn-close" id="closeUserModal"></button>
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
                <input type="text" class="form-control" name="last_name" id="last_name"
                    placeholder="Enter last name" value="{{ @$last_name }}">
                <div class="invalid-feedback">Enter last name</div>
            </div>
        </div>

        {{-- Username, Email, Phone --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" id="username"
                    placeholder="Enter username" value="{{ @$username }}">
                <div class="invalid-feedback">Enter username</div>
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="email"
                    placeholder="Enter email" value="{{ @$email }}">
                <div class="invalid-feedback">Enter email</div>
            </div>
            <div class="col-md-4">
                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="phone" id="phone"
                    placeholder="Enter phone" value="{{ @$phone }}">
                <div class="invalid-feedback">Enter phone</div>
            </div>
        </div>

        {{-- Address, Gender, Roles --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="address" id="address"
                    placeholder="Enter address" value="{{ @$address }}">
                <div class="invalid-feedback">Enter address</div>
            </div>
            <div class="col-md-4">
                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="gender" id="gender" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="Male" @if(trim(strtolower(@$gender))=='male' ) selected @endif>Male</option>
                    <option value="Female" @if(trim(strtolower(@$gender))=='female' ) selected @endif>Female</option>
                    <option value="Other" @if(trim(strtolower(@$gender))=='other' ) selected @endif>Other</option>
                </select>
                <div class="invalid-feedback">Select gender</div>
            </div>
            <div class="col-md-4">
                <label for="roles" class="form-label">Roles <span class="text-danger">*</span></label>
                <select name="roles[]" id="roles" class="form-control" multiple>
                    @foreach ($rolesList as $role)
                    <option value="{{ $role->id }}"
                        @if (!empty($userRoles) && in_array((string)$role->id, $userRoles)) selected @endif>
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Select at least one role</div>
            </div>
        </div>

        {{-- Wholesaler-only fields --}}
        <div id="wholesalerFields" style="display:none;">
            <hr class="my-3">
            <p class="text-muted fw-semibold mb-2" style="font-size:0.85rem;">Wholesaler Information</p>
            <div class="row mb-2">
                <div class="col-md-4">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="company_name" id="company_name"
                        placeholder="Enter company name" value="{{ @$company_name }}">
                    <div class="invalid-feedback">Enter company name</div>
                </div>
                <!-- <div class="col-md-4">
                    <label class="form-label">Tax Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="tax_number" id="tax_number"
                        placeholder="Enter tax number" value="{{ @$tax_number }}">
                    <div class="invalid-feedback">Enter tax number</div>
                </div> -->
                <!-- <div class="col-md-4">
                    <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="registration_number" id="registration_number"
                        placeholder="Enter registration number" value="{{ @$registration_number }}">
                    <div class="invalid-feedback">Enter registration number</div>
                </div> -->
            </div>
            <div class="row mb-2">
                <div class="col-md-4">
                    <label class="form-label">PAN Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="pan_number" id="pan_number"
                        placeholder="Enter PAN number" value="{{ @$pan_number }}">
                    <div class="invalid-feedback">Enter PAN number</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PAN Image<span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="pan_image" id="pan_image" accept="image/*">
                    <div class="mt-2">
                        @if (!empty($pan_image))
                        <img src="{{ asset('storage/profiles/' . $pan_image) }}" id="pan_img_preview" class="_image">
                        @else
                        <img src="{{ asset('/no-image.jpg') }}" id="pan_img_preview" class="_image">
                        @endif
                    </div>
                </div>
                <!-- <div class="col-md-4">
                    <label class="form-label">Registration Image<span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="registration_number_image" id="registration_number_image" accept="image/*">
                    <div class="mt-2">
                        @if (!empty($registration_number_image))
                        <img src="{{ asset('storage/profiles/' . $registration_number_image) }}" id="reg_img_preview" class="_image">
                        @else
                        <img src="{{ asset('/no-image.jpg') }}" id="reg_img_preview" class="_image">
                        @endif
                    </div>
                </div> -->
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
    <button type="button" class="btn btn-secondary" id="closeUserModal2">Close</button>
    <button type="button" class="btn btn-primary" id="saveUser">
        <i class="fa fa-save"></i> {{ empty($id) ? 'Save' : 'Update' }}
    </button>
</div>

<script>
    (function($) {

        /* ── Close modal buttons ─────────────────────────────────── */
        $(document).off('click.closeUserModal').on('click.closeUserModal', '#closeUserModal, #closeUserModal2', function() {
            var modalEl  = document.getElementById('userModel');
            var instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) {
                instance.hide();
            } else {
                $(modalEl).removeClass('show').css('display', 'none');
                $(modalEl).attr('aria-hidden', 'true').removeAttr('aria-modal role');
                $('#userModelContent').html('');
                setTimeout(function() {
                    if ($('.modal.show').length === 0) {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css({ 'padding-right': '', 'overflow': '' });
                    }
                }, 300);
            }
        });

        /* ── Select2 init ────────────────────────────────────────── */
        $('#roles').select2({
            theme:          'bootstrap-5',
            placeholder:    'Select roles...',
            allowClear:     true,
            width:          '100%',
            dropdownParent: $('#userModel'),
        });

        /* ── Wholesaler role detection ───────────────────────────── */
        var wholesalerRoleId = null;
        $('#roles option').each(function() {
            if ($(this).text().toLowerCase().indexOf('wholesaler') !== -1) {
                wholesalerRoleId = $(this).val();
            }
        });

        var isEdit      = $('#userForm input[name="id"]').val() !== '';
        var hasPanImage = {{ !empty($pan_image) ? 'true' : 'false' }};
        var hasRegImage = {{ !empty($registration_number_image) ? 'true' : 'false' }};

        function checkWholesalerRole() {
            var selected     = $('#roles').val() || [];
            var isWholesaler = wholesalerRoleId && selected.indexOf(String(wholesalerRoleId)) !== -1;
            var v            = $('#userForm').validate();

            if (isWholesaler) {
                $('#wholesalerFields').slideDown(200);
                v.settings.rules['company_name']        = 'required';
                v.settings.rules['tax_number']          = 'required';
                v.settings.rules['registration_number'] = 'required';
                v.settings.rules['pan_number']          = 'required';

                if (!isEdit || !hasPanImage) {
                    v.settings.rules['pan_image'] = 'required';
                } else {
                    delete v.settings.rules['pan_image'];
                }

                if (!isEdit || !hasRegImage) {
                    v.settings.rules['registration_number_image'] = 'required';
                } else {
                    delete v.settings.rules['registration_number_image'];
                }
            } else {
                $('#wholesalerFields').slideUp(200);
                delete v.settings.rules['company_name'];
                delete v.settings.rules['tax_number'];
                delete v.settings.rules['registration_number'];
                delete v.settings.rules['pan_number'];
                delete v.settings.rules['pan_image'];
                delete v.settings.rules['registration_number_image'];
            }
        }

        $('#roles').on('change', checkWholesalerRole);

        /* ── Image previews ──────────────────────────────────────── */
        $('#image').on('change', function(e) {
            var file = e.target.files[0];
            if (file) $('#img_preview').attr('src', URL.createObjectURL(file));
        });
        $('#pan_image').on('change', function(e) {
            var file = e.target.files[0];
            if (file) $('#pan_img_preview').attr('src', URL.createObjectURL(file));
        });
        $('#registration_number_image').on('change', function(e) {
            var file = e.target.files[0];
            if (file) $('#reg_img_preview').attr('src', URL.createObjectURL(file));
        });

        /* ── jQuery Validation ───────────────────────────────────── */
        $.validator.addMethod('gmailOnly', function(value) {
            return /^[a-zA-Z0-9._%+-]+@gmail\.com$/i.test(value);
        }, 'Email must be a valid @gmail.com address');

        $('#userForm').validate({
            ignore: [],
            rules: {
                first_name: 'required',
                last_name:  'required',
                username:   'required',
                email: {
                    required:  true,
                    email:     true,
                    gmailOnly: true
                },
                phone:    'required',
                address:  'required',
                gender:   'required',
                'roles[]': {
                    required:  true,
                    minlength: 1
                }
            },
            messages: {
                first_name: 'Enter first name',
                last_name:  'Enter last name',
                username:   'Enter username',
                email: {
                    required:  'Enter email',
                    email:     'Enter a valid email',
                    gmailOnly: 'Email must be a valid @gmail.com address'
                },
                phone:     'Enter phone',
                address:   'Enter address',
                gender:    'Select gender',
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

        /* ── Trigger Select2 to render pre-selected roles on edit ── */
        $('#roles').trigger('change'); // ← THIS renders existing roles as tags
        checkWholesalerRole();         // ← THEN check if wholesaler fields needed

        /* ── Save / Update ───────────────────────────────────────── */
        $('#saveUser').off('click').on('click', function() {
            if (!$('#userForm').valid()) return;

            var $btn     = $(this),
                origHtml = $btn.html();
            showLoader();

            $.ajax({
                url:         $('#userForm').attr('action'),
                type:        'POST',
                data:        new FormData(document.getElementById('userForm')),
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoader();
                    var result = (typeof response === 'string') ? JSON.parse(response) : response;

                    if (result.type === 'success') {
                        showNotification(result.message, 'success');

                        if (typeof window._forceModalCleanup === 'function') {
                            window._forceModalCleanup();
                        } else {
                            var modalEl  = document.getElementById('userModel');
                            var instance = bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                            setTimeout(function() {
                                $(modalEl).removeClass('show').css('display', 'none');
                                $('#userModelContent').html('');
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open').css({ 'padding-right': '', 'overflow': '' });
                            }, 350);
                        }

                        setTimeout(function() {
                            if (typeof userTable !== 'undefined' && userTable) {
                                userTable.fnDraw();
                            }
                        }, 400);

                    } else {
                        showNotification(result.message, 'error');
                        $btn.prop('disabled', false).html(origHtml);
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    $btn.prop('disabled', false).html(origHtml);
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            $('[name="' + field + '"]').addClass('is-invalid border-danger');
                            showNotification(messages[0], 'error');
                        });
                    } else {
                        showNotification('Something went wrong!', 'error');
                    }
                }
            });
        });

        /* ── Clear validation styles on input ───────────────────── */
        $('#userForm').on('input change', '.form-control', function() {
            $(this).removeClass('is-invalid border-danger');
        });

    })(jQuery);
</script>