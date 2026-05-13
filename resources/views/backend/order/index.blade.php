@extends('layouts.main')
@section('title', 'Order')
<style>
    th .status {
        width: 15% !important;
    }
</style>
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Order List</h5>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="itemTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Order Time</th>
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
                    Are you sure you want to update this order's status?
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
                sAjaxSource: '{{ route('order.list') }}',
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
                        data: 'email'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'order_status'
                    },
                    {
                        data: 'action'
                    },
                ],
                initComplete: function() {
                    this.api().columns([1, 2]).every(function() {
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

            // ── View Order click ──────────────────────────────────────────
            $(document).on('click', '.viewOrder', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                openOrderModal(
                    '{{ route('order.view') }}', {
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

            // Open confirm modal when dropdown changes
            $(document).on('change', '.changeStatus', function() {
                $activeDropdown = $(this);
                selectedId = $(this).data('id');
                previousStatus = $(this).data('current');
                selectedStatus = $(this).val();

                new bootstrap.Modal(document.getElementById('statusModal'), {
                    backdrop: 'static',
                    keyboard: false
                }).show();
            });

            // Confirm button — submit the status update
            $(document).on('click', '#confirmStatus', function() {
                if (!selectedId) return;

                $.post('{{ route('order.status.update') }}', {
                        id: selectedId,
                        status: selectedStatus,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(res) {
                        let result = (typeof res === 'string') ? JSON.parse(res) : res;

                        if (result.type === 'success') {
                            // Update data-current so future reverts are correct
                            if ($activeDropdown) {
                                $activeDropdown.data('current', selectedStatus);
                            }
                            showNotification(result.message, 'success');
                            itemTable.fnDraw(); // ← fixed: was userTable
                        } else {
                            // Revert dropdown on failure
                            if ($activeDropdown) {
                                $activeDropdown.val(previousStatus);
                            }
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        // Revert dropdown on AJAX failure
                        if ($activeDropdown) {
                            $activeDropdown.val(previousStatus);
                        }
                        showNotification('Status update failed. Please try again.', 'error');
                    })
                    .always(function() {
                        var modalInstance = bootstrap.Modal.getInstance(document.getElementById(
                            'statusModal'));
                        if (modalInstance) modalInstance.hide();
                        selectedId = null;
                        selectedStatus = null;
                        $activeDropdown = null;
                    });
            });

            // Cancel button — revert dropdown to previous value
            $(document).on('click', '#cancelStatus', function() {
                if ($activeDropdown) {
                    $activeDropdown.val(previousStatus);
                }
                selectedId = null;
                selectedStatus = null;
                $activeDropdown = null;
            });

            // Safety net: revert if modal closes without confirming
            document.getElementById('statusModal').addEventListener('hidden.bs.modal', function() {
                if (selectedId && $activeDropdown) {
                    $activeDropdown.val(previousStatus);
                }
                selectedId = null;
                selectedStatus = null;
                $activeDropdown = null;
            });

        });
    </script>
@endsection
