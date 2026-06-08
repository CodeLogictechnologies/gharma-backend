@extends('layouts.main')
@section('title', 'Users')
@section('content')

    {{-- Remark Modal --}}
    <div class="modal fade" id="remarkModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Remark</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="remark_user_id" />
                    <div class="mb-3">
                        <label for="remarkText" class="form-label">Remark <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="remarkText" rows="4" placeholder="Enter remark..."></textarea>
                        <div id="remarkError" class="text-danger mt-1" style="display:none;">Remark is required.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="cancelRemark">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveRemark">Save</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Status</h5>
                    <button type="button" class="btn-close" id="cancelStatusX" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to change this user's status?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="cancelStatus">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmStatus">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#active">
                        <i class="fas fa-list me-1"></i> Active Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="inactive-tab" data-bs-toggle="tab" href="#inactive">
                        <i class="fas fa-sitemap me-1"></i> Inactive Users (Pending / Rejected)
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-4" id="nav-tabContent"></div>
        </div>
    </div>

    {{-- Loading Spinner --}}
    <div id="loading" style="display:none;" class="text-center p-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading...</p>
    </div>

@endsection

@section('main-scripts')
    {{-- ✅ Load Select2 JS here once — guaranteed available before any form opens --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        var baseurl = '{{ url('') }}';

        var pendingStatusUserId = null;
        var pendingStatusValue = null;
        var pendingStatusDropdown = null;
        var previousStatusValue = null;

        $(document).ready(function() {

            /* =========================================================
               TAB LOADING
               FIX: $.globalEval() after .html(response) so the IIFE
               inside each tab partial actually executes.
            ========================================================= */
            $(document).off('click', '.nav-link').on('click', '.nav-link', function(e) {
                e.preventDefault();

                var $tab = $(this);
                var tabid = $tab.attr('id').replace('-tab', '');

                $('#loading').show();
                $('#nav-tabContent').empty();

                $.post('{{ route('user.tab') }}', {
                        tabid: tabid,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        $('#nav-tabContent').html(response);

                        // ✅ KEY FIX — execute scripts injected by .html()
                        // Without this the IIFE in the tab partial never runs,
                        // so DataTable, openUserModal, and all event bindings are missing.
                        $('#nav-tabContent').find('script').each(function() {
                            $.globalEval($(this).text());
                        });
                    })
                    .fail(function() {
                        $('#nav-tabContent').html(
                            '<div class="alert alert-danger">Error loading content.</div>');
                    })
                    .always(function() {
                        $('#loading').hide();
                    });

                $('.nav-link').removeClass('active');
                $tab.addClass('active');
            });

            $('#active-tab').trigger('click');
            $(document).off('change', '.changeStatus').on('change', '.changeStatus', function() {
                var $dd = $(this);
                var newVal = $dd.val();
                var origVal = $dd.data('original');

                if (newVal === origVal) return;

                pendingStatusDropdown = $dd;
                previousStatusValue = origVal;
                pendingStatusUserId = $dd.data('id');
                pendingStatusValue = newVal;

                $('#remarkText').val('');
                $('#remarkError').hide();
                $('#remark_user_id').val(pendingStatusUserId);

                bootstrap.Modal.getOrCreateInstance(document.getElementById('remarkModal')).show();
            });

            $(document).off('click', '#cancelRemark').on('click', '#cancelRemark', function() {
                if (pendingStatusDropdown) pendingStatusDropdown.val(previousStatusValue);
                bootstrap.Modal.getInstance(document.getElementById('remarkModal'))?.hide();
                resetStatusState();
            });

            $(document).off('click', '#saveRemark').on('click', '#saveRemark', function() {
                var remark = $('#remarkText').val().trim();
                if (!remark) {
                    $('#remarkError').show();
                    return;
                }
                $('#remarkError').hide();
                showLoader();

                $.post('{{ route('user.status') }}', {
                        user_id: pendingStatusUserId,
                        status: pendingStatusValue,
                        remark: remark,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            if (pendingStatusDropdown) pendingStatusDropdown.data('original',
                                pendingStatusValue);
                            resetStatusState();
                            bootstrap.Modal.getInstance(document.getElementById('remarkModal'))?.hide();
                            if (typeof userTable !== 'undefined') userTable.fnDraw();
                        } else {
                            if (pendingStatusDropdown) pendingStatusDropdown.val(previousStatusValue);
                            resetStatusState();
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        if (pendingStatusDropdown) pendingStatusDropdown.val(previousStatusValue);
                        resetStatusState();
                        showNotification('Request failed!', 'error');
                    })
                    .always(function() {
                        hideLoader();
                    });
            });

            document.getElementById('remarkModal').addEventListener('hidden.bs.modal', function() {
                if (pendingStatusUserId !== null) {
                    if (pendingStatusDropdown) pendingStatusDropdown.val(previousStatusValue);
                    resetStatusState();
                }
                $('#remarkText').val('');
                $('#remarkError').hide();
            });

            function resetStatusState() {
                pendingStatusUserId = pendingStatusValue = pendingStatusDropdown = previousStatusValue = null;
            }

        });
    </script>
@endsection
