@extends('layouts.main')
@section('title', 'Category')
@section('content')
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
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="category-tab" data-bs-toggle="tab" href="#category">
                        <i class="fas fa-list me-1"></i> Category
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="subcategory-tab" data-bs-toggle="tab" href="#subcategory">
                        <i class="fas fa-sitemap me-1"></i> Sub Category
                    </a>
                </li>
            </ul>

            <!-- Content Area -->
            <div class="tab-content mt-4" id="nav-tabContent">
                <!-- Content loads here via AJAX -->
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading" style="display: none;" class="text-center p-4">
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
            // ✅ YOUR EXACT PATTERN
            $(document).off('click', '.nav-link');
            $(document).on('click', '.nav-link', function(e) {
                e.preventDefault();
                $('#loading').show();

                var tabid = $(this).attr('id').replace('-tab', ''); // category-tab → category
                var url = '{{ route('category.tabs') }}';
                var infoData = {
                    tabid: tabid,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $.post(url, infoData, function(response) {
                    $('#nav-tabContent').html(response);
                    $('#loading').hide();

                    // Re-initialize scripts after content loads
                    // initializeScripts();
                }).fail(function() {
                    $('#nav-tabContent').html(
                        '<div class="alert alert-danger">Error loading content!</div>');
                    $('#loading').hide();
                });

                // Update active tab
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
            });

            // Trigger first tab on load
            $('#category-tab').trigger('click');
        });
    </script>
@endsection
