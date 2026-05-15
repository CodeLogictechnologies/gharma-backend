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
    <script>
        var baseurl = '{{ url('') }}';

        // Shared state for status change flow
        var pendingStatusUserId = null;
        var pendingStatusValue = null;
        var pendingStatusDropdown = null;
        var previousStatusValue = null;

        $(document).ready(function() {

            /* =========================================================
               TAB LOADING
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

            /* =========================================================
               STATUS CHANGE FLOW
               Step 1: dropdown changes  → store values → show status confirm modal
               Step 2: Confirm clicked   → hide status modal → show remark modal
               Step 3: Save remark       → POST status + remark → reload table
               Cancel at any step        → revert dropdown to previous value
            ========================================================= */

            // Step 1: Dropdown changed
            $(document).on('change', '.statusDropdown', function() {
                pendingStatusDropdown = $(this);
                previousStatusValue = pendingStatusDropdown.data('previous') || pendingStatusDropdown.data(
                    'original');
                pendingStatusUserId = pendingStatusDropdown.data('id');
                pendingStatusValue = pendingStatusDropdown.val();

                // Store current as previous for next change
                pendingStatusDropdown.data('previous', pendingStatusDropdown.data('original') ??
                    previousStatusValue);

                new bootstrap.Modal(document.getElementById('statusModal')).show();
            });

            // Cancel status modal (button or X) → revert dropdown
            $(document).on('click', '#cancelStatus, #cancelStatusX', function() {
                revertDropdown();
                var modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
                if (modal) modal.hide();
            });

            // Step 2: Confirmed status change → open remark modal
            $('#confirmStatus').on('click', function() {
                var statusModal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
                if (statusModal) statusModal.hide();

                $('#remark_user_id').val(pendingStatusUserId);
                $('#remarkText').val('');
                $('#remarkError').hide();

                document.getElementById('statusModal').addEventListener('hidden.bs.modal',
                function handler() {
                    this.removeEventListener('hidden.bs.modal', handler);
                    new bootstrap.Modal(document.getElementById('remarkModal')).show();
                });
            });

            // Cancel remark modal → revert dropdown
            $('#cancelRemark').on('click', function() {
                revertDropdown();
                bootstrap.Modal.getInstance(document.getElementById('remarkModal')).hide();
                resetStatusState();
            });

            document.getElementById('remarkModal').addEventListener('hidden.bs.modal', function() {
                // If closed without saving, revert
                if (pendingStatusUserId !== null) {
                    revertDropdown();
                    resetStatusState();
                }
            });

            // Step 3: Save remark + status
            $('#saveRemark').on('click', function() {
                var remark = $('#remarkText').val().trim();

                if (!remark) {
                    $('#remarkError').show();
                    return;
                }
                $('#remarkError').hide();

                $.post('{{ route('user.status') }}', {
                        id: pendingStatusUserId,
                        status: pendingStatusValue,
                        remark: remark,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            // Update the dropdown's stored "original" value so cancel works correctly next time
                            if (pendingStatusDropdown) {
                                pendingStatusDropdown.data('original', pendingStatusValue);
                                pendingStatusDropdown.data('previous', pendingStatusValue);
                            }
                            // Reload the active tab's DataTable if available
                            if (typeof userTable !== 'undefined' && userTable) {
                                userTable.fnDraw();
                            }
                        } else {
                            showNotification(result.message, 'error');
                            revertDropdown();
                        }
                    })
                    .fail(function() {
                        showNotification('Status update failed. Please try again.', 'error');
                        revertDropdown();
                    })
                    .always(function() {
                        bootstrap.Modal.getInstance(document.getElementById('remarkModal')).hide();
                        resetStatusState();
                    });
            });

            function revertDropdown() {
                if (pendingStatusDropdown && previousStatusValue !== null) {
                    pendingStatusDropdown.val(previousStatusValue);
                }
            }

            function resetStatusState() {
                pendingStatusUserId = null;
                pendingStatusValue = null;
                pendingStatusDropdown = null;
                previousStatusValue = null;
            }

        });
    </script>
@endsection
