@extends('layouts.main')
@section('title', 'Organization Fiscal Year')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Assign Fiscal Year</h5>
                @can('edit.org-fiscalyear')
                <button type="button" class="btn btn-primary" id="btnSaveFy">
                    <i class="bx bx-save"></i> Update
                </button>
                @endcan
            </div>

            <div class="mx-4 mb-4">
                <table class="table table-bordered" id="orgFiscalYear">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="selectAllFy">
                                </div>
                            </th>
                            <th>SN</th>
                            <th>Code</th>
                            <th>Start Date (BS)</th>
                            <th>End Date (BS)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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

            let selectedFyId = null;

            // ── DataTable ─────────────────────────────────────────────────────────────
            window.orgFiscalYear = $('#orgFiscalYear').dataTable({
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
                sAjaxSource: '{{ route('org-fiscalyear.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumns: [
                    {
                        data: 'checkbox',
                        bSortable: false,
                        className: 'text-center'
                    },
                    { data: 'sno', className: 'text-center' },
                    { data: 'code' },
                    { data: 'start_date' },
                    { data: 'end_date', bSortable: false },
                ],
                fnDrawCallback: function() {
                    let checkedBox = $('.fy-checkbox:checked');
                    selectedFyId = checkedBox.length ? checkedBox.val() : null;
                }
            });

            // ── Single checkbox selection (radio-like behavior) ───────────────────────
            $(document).on('change', '.fy-checkbox', function() {
                let $this = $(this);

                if ($this.is(':checked')) {
                    $('.fy-checkbox').not($this).prop('checked', false);
                    selectedFyId = $this.val();
                } else {
                    selectedFyId = null;
                }
            });

            // ── Select All / Deselect All ───────────────────────────────────────────────
            $('#selectAllFy').on('change', function() {
                if ($(this).is(':checked')) {
                    let $all = $('.fy-checkbox');
                    $all.prop('checked', false);
                    let $first = $all.first();
                    $first.prop('checked', true);
                    selectedFyId = $first.val();
                } else {
                    $('.fy-checkbox').prop('checked', false);
                    selectedFyId = null;
                }
            });

            // ── Save Button ─────────────────────────────────────────────────────────────
            $('#btnSaveFy').on('click', function() {
                let $btn = $(this);
                $btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Saving...');

                $.post('{{ route('org-fiscalyear.assign') }}', {
                    fiscal_year_id: selectedFyId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(res) {
                    if (res.status === true) {
                        showNotification(res.message, 'success');
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
                })
                .always(function() {
                    $btn.prop('disabled', false).html('<i class="bx bx-save"></i> Update');
                });
            });

        });
    </script>
@endsection