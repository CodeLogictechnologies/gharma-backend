@extends('layouts.main')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Fiscal Years</h5>
        <button type="button" class="btn btn-primary btn-add-fiscalyear">
            <i class="bx bx-plus"></i> Add Fiscal Year
        </button>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="fiscalyearTable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Start Date (BS)</th>
                    <th>End Date (BS)</th>
                    <th>Current</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="fiscalyearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" id="fiscalyearModalContent">
            <!-- form loaded here via ajax -->
        </div>
    </div>
</div>
@endsection

@section('main-scripts')
<script>
    $(function() {
        let table = $('#fiscalyearTable').DataTable({
            processing: true,
            serverSide: true,
            dom: 'lrtip', // removes the default search box
            ajax: {
                url: "{{ route('fiscalyear.list') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                }
            },
            columns: [{
                    data: 'code'
                },
                {
                    data: 'start_date'
                },
                {
                    data: 'end_date'
                },
                {
                    data: 'is_current',
                    render: d => d === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'
                },
                {
                    data: 'status',
                    render: d => d === 'Y' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'
                },
                {
                    data: 'id',
                    render: id => `
                    <button class="btn btn-sm btn-info btn-edit-fy" data-id="${id}"><i class="bx bx-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete-fy" data-id="${id}"><i class="bx bx-trash"></i></button>
                `
                }
            ]
        });

        let codeWatcher = null;
        let lastStart = null;
        let lastEnd = null;

        function computeCode(start, end) {
            if (start && end && start.length >= 4 && end.length >= 4) {
                return start.substring(0, 4).substring(1, 4) + '/' + end.substring(0, 4).substring(1, 4);
            }
            return '';
        }

        function updateCodePreview() {
            let start = $('#start_date_np').val();
            let end = $('#end_date_np').val();

            // only touch the DOM when something actually changed
            if (start === lastStart && end === lastEnd) return;
            lastStart = start;
            lastEnd = end;

            let code = computeCode(start, end);
            if (code) {
                $('#code_preview').val(code);
            }
        }

        function startCodeWatcher() {
            stopCodeWatcher();
            lastStart = null;
            lastEnd = null;
            // nepaliDatePicker doesn't reliably fire a native 'change' event,
            // so poll while the modal is open instead of depending on it.
            codeWatcher = setInterval(updateCodePreview, 300);
        }

        function stopCodeWatcher() {
            if (codeWatcher) {
                clearInterval(codeWatcher);
                codeWatcher = null;
            }
        }

        function loadForm(id = null) {
            $.post("{{ route('fiscalyear.form') }}", {
                id: id,
                _token: "{{ csrf_token() }}"
            }, function(html) {
                $('#fiscalyearModalContent').html(html);
                $('#fiscalyearModal').modal('show');

                // init nepali calendar AFTER form is injected into DOM
                // container option is required so the calendar renders correctly inside the modal
                $('#start_date_np').nepaliDatePicker({
                    container: "#fiscalyearModal"
                });

                $('#end_date_np').nepaliDatePicker({
                    container: "#fiscalyearModal"
                });

                // also bind change/input in case the plugin does fire them,
                // plus the polling watcher as a reliable fallback
                $('#start_date_np, #end_date_np').on('change input', updateCodePreview);
                startCodeWatcher();
                updateCodePreview(); // run once immediately in case editing an existing record
            });
        }

        $('.btn-add-fiscalyear').on('click', () => loadForm());
        $(document).on('click', '.btn-edit-fy', function() {
            loadForm($(this).data('id'));
        });

        $(document).on('click', '.btn-delete-fy', function() {
            if (!confirm('Delete this fiscal year?')) return;
            let id = $(this).data('id');
            $.post("{{ route('fiscalyear.delete') }}", {
                id: id,
                _token: "{{ csrf_token() }}"
            }, function() {
                table.ajax.reload();
            });
        });

        $(document).on('submit', '#fiscalyearForm', function(e) {
            e.preventDefault();
            $.post("{{ route('fiscalyear.save') }}", $(this).serialize(), function() {
                $('#fiscalyearModal').modal('hide');
                table.ajax.reload();
            });
        });

        // stop polling once the modal closes, so it doesn't run forever in the background
        $('#fiscalyearModal').on('hidden.bs.modal', function() {
            stopCodeWatcher();
        });
    });
</script>
@endsection