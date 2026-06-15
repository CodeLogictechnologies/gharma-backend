@extends('layouts.main')
@section('title', 'Coupons')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Coupon List</h5>
                <button type="button" id="addCoupon" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Coupon
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="couponTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Coupon Code</th>
                            <th>Discount</th> 
                            <th>Applies To</th>
                            <th>Min Requirement</th>
                            <th>Usage Limit</th>
                            <th>Used Count</th>
                            <th>Start At</th>
                            <th>End At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div class="modal fade" id="couponModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="couponModalContent"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Coupon</h5>
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
        var couponTable;

        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // ── DataTable ─────────────────────────────────────────────────
            couponTable = $('#couponTable').dataTable({
                sPaginationType : 'full_numbers',
                bSearchable     : false,
                language: {
                    paginate: {
                        first    : '<i class="bx bx-chevrons-left"></i>',
                        previous : '<i class="bx bx-chevron-left"></i>',
                        next     : '<i class="bx bx-chevron-right"></i>',
                        last     : '<i class="bx bx-chevrons-right"></i>'
                    }
                },
                lengthMenu    : [[10, 30, 50, 70, 90, -1], [10, 30, 50, 70, 90, 'All']],
                iDisplayLength: 10,
                sDom          : 'ltipr',
                bAutoWidth    : false,
                aaSorting     : [[0, 'desc']],
                bProcessing   : true,
                bServerSide   : true,
                sAjaxSource   : '{{ route('coupon.list') }}',
                oLanguage     : { sEmptyTable: "<p class='no_data_message'>No data available.</p>" },
                aoColumnDefs  : [
                    { bSortable: false, aTargets: [0, 6, 7, 8] },
                    { sWidth: '10%',    aTargets: [8] }
                ],
                aoColumns: [
                    { data: 'sno' },
                    { data: 'coupon_code' },
                    { data: 'applies_to' },
                    { data: 'min_requirement' },
                    { data: 'usage_limit_type' },
                    { data: 'used_count' },
                    { data: 'starts_at' },
                    { data: 'ends_at' },
                    { data: 'discount' },
                    { data: 'action' },
                ],
                initComplete: function () {
                    this.api().columns([1, 3]).every(function () {
                        var column = this;
                        var header = $(column.header()).text().trim();
                        $('<input type="text" class="form-control" placeholder="' + header + '..." style="width:100%;" />')
                            .appendTo($(column.header()).empty())
                            .on('keyup change', function () {
                                column.search(this.value).draw();
                            });
                    });
                }
            });

            // ── Helper: open modal ────────────────────────────────────────
            function openCouponModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
                req.done(function (response) {
                    $('#couponModalContent').html(response);
                    var modalEl  = document.getElementById('couponModal');
                    var existing = bootstrap.Modal.getInstance(modalEl);
                    if (existing) existing.dispose();
                    new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false }).show();
                }).fail(function () {
                    showNotification('Failed to load form. Please try again.', 'error');
                });
            }

            // ── Add ───────────────────────────────────────────────────────
            $('#addCoupon').on('click', function () {
                openCouponModal('{{ route('coupon.form') }}', {}, 'GET');
            });

            // ── Edit ──────────────────────────────────────────────────────
            $(document).on('click', '.editCoupon', function (e) {
                e.preventDefault();
                openCouponModal('{{ route('coupon.form') }}', { id: $(this).data('id'), _token: '{{ csrf_token() }}' }, 'POST');
            });

            // ── View ──────────────────────────────────────────────────────
            $(document).on('click', '.viewCoupon', function (e) {
                e.preventDefault();
                openCouponModal('{{ route('coupon.view') }}', { id: $(this).data('id'), _token: '{{ csrf_token() }}' }, 'POST');
            });

            // ── Delete ────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteCoupon', function (e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function () {
                if (!deleteId) return;
                $.post('{{ route('coupon.delete') }}', { id: deleteId, _token: '{{ csrf_token() }}' })
                    .done(function (response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            couponTable.fnDraw();
                        } else {
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function () {
                        showNotification('Delete failed. Please try again.', 'error');
                    })
                    .always(function () {
                        deleteId = null;
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    });
            });

            // ── Clear modal on close ──────────────────────────────────────
            document.getElementById('couponModal').addEventListener('hidden.bs.modal', function () {
                $('#couponModalContent').html('');
                $(document).off('.coupon');
                window._couponModalInitialized = false;
            });

            // ── Clear invalid on input ────────────────────────────────────
            $(document).on('input change', '#couponForm .form-control', function () {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endsection