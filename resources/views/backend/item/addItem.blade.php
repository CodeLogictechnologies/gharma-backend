<style>
    .image-drop-zone {
        border: 2px dashed #adb5bd;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: #f8f9fa;
        position: relative;
    }

    .image-drop-zone:hover,
    .image-drop-zone.dragover {
        border-color: #0d6efd;
        background: #e8f0fe;
    }

    .image-drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .image-drop-zone .drop-text {
        margin: 8px 0 0;
        color: #6c757d;
        font-size: .875rem;
    }

    #imagePreviewGrid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
    }

    .img-preview-card {
        position: relative;
        width: 110px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #dee2e6;
        background: #fff;
        transition: border-color .2s;
        cursor: grab;
    }

    .img-preview-card:active {
        cursor: grabbing;
    }

    .img-preview-card.is-primary {
        border-color: #0d6efd;
    }

    .img-preview-card img {
        width: 110px;
        height: 90px;
        object-fit: cover;
        display: block;
        pointer-events: none;
    }

    .img-preview-card .img-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 6px;
        background: #f1f3f5;
        font-size: .7rem;
    }

    .btn-remove-img {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: .85rem;
        position: relative;
        z-index: 10;
        padding: 2px 4px;
    }

    .btn-primary-img {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        font-size: .75rem;
        position: relative;
        z-index: 10;
        padding: 2px 4px;
    }

    .img-preview-card.is-primary .btn-primary-img {
        color: #0d6efd;
        font-weight: 600;
    }

    .primary-badge {
        display: none;
        position: absolute;
        top: 4px;
        left: 4px;
        background: #0d6efd;
        color: #fff;
        font-size: .6rem;
        border-radius: 4px;
        padding: 1px 5px;
        z-index: 2;
        pointer-events: none;
    }

    .img-preview-card.is-primary .primary-badge {
        display: block;
    }

    .sortable-ghost {
        opacity: 0.4;
        border: 2px dashed #0d6efd !important;
        background: #e8f0fe !important;
    }

    .variation-row {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 32px 14px 12px;
        margin-bottom: 10px;
        position: relative;
    }

    .remove-variation {
        position: absolute;
        top: 10px;
        right: 12px;
        background: none;
        border: none;
        color: #dc3545;
        font-size: 1.1rem;
        cursor: pointer;
    }

    .section-label {
        font-weight: 600;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #6c757d;
        margin-bottom: 8px;
    }

    .field-error {
        display: none;
        color: #dc3545;
        font-size: .875rem;
        margin-top: 4px;
    }

    .field-error.show {
        display: block;
    }

    .is-invalid-select {
        border-color: #dc3545 !important;
    }

    /* ── Dropdown multi-select ── */
    .multi-select-wrapper {
        position: relative;
    }

    .multi-select-trigger {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
        padding: 8px 34px 8px 12px;
        min-height: 42px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        cursor: text;
        position: relative;
    }

    .multi-select-trigger:hover {
        border-color: #adb5bd;
    }

    .multi-select-wrapper.open .multi-select-trigger {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, .15);
    }

    .multi-select-trigger::after {
        content: '';
        position: absolute;
        right: 14px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-right: 2px solid #6c757d;
        border-bottom: 2px solid #6c757d;
        transform: translateY(-70%) rotate(45deg);
        transition: transform .15s;
        pointer-events: none;
    }

    .multi-select-wrapper.open .multi-select-trigger::after {
        transform: translateY(-30%) rotate(-135deg);
    }

    /* Blinking text-caret shown in the trigger, like a real input */
    .ms-caret {
        display: inline-block;
        width: 1px;
        height: 18px;
        background: #212529;
        animation: ms-blink 1s step-start infinite;
        margin-left: 2px;
    }

    /* ADD THIS RULE — hides the caret unless that dropdown is open */
    .multi-select-wrapper:not(.open) .ms-caret {
        display: none;
    }

    @keyframes ms-blink {
        50% {
            opacity: 0;
        }
    }

    .multi-select-box {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 50;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        max-height: 240px;
        overflow-y: auto;
        background: #fff;
        padding: 4px 0;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    }

    .multi-select-wrapper.open .multi-select-box {
        display: block;
    }

    /* Plain text rows — no checkboxes. Selected row gets a solid blue fill. */
    .multi-select-box .ms-option {
        display: block;
        padding: 10px 14px;
        cursor: pointer;
        font-size: .95rem;
        color: #212529;
        transition: background .12s, color .12s;
        user-select: none;
    }

    .multi-select-box .ms-option:hover {
        background: #f0f4ff;
    }

    .multi-select-box .ms-option.selected {
        background: #0d6efd;
        color: #fff;
        font-weight: 500;
    }

    .multi-select-box .ms-option.selected:hover {
        background: #0d6efd;
    }

    .ms-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
    }

    .ms-placeholder {
        color: #adb5bd;
        font-size: .875rem;
    }

    .ms-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f3f5;
        color: #212529;
        font-size: .8rem;
        font-weight: 500;
        padding: 4px 8px 4px 10px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }

    .ms-tag .ms-tag-remove {
        background: none;
        border: none;
        color: #6c757d;
        font-size: .9rem;
        line-height: 1;
        cursor: pointer;
        padding: 0;
        margin-left: 2px;
        opacity: .7;
    }

    .ms-tag .ms-tag-remove:hover {
        opacity: 1;
        color: #dc3545;
    }

    .ms-empty {
        padding: 10px 12px;
        color: #adb5bd;
        font-size: .85rem;
        font-style: italic;
    }

    .multi-select-wrapper.ms-invalid .multi-select-trigger {
        border-color: #dc3545 !important;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title">
        {{ !empty($id) ? 'Edit Item' : 'Add Item' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="itemForm" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $id ?? '' }}">

    <div class="modal-body">

        {{-- ── Row 1: Name / Type / Brand ── --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="title" placeholder="Enter item name..."
                    value="{{ $data['title'] ?? '' }}">
                <div class="field-error" id="titleError">Item name is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select">
                    @foreach (['Regular', 'Special', 'Featured'] as $t)
                    <option value="{{ $t }}" {{ ($data['type'] ?? 'Regular') === $t ? 'selected' : '' }}>
                        {{ $t }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4" id="brandWrapper">
                <label class="form-label fw-semibold"
                    style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                    Brand <span class="text-danger">*</span>
                </label>

                <select name="brand" id="brandSelect" style="display:none;">
                    @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}"
                        {{ ($data['brand'] ?? '') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                    @endforeach
                </select>

                <div class="multi-select-wrapper" id="brandMultiSelect">
                    <div class="multi-select-trigger" id="brandTrigger" tabindex="0">
                        <div class="ms-tags" id="brandTags">
                            <span class="ms-placeholder">-- Select Brand --</span>
                        </div>
                    </div>
                    <div class="multi-select-box" id="brandCheckList">
                        <div class="ms-search-wrapper" style="position:sticky; top:0; background:#fff; padding:6px 8px; border-bottom:1px solid #eee; z-index:1;">
                            <input type="text" class="form-control form-control-sm ms-search-input" placeholder="Search brand...">
                        </div>
                        <div class="ms-option ms-add-option" data-target="brandSelect" style="color:#0d6efd; font-weight:600;">
                            + Add Brand
                        </div>
                        @forelse ($brands as $brand)
                        <div class="ms-option {{ ($data['brand'] ?? '') == $brand->id ? 'selected' : '' }}"
                            data-id="{{ $brand->id }}" data-label="{{ $brand->name }}" data-target="brandSelect">
                            {{ $brand->name }}
                        </div>
                        @empty
                        <div class="ms-empty">No brands found.</div>
                        @endforelse
                    </div>
                </div>
                <div class="field-error" id="brandError">Please select a brand.</div>
            </div>
        </div>

        {{-- ── Row 2: Product Code / Company Product Code ── --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Product Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="product_code" placeholder="Enter product code..."
                    value="{{ $data['product_code'] ?? '' }}">
                <div class="field-error" id="product_codeError">Product code is required.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Company Product Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="company_product_code"
                    placeholder="Enter company product code..." value="{{ $data['company_product_code'] ?? '' }}">
                <div class="field-error" id="company_product_codeError">Company product code is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">HS Code</label>
                <input type="text" class="form-control" name="hs_code" placeholder="Enter HS code..."
                    value="{{ $data['hs_code'] ?? '' }}">
            </div>

            {{-- Three toggle checkboxes grouped compactly --}}
            <div class="col-auto">
                <div class="d-flex gap-4">
                    <div class="d-flex flex-column align-items-center">
                        <label class="form-label" for="isWholesale">IS WHOLESALE</label>
                        <input type="hidden" name="is_wholesale" value="0">
                        <input class="form-check-input" type="checkbox" id="isWholesale" name="is_wholesale"
                            value="1" aria-label="Is Wholesale"
                            {{ !empty($data['is_wholesale']) ? 'checked' : '' }}>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <label class="form-label" for="vatStatus">VAT STATUS</label>
                        <input type="hidden" name="vat_status" value="0">
                        <input class="form-check-input" type="checkbox" id="vatStatus" name="vat_status" value="1"
                            aria-label="VAT Status" {{ !empty($data['vat_status']) ? 'checked' : '' }}>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <label class="form-label" for="exciseStatus">EXCISE DUTY</label>
                        <input type="hidden" name="excise_status" value="0">
                        <input class="form-check-input" type="checkbox" id="exciseStatus" name="excise_status"
                            value="1" aria-label="Excise Duty Status"
                            {{ !empty($data['excise_status']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- VAT rate field: shown when VAT status is enabled --}}
            <div class="col" id="vatPercentField"
                style="min-width: 130px;
                display: {{ !empty($data['vat_status']) ? 'block' : 'none' }};">
                <label class="form-label" for="vatPercent">VAT Rate <span class="text-danger">*</span></label>
                <select name="vat_percent" id="vatPercent" class="form-select">
                    @foreach (config('vat.taxable') as $rate)
                    <option value="{{ $rate }}"
                        {{ (float) ($data['vat_percent'] ?? config('vat.default')) === (float) $rate ? 'selected' : '' }}>
                        {{ $rate }}%
                    </option>
                    @endforeach
                </select>
                <div class="field-error" id="vatPercentError">VAT rate is required.</div>
            </div>

            {{-- Excise type + value fields side by side --}}
            <div class="col" id="exciseTypeField"
                style="display: {{ !empty($data['excise_status']) ? 'block' : 'none' }};">
                <div class="d-flex gap-2 align-items-start">
                    <div style="min-width: 160px;">
                        <label class="form-label" for="exciseType">Excise Type <span
                                class="text-danger">*</span></label>
                        <select name="excise_type" id="exciseType" class="form-select">
                            <option value="">-- Select Type --</option>
                            <option value="percentage"
                                {{ ($data['excise_type'] ?? '') === 'percentage' ? 'selected' : '' }}>Percentage (%)
                            </option>
                            <option value="fixed" {{ ($data['excise_type'] ?? '') === 'fixed' ? 'selected' : '' }}>
                                Fixed Amount
                            </option>
                        </select>
                        <div class="field-error" id="exciseTypeError">Excise type is required.</div>
                    </div>

                    {{-- Percentage field: shown when excise type = percentage --}}
                    <div id="excisePercentageField"
                        style="min-width: 130px;
                        display: {{ ($data['excise_status'] ?? false) && ($data['excise_type'] ?? '') === 'percentage' ? 'block' : 'none' }};">
                        <label class="form-label" for="excisePercentage">Percentage (%) <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="excise_percentage"
                                id="excisePercentage" placeholder="e.g. 10" min="0" max="100"
                                step="0.01" value="{{ $data['excise_percentage'] ?? '' }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="field-error" id="excisePercentageError">Excise percentage is required.</div>
                    </div>

                    {{-- Fixed amount field: shown when excise type = fixed --}}
                    <div id="exciseFixedField"
                        style="min-width: 150px;
                        display: {{ ($data['excise_status'] ?? false) && ($data['excise_type'] ?? '') === 'fixed' ? 'block' : 'none' }};">
                        <label class="form-label" for="exciseValue">Fixed Amount <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rs</span>
                            <input type="number" class="form-control" name="excise_value" id="exciseValue"
                                placeholder="e.g. 50" min="0" step="0.01"
                                value="{{ $data['excise_value'] ?? '' }}">
                        </div>
                        <div class="field-error" id="exciseValueError">Excise amount is required.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Row 3: Category / Sub Category / Sub Sub Category (dropdown multi-select) ── --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4" id="categoriesWrapper">
                <label class="form-label fw-semibold"
                    style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                    Category <span class="text-danger">*</span>
                </label>

                <select name="categories[]" id="categorySelect" multiple style="display:none;">
                    @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ in_array($cat->id, $data['categories'] ?? []) ? 'selected' : '' }}>
                        {{ $cat->title }}
                    </option>
                    @endforeach
                </select>

                <div class="multi-select-wrapper" id="categoryMultiSelect">
                    <div class="multi-select-trigger" id="categoryTrigger" tabindex="0">
                        <div class="ms-tags" id="categoryTags">
                            <span class="ms-placeholder">-- Select Category --</span>
                        </div>
                    </div>
                    <div class="multi-select-box" id="categoryCheckList">
                        <div class="ms-search-wrapper" style="position:sticky; top:0; background:#fff; padding:6px 8px; border-bottom:1px solid #eee; z-index:1;">
                            <input type="text" class="form-control form-control-sm ms-search-input" placeholder="Search category...">
                        </div>
                        <div class="ms-option ms-add-option" data-target="categorySelect" style="color:#0d6efd; font-weight:600;">
                            + Add Category
                        </div>
                        @forelse ($categories as $cat)
                        <div class="ms-option {{ in_array($cat->id, $data['categories'] ?? []) ? 'selected' : '' }}"
                            data-id="{{ $cat->id }}" data-label="{{ $cat->title }}" data-target="categorySelect">
                            {{ $cat->title }}
                        </div>
                        @empty
                        <div class="ms-empty">No categories found.</div>
                        @endforelse
                    </div>
                </div>
                <div class="field-error" id="categoriesError">Please select at least one category.</div>
            </div>

            <div class="col-md-4" id="subCategoriesWrapper">
                <label class="form-label fw-semibold"
                    style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                    Sub Category
                </label>

                <select name="sub_categories[]" id="subCategorySelect" multiple style="display:none;">
                    @foreach ($subCategories as $sub)
                    <option value="{{ $sub->id }}"
                        {{ in_array($sub->id, $data['sub_categories'] ?? []) ? 'selected' : '' }}>
                        {{ $sub->title }}
                    </option>
                    @endforeach
                </select>

                <div class="multi-select-wrapper" id="subCategoryMultiSelect">
                    <div class="multi-select-trigger" id="subCategoryTrigger" tabindex="0">
                        <div class="ms-tags" id="subCategoryTags">
                            <span class="ms-placeholder">-- Select Sub Category --</span>
                        </div>
                    </div>
                    <div class="multi-select-box" id="subCategoryCheckList">
                        <div class="ms-search-wrapper" style="position:sticky; top:0; background:#fff; padding:6px 8px; border-bottom:1px solid #eee; z-index:1;">
                            <input type="text" class="form-control form-control-sm ms-search-input" placeholder="Search sub category...">
                        </div>
                        <div class="ms-option ms-add-option" data-target="subCategorySelect" style="color:#0d6efd; font-weight:600;">
                            + Add Sub Category
                        </div>
                        @forelse ($subCategories as $sub)
                        <div class="ms-option {{ in_array($sub->id, $data['sub_categories'] ?? []) ? 'selected' : '' }}"
                            data-id="{{ $sub->id }}" data-label="{{ $sub->title }}" data-target="subCategorySelect">
                            {{ $sub->title }}
                        </div>
                        @empty
                        <div class="ms-empty">No sub categories found.</div>
                        @endforelse
                    </div>
                </div>
                <div class="field-error" id="subCategoriesError">Please select at least one sub category.</div>
            </div>

            <div class="col-md-4" id="subSubCategoriesWrapper">
                <label class="form-label fw-semibold"
                    style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;">
                    Sub Sub Category
                </label>

                <select name="sub_sub_categories[]" id="subSubCategorySelect" multiple style="display:none;">
                    @foreach ($subSubCategories ?? [] as $subSub)
                    <option value="{{ $subSub->id }}"
                        {{ in_array($subSub->id, $data['sub_sub_categories'] ?? []) ? 'selected' : '' }}>
                        {{ $subSub->title }}
                    </option>
                    @endforeach
                </select>

                <div class="multi-select-wrapper" id="subSubCategoryMultiSelect">
                    <div class="multi-select-trigger" id="subSubCategoryTrigger" tabindex="0">
                        <div class="ms-tags" id="subSubCategoryTags">
                            <span class="ms-placeholder">-- Select Sub Sub Category --</span>
                        </div>
                    </div>
                    <div class="multi-select-box" id="subSubCategoryCheckList">
                        <div class="ms-search-wrapper" style="position:sticky; top:0; background:#fff; padding:6px 8px; border-bottom:1px solid #eee; z-index:1;">
                            <input type="text" class="form-control form-control-sm ms-search-input" placeholder="Search sub sub category...">
                        </div>
                        <div class="ms-option ms-add-option" data-target="subSubCategorySelect" style="color:#0d6efd; font-weight:600;">
                            + Add Sub Sub Category
                        </div>
                        @forelse ($subSubCategories ?? [] as $subSub)
                        <div class="ms-option {{ in_array($subSub->id, $data['sub_sub_categories'] ?? []) ? 'selected' : '' }}"
                            data-id="{{ $subSub->id }}" data-label="{{ $subSub->title }}" data-target="subSubCategorySelect">
                            {{ $subSub->title }}
                        </div>
                        @empty
                        <div class="ms-empty">No sub sub categories found.</div>
                        @endforelse
                    </div>
                </div>
                <div class="field-error" id="subSubCategoriesError">Please select at least one sub sub category.</div>
            </div>
        </div>

        {{-- ── Row 4: Variations ── --}}
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="section-label mb-0">Variations</p>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addVariation">
                        <i class="fa fa-plus"></i> Add Variation
                    </button>
                </div>

                <div id="variationsContainer">
                    @php
                    $variations = $data['variations'] ?? [
                    [
                    'unit' => '',
                    'variationid' => '',
                    'name' => 'Size',
                    'value' => '',
                    'threshold' => '',
                    'discount_type' => '',
                    'discount_percentage' => '',
                    'discount_amount' => '',
                    'price' => '',
                    'stock' => '',
                    'product_code' => '',
                    'company_product_code' => '',
                    'status' => 'active',
                    ],
                    ];
                    @endphp

                    @foreach ($variations as $i => $v)
                    <div class="variation-row" data-index="{{ $i }}">
                        <button type="button" class="remove-variation" title="Remove row">✕</button>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label mb-1">Unit</label>
                                <select name="variations[{{ $i }}][unit_id]" class="form-select">
                                    @forelse ($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ ($v['unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->unit_name }}
                                    </option>
                                    @empty
                                    <option value="">No units defined</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Attribute</label>
                                <select name="variations[{{ $i }}][attribute_id]" class="form-select">
                                    @forelse ($variationAttributes as $attr)
                                    <option value="{{ $attr->id }}"
                                        {{ ($v['attribute_id'] ?? '') == $attr->id ? 'selected' : '' }}>
                                        {{ $attr->name }}
                                    </option>
                                    @empty
                                    <option value="">No attributes defined</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Value</label>
                                <input type="hidden" name="variations[{{ $i }}][variationid]"
                                    value="{{ $v['variationid'] ?? '' }}">
                                <input type="text" name="variations[{{ $i }}][value]"
                                    class="form-control" placeholder="e.g. Red, XL"
                                    value="{{ $v['value'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Product Code</label>
                                <input type="text" name="variations[{{ $i }}][product_code]"
                                    class="form-control" placeholder="PC-001"
                                    value="{{ $v['product_code'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Company Code</label>
                                <input type="text"
                                    name="variations[{{ $i }}][company_product_code]"
                                    class="form-control" placeholder="CC-001"
                                    value="{{ $v['company_product_code'] ?? '' }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label mb-1">HS Code</label>
                                <input type="text" name="variations[{{ $i }}][hs_code]"
                                    class="form-control" placeholder="HS-001" value="{{ $v['hs_code'] ?? '' }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label mb-1">Threshold</label>
                                <input type="number" name="variations[{{ $i }}][threshold]"
                                    class="form-control" placeholder="0" min="0" step="1"
                                    value="{{ $v['threshold'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Price</label>
                                <input type="number" name="variations[{{ $i }}][price]"
                                    class="form-control" placeholder="0.00" min="0" step="0.01"
                                    value="{{ $v['price'] ?? '' }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label mb-1">Discount Type</label>
                                <select name="variations[{{ $i }}][discount_type]"
                                    class="form-select discount-type-select">
                                    <option value="">-- None --</option>
                                    <option value="percentage"
                                        {{ ($v['discount_type'] ?? '') === 'percentage' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="fixed"
                                        {{ ($v['discount_type'] ?? '') === 'fixed' ? 'selected' : '' }}>Fixed
                                        Amount</option>
                                </select>
                            </div>
                            {{-- Percentage field: shown when discount type = percentage --}}
                            <div class="col-md-3 discount-percentage-col"
                                style="display: {{ ($v['discount_type'] ?? '') === 'percentage' ? 'block' : 'none' }};">
                                <label class="form-label mb-1">Percentage (%)</label>
                                <div class="input-group">
                                    <input type="number"
                                        name="variations[{{ $i }}][discount_percentage]"
                                        class="form-control discount-percentage-input" placeholder="e.g. 2"
                                        min="0" max="100" step="0.01"
                                        value="{{ $v['discount_percentage'] ?? '' }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            {{-- Fixed amount field: shown when discount type = fixed --}}
                            <div class="col-md-3 discount-amount-col"
                                style="display: {{ ($v['discount_type'] ?? '') === 'fixed' ? 'block' : 'none' }};">
                                <label class="form-label mb-1">Fixed Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" name="variations[{{ $i }}][discount_amount]"
                                        class="form-control discount-amount-input" placeholder="e.g. 50"
                                        min="0" step="0.01" value="{{ $v['discount_amount'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <small class="text-muted">Leave Value empty to skip a row.</small>
            </div>
        </div>

        {{-- ── Row 5: Images ── --}}
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <p class="section-label">Product Images</p>

                <div class="image-drop-zone" id="imageDropZone">
                    <input type="file" id="productImages" name="images[]" accept="image/*" multiple>
                    <div style="font-size:2rem">🖼️</div>
                    <p class="drop-text">
                        <strong>Click to upload</strong> or drag &amp; drop<br>
                        JPG, JPEG, PNG — multiple allowed
                    </p>
                </div>

                <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">

                <div id="imagePreviewGrid">
                    @if (!empty($data['images']))
                    @foreach ($data['images'] as $i => $img)
                    <div class="img-preview-card {{ $i === 0 ? 'is-primary' : '' }}"
                        data-index="{{ $i }}" data-type="existing"
                        data-db-id="{{ $img['id'] }}">
                        <span class="primary-badge">Primary</span>
                        <img src="{{ asset('storage/items/' . $img['filename']) }}" alt="product image">
                        <div class="img-actions">
                            <button type="button" class="btn-primary-img">★ Primary</button>
                            <button type="button" class="btn-remove-img">✕</button>
                        </div>
                        <input type="hidden" class="kept-path" name="kept_images[]"
                            value="{{ $img['filename'] }}">
                        <input type="hidden" class="kept-id" name="kept_image_ids[]"
                            value="{{ $img['id'] }}">
                    </div>
                    @endforeach
                    @endif
                </div>

                <small class="text-muted d-block mt-1">
                    Click <strong>★ Primary</strong> to set the main photo.
                    <strong>Drag</strong> to reorder. First image is primary by default.
                </small>
            </div>
        </div>

        {{-- ── Row 6: Description ── --}}
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label class="form-label" for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="5">{!! $data['description'] ?? '' !!}</textarea>
            </div>
        </div>

    </div>{{-- /modal-body --}}
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveItemBtn">
            <i class="fa {{ !empty($id) ? 'fa-save' : 'fa-plus' }} me-1"></i>
            {{ !empty($id) ? 'Update' : 'Save' }}
        </button>
    </div>
</form>

{{-- ── Quick-Add Modals: Brand / Category / Sub Category / Sub Sub Category ── --}}
{{-- These are OUTSIDE #itemForm on purpose — their inputs must never be
     picked up by `new FormData($('#itemForm')[0])` when the Item form saves. --}}

<div class="modal fade" id="addBrandModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" placeholder="Enter brand name...">
                <div class="text-danger small mt-1 quick-add-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary quick-add-save-btn" data-quick-add-type="brand">
                    <i class="fa fa-plus me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" placeholder="Enter category name...">
                <div class="text-danger small mt-1 quick-add-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary quick-add-save-btn" data-quick-add-type="category">
                    <i class="fa fa-plus me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSubCategoryModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Sub Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Sub Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="title" placeholder="Enter sub category name...">
                <select name="parent_id" style="display:none;"></select>
                <div class="text-danger small mt-1 quick-add-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary quick-add-save-btn" data-quick-add-type="subCategory">
                    <i class="fa fa-plus me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSubSubCategoryModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Sub Sub Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Sub Sub Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="title" placeholder="Enter sub sub category name...">
                <select name="parent_id" style="display:none;"></select>
                <div class="text-danger small mt-1 quick-add-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary quick-add-save-btn" data-quick-add-type="subSubCategory">
                    <i class="fa fa-plus me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    $(function() {
                let varIdx = {{ count($data['variations'] ?? [['']]) }};

        /* ── Prefill Name / Product Code coming from the Sales Voucher "+ Add" flow ── */
        if (window.prefillItemName) {
            $('[name="title"]').val(window.prefillItemName);
            window.prefillItemName = '';
        }
        // if (window.prefillProductCode) {
        //     $('[name="product_code"]').val(window.prefillProductCode);
        //     window.prefillProductCode = '';
        // }

        /* ── Prefill Product Code into a Variation row instead of the item-level field ──
   When editing an existing item, product codes belong to variations, not the item itself. */
if (window.prefillProductCode) {
    var isEditingExistingItem = !!$('#itemForm input[name="id"]').val();

    if (isEditingExistingItem) {
        if (window.prefillVariationId) {
            // A specific variation was already selected in the Sales row —
            // the user wants to correct THAT variation's code, not add a new one.
            var $targetRow = $('#variationsContainer .variation-row').filter(function() {
                return $(this).find('input[name$="[variationid]"]').val() === window.prefillVariationId;
            }).first();

            if (!$targetRow.length) {
                // Fallback: couldn't find it (shouldn't normally happen) — add a new row instead.
                $('#variationsContainer').append(newVariationRow(varIdx++));
                $targetRow = $('#variationsContainer .variation-row').last();
            }

            $targetRow.find('input[name$="[product_code]"]').val(window.prefillProductCode);
        } else {
            // Item selected but no specific variation — add a NEW variation row for the incoming code.
            $('#variationsContainer').append(newVariationRow(varIdx++));
            var $targetRow = $('#variationsContainer .variation-row').last();
            $targetRow.find('input[name$="[product_code]"]').val(window.prefillProductCode);
        }
    } else {
        // Brand new item, no variations yet — the item-level code is the right place.
        $('[name="product_code"]').val(window.prefillProductCode);
    }

    window.prefillProductCode = '';
    window.prefillVariationId = '';
}

        /* ─────────────────────────────────────────────
           QUICK-ADD CONFIG (must be before autoQuickSave)
        ───────────────────────────────────────────── */
        const quickAddConfig = {
            brand: {
                url: '{{ route("brand.save") }}',
                modal: '#addBrandModal',
                nameField: 'name',
                eventName: 'brand:created',
                responseKey: 'brand'
            },
            category: {
                url: '{{ route("category.save") }}',
                modal: '#addCategoryModal',
                nameField: 'name',
                eventName: 'category:created',
                responseKey: 'category'
            },
            subCategory: {
                url: '{{ route("category.save") }}',
                modal: '#addSubCategoryModal',
                nameField: 'name',
                eventName: 'subCategory:created',
                responseKey: 'category'
            },
            subSubCategory: {
                url: '{{ route("category.save") }}',
                modal: '#addSubSubCategoryModal',
                nameField: 'name',
                eventName: 'subSubCategory:created',
                responseKey: 'category'
            }
        };

        /* ─────────────────────────────────────────────
           AUTO QUICK-SAVE (no modal popup)
        ───────────────────────────────────────────── */
        function autoQuickSave(type, name) {
            const cfg = quickAddConfig[type];
            if (!cfg) return;

            const formData = new FormData();
            formData.append(cfg.nameField, name);
            formData.append('quick_add', '1');
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            // Add parent_id for sub-categories
            if (type === 'subCategory') {
                const selectedCategories = $('#categorySelect').val();
                if (selectedCategories && selectedCategories.length === 1) {
                    formData.append('category_id', selectedCategories[0]);
                }
            }
            if (type === 'subSubCategory') {
                const selectedSubCategories = $('#subCategorySelect').val();
                if (selectedSubCategories && selectedSubCategories.length === 1) {
                    formData.append('subcategory_id', selectedSubCategories[0]); // was 'subcategory'
                }
            }

            $.ajax({
                url: cfg.url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    const result = (typeof response === 'string') ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message || 'Saved successfully.', 'success');
                        const created = result[cfg.responseKey];
                        if (created) {
                            $(document).trigger(cfg.eventName, [created]);
                        }
                    } else {
                        showNotification(result.message || 'Something went wrong.', 'error');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message ||
                        xhr.responseJSON?.errors?.[Object.keys(xhr.responseJSON.errors)[0]]?.[0] ||
                        'Something went wrong. Please try again.';
                    showNotification(msg, 'error');
                }
            });
        }

        /* ─────────────────────────────────────────────
        // VAT STATUS → show/hide VAT rate field
        ───────────────────────────────────────────── */
        function toggleVatFields() {
            const enabled = $('#vatStatus').is(':checked');
            $('#vatPercentField').toggle(enabled);
        }
        $('#vatStatus').on('change', toggleVatFields);
        toggleVatFields();

        /* ─────────────────────────────────────────────
        // EXCISE DUTY → show/hide type & value fields
        ───────────────────────────────────────────── */
        function applyExciseTypeUI(type) {
            $('#excisePercentageField').toggle(type === 'percentage');
            $('#exciseFixedField').toggle(type === 'fixed');
            if (type !== 'percentage') $('#excisePercentage').val('');
            if (type !== 'fixed') $('#exciseValue').val('');
        }

        function toggleExciseFields() {
            const enabled = $('#exciseStatus').is(':checked');
            $('#exciseTypeField').toggle(enabled);
            if (!enabled) {
                $('#exciseType').val('');
            }
            applyExciseTypeUI(enabled ? $('#exciseType').val() : '');
        }
        $('#exciseStatus').on('change', toggleExciseFields);
        $('#exciseType').on('change', function() {
            applyExciseTypeUI(this.value);
        });
        toggleExciseFields();

        /* ─────────────────────────────────────────────
           IMAGE UPLOAD & PREVIEW
        ───────────────────────────────────────────── */
        let newFiles = [];

        const dropZone = document.getElementById('imageDropZone');

        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
            handleFiles(Array.from(e.dataTransfer.files));
        });

        $('#productImages').on('change', function() {
            handleFiles(Array.from(this.files));
            this.value = '';
        });

        function handleFiles(files) {
            files.filter(f => f.type.startsWith('image/')).forEach(file => {
                const reader = new FileReader();
                const fileIdx = newFiles.length;
                newFiles.push(file);

                reader.onload = e => {
                    const isPrimary = ($('#imagePreviewGrid .img-preview-card').length === 0);
                    $('#imagePreviewGrid').append(`
                <div class="img-preview-card ${isPrimary ? 'is-primary' : ''}"
                     data-index="${fileIdx}" data-type="new">
                    <span class="primary-badge">Primary</span>
                    <img src="${e.target.result}" alt="preview">
                    <div class="img-actions">
                        <button type="button" class="btn-primary-img">★ Primary</button>
                        <button type="button" class="btn-remove-img">✕</button>
                    </div>
                </div>`);
                    if (isPrimary) syncPrimary();
                    syncFileInput();
                };
                reader.readAsDataURL(file);
            });
        }

        /* ─────────────────────────────────────────────
           AFTER-CREATE REFRESH HANDLERS
        ───────────────────────────────────────────── */

        $(document).on('brand:created', function(e, brand) {
            if (!brand || !brand.id) return;

            const $list = $('#brandCheckList');
            $list.find('.ms-empty').remove();
            $list.find('.ms-option.selected').removeClass('selected'); // brand is single-select

            let $existing = $list.find(`.ms-option[data-id="${brand.id}"]`);
            if ($existing.length) {
                $existing.addClass('selected');
            } else {
                $list.find('.ms-add-option').after(
                    `<div class="ms-option selected" data-id="${brand.id}" data-label="${brand.name}" data-target="brandSelect">${brand.name}</div>`
                );
            }

            const $select = $('#brandSelect');
            if (!$select.find(`option[value="${brand.id}"]`).length) {
                $select.append(`<option value="${brand.id}">${brand.name}</option>`);
            }
            $select.val(brand.id);

            $('#brandTags').html(`
        <span class="ms-tag" data-id="${brand.id}">
            ${brand.name}
            <button type="button" class="ms-tag-remove" data-id="${brand.id}">×</button>
        </span><span class="ms-caret"></span>`);

            $select.removeClass('is-invalid-select');
            $('#brandError').removeClass('show');
        });

        $(document).on('category:created', function(e, category) {
            if (!category || !category.id) return;
            addOptionToMultiSelect({
                listId: 'categoryCheckList',
                selectId: 'categorySelect',
                tagsId: 'categoryTags',
                errorId: 'categoriesError',
                wrapperId: 'categoryMultiSelect',
                placeholder: '-- Select Category --',
                id: category.id,
                label: category.title
            });
            refreshSubCategoryOptions();
        });

        $(document).on('subCategory:created', function(e, subCategory) {
            if (!subCategory || !subCategory.id) return;
            addOptionToMultiSelect({
                listId: 'subCategoryCheckList',
                selectId: 'subCategorySelect',
                tagsId: 'subCategoryTags',
                errorId: 'subCategoriesError',
                wrapperId: 'subCategoryMultiSelect',
                placeholder: '-- Select Sub Category --',
                id: subCategory.id,
                label: subCategory.title
            });
            refreshSubSubCategoryOptions();
        });

        $(document).on('subSubCategory:created', function(e, subSubCategory) {
            if (!subSubCategory || !subSubCategory.id) return;
            addOptionToMultiSelect({
                listId: 'subSubCategoryCheckList',
                selectId: 'subSubCategorySelect',
                tagsId: 'subSubCategoryTags',
                errorId: 'subSubCategoriesError',
                wrapperId: 'subSubCategoryMultiSelect',
                placeholder: '-- Select Sub Sub Category --',
                id: subSubCategory.id,
                label: subSubCategory.title
            });
        });

        function addOptionToMultiSelect(cfg) {
            const $list = $('#' + cfg.listId);
            const $select = $('#' + cfg.selectId);
            const $tags = $('#' + cfg.tagsId);

            $list.find('.ms-empty').remove();

            let $existing = $list.find(`.ms-option[data-id="${cfg.id}"]`);
            if ($existing.length) {
                $existing.addClass('selected');
            } else {
                const $newOption = $(`
                    <div class="ms-option selected" data-id="${cfg.id}" data-label="${cfg.label}" data-target="${cfg.selectId}">
                        ${cfg.label}
                    </div>`);
                $list.find('.ms-add-option').after($newOption);
            }

            if (!$select.find(`option[value="${cfg.id}"]`).length) {
                $select.append(`<option value="${cfg.id}">${cfg.label}</option>`);
            }
            $select.find(`option[value="${cfg.id}"]`).prop('selected', true);

            $tags.empty();
            const $selected = $list.find('.ms-option.selected');
            if (!$selected.length) {
                $tags.append(`<span class="ms-placeholder">${cfg.placeholder}</span>`);
            } else {
                $selected.each(function() {
                    if ($(this).hasClass('ms-add-option')) return;
                    const id = $(this).data('id');
                    const label = $(this).data('label');
                    $tags.append(`
                        <span class="ms-tag" data-id="${id}">
                            ${label}
                            <button type="button" class="ms-tag-remove" data-id="${id}">×</button>
                        </span>`);
                });
            }
            $tags.append('<span class="ms-caret"></span>');

            $('#' + cfg.errorId).removeClass('show');
            $('#' + cfg.wrapperId).removeClass('ms-invalid');
        }

        $(document).on('click', '.btn-primary-img', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#imagePreviewGrid .img-preview-card').removeClass('is-primary');
            $(this).closest('.img-preview-card').addClass('is-primary');
            syncPrimary();
        });

        $(document).on('click', '.btn-remove-img', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const card = $(this).closest('.img-preview-card');
            const wasPrim = card.hasClass('is-primary');

            if (card.data('type') === 'new') {
                const idx = parseInt(card.data('index'));
                if (!isNaN(idx)) newFiles[idx] = null;
            }

            card.remove();
            syncFileInput();

            if (wasPrim) {
                const first = $('#imagePreviewGrid .img-preview-card').first();
                if (first.length) {
                    first.addClass('is-primary');
                    syncPrimary();
                } else {
                    $('#primaryImageIndex').val('');
                }
            }
        });

        function syncPrimary() {
            const cards = $('#imagePreviewGrid .img-preview-card');
            const primIdx = cards.index(cards.filter('.is-primary'));
            $('#primaryImageIndex').val(primIdx >= 0 ? primIdx : 0);
        }

        function syncFileInput() {
            const dt = new DataTransfer();
            const newCards = [...document.querySelectorAll(
                '#imagePreviewGrid .img-preview-card[data-type="new"]')];
            newCards.forEach(card => {
                const idx = parseInt(card.dataset.index);
                if (!isNaN(idx) && newFiles[idx]) dt.items.add(newFiles[idx]);
            });
            document.getElementById('productImages').files = dt.files;
        }

        syncPrimary();

        /* ─────────────────────────────────────────────
           DRAG-TO-REORDER (SortableJS)
        ───────────────────────────────────────────── */
        Sortable.create(document.getElementById('imagePreviewGrid'), {
            animation: 200,
            ghostClass: 'sortable-ghost',
            delay: 100,
            filter: '.btn-primary-img, .btn-remove-img',
            preventOnFilter: true,
            onStart: () => {
                dropZone.style.pointerEvents = 'none';
            },
            onEnd: () => {
                dropZone.style.pointerEvents = '';
                syncPrimary();
                syncFileInput();
            }
        });

        function syncImageOrder() {
            $('#itemForm input[name="image_order[]"]').remove();
            document.querySelectorAll('#imagePreviewGrid .img-preview-card[data-type="existing"]')
                .forEach(card => {
                    const dbId = card.dataset.dbId;
                    if (dbId) {
                        $('#itemForm').append(`<input type="hidden" name="image_order[]" value="${dbId}">`);
                    }
                });
        }

        /* ─────────────────────────────────────────────
           CUSTOM DROPDOWN MULTI-SELECT (no checkboxes)
        ───────────────────────────────────────────── */
        function initMultiSelect(wrapperId, checkListId, hiddenSelectId, tagsId, errorId, placeholder, onChange, addEventName) {
            const $wrapper = $('#' + wrapperId);
            const $trigger = $wrapper.find('.multi-select-trigger');
            const $list = $('#' + checkListId);
            const $select = $('#' + hiddenSelectId);
            const $tags = $('#' + tagsId);
            const $error = $('#' + errorId);
            const $searchInput = $list.find('.ms-search-input');

            function rebuildTags() {
                $tags.empty();
                const $selected = $list.find('.ms-option.selected');
                if (!$selected.length) {
                    $tags.append(`<span class="ms-placeholder">${placeholder}</span>`);
                } else {
                    $selected.each(function() {
                        if ($(this).hasClass('ms-add-option')) return;
                        const id = $(this).data('id');
                        const label = $(this).data('label');
                        $tags.append(`
                            <span class="ms-tag" data-id="${id}">
                                ${label}
                                <button type="button" class="ms-tag-remove" data-id="${id}">×</button>
                            </span>`);
                    });
                }
                $tags.append('<span class="ms-caret"></span>');
            }

            function syncSelect() {
                $select.find('option').prop('selected', false);
                $list.find('.ms-option.selected').each(function() {
                    if ($(this).hasClass('ms-add-option')) return;
                    $select.find(`option[value="${$(this).data('id')}"]`).prop('selected', true);
                });
            }

            function clearError() {
                const selectedValues = $select.val();
                if (selectedValues && selectedValues.length > 0) {
                    $error.removeClass('show');
                    $wrapper.removeClass('ms-invalid');
                }
            }

            function toggleOption($opt) {
                if ($opt.hasClass('ms-add-option')) return;
                const nowSelected = !$opt.hasClass('selected');
                $opt.toggleClass('selected', nowSelected);
                syncSelect();
                rebuildTags();
                clearError();
                if (typeof onChange === 'function') onChange();
            }

            $list.on('click', '.ms-add-option', function(e) {
                e.stopPropagation();
                const typedTerm = $searchInput.val().trim();
                if (!typedTerm) {
                    closeDropdown();
                    return;
                }

                // Auto-save without showing modal
                const typeMap = {
                    'item:openAddBrand': 'brand',
                    'item:openAddCategory': 'category',
                    'item:openAddSubCategory': 'subCategory',
                    'item:openAddSubSubCategory': 'subSubCategory'
                };
                const quickType = typeMap[addEventName];

                if (quickType) {
                    autoQuickSave(quickType, typedTerm);
                }
                closeDropdown();
            });

            function openDropdown() {
                $('.multi-select-wrapper.open').not($wrapper).removeClass('open');
                $wrapper.addClass('open');
                $searchInput.val('').trigger('input');
                setTimeout(() => $searchInput.trigger('focus'), 0);
            }

            function closeDropdown() {
                $wrapper.removeClass('open');
            }

            $trigger.on('click', function(e) {
                e.stopPropagation();
                $wrapper.hasClass('open') ? closeDropdown() : openDropdown();
            });

            $trigger.on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $wrapper.hasClass('open') ? closeDropdown() : openDropdown();
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });

            $list.on('click', function(e) {
                e.stopPropagation();
            });

            $list.on('click', '.ms-option', function() {
                toggleOption($(this));
            });

            $searchInput.on('input', function() {
                const term = $(this).val().toLowerCase();
                $list.find('.ms-option').each(function() {
                    if ($(this).hasClass('ms-add-option')) {
                        $(this).show();
                        return;
                    }
                    const label = $(this).data('label') || '';
                    $(this).toggle(label.toLowerCase().indexOf(term) > -1);
                });
            });

            $tags.on('click', '.ms-tag-remove', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                $list.find(`.ms-option[data-id="${id}"]`).removeClass('selected');
                syncSelect();
                rebuildTags();
                if (typeof onChange === 'function') onChange();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest($wrapper).length) closeDropdown();
            });

            syncSelect();
            rebuildTags();
        }

        initMultiSelect(
            'brandMultiSelect', 'brandCheckList', 'brandSelect', 'brandTags',
            'brandError', '-- Select Brand --', null, 'item:openAddBrand'
        );

        initMultiSelect('categoryMultiSelect', 'categoryCheckList', 'categorySelect', 'categoryTags',
            'categoriesError', '-- Select Category --', refreshSubCategoryOptions, 'item:openAddCategory');

        initMultiSelect('subCategoryMultiSelect', 'subCategoryCheckList', 'subCategorySelect',
            'subCategoryTags', 'subCategoriesError', '-- Select Sub Category --', refreshSubSubCategoryOptions, 'item:openAddSubCategory');

        initMultiSelect('subSubCategoryMultiSelect', 'subSubCategoryCheckList', 'subSubCategorySelect',
            'subSubCategoryTags', 'subSubCategoriesError', '-- Select Sub Sub Category --', null, 'item:openAddSubSubCategory');

        refreshSubCategoryOptions();

        function refreshSubCategoryOptions() {
            const categoryIds = $('#categorySelect').val() || [];

            if (!categoryIds.length) {
                rebuildSubCategoryList([]);
                return;
            }

            $.ajax({
                url: '{{ route("item.subcategories") }}',
                type: 'GET',
                data: {
                    category_ids: categoryIds
                },
                success: function(response) {
                    rebuildSubCategoryList(response.data || []);
                },
                error: function() {
                    rebuildSubCategoryList([]);
                }
            });
        }

        function rebuildSubCategoryList(subCats) {
            const previouslySelected = $('#subCategoryCheckList .ms-option.selected')
                .map(function() {
                    return String($(this).data('id'));
                }).get();

            const $list = $('#subCategoryCheckList');
            const $select = $('#subCategorySelect');
            const $tags = $('#subCategoryTags');

            $list.find('.ms-option:not(.ms-add-option), .ms-empty').remove();
            $select.empty();

            if (!subCats.length) {
                $list.append('<div class="ms-empty">No sub categories found.</div>');
            } else {
                subCats.forEach(function(sub) {
                    const isSelected = previouslySelected.includes(String(sub.id));
                    $list.append(`
                <div class="ms-option ${isSelected ? 'selected' : ''}"
                     data-id="${sub.id}" data-label="${sub.title}"
                     data-target="subCategorySelect">
                    ${sub.title}
                </div>`);
                    $select.append(`<option value="${sub.id}" ${isSelected ? 'selected' : ''}>${sub.title}</option>`);
                });
            }

            $tags.empty();
            const $selectedNow = $list.find('.ms-option.selected');
            if (!$selectedNow.length) {
                $tags.append('<span class="ms-placeholder">-- Select Sub Category --</span>');
            } else {
                $selectedNow.each(function() {
                    const id = $(this).data('id');
                    const label = $(this).data('label');
                    $tags.append(`
                <span class="ms-tag" data-id="${id}">
                    ${label}
                    <button type="button" class="ms-tag-remove" data-id="${id}">×</button>
                </span>`);
                });
            }
            $tags.append('<span class="ms-caret"></span>');

            refreshSubSubCategoryOptions();
        }

        function refreshSubSubCategoryOptions() {
            const subCategoryIds = $('#subCategorySelect').val() || [];

            if (!subCategoryIds.length) {
                rebuildSubSubCategoryList([]);
                return;
            }

            $.ajax({
                url: '{{ route("item.subsubcategories") }}',
                type: 'GET',
                data: {
                    sub_category_ids: subCategoryIds
                },
                success: function(response) {
                    rebuildSubSubCategoryList(response.data || []);
                },
                error: function() {
                    rebuildSubSubCategoryList([]);
                }
            });
        }

        function rebuildSubSubCategoryList(subSubCats) {
            const previouslySelected = $('#subSubCategoryCheckList .ms-option.selected')
                .map(function() {
                    return String($(this).data('id'));
                }).get();

            const $list = $('#subSubCategoryCheckList');
            const $select = $('#subSubCategorySelect');
            const $tags = $('#subSubCategoryTags');

            $list.find('.ms-option:not(.ms-add-option), .ms-empty').remove();
            $select.empty();

            if (!subSubCats.length) {
                $list.append('<div class="ms-empty">No sub sub categories found.</div>');
            } else {
                subSubCats.forEach(function(subSub) {
                    const isSelected = previouslySelected.includes(String(subSub.id));
                    $list.append(`
                <div class="ms-option ${isSelected ? 'selected' : ''}"
                     data-id="${subSub.id}" data-label="${subSub.title}"
                     data-target="subSubCategorySelect">
                    ${subSub.title}
                </div>`);
                    $select.append(`<option value="${subSub.id}" ${isSelected ? 'selected' : ''}>${subSub.title}</option>`);
                });
            }

            $tags.empty();
            const $selectedNow = $list.find('.ms-option.selected');
            if (!$selectedNow.length) {
                $tags.append('<span class="ms-placeholder">-- Select Sub Sub Category --</span>');
            } else {
                $selectedNow.each(function() {
                    const id = $(this).data('id');
                    const label = $(this).data('label');
                    $tags.append(`
                <span class="ms-tag" data-id="${id}">
                    ${label}
                    <button type="button" class="ms-tag-remove" data-id="${id}">×</button>
                </span>`);
                });
            }
            $tags.append('<span class="ms-caret"></span>');
        }

        /* ─────────────────────────────────────────────
           CLEAR ERRORS ON CHANGE
        ───────────────────────────────────────────── */
        $('[name="title"]').on('input', function() {
            if ($(this).val().trim()) {
                $(this).removeClass('is-invalid-select');
                $('#titleError').removeClass('show');
            }
        });

        $('[name="brand"]').on('change', function() {
            if ($(this).val() && $(this).val() !== '__add_brand__') {
                $(this).removeClass('is-invalid-select');
                $('#brandError').removeClass('show');
            }
        });

        /* ─────────────────────────────────────────────
           VARIATION ROWS
        ───────────────────────────────────────────── */
               const variationAttributeOptions = @json($variationAttributes->map(fn($a) => ['id' => $a->id, 'name' => $a->name]));
function buildAttributeOptions(selectedId) {
    if (!variationAttributeOptions.length) {
        return '<option value="">No attributes defined</option>';
    }
    return variationAttributeOptions.map(a => {
        const sel = (selectedId && a.id === selectedId) ? ' selected' : '';
        return `<option value="${a.id}"${sel}>${a.name}</option>`;
    }).join('');
}

const variationUnitOptions = @json($units->map(fn($u) => ['id' => $u->id, 'name' => $u->unit_name]));

function buildUnitOptions(selectedId) {
            if (!variationUnitOptions.length) {
                return '<option value="">No units defined</option>';
            }
            return variationUnitOptions.map(u => {
                const sel = (selectedId && u.id === selectedId) ? ' selected' : '';
                return `<option value="${u.id}"${sel}>${u.name}</option>`;
            }).join('');
        }

        function newVariationRow(idx) {
            return `
        <div class="variation-row" data-index="${idx}">
            <button type="button" class="remove-variation" title="Remove">✕</button>
            <div class="row g-2 align-items-end">
                 <div class="col-md-2">
                    <label class="form-label mb-1">Unit</label>
                    <select name="variations[${idx}][unit_id]" class="form-select">
                        ${buildUnitOptions(null)}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Attribute</label>
                    <select name="variations[${idx}][attribute_id]" class="form-select">
                        ${buildAttributeOptions(null)}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Value</label>
                    <input type="hidden" name="variations[${idx}][variationid]" value="">
                    <input type="text" name="variations[${idx}][value]"
                           class="form-control" placeholder="e.g. Red, XL">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Product Code</label>
                    <input type="text" name="variations[${idx}][product_code]"
                           class="form-control" placeholder="PC-001">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Company Code</label>
                    <input type="text" name="variations[${idx}][company_product_code]"
                           class="form-control" placeholder="CC-001">
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">HS Code</label>
                    <input type="text" name="variations[${idx}][hs_code]"
                           class="form-control" placeholder="HS-001" maxlength="15">
                </div>
                <div class="col-md-1">
                    <label class="form-label mb-1">Threshold</label>
                    <input type="number" name="variations[${idx}][threshold]"
                           class="form-control" placeholder="0" min="0" step="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Price</label>
                    <input type="number" name="variations[${idx}][price]"
                           class="form-control" placeholder="0.00" min="0" step="0.01">
                </div>

                <div class="col-md-3">
                    <label class="form-label mb-1">Discount Type</label>
                    <select name="variations[${idx}][discount_type]" class="form-select discount-type-select">
                        <option value="">-- None --</option>
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
                <div class="col-md-3 discount-percentage-col" style="display: none;">
                    <label class="form-label mb-1">Percentage (%)</label>
                    <div class="input-group">
                        <input type="number" name="variations[${idx}][discount_percentage]"
                               class="form-control discount-percentage-input" placeholder="e.g. 2" min="0" max="100" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-3 discount-amount-col" style="display: none;">
                    <label class="form-label mb-1">Fixed Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs</span>
                        <input type="number" name="variations[${idx}][discount_amount]"
                               class="form-control discount-amount-input" placeholder="e.g. 50" min="0" step="0.01">
                    </div>
                </div>
            </div>
        </div>`;
        }

        $('#addVariation').on('click', () => {
            $('#variationsContainer').append(newVariationRow(varIdx++));
        });

        $(document).on('click', '.remove-variation', function() {
            if ($('#variationsContainer .variation-row').length <= 1) {
                alert('At least one variation row is required.');
                return;
            }
            $(this).closest('.variation-row').remove();
        });

        function applyDiscountTypeUI($row) {
            const type = $row.find('.discount-type-select').val();
            const $percentageCol = $row.find('.discount-percentage-col');
            const $amountCol = $row.find('.discount-amount-col');

            $percentageCol.toggle(type === 'percentage');
            $amountCol.toggle(type === 'fixed');

            if (type !== 'percentage') $percentageCol.find('.discount-percentage-input').val('');
            if (type !== 'fixed') $amountCol.find('.discount-amount-input').val('');
        }

        $(document).on('change', '.discount-type-select', function() {
            applyDiscountTypeUI($(this).closest('.variation-row'));
        });

        $('.variation-row').each(function() {
            applyDiscountTypeUI($(this));
        });

        /* ─────────────────────────────────────────────
           FORM VALIDATION
        ───────────────────────────────────────────── */
        function validateForm() {
            let valid = true;

            $('.field-error').removeClass('show');
            $('.is-invalid-select').removeClass('is-invalid-select');
            $('.multi-select-wrapper').removeClass('ms-invalid');

            if (!$('[name="title"]').val().trim()) {
                $('[name="title"]').addClass('is-invalid-select');
                $('#titleError').addClass('show');
                valid = false;
            }

            const productCode = $('[name="product_code"]').val().trim();
            const companyProductCode = $('[name="company_product_code"]').val().trim();

            if (!productCode && !companyProductCode) {
                $('[name="product_code"]').addClass('is-invalid-select');
                $('[name="company_product_code"]').addClass('is-invalid-select');
                $('#product_codeError').text('Please enter at least one: Product Code or Company Product Code.')
                    .addClass('show');
                valid = false;
            } else {
                $('[name="product_code"]').removeClass('is-invalid-select');
                $('[name="company_product_code"]').removeClass('is-invalid-select');
                $('#product_codeError').removeClass('show');
                $('#company_product_codeError').removeClass('show');
            }

            if (!$('[name="brand"]').val() || $('[name="brand"]').val() === '__add_brand__') {
                $('[name="brand"]').addClass('is-invalid-select');
                $('#brandError').addClass('show');
                valid = false;
            }

            const cats = $('#categorySelect').val();
            if (!cats || cats.length === 0) {
                $('#categoryMultiSelect').addClass('ms-invalid');
                $('#categoriesError').addClass('show');
                valid = false;
            }

            let hsValid = true;
            $('input[name="hs_code"], input[name^="variations"][name$="[hs_code]"]').each(function() {
                const val = $(this).val().trim();
                if (val && !/^\d{6,15}$/.test(val)) {
                    $(this).addClass('is-invalid-select');
                    hsValid = false;
                } else {
                    $(this).removeClass('is-invalid-select');
                }
            });
            if (!hsValid) {
                showNotification('HS Code must be 6 to 15 digits.', 'error');
                valid = false;
            }

            if ($('#exciseStatus').is(':checked')) {
                const exciseType = $('#exciseType').val();
                if (!exciseType) {
                    $('#exciseType').addClass('is-invalid-select');
                    $('#exciseTypeError').addClass('show');
                    valid = false;
                } else if (exciseType === 'percentage') {
                    if (!$('#excisePercentage').val().toString().trim()) {
                        $('#excisePercentage').addClass('is-invalid-select');
                        $('#excisePercentageError').addClass('show');
                        valid = false;
                    }
                } else if (exciseType === 'fixed') {
                    if (!$('#exciseValue').val().toString().trim()) {
                        $('#exciseValue').addClass('is-invalid-select');
                        $('#exciseValueError').addClass('show');
                        valid = false;
                    }
                }
            }

            return valid;
        }

        /* ─────────────────────────────────────────────
           AJAX SAVE / UPDATE
        ───────────────────────────────────────────── */
        $('#saveItemBtn').on('click', function() {
            if (!validateForm()) return;

            syncImageOrder();

            const $btn = $(this);
            const origHtml = $btn.html();

            $btn.prop('disabled', true).html(
                // '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
            );

            $.ajax({
                url: '{{ route("item.save") }}',
                type: 'POST',
                data: new FormData($('#itemForm')[0]),
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function(response) {
                    const result = (typeof response === 'string') ? JSON.parse(response) : response;

                    if (result.type === 'success') {
                        showNotification(result.message, 'success');

                        // if (typeof itemTable !== 'undefined' && itemTable.ajax) {
                        //     itemTable.ajax.reload(null, false);
                        // }

                        if (typeof itemTable !== 'undefined') {
                            if (itemTable.fnDraw) {
                            // old-style .dataTable() init (matches this app's usual pattern)
                                itemTable.fnDraw();
                                } else if (itemTable.ajax) {
                            // new-style .DataTable() init
                            itemTable.ajax.reload(null, false);
                        } else if (itemTable.api) {
                            itemTable.api().ajax.reload(null, false);
                        }
                        }

                        if (typeof window.refreshLowStockAlerts === 'function') {
                            window.refreshLowStockAlerts();
                        }

                        // if (result.item) {
                        //     $(document).trigger('item:created', [result.item]);
                        // }

                        if (result.item) {
                            // Build variations (id + product_code + is_wholesale) straight from the form's
                            // current rows, so the sales voucher's Product Code dropdown updates correctly
                            // even if the server response doesn't echo full variation data.
                            var isWholesale = $('#isWholesale').is(':checked') ? 'Y' : 'N';
                            var variations = [];
                            var lastVariationId = null;

                            $('#variationsContainer .variation-row').each(function() {
                                var vid = $(this).find('input[name$="[variationid]"]').val();
                                var pc = $(this).find('input[name$="[product_code]"]').val();
                                if (vid) {
                                    variations.push({
                                        id: vid,
                                        product_code: pc,
                                        is_wholesale: isWholesale
                                    });
                                    lastVariationId = vid;
                                }
                            });

                            result.item.variations = result.item.variations || variations;

                            $(document).trigger('item:created', [result.item, lastVariationId]);
                        }

                        var $modalEl = $('#itemForm').closest('.modal');
                        if ($modalEl.length) {
                            var modalInstance = bootstrap.Modal.getInstance($modalEl[0]);
                            if (modalInstance) modalInstance.hide();
                        }

                        $btn.prop('disabled', false).html(origHtml);
                    } else {
                        showNotification(result.message || 'Something went wrong.', 'error');
                        $btn.prop('disabled', false).html(origHtml);
                    }
                },

                error: function(xhr) {
                    $btn.prop('disabled', false).html(origHtml);

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            const cleanField = field
                                .replace(/\.\*$/, '')
                                .replace(/\[\]$/, '')
                                .replace(/\.\d+\..+$/, '');

                            switch (cleanField) {
                                case 'categories':
                                    $('#categoryMultiSelect').addClass('ms-invalid');
                                    $('#categoriesError').text(messages[0]).addClass('show');
                                    break;
                                case 'sub_categories':
                                    $('#subCategoryMultiSelect').addClass('ms-invalid');
                                    $('#subCategoriesError').text(messages[0]).addClass('show');
                                    break;
                                case 'sub_sub_categories':
                                    $('#subSubCategoryMultiSelect').addClass('ms-invalid');
                                    $('#subSubCategoriesError').text(messages[0]).addClass('show');
                                    break;
                                case 'title':
                                    $('[name="title"]').addClass('is-invalid-select');
                                    $('#titleError').text(messages[0]).addClass('show');
                                    break;
                                case 'brand':
                                    $('[name="brand"]').addClass('is-invalid-select');
                                    $('#brandError').text(messages[0]).addClass('show');
                                    break;
                                default: {
                                    const $field = $(`[name="${cleanField}"]`);
                                    if ($field.length) {
                                        $field.addClass('is-invalid-select');
                                        $field.closest(
                                                '.col-md-1, .col-md-2, .col-md-4, .col-md-6, .col-md-12'
                                            )
                                            .find('.field-error')
                                            .text(messages[0]).addClass('show');
                                    }
                                }
                            }
                        });
                    } else {
                        showNotification('Something went wrong. Please try again.', 'error');
                    }
                }
            });
        });

    });
</script>