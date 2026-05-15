<div class="modal-header">
    <h5 class="modal-title">
        {{ isset($id) ? 'Edit Inventory' : 'Add Inventory' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="inventoryForm" action="{{ route('inventory.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $id ?? '' }}">
    <input type="hidden" id="preloadItemId" value="{{ $itemid ?? '' }}">
    <input type="hidden" id="preloadVariationId" value="{{ $variationid ?? '' }}">

    <div class="modal-body">

        @if (isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <div class="row g-3 mb-3">

            {{-- Product --}}
            <div class="col-md-4">
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select name="itemid" id="itemSelect" class="form-select" data-required>
                    <option value="">-- Select Product --</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->itemid }}"
                            {{ ($itemid ?? '') == $item->itemid ? 'selected' : '' }}>
                            {{ $item->itemname }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Product is required.</div>
            </div>

            {{-- Variation --}}
            <div class="col-md-4">
                <label class="form-label">Variation <span class="text-danger">*</span></label>
                <select name="variationid" id="variationSelect" class="form-select" data-required>
                    <option value="">-- Select Variation --</option>
                    @if (isset($variationid) && $variationid)
                        <option value="{{ $variationid }}" selected>Loading...</option>
                    @endif
                </select>
                <div class="invalid-feedback">Variation is required.</div>
            </div>

            {{-- Vendor --}}
            <div class="col-md-4">
                <label class="form-label">Vendor <span class="text-danger">*</span></label>
                <select name="vendorid" id="vendorSelect" class="form-select" data-required>
                    <option value="">-- Select Vendor --</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->vendorid }}"
                            {{ ($vendorid ?? '') == $vendor->vendorid ? 'selected' : '' }}>
                            {{ $vendor->vendorname }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Vendor is required.</div>
            </div>

        </div>

        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" name="quantity_available" class="form-control"
                    placeholder="Enter quantity" value="{{ $quantity_available ?? '' }}"
                    data-required min="0" />
                <div class="invalid-feedback">Quantity is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Reorder Threshold <span class="text-danger">*</span></label>
                <input type="number" name="reorder_level" class="form-control"
                    placeholder="Enter threshold" value="{{ $reorder_level ?? '' }}"
                    data-required min="0" />
                <div class="invalid-feedback">Threshold is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Unit Cost <span class="text-danger">*</span></label>
                <input type="number" name="unit_cost" class="form-control"
                    placeholder="Enter unit cost" value="{{ $unit_cost ?? '' }}"
                    data-required min="0" step="0.01" />
                <div class="invalid-feedback">Unit cost is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                <input type="number" name="selling_price" class="form-control"
                    placeholder="Enter selling price" value="{{ $selling_price ?? '' }}"
                    data-required min="0" step="0.01" />
                <div class="invalid-feedback">Selling price is required.</div>
            </div>

            {{-- ✅ Only ONE Manufacture Date --}}
            <div class="col-md-4">
                <label class="form-label">Manufacture Date <span class="text-danger">*</span></label>
                <input type="date" name="manufacturedatead" id="manufactureDate" class="form-control"
                    value="{{ $manufacturedatead ?? '' }}" data-required />
                <div class="invalid-feedback">Manufacture date is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Expire Time In Months <span class="text-danger">*</span></label>
                <input type="number" name="expirymonth" id="expiryMonth" class="form-control"
                    placeholder="e.g. 6" min="1" value="{{ $expirymonth ?? '' }}" data-required />
                <div class="invalid-feedback">Expiry months is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                <input type="date" name="expirydatead" id="expiryDate" class="form-control"
                    value="{{ $expirydatead ?? '' }}" data-required readonly />
                <div class="invalid-feedback">Expiry date is required.</div>
            </div>

        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="bx {{ isset($id) ? 'bx-save' : 'bx-plus' }} me-1"></i>
            {{ isset($id) ? 'Update' : 'Save' }}
        </button>
    </div>
</form>

<script>
    function calculateExpiryDate() {
        var mfgDate = $('#manufactureDate').val();
        var months  = parseInt($('#expiryMonth').val());
        if (!mfgDate || !months || months < 1) {
            $('#expiryDate').val('');
            return;
        }
        var date = new Date(mfgDate);
        date.setMonth(date.getMonth() + months);
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        $('#expiryDate').val(y + '-' + m + '-' + d);
    }

    $(document).on('change input', '#manufactureDate, #expiryMonth', function () {
        calculateExpiryDate();
    });

    // Auto-calculate on edit (pre-filled values)
    if ($('#manufactureDate').val() && $('#expiryMonth').val()) {
        calculateExpiryDate();
    }
</script>