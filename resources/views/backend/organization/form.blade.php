@extends('layouts.main')
@section('title', 'Organization')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Organizations List</h5>
                <button type="button" id="addOrg" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Adds Organization
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="orgTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>
                                Organization Name
                                <input type="text" class="form-control mt-1" id="filterName" placeholder="Search name..."
                                    style="height:35px;" />
                            </th>
                            <th>Phone Number</th>
                            <th>
                                Email
                                <input type="text" class="form-control mt-1" id="filterEmail"
                                    placeholder="Search email..." style="height:35px;" />
                            </th>
                            <th>
                                Address
                                <input type="text" class="form-control mt-1" id="filterAddress"
                                    placeholder="Search address..." style="height:35px;" />
                            </th>
                            <th>Logo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Organization Add/Edit Modal --}}
    <div class="modal fade" id="organizationModal" tabindex="-1" role="dialog" aria-labelledby="organizationModalLabel"
        aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="organizationModalContent">
                {{-- Loaded via AJAX --}}
            </div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Organization</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure? You won't be able to revert this.
                </div>
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
        var orgTable;

        $(document).ready(function() {

            orgTable = $('#orgTable').dataTable({
                "sPaginationType": "full_numbers",
                "bSearchable": false,
                "language": {
                    "paginate": {
                        "previous": '<i class="bx bx-chevron-left"></i>',
                        "next": '<i class="bx bx-chevron-right"></i>',
                        "first": '<i class="bx bx-chevrons-left"></i>',
                        "last": '<i class="bx bx-chevrons-right"></i>'
                    }
                },
                "lengthMenu": [
                    [10, 30, 50, 70, 90, -1],
                    [10, 30, 50, 70, 90, "All"]
                ],
                'iDisplayLength': 10,
                "sDom": 'ltipr',
                "bAutoWidth": false,
                "aaSorting": [
                    [0, 'desc']
                ],
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": '{{ route('organization.list') }}',
                "oLanguage": {
                    "sEmptyTable": "<p class='no_data_message'>No data available.</p>"
                },
                "aoColumnDefs": [{
                        "bSortable": false,
                        "aTargets": [0, ]
                    },
                    {
                        "sWidth": "18%",
                        "aTargets": [5, ]
                    }
                ],
                "columnDefs": [{
                    "targets": -1,
                    "className": 'dt-body-right'
                }],
                "aoColumns": [{
                        "data": "sno"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "phone"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "address"
                    },
                    {
                        "data": "logo"
                    },
                    {
                        "data": "action"
                    },
                ]
            }).columnFilter({
                sPlaceHolder: "head:after",
                aoColumns: [{
                        type: "null"
                    },
                    {
                        type: "text"
                    },
                    {
                        type: "null"
                    },
                    {
                        type: "text"
                    },
                    {
                        type: "text"
                    },
                    {
                        type: "null"
                    }

                ]
            });
            console.log({
                orgTable
            })
            // ─── Helper: open org modal ────────────────────────────────────
            function openOrgModal(url, data, method) {
                var request = method === 'POST' ?
                    $.post(url, data) :
                    $.get(url, data);

                request.done(function(response) {
                    $('#organizationModalContent').html(response);
                    var modal = new bootstrap.Modal(
                        document.getElementById('organizationModal'), {
                            backdrop: 'static',
                            keyboard: false
                        }
                    );
                    modal.show();
                }).fail(function() {
                    showNotification('Failed to load form. Please try again.', 'error');
                });
            }

            // ─── Add Organization ──────────────────────────────────────────
            $('#addOrg').on('click', function() {
                openOrgModal('{{ route('organization.form') }}', {}, 'GET');
            });

            // ─── Edit Organization ─────────────────────────────────────────
            $(document).on('click', '.editOrg', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                openOrgModal(
                    '{{ route('organization.form') }}', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ─── View Organization ─────────────────────────────────────────
            $(document).on('click', '.viewOrg', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                openOrgModal(
                    '{{ route('organization.view') }}', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ─── Delete Organization ───────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteOrg', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;

                $.post('{{ route('organization.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            // orgTable.ajax.reload(null, false);
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


            // ─── Clear validation state when modal closes ──────────────────
            document.getElementById('organizationModal').addEventListener('hidden.bs.modal', function() {
                $('#organizationModalContent').html('');
            });
            $(document).ready(function() {

                // ✅ CSRF setup (IMPORTANT for Laravel)
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // ✅ Image Preview
                $('#image').on('change', function(e) {
                    let file = e.target.files[0];
                    if (file) {
                        $('#img_preview').attr('src', URL.createObjectURL(file));
                    }
                });

                // ✅ Form Validation
                $('#orgForm').validate({
                    rules: {
                        name: "required",
                        email: "required",
                        phone: "required",
                        address: "required",
                        username: "required",
                    }
                });

                // ✅ AJAX SUBMIT (MAIN FIX 🚀)
                $('#orgForm').off('submit').on('submit', function(e) {
                    e.preventDefault(); // ❌ STOP reload

                    if (!$(this).valid()) return;

                    showLoader();

                    $.ajax({
                        url: $(this).attr('action'),
                        type: "POST",
                        data: new FormData(this),
                        processData: false,
                        contentType: false,

                        success: function(response) {
                            let result = typeof response === 'object' ? response : JSON
                                .parse(
                                    response);

                            if (result.type === 'success') {
                                showNotification(result.message, 'success');

                                // reload datatable
                                if (typeof orgTable !== 'undefined') {
                                    orgTable.draw();
                                }

                                // reset form
                                $('#orgForm')[0].reset();

                                // close modal
                                $('#organizationModel').modal('hide');

                            } else {
                                showNotification(result.message, 'error');
                            }

                            hideLoader();
                        },

                        error: function(xhr) {
                            hideLoader();

                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;

                                $.each(errors, function(key, value) {
                                    showNotification(value[0], 'error');
                                });
                            } else {
                                showNotification('Something went wrong!', 'error');
                            }
                        }
                    });
                });

            });
        });
    </script>
@endsection this is index page <div class="modal-header">
    <h5 class="modal-title">
        {{ isset($organization) ? 'Edit Organization' : 'Add Organization' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="orgForm" action="{{ route('organization.save') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if (isset($organization))
        <input type="hidden" name="id" value="{{ @$id }}">
    @endif

    <div class="modal-body">

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <input type="hidden" name="userid" value="{{ @$userid }}">

                <label class="form-label">Organization Name *</label>
                <input type="text" name="name" class="form-control" value="{{ @$name }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ @$phone }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ @$email }}">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="{{ @$username }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ @$address }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Image</label>
                <input type="file" name="image" id="image" class="form-control">

                <img id="img_preview"
                    src="{{ !empty($image) ? asset('storage/profiles/' . $image) : asset('no-image.jpg') }}"
                    style="width:100px;margin-top:10px;">
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

        <!-- ✅ IMPORTANT: type submit -->
        <button type="submit" class="btn btn-primary">
            {{ @$id ? 'Update' : 'Save' }}
        </button>
    </div>
</form> 
