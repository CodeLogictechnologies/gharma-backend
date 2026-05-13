<!-- jQuery first (KEEP THIS ORDER) -->
@extends('layouts.main')
@section('title', 'Permission')
<!-- jQuery FIRST -->
<script src="/assets/vendor/libs/jquery/jquery.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- jQuery Validate -->
@section('content')

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Permission</h5>
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
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="nav-align-top mb-4">

            <div class="tab-content mt-4" id="nav-tabContent">
                <div class="row g-4">
                    <div class="col-12 col-lg-4">

                        <h5 class="mb-3">Add Role</h5>
                        <form action="{{ route('role.save') }}" method="POST" id="roleForm" enctype="multipart/form-data">

                            <div class="mb-3">
                                <input type="hidden" name="id" value="" id="id">
                                <label class="form-label" for="name">Role Name</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    placeholder="Example: user.view" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Permissions</label>

                                <div class="row">
                                    @foreach ($permissions as $perm)
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox"
                                                    name="permissions[]" value="{{ $perm->id }}"
                                                    id="perm_{{ $perm->id }}">

                                                <label class="form-check-label" for="perm_{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary savePermission">Save</button>
                        </form>

                    </div>
                    <div class="col-12 col-lg-8" style="flex: 1;">
                        <div class="table-header mb-3 d-flex justify-content-between align-items-center">
                            <div class="dt-length">
                                <label class="d-flex align-items-center gap-2">

                                </label>
                            </div>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <div id="datatable-basic_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">

                                <div class="dataTables_length" id="datatable-basic_length">

                                    <table class="table" id="roleTable" aria-describedby="datatable-basic_info">
                                        <thead class="table-light">
                                            <tr class="align-middle">
                                                <th data-dt-column="1" class="">
                                                    S.No
                                                </th>
                                                <th class="fs-6">
                                                    <input type="text" class="form-control" id="defaultFormControlInput"
                                                        placeholder="Permission name"
                                                        aria-describedby="defaultFormControlHelp" />
                                                </th>
                                                <th class="">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- delete -->
@endsection
<script>
    var roleTable;

    $(document).ready(function() {

        // ── CSRF setup ────────────────────────────────────────────────
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ── DataTable ─────────────────────────────────────────────────
        roleTable = $('#roleTable').dataTable({
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
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, 'All']
            ],

            iDisplayLength: 5,
            sDom: 'ltipr',
            bAutoWidth: false,

            aaSorting: [
                [0, 'desc']
            ],

            bProcessing: true,
            bServerSide: true,

            // ✅ KEEP ONLY THIS AJAX
            ajax: {
                url: '{{ route('role.list') }}',
                type: 'POST',
                data: function(d) {
                    d.type = $('#trashed_file').is(':checked') ? 'trashed' : 'nottrashed';
                }
            },

            oLanguage: {
                sEmptyTable: "<p class='no_data_message'>No data available.</p>"
            },

            aoColumnDefs: [{
                bSortable: false,
                aTargets: [2]
            }],

            aoColumns: [{
                    data: "sno"
                },
                {
                    data: "name"
                },
                {
                    data: "action"
                }
            ],

            // ✅ COLUMN FILTER
            initComplete: function() {
                this.api().columns([1]).every(function() {
                    var column = this;

                    var input = $(
                            '<input type="text"  class="form-control" id="defaultFormControlInput" placeholder="Search name" style="width:100%;" />'
                        )
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            column.search(this.value).draw();
                        });
                });
            }
        });


        $('#roleForm').validate({
            rules: {
                name: "required",

            },
            message: {
                name: {
                    required: "This field is required."
                },
            },
            highlight: function(element) {
                $(element).addClass("border-danger")
            },
            unhighlight: function(element) {
                $(element).removeClass("border-danger")
            },
        });

        $('.savePermission').off('click').on('click', function() {
            if ($('#roleForm').valid()) {

                let form = document.getElementById('roleForm');
                let formData = new FormData(form);

                $.ajax({
                    url: "{{ route('role.save') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        let result = typeof response === 'string' ? JSON.parse(response) :
                            response;

                        if (result.type === 'success') {
                            showNotification(result.message, 'success');

                            roleTable.fnDraw();

                            $('#roleForm')[0].reset();
                            $('#id').val('');
                            $('.savePermission').html('<i class="fa fa-save"></i> Save');
                        } else {
                            showNotification(result.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Something went wrong!', 'error');
                    }
                });
            }
        });


        $(document).on('click', '.editRole', function(e) {
            e.preventDefault();

            var id = $(this).data('id');
            var name = $(this).data('name');
            var permissions = $(this).data('permissions'); // ← jQuery auto-parses, no JSON.parse needed

            // Ensure array
            if (!Array.isArray(permissions)) {
                permissions = permissions ? Object.values(permissions) : [];
            }

            // ── Populate form ──────────────────────────────────────
            $('#id').val(id);
            $('#name').val(name);

            // Uncheck all first
            $('.permission-checkbox').prop('checked', false);

            // Check matching permissions by ID
            permissions.forEach(function(permId) {
                $('#perm_' + permId).prop('checked', true);
            });

            // ── Update button label ────────────────────────────────
            $('.savePermission').html('<i class="fa fa-save me-1"></i> Update');

            // ── Scroll to form ─────────────────────────────────────
            $('html, body').animate({
                scrollTop: $('#roleForm').offset().top - 20
            }, 300);
        });


        function resetRoleForm() {
            $('#roleForm')[0].reset(); // clear all inputs
            $('#id').val(''); // clear hidden id
            $('.permission-checkbox').prop('checked', false); // uncheck all permissions
            $('.savePermission').html('<i class="fa fa-save me-1"></i> Save'); // reset button
            $('#cancelEdit').addClass('d-none'); // hide cancel button
            $('#name').removeClass('border-danger is-invalid'); // clear validation errors
        }
        var deleteId = null;

        $(document).on('click', '.deleteRole', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });

        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            $.post('{{ route('role.delete') }}', {
                    id: deleteId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        roleTable.fnDraw();
                        resetRoleForm();

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
    });
</script>
