@extends('layouts.main')
@section('title', 'Price')

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
                aTargets: [2, 3]
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
                    data: 'action'
                }
            ],
            initComplete: function() {
                this.api().columns([1]).every(function() {
                    var column = this;
                    $('<input type="text" placeholder="Search name" style="width:100%;" />')
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            column.search(this.value).draw();
                        });
                });
            }
        });

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

            // Populate hidden id and price
            $('#id').val(id);
            $('#price').val(price);

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
                return;
            }

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
            $('#formTitle').text('Add Price For Retailer');
            $('.saveRetailer').html('<i class="fa fa-save"></i> Save');
            $('#cancelEdit').addClass('d-none');
        }

    });
</script>
