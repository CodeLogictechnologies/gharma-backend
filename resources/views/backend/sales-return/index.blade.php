@extends('layouts.main')
@section('title', 'Sales Return (Cr. Note)')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Sales Return (Credit Note)</h5>
                @can('add.sales-return')
                <button type="button" id="addSalesReturn" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Credit Note
                </button>
                @endcan
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="salesReturnTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>S.N</th>
                            <!-- <th>Credit Note No.</th> -->
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Against Voucher</th>
                            <th>Qty</th>
                            <th>Total (Rs.)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit/View Modal --}}
    <div class="modal fade" id="srModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 1200px;">
            <div class="modal-content" id="srModalContent"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Sales Return</h5>
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
        var salesReturnTable;

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /* ── DataTable ───────────────────────────────────────── */
            salesReturnTable = $('#salesReturnTable').dataTable({
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
                sAjaxSource: '{{ route('sales-return.list') }}',
                sServerMethod: 'POST',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 7]
                }],
                aoColumns: [{
                        data: 'sno'
                    },
                    // {
                    //     data: 'credit_note_no'
                    // },
                    {
                        data: 'return_date'
                    },
                    {
                        data: 'customer'
                    },
                    {
                        data: 'against_voucher'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'status'
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
            function openSrModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
                req.done(function(response) {
                    $('#srModalContent').html(response);
                    $('#return_date').nepaliDatePicker({
                        container: '#srModal'
                    });
                    var modalEl = document.getElementById('srModal');
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

            /* ── Add ─────────────────────────────────────────────── */
            $('#addSalesReturn').on('click', function() {
                openSrModal('{{ route('sales-return.form') }}', {}, 'GET');
            });

            /* ── Edit ────────────────────────────────────────────── */
            $(document).on('click', '.editSalesReturn', function(e) {
                e.preventDefault();
                openSrModal(
                    '{{ route('sales-return.form') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            /* ── View ────────────────────────────────────────────── */
            $(document).on('click', '.viewSalesReturn', function(e) {
                e.preventDefault();
                openSrModal(
                    '{{ route('sales-return.view') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            /* ── Delete ──────────────────────────────────────────── */
            var deleteId = null;

            $(document).on('click', '.deleteSalesReturn', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('sales-return.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            salesReturnTable.fnDraw();
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

            /* ── Approve / Reject ─────────────────────────────────── */
            function updateSalesReturnStatus(id, status) {
                $.post('{{ route('sales-return.status') }}', {
                        id: id,
                        return_status: status,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            salesReturnTable.fnDraw();
                        } else {
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        showNotification('Failed to update status. Please try again.', 'error');
                    });
            }

            $(document).on('click', '.approveSalesReturn', function(e) {
                e.preventDefault();
                updateSalesReturnStatus($(this).data('id'), 'Approved');
            });

            $(document).on('click', '.rejectSalesReturn', function(e) {
                e.preventDefault();
                updateSalesReturnStatus($(this).data('id'), 'Rejected');
            });

            /* ── Clear modal on close ────────────────────────────── */
            document.getElementById('srModal').addEventListener('hidden.bs.modal', function() {
                $('#srModalContent').html('');
            });

            /* ── Add/Edit form submit ─────────────────────────────── */
            $(document).on('submit', '#salesReturnForm', function(e) {
                e.preventDefault();

                var $form = $(this);
                if (!window.srValidateForm($form)) return;

                var isEdit = $form.find('[name="id"]').val() !== '';
                var $btn = $form.find('[type=submit]');
                var origText = isEdit ? 'Update' : 'Save';

                // $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Saving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            salesReturnTable.fnDraw();
                            if (typeof window.refreshLowStockAlerts === 'function') {
                                window.refreshLowStockAlerts();
                            }
                            bootstrap.Modal.getInstance(document.getElementById('srModal'))
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
