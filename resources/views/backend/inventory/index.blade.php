@extends('layouts.main')
@section('title', 'Inventory')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Inventory List</h5>
                <button type="button" id="addInventory" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Inventory
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="inventoryTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Product</th>
                            <th>Value</th>
                            <th>Stock</th>
                            <th>Sold Qty</th>
                            <th>Remaining</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- item Add/Edit Modal --}}
    <div class="modal fade" id="invModel" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="inventoryModel"></div>
        </div>
    </div>


    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Inventory</h5>
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

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to update this order's status?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatus">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('main-scripts')
    <script>
        var inventoryTable;

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────
            inventoryTable = $('#inventoryTable').dataTable({
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
                    [10, 30, 50, 70, 90, -1],
                    [10, 30, 50, 70, 90, 'All']
                ],
                iDisplayLength: 10,
                sDom: 'ltipr',
                bAutoWidth: false,
                aaSorting: [
                    [0, 'desc']
                ],
                bProcessing: true,
                bServerSide: true,
                ajax: {
                    url: '{{ route('inventory.list') }}',
                    type: 'POST',
                    data: function(d) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 6]
                }],
                aoColumns: [{
                        data: 'sno'
                    },
                    // {
                    //     data: 'categorytitle'
                    // },
                    // {
                    //     data: 'subcategorytitle'
                    // },
                    {
                        data: 'title'
                    },
                    {
                        data: 'variation_value'
                    },
                    {
                        data: 'stock'
                    },
                    {
                        data: 'soldqty'
                    },
                    {
                        data: 'remainingqty'
                    },
                    {
                        data: 'action',
                        bSortable: false
                    },
                ],

                initComplete: function() {
                    this.api().columns([1, 2, 3]).every(function() {
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

            // ── Open modal helper ─────────────────────────────────────────
            function openInventoryModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
                req.done(function(response) {
                    $('#inventoryModel').html(response);
                    var modalEl = document.getElementById('invModel');
                    var existing = bootstrap.Modal.getInstance(modalEl);
                    if (existing) existing.dispose();
                    new bootstrap.Modal(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    }).show();

                    // ✅ Pre-load variations on edit
                    var preloadItemId = $('#preloadItemId').val();
                    var preloadVariationId = $('#preloadVariationId').val();

                    if (preloadItemId) {
                        $.get('{{ route('inventory.variations') }}', {
                            item_id: preloadItemId,
                            _token: '{{ csrf_token() }}'
                        }).done(function(response) {
                            var options = '<option value="">-- Select Variation --</option>';
                            $.each(response, function(i, v) {
                                var selected = v.id == preloadVariationId ? 'selected' : '';
                                options += '<option value="' + v.id + '" ' + selected +
                                    '>' +
                                    v.attribute + ': ' + v.value +
                                    '</option>';
                            });
                            $('#variationSelect').html(options).prop('disabled', false);
                        });
                    }

                }).fail(function() {
                    showNotification('Failed to load form. Please try again.', 'error');
                });
            }

            // ── Add ───────────────────────────────────────────────────────
            $('#addInventory').on('click', function() {
                openInventoryModal('{{ route('inventory.form') }}', {}, 'GET');
            });

            // ── Edit ──────────────────────────────────────────────────────
            $(document).on('click', '.editInventory', function(e) {
                e.preventDefault();
                openInventoryModal(
                    '{{ route('inventory.form') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ── View ──────────────────────────────────────────────────────
            $(document).on('click', '.viewInventory', function(e) {
                e.preventDefault();
                openInventoryModal(
                    '{{ route('inventory.view') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ── Delete ────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteInventory', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                // ✅ Fixed: was posting to inventory.save
                $.post('{{ route('inventory.save') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            inventoryTable.fnDraw();
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

            // ── Clear modal on close ──────────────────────────────────────
            document.getElementById('invModel').addEventListener('hidden.bs.modal', function() {
                $('#inventoryModel').html('');
            });

            // ── Clear invalid on input ────────────────────────────────────
            $(document).on('input change', '#inventoryForm .form-control, #inventoryForm .form-select', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            });

            // ── Form submit ───────────────────────────────────────────────
            $(document).on('submit', '#inventoryForm', function(e) {
                e.preventDefault();

                var valid = true;
                var $form = $(this);

                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').hide();

                // Required field check
                $form.find('[data-required]').each(function() {
                    if (!$(this).val() || !$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        $(this).siblings('.invalid-feedback').show();
                        valid = false;
                    }
                });

                // Expiry must be after manufacture date
                var mfgDate = $form.find('[name="manufacturedatead"]').val();
                var expiryDate = $form.find('[name="expirydatead"]').val();
                if (mfgDate && expiryDate && expiryDate <= mfgDate) {
                    $form.find('[name="expirydatead"]').addClass('is-invalid');
                    $form.find('[name="expirydatead"]')
                        .siblings('.invalid-feedback')
                        .text('Expiry date must be after manufacture date.')
                        .show();
                    valid = false;
                }

                if (!valid) return;

                var isEdit = $form.find('[name="id"]').val() !== '';
                var $btn = $form.find('[type=submit]');
                var origText = isEdit ? 'Update' : 'Save';

                $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Saving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            inventoryTable.fnDraw();
                            bootstrap.Modal.getInstance(
                                document.getElementById('invModel')
                            ).hide();
                        } else {
                            showNotification(result.message, 'error');
                            $btn.prop('disabled', false).html(
                                '<i class="bx ' + (isEdit ? 'bx-save' : 'bx-plus') +
                                ' me-1"></i> ' + origText
                            );
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(
                            '<i class="bx ' + (isEdit ? 'bx-save' : 'bx-plus') +
                            ' me-1"></i> ' + origText
                        );
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                $form.find('[name="' + field + '"]').addClass(
                                    'is-invalid');
                                $form.find('[name="' + field + '"]')
                                    .siblings('.invalid-feedback')
                                    .text(messages[0]).show();
                            });
                        } else {
                            showNotification('Something went wrong!', 'error');
                        }
                    }
                });
            });

            // ── Variation loader (delegated) ──────────────────────────────
            $(document).on('change', '#itemSelect', function() {
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
                                options += '<option value="' + v.id + '">' +
                                    v.attribute + ': ' + v.value +
                                    '</option>';
                            });
                        } else {
                            options = '<option value="">No variations found</option>';
                        }
                        $varSelect.html(options).prop('disabled', false);
                    })
                    .fail(function() {
                        $varSelect.html('<option value="">Failed to load</option>').prop('disabled',
                            false);
                        showNotification('Failed to load variations.', 'error');
                    });
            });

        });
    </script>
@endsection
