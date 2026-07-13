@extends('layouts.main')
@section('title', 'Sales')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Sales</h5>
                <button type="button" id="addSalesVoucher" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Sales Voucher
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="salesVoucherTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th style="width: 5%">S.N</th>
                            <th style="width: 10%">Voucher Number</th>
                            <th style="width: 10%">Order Date</th>
                            <th style="width: 40%">Customer Name</th>
                            <th style="width: 5%">Email</th>
                            <th style="width: 5%">Phone Number</th>
                            <th style="width: 5%">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit/View Modal --}}
    <div class="modal fade" id="svModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 1200px;">
            <div class="modal-content" id="svModalContent"></div>
        </div>
    </div>

    {{-- Add Customer Modal --}}
    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="addCustomerModalContent"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Sales Voucher</h5>
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
        var salesVoucherTable;

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /* ── DataTable ───────────────────────────────────────── */
            salesVoucherTable = $('#salesVoucherTable').dataTable({
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
                sAjaxSource: '{{ route('sales.list') }}',
                sServerMethod: 'POST',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 3]
                }],
                aoColumns: [{
                        data: 'sno'
                    },
                    {
                        data: 'voucher_number'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone'
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
            function openSvModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
                req.done(function(response) {
                    $('#svModalContent').html(response);
                    var modalEl = document.getElementById('svModal');
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
            $('#addSalesVoucher').on('click', function() {
                openSvModal('{{ route('sales.form') }}', {}, 'GET');
            });

            /* ── Edit ────────────────────────────────────────────── */
            $(document).on('click', '.editSalesVoucher', function(e) {
                e.preventDefault();
                openSvModal(
                    '{{ route('sales.form') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            /* ── View ────────────────────────────────────────────── */
            $(document).on('click', '.viewSalesVoucher', function(e) {
                e.preventDefault();
                openSvModal(
                    '{{ route('sales.view') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            /* ── Delete ──────────────────────────────────────────── */
            var deleteId = null;

            $(document).on('click', '.deleteSalesVoucher', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('sales.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            salesVoucherTable.fnDraw();
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
            document.getElementById('svModal').addEventListener('hidden.bs.modal', function() {
                $('#svModalContent').html('');
            });

            document.getElementById('addCustomerModal').addEventListener('hidden.bs.modal', function() {
                $('#addCustomerModalContent').html('');
            });

            /* ── "+ Add Customer" from the customer dropdown ─────── */
            var customerSelectPrevValue = '';

            $(document).on('focus', '#customerSelect', function() {
                customerSelectPrevValue = $(this).val();
            });

            $(document).on('change', '#customerSelect', function() {
                if ($(this).val() !== '__add_customer__') return;

                $(this).val(customerSelectPrevValue);

                $.get('{{ route('user.customer.form') }}')
                    .done(function(response) {
                        $('#addCustomerModalContent').html(response);
                        var modalEl = document.getElementById('addCustomerModal');
                        var existing = bootstrap.Modal.getInstance(modalEl);
                        if (existing) existing.dispose();
                        new bootstrap.Modal(modalEl).show();
                    })
                    .fail(function() {
                        showNotification('Failed to load Add Customer form. Please try again.', 'error');
                    });
            });

            /* ── Add/Edit form submit ─────────────────────────────── */
            $(document).on('submit', '#salesVoucherForm', function(e) {
                e.preventDefault();

                var $form = $(this);
                if (!window.svValidateForm($form)) return;

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
                            salesVoucherTable.fnDraw();
                            bootstrap.Modal.getInstance(document.getElementById('svModal'))
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
