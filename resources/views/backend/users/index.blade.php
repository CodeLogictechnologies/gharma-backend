@extends('layouts.main')
@section('title', 'User')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">User List</h5>
                <button type="button" id="addOrg" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add User
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="userTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Organization Add/Edit Modal --}}
    <div class="modal fade" id="userModel" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="userModelContent"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
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

    {{-- Status Confirm Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to change user status?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatus">Yes, Change</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('main-scripts')
    <script>
        var userTable;

        $(document).ready(function() {

            // ── CSRF setup ────────────────────────────────────────────────
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────
            userTable = $('#userTable').dataTable({
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
                sAjaxSource: '{{ route('user.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [0, 5, 6]
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
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'address'
                    },
                    {
                        data: 'user_status'
                    },
                    {
                        data: 'action'
                    },
                ]
            });

            // ── Helper: open modal via AJAX ───────────────────────────────
            function openOrgModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);

                req.done(function(response) {
                    $('#userModelContent').html(response);

                    // Destroy previous instance if any, then show fresh
                    var modalEl = document.getElementById('userModel');
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
            $('#addOrg').on('click', function() {
                openOrgModal('{{ route('user.form') }}', {}, 'GET');
            });

            // ── Edit ──────────────────────────────────────────────────────
            $(document).on('click', '.editOrg', function(e) {
                e.preventDefault();
                openOrgModal(
                    '{{ route('user.form') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ── Delete ────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteOrg', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;

                $.post('{{ route('user.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            userTable.fnDraw(); // ✅ old-style API
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
            document.getElementById('userModel').addEventListener('hidden.bs.modal', function() {
                $('#userModelContent').html('');
            });

            // ── Image preview (delegated - works on AJAX loaded content) ──
            $(document).on('change', '#image', function() {
                var file = this.files[0];
                if (file) $('#img_preview').attr('src', URL.createObjectURL(file));
            });

            // ── Form submit (delegated - works on AJAX loaded content) ────
            $(document).on('submit', '#orgForm', function(e) {
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
                            userTable.fnDraw(); // ✅ old-style API

                            // Close modal
                            var modalEl = document.getElementById('userModel');
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
            $(document).on('input change', '#orgForm .form-control', function() {
                $(this).removeClass('is-invalid');
            });
            $(document).on('click', '.viewOrg', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                openOrgModal(
                    '{{ route('user.view') }}', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            var statusId = null;
            var statusValue = null;
            var currentDropdown = null;

            // ── When dropdown changes ─────────────────────────
            $(document).on('change', '.changeStatus', function() {

                statusId = $(this).data('id');
                statusValue = $(this).val();
                currentDropdown = $(this);

                // store old value (for cancel revert)
                currentDropdown.data('old', currentDropdown.find('option:selected').text());

                // show modal
                new bootstrap.Modal(document.getElementById('statusModal')).show();
            });

            $('#confirmStatus').on('click', function() {

                if (!statusId) return;

                showLoader();

                $.post('{{ route('user.status') }}', {
                    id: statusId,
                    status: statusValue,
                    _token: '{{ csrf_token() }}'
                }, function(response) {

                    hideLoader();

                    if (response.type === 'success') {
                        showNotification(response.message, 'success');
                    } else {
                        showNotification(response.message, 'error');
                    }

                }).fail(function() {
                    hideLoader();
                    showNotification('Status update failed!', 'error');
                }).always(function() {

                    statusId = null;
                    statusValue = null;

                    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                });
            });

            $('#statusModal').on('hidden.bs.modal', function() {
                if (currentDropdown && statusId === null) {
                    // revert selection
                    currentDropdown.val(currentDropdown.data('old'));
                }
            });
        });
    </script>
@endsection
