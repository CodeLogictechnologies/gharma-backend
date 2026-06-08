<style>
    #userModel,
    #deleteModal {
        z-index: 1060 !important;
    }

    .modal-backdrop {
        z-index: 1055 !important;
    }

    .layout-menu,
    .layout-navbar {
        z-index: 1040 !important;
    }
</style>

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

{{-- Add / Edit Modal --}}
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
(function ($) {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    /* ── Helper: fully clean up any open modal/backdrop ─────── */
    function cleanupModal() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    }

    /* ── DataTable ──────────────────────────────────────────── */
    if ($.fn.DataTable.isDataTable('#userTable')) {
        $('#userTable').DataTable().destroy();
    }

    userTable = $('#userTable').dataTable({
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
        sAjaxSource   : '{{ route('user.list') }}',
        oLanguage     : { sEmptyTable: "<p class='no_data_message'>No data available.</p>" },
        aoColumnDefs  : [
            { bSortable: false, aTargets: [0, 5, 6] },
            { sWidth: '10%',   aTargets: [6] }
        ],
        aoColumns: [
            { data: 'sno'     },
            { data: 'name'    },
            { data: 'email'   },
            { data: 'phone'   },
            { data: 'address' },
            { data: 'status'  },
            { data: 'action'  },
        ],
        initComplete: function () {
            this.api().columns([1, 2]).every(function () {
                var column = this;
                var header = $(column.header()).text().trim();
                $('<input type="text" class="form-control" placeholder="Search ' + header + '..." style="width:100%;" />')
                    .appendTo($(column.header()).empty())
                    .on('keyup change', function () {
                        column.search(this.value).draw();
                    });
            });
        }
    });

    /* ── Helper: open modal via AJAX ────────────────────────── */
    function openUserModal(url, data, method) {
        var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);

        req.done(function (response) {
            $('#userModelContent').html(response);

            $('#userModelContent').find('script').each(function () {
                $.globalEval($(this).text());
            });

            var modalEl  = document.getElementById('userModel');
            var existing = bootstrap.Modal.getInstance(modalEl);
            if (existing) existing.dispose();
            $(modalEl).appendTo('body');

            new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false }).show();

        }).fail(function () {
            showNotification('Failed to load form. Please try again.', 'error');
        });
    }

    /* ── Add ─────────────────────────────────────────────────── */
    $('#addOrg').on('click', function () {
        openUserModal('{{ route('user.form') }}', {}, 'GET');
    });

    /* ── Edit ────────────────────────────────────────────────── */
    $(document).on('click', '.editOrg', function (e) {
        e.preventDefault();
        openUserModal('{{ route('user.form') }}', {
            id     : $(this).data('id'),
            _token : '{{ csrf_token() }}'
        }, 'POST');
    });

    /* ── View ────────────────────────────────────────────────── */
    $(document).on('click', '.viewOrg', function (e) {
        e.preventDefault();
        openUserModal('{{ route('user.view') }}', {
            id     : $(this).data('id'),
            _token : '{{ csrf_token() }}'
        }, 'POST');
    });

    /* ── Delete ──────────────────────────────────────────────── */
    var deleteId = null;

    $(document).on('click', '.deleteOrg', function (e) {
        e.preventDefault();
        deleteId    = $(this).data('id');
        var modalEl = document.getElementById('deleteModal');
        $(modalEl).appendTo('body');
        new bootstrap.Modal(modalEl).show();
    });

    $(document).on('click', '#confirmDelete', function () {
        if (!deleteId) return;

        var $btn     = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Deleting...');

        $.post('{{ route('user.delete') }}', {
            id     : deleteId,
            _token : '{{ csrf_token() }}'
        })
        .done(function (response) {
            var result = (typeof response === 'string') ? JSON.parse(response) : response;

            // ✅ Step 1: Hide delete modal first
            var deleteModalEl  = document.getElementById('deleteModal');
            var deleteInstance = bootstrap.Modal.getInstance(deleteModalEl);
            if (deleteInstance) deleteInstance.hide();

            // ✅ Step 2: Show notification + redraw table after backdrop is gone
            setTimeout(function () {
                cleanupModal();

                if (result.type === 'success') {
                    showNotification(result.message, 'success');
                    if (typeof userTable !== 'undefined' && userTable) {
                        userTable.fnDraw();
                    }
                } else {
                    showNotification(result.message, 'error');
                }
            }, 350);
        })
        .fail(function () {
            // ✅ Also clean up on failure
            var deleteModalEl  = document.getElementById('deleteModal');
            var deleteInstance = bootstrap.Modal.getInstance(deleteModalEl);
            if (deleteInstance) deleteInstance.hide();

            setTimeout(function () {
                cleanupModal();
                showNotification('Delete failed. Please try again.', 'error');
            }, 350);
        })
        .always(function () {
            deleteId = null;
            $btn.prop('disabled', false).html('Yes, Delete');
        });
    });

    /* ── Clean up userModel on close ────────────────────────── */
    document.getElementById('userModel').addEventListener('hidden.bs.modal', function () {
        $('#userModelContent').html('');
        cleanupModal();
    });

    /* ── Clean up deleteModal on close ──────────────────────── */
    document.getElementById('deleteModal').addEventListener('hidden.bs.modal', function () {
        cleanupModal();
    });

    /* ── Image preview ───────────────────────────────────────── */
    $(document).on('change', '#image', function () {
        var file = this.files[0];
        if (file) $('#img_preview').attr('src', URL.createObjectURL(file));
    });

})(jQuery);
</script>