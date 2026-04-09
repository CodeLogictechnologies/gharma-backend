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
                    value="{{ @$phone ?? '' }}" data-required />
                <div class="invalid-feedback">Phone is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="org@example.com"
                    value="{{ @$email ?? '' }}" data-required />
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
                    placeholder="Enter registration number" value="{{ @$registration_number ?? '' }}" data-required />
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
                <input type="text" name="address" class="form-control" placeholder="Enter registration number"
                    value="{{ @$address ?? '' }}" data-required />
                <div class="invalid-feedback">Address is required.</div>
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
