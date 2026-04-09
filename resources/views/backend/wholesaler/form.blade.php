<style>
    .variation-row {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 14px;
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
</style>

<div class="modal-header">
    <h5 class="modal-title">
        {{ !empty($id) ? 'Edit Price' : 'Add Price' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="itemForm" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $id ?? '' }}">

    <div class="modal-body">

        {{-- Row 1: Product / Variation --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select name="itemid" id="itemSelect" class="form-select">
                    <option value="">-- Select Product --</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->itemid }}"
                            {{ ($data['itemid'] ?? '') == $item->itemid ? 'selected' : '' }}>
                            {{ $item->itemname }}
                        </option>
                    @endforeach
                </select>
                <div class="field-error" id="itemError">Product is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Variation <span class="text-danger">*</span></label>
                {{-- Keep a placeholder option so the saved value is visible immediately
                     while JS loads the full list in the background --}}
                <select name="variationid" id="variationSelect" class="form-select">
                    <option value="">-- Select Variation --</option>
                    @if (!empty($data['variation_id']) && !empty($data['variationname']))
                        <option value="{{ $data['variation_id'] }}" selected>
                            {{ $data['variationname'] }}
                        </option>
                    @endif
                </select>
                <div class="field-error" id="variationError">Variation is required.</div>
            </div>

        </div>

        {{-- Row 2: Price Details --}}
        <div class="row g-3 mb-3">
            <div class="col-md-12">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="section-label mb-0">Price Details</p>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addPriceRow">
                        <i class="fa fa-plus"></i> Add Price
                    </button>
                </div>

                <div id="wholesaleDetContainer">
                    @php
                        $details = $data['wholesaler_price_details'] ?? [
                            [
                                'wholesaler_price_details_id' => '',
                                'min_qty' => '',
                                'max_qty' => '',
                                'price' => '',
                            ],
                        ];
                    @endphp

                    @foreach ($details as $i => $v)
                        <div class="variation-row" data-index="{{ $i }}">
                            <button type="button" class="remove-variation">✕</button>
                            <input type="hidden" name="wholesaleDet[{{ $i }}][wholesaler_price_details_id]"
                                value="{{ $v['wholesaler_price_details_id'] ?? '' }}">
                            <div class="row g-2 align-items-end">

                                <div class="col-md-2">
                                    <label class="form-label mb-1">Min Qty</label>
                                    <input type="number" name="wholesaleDet[{{ $i }}][min_qty]"
                                        class="form-control" placeholder="0" min="0" step="1"
                                        value="{{ $v['min_qty'] ?? '' }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1">Max Qty</label>
                                    <input type="number" name="wholesaleDet[{{ $i }}][max_qty]"
                                        class="form-control" placeholder="0" min="0" step="1"
                                        value="{{ $v['max_qty'] ?? '' }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label mb-1">Price</label>
                                    <input type="number" name="wholesaleDet[{{ $i }}][price]"
                                        class="form-control" placeholder="0" min="0" step="1"
                                        value="{{ $v['price'] ?? '' }}">
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                <small class="text-muted">Leave fields empty to skip a row.</small>
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

<script>
    $(function () {

        /* ─────────────────────────────────────────────
           VARIATION LOADER (shared function)
           - itemId       : the selected product id
           - preSelectId  : variation id to pre-select after load (edit mode)
        ───────────────────────────────────────────── */
        function loadVariations(itemId, preSelectId) {
            var $varSelect = $('#variationSelect');
            $varSelect.html('<option value="">Loading...</option>').prop('disabled', true);

            if (!itemId) {
                $varSelect.html('<option value="">-- Select Variation --</option>').prop('disabled', false);
                return;
            }

            $.get('{{ route('inventory.variations') }}', {
                item_id: itemId,          // ✅ correct param name your route expects
                _token : '{{ csrf_token() }}'
            })
            .done(function (response) {
                var options = '<option value="">-- Select Variation --</option>';
                if (response.length > 0) {
                    $.each(response, function (i, v) {
                        options += '<option value="' + v.id + '">'
                                 + v.attribute + ': ' + v.value
                                 + '</option>';
                    });
                } else {
                    options = '<option value="">No variations found</option>';
                }
                $varSelect.html(options).prop('disabled', false);

                // ✅ Pre-select AFTER options are injected (edit mode)
                if (preSelectId) {
                    $varSelect.val(preSelectId);
                }
            })
            .fail(function () {
                $varSelect.html('<option value="">Failed to load</option>').prop('disabled', false);
                showNotification('Failed to load variations.', 'error');
            });
        }

        /* ─────────────────────────────────────────────
           ON PRODUCT CHANGE (user interaction)
        ───────────────────────────────────────────── */
        $('#itemSelect').on('change', function () {
            $(this).removeClass('is-invalid-select');
            $('#itemError').removeClass('show');
            // No pre-select — user is choosing a new product
            loadVariations($(this).val(), null);
        });

        /* ─────────────────────────────────────────────
           AUTO-LOAD ON EDIT MODE
           PHP passes the saved variation_id into JS.
           loadVariations() fetches the full list then
           sets the correct <option> selected.
        ───────────────────────────────────────────── */
        var savedItemId      = '{{ $data['itemid'] ?? '' }}';
        var savedVariationId = '{{ $data['variation_id'] ?? '' }}';

        if (savedItemId) {
            loadVariations(savedItemId, savedVariationId);
        }

        /* ─────────────────────────────────────────────
           ADD / REMOVE PRICE ROWS
        ───────────────────────────────────────────── */
        var rowIdx = $('#wholesaleDetContainer .variation-row').length;

        function newPriceRow(idx) {
            return `
            <div class="variation-row" data-index="${idx}">
                <button type="button" class="remove-variation">✕</button>
                <input type="hidden" name="wholesaleDet[${idx}][wholesaler_price_details_id]" value="">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label mb-1">Min Qty</label>
                        <input type="number" name="wholesaleDet[${idx}][min_qty]"
                               class="form-control" placeholder="0" min="0" step="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">Max Qty</label>
                        <input type="number" name="wholesaleDet[${idx}][max_qty]"
                               class="form-control" placeholder="0" min="0" step="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">Price</label>
                        <input type="number" name="wholesaleDet[${idx}][price]"
                               class="form-control" placeholder="0" min="0" step="1">
                    </div>
                </div>
            </div>`;
        }

        $('#addPriceRow').on('click', function () {
            $('#wholesaleDetContainer').append(newPriceRow(rowIdx++));
        });

        $(document).on('click', '.remove-variation', function () {
            if ($('#wholesaleDetContainer .variation-row').length <= 1) {
                alert('At least one price row is required.');
                return;
            }
            $(this).closest('.variation-row').remove();
        });

        /* ─────────────────────────────────────────────
           VALIDATION
        ───────────────────────────────────────────── */
        function validateForm() {
            var valid = true;
            $('.field-error').removeClass('show');
            $('.is-invalid-select').removeClass('is-invalid-select');

            if (!$('[name="itemid"]').val()) {
                $('[name="itemid"]').addClass('is-invalid-select');
                $('#itemError').addClass('show');
                valid = false;
            }
            if (!$('[name="variationid"]').val()) {
                $('[name="variationid"]').addClass('is-invalid-select');
                $('#variationError').addClass('show');
                valid = false;
            }
            return valid;
        }

        $('#variationSelect').on('change', function () {
            if ($(this).val()) {
                $(this).removeClass('is-invalid-select');
                $('#variationError').removeClass('show');
            }
        });

        /* ─────────────────────────────────────────────
           AJAX SAVE / UPDATE
        ───────────────────────────────────────────── */
        $('#saveItemBtn').on('click', function () {
            if (!validateForm()) return;

            var $btn     = $(this);
            var origHtml = $btn.html();

            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
            );

            $.ajax({
                url         : '{{ route('wholesaler.save') }}',
                type        : 'POST',
                data        : new FormData($('#itemForm')[0]),
                contentType : false,
                processData : false,
                headers     : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },

                success: function (response) {
                    var result = (typeof response === 'string') ? JSON.parse(response) : response;

                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        if (typeof itemTable !== 'undefined') itemTable.fnDraw();  // ✅ old-style API
                        bootstrap.Modal.getInstance(document.getElementById('itemModel')).hide();
                    } else {
                        showNotification(result.message || 'Something went wrong.', 'error');
                        $btn.prop('disabled', false).html(origHtml);
                    }
                },

                error: function (xhr) {
                    $btn.prop('disabled', false).html(origHtml);

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        $.each(xhr.responseJSON.errors, function (field, messages) {
                            var cleanField = field.replace(/\.\*$/, '').replace(/\[\]$/, '');

                            if (cleanField === 'itemid') {
                                $('[name="itemid"]').addClass('is-invalid-select');
                                $('#itemError').text(messages[0]).addClass('show');
                            } else if (cleanField === 'variationid') {
                                $('[name="variationid"]').addClass('is-invalid-select');
                                $('#variationError').text(messages[0]).addClass('show');
                            } else {
                                var $field = $('[name="' + cleanField + '"]');
                                if ($field.length) {
                                    $field.addClass('is-invalid-select');
                                    $field.closest('.col-md-2, .col-md-4, .col-md-6, .col-md-12')
                                          .find('.field-error')
                                          .text(messages[0])
                                          .addClass('show');
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