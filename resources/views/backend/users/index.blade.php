<style>
    #userModel,
    #deleteModal,
    #statusModal {
        z-index: 1060 !important;
    }

    .modal-backdrop {
        z-index: 1055 !important;
    }

    #deleteModalInactive,
    #viewModalInactive,
    #statusModalInactive {
        z-index: 1060 !important;
    }

    .modal-backdrop {
        z-index: 1055 !important;
    }
</style>

<div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
    <h5 class="mb-0">Active User List</h5>
        @can('add.user')
    <button type="button" id="addOrg" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Add User
    </button>
    @endcan
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

{{-- Add / Edit / View Modal --}}
<div class="modal fade" id="userModel" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" id="userModelContent"></div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" id="closeDeleteModal"></button>
            </div>
            <div class="modal-body">Are you sure? You won't be able to revert this.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="cancelDelete">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- Status Change Confirm Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="btn-close" id="closeStatusModal"></button>
            </div>
            <div class="modal-body">Are you sure you want to change this user's status?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="cancelStatusChange">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmStatusChange">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* ── Remove leftover backdrops ───────────────────────────── */
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ 'padding-right': '', 'overflow': '' });

        /* ── Remove orphaned duplicate modals left over from a previous
               tab load. Keep only the first (document-order) copy of each
               id — that's always the freshly-loaded one — and dispose of
               any extras regardless of where they live in the DOM. ────── */
        ['userModel', 'deleteModal', 'statusModal'].forEach(function(id) {
            var all = document.querySelectorAll('#' + id);
            for (var i = 1; i < all.length; i++) {
                var inst = bootstrap.Modal.getInstance(all[i]);
                if (inst) inst.dispose();
                all[i].remove();
            }
        });

        /* ── Move modals to body safely ──────────────────────────── */
        var userModelEl = document.getElementById('userModel');
        var deleteModalEl = document.getElementById('deleteModal');
        var statusModalEl = document.getElementById('statusModal');

        if (userModelEl.parentElement !== document.body) $(userModelEl).appendTo('body');
        if (deleteModalEl.parentElement !== document.body) $(deleteModalEl).appendTo('body');
        if (statusModalEl.parentElement !== document.body) $(statusModalEl).appendTo('body');

        /* ── Shared helper ───────────────────────────────────────── */
        function cleanupBackdrop() {
            setTimeout(function() {
                if ($('.modal.show').length === 0) {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css({ 'padding-right': '', 'overflow': '' });
                }
            }, 300);
        }

        /* ── DataTable ───────────────────────────────────────────── */
        if ($.fn.DataTable.isDataTable('#userTable')) {
            $('#userTable').DataTable().destroy();
        }

        window.userTable = $('#userTable').dataTable({
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
                    aTargets: [6]
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
                    data: 'status'
                },
                {
                    data: 'action'
                },
            ],
            initComplete: function() {
                this.api().columns([1, 2]).every(function() {
                    var column = this;
                    var header = $(column.header()).text().trim();
                    $('<input type="text" class="form-control" placeholder="Search ' + header + '..." style="width:100%;" />')
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            column.search(this.value).draw();
                        });
                });
            }
        });

        /* ── Global cleanup ──────────────────────────────────────── */
        window._forceModalCleanup = function() {
            setTimeout(function() {
                var inst = bootstrap.Modal.getInstance(userModelEl);
                if (inst) inst.dispose();
                $(userModelEl).removeClass('show').css('display', 'none');
                $(userModelEl).attr('aria-hidden', 'true').removeAttr('aria-modal role');
                $('#userModelContent').html('');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'padding-right': '', 'overflow': '' });
            }, 350);
        };

        /* ── Open Add/Edit/View modal ────────────────────────────── */
        function openUserModal(url, data, method) {
            var existing = bootstrap.Modal.getInstance(userModelEl);

            if (existing) {
                if (!userModelEl.classList.contains('show')) {
                    // Already hidden (e.g. a prior Close click) — hide() is a
                    // no-op here and 'hidden.bs.modal' would never fire again,
                    // so dispose immediately instead of waiting for it.
                    existing.dispose();
                    loadUserModalContent(url, data, method);
                    return;
                }

                // Still visible/transitioning — wait for it to fully hide
                // first, THEN dispose and reload.
                userModelEl.addEventListener('hidden.bs.modal', function onceHidden() {
                    userModelEl.removeEventListener('hidden.bs.modal', onceHidden);
                    existing.dispose();
                    loadUserModalContent(url, data, method);
                }, {
                    once: true
                });
                existing.hide();
                return;
            }

            loadUserModalContent(url, data, method);
        }

        function loadUserModalContent(url, data, method) {
            $('#userModelContent').html(
                '<div class="d-flex justify-content-center align-items-center p-5">' +
                '<div class="spinner-border text-primary" role="status"></div></div>'
            );

            var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
            req.done(function(response) {
                // jQuery's .html() already runs embedded <script> tags when
                // the inserted markup contains one (it internally falls back
                // to .append()) — re-evaluating them here would double-run
                // form.blade.php's setup script (double select2 init, etc.).
                $('#userModelContent').html(response);
                new bootstrap.Modal(userModelEl, {
                    backdrop: 'static',
                    keyboard: false
                }).show();
            }).fail(function() {
                $('#userModelContent').html('');
                showNotification('Failed to load. Please try again.', 'error');
            });
        }

        /* ── Add ─────────────────────────────────────────────────── */
        $(document).off('click.addOrg').on('click.addOrg', '#addOrg', function() {
            openUserModal('{{ route('user.form') }}', {}, 'GET');
        });

        /* ── Edit ────────────────────────────────────────────────── */
        $(document).off('click.editOrg').on('click.editOrg', '.editOrg', function(e) {
            e.preventDefault();
            openUserModal('{{ route('user.form') }}', {
                    id: $(this).data('id'),
                    _token: '{{ csrf_token() }}'
                }, 'POST');
        });

        /* ── View ────────────────────────────────────────────────── */
        $(document).off('click.viewOrg').on('click.viewOrg', '.viewOrg', function(e) {
            e.preventDefault();
            openUserModal('{{ route('user.view') }}', {
                    id: $(this).data('id'),
                    _token: '{{ csrf_token() }}'
                }, 'POST');
        });

        /* ── userModel hidden cleanup ────────────────────────────── */
        userModelEl.addEventListener('hidden.bs.modal', function() {
            $('#userModelContent').html('');
            cleanupBackdrop();
        });

        /* ════════════════════════════════════════════════════════════
           DELETE
        ════════════════════════════════════════════════════════════ */
        var deleteId = null;

        $(document).off('click.deleteOrg').on('click.deleteOrg', '.deleteOrg', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            var inst = bootstrap.Modal.getInstance(deleteModalEl);
            if (!inst) inst = new bootstrap.Modal(deleteModalEl, {
                backdrop: 'static'
            });
            inst.show();
        });

        $(document).off('click.closeDeleteModal').on('click.closeDeleteModal', '#closeDeleteModal, #cancelDelete', function() {
            var inst = bootstrap.Modal.getInstance(deleteModalEl);
            if (inst) inst.hide();
        });

        $(document).off('click.confirmDelete').on('click.confirmDelete', '#confirmDelete', function() {
            if (!deleteId) return;

            var $btn = $(this),
                origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');

            $.post('{{ route('user.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                .done(function(response) {
                    var result = (typeof response === 'string') ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        userTable.fnDraw();
                    } else {
                        showNotification(result.message, 'error');
                    }
                })
                .fail(function() {
                    showNotification('Delete failed.', 'error');
                })
                .always(function() {
                    deleteId = null;
                    $btn.prop('disabled', false).html(origHtml);
                    var inst = bootstrap.Modal.getInstance(deleteModalEl);
                    if (inst) inst.hide();
                });
        });

        deleteModalEl.addEventListener('hidden.bs.modal', function() {
            cleanupBackdrop();
        });

        /* ════════════════════════════════════════════════════════════
           STATUS TOGGLE
        ════════════════════════════════════════════════════════════ */
        var pendingStatus = null;

        $(document).off('change.activeStatus').on('change.activeStatus', '.changeActiveStatus', function() {
            var $sel = $(this);
            pendingStatus = {
                $sel: $sel,
                id: $sel.data('id'),
                val: $sel.val(),
                prev: $sel.data('prev')
            };

            var inst = bootstrap.Modal.getInstance(statusModalEl);
            if (!inst) inst = new bootstrap.Modal(statusModalEl, {
                backdrop: 'static'
            });
            inst.show();
        });

        $(document).off('click.closeStatusModal').on('click.closeStatusModal', '#closeStatusModal, #cancelStatusChange', function() {
            var inst = bootstrap.Modal.getInstance(statusModalEl);
            if (inst) inst.hide();
        });

        $(document).off('click.confirmStatus').on('click.confirmStatus', '#confirmStatusChange', function() {
            if (!pendingStatus) return;

            var $btn = $(this),
                origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            var s = pendingStatus;
            pendingStatus = null; // clear BEFORE ajax so hidden event won't revert

            $.post('{{ route('user.status') }}', {
                        user_id: s.id,
                        status: s.val,
                        remark: 'Status changed by admin',
                        _token: '{{ csrf_token() }}'
                    })
                .done(function(response) {
                    var result = (typeof response === 'string') ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        s.$sel.data('prev', s.val);
                        userTable.fnDraw();
                        if (s.val !== 'Approve') {
                            $('[data-tabid="inactive"]').trigger('click');
                        }
                    } else {
                        s.$sel.val(s.prev);
                        showNotification(result.message, 'error');
                    }
                })
                .fail(function() {
                    s.$sel.val(s.prev);
                    showNotification('Request failed!', 'error');
                })
                .always(function() {
                    $btn.prop('disabled', false).html(origHtml);
                    var inst = bootstrap.Modal.getInstance(statusModalEl);
                    if (inst) inst.hide();
                });
        });

        statusModalEl.addEventListener('hidden.bs.modal', function() {
            if (pendingStatus) {
                pendingStatus.$sel.val(pendingStatus.prev);
                pendingStatus = null;
            }
            cleanupBackdrop();
        });

    })(jQuery);
</script>