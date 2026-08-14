@extends('layouts.main')
@section('title', 'Users')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab" data-tabid="active" data-bs-toggle="tab" href="#active">
                        <i class="fas fa-list me-1"></i> Active Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="inactive-tab" data-tabid="inactive" data-bs-toggle="tab" href="#inactive">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        var baseurl = '{{ url('') }}';

        $(document).ready(function() {

            /* ── Tab loading ─────────────────────────────────────── */
            $(document).off('click', '.nav-link').on('click', '.nav-link', function(e) {
                e.preventDefault();

                var $tab  = $(this);
                var tabid = $tab.data('tabid');

                if (!tabid) return;

                $('#loading').show();
                $('#nav-tabContent').empty();

                $.post('{{ route('user.tab') }}', {
                        tabid:  tabid,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        // jQuery's .html() already runs embedded <script> tags
                        // when the inserted markup contains one (it internally
                        // falls back to .append()) — re-evaluating them here
                        // would execute this page's setup script twice.
                        $('#nav-tabContent').html(response);
                    })
                    .fail(function() {
                        $('#nav-tabContent').html(
                            '<div class="alert alert-danger">Error loading content.</div>'
                        );
                    })
                    .always(function() {
                        $('#loading').hide();
                    });

                $('.nav-link').removeClass('active');
                $tab.addClass('active');
            });

            /* ── Load active tab on page ready ───────────────────── */
            $('#active-tab').trigger('click');

        });
    </script>
@endsection