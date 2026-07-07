<div class="modal-header">
    <h5 class="modal-title">
        {{ isset($id) ? 'Edit Purchase Return (Debit Note)' : 'Add Purchase Return (Debit Note)' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="purchaseReturnForm" action="{{ route('purchase-return.save') }}" method="POST">
    @csrf
    <input type="hidden" name="id" value="{{ $id ?? '' }}">

    <div class="modal-body">

        @if (isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="return_date" class="form-control" data-required
                    value="{{ $return_date ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                <div class="invalid-feedback">Date is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Debit Note No. <span class="text-danger">*</span></label>
                <input type="text" name="debit_note_no" class="form-control" placeholder="e.g. DN-001"
                    value="{{ $debit_note_no ?? '' }}" data-required>
                <div class="invalid-feedback">Debit Note No. is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Vendor <span class="text-danger">*</span></label>
                <select name="vendor_id" id="vendorSelect" class="form-select" data-required>
                    <option value="">-- Select --</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->vendorid }}"
                            {{ ($vendor_id ?? '') == $vendor->vendorid ? 'selected' : '' }}>
                            {{ $vendor->vendorname }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Vendor is required.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Against Voucher</label>
                <select name="against_voucher_id" id="againstVoucherSelect" class="form-select">
                    <option value="">-- Optional --</option>
                    @foreach ($vendorVouchers ?? [] as $voucher)
                        <option value="{{ $voucher->id }}"
                            {{ ($against_voucher_id ?? '') == $voucher->id ? 'selected' : '' }}>
                            {{ $voucher->voucher_no }} ({{ \Carbon\Carbon::parse($voucher->voucher_date)->format('Y-m-d') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" placeholder="Optional"
                    value="{{ $remarks ?? '' }}">
            </div>
        </div>

        <style>
            #prItemsTable input[type=number]::-webkit-outer-spin-button,
            #prItemsTable input[type=number]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            #prItemsTable input[type=number] {
                -moz-appearance: textfield;
            }
        </style>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="prItemsTable" style="min-width:960px; table-layout:fixed;">
                <colgroup>
                    <col style="width:40px;">
                    <col style="width:210px;">
                    <col style="width:190px;">
                    <col style="width:100px;">
                    <col style="width:110px;">
                    <col style="width:110px;">
                    <col style="width:90px;">
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
                        <th>VAT</th>
                        <th>Excise Duty</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemRows"></tbody>
            </table>
        </div>

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
                                    min="0" max="100" step="0.01" value="{{ $bill_discount_percent ?? 0 }}" readonly> %
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

<script>
(function($) {

    var itemsMeta = {};
    @foreach ($items as $item)
        itemsMeta['{{ $item->itemid }}'] = {
            vat_status: '{{ $item->vat_status }}',
            excise_status: '{{ $item->excise_status }}',
            excise_type: @json($item->excise_type),
            excise_percentage: @json($item->excise_percentage),
            excise_value: @json($item->excise_value)
        };
    @endforeach

    var itemOptionsHtml = '<option value="">-- Item --</option>';
    @foreach ($items as $item)
        itemOptionsHtml += '<option value="{{ $item->itemid }}">{{ addslashes($item->itemname) }}</option>';
    @endforeach

    var VAT_RATE = {{ (float) config('vat.taxable') }};
    var rowIndex = 0;

    function round2(n) {
        return Math.round((n + Number.EPSILON) * 100) / 100;
    }

    function rowTemplate(idx) {
        return '' +
            '<tr class="item-row align-middle" data-index="' + idx + '">' +
                '<td class="row-no">' + (idx + 1) + '</td>' +
                '<td>' +
                    '<select class="form-select item-select" data-required disabled>' + itemOptionsHtml + '</select>' +
                    '<input type="hidden" name="items[' + idx + '][item_id]" class="item-id-hidden">' +
                '</td>' +
                '<td>' +
                    '<select class="form-select variation-select" disabled><option value="">-- None --</option></select>' +
                    '<input type="hidden" name="items[' + idx + '][variation_id]" class="variation-id-hidden">' +
                '</td>' +
                '<td><input type="number" name="items[' + idx + '][qty]" class="form-control qty-input" min="0.01" step="0.01" data-required></td>' +
                '<td><input type="number" name="items[' + idx + '][unit_rate]" class="form-control rate-input" min="0" step="0.01" data-required readonly></td>' +
                '<td><input type="text" class="form-control amount-display" readonly value="0.00"></td>' +
                '<td class="text-center vat-col">-</td>' +
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
        var vatText = '-';
        var exciseText = '-';
        if (meta) {
            vatText = meta.vat_status === 'Y' ? VAT_RATE + '%' : '0%';
            if (meta.excise_status === 'Y') {
                if (meta.excise_type === 'percentage') {
                    exciseText = meta.excise_percentage + '%';
                } else if (meta.excise_type === 'fixed') {
                    exciseText = 'Rs ' + meta.excise_value + '/unit';
                }
            } else {
                exciseText = 'N/A';
            }
        }
        $tr.find('.vat-col').text(vatText);
        $tr.find('.excise-col').text(exciseText);
    }

    function loadVariationsForRow($tr, itemId, selectedVariationId) {
        var $varSelect = $tr.find('.variation-select');
        if (!itemId) {
            $varSelect.html('<option value="">-- None --</option>');
            return;
        }
        $varSelect.html('<option value="">Loading...</option>');
        $.get('{{ route('inventory.variations') }}', { item_id: itemId, _token: '{{ csrf_token() }}' })
            .done(function(resp) {
                var html = '<option value="">-- None --</option>';
                $.each(resp, function(i, v) {
                    var sel = (selectedVariationId && String(selectedVariationId) === String(v.id)) ? 'selected' : '';
                    html += '<option value="' + v.id + '" ' + sel + '>' + (v.attribute ? v.attribute + ': ' : '') + v.value + '</option>';
                });
                $varSelect.html(html);
            })
            .fail(function() {
                $varSelect.html('<option value="">Failed to load</option>');
            });
    }

    function newItemRow(prefill) {
        prefill = prefill || {};
        var idx = rowIndex++;
        var $tr = $(rowTemplate(idx));
        $('#itemRows').append($tr);

        if (prefill.item_id) {
            $tr.find('.item-select').val(prefill.item_id);
            $tr.find('.item-id-hidden').val(prefill.item_id);
            applyItemTaxInfo($tr, prefill.item_id);
            loadVariationsForRow($tr, prefill.item_id, prefill.variation_id);
        }
        if (prefill.variation_id) {
            $tr.find('.variation-id-hidden').val(prefill.variation_id);
        }
        if (prefill.qty)       $tr.find('.qty-input').val(prefill.qty);
        if (prefill.unit_rate) $tr.find('.rate-input').val(prefill.unit_rate);

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
            rows.push({ $tr: $tr, amount: amount, qty: qty, itemId: $tr.find('.item-select').val() });
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
            var vatPercent = meta && meta.vat_status === 'Y' ? VAT_RATE : 0;
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
    $(document).on('input change', '.qty-input, .rate-input, #billDiscountPercent', function() {
        recalcAll();
    });

    $(document).on('click', '.remove-item-row', function() {
        if ($('#itemRows tr.item-row').length <= 1) {
            showNotification('At least one item row is required.', 'warning');
            return;
        }
        $(this).closest('tr').remove();
        renumberRows();
        recalcAll();
    });

    /* ── Vendor → Against Voucher list ───────────────────────────── */
    function loadVendorVouchers(vendorId, selectedVoucherId) {
        var $sel = $('#againstVoucherSelect');
        if (!vendorId) {
            $sel.html('<option value="">-- Optional --</option>');
            return;
        }
        $.get('{{ route('purchase-return.vendor-vouchers') }}', {
                vendor_id: vendorId,
                exclude_return_id: $('input[name="id"]').val(),
                _token: '{{ csrf_token() }}'
            })
            .done(function(resp) {
                var html = '<option value="">-- Optional --</option>';
                $.each(resp, function(i, v) {
                    var sel = (selectedVoucherId && String(selectedVoucherId) === String(v.id)) ? 'selected' : '';
                    var d = v.voucher_date ? v.voucher_date.substring(0, 10) : '';
                    html += '<option value="' + v.id + '" ' + sel + '>' + v.voucher_no + ' (' + d + ')</option>';
                });
                $sel.html(html);
            })
            .fail(function() {
                $sel.html('<option value="">Failed to load</option>');
            });
    }

    $('#vendorSelect').on('change', function() {
        loadVendorVouchers($(this).val(), null);
    });

    /* ── Against Voucher → auto-populate items from that purchase ── */
    function populateFromVoucher(voucherId) {
        if (!voucherId) return;

        $.get('{{ route('purchase-return.voucher-items') }}', { voucher_id: voucherId, _token: '{{ csrf_token() }}' })
            .done(function(resp) {
                var items = (resp && resp.items) || [];
                if (!items.length) {
                    showNotification('No items found for the selected voucher.', 'warning');
                    return;
                }
                $('#billDiscountPercent').val(resp.bill_discount_percent || 0);
                $('#itemRows').empty();
                rowIndex = 0;
                items.forEach(function(li) {
                    newItemRow({
                        item_id: li.item_id,
                        variation_id: li.variation_id,
                        qty: li.qty,
                        unit_rate: li.unit_rate
                    });
                });
            })
            .fail(function() {
                showNotification('Failed to load voucher items.', 'error');
            });
    }

    $('#againstVoucherSelect').on('change', function() {
        populateFromVoucher($(this).val());
    });

    /* ── Hydrate initial rows ─────────────────────────────────── */
    var initialLineItems = @json($lineItems ?? []);
    if (initialLineItems.length > 0) {
        initialLineItems.forEach(function(li) {
            newItemRow({
                item_id: li.item_id,
                variation_id: li.variation_id,
                qty: li.qty,
                unit_rate: li.unit_rate
            });
        });
    } else {
        newItemRow();
    }

    @if (isset($vendor_id) && $vendor_id)
        loadVendorVouchers('{{ $vendor_id }}', '{{ $against_voucher_id ?? '' }}');
    @endif

    /* ── Client-side validation ───────────────────────────────── */
    window.prValidateForm = function($form) {
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

    $(document).on('input change', '#purchaseReturnForm .form-control, #purchaseReturnForm .form-select', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').hide();
    });

})(jQuery);
</script>
