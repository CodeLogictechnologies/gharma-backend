@extends('layouts.main')
@section('title', 'Return & Refund Policy')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Return & Refund Policy</h5>
        </div>
        <div class="card-body">

            {{-- ── Tabs ── --}}
            <ul class="nav nav-tabs mb-4" id="policyTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#returnTab">
                        <i class="bx bx-undo me-1"></i> Return Policy
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#refundTab">
                        <i class="bx bx-money me-1"></i> Refund Policy
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- ── Return Policy Tab ── --}}
                <div class="tab-pane fade show active" id="returnTab">
                    <form id="returnPolicyForm">
                        @csrf
                        <input type="hidden" name="type" value="return">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Return Policy Description<span class="text-danger">*</span></label>
                            <textarea name="description" id="returnEditor" class="form-control">{!! $returnPolicy->description ?? '' !!}</textarea>
                        </div>
                        <button type="button" class="btn btn-primary savePolicy" data-type="return">
                            <i class="bx bx-save me-1"></i> Update Return Policy
                        </button>
                    </form>
                </div>

                {{-- ── Refund Policy Tab ── --}}
                <div class="tab-pane fade" id="refundTab">
                    <form id="refundPolicyForm">
                        @csrf
                        <input type="hidden" name="type" value="refund">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Refund Policy Description<span class="text-danger">*</span></label>
                            <textarea name="description" id="refundEditor" class="form-control">{!! $refundPolicy->description ?? '' !!}</textarea>
                        </div>
                        <button type="button" class="btn btn-primary savePolicy" data-type="refund">
                            <i class="bx bx-save me-1"></i> Update Refund Policy
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('main-scripts')

{{-- ✅ Summernote CSS + JS loaded here after jQuery is ready --}}
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
$(function () {

    const summernoteConfig = {
        height: 300,
        dialogsInBody: true,
        toolbar: [
            ['style',  ['style']],
            ['font',   ['bold', 'underline', 'italic', 'clear']],
            ['color',  ['color']],
            ['para',   ['ul', 'ol', 'paragraph']],
            ['table',  ['table']],
            ['insert', ['link']],
            ['view',   ['fullscreen', 'codeview', 'help']]
        ]
    };

    // ── Init both editors ────────────────────────────────────────
    $('#returnEditor').summernote(summernoteConfig);
    $('#refundEditor').summernote(summernoteConfig);

    // ── Fix Summernote height in hidden tab ──────────────────────
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        if (target === '#refundTab') {
            $('#refundEditor').summernote('destroy');
            $('#refundEditor').summernote(summernoteConfig);
            $('#refundEditor').summernote('code', {!! json_encode($refundPolicy->description ?? '') !!});
        }
        if (target === '#returnTab') {
            $('#returnEditor').summernote('destroy');
            $('#returnEditor').summernote(summernoteConfig);
            $('#returnEditor').summernote('code', {!! json_encode($returnPolicy->description ?? '') !!});
        }
    });

    // ── Save ────────────────────────────────────────────────────
    $(document).on('click', '.savePolicy', function () {
        const type     = $(this).data('type');
        const editorId = type === 'return' ? '#returnEditor' : '#refundEditor';
        const content  = $(editorId).summernote('code');

        if (!content || content.trim() === '' || content === '<p><br></p>') {
            showNotification('Please enter description.', 'error');
            return;
        }

        const $btn     = $(this);
        const origHtml = $btn.html();
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
        );

        $.ajax({
            url:  '{{ route("refunds.policy.save") }}',
            type: 'POST',
            data: {
                _token:      '{{ csrf_token() }}',
                type:        type,
                description: content,
            },
            success: function (response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                showNotification(result.message, result.type);
                $btn.prop('disabled', false).html(origHtml);
            },
            error: function () {
                showNotification('Something went wrong.', 'error');
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });

});
</script>
@endsection