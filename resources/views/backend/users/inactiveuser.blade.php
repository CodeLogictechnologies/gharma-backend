<style>
    #deleteModalInactive { z-index: 1060 !important; }
    #viewModalInactive   { z-index: 1060 !important; }
    #statusModalInactive { z-index: 1060 !important; }
    .modal-backdrop      { z-index: 1055 !important; }
</style>

<div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mb-3">
    <h5 class="mb-0">Inactive / Pending Users</h5>
</div>

<div class="table-responsive text-nowrap mx-4 mb-4">
    <table class="table" id="inactiveUserTable">
        <thead class="table-light">
            <tr class="align-middle">
                <th>ID</th>
                <th>User Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

{{-- View Modal --}}
<div class="modal fade" id="viewModalInactive" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" id="viewModalInactiveContent"></div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal fade" id="deleteModalInactive" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" id="closeDeleteInactive"></button>
            </div>
            <div class="modal-body">Are you sure? You won't be able to revert this.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="cancelDeleteInactive">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteInactive">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

{{-- Status Change Confirm Modal --}}
<div class="modal fade" id="statusModalInactive" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="btn-close" id="closeStatusInactive"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to change this user's status?</p>
                <div class="mb-3">
                    <label for="statusRemarkInactive" class="form-label">Remark <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="statusRemarkInactive" rows="3" placeholder="Enter remark..."></textarea>
                    <div class="text-danger small mt-1 d-none" id="statusRemarkError">Remark is required.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="cancelStatusInactive">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmStatusInactive">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {

    /* ── Remove leftover backdrops ───────────────────────────── */
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');

    /* ── Move modals to body safely ──────────────────────────── */
    ['viewModalInactive', 'deleteModalInactive', 'statusModalInactive'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.parentElement !== document.body) $(el).appendTo('body');
    });

    /* ── Shared helper: force-hide any modal ─────────────────── */
    function hideModal(id) {
        var el   = document.getElementById(id);
        var inst = bootstrap.Modal.getInstance(el);
        if (inst) {
            inst.hide();
        } else {
            $(el).removeClass('show').css('display', 'none');
            $(el).attr('aria-hidden', 'true').removeAttr('aria-modal role');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        }
    }

    /* ── Backdrop cleanup ────────────────────────────────────── */
    function cleanupModal() {
        setTimeout(function () {
            if ($('.modal.show').length === 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            }
        }, 400);
    }

    /* ── DataTable ───────────────────────────────────────────── */
    if ($.fn.DataTable.isDataTable('#inactiveUserTable')) {
        $('#inactiveUserTable').DataTable().destroy();
    }

    var inactiveTable = $('#inactiveUserTable').dataTable({
        sPaginationType: 'full_numbers',
        bSearchable:     false,
        language: {
            paginate: {
                first:    '<i class="bx bx-chevrons-left"></i>',
                previous: '<i class="bx bx-chevron-left"></i>',
                next:     '<i class="bx bx-chevron-right"></i>',
                last:     '<i class="bx bx-chevrons-right"></i>'
            }
        },
        lengthMenu:     [[10, 30, 50, 70, 90, -1], [10, 30, 50, 70, 90, 'All']],
        iDisplayLength: 10,
        sDom:           'ltipr',
        bAutoWidth:     false,
        aaSorting:      [[0, 'desc']],
        bProcessing:    true,
        bServerSide:    true,
        sAjaxSource:    '{{ route('inactive.user.list') }}',
        oLanguage:      { sEmptyTable: "<p class='no_data_message'>No data available.</p>" },
        aoColumnDefs: [
            { bSortable: false, aTargets: [0, 6, 7] }
        ],
        aoColumns: [
            { data: 'sno'         },
            { data: 'name'        },
            { data: 'email'       },
            { data: 'phone'       },
            { data: 'address'     },
            { data: 'type'        },
            { data: 'user_status' },
            { data: 'action'      },
        ],
        initComplete: function () {
            this.api().columns([1, 2]).every(function () {
                var column = this;
                var header = $(column.header()).text().trim();
                $('<input type="text" class="form-control" placeholder="Search ' + header + '..." style="width:100%;" />')
                    .appendTo($(column.header()).empty())
                    .on('keyup change', function () { column.search(this.value).draw(); });
            });
        }
    });

    /* ════════════════════════════════════════════════════════════
       VIEW
    ════════════════════════════════════════════════════════════ */
    $(document).off('click', '.viewOrgInactive').on('click', '.viewOrgInactive', function (e) {
        e.preventDefault();
        var id      = $(this).data('id');
        var modalEl = document.getElementById('viewModalInactive');

        var existing = bootstrap.Modal.getInstance(modalEl);
        if (existing) { existing.hide(); existing.dispose(); }

        $('#viewModalInactiveContent').html(
            '<div class="modal-body d-flex justify-content-center align-items-center p-5">' +
            '<div class="spinner-border text-primary" role="status"></div></div>'
        );
        var modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        modal.show();

        $.post('{{ route('user.view') }}', { id: id, _token: '{{ csrf_token() }}' })
            .done(function (response) {
                $('#viewModalInactiveContent').html(response);
            })
            .fail(function () {
                modal.hide();
                showNotification('Failed to load user details.', 'error');
            });
    });

    document.getElementById('viewModalInactive').addEventListener('hidden.bs.modal', function () {
        $('#viewModalInactiveContent').html('');
        cleanupModal();
    });

    /* ════════════════════════════════════════════════════════════
       DELETE
    ════════════════════════════════════════════════════════════ */
    var deleteId = null;

    /* Open */
    $(document).off('click', '.deleteOrgInactive').on('click', '.deleteOrgInactive', function (e) {
        e.preventDefault();
        deleteId     = $(this).data('id');
        var existing = bootstrap.Modal.getInstance(document.getElementById('deleteModalInactive'));
        if (existing) { existing.hide(); existing.dispose(); }
        new bootstrap.Modal(document.getElementById('deleteModalInactive'), { backdrop: 'static' }).show();
    });

    /* Close — X and Cancel */
    $(document).off('click', '#closeDeleteInactive, #cancelDeleteInactive')
        .on('click', '#closeDeleteInactive, #cancelDeleteInactive', function () {
            hideModal('deleteModalInactive');
        });

    /* Confirm */
    $(document).off('click', '#confirmDeleteInactive').on('click', '#confirmDeleteInactive', function () {
        if (!deleteId) return;

        var $btn     = $(this),
            origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');

        $.post('{{ route('user.delete') }}', { id: deleteId, _token: '{{ csrf_token() }}' })
            .done(function (response) {
                var result = (typeof response === 'string') ? JSON.parse(response) : response;
                if (result.type === 'success') {
                    showNotification(result.message, 'success');
                    inactiveTable.fnDraw();
                } else {
                    showNotification(result.message, 'error');
                }
            })
            .fail(function () {
                showNotification('Delete failed!', 'error');
            })
            .always(function () {
                deleteId = null;
                $btn.prop('disabled', false).html(origHtml);
                hideModal('deleteModalInactive');
            });
    });

    document.getElementById('deleteModalInactive').addEventListener('hidden.bs.modal', function () {
        cleanupModal();
    });

    /* ════════════════════════════════════════════════════════════
       STATUS TOGGLE
    ════════════════════════════════════════════════════════════ */
    var pendingStatusInactive = null;

    /* Open */
    $(document).off('change', '.changeStatus').on('change', '.changeStatus', function () {
        var $sel = $(this);
        pendingStatusInactive = {
            $sel:     $sel,
            id:       $sel.data('id'),
            val:      $sel.val(),
            original: $sel.data('original')
        };

        var existing = bootstrap.Modal.getInstance(document.getElementById('statusModalInactive'));
        if (existing) { existing.hide(); existing.dispose(); }
        new bootstrap.Modal(document.getElementById('statusModalInactive'), { backdrop: 'static' }).show();
    });

    /* Close — X and Cancel */
    $(document).off('click', '#closeStatusInactive, #cancelStatusInactive')
        .on('click', '#closeStatusInactive, #cancelStatusInactive', function () {
            hideModal('statusModalInactive');
        });

    /* Confirm */
    $(document).off('click', '#confirmStatusInactive').on('click', '#confirmStatusInactive', function () {
        if (!pendingStatusInactive) return;

        var remark = $('#statusRemarkInactive').val().trim();
        if (!remark) {
            $('#statusRemarkError').removeClass('d-none');
            return;
        }
        $('#statusRemarkError').addClass('d-none');

        var $btn     = $(this),
            origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        var s = pendingStatusInactive;

        $.post('{{ route('user.status') }}', {
            user_id: s.id,
            status:  s.val,
            remark:  remark,
            _token:  '{{ csrf_token() }}'
        })
        .done(function (response) {
            var result = (typeof response === 'string') ? JSON.parse(response) : response;
            if (result.type === 'success') {
                showNotification(result.message, 'success');
                s.$sel.data('original', s.val);
                inactiveTable.fnDraw();

                /* Switch to active tab if Approved */
                if (s.val === 'Approve') {
                    $('[data-tabid="active"]').trigger('click');
                }
            } else {
                s.$sel.val(s.original);
                showNotification(result.message, 'error');
            }
        })
        .fail(function () {
            s.$sel.val(s.original);
            showNotification('Request failed!', 'error');
        })
        .always(function () {
            pendingStatusInactive = null;
            $btn.prop('disabled', false).html(origHtml);
            hideModal('statusModalInactive');
        });
    });

    /* Revert select + clear remark if user dismissed without confirming */
    document.getElementById('statusModalInactive').addEventListener('hidden.bs.modal', function () {
        $('#statusRemarkInactive').val('');
        $('#statusRemarkError').addClass('d-none');
        if (pendingStatusInactive) {
            pendingStatusInactive.$sel.val(pendingStatusInactive.original);
            pendingStatusInactive = null;
        }
        cleanupModal();
    });

})(jQuery);
</script>