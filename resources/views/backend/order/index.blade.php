@extends('layouts.main')
@section('title', 'Order')
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
                            <th>Phone Number</th>
                            <th>Product Name</th>
                            <th>QTY</th>
                            <th>Total</th>
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
            <div class="modal-content" id="d"></div>
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
                        aTargets: [0, 5]
                    },
                    {
                        sWidth: '10%',
                        aTargets: [5]
                    }
                ],
                aoColumns: [{
                        data: 'sno'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'price'
                    },
                    {
                        data: 'action',
                        bSortable: false
                    },
                ],

            });

            function openOrderModel(url, data, method) {
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

            $(document).on('click', '.viewOrder', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                openOrderModel(
                    '{{ route('order.view') }}', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });
        });
    </script>
@endsection
