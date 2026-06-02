@extends('layouts.main')
@section('title', 'Favicon Setting')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            {{-- Page Header --}}
            <div class="d-flex align-items-center mb-4">
                <div class="me-3" style="width:42px;height:42px;background:linear-gradient(135deg,#696cff,#567bfb);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="bx bx-image-alt text-white" style="font-size:20px;"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Favicon Setting</h5>
                    <small class="text-muted">Update your site favicon</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    {{-- Success Alert --}}
                    @if(session('success'))
                        <div class="alert border-0 mb-4 d-flex align-items-center gap-2"
                             style="background:#e8f5e9;color:#2e7d32;border-radius:10px;">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>{{ session('success') }}</span>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="filter:invert(30%) sepia(60%) saturate(400%) hue-rotate(90deg);"></button>
                        </div>
                    @endif

                    <form action="{{ route('favicon.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Upload Area --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2">Favicon Image</label>
                            <small class="text-muted d-block mb-3">Accepted formats: PNG, ICO, JPG, GIF, SVG — any size</small>

                            {{-- Drop Zone --}}
                            <label for="favicon" id="dropZone"
                                   class="d-flex flex-column align-items-center justify-content-center w-100 rounded-3 cursor-pointer"
                                   style="border: 2px dashed #c9cbff; background:#f5f5ff; min-height:140px; transition:all .2s; cursor:pointer;">

                                <div id="dropZoneContent" class="text-center p-3">
                                    <i class="bx bx-cloud-upload mb-2" style="font-size:36px;color:#696cff;"></i>
                                    <p class="mb-1 fw-semibold" style="color:#696cff;">Click to upload</p>
                                    <p class="mb-0 text-muted" style="font-size:12px;">PNG, ICO, JPG, GIF, SVG</p>
                                </div>

                                <input type="file"
                                       class="d-none @error('favicon') is-invalid @enderror"
                                       name="favicon"
                                       id="favicon"
                                       accept=".png,.ico,.jpg,.jpeg,.gif,.svg">
                            </label>

                            @error('favicon')
                                <div class="text-danger mt-2" style="font-size:13px;">
                                    <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Preview --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2">Preview</label>
                            <div class="p-3 rounded-3" style="background:#f8f8ff;border:1px solid #e0e0ff;">

                                <div class="d-flex align-items-center gap-4">

                                    {{-- Large favicon preview box --}}
                                    <div style="width:120px;height:120px;border:1px solid #dee2e6;border-radius:12px;background:#fff;padding:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(105,108,255,.1);">
                                        @if(!empty($favicon?->image))
                                            <img src="{{ asset('storage/favicon/' . $favicon->image) }}"
                                                 id="favicon_preview"
                                                 style="max-width:100%;max-height:100%;object-fit:contain;"
                                                 alt="Favicon">
                                        @else
                                            <img src="{{ asset('/no-image.jpg') }}"
                                                 id="favicon_preview"
                                                 style="max-width:100%;max-height:100%;object-fit:contain;"
                                                 alt="No favicon">
                                        @endif
                                    </div>

                                    <!-- {{-- Right side: label + browser tab mockup --}}
                                    <div style="flex:1;">
                                        <p class="mb-2 text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Actual size in browser tab</p>

                                        {{-- Browser tab mockup --}}
                                        <div class="d-flex align-items-center gap-1 px-2 py-1"
                                             style="background:#e8e8f0;width:fit-content;min-width:170px;font-size:12px;color:#555;border-radius:6px 6px 0 0;">
                                            <img id="tab_preview"
                                                 src="{{ !empty($favicon?->image) ? asset('storage/favicon/'.$favicon->image) : asset('/no-image.jpg') }}"
                                                 style="width:16px;height:16px;object-fit:contain;border-radius:2px;flex-shrink:0;">
                                            <span class="ms-1 text-truncate" style="max-width:110px;">Your Site</span>
                                            <i class="bx bx-x ms-2" style="font-size:13px;"></i>
                                        </div>
                                        <div style="height:5px;background:#f0f0f8;border-radius:0 0 4px 4px;width:170px;"></div>

                                        <p class="mb-0 mt-2 text-muted" style="font-size:11px;">
                                            <i class="bx bx-info-circle me-1"></i>Browser tab preview
                                        </p>
                                    </div> -->

                                </div>

                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn px-4 py-2 fw-semibold text-white"
                                    style="background:linear-gradient(135deg,#696cff,#567bfb);border:none;border-radius:8px;box-shadow:0 4px 12px rgba(105,108,255,.35);">
                                <i class="bx bx-save me-1"></i> Update Favicon
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('main-scripts')
<script>
    const input      = document.getElementById('favicon');
    const preview    = document.getElementById('favicon_preview');
    const tabPreview = document.getElementById('tab_preview');
    const dropZone   = document.getElementById('dropZone');
    const zoneContent= document.getElementById('dropZoneContent');

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src    = e.target.result;
            tabPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Update drop zone to show filename
        zoneContent.innerHTML = `
            <i class='bx bx-file mb-2' style='font-size:32px;color:#696cff;'></i>
            <p class='mb-1 fw-semibold' style='color:#696cff;'>${file.name}</p>
            <p class='mb-0 text-muted' style='font-size:12px;'>${(file.size/1024).toFixed(1)} KB</p>
        `;
    });

    // Drag & drop styling
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.style.borderColor = '#696cff';
        dropZone.style.background  = '#ededff';
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '#c9cbff';
        dropZone.style.background  = '#f5f5ff';
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.style.borderColor = '#c9cbff';
        dropZone.style.background  = '#f5f5ff';
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection