<div class="modal-header">
    <h5 class="modal-title">
        {{ @$id ? 'Edit Store' : 'Add Store' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="storeForm" action="{{ route('store.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ @$id ?? '' }}">

    <div class="modal-body">

        {{-- Basic Info --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Store Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Enter store name"
                    value="{{ @$name ?? '' }}" data-required />
                <div class="invalid-feedback">Store name is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Phone<span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" placeholder="98X-XXXXXXX"
                    value="{{ @$phone ?? '' }}" data-required />
                <div class="invalid-feedback">Store phone number is required.</div>

            </div>

            <div class="col-md-4">
                <label class="form-label">Email<span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="store@example.com"
                    value="{{ @$email ?? '' }}" data-required />
                <div class="invalid-feedback">Store email is required.</div>

            </div>

        </div>

        {{-- Address --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Address<span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control" placeholder="Enter address"
                    value="{{ @$address ?? '' }}" data-required />
                <div class="invalid-feedback">Store address is required.</div>

            </div>

            <div class="col-md-4">
                <label class="form-label">City<span class="text-danger">*</span></label>
                <input type="text" name="city" class="form-control" placeholder="Enter city"
                    value="{{ @$city ?? '' }}" data-required />
                <div class="invalid-feedback">Store city is required.</div>

            </div>

            <div class="col-md-4">
                <label class="form-label">Country<span class="text-danger">*</span></label>
                <input type="text" name="country" class="form-control" placeholder="Enter country"
                    value="{{ @$country ?? '' }}" data-required />
                <div class="invalid-feedback">Store country is required.</div>

            </div>

        </div>

        {{-- Coordinates --}}
        {{-- <div class="row g-3 mb-3">

            <div class="col-md-6">
                <label class="form-label">Latitude</label>
                <input type="text" name="latitude" class="form-control" placeholder="e.g. 27.7172"
                    value="{{ @$latitude ?? '' }}" />
            </div>

            <div class="col-md-6">
                <label class="form-label">Longitude</label>
                <input type="text" name="longitude" class="form-control" placeholder="e.g. 85.3240"
                    value="{{ @$longitude ?? '' }}" />
            </div>

        </div> --}}



    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="bx {{ @$id ? 'bx-save' : 'bx-plus' }} me-1"></i>
            {{ @$id ? 'Update' : 'Save' }}
        </button>
    </div>
</form>
