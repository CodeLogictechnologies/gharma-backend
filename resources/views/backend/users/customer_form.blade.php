<style>
    ._image {
        max-height: 160px;
        max-width: 160px;
        border: 1px solid #ccc;
    }

    .border-danger {
        border-color: red !important;
    }
</style>

<div class="modal-header">
    <h1 class="modal-title fs-5">Add Customer</h1>
    <button type="button" class="btn-close" id="closeAddCustomerModal"></button>
</div>

<div class="modal-body">
    <form action="{{ route('user.customer.save') }}" method="POST" id="addCustomerForm" enctype="multipart/form-data">
        @csrf

        {{-- Name Fields --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="ac_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="first_name" id="ac_first_name"
                    placeholder="Enter first name">
                <div class="invalid-feedback">Enter first name</div>
            </div>
            <div class="col-md-4">
                <label for="ac_middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" name="middle_name" id="ac_middle_name"
                    placeholder="Enter middle name">
            </div>
            <div class="col-md-4">
                <label for="ac_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="last_name" id="ac_last_name"
                    placeholder="Enter last name">
                <div class="invalid-feedback">Enter last name</div>
            </div>
        </div>

        {{-- Username, Email, Phone --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="ac_username" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="username" id="ac_username"
                    placeholder="Enter username">
                <div class="invalid-feedback">Enter username</div>
            </div>
            <div class="col-md-4">
                <label for="ac_email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="ac_email"
                    placeholder="Enter email">
                <div class="invalid-feedback">Enter email</div>
            </div>
            <div class="col-md-4">
                <label for="ac_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="phone" id="ac_phone"
                    placeholder="Enter phone">
                <div class="invalid-feedback">Enter phone</div>
            </div>
        </div>

        {{-- Address, Gender, Customer Type --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="ac_address" class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="address" id="ac_address"
                    placeholder="Enter address">
                <div class="invalid-feedback">Enter address</div>
            </div>
            <div class="col-md-4">
                <label for="ac_gender" class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="gender" id="ac_gender" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <div class="invalid-feedback">Select gender</div>
            </div>
            <div class="col-md-4">
                <label for="ac_role_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                <select name="role_type" id="ac_role_type" class="form-control">
                    <option value="">-- Select Type --</option>
                    <option value="Wholesaler">Wholesaler</option>
                    <option value="Retailer">Retailer</option>
                </select>
                <div class="invalid-feedback">Select Wholesaler or Retailer</div>
            </div>
        </div>

        {{-- Wholesaler-only fields --}}
        <div id="ac_wholesalerFields" style="display:none;">
            <hr class="my-3">
            <p class="text-muted fw-semibold mb-2" style="font-size:0.85rem;">Wholesaler Information</p>
            <div class="row mb-2">
                <div class="col-md-4">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="company_name" id="ac_company_name"
                        placeholder="Enter company name">
                    <div class="invalid-feedback">Enter company name</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PAN Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="pan_number" id="ac_pan_number"
                        placeholder="Enter PAN number">
                    <div class="invalid-feedback">Enter PAN number</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PAN Image <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="pan_image" id="ac_pan_image" accept="image/*">
                    <div class="mt-2">
                        <img src="{{ asset('/no-image.jpg') }}" id="ac_pan_img_preview" class="_image">
                    </div>
                </div>
            </div>
        </div>

        {{-- Profile Image --}}
        <div class="row mb-2">
            <div class="col-md-12">
                <label for="ac_image" class="form-label">Profile Image</label>
                <input type="file" class="form-control" name="image" id="ac_image" accept="image/*">
                <div class="mt-2">
                    <img src="{{ asset('/no-image.jpg') }}" id="ac_img_preview" class="_image">
                </div>
            </div>
        </div>

    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" id="closeAddCustomerModal2">Close</button>
    <button type="button" class="btn btn-primary" id="saveAddCustomer">
        <i class="fa fa-save"></i> Save
    </button>
</div>

<script>
(function($) {

    /* ── Close modal buttons ─────────────────────────────────── */
    $(document).off('click.closeAddCustomerModal').on('click.closeAddCustomerModal', '#closeAddCustomerModal, #closeAddCustomerModal2', function() {
        var modalEl = document.getElementById('addCustomerModal');
        var instance = bootstrap.Modal.getInstance(modalEl);
        if (instance) instance.hide();
    });

    /* ── Wholesaler fields toggle ────────────────────────────── */
    function checkCustomerType() {
        var isWholesaler = $('#ac_role_type').val() === 'Wholesaler';
        var v = $('#addCustomerForm').validate();

        if (isWholesaler) {
            $('#ac_wholesalerFields').slideDown(200);
            v.settings.rules['company_name'] = 'required';
            v.settings.rules['pan_number']   = 'required';
            v.settings.rules['pan_image']    = 'required';
        } else {
            $('#ac_wholesalerFields').slideUp(200);
            delete v.settings.rules['company_name'];
            delete v.settings.rules['pan_number'];
            delete v.settings.rules['pan_image'];
        }
    }

    $(document).off('change.acRoleType').on('change.acRoleType', '#ac_role_type', checkCustomerType);

    /* ── Image previews ──────────────────────────────────────── */
    $(document).off('change.acImage').on('change.acImage', '#ac_image', function(e) {
        var file = e.target.files[0];
        if (file) $('#ac_img_preview').attr('src', URL.createObjectURL(file));
    });
    $(document).off('change.acPanImage').on('change.acPanImage', '#ac_pan_image', function(e) {
        var file = e.target.files[0];
        if (file) $('#ac_pan_img_preview').attr('src', URL.createObjectURL(file));
    });

    /* ── jQuery Validation ───────────────────────────────────── */
    $('#addCustomerForm').validate({
        ignore: [],
        rules: {
            first_name: 'required',
            last_name:  'required',
            username:   'required',
            email: {
                required: true,
                email:    true
            },
            phone:     'required',
            address:   'required',
            gender:    'required',
            role_type: 'required'
        },
        messages: {
            first_name: 'Enter first name',
            last_name:  'Enter last name',
            username:   'Enter username',
            email: {
                required: 'Enter email',
                email:    'Enter a valid email'
            },
            phone:     'Enter phone',
            address:   'Enter address',
            gender:    'Select gender',
            role_type: 'Select Wholesaler or Retailer'
        },
        highlight: function(el) {
            $(el).addClass('border-danger');
        },
        unhighlight: function(el) {
            $(el).removeClass('border-danger');
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });

    /* ── Save ─────────────────────────────────────────────────── */
    $(document).off('click.saveAddCustomer').on('click.saveAddCustomer', '#saveAddCustomer', function() {
        if (!$('#addCustomerForm').valid()) return;

        var $btn = $(this),
            origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Saving...');

        $.ajax({
            url: $('#addCustomerForm').attr('action'),
            type: 'POST',
            data: new FormData(document.getElementById('addCustomerForm')),
            processData: false,
            contentType: false,
            success: function(response) {
                var result = (typeof response === 'string') ? JSON.parse(response) : response;

                if (result.type === 'success') {
                    showNotification(result.message, 'success');

                    if (result.customer) {
                        var $select = $('#customerSelect');
                        var $opt = $('<option></option>')
                            .attr('value', result.customer.id)
                            .text(result.customer.username);
                        $select.append($opt);
                        $select.val(result.customer.id).trigger('change');
                    }

                    var modalEl = document.getElementById('addCustomerModal');
                    var instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) instance.hide();
                } else {
                    showNotification(result.message, 'error');
                    $btn.prop('disabled', false).html(origHtml);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(origHtml);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showNotification(Object.values(xhr.responseJSON.errors)[0][0], 'error');
                } else {
                    showNotification('Something went wrong!', 'error');
                }
            }
        });
    });

    $('#addCustomerForm').on('input change', '.form-control', function() {
        $(this).removeClass('is-invalid border-danger');
    });

})(jQuery);
</script>
