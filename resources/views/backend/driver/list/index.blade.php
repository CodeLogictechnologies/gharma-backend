@extends('layouts.main')
@section('title', 'Driver List')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Driver List</h5>
                <button type="button" id="addDriver" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Driver
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="drivertable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Driver</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            {{-- <th>Country</th> --}}
                            {{-- <th>City</th> --}}
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Organization Add/Edit Modal --}}
    <div class="modal fade" id="driverModel" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="driverModelContainer"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove User</h5>
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
    var drivertable;

    $(document).ready(function() {

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        drivertable = $('#drivertable').dataTable({
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
            sAjaxSource:    '{{ route('driver.list') }}',
            oLanguage:      { sEmptyTable: "<p class='no_data_message'>No data available.</p>" },
            aoColumnDefs: [
                { bSortable: false, aTargets: [0, 5] },
                { sWidth: '10%',    aTargets: [5] }
            ],
            aoColumns: [
                { data: 'sno'     },
                { data: 'name'    },
                { data: 'phone'   },
                { data: 'email'   },
                { data: 'address' },
                { data: 'action'  },
            ],

            fnServerParams: function(aoData) {
                aoData.push({ name: 'sSearch_1', value: $('#drivertable thead th:eq(1) input').val() || '' });
                aoData.push({ name: 'sSearch_2', value: $('#drivertable thead th:eq(2) input').val() || '' });
                aoData.push({ name: 'sSearch_3', value: $('#drivertable thead th:eq(3) input').val() || '' });
            },

            initComplete: function() {
                this.api().columns([1, 2, 3]).every(function() {
                    var column = this;
                    var header = $(column.header()).text().trim();
                    $('<input type="text" class="form-control" placeholder="' + header + '..." style="width:100%;" />')
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            drivertable.fnDraw();
                        });
                });
            }
        });

        $(document).on('driver:saved', function() {
            drivertable.fnDraw();
        });

        function openOrgModal(url, data, method) {
            var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
            req.done(function(response) {
                $('#driverModelContainer').html(response);
                var modalEl  = document.getElementById('driverModel');
                var existing = bootstrap.Modal.getInstance(modalEl);
                if (existing) existing.dispose();
                new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false }).show();
            }).fail(function() {
                showNotification('Failed to load form. Please try again.', 'error');
            });
        }

        $('#addDriver').on('click', function() {
            openOrgModal('{{ route('driver.form') }}', {}, 'GET');
        });

        $(document).on('click', '.editDriver', function(e) {
            e.preventDefault();
            openOrgModal('{{ route('driver.form') }}', {
                id: $(this).data('id'),
                _token: '{{ csrf_token() }}'
            }, 'POST');
        });

        var deleteId = null;

        $(document).on('click', '.deleteDriver', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });

        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;
            $.post('{{ route('driver.delete') }}', { id: deleteId, _token: '{{ csrf_token() }}' })
                .done(function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        drivertable.fnDraw();
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

        document.getElementById('driverModel').addEventListener('hidden.bs.modal', function() {
            $('#driverModelContainer').html('');
        });

        $(document).on('input change', '#storeForm .form-control', function() {
            $(this).removeClass('is-invalid');
        });
    });
</script>
@endsection
