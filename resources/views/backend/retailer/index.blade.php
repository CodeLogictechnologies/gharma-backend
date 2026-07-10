@extends('layouts.main')
@section('title', 'Retailer Price Management')

<script src="/assets/vendor/libs/jquery/jquery.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

@section('content')

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Price</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Are you sure? You won't be able to revert this.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="nav-align-top mb-4">
            <div class="tab-content mt-4" id="nav-tabContent">
                <div class="row g-4">

                    {{-- ── LEFT: Form ─────────────────────────────────────── --}}
                    <div class="col-12 col-lg-4">
                        <h5 class="mb-3" id="formTitle">Add Price For Retailer</h5>
                        <form id="retailerForm" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="id" id="id" value="">

                            <div class="mb-3">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select name="itemid" id="itemSelect" class="form-select">
                                    <option value="">-- Select Product --</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->itemid }}"
                                            data-vat-status="{{ $item->vat_status }}"
                                            data-vat-percent="{{ $item->vat_percent }}"
                                            data-excise-status="{{ $item->excise_status }}"
                                            data-excise-type="{{ $item->excise_type }}"
                                            data-excise-percentage="{{ $item->excise_percentage }}"
                                            data-excise-value="{{ $item->excise_value }}"
                                            {{ ($itemid ?? '') == $item->itemid ? 'selected' : '' }}>
                                            {{ $item->itemname }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Product is required.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Variation <span class="text-danger">*</span></label>
                                <select name="variationid" id="variationSelect" class="form-select">
                                    <option value="">-- Select Variation --</option>
                                    @if (isset($variationid) && $variationid)
                                        <option value="{{ $variationid }}" selected>
                                            {{ $variationname ?? 'Loading...' }}
                                        </option>
                                    @endif
                                </select>
                                <div class="invalid-feedback">Variation is required.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Price Per Unit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="price" id="price"
                                    placeholder="Example: 12" min="0" step="any" />
                                <div class="invalid-feedback">Price is required.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Discount Type</label>
                                <select name="discount_type" id="discountType" class="form-select">
                                    <option value="">-- Select Type --</option>
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>

                            {{-- Percentage field: shown when discount type = percentage --}}
                            <div class="mb-3" id="discountPercentageField" style="display: none;">
                                <label class="form-label">Percentage (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="discount_percentage"
                                        id="discountPercentage" placeholder="e.g. 2" min="0" max="100"
                                        step="0.01" />
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback">Discount percentage must be between 0 and 100.</div>
                            </div>

                            {{-- Fixed amount field: shown when discount type = fixed --}}
                            <div class="mb-3" id="discountAmountField" style="display: none;">
                                <label class="form-label">Fixed Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" class="form-control" name="discount_amount"
                                        id="discountAmount" placeholder="e.g. 50" min="0" step="0.01" />
                                </div>
                                <div class="invalid-feedback">Discount amount is required.</div>
                            </div>

                            <div class="mb-3 d-flex gap-4">
                                <div class="form-check">
                                    <input type="hidden" name="excise_status" value="0">
                                    <input class="form-check-input" type="checkbox"
                                        id="exciseStatus" name="excise_status" value="1">
                                    <label class="form-check-label" for="exciseStatus">Excise Duty</label>
                                </div>
                                <div class="form-check">
                                    <input type="hidden" name="vat_status" value="0">
                                    <input class="form-check-input" type="checkbox"
                                        id="vatStatus" name="vat_status" value="1">
                                    <label class="form-check-label" for="vatStatus">VAT Status</label>
                                </div>
                            </div>

                            <div class="mb-3" id="vatPercentField" style="display: none;">
                                <label class="form-label" for="vatPercent">VAT Rate</label>
                                <select name="vat_percent" id="vatPercent" class="form-select">
                                    @foreach (config('vat.taxable') as $rate)
                                        <option value="{{ $rate }}">{{ $rate }}%</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3" id="exciseTypeField" style="display: none;">
                                <label class="form-label" for="exciseType">Excise Type</label>
                                <select name="excise_type" id="exciseType" class="form-select">
                                    <option value="">-- Select Type --</option>
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>

                            {{-- Percentage field: shown when excise type = percentage --}}
                            <div class="mb-3" id="excisePercentageField" style="display: none;">
                                <label class="form-label" for="excisePercentage">Percentage (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="excise_percentage"
                                        id="excisePercentage" placeholder="e.g. 10" min="0" max="100"
                                        step="0.01" />
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback">Excise percentage must be between 0 and 100.</div>
                            </div>

                            {{-- Fixed amount field: shown when excise type = fixed --}}
                            <div class="mb-3" id="exciseValueField" style="display: none;">
                                <label class="form-label" for="exciseValue">Fixed Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" class="form-control" name="excise_value"
                                        id="exciseValue" placeholder="e.g. 50" min="0" step="0.01" />
                                </div>
                                <div class="invalid-feedback">Excise amount is required.</div>
                            </div>

                            <button type="button" class="btn btn-primary saveRetailer">
                                <i class="fa fa-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-secondary ms-2 d-none" id="cancelEdit">
                                Cancel
                            </button>
                        </form>
                    </div>

                    {{-- ── RIGHT: Table ───────────────────────────────────── --}}
                    <div class="col-12 col-lg-8">
                        <div class="table-responsive text-nowrap">
                            <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                                <table class="table" id="retailerTable">
                                    <thead class="table-light">
                                        <tr class="align-middle">
                                            <th>S.No</th>
                                            <th>Product</th>
                                            <th>Variation</th>
                                            <th>Price</th>
                                            <th>Discount</th>
                                            <th>VAT Status</th>
                                            <th>Excise Duty</th>
                                            <th>Selling Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

<script>
    var retailerTable;

    $(document).ready(function() {

        // ── CSRF ──────────────────────────────────────────────────────────────
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ── jQuery Validate – custom rule for select ──────────────────────────
        $.validator.addMethod('selectRequired', function(value) {
            return value !== '' && value !== null && value !== undefined;
        }, 'This field is required.');

        // ── Validation ────────────────────────────────────────────────────────
        $('#retailerForm').validate({
            rules: {
                itemid: {
                    selectRequired: true
                },
                variationid: {
                    selectRequired: true
                },
                price: {
                    required: true,
                    min: 0
                },
                discount_percentage: {
                    min: 0,
                    max: 100
                },
                discount_amount: {
                    min: 0
                },
                excise_percentage: {
                    min: 0,
                    max: 100
                },
                excise_value: {
                    min: 0
                },
            },
            messages: {
                itemid: {
                    selectRequired: 'Please select a product.'
                },
                variationid: {
                    selectRequired: 'Please select a variation.'
                },
                price: {
                    required: 'Price is required.',
                    min: 'Price must be 0 or greater.'
                },
                discount_percentage: {
                    min: 'Discount must be 0 or greater.',
                    max: 'Discount cannot exceed 100.'
                },
                discount_amount: {
                    min: 'Discount amount must be 0 or greater.'
                },
                excise_percentage: {
                    min: 'Excise percentage must be 0 or greater.',
                    max: 'Excise percentage cannot exceed 100.'
                },
                excise_value: {
                    min: 'Excise amount must be 0 or greater.'
                },
            },
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            errorPlacement: function(error, element) {
                // Show error in the .invalid-feedback sibling
                error.appendTo(element.closest('.mb-3').find('.invalid-feedback'));
            },
            submitHandler: function() {
                /* handled by button click */
            }
        });

        // ── DataTable ─────────────────────────────────────────────────────────
        retailerTable = $('#retailerTable').dataTable({
            sPaginationType: 'full_numbers',
            bSearchable: false,
            language: {
                paginate: {
                    first: '<i class="bx bx-chevrons-left"></i>',
                    previous: '<i class="bx bx-chevron-left"></i>',
                    next: '<i class="bx bx-chevron-right"></i>',
                    last: '<i class="bx bx-chevrons-right"></i>'
                }
            },
            lengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, 'All']
            ],
            iDisplayLength: 5,
            sDom: 'ltipr',
            bAutoWidth: false,
            aaSorting: [
                [0, 'desc']
            ],
            bProcessing: true,
            bServerSide: true,
            ajax: {
                url: '{{ route('retailer.list') }}',
                type: 'POST',
                data: function(d) {
                    d.type = $('#trashed_file').is(':checked') ? 'trashed' : 'nottrashed';
                }
            },
            oLanguage: {
                sEmptyTable: "<p class='no_data_message'>No data available.</p>"
            },
            aoColumnDefs: [{
                bSortable: false,
                aTargets: [2, 3, 4, 5, 6, 7]
            }],
            aoColumns: [{
                    data: 'sno'
                },
                {
                    data: 'title'
                },
                {
                    data: 'value'
                },
                {
                    data: 'price'
                },
                {
                    data: 'discount'
                },
                {
                    data: 'vat_status'
                },
                {
                    data: 'excise_duty'
                },
                {
                    data: 'selling_price'
                },
                {
                    data: 'action'
                }
            ],

            initComplete: function() {
                this.api().columns([1, 2]).every(function() {
                    var column = this;
                    var header = $(column.header()).text()
                        .trim();

                    var input = $(
                            '<input type="text" class="form-control" placeholder="' +
                            header + '..." style="width:100%;" />'
                        )
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            column.search(this.value).draw();
                        });
                });
            }
        });

        // ── DISCOUNT TYPE → show/hide percentage & amount fields ──────────────
        function applyDiscountTypeUI(type) {
            $('#discountPercentageField').toggle(type === 'percentage');
            $('#discountAmountField').toggle(type === 'fixed');
            if (type !== 'percentage') $('#discountPercentage').val('');
            if (type !== 'fixed') $('#discountAmount').val('');
        }

        $('#discountType').on('change', function() {
            applyDiscountTypeUI($(this).val());
        });

        // ── VAT STATUS → show/hide rate field ───────────────────────────────────
        function toggleVatFields() {
            var enabled = $('#vatStatus').is(':checked');
            $('#vatPercentField').toggle(enabled);
        }

        $('#vatStatus').on('change', toggleVatFields);

        // ── EXCISE DUTY → show/hide type & value fields ────────────────────────
        function applyExciseTypeUI(type) {
            $('#excisePercentageField').toggle(type === 'percentage');
            $('#exciseValueField').toggle(type === 'fixed');
            if (type !== 'percentage') $('#excisePercentage').val('');
            if (type !== 'fixed') $('#exciseValue').val('');
        }

        function toggleExciseFields() {
            var enabled = $('#exciseStatus').is(':checked');
            $('#exciseTypeField').toggle(enabled);
            if (!enabled) {
                $('#exciseType').val('');
                applyExciseTypeUI('');
            } else {
                applyExciseTypeUI($('#exciseType').val());
            }
        }

        $('#exciseStatus').on('change', toggleExciseFields);
        $('#exciseType').on('change', function() {
            applyExciseTypeUI($(this).val());
        });

        // Populate the VAT/excise section from the selected product's own tax settings
        function applyExciseFromItem($option) {
            var status = $option.data('excise-status') === 'Y';
            var type = $option.data('excise-type') || '';
            var percentage = $option.data('excise-percentage');
            var value = $option.data('excise-value');
            var vatStatus = $option.data('vat-status') === 'Y';
            var vatPercent = $option.data('vat-percent');

            $('#exciseStatus').prop('checked', status);
            $('#exciseType').val(status ? type : '');
            $('#excisePercentage').val(type === 'percentage' ? percentage : '');
            $('#exciseValue').val(type === 'fixed' ? value : '');
            $('#vatStatus').prop('checked', vatStatus);
            if (vatPercent !== undefined && vatPercent !== null && vatPercent !== '') {
                var vatPercentValue = parseFloat(vatPercent);
                $('#vatPercent option').each(function() {
                    $(this).prop('selected', parseFloat($(this).val()) === vatPercentValue);
                });
            }
            toggleExciseFields();
            toggleVatFields();
        }

        // ── Save / Update ─────────────────────────────────────────────────────
        $(document).on('click', '.saveRetailer', function() {
            if (!$('#retailerForm').valid()) return;

            var formData = new FormData(document.getElementById('retailerForm'));

            $.ajax({
                url: '{{ route('retailer.save') }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) :
                        response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        retailerTable.fnDraw();
                        resetForm();
                    } else {
                        showNotification(result.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Something went wrong!', 'error');
                }
            });
        });

        // ── Edit ──────────────────────────────────────────────────────────────
        $(document).on('click', '.editRetailer', function(e) {
            e.preventDefault();

            var id = $(this).data('id');
            var itemId = $(this).data('itemid');
            var variationId = $(this).data('variationid');
            var price = $(this).data('price');
            var discountType = $(this).data('discounttype');
            var discountPercentage = $(this).data('discountpercentage');
            var discountAmount = $(this).data('discountamount');

            // Populate hidden id, price and discount
            $('#id').val(id);
            $('#price').val(price);
            $('#discountType').val(discountType || '');
            $('#discountPercentage').val(discountPercentage || '');
            $('#discountAmount').val(discountAmount || '');
            applyDiscountTypeUI(discountType || '');

            // Set the product dropdown, then load variations
            $('#itemSelect').val(itemId).trigger('change', [variationId]);
            // Update UI
            $('#formTitle').text('Edit Price For Retailer');
            $('.saveRetailer').html('<i class="fas fa-save"></i> Update');
            $('#cancelEdit').removeClass('d-none');

            $('html, body').animate({
                scrollTop: $('#retailerForm').offset().top - 20
            }, 300);
        });

        // ── Cancel Edit ───────────────────────────────────────────────────────
        $(document).on('click', '#cancelEdit', function() {
            resetForm();
        });

        // ── Delete ────────────────────────────────────────────────────────────
        var deleteId = null;

        $(document).on('click', '.deleteRetailer', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });

        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            $.post('{{ route('retailer.delete') }}', {
                    id: deleteId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        retailerTable.fnDraw();
                    } else {
                        showNotification(result.message, 'error');
                    }
                })
                .fail(function() {
                    showNotification('Delete failed. Please try again.', 'error');
                })
                .always(function() {
                    deleteId = null;
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                });
        });

        // ── Load Variations on product change ────────────────────────────────
        // The custom event `.loadVariations` carries the pre-selected variationId (for edit).
        $(document).on('change', '#itemSelect', function(e, preSelectedVariationId) {
            var itemId = $(this).val();
            var $varSelect = $('#variationSelect');

            $varSelect.html('<option value="">Loading...</option>').prop('disabled', true);

            if (!itemId) {
                $varSelect.html('<option value="">-- Select Variation --</option>').prop('disabled',
                    false);
                applyExciseFromItem($(this).find(':selected'));
                return;
            }

            applyExciseFromItem($(this).find(':selected'));

            $.get('{{ route('inventory.variations') }}', {
                    item_id: itemId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    var options = '<option value="">-- Select Variation --</option>';
                    if (response.length > 0) {
                        $.each(response, function(i, v) {
                            options += '<option value="' + v.id + '">' + v.attribute +
                                ': ' + v.value + '</option>';
                        });
                    } else {
                        options = '<option value="">No variations found</option>';
                    }
                    $varSelect.html(options).prop('disabled', false);

                    // If we came from edit, pre-select the saved variation
                    if (preSelectedVariationId) {
                        $varSelect.val(preSelectedVariationId);
                    }
                })
                .fail(function() {
                    $varSelect.html('<option value="">Failed to load</option>').prop('disabled',
                        false);
                    showNotification('Failed to load variations.', 'error');
                });
        });

        // ── Helpers ───────────────────────────────────────────────────────────
        function resetForm() {
            $('#retailerForm')[0].reset();
            $('#id').val('');
            $('#retailerForm').validate().resetForm();
            $('#retailerForm .is-invalid').removeClass('is-invalid');
            $('#retailerForm .is-valid').removeClass('is-valid');
            $('#variationSelect').html('<option value="">-- Select Variation --</option>').prop('disabled',
                false);
            applyDiscountTypeUI('');
            applyExciseFromItem($());
            $('#formTitle').text('Add Price For Retailer');
            $('.saveRetailer').html('<i class="fa fa-save"></i> Save');
            $('#cancelEdit').addClass('d-none');
        }

    });
</script>
