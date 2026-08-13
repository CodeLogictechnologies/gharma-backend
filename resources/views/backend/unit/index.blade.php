@extends('layouts.main')
@section('title', 'Unit')
@section('content')
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Unit</h5>
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
    <div class="row g-4">
        @canany(['add.unit', 'edit.unit'])
        <div class="col-12 col-lg-4">
            <div class="card p-4">
                <h5 class="mb-3" id="formTitle">Add Unit</h5>
                <form action="{{ route('unit.save') }}" method="POST" id="unitForm">
                    @csrf
                    <input type="hidden" name="id" id="unitId" value="">

                    <div class="mb-3">
                        <label class="form-label" for="name">
                            Unit Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="name" id="unitname"
                            placeholder="e.g. Size, Color, Weight" />
                    </div>

                    <button type="button" class="btn btn-primary saveUnit">Save</button>
                    <button type="button" class="btn btn-outline-secondary ms-2 d-none" id="cancelEdit">Cancel</button>
                </form>
            </div>
        </div>
        @endcan


        <div class="col-12 col-lg-8">
            <div class="card p-4">
                <div class="table-responsive">
                    <table class="table" id="unitTable" aria-describedby="unitTable_info">
                        <thead class="table-light">
                            <tr>
                                <th>S.No</th>
                                <th>Unit Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('main-scripts')
<script>
    var unitTable;

    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ── DataTable ────────────────────────────────────────────────
        unitTable = $('#unitTable').dataTable({
            sPaginationType: 'full_numbers',
            bSearchable: false,
            language: {
                paginate: {
                    first: '<i class="bx bx-chevrons-left"></i>',
                    previous: '<i class="bx bx-chevron-left"></i>',
                    next: '<i class="bx bx-chevron-right"></i>',
                    last: '<i class="bx bx-chevrons-right"></i>',
                }
            },
            lengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, 'All']
            ],
            iDisplayLength: 10,
            sDom: 'ltipr',
            bAutoWidth: false,
            aaSorting: [
                [0, 'desc']
            ],
            bProcessing: true,
            bServerSide: true,
            ajax: {
                url: '{{ route('
                unit.list ') }}',
                type: 'POST',
            },
            oLanguage: {
                sEmptyTable: "<p class='no_data_message'>No unit found.</p>"
            },
            aoColumnDefs: [{
                bSortable: false,
                aTargets: [2]
            }],
            aoColumns: [{
                    data: 'sno'
                },
                {
                    data: 'name'
                },
                {
                    data: 'action'
                },
            ],
            initComplete: function() {
                this.api().columns([1]).every(function() {
                    var column = this;
                    var header = $(column.header()).text().trim();
                    $('<input type="text" class="form-control" placeholder="' + header +
                            '..." style="width:100%;" />')
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            column.search(this.value).draw();
                        });
                });
            }
        });

        // ── Validate ──────────────────────────────────────────────────
        $('#unitForm').validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2
                }
            },
            messages: {
                name: {
                    required: 'Please enter unit name.',
                    minlength: 'Minimum 2 characters.'
                }
            },
            highlight: function(el) {
                $(el).addClass('border-danger');
            },
            unhighlight: function(el) {
                $(el).removeClass('border-danger');
            },
        });

        // Prevent Enter-key from doing a native full-page POST
        $('#unitForm').on('submit', function(e) {
            e.preventDefault();
            $('.saveUnit').trigger('click');
        });

        // ── Save ──────────────────────────────────────────────────────
        $(document).on('click', '.saveUnit', function() {
            showLoader();

            if ($('#unitForm').valid()) {
                $.ajax({
                    url: '{{ route('
                    unit.save ') }}',
                    type: 'POST',
                    data: $('#unitForm').serialize(),
                    success: function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) :
                            response;

                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            unitTable.fnDraw();
                            resetForm();
                        } else {
                            showNotification(result.message, 'error');
                        }
                        hideLoader();
                    },
                    error: function() {
                        showNotification('Something went wrong!', 'error');
                        hideLoader();
                    }
                });
            } else {
                hideLoader();
            }
        });

        // ── Edit ──────────────────────────────────────────────────────
        $(document).on('click', '.editUnit', function(e) {
            e.preventDefault();
            $('#unitId').val($(this).data('id'));
            $('#unitname').val($(this).data('name'));
            $('#formTitle').text('Edit Unit');
            $('.saveUnit').text('Update');
            $('#cancelEdit').removeClass('d-none');
        });

        // ── Cancel edit ───────────────────────────────────────────────
        $('#cancelEdit').on('click', function() {
            resetForm();
        });

        // ── Delete ────────────────────────────────────────────────────
        var deleteId = null;

        $(document).on('click', '.deleteUnit', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });

        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;
            showLoader();

            $.post('{{ route('
                    unit.delete ') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                .done(function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        unitTable.fnDraw();
                    } else {
                        showNotification(result.message, 'error');
                    }
                    hideLoader();
                })
                .fail(function() {
                    showNotification('Delete failed. Please try again.', 'error');
                    hideLoader();
                })
                .always(function() {
                    deleteId = null;
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                });
        });

        function resetForm() {
            $('#unitForm')[0].reset();
            $('#unitId').val('');
            $('#formTitle').text('Add Variation Attribute');
            $('.saveUnit').text('Save');
            $('#cancelEdit').addClass('d-none');
        }
    });
</script>
@endsection