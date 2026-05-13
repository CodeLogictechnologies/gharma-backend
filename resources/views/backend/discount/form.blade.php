<div class="modal-header">
    <h5 class="modal-title">
        {{ @$id ? 'Edit Discount' : 'Add Discount' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="orgForm" action="{{ route('discount.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ @$id ?? '' }}">
    <input type="hidden" name="userid" value="{{ @$userid ?? '' }}">

    <div class="modal-body">

        {{-- ── Row 1: Title / Type / Value ─────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Discount Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Enter discount title"
                    value="{{ @$title ?? '' }}" data-required />
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
                    <option value="entire" {{ (@$applies_to ?? '') == 'entire' ? 'selected' : '' }}>Entire Order
                    </option>
                    <option value="item" {{ (@$applies_to ?? '') == 'item' ? 'selected' : '' }}>Specific Item
                    </option>
                    <option value="variation" {{ (@$applies_to ?? '') == 'variation' ? 'selected' : '' }}>Specific
                        Variation</option>
                </select>
                <div class="invalid-feedback">Please select what this discount applies to.</div>
            </div>

            {{-- Item dropdown --}}
            <div class="col-md-4" id="itemField" style="display: none;">
                <label class="form-label">Select Item <span class="text-danger">*</span></label>
                <select name="item_id" id="itemSelect" class="form-select">
                    <option value="">-- Select Item --</option>
                </select>
                <div class="invalid-feedback">Please select an item.</div>
            </div>

            {{-- Variation dropdown --}}
            <div class="col-md-4" id="variationField" style="display: none;">
                <label class="form-label">Select Variation <span class="text-danger">*</span></label>
                <select name="variation_id" id="variationSelect" class="form-select">
                    <option value="">-- Select item first --</option>
                </select>
                <div class="invalid-feedback">Please select a variation.</div>
            </div>

        </div>

        {{-- ── Row 3: Minimum Requirement ──────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Minimum Requirement</label>
                <select name="min_requirement" id="minRequirement" class="form-select">
                    <option value="none" {{ (@$min_requirement ?? 'none') == 'none' ? 'selected' : '' }}>None
                    </option>
                    <option value="purchase" {{ (@$min_requirement ?? '') == 'purchase' ? 'selected' : '' }}>Minimum
                        Purchase Amount</option>
                    <option value="quantity" {{ (@$min_requirement ?? '') == 'quantity' ? 'selected' : '' }}>Minimum
                        Quantity</option>
                </select>
            </div>

            <div class="col-md-4" id="minValueField" style="display: none;">
                <label class="form-label">Minimum Value <span class="text-danger">*</span></label>
                <input type="number" name="min_value" class="form-control" placeholder="Enter minimum value"
                    min="0" value="{{ @$min_value ?? '' }}" />
                <div class="invalid-feedback">Minimum value is required.</div>
            </div>

        </div>

        {{-- ── Row 4: Usage Limits ──────────────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Usage Limit Type</label>
                <select name="usage_limit_type" id="usageLimitType" class="form-select">
                    <option value="once" {{ (@$usage_limit_type ?? 'once') == 'once' ? 'selected' : '' }}>One Time
                        Only</option>
                    <option value="limited" {{ (@$usage_limit_type ?? '') == 'limited' ? 'selected' : '' }}>Limited
                        Number of Uses</option>
                    <option value="per_user" {{ (@$usage_limit_type ?? '') == 'per_user' ? 'selected' : '' }}>Limit
                        Per Customer</option>
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

        {{-- ── Row 5: Dates ─────────────────────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Active Date <span class="text-danger">*</span></label>
                <input type="date" name="starts_at" class="form-control" data-required
                    value="{{ @$starts_at ?? '' }}" />
                <div class="invalid-feedback">Start date is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="date" name="ends_at" class="form-control" data-required
                    value="{{ @$ends_at ?? '' }}" />
                <div class="invalid-feedback">End date must be after start date.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="discount_type" class="form-select" data-required>
                    <option value="">-- Select Type --</option>
                    {{-- FIX: was comparing $type instead of $discount_type --}}
                    <option value="normal" {{ (@$discount_type ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="coupon" {{ (@$discount_type ?? '') == 'coupon' ? 'selected' : '' }}>Coupon</option>
                </select>
                <div class="invalid-feedback">Type is required.</div>
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

<script>
    (function() {
        // ─── Guard: run only once even if modal HTML is injected multiple times ───
        if (window._discountModalInitialized) return;
        window._discountModalInitialized = true;

        var ITEMS_URL = '{{ route('api.items.list') }}';
        var VARIATIONS_URL = '{{ url('admin/discount/items') }}';

        // Pre-saved values for edit mode (passed from controller)
        var editItemId = '{{ @$item_id ?? '' }}';
        var editVariationId = '{{ @$variation_id ?? '' }}';

        /* =========================================================
           HELPERS
        ========================================================= */
        function showField($el) {
            $el.show();
        }

        function hideField($el) {
            $el.hide();
            $el.find('input').val('');
        }

        /* =========================================================
           DISCOUNT TYPE → % or fixed
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
           LOAD ITEMS FROM API
        ========================================================= */
        function loadItems(selectedId, callback) {
            $('#itemSelect').html('<option value="">-- Loading... --</option>');

            $.get(ITEMS_URL)
                .done(function(response) {
                    var options = '<option value="">-- Select Item --</option>';
                    $.each(response.data, function(i, item) {
                        var sel = (String(item.id) === String(selectedId)) ? 'selected' : '';
                        options += '<option value="' + item.id + '" ' + sel + '>' + item.title +
                            '</option>';
                    });
                    $('#itemSelect').html(options);
                    if (typeof callback === 'function') callback();
                })
                .fail(function() {
                    $('#itemSelect').html('<option value="">-- Failed to load --</option>');
                    showNotification('Could not load items.', 'error');
                });
        }

        /* =========================================================
           LOAD VARIATIONS FROM API
        ========================================================= */
        function loadVariations(itemId, selectedId) {
            $('#variationSelect').html('<option value="">-- Loading... --</option>');
            $('#variationField').show();

            $.get(VARIATIONS_URL + '/' + itemId + '/variations')
                .done(function(response) {
                    var options = '<option value="">-- Select Variation --</option>';
                    $.each(response.data, function(i, v) {
                        var sel = (String(v.id) === String(selectedId)) ? 'selected' : '';
                        options += '<option value="' + v.id + '" ' + sel + '>' + v.attribute + ' - ' + v
                            .value + '</option>';
                    });
                    $('#variationSelect').html(options);
                })
                .fail(function() {
                    $('#variationSelect').html('<option value="">-- Failed to load --</option>');
                    showNotification('Could not load variations.', 'error');
                });
        }

        /* =========================================================
           APPLIES TO → show item / variation
        ========================================================= */
        function applyAppliesToToggle(val, selectedItemId, selectedVariationId) {
            $('#itemField').hide();
            $('#variationField').hide();
            $('#variationSelect').html('<option value="">-- Select item first --</option>');

            if (val === 'item') {
                $('#itemField').show();
                loadItems(selectedItemId || '');

            } else if (val === 'variation') {
                $('#itemField').show();
                loadItems(selectedItemId || '', function() {
                    if (selectedItemId) {
                        loadVariations(selectedItemId, selectedVariationId || '');
                    }
                });
            }
        }

        /* =========================================================
           MIN REQUIREMENT → show value input
        ========================================================= */
        function applyMinRequirementToggle(val) {
            if (val === 'purchase' || val === 'quantity') {
                showField($('#minValueField'));
            } else {
                hideField($('#minValueField'));
            }
        }

        /* =========================================================
           USAGE LIMIT TYPE → show relevant input
        ========================================================= */
        function applyUsageLimitToggle(val) {
            $('#totalUsageField').hide();
            $('#perUserField').hide();

            if (val === 'limited') {
                $('#totalUsageField').show();
            } else if (val === 'per_user') {
                $('#perUserField').show();
            }
        }

        /* =========================================================
           BIND ALL EVENTS — use .off().on() to prevent stacking
        ========================================================= */

        // Discount type change
        $(document).off('change.discount', '#discountType')
            .on('change.discount', '#discountType', function() {
                applyTypeToggle($(this).val());
            });

        // Applies to change
        $(document).off('change.discount', '#appliesTo')
            .on('change.discount', '#appliesTo', function() {
                applyAppliesToToggle($(this).val(), '', '');
            });

        // Item select change → load variations
        $(document).off('change.discount', '#itemSelect')
            .on('change.discount', '#itemSelect', function() {
                var itemId = $(this).val();
                var applies = $('#appliesTo').val();

                $('#variationField').hide();
                $('#variationSelect').html('<option value="">-- Select item first --</option>');

                if (itemId && applies === 'variation') {
                    loadVariations(itemId, '');
                }
            });

        // Min requirement change
        $(document).off('change.discount', '#minRequirement')
            .on('change.discount', '#minRequirement', function() {
                applyMinRequirementToggle($(this).val());
            });

        // Usage limit type change
        $(document).off('change.discount', '#usageLimitType')
            .on('change.discount', '#usageLimitType', function() {
                applyUsageLimitToggle($(this).val());
            });

        /* =========================================================
           MODAL SHOWN → initialise all toggles (handles edit mode)
        ========================================================= */
        $(document).off('shown.bs.modal.discount', '#discountModel')
            .on('shown.bs.modal.discount', '#discountModel', function() {
                // Reset guard so next open re-reads fresh PHP values
                editItemId = '{{ @$item_id ?? '' }}';
                editVariationId = '{{ @$variation_id ?? '' }}';

                applyTypeToggle($('#discountType').val());

                var appliesTo = $('#appliesTo').val();
                if (appliesTo) {
                    applyAppliesToToggle(appliesTo, editItemId, editVariationId);
                }

                applyMinRequirementToggle($('#minRequirement').val());
                applyUsageLimitToggle($('#usageLimitType').val());
            });

        /* =========================================================
           MODAL HIDDEN → full reset
        ========================================================= */
        $(document).off('hidden.bs.modal.discount', '#discountModel')
            .on('hidden.bs.modal.discount', '#discountModel', function() {
                var $form = $('#orgForm');
                $form[0].reset();
                $form.find('.is-invalid').removeClass('is-invalid');

                $('#percentageField, #fixedAmountField').hide();
                $('#percentageField input, #fixedAmountField input').removeAttr('data-required');
                $('#itemField, #variationField').hide();
                $('#minValueField, #totalUsageField, #perUserField').hide();

                editItemId = '';
                editVariationId = '';

                // Re-allow next open to re-init
                window._discountModalInitialized = false;
            });

        /* =========================================================
           FORM SUBMIT — prevent multiple submissions
        ========================================================= */
        $(document).off('submit.discount', '#orgForm')
            .on('submit.discount', '#orgForm', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $btn = $form.find('#submitBtn');

                // Prevent double-click submission
                if ($btn.prop('disabled')) return;

                // Clear previous errors
                $form.find('.is-invalid').removeClass('is-invalid');

                /* ── Validate data-required visible fields ── */
                var valid = true;

                $form.find('[data-required]').each(function() {
                    var $el = $(this);
                    // Only validate visible fields
                    if (!$el.is(':visible')) return;
                    if (!$el.val() || !String($el.val()).trim()) {
                        $el.addClass('is-invalid');
                        valid = false;
                    }
                });

                /* ── Validate dynamic dropdowns ── */
                var appliesTo = $('#appliesTo').val();
                if (appliesTo === 'item' || appliesTo === 'variation') {
                    if (!$('#itemSelect').val()) {
                        $('#itemSelect').addClass('is-invalid');
                        valid = false;
                    }
                }
                if (appliesTo === 'variation') {
                    if (!$('#variationSelect').val()) {
                        $('#variationSelect').addClass('is-invalid');
                        valid = false;
                    }
                }

                /* ── Validate min value if required ── */
                var minReq = $('#minRequirement').val();
                if ((minReq === 'purchase' || minReq === 'quantity')) {
                    var $minVal = $form.find('[name="min_value"]');
                    if (!$minVal.val() || !String($minVal.val()).trim()) {
                        $minVal.addClass('is-invalid');
                        valid = false;
                    }
                }

                /* ── Validate usage limit fields ── */
                var usageLimitType = $('#usageLimitType').val();
                if (usageLimitType === 'limited') {
                    var $usageLimit = $form.find('[name="usage_limit"]');
                    if (!$usageLimit.val() || !String($usageLimit.val()).trim()) {
                        $usageLimit.addClass('is-invalid');
                        valid = false;
                    }
                }
                if (usageLimitType === 'per_user') {
                    var $perUserLimit = $form.find('[name="usage_limit_per_user"]');
                    if (!$perUserLimit.val() || !String($perUserLimit.val()).trim()) {
                        $perUserLimit.addClass('is-invalid');
                        valid = false;
                    }
                }

                /* ── Validate end date >= start date ── */
                var startsAt = $('[name="starts_at"]').val();
                var endsAt = $('[name="ends_at"]').val();
                if (startsAt && endsAt && endsAt < startsAt) {
                    $('[name="ends_at"]').addClass('is-invalid');
                    valid = false;
                }

                if (!valid) {
                    showNotification('Please fill in all required fields.', 'warning');
                    return;
                }

                /* ── Submit ── */
                var origHTML = $btn.html();
                $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
                showLoader();

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData($form[0]),
                    processData: false,
                    contentType: false,

                    success: function(response) {
                        hideLoader();
                        var result = (typeof response === 'string') ? JSON.parse(response) :
                            response;

                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            if (typeof orgTable !== 'undefined') orgTable.fnDraw();
                            bootstrap.Modal.getInstance(document.getElementById('discountModel'))
                                .hide();
                        } else {
                            showNotification(result.message, 'error');
                            $btn.prop('disabled', false).html(origHTML);
                        }
                    },

                    error: function(xhr) {
                        hideLoader();
                        $btn.prop('disabled', false).html(origHTML);

                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                var $field = $form.find('[name="' + field + '"]');
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(messages[0]);
                            });
                            showNotification('Please fix the errors below.', 'error');
                        } else {
                            showNotification('Something went wrong!', 'error');
                        }
                    }
                });
            });

    })(); // end IIFE
</script>
