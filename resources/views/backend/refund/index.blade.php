@extends('layouts.main')
@section('title', 'Refund')
<style>
    th .status {
        width: 18% !important;
    }
</style>
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Refund List</h5>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="refundTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Product</th>
                            <th>Reason</th>
                            <th class="status">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- View Order Modal --}}
    <div class="modal fade" id="itemModel" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="d"></div>
        </div>
    </div>

    {{-- Status Confirm Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to update this refund status?</p>

                    {{-- Admin reason — only shown for REJECTED, COMPLETED, CANCELLED --}}
                    <div id="adminReasonWrapper" style="display:none; margin-top: 14px;">
                        <label for="adminReason" class="form-label fw-semibold">
                            Admin Reason <span class="text-danger">*</span>
                        </label>
                        <textarea id="adminReason" class="form-control" rows="3" placeholder="Enter reason..." maxlength="500"></textarea>
                        <div id="adminReasonError" class="text-danger small mt-1" style="display:none;">
                            Please provide a reason.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelStatus"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatus">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('main-scripts')
    <script>
        var refundTable;

        // Statuses that require an admin reason
        var REASON_REQUIRED_STATUSES = ['REJECTED', 'COMPLETED', 'CANCELLED'];

        $(document).ready(function() {

            // ── CSRF setup ────────────────────────────────────────────────
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────
            refundTable = $('#refundTable').dataTable({
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
                sAjaxSource: '{{ route('refund.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [0]
                    },
                    {
                        sWidth: '15%',
                        aTargets: [4]
                    }
                ],
                aoColumns: [{
                        data: 'sno'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'product'
                    },
                    {
                        data: 'reason'
                    },
                    {
                        data: 'refund_status'
                    },
                    {
                        data: 'action'
                    },
                ],
                initComplete: function() {
                    this.api().columns([1]).every(function() {
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

            // ── Open View Order Modal ─────────────────────────────────────
            function openOrderModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);

                req.done(function(response) {
                    $('#d').html(response);
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

            // ── Clear modal content on close ──────────────────────────────
            document.getElementById('itemModel').addEventListener('hidden.bs.modal', function() {
                $('#d').html('');
            });

            // ── Clear invalid state on input ──────────────────────────────
            $(document).on('input change', '#itemForm .form-control', function() {
                $(this).removeClass('is-invalid');
            });

            // ── View refund click ──────────────────────────────────────────
            $(document).on('click', '.viewRefund', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                openOrderModal(
                    '{{ route('refund.view') }}', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ── Status Change ─────────────────────────────────────────────
            let selectedStatus = null;
            let selectedId = null;
            let previousStatus = null;
            let $activeDropdown = null;

            // Helper — reset modal to clean state
            function resetStatusModal() {
                selectedId = null;
                selectedStatus = null;
                $activeDropdown = null;
                $('#adminReason').val('').removeClass('is-invalid');
                $('#adminReasonError').hide();
                $('#adminReasonWrapper').hide();
            }

            // Open confirm modal when dropdown changes
            $(document).on('change', '.changeStatus', function() {
                $activeDropdown = $(this);
                selectedId = $(this).data('id');
                previousStatus = $(this).data('current');
                selectedStatus = $(this).val();

                // Show admin reason field only for specific statuses
                if (REASON_REQUIRED_STATUSES.includes(selectedStatus)) {
                    $('#adminReasonWrapper').show();
                    $('#adminReason').val('');
                    $('#adminReasonError').hide();
                    $('#adminReason').removeClass('is-invalid');
                } else {
                    $('#adminReasonWrapper').hide();
                    $('#adminReason').val('');
                }

                new bootstrap.Modal(document.getElementById('statusModal'), {
                    backdrop: 'static',
                    keyboard: false
                }).show();
            });

            // Confirm button — validate then submit
            $(document).on('click', '#confirmStatus', function() {
                if (!selectedId) return;

                // Validate admin reason for REJECTED, COMPLETED, CANCELLED
                var adminReason = '';
                if (REASON_REQUIRED_STATUSES.includes(selectedStatus)) {
                    adminReason = $('#adminReason').val().trim();
                    if (!adminReason) {
                        $('#adminReason').addClass('is-invalid');
                        $('#adminReasonError').show();
                        return; // Stop — keep modal open
                    }
                }

                $.post('{{ route('refund.update.status') }}', {
                        id: selectedId,
                        status: selectedStatus,
                        admin_reason: adminReason,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(res) {
                        let result = (typeof res === 'string') ? JSON.parse(res) : res;

                        if (result.type === 'success') {
                            if ($activeDropdown) {
                                $activeDropdown.data('current', selectedStatus);
                            }
                            showNotification(result.message, 'success');
                            refundTable.fnDraw();
                        } else {
                            if ($activeDropdown) $activeDropdown.val(previousStatus);
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        if ($activeDropdown) $activeDropdown.val(previousStatus);
                        showNotification('Status update failed. Please try again.', 'error');
                    })
                    .always(function() {
                        var modalInstance = bootstrap.Modal.getInstance(
                            document.getElementById('statusModal')
                        );
                        if (modalInstance) modalInstance.hide();
                        resetStatusModal();
                    });
            });

            // Cancel button — revert dropdown to previous value
            $(document).on('click', '#cancelStatus', function() {
                if ($activeDropdown) $activeDropdown.val(previousStatus);
                resetStatusModal();
            });

            // Safety net — revert if modal closes without confirming
            document.getElementById('statusModal').addEventListener('hidden.bs.modal', function() {
                if (selectedId && $activeDropdown) $activeDropdown.val(previousStatus);
                resetStatusModal();
            });

        });
    </script>
@endsection
