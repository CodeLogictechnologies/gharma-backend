@extends('layouts.main')
@section('title', 'Promotional Banners')
@section('content')

    {{-- Loaded once here (page load) instead of inside the AJAX-injected form partial,
         so the Add/Edit modal's Save button isn't briefly unresponsive on a cold cache
         while the modal's own script waits on this to finish loading. --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <h5 class="mb-0">Promotional Banners</h5>
                @can('add.promotion')
                <button type="button" id="addPromotion" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add Promotion
                </button>
                @endcan
            </div>

            <div class="mx-4 mb-4">
                <table class="table" id="promotionTable">
                    <thead class="table-light">
                        <tr class="align-middle">
                            <th style="width: 5%">S.N</th>
                            <th style="width: 10%">Banner</th>
                            <th style="width: 18%">Name</th>
                            <th style="width: 10%">Applies To</th>
                            <th style="width: 20%">Target</th>
                            <th style="width: 8%">Color</th>
                            <th style="width: 7%">Sort Order</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 5%">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div class="modal fade" id="promotionModal" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content" id="promotionModalContent"></div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Promotion</h5>
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
    {{-- Must load after jquery.js (included near the end of layouts.main's body) —
         @section('content') above renders before that script tag, so select2 can't
         be loaded there without failing to attach to $.fn. --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable ─────────────────────────────────────────────────────────────
            window.promotionTable = $('#promotionTable').dataTable({
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
                sAjaxSource: '{{ route('promotion.list') }}',
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },
                aoColumns: [
                    { data: 'sno' },
                    {
                        data: 'image_url',
                        bSortable: false,
                        mRender: function(url) {
                            return url
                                ? '<img src="' + url + '" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">'
                                : '—';
                        }
                    },
                    { data: 'name' },
                    { data: 'applies_to' },
                    { data: 'target_names' },
                    { data: 'bg_color', bSortable: false },
                    { data: 'sort_order', bSortable: false },
                    {
                        data: 'status',
                        bSortable: false,
                        mRender: function(status, type, row) {
                            var checked = status === 'Y' ? 'checked' : '';
                            return '<div class="form-check form-switch">' +
                                '<input class="form-check-input status-toggle" type="checkbox" role="switch" data-id="' + row.id + '" ' + checked + '>' +
                                '</div>';
                        }
                    },
                    { data: 'action', bSortable: false },
                ],
            });

            // ── Open modal helper ──────────────────────────────────────────────────────
            function openModal(url, params, method) {
                var req = method === 'POST' ? $.post(url, params) : $.get(url, params);
                req.done(function(response) {
                    $('#promotionModalContent').html(response);
                    var modalEl = document.getElementById('promotionModal');
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
            $('#addPromotion').on('click', function() {
                openModal('{{ route('promotion.form') }}', {}, 'GET');
            });

            // ── Edit ───────────────────────────────────────────────────────────────────
            $(document).on('click', '.editPromotion', function() {
                openModal('{{ route('promotion.form') }}', {
                    id: $(this).data('id'),
                    _token: '{{ csrf_token() }}'
                }, 'POST');
            });

            // ── Status toggle ─────────────────────────────────────────────────────────
            $(document).on('change', '.status-toggle', function() {
                var $toggle = $(this);
                var id = $toggle.data('id');
                var wasChecked = !$toggle.is(':checked');

                $.post('{{ route('promotion.toggle-status') }}', {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(res) {
                        if (res.type === 'success') {
                            showNotification(res.message, 'success');
                        } else {
                            showNotification(res.message, 'error');
                            $toggle.prop('checked', wasChecked);
                        }
                    })
                    .fail(function() {
                        showNotification('Failed to update status.', 'error');
                        $toggle.prop('checked', wasChecked);
                    });
            });

            // ── Delete ─────────────────────────────────────────────────────────────────
            var deleteId = null;

            $(document).on('click', '.deletePromotion', function() {
                deleteId = $(this).data('id');
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;
                $.post('{{ route('promotion.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(res) {
                        var result = typeof res === 'string' ? JSON.parse(res) : res;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            promotionTable.fnDraw();
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

            // ── Clear modal on close ───────────────────────────────────────────────────
            document.getElementById('promotionModal').addEventListener('hidden.bs.modal', function() {
                $('#promotionModalContent').html('');
            });
        });
    </script>
@endsection
