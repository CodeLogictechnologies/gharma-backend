@extends('layouts.main')
@section('title', 'Category')
@section('content')
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveRemark">Save</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Category</h5>
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

    {{-- Status Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to change this user's status?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    {{-- Fixed: id="confirmStatus", no hardcoded data-id --}}
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
               Step 1: dropdown changes  → show Status confirmation modal
               Step 2: user clicks Confirm in status modal → hide status modal, show Remark modal
               Step 3: user writes remark & clicks Save → POST status + remark together
            ========================================================= */


        });
    </script>
@endsection
