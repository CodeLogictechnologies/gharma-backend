@extends('layouts.main')
@section('title', 'Assign Drive')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Stat cards --}}
        <div class="row mb-2">
            <div class="col-6 col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-label-warning rounded-circle p-2"><i class="bx bx-time-five"></i></span>
                            <span class="text-muted">Unassigned</span>
                        </div>
                        <h4 class="mb-0" id="statUnassigned">{{ $stats['unassigned'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-label-success rounded-circle p-2"><i class="bx bx-check-double"></i></span>
                            <span class="text-muted">Assigned Today</span>
                        </div>
                        <h4 class="mb-0" id="statAssignedToday">{{ $stats['assigned_today'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-label-info rounded-circle p-2"><i class="bx bx-navigation"></i></span>
                            <span class="text-muted">In Transit</span>
                        </div>
                        <h4 class="mb-0" id="statInTransit">{{ $stats['in_transit'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-label-primary rounded-circle p-2"><i class="bx bx-car"></i></span>
                            <span class="text-muted">Available Drivers</span>
                        </div>
                        <h4 class="mb-0">{{ $drivers->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-pills mb-0" id="assignTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:;" data-tab="unassigned">Unassigned</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:;" data-tab="assigned">Assigned</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:;" data-tab="all">All</a>
                    </li>
                </ul>
            </div>

            <div class="d-flex flex-wrap align-items-end gap-3 px-4 pb-3 border-bottom">
                <div>
                    <label class="form-label small text-muted mb-1" for="filterDriver">Driver</label>
                    <select id="filterDriver" class="form-select form-select-sm" style="min-width: 200px;">
                        <option value="">All Drivers</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted mb-1" for="filterDate">Assign Date (B.S.)</label>
                    <input type="text" id="filterDate" class="form-control form-control-sm" autocomplete="off"
                        placeholder="yyyy-mm-dd" style="min-width: 170px;">
                </div>
                <button type="button" id="clearFilters" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-x"></i> Clear Filters
                </button>
            </div>

            {{-- Bulk-assign bar --}}
            <div class="alert alert-primary d-none align-items-center justify-content-between mx-4 mt-3 mb-0 py-2"
                id="bulkBar">
                <span><strong id="bulkCount">0</strong> order(s) selected</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="bulkClear">Clear</button>
                    <button type="button" class="btn btn-sm btn-primary" id="bulkAssignBtn">
                        <i class="bx bx-user-plus me-1"></i> Assign Selected
                    </button>
                </div>
            </div>

            <div class="table-responsive text-nowrap mx-4 mb-4 mt-3">
                <table class="table" id="assignTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th style="width: 3%;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                            <th>Customer</th>
                            <th>Delivery Address</th>
                            <th>Order Time</th>
                            <th>Order Status</th>
                            <th>Driver</th>
                            <th>Assign Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Single assign/reassign modal (AJAX-loaded) --}}
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" id="assignModalContainer"></div>
        </div>
    </div>

    {{-- Bulk assign modal --}}
    <div class="modal fade" id="bulkAssignModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Selected Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkAssignForm">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3"><strong id="bulkModalCount">0</strong> order(s) will be assigned to
                            the selected driver and date.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="bulkDriverSelect">Select Driver</label>
                                <select name="driver_id" id="bulkDriverSelect" class="form-select" data-required>
                                    <option value="">-- Select Driver --</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="bulkDateInput">Assign Date (B.S.)</label>
                                <input type="text" name="delivery_date" id="bulkDateInput" class="form-control"
                                    autocomplete="off" data-required value="{{ $todayNep }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-user-plus me-1"></i> Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('main-scripts')
    <script>
        var assignTable;
        var currentTab = 'unassigned';
        var selectedOrders = new Set();

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#filterDate').nepaliDatePicker();
            $('#bulkDateInput').nepaliDatePicker({
                container: '#bulkAssignModal'
            });

            assignTable = $('#assignTable').dataTable({
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
                bProcessing: true,
                bServerSide: true,
                sAjaxSource: '{{ route('assign.driver.list') }}',
                fnServerParams: function(aoData) {
                    aoData.push({
                        name: 'tab',
                        value: currentTab
                    });
                    aoData.push({
                        name: 'driver_id',
                        value: $('#filterDriver').val()
                    });
                    aoData.push({
                        name: 'delivery_date',
                        value: $('#filterDate').val()
                    });
                    aoData.push({
                        name: 'sSearch_1',
                        value: $('#assignTable thead th:eq(1) input').val() || ''
                    });
                },
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 7]
                }],
                aoColumns: [{
                        data: 'checkbox'
                    },
                    {
                        data: 'customer_name'
                    },
                    {
                        data: 'address_name'
                    },
                    {
                        data: 'order_time'
                    },
                    {
                        data: 'order_status'
                    },
                    {
                        data: 'driver_name'
                    },
                    {
                        data: 'delivery_date'
                    },
                    {
                        data: 'action'
                    },
                ],
                fnDrawCallback: function() {
                    $('#assignTable .rowCheckbox').each(function() {
                        $(this).prop('checked', selectedOrders.has($(this).val()));
                    });
                    syncSelectAllState();
                },
                initComplete: function() {
                    this.api().columns([1]).every(function() {
                        var column = this;
                        var header = $(column.header()).text().trim();
                        $('<input type="text" class="form-control form-control-sm" placeholder="Search ' +
                                header + '..." style="width:100%;" />')
                            .appendTo($(column.header()).empty())
                            .on('keyup change', function() {
                                assignTable.fnDraw();
                            });
                    });
                }
            });

            // ── Tabs ──────────────────────────────────────────────────────
            $(document).on('click', '#assignTabs .nav-link', function() {
                $('#assignTabs .nav-link').removeClass('active');
                $(this).addClass('active');
                currentTab = $(this).data('tab');
                assignTable.fnDraw();
            });

            // ── Filters ───────────────────────────────────────────────────
            $('#filterDriver, #filterDate').on('change', function() {
                assignTable.fnDraw();
            });

            $('#clearFilters').on('click', function() {
                $('#filterDriver').val('');
                $('#filterDate').val('');
                $('#assignTable thead th:eq(1) input').val('');
                assignTable.fnDraw();
            });

            // ── Row selection ─────────────────────────────────────────────
            $(document).on('change', '.rowCheckbox', function() {
                var id = $(this).val();
                if (this.checked) {
                    selectedOrders.add(id);
                } else {
                    selectedOrders.delete(id);
                }
                syncSelectAllState();
                updateBulkBar();
            });

            $('#selectAll').on('change', function() {
                var checked = this.checked;
                $('#assignTable .rowCheckbox').each(function() {
                    $(this).prop('checked', checked);
                    if (checked) {
                        selectedOrders.add($(this).val());
                    } else {
                        selectedOrders.delete($(this).val());
                    }
                });
                updateBulkBar();
            });

            function syncSelectAllState() {
                var boxes = $('#assignTable .rowCheckbox');
                var checkedBoxes = boxes.filter(':checked');
                $('#selectAll').prop('checked', boxes.length > 0 && boxes.length === checkedBoxes.length);
            }

            function updateBulkBar() {
                var count = selectedOrders.size;
                $('#bulkCount, #bulkModalCount').text(count);
                $('#bulkBar').toggleClass('d-none', count === 0).toggleClass('d-flex', count > 0);
            }

            $('#bulkClear').on('click', function() {
                selectedOrders.clear();
                $('.rowCheckbox, #selectAll').prop('checked', false);
                updateBulkBar();
            });

            // ── Single assign modal ──────────────────────────────────────
            function openAssignModal(orderId) {
                $.get('{{ route('assign.driver.modal') }}', {
                        id: orderId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        $('#assignModalContainer').html(response);
                        $('#deliveryDateInput').nepaliDatePicker({
                            container: '#assignModal'
                        });
                        var modalEl = document.getElementById('assignModal');
                        var existing = bootstrap.Modal.getInstance(modalEl);
                        if (existing) existing.dispose();
                        new bootstrap.Modal(modalEl, {
                            backdrop: 'static',
                            keyboard: false
                        }).show();
                    })
                    .fail(function() {
                        showNotification('Failed to load form. Please try again.', 'error');
                    });
            }

            $(document).on('click', '.assignRow', function(e) {
                e.preventDefault();
                openAssignModal($(this).data('id'));
            });

            document.getElementById('assignModal').addEventListener('hidden.bs.modal', function() {
                $('#assignModalContainer').html('');
            });

            $(document).on('submit', '#assignDriverForm', function(e) {
                e.preventDefault();

                var valid = true;
                $(this).find('[data-required]').each(function() {
                    $(this).removeClass('is-invalid');
                    if (!$(this).val() || !$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        valid = false;
                    }
                });
                if (!valid) return;

                var $btn = $(this).find('[type=submit]');
                $btn.prop('disabled', true);
                showLoader();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        hideLoader();
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
                            assignTable.fnDraw();
                        } else {
                            showNotification(result.message, 'error');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        hideLoader();
                        $btn.prop('disabled', false);
                        showNotification('Something went wrong!', 'error');
                    }
                });
            });

            // ── Bulk assign modal ────────────────────────────────────────
            $('#bulkAssignBtn').on('click', function() {
                if (selectedOrders.size === 0) return;
                $('#bulkModalCount').text(selectedOrders.size);
                loadWorkload($('#bulkDateInput').val(), '#bulkDriverSelect');
                new bootstrap.Modal(document.getElementById('bulkAssignModal')).show();
            });

            $('#bulkDateInput').on('change', function() {
                loadWorkload($(this).val(), '#bulkDriverSelect');
            });

            function loadWorkload(date, selectId) {
                if (!date) return;
                $.get('{{ route('assign.driver.workload') }}', {
                    date: date
                }).done(function(counts) {
                    $(selectId + ' option').each(function() {
                        var id = $(this).val();
                        if (!id) return;
                        var base = $(this).text().replace(/\s*\(\d+ order.*\)$/, '');
                        var count = counts[id] || 0;
                        $(this).text(base + (count > 0 ? ' (' + count + ' order' + (count === 1 ? '' :
                            's') + ')' : ''));
                    });
                });
            }

            $(document).on('submit', '#bulkAssignForm', function(e) {
                e.preventDefault();

                var valid = true;
                $(this).find('[data-required]').each(function() {
                    $(this).removeClass('is-invalid');
                    if (!$(this).val() || !$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        valid = false;
                    }
                });
                if (!valid || selectedOrders.size === 0) return;

                var $btn = $(this).find('[type=submit]');
                $btn.prop('disabled', true);
                showLoader();

                var payload = {
                    _token: '{{ csrf_token() }}',
                    driver_id: $('#bulkDriverSelect').val(),
                    delivery_date: $('#bulkDateInput').val(),
                    ordermasterids: Array.from(selectedOrders)
                };

                $.post('{{ route('assign.driver.bulk-save') }}', payload)
                    .done(function(response) {
                        hideLoader();
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById('bulkAssignModal')).hide();
                            selectedOrders.clear();
                            updateBulkBar();
                            assignTable.fnDraw();
                        } else {
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        showNotification('Bulk assign failed. Please try again.', 'error');
                    })
                    .always(function() {
                        hideLoader();
                        $btn.prop('disabled', false);
                    });
            });

            $(document).on('input change', '#bulkAssignForm .form-control, #bulkAssignForm .form-select', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endsection
