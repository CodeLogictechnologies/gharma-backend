@extends('layouts.main')
@section('title', 'Terms Conditions and Privacy Policy')

@section('content')
<style>
    /* Force tab highlight to follow .active only, not hover/focus */
    #policyTabs {
        border-bottom: 1px solid #dee2e6;
    }

    #policyTabs .nav-link {
        background-color: transparent !important;
        border: 1px solid transparent !important;
        color: #697a8d;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    #policyTabs .nav-link:hover,
    #policyTabs .nav-link:focus {
        background-color: transparent !important;
        border-color: transparent !important;
        color: #566a7f;
    }

    #policyTabs .nav-link.active {
        background-color: #f5f5f9 !important;
        border-color: #dee2e6 #dee2e6 #f5f5f9 !important;
        color: #333 !important;
        font-weight: 600;
    }

    .note-editor.note-frame.fullscreen {
        z-index: 99999 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: #fff !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .note-editor.note-frame.fullscreen .note-toolbar {
        flex-shrink: 0 !important;
        background: #fff !important;
        z-index: 100000 !important;
    }

    .note-editor.note-frame.fullscreen .note-editing-area {
        flex: 1 !important;
        height: auto !important;
        overflow-y: auto !important;
        background: #fff !important;
    }

    .note-editor.note-frame.fullscreen .note-editable {
        height: 100% !important;
        min-height: 300px !important;
        background: #fff !important;
        overflow-y: auto !important;
    }

    .note-editor.note-frame.fullscreen .note-statusbar {
        flex-shrink: 0 !important;
    }

    #layout-menu,
    #layout-navbar,
    .layout-menu,
    .layout-navbar,
    .layout-overlay,
    .layout-menu-toggle {
        z-index: 1 !important;
    }

    .note-modal,
    .note-popover,
    .note-help-dialog,
    .note-modal.open {
        z-index: 100000 !important;
    }

    .note-modal .modal-dialog {
        z-index: 100001 !important;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Terms Conditions and Privacy Policy</h5>
        </div>
        <div class="card-body">

            {{-- ── Tabs ── --}}
            <ul class="nav nav-tabs mb-4" id="policyTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#termsTab">
                        <i class="bx bx-file me-1"></i> Terms & Conditions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#privacyTab">
                        <i class="bx bx-lock-alt me-1"></i> Privacy Policy
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- ── Terms & Conditions Tab ── --}}
                <div class="tab-pane fade show active" id="termsTab">
                    <form id="termsPolicyForm">
                        @csrf
                        <input type="hidden" name="type" value="terms">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Terms & Conditions Description<span class="text-danger">*</span></label>
                            <textarea name="description" id="termsEditor" class="form-control">{!! $termsPolicy->description ?? '' !!}</textarea>
                        </div>
                        <button type="button" class="btn btn-primary savePolicy" data-type="terms">
                            <i class="bx bx-save me-1"></i> Update Terms & Conditions
                        </button>
                    </form>
                </div>

                {{-- ── Privacy Policy Tab ── --}}
                <div class="tab-pane fade" id="privacyTab">
                    <form id="privacyPolicyForm">
                        @csrf
                        <input type="hidden" name="type" value="privacy">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Privacy Policy Description<span class="text-danger">*</span></label>
                            <textarea name="description" id="privacyEditor" class="form-control">{!! $privacyPolicy->description ?? '' !!}</textarea>
                        </div>
                        <button type="button" class="btn btn-primary savePolicy" data-type="privacy">
                            <i class="bx bx-save me-1"></i> Update Privacy Policy
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('main-scripts')

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    $(function() {

        const summernoteConfig = {
            height: 300,
            dialogsInBody: false,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        };

        // ── Init both editors ────────────────────────────────────────
        $('#termsEditor').summernote(summernoteConfig);
        $('#privacyEditor').summernote(summernoteConfig);

        // ── Fix Summernote rendering in previously-hidden tab ──────────────────────
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');

            if (target === '#privacyTab') {
                // Just refresh layout, don't destroy/rebuild
                $('#privacyEditor').summernote('code', $('#privacyEditor').summernote('code'));
            }
            if (target === '#termsTab') {
                $('#termsEditor').summernote('code', $('#termsEditor').summernote('code'));
            }

            // Force Summernote to recalculate size now that the pane is visible
            $(window).trigger('resize');
        }); // ── Fix Summernote height in hidden tab ──────────────────────


        // ── Save ────────────────────────────────────────────────────
        $(document).on('click', '.savePolicy', function() {
            const type = $(this).data('type');
            const editorId = type === 'terms' ? '#termsEditor' : '#privacyEditor';
            const content = $(editorId).summernote('code');

            if (!content || content.trim() === '' || content === '<p><br></p>') {
                showNotification('Please enter description.', 'error');
                return;
            }

            const $btn = $(this);
            const origHtml = $btn.html();
            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
            );

            $.ajax({
                url: '{{ route("terms.conditions.save") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    description: content,
                },
                success: function(response) {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    showNotification(result.message, result.type);
                    $btn.prop('disabled', false).html(origHtml);
                },
                error: function() {
                    showNotification('Something went wrong.', 'error');
                    $btn.prop('disabled', false).html(origHtml);
                }
            });
        });

    });
</script>
@endsection