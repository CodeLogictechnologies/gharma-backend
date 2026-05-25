<style>
    .status {
        width: 20% !important;
    }
</style>

<div class="table-responsive text-nowrap mx-4 mb-4">

    {{-- Remark Modal --}}

    {{-- Status Confirm Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to change this user's status?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatus">Yes, Change</button>
                </div>
            </div>
        </div>
    </div>

    <table class="table" id="userTable">
        <thead class="table-light">
            <tr class="align-middle">
                <th>ID</th>
                <th width="15%">User Name</th>
                <th width="20%">Email</th>
                <th width="15%">Phone</th>
                <th width="15%">Address</th>
                <th width="15%">Type</th>
                <th class="status" style="width: 20% !important">Status</th>
                <th width="5%">Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
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
            sAjaxSource: '{{ route('inactive.user.list') }}',
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
                    data: 'type'
                },
                {
                    data: 'user_status'
                },
                {
                    data: 'action'
                },
            ],
            initComplete: function() {
                this.api().columns([1, 2]).every(function() {
                    var column = this;
                    var header = $(column.header()).text().trim();
                    $('<input type="text" class="form-control" placeholder="Search ' +
                            header + '..." style="width:100%;" />')
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            column.search(this.value).draw();
                        });
                });
            }
        });

        // ── Helper: open modal via AJAX ───────────────────────────────
        function openOrgModal(url, data, method) {
            var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
            req.done(function(response) {
                $('#userModelContent').html(response);
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
            openOrgModal('{{ route('user.form') }}', {
                id: $(this).data('id'),
                _token: '{{ csrf_token() }}'
            }, 'POST');
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
                        userTable.fnDraw();
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

        // ── Clear userModel content on close ──────────────────────────
        document.getElementById('userModel').addEventListener('hidden.bs.modal', function() {
            $('#userModelContent').html('');
        });

        // ── Image preview ─────────────────────────────────────────────
        $(document).on('change', '#image', function() {
            var file = this.files[0];
            if (file) $('#img_preview').attr('src', URL.createObjectURL(file));
        });

        var statusId = null;
        var statusValue = null;
        var $statusDropdown = null;
        var remarkSaved = false;

        // Step 1 — dropdown changed → show remark modal directly
        $(document).on('change', '.changeStatus', function() {
            $statusDropdown = $(this);

            var prevVal = $statusDropdown.data('current-val') || $statusDropdown.find('option:first')
                .val();
            $statusDropdown.data('prev-val', prevVal);

            statusId = $statusDropdown.data('id');
            statusValue = $statusDropdown.val();
            remarkSaved = false;

            $('#remarkText').val('');
            $('#remarkError').hide();
            $('#remark_user_id').val(statusId);

            bootstrap.Modal.getOrCreateInstance(document.getElementById('remarkModal')).show();
        });

        // Step 2 — Save remark → POST → close modal → reload table
        $('#saveRemark').on('click', function() {
            var remark = $('#remarkText').val().trim();
            if (!remark) {
                $('#remarkError').show();
                return;
            }
            $('#remarkError').hide();
            showLoader();

            var postUserId = statusId;
            var postStatus = statusValue;
            var postDropdown = $statusDropdown;

            $.post('{{ route('user.status') }}', {
                    user_id: postUserId, // ✅ user_id
                    status: postStatus, // ✅ status
                    remark: remark, // ✅ remark
                    _token: '{{ csrf_token() }}' // ✅ _token
                })
                .done(function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');

                        if (postDropdown) {
                            postDropdown.data('current-val', postStatus);
                        }

                        remarkSaved = true;
                        resetStatusState();

                        var remarkModalInstance = bootstrap.Modal.getInstance(document
                            .getElementById('remarkModal'));
                        if (remarkModalInstance) remarkModalInstance.hide();

                        userTable.fnDraw(); // ✅ reload DataTable

                    } else {
                        revertDropdown();
                        resetStatusState();
                        showNotification(result.message, 'error');
                    }
                })
                .fail(function() {
                    revertDropdown();
                    resetStatusState();
                    showNotification('Request failed!', 'error');
                })
                .always(function() {
                    hideLoader();
                });
        });

        // Remark modal closed without saving → revert dropdown
        $('#remarkModal').on('hidden.bs.modal', function() {
            if (statusId !== null) {
                revertDropdown();
                resetStatusState();
            }
            $('#remarkText').val('');
            $('#remarkError').hide();
        });

        // ── Helpers ───────────────────────────────────────────────────
        function revertDropdown() {
            if ($statusDropdown) {
                $statusDropdown.val($statusDropdown.data('prev-val'));
            }
        }

        function resetStatusState() {
            statusId = null;
            statusValue = null;
            $statusDropdown = null;
            remarkSaved = false;
        }
    });
</script>
