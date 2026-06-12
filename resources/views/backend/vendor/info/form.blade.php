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
</style>

<div class="modal-header">
    <h5 class="modal-title">
        {{ @$id ? 'Edit Vendor' : 'Add Vendor' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="vendorForm" action="{{ route('vendor.info.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ @$id ?? '' }}">

    <div class="modal-body">

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Enter vendor name"
                    value="{{ @$name ?? '' }}" data-required />
                <div class="invalid-feedback">Vendor name is required.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" placeholder="98X-XXXXXXX"
                    value="{{ @$phone ?? '' }}" data-required data-phone />
                <div class="invalid-feedback">Phone is required.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="org@example.com"
                    value="{{ @$email ?? '' }}" data-required data-email />
                <div class="invalid-feedback">Email is required.</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                <input type="text" name="company" class="form-control" placeholder="Enter company name"
                    value="{{ @$company_name ?? '' }}" data-required />
                <div class="invalid-feedback">Company is required.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pan Number <span class="text-danger">*</span></label>
                <input type="text" name="pan" class="form-control" placeholder="Enter pan number"
                    value="{{ @$pan ?? '' }}" data-required />
                <div class="invalid-feedback">Pan Number is required.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                <input type="text" name="registration_number" class="form-control"
                    placeholder="Enter registration number"
                    value="{{ @$registration_number ?? '' }}" data-required />
                <div class="invalid-feedback">Registration Number is required.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" name="city" class="form-control" placeholder="Enter city"
                    value="{{ @$city ?? '' }}" data-required />
                <div class="invalid-feedback">City is required.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control" placeholder="Enter address"
                    value="{{ @$address ?? '' }}" data-required />
                <div class="invalid-feedback">Address is required.</div>
            </div>
        </div>

        {{-- PAN Image & Registration Image --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">PAN Image <span class="text-danger">*</span></label>
                <input type="file" class="form-control" name="pan_image" id="pan_image" accept="image/*"
                    {{ empty($pan_image) ? 'data-required-file' : '' }}>
                <div class="invalid-feedback" id="pan_image_error">PAN image is required.</div>
                <div class="mt-2">
                    @if (!empty($pan_image))
                        <img src="{{ asset('storage/vendor/' . $pan_image) }}" id="pan_img_preview" class="_image">
                    @else
                        <img src="{{ asset('/no-image.jpg') }}" id="pan_img_preview" class="_image">
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Registration Image <span class="text-danger">*</span></label>
                <input type="file" class="form-control" name="registration_number_image" id="registration_number_image"
                    accept="image/*" {{ empty($registration_number_image) ? 'data-required-file' : '' }}>
                <div class="invalid-feedback" id="registration_number_image_error">Registration image is required.</div>
                <div class="mt-2">
                    @if (!empty($registration_number_image))
                        <img src="{{ asset('storage/vendor/' . $registration_number_image) }}" id="reg_img_preview" class="_image">
                    @else
                        <img src="{{ asset('/no-image.jpg') }}" id="reg_img_preview" class="_image">
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="bx {{ @$id ? 'bx-save' : 'bx-plus' }} me-1"></i>
            {{ @$id ? 'Update' : 'Save' }}
        </button>
    </div>
</form>

<script>
(function($) {

    /* ── Image previews ──────────────────────────────────────── */
    $('#pan_image').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowed.includes(file.type)) {
                $(this).addClass('is-invalid border-danger');
                $('#pan_image_error').text('Only image files are allowed (jpeg, png, jpg, gif, webp).').show();
                this.value = '';
                $('#pan_img_preview').attr('src', '{{ asset("/no-image.jpg") }}');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                $(this).addClass('is-invalid border-danger');
                $('#pan_image_error').text('Image size must not exceed 2MB.').show();
                this.value = '';
                $('#pan_img_preview').attr('src', '{{ asset("/no-image.jpg") }}');
                return;
            }
            $(this).removeClass('is-invalid border-danger');
            $('#pan_image_error').hide();
            $('#pan_img_preview').attr('src', URL.createObjectURL(file));
        }
    });

    $('#registration_number_image').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowed.includes(file.type)) {
                $(this).addClass('is-invalid border-danger');
                $('#registration_number_image_error').text('Only image files are allowed (jpeg, png, jpg, gif, webp).').show();
                this.value = '';
                $('#reg_img_preview').attr('src', '{{ asset("/no-image.jpg") }}');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                $(this).addClass('is-invalid border-danger');
                $('#registration_number_image_error').text('Image size must not exceed 2MB.').show();
                this.value = '';
                $('#reg_img_preview').attr('src', '{{ asset("/no-image.jpg") }}');
                return;
            }
            $(this).removeClass('is-invalid border-danger');
            $('#registration_number_image_error').hide();
            $('#reg_img_preview').attr('src', URL.createObjectURL(file));
        }
    });

})(jQuery);
</script>