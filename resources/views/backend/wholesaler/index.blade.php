@extends('layouts.main')
@section('title', 'Item')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">WholeSaler Price List</h5>
                <button type="button" id="addItem" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Item
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="itemTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Product</th>
                            <th>Variation</th>
                            {{-- <th>Min Qty</th>
                            <th>Max Qty</th>
                            <th>Price</th> --}}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- item Add/Edit Modal --}}
    <div class="modal fade" id="itemModel" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="itemModelContent"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
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

@endsection

@section('main-scripts')
    <script>
        var itemTable;

        $(document).ready(function() {

            // ── CSRF setup ────────────────────────────────────────────────
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────
            itemTable = $('#itemTable').dataTable({
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
                sAjaxSource: '{{ route('wholesaler.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [0]
                    },
                    {
                        sWidth: '10%',
                        aTargets: [0]
                    }
                ],
                aoColumns: [{
                        data: 'sno'
                    }, // [0]
                    {
                        data: 'title'
                    }, // [1] — searchable (column index 1)
                    {
                        data: 'value'
                    },
                    // {
                    //     data: 'min_qty'
                    // }, // [2] — searchable (column index 2)
                    // {
                    //     data: 'max_qty'
                    // }, // [3]
                    // {
                    //     data: 'price'
                    // },
                    {
                        data: 'action',
                        bSortable: false
                    }, // [6]
                ],

            });

            // ── Helper: open modal via AJAX ───────────────────────────────
            function openItemModel(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);

                req.done(function(response) {
                    $('#itemModelContent').html(response);

                    // Destroy previous instance if any, then show fresh
                    var modalEl = document.getElementById('itemModel');
                    var existing = bootstrap.Modal.getInstance(modalEl);
                    if (existing) existing.dispose();

                    new bootstrap.Modal(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    }).show();

                }).fail(function() {
                    showNotification('Failed to load form. Please try again.', 'error');
                });
            }

            // ── Add ───────────────────────────────────────────────────────
            $('#addItem').on('click', function() {
                openItemModel('{{ route('wholesaler.form') }}', {}, 'GET');
            });

            // ── Edit ──────────────────────────────────────────────────────
            $(document).on('click', '.editWholesaleprice', function(e) {
                e.preventDefault();
                openItemModel(
                    '{{ route('wholesaler.form') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ── Delete ────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteWholesaleprice', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;

                $.post('{{ route('wholesaler.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            itemTable.fnDraw(); // ✅ old-style API
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

            // ── Clear modal content on close ──────────────────────────────
            document.getElementById('itemModel').addEventListener('hidden.bs.modal', function() {
                $('#itemModelContent').html('');
            });

            // ── Image preview (delegated - works on AJAX loaded content) ──
            $(document).on('change', '#image', function() {
                var file = this.files[0];
                if (file) $('#img_preview').attr('src', URL.createObjectURL(file));
            });

            // ── Form submit (delegated - works on AJAX loaded content) ────
            $(document).on('submit', '#itemForm', function(e) {
                e.preventDefault();

                // Basic required field check
                var valid = true;
                $(this).find('[data-required]').each(function() {
                    $(this).removeClass('is-invalid');
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        valid = false;
                    }
                });
                if (!valid) return;

                var $btn = $(this).find('[type=submit]');
                $btn.prop('disabled', true).text('Saving...');
                showLoader();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        hideLoader();
                        var result = typeof response === 'string' ? JSON.parse(response) :
                            response;

                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            itemTable.fnDraw(); // ✅ old-style API

                            // Close modal
                            var modalEl = document.getElementById('itemModel');
                            bootstrap.Modal.getInstance(modalEl).hide();

                        } else {
                            showNotification(result.message, 'error');
                            $btn.prop('disabled', false).text('Save');
                        }
                    },
                    error: function(xhr) {
                        hideLoader();
                        $btn.prop('disabled', false).text('Save');

                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                $('[name="' + field + '"]').addClass('is-invalid');
                                showNotification(messages[0], 'error');
                            });
                        } else {
                            showNotification('Something went wrong!', 'error');
                        }
                    }
                });
            });

            // ── Clear invalid state on input ──────────────────────────────
            $(document).on('input change', '#itemForm .form-control', function() {
                $(this).removeClass('is-invalid');
            });
            $(document).on('click', '.viewWholesaleprice', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                openItemModel(
                    '{{ route('wholesaler.save') }}', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });
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
