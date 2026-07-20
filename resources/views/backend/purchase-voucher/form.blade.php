<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />

<div class="modal-header">
    <h5 class="modal-title">
        {{ isset($id) ? 'Edit Purchase Voucher' : 'Add Purchase Voucher' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="purchaseVoucherForm" action="{{ route('purchase-voucher.save') }}" method="POST">
    @csrf
    <input type="hidden" name="id" value="{{ $id ?? '' }}">

    <div class="modal-body">

        @if (isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="text" name="voucher_date" id="voucher_date" class="form-control" autocomplete="off"
                    value="{{ $voucher_date ?? '' }}" data-required>
                <div class="invalid-feedback">Date is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Bill / Voucher No. <span class="text-danger">*</span></label>
                <input type="text" name="voucher_no" class="form-control" placeholder="e.g. INV-001"
                    value="{{ $voucher_no ?? '' }}" data-required>
                <div class="invalid-feedback">Bill / Voucher No. is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Purchase Type <span class="text-danger">*</span></label>
                <select name="purchase_type" class="form-select" data-required>
                    <option value="trading" {{ ($purchase_type ?? 'trading') === 'trading' ? 'selected' : '' }}>Trading
                    </option>
                    <option value="non_trading_capitalized"
                        {{ ($purchase_type ?? '') === 'non_trading_capitalized' ? 'selected' : '' }}>
                        Non-Trading-Capitalized</option>
                    <option value="non_trading_non_capitalized"
                        {{ ($purchase_type ?? '') === 'non_trading_non_capitalized' ? 'selected' : '' }}>
                        Non-Trading-Non-Capitalized</option>
                </select>
                <div class="invalid-feedback">Purchase type is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Party (Vendor) <span class="text-danger">*</span></label>
                <select name="vendor_id" id="vendorSelect" class="form-select" data-required>
                    <option value="">-- Select Vendor --</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->vendorid }}" data-pan="{{ $vendor->vendorpan }}"
                            {{ ($vendor_id ?? '') == $vendor->vendorid ? 'selected' : '' }}>
                            {{ $vendor->vendorname }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Vendor is required.</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">PAN</label>
                <input type="text" id="panInput" class="form-control" value="{{ $pan ?? '' }}" readonly>
            </div>
            <div class="col-md-8">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" placeholder="Optional"
                    value="{{ $remarks ?? '' }}">
            </div>
        </div>

        <style>
            #pvItemsTable input[type=number]::-webkit-outer-spin-button,
            #pvItemsTable input[type=number]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            #pvItemsTable input[type=number] {
                -moz-appearance: textfield;
            }
        </style>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="pvItemsTable"
                style="min-width:1320px; table-layout:fixed;">
                <colgroup>
                    <col style="width:40px;">
                    <col style="width:190px;">
                    <col style="width:170px;">
                    <col style="width:130px;">
                    <col style="width:140px;">
                    <col style="width:160px;">
                    <col style="width:130px;">
                    <col style="width:140px;">
                    <col style="width:60px;">
                </colgroup>
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th>#</th>
                        <th>Item <span class="text-danger">*</span></th>
                        <th>Variation</th>
                        <th>Qty <span class="text-danger">*</span></th>
                        <th>Rate <span class="text-danger">*</span></th>
                        <th>Amount</th>
                        <th>
                            <select id="headerVatSelect" class="form-select form-select-sm mt-1">
                                <option value="">VAT</option>
                                <option value="{{ config('vat.non-taxable') }}">VAT {{ config('vat.non-taxable') }}%</option>
                                @foreach (config('vat.taxable') as $rate)
                                    <option value="{{ $rate }}">VAT {{ $rate }}%</option>
                                @endforeach
                            </select>
                        </th>
                        <th>Excise Duty</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemRows"></tbody>
            </table>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addItemRowBtn">
            <i class="bx bx-plus me-1"></i> Add Item
        </button>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th>Subtotal</th>
                            <td class="text-end" id="subtotalDisplay">0.00</td>
                        </tr>
                        <tr>
                            <th>
                                Bill Discount
                                <input type="number" name="bill_discount_percent" id="billDiscountPercent"
                                    class="form-control form-control-sm d-inline-block ms-2" style="width:80px;"
                                    min="0" max="100" step="0.01"
                                    value="{{ $bill_discount_percent ?? 0 }}"> %
                            </th>
                            <td class="text-end" id="discountAmountDisplay">0.00</td>
                        </tr>
                        <tr>
                            <th>Excise Amount</th>
                            <td class="text-end" id="exciseAmountDisplay">0.00</td>
                        </tr>
                        <tr>
                            <th>Taxable Amount</th>
                            <td class="text-end" id="taxableAmountDisplay">0.00</td>
                        </tr>
                        <tr>
                            <th>VAT Amount</th>
                            <td class="text-end" id="vatAmountDisplay">0.00</td>
                        </tr>
                        <tr class="fw-bold">
                            <th>Total</th>
                            <td class="text-end" id="totalDisplay">0.00</td>
                        </tr>
                    </tbody>
                </table>
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    (function($) {

        var itemsMeta = {};
        @foreach ($items as $item)
            itemsMeta['{{ $item->itemid }}'] = {
                vat_status: '{{ $item->vat_status }}',
                vat_percent: @json((float) ($item->vat_percent ?? config('vat.default'))),
                excise_status: '{{ $item->excise_status }}',
                excise_type: @json($item->excise_type),
                excise_percentage: @json($item->excise_percentage),
                excise_value: @json($item->excise_value)
            };
        @endforeach

        var itemOptionsHtml = '<option value="">-- Item --</option>';
        itemOptionsHtml += '<option value="__add_new_item__">+ Add New Item</option>';
        @foreach ($items as $item)
            itemOptionsHtml += '<option value="{{ $item->itemid }}">{{ addslashes($item->itemname) }}</option>';
        @endforeach

        var allowedVatRates = @json(array_values(array_unique(array_merge([(float) config('vat.non-taxable')], array_map('floatval', config('vat.taxable'))))));
        var defaultVatRate = @json((float) config('vat.default'));
        var vatOptionsHtml = '';
        allowedVatRates.forEach(function(rate) {
            vatOptionsHtml += '<option value="' + rate + '">' + rate + '%</option>';
        });

        function normalizeVat(v) {
            v = parseFloat(v);
            return allowedVatRates.indexOf(v) !== -1 ? v : defaultVatRate;
        }

        var rowIndex = 0;
        var globalVatOverride = null;
        var pendingItemRow = null;

        function escapeHtml(str) {
            return $('<div>').text(str == null ? '' : str).html();
        }

        function addNewItemOption(item) {
            if (!item || !item.itemid) return;

            itemsMeta[item.itemid] = {
                vat_status: item.vat_status,
                vat_percent: parseFloat(item.vat_percent) || 0,
                excise_status: item.excise_status,
                excise_type: item.excise_type,
                excise_percentage: item.excise_percentage,
                excise_value: item.excise_value
            };

            var alreadyListed = itemOptionsHtml.indexOf('value="' + item.itemid + '"') !== -1;
            if (!alreadyListed) {
                var optionHtml = '<option value="' + item.itemid + '">' + escapeHtml(item.itemname) +
                    '</option>';
                itemOptionsHtml = itemOptionsHtml.replace('<option value="__add_new_item__">+ Add New Item</option>',
                    '<option value="__add_new_item__">+ Add New Item</option>' + optionHtml);

                $('.item-select').each(function() {
                    if ($(this).find('option[value="' + item.itemid + '"]').length === 0) {
                        $(this).find('option[value="__add_new_item__"]').after(optionHtml);
                    }
                });
            }
        }

        function initItemSelect($tr) {
            $tr.find('.item-select').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Item --',
                width: '100%',
                dropdownParent: $('#pvModal'),
                minimumResultsForSearch: 0
            });
        }

        function round2(n) {
            return Math.round((n + Number.EPSILON) * 100) / 100;
        }

        function rowTemplate(idx) {
            return '' +
                '<tr class="item-row align-middle" data-index="' + idx + '">' +
                '<td class="row-no">' + (idx + 1) + '</td>' +
                '<td>' +
                '<select name="items[' + idx + '][item_id]" class="form-select item-select" data-required>' +
                itemOptionsHtml + '</select>' +
                '</td>' +
                '<td><select name="items[' + idx +
                '][variation_id]" class="form-select variation-select"><option value="">-- None --</option></select></td>' +
                '<td><input type="number" name="items[' + idx +
                '][qty]" class="form-control qty-input" min="0.01" step="0.01" data-required></td>' +
                '<td><input type="number" name="items[' + idx +
                '][unit_rate]" class="form-control rate-input" min="0" step="0.01" data-required></td>' +
                '<td><input type="text" class="form-control amount-display" readonly value="0.00"></td>' +
                '<td>' +
                '<select name="items[' + idx + '][vat_percent]" class="form-select form-select-sm vat-select">' +
                vatOptionsHtml + '</select>' +
                '<input type="hidden" name="items[' + idx +
                '][vat_touched]" class="vat-touched-input" value="0">' +
                '</td>' +
                '<td class="text-center excise-col">-</td>' +
                '<td>' +
                '<div class="d-flex align-items-center justify-content-center">' +
                '<button type="button" class="btn btn-icon btn-danger remove-item-row" title="Remove item">' +
                '<i class="bx bx-trash"></i>' +
                '</button>' +
                '</div>' +
                '</td>' +
                '</tr>';
        }

        function applyItemTaxInfo($tr, itemId) {
            var meta = itemsMeta[itemId];
            var exciseText = '-';
            if (meta) {
                if (globalVatOverride !== null) {
                    $tr.find('.vat-select').val(String(normalizeVat(globalVatOverride)));
                    $tr.find('.vat-touched-input').val('1');
                } else {
                    var defaultVat = meta.vat_status === 'Y' ? meta.vat_percent : 0;
                    $tr.find('.vat-select').val(String(normalizeVat(defaultVat)));
                    $tr.find('.vat-touched-input').val('0');
                }
                if (meta.excise_status === 'Y') {
                    if (meta.excise_type === 'percentage') {
                        exciseText = meta.excise_percentage + '%';
                    } else if (meta.excise_type === 'fixed') {
                        exciseText = 'Rs ' + meta.excise_value + '/unit';
                    }
                } else {
                    exciseText = 'N/A';
                }
            } else {
                $tr.find('.vat-select').val('0');
                $tr.find('.vat-touched-input').val('0');
            }
            $tr.find('.excise-col').text(exciseText);
        }

        function loadVariationsForRow($tr, itemId, selectedVariationId) {
            var $varSelect = $tr.find('.variation-select');
            if (!itemId) {
                $varSelect.html('<option value="">-- None --</option>').prop('disabled', false);
                return;
            }
            $varSelect.html('<option value="">Loading...</option>').prop('disabled', true);
            $.get('{{ route('inventory.variations') }}', {
                    item_id: itemId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(resp) {
                    var html = '<option value="">-- None --</option>';
                    $.each(resp, function(i, v) {
                        var sel = (selectedVariationId && String(selectedVariationId) === String(v
                            .id)) ? 'selected' : '';
                        html += '<option value="' + v.id + '" ' + sel + '>' + (v.attribute ? v
                            .attribute + ': ' : '') + v.value + '</option>';
                    });
                    $varSelect.html(html).prop('disabled', false);
                })
                .fail(function() {
                    $varSelect.html('<option value="">Failed to load</option>').prop('disabled', false);
                });
        }

        function newItemRow(prefill) {
            prefill = prefill || {};
            var idx = rowIndex++;
            var $tr = $(rowTemplate(idx));
            $('#itemRows').append($tr);

            if (prefill.item_id) {
                $tr.find('.item-select').val(prefill.item_id);
                $tr.data('lastItemId', prefill.item_id);
            }

            initItemSelect($tr);

            if (prefill.item_id) {
                applyItemTaxInfo($tr, prefill.item_id);
                loadVariationsForRow($tr, prefill.item_id, prefill.variation_id);
            }
            if (prefill.qty) $tr.find('.qty-input').val(prefill.qty);
            if (prefill.unit_rate) $tr.find('.rate-input').val(prefill.unit_rate);

            if (prefill.vat_percent !== undefined && prefill.vat_percent !== null && prefill.vat_percent !== '') {
                var savedVat = normalizeVat(prefill.vat_percent);
                var currentVat = parseFloat($tr.find('.vat-select').val());
                if (savedVat !== currentVat) {
                    $tr.find('.vat-select').val(String(savedVat));
                    $tr.find('.vat-touched-input').val('1');
                }
            }

            recalcAll();
        }

        function renumberRows() {
            $('#itemRows tr.item-row').each(function(i) {
                $(this).find('.row-no').text(i + 1);
            });
        }

        function recalcAll() {
            var subtotal = 0;
            var rows = [];

            $('#itemRows tr.item-row').each(function() {
                var $tr = $(this);
                var qty = parseFloat($tr.find('.qty-input').val()) || 0;
                var rate = parseFloat($tr.find('.rate-input').val()) || 0;
                var amount = round2(qty * rate);
                $tr.find('.amount-display').val(amount.toFixed(2));
                subtotal += amount;
                rows.push({
                    $tr: $tr,
                    amount: amount,
                    qty: qty,
                    itemId: $tr.find('.item-select').val()
                });
            });

            var discountPercent = parseFloat($('#billDiscountPercent').val()) || 0;
            var discountAmount = round2(subtotal * discountPercent / 100);
            var preVatBase = round2(subtotal - discountAmount);

            var totalVat = 0;
            var totalExcise = 0;

            rows.forEach(function(r) {
                var meta = itemsMeta[r.itemId];
                var share = subtotal > 0 ? (r.amount / subtotal) * preVatBase : 0;

                var exciseAmt = 0;
                if (meta && meta.excise_status === 'Y') {
                    if (meta.excise_type === 'percentage') {
                        exciseAmt = round2(share * (parseFloat(meta.excise_percentage) || 0) / 100);
                    } else if (meta.excise_type === 'fixed') {
                        exciseAmt = round2((parseFloat(meta.excise_value) || 0) * r.qty);
                    }
                }

                var taxableForVat = share + exciseAmt;
                var vatPercent = parseFloat(r.$tr.find('.vat-select').val()) || 0;
                var vatAmt = round2(taxableForVat * vatPercent / 100);

                totalVat += vatAmt;
                totalExcise += exciseAmt;
            });

            var taxableAmount = round2(preVatBase + totalExcise);
            var grandTotal = round2(taxableAmount + totalVat);

            $('#subtotalDisplay').text(subtotal.toFixed(2));
            $('#discountAmountDisplay').text(discountAmount.toFixed(2));
            $('#exciseAmountDisplay').text(totalExcise.toFixed(2));
            $('#taxableAmountDisplay').text(taxableAmount.toFixed(2));
            $('#vatAmountDisplay').text(totalVat.toFixed(2));
            $('#totalDisplay').text(grandTotal.toFixed(2));
        }

        /* ── Row events ───────────────────────────────────────────── */
        var suppressItemChange = false;

        $(document).off('change.pv', '.item-select').on('change.pv', '.item-select', function() {
            if (suppressItemChange) return;

            var $tr = $(this).closest('tr');
            var itemId = $(this).val();

            if (itemId === '__add_new_item__') {
                pendingItemRow = $tr;

                suppressItemChange = true;
                $(this).val($tr.data('lastItemId') || '').trigger('change');
                suppressItemChange = false;

                if (typeof window.pvOpenAddItemModal === 'function') {
                    window.pvOpenAddItemModal();
                }
                return;
            }

            $tr.data('lastItemId', itemId);
            applyItemTaxInfo($tr, itemId);
            loadVariationsForRow($tr, itemId, null);
            recalcAll();
        });

        /* ── New item created from the nested "Add Item" modal ─────── */
        $(document).off('item:created.pv').on('item:created.pv', function(e, item) {
            addNewItemOption(item);

            if (pendingItemRow && $.contains(document, pendingItemRow[0])) {
                pendingItemRow.data('lastItemId', item.itemid);
                pendingItemRow.find('.item-select').val(item.itemid).trigger('change');
            }
            pendingItemRow = null;
        });

        $('#headerVatSelect').on('change', function() {
            var v = $(this).val();
            globalVatOverride = v === '' ? null : parseFloat(v);

            $('#itemRows tr.item-row').each(function() {
                var $tr = $(this);
                applyItemTaxInfo($tr, $tr.find('.item-select').val());
            });

            recalcAll();
        });

        $(document).off('change.pv', '.vat-select').on('change.pv', '.vat-select', function() {
            $(this).closest('tr').find('.vat-touched-input').val('1');
            recalcAll();
        });

        $(document).off('input.pv change.pv', '.qty-input, .rate-input, #billDiscountPercent')
            .on('input.pv change.pv', '.qty-input, .rate-input, #billDiscountPercent', function() {
                recalcAll();
            });

        $(document).off('click.pv', '.remove-item-row').on('click.pv', '.remove-item-row', function() {
            if ($('#itemRows tr.item-row').length <= 1) {
                showNotification('At least one item row is required.', 'warning');
                return;
            }
            $(this).closest('tr').remove();
            renumberRows();
            recalcAll();
        });

        $('#addItemRowBtn').on('click', function() {
            newItemRow();
        });

        /* ── Vendor → PAN autofill ────────────────────────────────── */
        $('#vendorSelect').on('change', function() {
            $('#panInput').val($(this).find(':selected').data('pan') || '');
        });

        /* ── Hydrate initial rows ─────────────────────────────────── */
        /* Deferred until the modal is actually visible — select2 needs a laid-out (non display:none) container to size correctly */
        $(document).off('shown.bs.modal.purchaseVoucher', '#pvModal')
            .on('shown.bs.modal.purchaseVoucher', '#pvModal', function() {
                var initialLineItems = @json($lineItems ?? []);
                if (initialLineItems.length > 0) {
                    initialLineItems.forEach(function(li) {
                        newItemRow({
                            item_id: li.item_id,
                            variation_id: li.variation_id,
                            unit: li.unit,
                            qty: li.qty,
                            unit_rate: li.unit_rate,
                            vat_percent: li.vat_percent
                        });
                    });
                } else {
                    newItemRow();
                }
            });

        /* ── Client-side validation ───────────────────────────────── */
        window.pvValidateForm = function($form) {
            var valid = true;

            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').hide();

            $form.find('[data-required]').each(function() {
                if (!$(this).val() || !String($(this).val()).trim()) {
                    $(this).addClass('is-invalid');
                    $(this).siblings('.invalid-feedback').show();
                    valid = false;
                }
            });

            if ($('#itemRows tr.item-row').length === 0) {
                showNotification('At least one item is required.', 'error');
                valid = false;
            }

            return valid;
        };

        $(document).off('input.pv change.pv', '#purchaseVoucherForm .form-control, #purchaseVoucherForm .form-select')
            .on('input.pv change.pv', '#purchaseVoucherForm .form-control, #purchaseVoucherForm .form-select',
                function() {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').hide();
                });

    })(jQuery);
</script>
