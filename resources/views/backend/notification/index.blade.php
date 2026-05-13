@extends('layouts.main')
@section('title', 'Notice')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Notice List</h5>
                <button type="button" id="addNotice" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Notice
                </button>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4">
                <table class="table" id="noticeTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Message</th>
                            <th>To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Vendor Add/Edit Modal --}}
    <div class="modal fade" id="noticeModel" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" id="noticeMod"></div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Notice</h5>
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
        var noticeTable;

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────
            noticeTable = $('#noticeTable').dataTable({
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
                sAjaxSource: '{{ route('notification.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [0]
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
                        data: 'title'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'message'
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'action'
                    },
                ],

                initComplete: function() {
                    this.api().columns([1, 3, 4]).every(function() {
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
                    $('#noticeMod').html(response);
                    var modalEl = document.getElementById('noticeModel');
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
            $('#addNotice').on('click', function() {
                openVendorModal('{{ route('notification.form') }}', {}, 'GET');
            });

            // ── Edit ──────────────────────────────────────────────────────
            $(document).on('click', '.editNotice', function(e) {
                e.preventDefault();
                openVendorModal(
                    '{{ route('notification.form') }}', // ✅ fixed route
                    {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });

            // ── Delete ────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deleteNotice', function(e) {
                e.preventDefault();
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('notification.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            noticeTable.fnDraw();
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
            document.getElementById('noticeModel').addEventListener('hidden.bs.modal', function() {
                $('#noticeMod').html('');
            });



            // ── Form Submit with full validation ──────────────────────────
            $(document).on('submit', '#noticeForm', function(e) {
                e.preventDefault();

                var valid = true;
                var $form = $(this);

                // Clear previous errors
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').hide();

                // Required field check (works for input, select, textarea)
                $form.find('[data-required]').each(function() {
                    var val = $(this).val();
                    if (!val || !val.toString().trim()) {
                        $(this).addClass('is-invalid');
                        $(this).siblings('.invalid-feedback').show();
                        valid = false;
                    }
                });

                if (!valid) return;

                var isEdit = $form.find('[name="id"]').val() !== '';
                var $btn = $form.find('[type=submit]');
                var origText = isEdit ? 'Update' : 'Save';
                var origIcon = isEdit ? 'bx-save' : 'bx-plus';

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
                            noticeTable.fnDraw();
                            var modalEl = document.getElementById(
                                'noticeModel'); // ✅ fixed typo: noticeModel → noticeModal
                            bootstrap.Modal.getInstance(modalEl).hide();
                        } else {
                            showNotification(result.message, 'error');
                            $btn.prop('disabled', false).html('<i class="bx ' + origIcon +
                                ' me-1"></i> ' + origText);
                        }
                    },
                    error: function(xhr) {
                        hideLoader();
                        $btn.prop('disabled', false).html('<i class="bx ' + origIcon +
                            ' me-1"></i> ' + origText);

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
            $(document).on('input change', '#noticeForm .form-control', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            });

            // ── View ──────────────────────────────────────────────────────
            $(document).on('click', '.viewNotice', function(e) {
                e.preventDefault();
                openVendorModal(
                    '{{ route('notification.view') }}', {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    },
                    'POST'
                );
            });
        });
    </script>
@endsection
