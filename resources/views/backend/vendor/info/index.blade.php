@extends('layouts.main')
@section('title', 'Vendor')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Vendor List</h5>
                <button type="button" id="addVendor" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Vendor
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="vendorTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Vendor Name</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            <th>Company Name</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Vendor Add/Edit Modal --}}
    <div class="modal fade" id="organizationModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="venforModel"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Vendor</h5>
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
        var vendorTable;

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────
            vendorTable = $('#vendorTable').dataTable({
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
                sAjaxSource: '{{ route('vendor.info.list') }}',
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
                        data: 'phone'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'company_name'
                    },
                    {
                        data: 'address'
                    },
                    {
                        data: 'action'
                    },
                ],
                
                initComplete: function() {
                    this.api().columns([1, 2, 3]).every(function() {
                        var column = this;
                        var header = $(column.header()).text()
                            .trim();

                        var input = $(
                                '<input type="text" class="form-control" placeholder="' +
                                header + '..." style="width:100%;" />'
                            )
                            .appendTo($(column.header()).empty())
                            .on('keyup change', function() {
                                column.search(this.value).draw();
                            });
                    });
                }
            });

            // ── Helper: open modal via AJAX ───────────────────────────────
            function openVendorModal(url, data, method) {
                var req = (method === 'POST') ? $.post(url, data) : $.get(url, data);
                req.done(function(response) {
                    $('#venforModel').html(response);
                    var modalEl = document.getElementById('organizationModal');
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
            $('#addVendor').on('click', function() {
                openVendorModal('{{ route('vendor.info.form') }}', {}, 'GET');
            });

            // ── Edit ──────────────────────────────────────────────────────
            $(document).on('click', '.editVendor', function(e) {
                e.preventDefault();
                openVendorModal(
                    '{{ route('vendor.info.form') }}', // ✅ fixed route
                    {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ── Delete ────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteVendor', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('vendor.info.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            vendorTable.fnDraw();
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

            // ── Clear modal on close ──────────────────────────────────────
            document.getElementById('organizationModal').addEventListener('hidden.bs.modal', function() {
                $('#venforModel').html('');
            });



            // ── Form Submit with full validation ──────────────────────────
            $(document).on('submit', '#vendorForm', function(e) {
                e.preventDefault();

                var valid = true;
                var $form = $(this);

                // ✅ Clear previous errors
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').hide();

                // ✅ Required field check
                $form.find('[data-required]').each(function() {
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        $(this).siblings('.invalid-feedback').show();
                        valid = false;
                    }
                });

                // ✅ Email format check
                var $email = $form.find('[name="email"]');
                if ($email.val().trim()) {
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test($email.val().trim())) {
                        $email.addClass('is-invalid');
                        $email.siblings('.invalid-feedback').text('Please enter a valid email.').show();
                        valid = false;
                    }
                }

                // ✅ Phone format check (numbers only, 7-15 digits)
                var $phone = $form.find('[name="phone"]');
                if ($phone.val().trim()) {
                    var phoneRegex = /^[0-9\-\+\s]{7,15}$/;
                    if (!phoneRegex.test($phone.val().trim())) {
                        $phone.addClass('is-invalid');
                        $phone.siblings('.invalid-feedback').text('Please enter a valid phone number.')
                            .show();
                        valid = false;
                    }
                }

                if (!valid) return;

                // ✅ Fix button label based on add/edit
                var isEdit = $form.find('[name="id"]').val() !== '';
                var $btn = $form.find('[type=submit]');
                var origText = isEdit ? 'Update' : 'Save';

                $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> Saving...');
                showLoader();

                $.ajax({
                    url: $form.attr('action'),
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
                            vendorTable.fnDraw();
                            var modalEl = document.getElementById('organizationModal');
                            bootstrap.Modal.getInstance(modalEl).hide();
                        } else {
                            showNotification(result.message, 'error');
                            $btn.prop('disabled', false).html(
                                '<i class="bx ' + (isEdit ? 'bx-save' : 'bx-plus') +
                                ' me-1"></i> ' + origText
                            );
                        }
                    },
                    error: function(xhr) {
                        hideLoader();
                        $btn.prop('disabled', false).html(
                            '<i class="bx ' + (isEdit ? 'bx-save' : 'bx-plus') +
                            ' me-1"></i> ' + origText
                        );
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                var $field = $form.find('[name="' + field + '"]');
                                $field.addClass('is-invalid');
                                $field.siblings('.invalid-feedback').text(messages[0])
                                    .show();
                            });
                        } else {
                            showNotification('Something went wrong!', 'error');
                        }
                    }
                });
            });

            // ✅ Clear invalid on input
            $(document).on('input change', '#vendorForm .form-control', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            });

            // ── View ──────────────────────────────────────────────────────
            $(document).on('click', '.viewVendor', function(e) {
                e.preventDefault();
                openVendorModal(
                    '{{ route('vendor.info.view') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });
        });
    </script>
@endsection
