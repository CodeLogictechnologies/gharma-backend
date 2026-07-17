@extends('layouts.main')
@section('title', 'Fiscal Year')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Fiscal Years</h5>
                <button type="button" class="btn btn-primary btn-add-fiscalyear">
                    <i class="bx bx-plus"></i> Add Fiscal Year
                </button>
            </div>

            <div class="mx-4 mb-4">
                <table class="table" id="fiscalYear">
                    <thead class="table-light">
                        <tr>
                            <th>SN</th>
                            <th>Code</th>
                            <th>Start Date (BS)</th>
                            <th>End Date (BS)</th>
                            <th>Current</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="fiscalyearModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" id="fiscalyearModalContent">
                <!-- form loaded here via ajax -->
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Fiscal Year</h5>
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
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────────────────
            window.fiscalYear = $('#fiscalYear').dataTable({
                sPaginationType: 'full_numbers',
                bSort: false,
                language: {
                    paginate: {
                        first: '<i class="bx bx-chevrons-left"></i>',
                        previous: '<i class="bx bx-chevron-left"></i>',
                        next: '<i class="bx bx-chevron-right"></i>',
                        last: '<i class="bx bx-chevrons-right"></i>'
                    }
                },
                lengthMenu: [
                    [10, 30, 50, -1],
                    [10, 30, 50, 'All']
                ],
                iDisplayLength: 10,
                sDom: 'ltipr',
                bProcessing: true,
                bServerSide: true,
                sAjaxSource: '{{ route('fiscalyear.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumns: [{
                        data: 'sno'
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date',
                        bSortable: false
                    },
                    {
                        data: 'is_current'
                    },
                    {
                        data: 'action',
                        bSortable: false
                    },
                ],
            });

            // ── Fiscal year code auto-generation ─────────────────────────────────────
            function computeFiscalYearCode(startDate, endDate) {
                if (!startDate || !endDate) return '';
                var startYear = startDate.split('-')[0];
                var endYear = endDate.split('-')[0];
                if (!startYear || !endYear) return '';
                return startYear + '-' + endYear.slice(-2);
            }

            function updateCodePreview() {
                var code = computeFiscalYearCode($('#start_date_np').val(), $('#end_date_np').val());
                $('#code_preview').val(code);
                $('#code_hidden').val(code);
            }

            function startCodeWatcher() {
                updateCodePreview();
                $(document).on('change.codeWatcher', '#start_date_np, #end_date_np', updateCodePreview);
            }

            function stopCodeWatcher() {
                $(document).off('change.codeWatcher');
            }

            // ── Open modal helper ──────────────────────────────────────────────────────
            function openModal(url, params, method) {
                var req = method === 'POST' ? $.post(url, params) : $.get(url, params);
                req.done(function(response) {
                    $('#fiscalyearModalContent').html(response);

                    $('#start_date_np').nepaliDatePicker({
                        container: "#fiscalyearModal"
                    });

                    $('#end_date_np').nepaliDatePicker({
                        container: "#fiscalyearModal"
                    });

                    startCodeWatcher();

                    var modalEl = document.getElementById('fiscalyearModal');
                    var existing = bootstrap.Modal.getInstance(modalEl);
                    if (existing) existing.dispose();
                    new bootstrap.Modal(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    }).show();
                }).fail(function() {
                    showNotification('Failed to load form.', 'error');
                });
            }

            // ── Add ────────────────────────────────────────────────────────────────────
            $('.btn-add-fiscalyear').on('click', function() {
                openModal('{{ route('fiscalyear.form') }}', {}, 'GET');
            });

            // ── Edit ───────────────────────────────────────────────────────────────────
            $(document).on('click', '.editfiscalyear', function() {
                openModal('{{ route('fiscalyear.form') }}', {
                    id: $(this).data('id'),
                    _token: '{{ csrf_token() }}'
                }, 'POST');
            });

            // ── Delete ─────────────────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deletefiscalyear', function() {
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('fiscalyear.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(res) {
                        var result = typeof res === 'string' ? JSON.parse(res) : res;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            window.fiscalYear.fnDraw();
                        } else {
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        showNotification('Delete failed.', 'error');
                    })
                    .always(function() {
                        deleteId = null;
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    });
            });

            // ── Save (Add/Edit) ───────────────────────────────────────────────────────
            $(document).on('submit', '#fiscalyearForm', function(e) {
                e.preventDefault();

                $.post("{{ route('fiscalyear.save') }}", $(this).serialize())
                    .done(function(res) {
                        if (res.type === 'success') {
                            showNotification(res.message, 'success');
                            $('#fiscalyearModal').modal('hide');
                            window.fiscalYear.fnDraw();
                        } else {
                            showNotification(res.message, 'error');
                        }
                    })
                    .fail(function(xhr) {

                        let message = 'Save failed.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        showNotification(message, 'error');
                    });
            });

            $('#fiscalyearModal').on('hidden.bs.modal', function() {
                stopCodeWatcher();
            });

        });
    </script>
@endsection
