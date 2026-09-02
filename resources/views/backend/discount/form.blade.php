<div class="modal-header">
    <h5 class="modal-title">
        {{ @$id ? 'Edit Discount' : 'Add Discount' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<style>
    .item-checklist-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 16px;
    }

    .item-checklist-grid .form-check {
        min-width: 0;
    }

    .item-checklist-grid .form-check-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }
</style>

<form id="orgForm" action="{{ route('discount.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ @$id ?? '' }}">
    <input type="hidden" name="userid" value="{{ @$userid ?? '' }}">

    <div class="modal-body">

        {{-- ── Row 1: Type / Value ─────────────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Discount Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" data-required
                    placeholder="e.g. Dashain Offer" value="{{ @$title ?? '' }}" />
                <div class="invalid-feedback">Discount title is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                <select name="type" id="discountType" class="form-select" data-required>
                    <option value="">-- Select Type --</option>
                    <option value="percentage" {{ (@$type ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (%)
                    </option>
                    <option value="fixed" {{ (@$type ?? '') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                </select>
                <div class="invalid-feedback">Discount type is required.</div>
            </div>

            {{-- Percentage field --}}
            <div class="col-md-4" id="percentageField" style="display: none;">
                <label class="form-label">Percentage (%) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="percentage" class="form-control" placeholder="e.g. 20" min="1"
                        max="100" value="{{ @$percentage ?? '' }}" />
                    <span class="input-group-text">%</span>
                </div>
                <div class="invalid-feedback">Percentage is required.</div>
            </div>

            {{-- Fixed amount field --}}
            <div class="col-md-4" id="fixedAmountField" style="display: none;">
                <label class="form-label">Fixed Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rs</span>
                    <input type="number" name="value" class="form-control" placeholder="e.g. 50" min="0"
                        value="{{ @$value ?? '' }}" />
                </div>
                <div class="invalid-feedback">Amount is required.</div>
            </div>

        </div>

        {{-- ── Row 2: Applies To ────────────────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Applies To <span class="text-danger">*</span></label>
                <select name="applies_to" id="appliesTo" class="form-select" data-required>
                    <option value="">-- Select --</option>
                    <option value="item" {{ (@$applies_to ?? '') == 'item' ? 'selected' : '' }}>Item</option>
                    <option value="category" {{ (@$applies_to ?? '') == 'category' ? 'selected' : '' }}>Category
                    </option>
                    <option value="sub_category" {{ (@$applies_to ?? '') == 'sub_category' ? 'selected' : '' }}>
                        Sub Category</option>
                    <option value="sub_sub_category"
                        {{ (@$applies_to ?? '') == 'sub_sub_category' ? 'selected' : '' }}>
                        Sub Sub Category</option>
                    <option value="brand" {{ (@$applies_to ?? '') == 'brand' ? 'selected' : '' }}>
                        Brand</option>
                </select>
                <div class="invalid-feedback">Please select what this discount applies to.</div>
            </div>

            {{-- Category dropdown (visible for Category, Sub Category, Sub Sub Category) --}}
            <div class="col-md-8" id="categoryField" style="display: none;">
                <label class="form-label">Select Category <span class="text-danger">*</span></label>
                <select name="category_target_id" id="categorySelect" class="form-select">
                    <option value="">-- Select Category --</option>
                </select>
                <div class="invalid-feedback">Please select a category.</div>
            </div>

            {{-- Brand dropdown --}}
            <div class="col-md-8" id="brandField" style="display: none;">
                <label class="form-label">Select Brand <span class="text-danger">*</span></label>
                <select name="brand_target_id" id="brandSelect" class="form-select">
                    <option value="">-- Select Brand --</option>
                </select>
                <div class="invalid-feedback">Please select a brand.</div>
            </div>

        </div>

        {{-- ── Row 2b: Sub Category + Sub Sub Category — shown together, side by side ── --}}
        <div class="row g-3 mb-3">

            {{-- Sub Category dropdown (visible for Sub Category, Sub Sub Category) --}}
            <div class="col-md-6" id="subCategoryField" style="display: none;">
                <label class="form-label">Select Sub Category <span class="text-danger">*</span></label>
                <select name="sub_category_target_id" id="subCategorySelect" class="form-select">
                    <option value="">-- Select Sub Category --</option>
                </select>
                <div class="invalid-feedback">Please select a sub category.</div>
            </div>

            {{-- Sub Sub Category dropdown (visible only for Sub Sub Category) --}}
            <div class="col-md-6" id="subSubCategoryField" style="display: none;">
                <label class="form-label">Select Sub Sub Category <span class="text-danger">*</span></label>
                <select name="sub_sub_category_target_id" id="subSubCategorySelect" class="form-select">
                    <option value="">-- Select Sub Sub Category --</option>
                </select>
                <div class="invalid-feedback">Please select a sub sub category.</div>
            </div>

        </div>

        {{-- ── Row 2c: Item checklist — shared across all "applies to" types, filtered accordingly ── --}}
        <div class="row g-3 mb-3">
            <div class="col-12" id="itemField" style="display: none;">
                <label class="form-label">Select Item(s) <span class="text-danger">*</span></label>
                <div id="itemChecklist" class="item-checklist-grid border rounded p-2"
                    style="max-height: 260px; overflow-y: auto;">
                    <div class="text-muted small">-- Select "Applies To" above to load items --</div>
                </div>
                <div class="invalid-feedback d-block" id="itemChecklistError" style="display:none !important;">
                    Please select at least one item.
                </div>
            </div>
        </div>

        {{-- ── Row 3: Minimum Requirement ──────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Requirement Type</label>
                <select name="min_requirement" id="minRequirement" class="form-select">
                    <option value="none" {{ (@$min_requirement ?? 'none') == 'none' ? 'selected' : '' }}>None
                    </option>
                    <option value="purchase" {{ (@$min_requirement ?? '') == 'purchase' ? 'selected' : '' }}>
                        Purchase Amount</option>
                    <option value="quantity" {{ (@$min_requirement ?? '') == 'quantity' ? 'selected' : '' }}>
                        Quantity</option>
                </select>
            </div>

            <div class="col-md-4" id="minValueField" style="display: none;">
                <label class="form-label">Minimum Value <span class="text-danger">*</span></label>
                <input type="number" name="min_value" class="form-control" placeholder="Enter minimum value"
                    min="0" value="{{ @$min_value ?? '' }}" />
                <div class="invalid-feedback">Minimum value is required.</div>
            </div>

            <div class="col-md-4" id="maxValueField" style="display: none;">
                <label class="form-label">Maximum Value</label>
                <input type="number" name="max_value" class="form-control" placeholder="Enter maximum value (optional)"
                    min="0" value="{{ @$max_value ?? '' }}" />
                <div class="invalid-feedback">Maximum value must be greater than the minimum value.</div>
            </div>

        </div>

        {{-- ── Row 4: Usage Limits ──────────────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Usage Limit Type</label>
                <select name="usage_limit_type" id="usageLimitType" class="form-select">
                    <option value="once" {{ (@$usage_limit_type ?? 'once') == 'once' ? 'selected' : '' }}>One
                        Time Only</option>
                    <option value="limited" {{ (@$usage_limit_type ?? '') == 'limited' ? 'selected' : '' }}>
                        Limited Number of Uses</option>
                    <option value="per_user" {{ (@$usage_limit_type ?? '') == 'per_user' ? 'selected' : '' }}>
                        Limit Per Customer</option>
                </select>
            </div>

            <div class="col-md-4" id="totalUsageField" style="display: none;">
                <label class="form-label">Total Usage Limit <span class="text-danger">*</span></label>
                <input type="number" name="usage_limit" class="form-control" placeholder="e.g. 100" min="1"
                    value="{{ @$usage_limit ?? '' }}" />
                <div class="invalid-feedback">Please enter total usage limit.</div>
            </div>

            <div class="col-md-4" id="perUserField" style="display: none;">
                <label class="form-label">Uses Per Customer <span class="text-danger">*</span></label>
                <input type="number" name="usage_limit_per_user" class="form-control" placeholder="e.g. 1"
                    min="1" value="{{ @$usage_limit_per_user ?? '' }}" />
                <div class="invalid-feedback">Please enter per customer limit.</div>
            </div>

        </div>

        {{-- ── Row 5: Dates & Times ─────────────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-3">
                <label class="form-label">Active Date <span class="text-danger">*</span></label>
                <input type="text" name="starts_at" id="start_date_np" class="form-control" autocomplete="off"
                    readonly value="{{ @$starts_at ?? '' }}">
                <div class="invalid-feedback">Active date is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Active Time <span class="text-danger">*</span></label>
                <input type="text" id="starts_time_np" class="form-control nptp-display" autocomplete="off"
                    readonly placeholder="04:04 PM">
                <input type="hidden" name="starts_time" id="starts_time" value="{{ @$starts_time ?? '00:00' }}">
                <div class="invalid-feedback">Active time is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="text" name="ends_at" id="end_date_np" class="form-control" autocomplete="off"
                    readonly value="{{ @$ends_at ?? '' }}">
                <div class="invalid-feedback">End date is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">End Time <span class="text-danger">*</span></label>
                <input type="text" id="ends_time_np" class="form-control nptp-display" autocomplete="off"
                    readonly placeholder="11:59 PM">
                <input type="hidden" name="ends_time" id="ends_time" value="{{ @$ends_time ?? '23:59' }}">
                <div class="invalid-feedback">End time is required.</div>
            </div>

        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="bx {{ @$id ? 'bx-save' : 'bx-plus' }} me-1"></i>
            {{ @$id ? 'Update' : 'Save' }}
        </button>
    </div>
</form>

<style>
    .nptp-popover {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 2000;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .15);
        padding: 10px;
        width: 210px;
    }

    .nptp-selected {
        display: flex;
        gap: 6px;
        margin-bottom: 8px;
    }

    .nptp-box {
        flex: 1;
        text-align: center;
        border: 1px solid #0d6efd;
        border-radius: 6px;
        padding: 5px 2px;
        font-weight: 600;
        background: #eaf2ff;
        font-size: .85rem;
    }

    .nptp-columns {
        display: flex;
        gap: 6px;
        border-top: 1px solid #eee;
        padding-top: 6px;
    }

    .nptp-col {
        flex: 1;
        max-height: 170px;
        overflow-y: auto;
        text-align: center;
    }

    .nptp-item {
        padding: 5px 0;
        cursor: pointer;
        color: #0d6efd;
        font-size: .9rem;
        border-radius: 4px;
    }

    .nptp-item:hover {
        background: #e8f0fe;
    }

    .nptp-item.active {
        font-weight: 700;
        color: #fff;
        background: #0d6efd;
    }
</style>

<script>
    (function() {

        if (window._discountModalInitialized) return;
        window._discountModalInitialized = true;

        var ITEMS_URL = '{{ route('api.items.list') }}';
        var CATEGORY_URL = '{{ url('admin/discount/categories') }}'; // ?level=1/2/3&parent_id=
        var BRAND_URL = '{{ url('admin/discount/brands') }}';

        // Pre-selected values when editing
        var editSelectedItemIds = {
            !!json_encode(@$selected_item_ids ?? []) !!
        };
        var editCategoryTargetId = '{{ @$category_target_id ?? '
        ' }}';
        var editSubCategoryTargetId = '{{ @$sub_category_target_id ?? '
        ' }}';
        var editSubSubCategoryTargetId = '{{ @$sub_sub_category_target_id ?? '
        ' }}';
        var editBrandTargetId = '{{ @$brand_target_id ?? '
        ' }}';

        /* =========================================================
           NEPALI TIME PICKER (wheel-style, Devanagari numerals)
        ========================================================= */
        var nepaliDigits = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

        function toNepaliNum(n) {
            return String(n).split('').map(function(d) {
                return /[0-9]/.test(d) ? nepaliDigits[+d] : d;
            }).join('');
        }

        function pad2(n) {
            return (n < 10 ? '0' : '') + n;
        }

        function buildTimePickerColumn($col, type) {
            var html = '';
            if (type === 'hour') {
                for (var h = 1; h <= 12; h++) {
                    html += '<div class="nptp-item" data-val="' + pad2(h) + '">' + toNepaliNum(pad2(
                        h)) + '</div>';
                }
            } else if (type === 'minute') {
                for (var m = 0; m < 60; m++) {
                    html += '<div class="nptp-item" data-val="' + pad2(m) + '">' + toNepaliNum(pad2(
                        m)) + '</div>';
                }
            } else if (type === 'ampm') {
                html += '<div class="nptp-item" data-val="AM">AM</div>';
                html += '<div class="nptp-item" data-val="PM">PM</div>';
            }
            $col.html(html);
        }

        var activeTimePickers = [];

        function initNepaliTimePicker(displayId, hiddenId) {
            var $display = $('#' + displayId);
            var $hidden = $('#' + hiddenId);

            if (!$display.length || !$hidden.length) return;

            var $wrap = $display.parent();
            $wrap.css('position', 'relative');

            var $picker = $(
                '<div class="nptp-popover" style="display:none;">' +
                '<div class="nptp-selected">' +
                '<span class="nptp-box" data-col="hour"></span>' +
                '<span class="nptp-box" data-col="minute"></span>' +
                '<span class="nptp-box" data-col="ampm"></span>' +
                '</div>' +
                '<div class="nptp-columns">' +
                '<div class="nptp-col" data-col="hour"></div>' +
                '<div class="nptp-col" data-col="minute"></div>' +
                '<div class="nptp-col" data-col="ampm"></div>' +
                '</div>' +
                '</div>'
            );
            $wrap.append($picker);

            buildTimePickerColumn($picker.find('.nptp-col[data-col="hour"]'), 'hour');
            buildTimePickerColumn($picker.find('.nptp-col[data-col="minute"]'), 'minute');
            buildTimePickerColumn($picker.find('.nptp-col[data-col="ampm"]'), 'ampm');

            var state = {
                hour: '12',
                minute: '00',
                ampm: 'AM'
            };

            function setFromValue24(val24) {
                var parts = (val24 || '00:00').split(':');
                var h24 = parseInt(parts[0], 10) || 0;
                var m = parseInt(parts[1], 10) || 0;
                var ampm = h24 >= 12 ? 'PM' : 'AM';
                var h12 = h24 % 12;
                if (h12 === 0) h12 = 12;
                state.hour = pad2(h12);
                state.minute = pad2(m);
                state.ampm = ampm;
                refreshUI();
            }

            function to24() {
                var h = parseInt(state.hour, 10);
                if (state.ampm === 'AM') {
                    if (h === 12) h = 0;
                } else {
                    if (h !== 12) h += 12;
                }
                return pad2(h) + ':' + state.minute;
            }

            function refreshUI() {
                $picker.find('.nptp-box[data-col="hour"]').text(toNepaliNum(state.hour));
                $picker.find('.nptp-box[data-col="minute"]').text(toNepaliNum(state.minute));
                $picker.find('.nptp-box[data-col="ampm"]').text(state.ampm);

                $picker.find('.nptp-col[data-col="hour"] .nptp-item').removeClass('active');
                $picker.find('.nptp-col[data-col="hour"] .nptp-item[data-val="' + state.hour +
                    '"]').addClass('active');

                $picker.find('.nptp-col[data-col="minute"] .nptp-item').removeClass('active');
                $picker.find('.nptp-col[data-col="minute"] .nptp-item[data-val="' + state.minute +
                    '"]').addClass('active');

                $picker.find('.nptp-col[data-col="ampm"] .nptp-item').removeClass('active');
                $picker.find('.nptp-col[data-col="ampm"] .nptp-item[data-val="' + state.ampm +
                    '"]').addClass('active');

                $display.val(state.hour + ':' + state.minute + ' ' + state.ampm);
                $hidden.val(to24());
                $display.removeClass('is-invalid');
            }

            $picker.on('click', '.nptp-item', function() {
                var $item = $(this);
                var col = $item.parent().data('col');
                state[col] = $item.data('val');
                refreshUI();
                $hidden.trigger('change');
            });

            $display.off('click.nptp').on('click.nptp', function(e) {
                e.stopPropagation();
                $('.nptp-popover').not($picker).hide();
                $picker.toggle();
                if ($picker.is(':visible')) {
                    $picker.find('.nptp-col').each(function() {
                        var $active = $(this).find('.active');
                        if ($active.length) {
                            this.scrollTop = $active[0].offsetTop - 60;
                        }
                    });
                }
            });

            setFromValue24($hidden.val());

            activeTimePickers.push($picker);
        }

        $(document).off('click.nptpOutside').on('click.nptpOutside', function(e) {
            $('.nptp-popover').each(function() {
                if (!$(e.target).closest(this).length && !$(e.target).hasClass('nptp-display')) {
                    $(this).hide();
                }
            });
        });

        /* =========================================================
           DISCOUNT TYPE → show % or fixed field
        ========================================================= */
        function applyTypeToggle(type) {
            $('#percentageField, #fixedAmountField').hide();
            $('#percentageField input, #fixedAmountField input').removeAttr('data-required');

            if (type === 'percentage') {
                $('#percentageField').show();
                $('#percentageField input').attr('data-required', '');
            } else if (type === 'fixed') {
                $('#fixedAmountField').show();
                $('#fixedAmountField input').attr('data-required', '');
            }
        }

        /* =========================================================
           LOAD ITEMS AS CHECKBOX LIST
        ========================================================= */
        function loadItemChecklist(selectedIds, filters) {
            selectedIds = selectedIds || [];
            filters = filters || {};

            $('#itemChecklist').html('<div class="text-muted small">Loading items...</div>');

            $.get(ITEMS_URL, filters, function(response) {

                var html = '';

                if (!response.data || response.data.length === 0) {
                    html = '<div class="text-muted small">No items found.</div>';
                } else {

                    $.each(response.data, function(i, item) {

                        var checked = selectedIds.map(String).includes(String(item.id)) ?
                            'checked' :
                            '';

                        html += `
                    <div class="form-check">
                        <input class="form-check-input item-checkbox"
                               type="checkbox"
                               name="item_ids[]"
                               value="${item.id}"
                               id="itemChk${item.id}"
                               ${checked}>
                        <label class="form-check-label" for="itemChk${item.id}">
                            ${item.title}
                        </label>
                    </div>
                `;
                    });
                }

                $('#itemChecklist').html(html);
            });
        }

        /* =========================================================
           LOAD CATEGORY-TYPE DROPDOWNS (category / sub / sub-sub)
           level: 1 = category, 2 = sub category, 3 = sub sub category
           parentId: pass the parent category/sub-category id to filter
                     level 2 / level 3 options (cascading)
        ========================================================= */
        function loadCategoryLevelOptions(level, $select, selectedId, placeholder, parentId) {
            $select.html('<option value="">Loading...</option>');

            var params = {
                level: level
            };
            if (parentId) {
                params.parent_id = parentId;
            }

            $.get(CATEGORY_URL, params)
                .done(function(response) {
                    var options = '<option value="">' + placeholder + '</option>';
                    $.each(response.data, function(i, cat) {
                        var sel = (String(cat.id) === String(selectedId)) ? 'selected' : '';
                        options += '<option value="' + cat.id + '" ' + sel + '>' + cat.title +
                            '</option>';
                    });
                    $select.html(options);
                })
                .fail(function() {
                    $select.html('<option value="">-- Failed to load --</option>');
                    showNotification('Could not load options.', 'error');
                });
        }

        function loadBrands(selectedId) {
            $('#brandSelect').html('<option value="">Loading...</option>');
            $.get(BRAND_URL)
                .done(function(response) {
                    var options = '<option value="">-- Select Brand --</option>';
                    $.each(response.data, function(i, b) {
                        var sel = (String(b.id) === String(selectedId)) ? 'selected' : '';
                        options += '<option value="' + b.id + '" ' + sel + '>' + b.name + '</option>';
                    });
                    $('#brandSelect').html(options);
                })
                .fail(function() {
                    $('#brandSelect').html('<option value="">-- Failed to load --</option>');
                    showNotification('Could not load brands.', 'error');
                });
        }

        /* =========================================================
           APPLIES TO TOGGLE — one field group per type
           - category         -> Category
           - sub_category     -> Category -> Sub Category
           - sub_sub_category -> Category -> Sub Category -> Sub Sub Category
        ========================================================= */
        function applyAppliesToToggle(val) {

            // Hide everything first
            $('#itemField,#categoryField,#subCategoryField,#subSubCategoryField,#brandField').hide();

            if (val === 'item') {

                $('#itemField').show();
                loadItemChecklist(editSelectedItemIds);

            } else if (val === 'category') {

                $('#categoryField').show();

                loadCategoryLevelOptions(
                    1,
                    $('#categorySelect'),
                    editCategoryTargetId,
                    '-- Select Category --'
                );

                if (editCategoryTargetId) {
                    $('#itemField').show();
                    loadItemChecklist(editSelectedItemIds, {
                        category_id: editCategoryTargetId
                    });
                }

            } else if (val === 'sub_category') {

                $('#categoryField').show();
                $('#subCategoryField').show();

                loadCategoryLevelOptions(
                    1,
                    $('#categorySelect'),
                    editCategoryTargetId,
                    '-- Select Category --'
                );

                if (editCategoryTargetId) {
                    loadCategoryLevelOptions(
                        2,
                        $('#subCategorySelect'),
                        editSubCategoryTargetId,
                        '-- Select Sub Category --',
                        editCategoryTargetId
                    );
                } else {
                    $('#subCategorySelect').html('<option value="">-- Select Category first --</option>');
                }

                if (editSubCategoryTargetId) {
                    $('#itemField').show();
                    loadItemChecklist(editSelectedItemIds, {
                        sub_category_id: editSubCategoryTargetId
                    });
                }

            } else if (val === 'sub_sub_category') {

                $('#categoryField').show();
                $('#subCategoryField').show();
                $('#subSubCategoryField').show();

                loadCategoryLevelOptions(
                    1,
                    $('#categorySelect'),
                    editCategoryTargetId,
                    '-- Select Category --'
                );

                if (editCategoryTargetId) {
                    loadCategoryLevelOptions(
                        2,
                        $('#subCategorySelect'),
                        editSubCategoryTargetId,
                        '-- Select Sub Category --',
                        editCategoryTargetId
                    );
                } else {
                    $('#subCategorySelect').html('<option value="">-- Select Category first --</option>');
                }

                if (editSubCategoryTargetId) {
                    loadCategoryLevelOptions(
                        3,
                        $('#subSubCategorySelect'),
                        editSubSubCategoryTargetId,
                        '-- Select Sub Sub Category --',
                        editSubCategoryTargetId
                    );
                } else {
                    $('#subSubCategorySelect').html(
                        '<option value="">-- Select Sub Category first --</option>');
                }

                if (editSubSubCategoryTargetId) {
                    $('#itemField').show();
                    loadItemChecklist(editSelectedItemIds, {
                        sub_sub_category_id: editSubSubCategoryTargetId
                    });
                }

            } else if (val === 'brand') {

                $('#brandField').show();

                loadBrands(editBrandTargetId);

                if (editBrandTargetId) {
                    $('#itemField').show();
                    loadItemChecklist(editSelectedItemIds, {
                        brand_id: editBrandTargetId
                    });
                }

            }
        }

        /* =========================================================
           CASCADING CHANGE HANDLERS
           Behaviour depends on the current "Applies To" selection:
           - category:          selecting Category loads the item list
           - sub_category:      selecting Category loads Sub Categories,
                                 selecting Sub Category loads the item list
           - sub_sub_category:  selecting Category loads Sub Categories,
                                 selecting Sub Category loads Sub Sub Categories,
                                 selecting Sub Sub Category loads the item list
        ========================================================= */
        $(document).on('change', '#categorySelect', function() {

            var appliesTo = $('#appliesTo').val();
            var categoryId = $(this).val();

            if (appliesTo === 'category') {

                $('#itemField').show();
                loadItemChecklist([], {
                    category_id: categoryId
                });

            } else if (appliesTo === 'sub_category' || appliesTo === 'sub_sub_category') {

                $('#itemField').hide();

                if (categoryId) {
                    loadCategoryLevelOptions(
                        2,
                        $('#subCategorySelect'),
                        '',
                        '-- Select Sub Category --',
                        categoryId
                    );
                } else {
                    $('#subCategorySelect').html(
                        '<option value="">-- Select Category first --</option>');
                }

                if (appliesTo === 'sub_sub_category') {
                    $('#subSubCategorySelect').html(
                        '<option value="">-- Select Sub Category first --</option>');
                }
            }
        });

        $(document).on('change', '#subCategorySelect', function() {

            var appliesTo = $('#appliesTo').val();
            var subCategoryId = $(this).val();

            if (appliesTo === 'sub_category') {

                $('#itemField').show();
                loadItemChecklist([], {
                    sub_category_id: subCategoryId
                });

            } else if (appliesTo === 'sub_sub_category') {

                $('#itemField').hide();

                if (subCategoryId) {
                    loadCategoryLevelOptions(
                        3,
                        $('#subSubCategorySelect'),
                        '',
                        '-- Select Sub Sub Category --',
                        subCategoryId
                    );
                } else {
                    $('#subSubCategorySelect').html(
                        '<option value="">-- Select Sub Category first --</option>');
                }
            }
        });

        $(document).on('change', '#subSubCategorySelect', function() {
            $('#itemField').show();
            loadItemChecklist([], {
                sub_sub_category_id: $(this).val()
            });
        });

        $(document).on('change', '#brandSelect', function() {
            $('#itemField').show();
            loadItemChecklist([], {
                brand_id: $(this).val()
            });
        });

        /* =========================================================
           MIN REQUIREMENT TOGGLE
        ========================================================= */
        function applyMinRequirementToggle(val) {
            if (val === 'purchase' || val === 'quantity') {
                $('#minValueField, #maxValueField').show();
            } else {
                $('#minValueField, #maxValueField').hide();
                $('#minValueField input, #maxValueField input').val('');
            }
        }

        /* =========================================================
           USAGE LIMIT TOGGLE
        ========================================================= */
        function applyUsageLimitToggle(val) {
            $('#totalUsageField, #perUserField').hide();
            if (val === 'limited') $('#totalUsageField').show();
            if (val === 'per_user') $('#perUserField').show();
        }

        /* =========================================================
           EVENTS
        ========================================================= */
        $(document).off('change.discount', '#discountType')
            .on('change.discount', '#discountType', function() {
                applyTypeToggle($(this).val());
            });

        $(document).off('change.discount', '#appliesTo')
            .on('change.discount', '#appliesTo', function() {
                // Manual change clears any "edit mode" pre-selections so switching types starts fresh
                editSelectedItemIds = [];
                editCategoryTargetId = editSubCategoryTargetId = editSubSubCategoryTargetId =
                    editBrandTargetId = '';
                applyAppliesToToggle($(this).val());
            });

        $(document).off('change.discount', '#minRequirement')
            .on('change.discount', '#minRequirement', function() {
                applyMinRequirementToggle($(this).val());
            });

        $(document).off('change.discount', '#usageLimitType')
            .on('change.discount', '#usageLimitType', function() {
                applyUsageLimitToggle($(this).val());
            });

        /* =========================================================
           MODAL SHOWN — init toggles (edit mode)
        ========================================================= */
        $(document).off('shown.bs.modal.discount', '#discountModel')
            .on('shown.bs.modal.discount', '#discountModel', function() {
                applyTypeToggle($('#discountType').val());

                var appliesTo = $('#appliesTo').val();
                if (appliesTo) applyAppliesToToggle(appliesTo);

                applyMinRequirementToggle($('#minRequirement').val());
                applyUsageLimitToggle($('#usageLimitType').val());

                initNepaliTimePicker('starts_time_np', 'starts_time');
                initNepaliTimePicker('ends_time_np', 'ends_time');
            });

        /* =========================================================
           MODAL HIDDEN — full reset
        ========================================================= */
        $(document).off('hidden.bs.modal.discount', '#discountModel')
            .on('hidden.bs.modal.discount', '#discountModel', function() {
                var $form = $('#orgForm');
                $form[0].reset();
                $form.find('.is-invalid').removeClass('is-invalid');
                $('#percentageField, #fixedAmountField').hide();
                $('#percentageField input, #fixedAmountField input').removeAttr('data-required');
                $('#itemField, #categoryField, #subCategoryField, #subSubCategoryField, #brandField').hide();
                $('#itemChecklist').html(
                    '<div class="text-muted small">-- Select "Item" above to load items --</div>');
                $('#minValueField, #maxValueField, #totalUsageField, #perUserField').hide();
                editSelectedItemIds = [];
                editCategoryTargetId = editSubCategoryTargetId = editSubSubCategoryTargetId =
                    editBrandTargetId = '';
                activeTimePickers = [];
                $(document).off('click.nptpOutside');
                window._discountModalInitialized = false;
            });

        /* =========================================================
           FORM SUBMIT
        ========================================================= */
        $(document).off('submit.discount', '#orgForm')
            .on('submit.discount', '#orgForm', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $btn = $form.find('#submitBtn');
                if ($btn.prop('disabled')) return;

                $form.find('.is-invalid').removeClass('is-invalid');
                $('#itemChecklistError').hide();
                var valid = true;

                // Required visible fields
                $form.find('[data-required]').each(function() {
                    var $el = $(this);
                    if (!$el.is(':visible')) return;
                    if (!$el.val() || !String($el.val()).trim()) {
                        $el.addClass('is-invalid');
                        valid = false;
                    }
                });

                // Applies-to validation, per type
                var appliesTo = $('#appliesTo').val();

                if (appliesTo === 'item') {
                    if ($('.item-checkbox:checked').length === 0) {
                        $('#itemChecklistError').show();
                        valid = false;
                    }
                } else if (appliesTo === 'category' && !$('#categorySelect').val()) {
                    $('#categorySelect').addClass('is-invalid');
                    valid = false;
                } else if (appliesTo === 'sub_category' && !$('#subCategorySelect').val()) {
                    $('#subCategorySelect').addClass('is-invalid');
                    valid = false;
                } else if (appliesTo === 'sub_sub_category' && !$('#subSubCategorySelect').val()) {
                    $('#subSubCategorySelect').addClass('is-invalid');
                    valid = false;
                } else if (appliesTo === 'brand' && !$('#brandSelect').val()) {
                    $('#brandSelect').addClass('is-invalid');
                    valid = false;
                }

                // Min value
                var minReq = $('#minRequirement').val();
                var minVal = $form.find('[name="min_value"]').val();
                if (minReq === 'purchase' || minReq === 'quantity') {
                    var $minVal = $form.find('[name="min_value"]');
                    if (!minVal) {
                        $minVal.addClass('is-invalid');
                        valid = false;
                    }
                }

                // Max value (optional cap on the same min_requirement basis)
                var maxVal = $form.find('[name="max_value"]').val();
                if ((minReq === 'purchase' || minReq === 'quantity') && minVal && maxVal &&
                    Number(maxVal) <= Number(minVal)) {
                    $form.find('[name="max_value"]').addClass('is-invalid');
                    valid = false;
                }

                // Usage limit fields
                var usageLimitType = $('#usageLimitType').val();
                if (usageLimitType === 'limited') {
                    var $ul = $form.find('[name="usage_limit"]');
                    if (!$ul.val()) {
                        $ul.addClass('is-invalid');
                        valid = false;
                    }
                }
                if (usageLimitType === 'per_user') {
                    var $pul = $form.find('[name="usage_limit_per_user"]');
                    if (!$pul.val()) {
                        $pul.addClass('is-invalid');
                        valid = false;
                    }
                }

                // Date validation
                var startsAt = $('[name="starts_at"]').val();
                var endsAt = $('[name="ends_at"]').val();
                if (!startsAt) {
                    $('[name="starts_at"]').addClass('is-invalid');
                    valid = false;
                }
                if (!endsAt) {
                    $('[name="ends_at"]').addClass('is-invalid');
                    valid = false;
                }
                if (startsAt && endsAt && endsAt < startsAt) {
                    $('[name="ends_at"]').addClass('is-invalid');
                    valid = false;
                }

                // Time validation
                var startsTime = $('#starts_time').val();
                var endsTime = $('#ends_time').val();
                if (!startsTime) {
                    $('#starts_time_np').addClass('is-invalid');
                    valid = false;
                }
                if (!endsTime) {
                    $('#ends_time_np').addClass('is-invalid');
                    valid = false;
                }
                if (startsAt && endsAt && startsAt === endsAt && startsTime && endsTime && endsTime <=
                    startsTime) {
                    $('#ends_time_np').addClass('is-invalid');
                    valid = false;
                }

                if (!valid) {
                    showNotification('Please fill in all required fields.', 'warning');
                    return;
                }

                var origHTML = $btn.html();
                $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
                showLoader();

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData($form[0]),
                    processData: false,
                    contentType: false,

                    beforeSend: function() {
                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback').text('');
                    },

                    success: function(response) {

                        hideLoader();

                        var result = (typeof response === 'string') ?
                            JSON.parse(response) :
                            response;

                        if (result.type === 'success') {

                            showNotification(result.message, 'success');

                            if (typeof orgTable !== 'undefined') {
                                orgTable.fnDraw();
                            }

                            bootstrap.Modal.getInstance(
                                document.getElementById('discountModel')
                            ).hide();

                        } else {

                            showNotification(result.message, 'error');
                            $btn.prop('disabled', false).html(origHTML);
                        }
                    },

                    error: function(xhr) {

                        hideLoader();
                        $btn.prop('disabled', false).html(origHTML);

                        if (xhr.status === 422) {

                            $('.is-invalid').removeClass('is-invalid');
                            $('.invalid-feedback').text('');

                            $.each(xhr.responseJSON.errors, function(field, messages) {

                                var message = messages[0];

                                // Handle array fields like item_ids.0
                                var selector = field.replace(/\.\d+/g, '');

                                var $field = $form.find('[name="' + selector + '"]');

                                if (!$field.length) {
                                    $field = $form.find('[name="' + selector + '[]"]');
                                }

                                if ($field.length) {

                                    $field.addClass('is-invalid');

                                    if ($field.next('.invalid-feedback').length) {
                                        $field.next('.invalid-feedback').text(message);
                                    } else if ($field.parent().find('.invalid-feedback')
                                        .length) {
                                        $field.parent().find('.invalid-feedback').text(
                                            message);
                                    }
                                }
                            });

                            var errorMessage = xhr.responseJSON.message;

                            if (xhr.responseJSON.errors) {
                                $.each(xhr.responseJSON.errors, function(field, messages) {
                                    errorMessage = messages[0];
                                    return false;
                                });
                            }

                            showNotification(errorMessage, 'error');
                        } else {

                            showNotification('Something went wrong!', 'error');
                        }
                    }
                });
            });

    })();
</script>