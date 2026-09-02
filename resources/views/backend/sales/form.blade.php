<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<style>
    #svItemsTable input[type=number]::-webkit-outer-spin-button,
    #svItemsTable input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    #svItemsTable input[type=number] {
        -moz-appearance: textfield;
    }

    /* ── Highlight rows where a discount is actually applied ── */
    #svItemsTable tr.item-row.has-discount td {
        border-top: 2px solid #fd7e14;
        border-bottom: 2px solid #fd7e14;
    }

    #svItemsTable tr.item-row.has-discount td:first-child {
        border-left: 3px solid #fd7e14;
    }

    #svItemsTable tr.item-row.has-discount .discount-col {
        color: #fd7e14;
        font-weight: 600;
    }

    /* ── Abbreviated Bill mode: hide VAT / VAT Amt / Excise Duty columns entirely ── */
    #svItemsTable.abbreviated-mode .col-vat,
    #svItemsTable.abbreviated-mode .col-vatamt,
    #svItemsTable.abbreviated-mode .col-excise,
    #svItemsTable.abbreviated-mode .vat-th,
    #svItemsTable.abbreviated-mode .vat-amt-th,
    #svItemsTable.abbreviated-mode .excise-th,
    #svItemsTable.abbreviated-mode .vat-col,
    #svItemsTable.abbreviated-mode .vat-total-col,
    #svItemsTable.abbreviated-mode .excise-col {
        display: none;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title">
        {{ isset($id) ? 'Edit Sales Voucher' : 'Add Sales Voucher' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="salesVoucherForm" action="{{ route('sales.save') }}" method="POST">
    @csrf
    <input type="hidden" name="id" value="{{ $id ?? '' }}">

    <div class="modal-body">

        @if (isset($error))
        <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="text" name="voucher_date" id="voucher_date" class="form-control" data-required
                    autocomplete="off" value="{{ $voucher_date ?? '' }}">
                <div class="invalid-feedback">Date is required.</div>
            </div>

            <div class="col-md-5">
                <label class="form-label">Customer <span class="text-danger">*</span></label>
                <select name="customer_id" id="customerSelect" class="form-select" data-required>
                    <option value="">-- Select Customer --</option>
                    <option value="__add_customer__">+ Add Customer</option>
                    @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}"
                        data-customer-type="{{ strtolower($customer->customer_type ?? '') }}"
                        {{ ($customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->username }} ({{ $customer->customer_type }})
                    </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Customer is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Bill Type <span class="text-danger">*</span></label>
                <select name="bill_type" id="billType" class="form-select" data-required>
                    <option value="">Select Bill Type</option>

                    <option value="vat_bill"
                        {{ (($bill_type ?? '') === 'vat_bill') ? 'selected' : '' }}>
                        VAT Bill
                    </option>

                    <option value="abbreviated_bill"
                        {{ (($bill_type ?? '') === 'abbreviated_bill') ? 'selected' : '' }}>
                        Abbreviated Bill
                    </option>
                </select>

                <div class="invalid-feedback">Bill Type is required.</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" placeholder="Optional"
                    value="{{ $remarks ?? '' }}">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="svItemsTable"
                style="min-width:1340px; table-layout:fixed;">
                <colgroup>
                    <col style="width:40px;">
                    <col style="width:160px;">
                    <col style="width:190px;">
                    <col style="width:170px;">
                    <col style="width:110px;">
                    <col style="width:210px;">
                    <col style="width:100px;">
                    <col style="width:120px;">
                    <col class="col-vat" style="width:80px;">
                    <col class="col-vatamt" style="width:90px;">
                    <col class="col-excise" style="width:120px;">
                    <col style="width:60px;">
                </colgroup>
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Item <span class="text-danger">*</span></th>
                        <th>Variation</th>
                        <th>Qty <span class="text-danger">*</span></th>
                        <th id="rateColHeader">Rate with Discount <span class="text-danger">*</span></th>
                        <th>Discount</th>
                        <th>Amount</th>
                        <th class="vat-th">VAT</th>
                        <th class="vat-amt-th">VAT Amt</th>
                        <th class="excise-th">Excise Duty</th>
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
                            <td class="text-end">
                                <span id="subtotalDisplay">0.00</span>
                                <input type="hidden" name="subtotal" id="subtotalInput" value="0">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Bill Discount
                                <input type="number" name="bill_discount_percent" id="billDiscountPercent"
                                    class="form-control form-control-sm d-inline-block ms-2" style="width:80px;"
                                    min="0" max="100" step="0.01"
                                    value="{{ $bill_discount_percent ?? 0 }}"> %
                            </th>
                            <td class="text-end">
                                <span id="discountAmountDisplay">0.00</span>
                                <input type="hidden" name="discount_amount" id="discountAmountInput" value="0">
                            </td>
                        </tr>
                        <tr id="exciseAmountRow">
                            <th>Excise Amount</th>
                            <td class="text-end">
                                <span id="exciseAmountDisplay">0.00</span>
                                <input type="hidden" name="excise_amount" id="exciseAmountInput" value="0">
                            </td>
                        </tr>
                        <tr id="taxableAmountRow">
                            <th>Taxable Amount</th>
                            <td class="text-end">
                                <span id="taxableAmountDisplay">0.00</span>
                                <input type="hidden" name="taxable_amount" id="taxableAmountInput" value="0">
                            </td>
                        </tr>
                        <tr id="vatAmountRow">
                            <th>VAT Amount</th>
                            <td class="text-end">
                                <span id="vatAmountDisplay">0.00</span>
                                <input type="hidden" name="vat_amount" id="vatAmountInput" value="0">
                            </td>
                        </tr>
                        <tr class="fw-bold">
                            <th>Total</th>
                            <td class="text-end">
                                <span id="totalDisplay">0.00</span>
                                <input type="hidden" name="total_amount" id="totalInput" value="0">
                            </td>
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
        var itemIdOrder = [];
        @foreach($items as $item)
        itemsMeta['{{ $item->itemid }}'] = {
            itemname: @json($item->itemname),
            vat_status: '{{ $item->vat_status }}',
            vat_percent: @json((float)($item->vat_percent ?? config('vat.default'))),
            excise_status: '{{ $item->excise_status }}',
            excise_type: @json($item->excise_type),
            excise_percentage: @json($item->excise_percentage),
            excise_value: @json($item->excise_value),
            is_wholesale: @json($item->is_wholesale)
        };
        itemIdOrder.push('{{ $item->itemid }}');
        @endforeach

        function buildItemOptionsHtml(wholesaleOnly) {
            var html = '<option value="">-- Item --</option>';
            html += '<option value="__add_new_item__">+ Add New Item</option>';
            itemIdOrder.forEach(function(id) {
                var meta = itemsMeta[id];
                if (!meta) return;
                if (wholesaleOnly && meta.is_wholesale !== 'Y') return;
                html += '<option value="' + id + '">' + escapeHtml(meta.itemname) + '</option>';
            });
            return html;
        }

        /* ── Product Code dropdown: items + itemvariations, keyed "i:<item_id>" / "v:<variation_id>" ── */
        var productCodeIndex = {};
        var productCodeMeta = {};
        var productCodeOrder = [];
        @foreach($items as $item)
        @if(!empty($item->product_code))
        productCodeIndex['i:{{ $item->itemid }}'] = {
            item_id: '{{ $item->itemid }}',
            variation_id: null
        };
        productCodeMeta['i:{{ $item->itemid }}'] = {
            code: @json($item->product_code),
            is_wholesale: @json($item->is_wholesale)
        };
        productCodeOrder.push('i:{{ $item->itemid }}');
        @endif
        @endforeach
        @foreach($itemVariations as $iv)
        productCodeIndex['v:{{ $iv->variationid }}'] = {
            item_id: '{{ $iv->itemid }}',
            variation_id: '{{ $iv->variationid }}'
        };
        productCodeMeta['v:{{ $iv->variationid }}'] = {
            code: @json($iv->product_code),
            is_wholesale: @json($iv->is_wholesale)
        };
        productCodeOrder.push('v:{{ $iv->variationid }}');
        @endforeach

        function buildProductCodeOptionsHtml(wholesaleOnly) {
            var html = '<option value="">-- Product Code --</option>';
            html += '<option value="__add_product_code__">+ Add Product Code</option>';
            productCodeOrder.forEach(function(key) {
                var meta = productCodeMeta[key];
                if (!meta) return;
                if (wholesaleOnly && meta.is_wholesale !== 'Y') return;
                html += '<option value="' + key + '">' + escapeHtml(meta.code) + '</option>';
            });
            return html;
        }

        function findProductCodeValue(itemId, variationId) {
            if (variationId && productCodeIndex['v:' + variationId]) {
                return 'v:' + variationId;
            }
            if (itemId && productCodeIndex['i:' + itemId]) {
                return 'i:' + itemId;
            }
            return '';
        }

        var rowIndex = 0;
        var pendingItemRow = null;
        var suppressItemChange = false;
        var suppressProductCodeChange = false;

        function escapeHtml(str) {
            return $('<div>').text(str == null ? '' : str).html();
        }

        function addNewItemOption(item) {
            if (!item || !item.itemid) return;

            if (!itemsMeta[item.itemid]) {
                itemIdOrder.push(item.itemid);
            }
            itemsMeta[item.itemid] = {
                itemname: item.itemname,
                vat_status: item.vat_status,
                vat_percent: parseFloat(item.vat_percent) || 0,
                excise_status: item.excise_status,
                excise_type: item.excise_type,
                excise_percentage: item.excise_percentage,
                excise_value: item.excise_value,
                is_wholesale: item.is_wholesale
            };

            if (item.product_code && !productCodeIndex['i:' + item.itemid]) {
                productCodeIndex['i:' + item.itemid] = {
                    item_id: item.itemid,
                    variation_id: null
                };
                productCodeMeta['i:' + item.itemid] = {
                    code: item.product_code,
                    is_wholesale: item.is_wholesale
                };
                productCodeOrder.push('i:' + item.itemid);
            }

            refreshItemFilterForAllRows();
        }

        function itemSelectMatcher(params, data) {
            if (data.id === '__add_new_item__') return data;
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            return data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1 ? data : null;
        }

        function productCodeSelectMatcher(params, data) {
            if (data.id === '__add_product_code__') return data;
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            return data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1 ? data : null;
        }

        function initItemSelect($tr) {
            $tr.find('.item-select').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Item --',
                width: '100%',
                dropdownParent: $('#svModal'),
                minimumResultsForSearch: 0,
                matcher: itemSelectMatcher
            });
        }

        function initProductCodeSelect($tr) {
            $tr.find('.product-code-select').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Product Code --',
                width: '100%',
                dropdownParent: $('#svModal'),
                minimumResultsForSearch: 0,
                matcher: productCodeSelectMatcher
            });
        }

        function round2(n) {
            return Math.round((n + Number.EPSILON) * 100) / 100;
        }

        /* ── Bill Type helpers ── */
        function isAbbreviated() {
            return $('#billType').val() === 'abbreviated_bill';
        }

        function toggleBillTypeUI() {
            var abbreviated = isAbbreviated();
            $('#svItemsTable').toggleClass('abbreviated-mode', abbreviated);
            $('#exciseAmountRow, #taxableAmountRow, #vatAmountRow').toggle(!abbreviated);
        }

        function rowTemplate(idx) {
            var wholesaleOnly = getCustomerType() === 'wholesaler';
            return '' +
                '<tr class="item-row align-middle" data-index="' + idx + '">' +
                '<td class="row-no">' + (idx + 1) + '</td>' +
                '<td><select class="form-select product-code-select">' + buildProductCodeOptionsHtml(wholesaleOnly) + '</select></td>' +
                '<td>' +
                '<select name="items[' + idx + '][item_id]" class="form-select item-select" data-required>' +
                buildItemOptionsHtml(wholesaleOnly) + '</select>' +
                '</td>' +
                '<td><select name="items[' + idx +
                '][variation_id]" class="form-select variation-select"><option value="">-- None --</option></select></td>' +
                '<td><input type="number" name="items[' + idx +
                '][qty]" class="form-control qty-input" min="0.01" step="0.01" data-required>' +
                '<div class="invalid-feedback"></div></td>' +
                '<td><input type="number" name="items[' + idx +
                '][unit_rate]" class="form-control rate-input" min="0" step="0.01" data-required readonly></td>' +
                '<td class="text-center discount-col">-</td>' +
                '<td><input type="text" class="form-control amount-display" readonly value="0.00"></td>' +
                '<td class="text-center vat-col">-</td>' +
                '<td class="text-center vat-total-col">-</td>' +
                '<td class="text-center excise-col">-</td>' +
                '<td>' +
                '<div class="d-flex align-items-center justify-content-center">' +
                '<button type="button" class="btn btn-icon btn-danger remove-item-row" title="Remove item">' +
                '<i class="bx bx-trash"></i>' +
                '</button>' +
                '<input type="hidden" name="items[' + idx + '][excise_amount]" class="excise-amount-input" value="0">' +
                '<input type="hidden" name="items[' + idx + '][vat_amount]" class="vat-amount-input" value="0">' +
                '</div>' +
                '</td>' +
                '</tr>';
        }

        function applyItemTaxInfo($tr, itemId) {
            var meta = itemsMeta[itemId];
            var vatText = meta ? (meta.vat_status === 'Y' ? meta.vat_percent + '%' : '0%') : '-';
            var exciseText = '-';
            if (meta) {
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
            $tr.data('exciseType', meta && meta.excise_status === 'Y' ? meta.excise_type : null);
            $tr.data('excisePercentage', meta && meta.excise_status === 'Y' && meta.excise_type === 'percentage' ?
                meta.excise_percentage : null);
            $tr.find('.vat-col').text(vatText);
            $tr.find('.excise-col').text(exciseText);
            $tr.find('.vat-total-col').text('-');
        }

        function loadVariationsForRow($tr, itemId, selectedVariationId, callback) {
            var $varSelect = $tr.find('.variation-select');
            if (!itemId) {
                $varSelect.html('<option value="">-- None --</option>').prop('disabled', false);
                if (typeof callback === 'function') callback();
                return;
            }
            $varSelect.html('<option value="">Loading...</option>').prop('disabled', true);
            $.get('{{ route('inventory.variations') }}', {
                        item_id: itemId,
                        in_stock_only: 1,
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
                    if (typeof callback === 'function') callback();
                })
                .fail(function() {
                    $varSelect.html('<option value="">Failed to load</option>').prop('disabled', false);
                });
        }

        function syncProductCodeSelect($tr, itemId, variationId) {
            var val = findProductCodeValue(itemId, variationId);
            suppressProductCodeChange = true;
            $tr.find('.product-code-select').val(val).trigger('change');
            suppressProductCodeChange = false;
            $tr.data('lastProductCode', val);
        }

        var priceCache = {};

        function getCustomerType() {
            var selected = $('#customerSelect option:selected');
            return (selected.data('customerType') || '').toString().toLowerCase();
        }

        function updateRateColumnHeader() {
            var isWholesaler = getCustomerType() === 'wholesaler';
            $('#rateColHeader').html(isWholesaler ?
                'Rate <span class="text-danger">*</span>' :
                'Rate with Discount/<br>per unit </br><span class="text-danger">*</span>');
        }

        /* ── Restrict Item / Product Code dropdowns to wholesale-enabled items when a wholesaler customer is selected ── */
        function refreshItemFilterForAllRows() {
            var wholesaleOnly = getCustomerType() === 'wholesaler';

            $('#itemRows tr.item-row').each(function() {
                var $tr = $(this);
                var $itemSelect = $tr.find('.item-select');
                var $pcSelect = $tr.find('.product-code-select');
                var currentItemId = $itemSelect.val();
                var currentPcVal = $pcSelect.val();

                var itemStillValid = !wholesaleOnly || !currentItemId ||
                    (itemsMeta[currentItemId] && itemsMeta[currentItemId].is_wholesale === 'Y');
                var pcStillValid = !wholesaleOnly || !currentPcVal ||
                    (productCodeMeta[currentPcVal] && productCodeMeta[currentPcVal].is_wholesale === 'Y');

                $itemSelect.select2('destroy').html(buildItemOptionsHtml(wholesaleOnly));
                $pcSelect.select2('destroy').html(buildProductCodeOptionsHtml(wholesaleOnly));

                if (itemStillValid && currentItemId) {
                    $itemSelect.val(currentItemId);
                }
                if (pcStillValid && currentPcVal) {
                    $pcSelect.val(currentPcVal);
                }

                initItemSelect($tr);
                initProductCodeSelect($tr);

                if (!itemStillValid) {
                    $tr.data('lastItemId', '');
                    $tr.data('lastProductCode', '');
                    applyItemTaxInfo($tr, '');
                    loadVariationsForRow($tr, '', null);
                    $tr.find('.rate-input').val('');
                    $tr.find('.discount-col').text('-');
                    $tr.data('exciseUnit', 0);
                    $tr.data('vatUnit', 0);
                    $tr.find('.excise-amount-input').val(0);
                    $tr.find('.vat-amount-input').val(0);
                    $tr.find('.vat-total-col').text('-');
                    $tr.find('.excise-col').text('-');
                }
            });

            recalcAll();
        }

        function fetchPricing(itemId, variationId, callback) {
            var key = itemId + '|' + (variationId || '');
            if (priceCache[key]) {
                callback(priceCache[key]);
                return;
            }
            $.get('{{ route('sales.item-price') }}', {
                        item_id: itemId,
                        variation_id: variationId || '',
                        exclude_ordermasterid: $('#salesVoucherForm input[name="id"]').val() || '',
                        _token: '{{ csrf_token() }}'
                    })
                .done(function(resp) {
                    priceCache[key] = resp;
                    callback(resp);
                })
                .fail(function() {
                    callback(null);
                });
        }

        function applyWholesaleRateForGroup(itemId, variationId, tiers) {
            var $rows = $('#itemRows tr.item-row').filter(function() {
                return $(this).find('.item-select').val() === itemId &&
                    $(this).find('.variation-select').val() === variationId;
            });

            var totalQty = 0;
            $rows.each(function() {
                totalQty += parseFloat($(this).find('.qty-input').val()) || 0;
            });

            var tier = null;
            for (var i = 0; i < tiers.length; i++) {
                var min = parseFloat(tiers[i].min_qty);
                var max = parseFloat(tiers[i].max_qty);
                if (totalQty >= min && totalQty <= max) {
                    tier = tiers[i];
                    break;
                }
            }

            $rows.each(function() {
                $(this).find('.rate-input').val(tier ? tier.price : '');
            });
            recalcAll();
        }

        function validateQtyAgainstStock($tr) {
            var itemId = $tr.find('.item-select').val();
            var variationId = $tr.find('.variation-select').val();
            if (!itemId || !variationId) return true;

            var remaining = $tr.data('remaining');
            if (typeof remaining === 'undefined') return true;

            var $rows = $('#itemRows tr.item-row').filter(function() {
                return $(this).find('.item-select').val() === itemId &&
                    $(this).find('.variation-select').val() === variationId;
            });
            var totalQty = 0;
            $rows.each(function() {
                totalQty += parseFloat($(this).find('.qty-input').val()) || 0;
            });

            var ok = totalQty <= remaining;
            var $qtyInputs = $rows.find('.qty-input');
            $qtyInputs.toggleClass('is-invalid', !ok);
            $qtyInputs.siblings('.invalid-feedback')
                .text('Only ' + remaining + ' unit(s) remaining in stock.')
                .toggle(!ok);
            return ok;
        }

        function formatDiscount(retailer) {
            if (retailer.discount_type === 'percentage' && retailer.discount_percentage) {
                return parseFloat(retailer.discount_percentage) + '%';
            } else if (retailer.discount_type === 'fixed' && retailer.discount_amount) {
                return 'Rs ' + retailer.discount_amount + '/unit';
            }
            return '-';
        }

        function applyAutoRate($tr) {
            var itemId = $tr.find('.item-select').val();
            var variationId = $tr.find('.variation-select').val();
            var customerType = getCustomerType();

            $tr.find('.discount-col').text('-');
            $tr.removeClass('has-discount');

            if (!itemId || !variationId) {
                $tr.find('.vat-total-col').text('-');
                $tr.find('.excise-col').text('-');
                return;
            }

            fetchPricing(itemId, variationId, function(pricing) {
                if (!pricing) return;

                $tr.data('remaining', pricing.remaining);
                validateQtyAgainstStock($tr);

                if (customerType === 'wholesaler') {
                    $tr.find('.discount-col').text('-');
                    $tr.removeClass('has-discount');
                    $tr.data('exciseUnit', 0);
                    $tr.data('vatUnit', 0);
                    $tr.find('.excise-amount-input').val(0);
                    $tr.find('.vat-amount-input').val(0);
                    $tr.find('.vat-col').text('-');
                    $tr.find('.excise-col').text('-');
                    $tr.find('.vat-total-col').text('-');
                    applyWholesaleRateForGroup(itemId, variationId, pricing.wholesale_tiers || []);
                    // applyWholesaleRateForGroup() already calls recalcAll() once rates are set.
                } else if (pricing.retailer) {
                    // SP = MRP - (variation discount + active module discount), NO tax baked in.
                    var sp = round2((parseFloat(pricing.retailer.price) || 0) -
                        (parseFloat(pricing.retailer.discount_total) || 0));

                    $tr.find('.rate-input').val(sp);
                    $tr.find('.discount-col').text(formatDiscount(pricing.retailer));

                    var hasDiscount = pricing.retailer.discount_type &&
                        (
                            (pricing.retailer.discount_type === 'percentage' && parseFloat(pricing.retailer.discount_percentage) > 0) ||
                            (pricing.retailer.discount_type === 'fixed' && parseFloat(pricing.retailer.discount_amount) > 0)
                        );
                    $tr.toggleClass('has-discount', !!hasDiscount);

                    // Per-unit excise/VAT amounts (already ₹, converted server-side from % or fixed rules
                    // for a SINGLE unit). Stashed here for recalcAll() to use — VAT column shows this
                    // per-unit figure directly; Excise Duty column and VAT Amt column multiply it by qty.
                    $tr.data('exciseUnit', pricing.retailer.excise_amount || 0);
                    $tr.data('vatUnit', pricing.retailer.vat_amount || 0);
                    $tr.data('spItemOnly', (pricing.retailer.sp_item_only !== undefined && pricing.retailer.sp_item_only !== null) ? parseFloat(pricing.retailer.sp_item_only) : sp);
                    $tr.find('.excise-amount-input').val(pricing.retailer.excise_amount || 0);
                    $tr.find('.vat-amount-input').val(pricing.retailer.vat_amount || 0);

                    recalcAll(); // paints Amount, Excise Duty (unit*qty), and VAT Amt (rate+excise+vat)*qty
                }
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
                var pcVal = findProductCodeValue(prefill.item_id, prefill.variation_id);
                $tr.find('.product-code-select').val(pcVal);
                $tr.data('lastProductCode', pcVal);
            }

            initItemSelect($tr);
            initProductCodeSelect($tr);

            if (prefill.item_id) {
                applyItemTaxInfo($tr, prefill.item_id);
                loadVariationsForRow($tr, prefill.item_id, prefill.variation_id);
            }
            if (prefill.qty) $tr.find('.qty-input').val(prefill.qty);
            if (prefill.unit_rate) $tr.find('.rate-input').val(prefill.unit_rate);

            var qtyForUnit = parseFloat(prefill.qty) || 1;

            if (prefill.excise_amount !== undefined) {
                var exciseUnitVal = round2((parseFloat(prefill.excise_amount) || 0) / qtyForUnit);
                $tr.data('exciseUnit', exciseUnitVal);
                $tr.find('.excise-amount-input').val(exciseUnitVal);
            }
            if (prefill.vat_amount !== undefined) {
                var vatUnitVal = round2((parseFloat(prefill.vat_amount) || 0) / qtyForUnit);
                $tr.data('vatUnit', vatUnitVal);
                $tr.find('.vat-amount-input').val(vatUnitVal);
            }

            if (prefill.item_id && prefill.variation_id) {
                fetchPricing(prefill.item_id, prefill.variation_id, function(pricing) {
                    if (pricing) {
                        $tr.data('remaining', pricing.remaining);
                        validateQtyAgainstStock($tr);

                        if (pricing.retailer && pricing.retailer.sp_item_only !== undefined && pricing.retailer.sp_item_only !== null) {
                            $tr.data('spItemOnly', parseFloat(pricing.retailer.sp_item_only));
                            recalcAll();
                        }
                    }
                });
            }

            recalcAll();
        }

        function renumberRows() {
            $('#itemRows tr.item-row').each(function(i) {
                $(this).find('.row-no').text(i + 1);
            });
        }

        /* ── recalcAll:
           - Amount column (display)     = qty * SP (rate-input).
           - Excise Duty column (display)= excise/unit * qty.
           - VAT Amt column (display)    = (rate + excise/unit + vat/unit) * qty.
           - VAT column (display) is the per-unit ₹ VAT amount only, set in applyAutoRate()/newItemRow() —
             it is NOT re-multiplied here.
           - Real totals (Subtotal → Bill Discount → Taxable → VAT → Total) run on
             qty * SP for subtotal, and separately accumulated excise/vat for the tax rows.
           - Abbreviated Bill mode only changes the FINAL combination step: excise/VAT are
             computed the same way per row, but are not added into Taxable Amount / Total,
             and their summary rows + item-table columns are hidden via CSS. ── */
        function recalcAll() {
            var grandTotal = 0; // pre-tax, sum of qty*SP — drives Subtotal/Total
            var infoExcise = 0;
            var infoVat = 0;

            $('#itemRows tr.item-row').each(function() {
                var $tr = $(this);
                var qty = parseFloat($tr.find('.qty-input').val()) || 0;
                var rate = parseFloat($tr.find('.rate-input').val()) || 0; // actual charged SP — still drives real totals below
                var spItemOnly = parseFloat($tr.data('spItemOnly'));
                if (isNaN(spItemOnly)) spItemOnly = rate; // fallback until pricing has loaded

                var exciseUnit = parseFloat($tr.data('exciseUnit')) || 0;
                var vatUnit = parseFloat($tr.data('vatUnit')) || 0;

                // ── Amount column: qty * SP-with-item-discount-only (excludes promo/module discount) ──
                var displayAmount = round2(qty * spItemOnly);
                $tr.find('.amount-display').val(displayAmount.toFixed(2));

                // ── Excise Duty column: fixed-type multiplies by qty, percentage-type shows per-unit as-is ──
                var exciseType = $tr.data('exciseType');
                if (rate > 0 && exciseType === 'fixed') {
                    var exciseLine = round2(exciseUnit * qty);
                    $tr.find('.excise-col').text(exciseLine.toFixed(2));
                } else if (rate > 0 && exciseType === 'percentage') {
                    var excisePct = $tr.data('excisePercentage');
                    $tr.find('.excise-col').text((excisePct != null ? excisePct : 0) + '%');
                } else {
                    $tr.find('.excise-col').text('-');
                }

                // ── VAT Amt column: (rate + excise/unit + vat/unit) * qty ──
                if (rate > 0) {
                    var vatAmtLine = round2((rate + exciseUnit + vatUnit) * qty);
                    $tr.find('.vat-total-col').text(vatAmtLine.toFixed(2));
                } else {
                    $tr.find('.vat-total-col').text('-');
                }

                // ── Real totals math: based on the ACTUAL charged rate, not spItemOnly ──
                grandTotal += round2(qty * rate);
                infoExcise += exciseUnit * qty;
                infoVat += vatUnit * qty;
            });

            var subtotal = round2(grandTotal);
            var discountPercent = parseFloat($('#billDiscountPercent').val()) || 0;
            var discountAmount = round2(subtotal * discountPercent / 100);
            var afterBillDiscount = round2(subtotal - discountAmount);

            infoExcise = round2(infoExcise);
            infoVat = round2(infoVat);

            var taxableAmount, total;

            if (isAbbreviated()) {
                // Abbreviated Bill: no excise, no VAT added on top of Subtotal - Discount.
                taxableAmount = afterBillDiscount;
                total = afterBillDiscount;
                infoExcise = 0;
                infoVat = 0;
            } else {
                // VAT Bill: unchanged original behaviour.
                taxableAmount = round2(afterBillDiscount + infoExcise);
                total = round2(taxableAmount + infoVat);
            }

            $('#subtotalDisplay').text(subtotal.toFixed(2));
            $('#subtotalInput').val(subtotal.toFixed(2));

            $('#discountAmountDisplay').text(discountAmount.toFixed(2));
            $('#discountAmountInput').val(discountAmount.toFixed(2));

            $('#exciseAmountDisplay').text(infoExcise.toFixed(2));
            $('#exciseAmountInput').val(infoExcise.toFixed(2));

            $('#taxableAmountDisplay').text(taxableAmount.toFixed(2));
            $('#taxableAmountInput').val(taxableAmount.toFixed(2));

            $('#vatAmountDisplay').text(infoVat.toFixed(2));
            $('#vatAmountInput').val(infoVat.toFixed(2));

            $('#totalDisplay').text(total.toFixed(2));
            $('#totalInput').val(total.toFixed(2));
        }

        /* ── Row events ───────────────────────────────────────────── */
        $(document).on('change', '.item-select', function() {
            if (suppressItemChange) return;

            var $tr = $(this).closest('tr');
            var itemId = $(this).val();

            if (itemId === '__add_new_item__') {
                pendingItemRow = $tr;
                var existingItemId = $tr.data('lastItemId');

                suppressItemChange = true;
                $(this).val(existingItemId || '').trigger('change');
                suppressItemChange = false;

                window.prefillItemName = lastItemSearchTerm;
                window.prefillProductCode = '';

                if (typeof window.svOpenAddItemModal === 'function') {
                    window.svOpenAddItemModal(existingItemId || null);
                }
                return;
            }

            $tr.data('lastItemId', itemId);
            applyItemTaxInfo($tr, itemId);
            loadVariationsForRow($tr, itemId, null);
            syncProductCodeSelect($tr, itemId, null);
            recalcAll();
        });

        $(document).on('change', '.variation-select', function() {
            var $tr = $(this).closest('tr');
            var itemId = $tr.find('.item-select').val();
            var variationId = $(this).val();
            syncProductCodeSelect($tr, itemId, variationId);
            applyAutoRate($tr);
        });

        $(document).on('change', '.product-code-select', function() {
            if (suppressProductCodeChange) return;

            var $tr = $(this).closest('tr');
            var val = $(this).val();

            if (val === '__add_product_code__') {
                pendingItemRow = $tr;
                var existingItemId = $tr.data('lastItemId');
                var existingVariationId = $tr.find('.variation-select').val();

                suppressProductCodeChange = true;
                $(this).val($tr.data('lastProductCode') || '').trigger('change');
                suppressProductCodeChange = false;

                window.prefillItemName = '';
                window.prefillProductCode = lastProductCodeSearchTerm;
                window.prefillVariationId = existingVariationId || '';

                if (typeof window.svOpenAddItemModal === 'function') {
                    window.svOpenAddItemModal(existingItemId || null);
                }
                return;
            }

            var ref = productCodeIndex[val];
            if (!ref) return;

            $tr.data('lastProductCode', val);

            var $itemSelect = $tr.find('.item-select');
            if ($itemSelect.find('option[value="' + ref.item_id + '"]').length === 0) {
                var meta = itemsMeta[ref.item_id];
                if (meta) {
                    $itemSelect.select2('destroy');
                    $itemSelect.append(
                        '<option value="' + ref.item_id + '">' + escapeHtml(meta.itemname) + '</option>'
                    );
                    initItemSelect($tr);
                }
            }

            suppressItemChange = true;
            $itemSelect.val(ref.item_id).trigger('change');
            suppressItemChange = false;

            $tr.data('lastItemId', ref.item_id);
            applyItemTaxInfo($tr, ref.item_id);
            loadVariationsForRow($tr, ref.item_id, ref.variation_id, function() {
                applyAutoRate($tr);
            });
            recalcAll();
        });

        $(document).off('item:created.sv').on('item:created.sv', function(e, item) {
            addNewItemOption(item);

            if (pendingItemRow && $.contains(document, pendingItemRow[0])) {
                pendingItemRow.data('lastItemId', item.itemid);
                pendingItemRow.find('.item-select').val(item.itemid).trigger('change');
            }
            pendingItemRow = null;
        });

        $(document).on('input change', '.qty-input', function() {
            applyAutoRate($(this).closest('tr'));
            recalcAll();
            validateQtyAgainstStock($(this).closest('tr'));
        });

        $(document).on('input change', '.rate-input, #billDiscountPercent', function() {
            recalcAll();
        });

        /* ── Bill Type change: toggle column/row visibility and recalc totals ── */
        $(document).on('change', '#billType', function() {
            toggleBillTypeUI();
            recalcAll();
        });

        var lastCustomerSearchTerm = '';
        var lastItemSearchTerm = '';
        var lastProductCodeSearchTerm = '';
        var activeSearchField = null;

        $(document).on('select2:open', function(e) {
            activeSearchField = $(e.target);
        });

        $(document).on('input', '#svModal .select2-search__field', function() {
            var val = $(this).val();
            if (!activeSearchField) return;

            if (activeSearchField.is('#customerSelect')) {
                lastCustomerSearchTerm = val;
            } else if (activeSearchField.hasClass('item-select')) {
                lastItemSearchTerm = val;
            } else if (activeSearchField.hasClass('product-code-select')) {
                lastProductCodeSearchTerm = val;
            }
        });

        $('#customerSelect').on('change', function() {
            var val = $(this).val();

            if (val === '__add_customer__') {
                window.prefillCustomerName = lastCustomerSearchTerm;
                $(document).trigger('sv:openAddCustomer', [lastCustomerSearchTerm]);
            }

            updateRateColumnHeader();
            refreshItemFilterForAllRows();
            $('#itemRows tr.item-row').each(function() {
                applyAutoRate($(this));
            });
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

        $('#addItemRowBtn').on('click', function() {
            newItemRow();
        });

        function customerMatcher(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (data.id === '__add_customer__') {
                return data;
            }
            if (typeof data.text === 'undefined') {
                return null;
            }
            if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                return data;
            }
            return null;
        }

        $(document).off('shown.bs.modal.salesVoucher', '#svModal')
            .on('shown.bs.modal.salesVoucher', '#svModal', function() {
                $('#customerSelect').select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Select Customer --',
                    width: '100%',
                    dropdownParent: $('#svModal'),
                    minimumResultsForSearch: 0,
                    matcher: customerMatcher
                });

                updateRateColumnHeader();
                toggleBillTypeUI();

                var initialLineItems = @json($lineItems ?? []);
                if (initialLineItems.length > 0) {
                    initialLineItems.forEach(function(li) {
                        newItemRow({
                            item_id: li.item_id,
                            variation_id: li.variation_id,
                            qty: li.qty,
                            unit_rate: li.unit_rate,
                            excise_amount: li.excise_amount,
                            vat_amount: li.vat_amount
                        });
                    });
                } else {
                    newItemRow();
                }
            });

        window.svValidateForm = function($form) {
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

            $('#itemRows tr.item-row').each(function() {
                if (!validateQtyAgainstStock($(this))) valid = false;
            });

            return valid;
        };

        $(document).on('input change', '#salesVoucherForm .form-control:not(.qty-input), #salesVoucherForm .form-select',
            function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            });

    })(jQuery);
</script>