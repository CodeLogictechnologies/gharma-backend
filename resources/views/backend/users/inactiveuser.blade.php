<style>
    .status { width: 20% !important; }
</style>

<div class="table-responsive text-nowrap mx-4 mb-4">
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
    // ✅ NO $(document).ready() wrapper
    // ✅ NO status listeners here — all in main blade
    // ✅ Destroy first to prevent reinit warning

    if ($.fn.DataTable.isDataTable('#userTable')) {
        $('#userTable').DataTable().destroy();
    }

    userTable = $('#userTable').dataTable({
        sPaginationType: 'full_numbers',
        bSearchable: false,
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
            { bSortable: false, aTargets: [0, 5, 6] },
            { sWidth: '10%', aTargets: [5] }
        ],
        aoColumns: [
            { data: 'sno' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'address' },
            { data: 'type' },
            { data: 'user_status' },
            { data: 'action' },
        ],
        initComplete: function() {
            this.api().columns([1, 2]).every(function() {
                var column = this;
                var header = $(column.header()).text().trim();
                $('<input type="text" class="form-control" placeholder="Search ' + header + '..." style="width:100%;" />')
                    .appendTo($(column.header()).empty())
                    .on('keyup change', function() { column.search(this.value).draw(); });
            });
        }
    });

    // Delete
    var deleteId = null;

    $(document).off('click', '.deleteOrg').on('click', '.deleteOrg', function(e) {
        e.preventDefault();
        deleteId = $(this).data('id');
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });

    $(document).off('click', '#confirmDelete').on('click', '#confirmDelete', function() {
        if (!deleteId) return;
        $.post('{{ route('user.delete') }}', { id: deleteId, _token: '{{ csrf_token() }}' })
            .done(function(response) {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.type === 'success') {
                    showNotification(result.message, 'success');
                    userTable.fnDraw();
                } else {
                    showNotification(result.message, 'error');
                }
            })
            .fail(function() { showNotification('Delete failed. Please try again.', 'error'); })
            .always(function() {
                deleteId = null;
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            });
    });
</script>