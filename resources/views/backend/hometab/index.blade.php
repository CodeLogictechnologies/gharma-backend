@extends('layouts.main')
@section('title', 'Home Tab')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Home Tab List</h5>
                <button type="button" id="addHomeTab" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Home Tab
                </button>
            </div>

            <div class="mx-4 mb-4">
                <table class="table" id="homeTabTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th style="width: 5%">S.N</th>
                            <th style="width: 20%">Tab Name</th>
                            <th style="width: 10%">Icon</th>
                            <th style="width: 10%">Color</th>
                            <th style="width: 50%">Category</th>
                            <th style="width: 5%">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div class="modal fade" id="homeTabModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content" id="homeTabModalContent"></div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Home Tab</h5>
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
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────────────────
            window.homeTabTable = $('#homeTabTable').dataTable({
                sPaginationType: 'full_numbers',
                bSort: false,
                language: {
                    paginate: {
                        first: '<i class="bx bx-chevrons-left"></i>',
                        previous: '<i class="bx bx-chevron-left"></i>',
                        next: '<i class="bx bx-chevron-right"></i>',
                        last: '<i class="bx bx-chevrons-right"></i>'
                    }
                },
                lengthMenu: [
                    [10, 30, 50, -1],
                    [10, 30, 50, 'All']
                ],
                iDisplayLength: 10,
                sDom: 'ltipr',
                bProcessing: true,
                bServerSide: true,
                sAjaxSource: '{{ route('hometab.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumns: [{
                        data: 'sno'
                    },
                    {
                        data: 'tab_name'
                    },
                    {
                        data: 'icon_name'
                    },
                    {
                        data: 'bg_color',
                        bSortable: false
                    },
                    {
                        data: 'category_names'
                    },
                    {
                        data: 'action',
                        bSortable: false
                    },
                ],
            });

            // ── Open modal helper ──────────────────────────────────────────────────────
            function openModal(url, params, method) {
                var req = method === 'POST' ? $.post(url, params) : $.get(url, params);
                req.done(function(response) {
                    $('#homeTabModalContent').html(response);
                    var modalEl = document.getElementById('homeTabModal');
                    var existing = bootstrap.Modal.getInstance(modalEl);
                    if (existing) existing.dispose();
                    new bootstrap.Modal(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    }).show();
                }).fail(function() {
                    showNotification('Failed to load form.', 'error');
                });
            }

            // ── Add ────────────────────────────────────────────────────────────────────
            $('#addHomeTab').on('click', function() {
                openModal('{{ route('hometab.form') }}', {}, 'GET');
            });

            // ── Edit ───────────────────────────────────────────────────────────────────
            $(document).on('click', '.editHomeTab', function() {
                openModal('{{ route('hometab.form') }}', {
                    id: $(this).data('id'),
                    _token: '{{ csrf_token() }}'
                }, 'POST');
            });

            // ── Delete ─────────────────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteHomeTab', function() {
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('hometab.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(res) {
                        var result = typeof res === 'string' ? JSON.parse(res) : res;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            homeTabTable.fnDraw();
                        } else {
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        showNotification('Delete failed.', 'error');
                    })
                    .always(function() {
                        deleteId = null;
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    });
            });

            // ── Clear modal on close ───────────────────────────────────────────────────
            document.getElementById('homeTabModal').addEventListener('hidden.bs.modal', function() {
                $('#homeTabModalContent').html('');
            });
        });
    </script>
@endsection
