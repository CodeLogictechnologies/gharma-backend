@extends('layouts.main')
@section('title', 'Purchase Voucher')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Purchase Vouchers</h5>
                <button type="button" id="addPurchaseVoucher" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Purchase Voucher
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="purchaseVoucherTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>S.N</th>
                            <th>Voucher No.</th>
                            <th>Date</th>
                            <th>Vendor</th>
                            <th>Type</th>
                            <th>Total (Rs.)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit/View Modal --}}
    <div class="modal fade" id="pvModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 1380px;">
            <div class="modal-content" id="pvModalContent"></div>
        </div>
    </div>

    {{-- Add Item Modal (triggered from "+ Add New Item" inside the purchase voucher item row) --}}
    <div class="modal fade" id="pvAddItemModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="pvAddItemModalContent"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Purchase Voucher</h5>
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
        var purchaseVoucherTable;

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /* ── DataTable ───────────────────────────────────────── */
            purchaseVoucherTable = $('#purchaseVoucherTable').dataTable({
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
                sAjaxSource: '{{ route('purchase-voucher.list') }}',
                sServerMethod: 'POST',
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
                    {
                        data: 'voucher_no'
                    },
                    {
                        data: 'voucher_date'
                    },
                    {
                        data: 'vendor'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'action'
                    },
                ],
                initComplete: function() {
                    this.api().columns([1, 3]).every(function() {
                        var column = this;
                        var header = $(column.header()).text().trim();
                        $('<input type="text" class="form-control" placeholder="' + header +
                                '..." style="width:100%;" />')
                            .appendTo($(column.header()).empty())
                            .on('keyup change', function() {
                                column.search(this.value).draw();
                            });
                    });
                }
            });

            /* ── Helper: open modal via AJAX ─────────────────────── */
            function openPvModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
                req.done(function(response) {
                    $('#pvModalContent').html(response);
                    $('#voucher_date').nepaliDatePicker({
                        container: '#pvModal'
                    });

                    var modalEl = document.getElementById('pvModal');
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

            /* ── Helper: open the "Add/Edit Item" modal on top of the purchase voucher form ──
               Passing an itemId opens that item in edit mode (used when the row already has
               an item selected and the user wants to add/fix its product code) instead of a
               blank "Add Item" form. ── */
            window.pvOpenAddItemModal = function(itemId) {
                $.get('{{ route('item.form') }}', itemId ? { id: itemId } : {})
                    .done(function(response) {
                        $('#pvAddItemModalContent').html(response);

                        var modalEl = document.getElementById('pvAddItemModal');
                        var existing = bootstrap.Modal.getInstance(modalEl);
                        if (existing) existing.dispose();
                        new bootstrap.Modal(modalEl, {
                            backdrop: 'static',
                            keyboard: false
                        }).show();
                    }).fail(function() {
                        showNotification('Failed to load item form. Please try again.', 'error');
                    });
            };

            document.getElementById('pvAddItemModal').addEventListener('hidden.bs.modal', function() {
                $('#pvAddItemModalContent').html('');
            });

            /* ── Add ─────────────────────────────────────────────── */
            $('#addPurchaseVoucher').on('click', function() {
                openPvModal('{{ route('purchase-voucher.form') }}', {}, 'GET');
            });

            /* ── Edit ────────────────────────────────────────────── */
            $(document).on('click', '.editPurchaseVoucher', function(e) {
                e.preventDefault();
                openPvModal(
                    '{{ route('purchase-voucher.form') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            /* ── View ────────────────────────────────────────────── */
            $(document).on('click', '.viewPurchaseVoucher', function(e) {
                e.preventDefault();
                openPvModal(
                    '{{ route('purchase-voucher.view') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            /* ── Delete ──────────────────────────────────────────── */
            var deleteId = null;

            $(document).on('click', '.deletePurchaseVoucher', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('purchase-voucher.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            purchaseVoucherTable.fnDraw();
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

            /* ── Clear modal on close ────────────────────────────── */
            document.getElementById('pvModal').addEventListener('hidden.bs.modal', function() {
                $('#pvModalContent').html('');
            });

            /* ── Add/Edit form submit ─────────────────────────────── */
            $(document).on('submit', '#purchaseVoucherForm', function(e) {
                e.preventDefault();

                var $form = $(this);
                if (!window.pvValidateForm($form)) return;

                var isEdit = $form.find('[name="id"]').val() !== '';
                var $btn = $form.find('[type=submit]');
                var origText = isEdit ? 'Update' : 'Save';

                $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Saving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            purchaseVoucherTable.fnDraw();
                            if (typeof window.refreshLowStockAlerts === 'function') {
                                window.refreshLowStockAlerts();
                            }
                            bootstrap.Modal.getInstance(document.getElementById('pvModal'))
                                .hide();
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
                            showNotification(Object.values(xhr.responseJSON.errors)[0][0],
                                'error');
                        } else {
                            showNotification('Something went wrong!', 'error');
                        }
                    }
                });
            });

        });
    </script>
@endsection
