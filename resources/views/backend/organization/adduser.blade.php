<div class="modal-header">
    <h5 class="modal-title">
        {{ @$id ? 'Edit Organization' : 'Add Organization' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="orgForm" action="{{ route('organization.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ @$id ?? '' }}">
    <input type="hidden" name="userid" value="{{ @$userid ?? '' }}">

    <div class="modal-body">

        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Organization Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Enter organization name"
                    value="{{ @$name ?? '' }}" data-required />
                <div class="invalid-feedback">Organization name is required.</div>
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
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" placeholder="Enter username"
                    value="{{ @$username ?? '' }}" data-required />
                <div class="invalid-feedback">Username is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control" placeholder="Enter address"
                    value="{{ @$address ?? '' }}" data-required />
                <div class="invalid-feedback">Address is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Image<span class="text-danger">*</span></label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*" />
                <div class="invalid-feedback">Image Field is required.</div>

                @if (@$logo)
                    <img id="img_preview" src="{{ asset('uploads/organizations/' . @$logo) }}"
                        style="width:100px; margin-top:10px; border-radius:6px;" alt="Preview" />
                @else
                    <img id="img_preview" src="{{ asset('no-image.jpg') }}"
                        style="width:100px; margin-top:10px; border-radius:6px;" alt="Preview" />
                @endif
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
