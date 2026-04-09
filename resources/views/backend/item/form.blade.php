{{-- resources/views/backend/organization/form.blade.php --}}
<style>
    .ck-content {
        min-height: 300px !important;
    }

    .image-drop-zone {
        border: 2px dashed #adb5bd;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: #f8f9fa;
        position: relative;
    }

    .image-drop-zone:hover,
    .image-drop-zone.dragover {
        border-color: #0d6efd;
        background: #e8f0fe;
    }

    .image-drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .image-drop-zone .drop-text {
        margin: 8px 0 0;
        color: #6c757d;
        font-size: .875rem;
    }

    #imagePreviewGrid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
    }

    .img-preview-card {
        position: relative;
        width: 110px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #dee2e6;
        background: #fff;
        transition: border-color .2s;
    }

    .img-preview-card.is-primary {
        border-color: #0d6efd;
    }

    .img-preview-card img {
        width: 110px;
        height: 90px;
        object-fit: cover;
        display: block;
    }

    .img-preview-card .img-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 6px;
        background: #f1f3f5;
        font-size: .7rem;
    }

    .btn-remove-img {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: .85rem;
    }

    .btn-primary-img {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        font-size: .75rem;
    }

    .img-preview-card.is-primary .btn-primary-img {
        color: #0d6efd;
        font-weight: 600;
    }

    .primary-badge {
        display: none;
        position: absolute;
        top: 4px;
        left: 4px;
        background: #0d6efd;
        color: #fff;
        font-size: .6rem;
        border-radius: 4px;
        padding: 1px 5px;
    }

    .img-preview-card.is-primary .primary-badge {
        display: block;
    }

    .variation-row {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 10px;
        position: relative;
    }

    .remove-variation {
        position: absolute;
        top: 10px;
        right: 12px;
        background: none;
        border: none;
        color: #dc3545;
        font-size: 1.1rem;
        cursor: pointer;
    }

    .section-label {
        font-weight: 600;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #6c757d;
        margin-bottom: 8px;
    }

    .border-danger {
        border-color: red !important;
    }
</style>

<div class="modal-header">
    <h1 class="modal-title fs-5">{{ empty($data['id']) ? 'Add Item' : 'Edit Item' }}</h1>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <form action="{{ route('item.save') }}" method="POST" id="itemForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}">

        {{-- ── Row 1: title / brand / type ── --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="title" placeholder="Enter item name..."
                    value="{{ $data['title'] ?? '' }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="brand" placeholder="Enter brand name..."
                    value="{{ $data['brand'] ?? '' }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select">
                    @foreach (['Regular', 'Special', 'Featured'] as $t)
                        <option value="{{ $t }}" {{ ($data['type'] ?? 'Regular') === $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── Row 2: category / sub-category / status ── --}}
        <div class="row mb-2">
            <div class="col-md-4">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="categories" id="categorySelect" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ ($data['categories'] ?? '') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Sub Category <span class="text-danger">*</span></label>
                <select name="sub_categories" id="subCategorySelect" class="form-select" required>
                    <option value="">-- Select Sub Category --</option>
                    @foreach ($subCategories as $sub)
                        <option value="{{ $sub->id }}"
                            {{ ($data['sub_categories'] ?? '') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Y" {{ ($data['status'] ?? 'Y') === 'Y' ? 'selected' : '' }}>Active</option>
                    <option value="N" {{ ($data['status'] ?? '') === 'N' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        {{-- ── Description ── --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="5">{!! $data['description'] ?? '' !!}</textarea>
            </div>
        </div>

        {{-- ── Multiple Images ── --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <p class="section-label">Product Images</p>

                <div class="image-drop-zone" id="imageDropZone">
                    <input type="file" id="productImages" name="images[]" accept="image/*" multiple
                        {{ empty($data['id']) ? 'required' : '' }}>
                    <div style="font-size:2rem">🖼️</div>
                    <p class="drop-text">
                        <strong>Click to upload</strong> or drag &amp; drop<br>
                        JPG, JPEG, PNG — multiple allowed
                    </p>
                </div>

                {{-- Tracks which grid position is the primary image --}}
                <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">

                <div id="imagePreviewGrid">
                    @if (!empty($data['images']))
                        @foreach ($data['images'] as $i => $imgTag)
                            @php preg_match('/data-path="([^"]+)"/', $imgTag, $m); @endphp
                            <div class="img-preview-card {{ $i === 0 ? 'is-primary' : '' }}"
                                data-index="{{ $i }}" data-type="existing">
                                <span class="primary-badge">Primary</span>
                                {!! $imgTag !!}
                                <div class="img-actions">
                                    <button type="button" class="btn-primary-img">★ Primary</button>
                                    <button type="button" class="btn-remove-img">✕</button>
                                </div>
                                {{-- kept_images[] tells the controller which existing paths to retain --}}
                                <input type="hidden" class="kept-path" name="kept_images[]"
                                    value="{{ $m[1] ?? '' }}">
                            </div>
                        @endforeach
                    @endif
                </div>

                <small class="text-muted d-block mt-1">
                    Click <strong>★ Primary</strong> to set the main photo. First image is primary by default.
                </small>
            </div>
        </div>

        {{-- ── Variations → saved in extra_attributes JSON ── --}}
        <div class="row mb-2">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="section-label mb-0">Variations</p>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addVariation">
                        <i class="fa fa-plus"></i> Add Variation
                    </button>
                </div>

                <div id="variationsContainer">
                    @php
                        $variations = $data['variations'] ?? [
                            [
                                'name' => 'Size',
                                'value' => '',
                                'sku' => '',
                                'price' => '',
                                'stock' => '',
                                'status' => 'active',
                            ],
                        ];
                    @endphp

                    @foreach ($variations as $i => $v)
                        <div class="variation-row" data-index="{{ $i }}">
                            <button type="button" class="remove-variation">✕</button>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label mb-1">Attribute</label>
                                    <select name="variations[{{ $i }}][name]" class="form-select">
                                        @foreach (['Size', 'Color', 'Weight', 'Material', 'Other'] as $attr)
                                            <option value="{{ $attr }}"
                                                {{ ($v['name'] ?? '') === $attr ? 'selected' : '' }}>
                                                {{ $attr }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">Value</label>
                                    <input type="text" name="variations[{{ $i }}][value]"
                                        class="form-control" placeholder="e.g. Red, XL"
                                        value="{{ $v['value'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">SKU</label>
                                    <input type="text" name="variations[{{ $i }}][sku]"
                                        class="form-control" placeholder="SKU-001" value="{{ $v['sku'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">Price</label>
                                    <input type="number" name="variations[{{ $i }}][price]"
                                        class="form-control" placeholder="0.00" min="0" step="0.01"
                                        value="{{ $v['price'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">Stock</label>
                                    <input type="number" name="variations[{{ $i }}][stock]"
                                        class="form-control" placeholder="0" min="0"
                                        value="{{ $v['stock'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">Status</label>
                                    <select name="variations[{{ $i }}][status]" class="form-select">
                                        <option value="active"
                                            {{ ($v['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive"
                                            {{ ($v['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted">Leave Value empty to skip a row.</small>
            </div>
        </div>

    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary saveItem">
        <i class="fa fa-save"></i> {{ empty($data['id']) ? 'Save' : 'Update' }}
    </button>
</div>

<script>
    $(document).ready(function() {

        /* ─────────────────────────────────────────
           MULTIPLE IMAGE UPLOAD & PREVIEW
        ───────────────────────────────────────── */
        let newFiles = [];

        const dropZone = document.getElementById('imageDropZone');
        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(Array.from(e.dataTransfer.files));
        });

        $('#productImages').on('change', function() {
            handleFiles(Array.from(this.files));
            this.value = '';
        });

        function handleFiles(files) {
            files.filter(f => f.type.startsWith('image/')).forEach(file => {
                const reader = new FileReader();
                const fileIdx = newFiles.length;
                newFiles.push(file);

                reader.onload = e => {
                    const isPrimary = ($('#imagePreviewGrid .img-preview-card').length === 0);
                    $('#imagePreviewGrid').append(`
                    <div class="img-preview-card ${isPrimary ? 'is-primary' : ''}"
                         data-index="${fileIdx}" data-type="new">
                        <span class="primary-badge">Primary</span>
                        <img src="${e.target.result}" alt="preview">
                        <div class="img-actions">
                            <button type="button" class="btn-primary-img">★ Primary</button>
                            <button type="button" class="btn-remove-img">✕</button>
                        </div>
                    </div>`);
                    if (isPrimary) syncPrimary();
                    syncFileInput();
                };
                reader.readAsDataURL(file);
            });
        }

        $(document).on('click', '.btn-primary-img', function() {
            $('#imagePreviewGrid .img-preview-card').removeClass('is-primary');
            $(this).closest('.img-preview-card').addClass('is-primary');
            syncPrimary();
        });

        $(document).on('click', '.btn-remove-img', function() {
            const card = $(this).closest('.img-preview-card');
            const wasPrim = card.hasClass('is-primary');
            const type = card.data('type');
            const idx = parseInt(card.data('index'));

            if (type === 'new') newFiles[idx] = null;
            // For existing: removing the card also removes its hidden kept_images input
            card.remove();
            syncFileInput();

            if (wasPrim) {
                const first = $('#imagePreviewGrid .img-preview-card').first();
                if (first.length) {
                    first.addClass('is-primary');
                    syncPrimary();
                }
            }
        });

        function syncPrimary() {
            const cards = $('#imagePreviewGrid .img-preview-card');
            const primary = cards.filter('.is-primary');
            $('#primaryImageIndex').val(cards.index(primary));
        }

        function syncFileInput() {
            const dt = new DataTransfer();
            newFiles.forEach(f => {
                if (f) dt.items.add(f);
            });
            document.getElementById('productImages').files = dt.files;
        }

        /* ─────────────────────────────────────────
           DYNAMIC VARIATION ROWS
        ───────────────────────────────────────── */
        let varIdx = $('#variationsContainer .variation-row').length;

        function newVariationRow(idx) {
            return `
        <div class="variation-row" data-index="${idx}">
            <button type="button" class="remove-variation">✕</button>
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label mb-1">Attribute</label>
                    <select name="variations[${idx}][name]" class="form-select">
                        <option value="Size">Size</option>
                        <option value="Color">Color</option>
                        <option value="Weight">Weight</option>
                        <option value="Material">Material</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Value</label>
                    <input type="text" name="variations[${idx}][value]" class="form-control" placeholder="e.g. Red, XL">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">SKU</label>
                    <input type="text" name="variations[${idx}][sku]" class="form-control" placeholder="SKU-00${idx+1}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Price</label>
                    <input type="number" name="variations[${idx}][price]" class="form-control" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Stock</label>
                    <input type="number" name="variations[${idx}][stock]" class="form-control" placeholder="0" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="variations[${idx}][status]" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>`;
        }

        $('#addVariation').on('click', () => $('#variationsContainer').append(newVariationRow(varIdx++)));

        $(document).on('click', '.remove-variation', function() {
            if ($('#variationsContainer .variation-row').length <= 1) {
                alert('At least one variation row is required.');
                return;
            }
            $(this).closest('.variation-row').remove();
        });

        /* ─────────────────────────────────────────
           FORM SUBMIT
        ───────────────────────────────────────── */
        $('#itemForm').validate({
            rules: {
                title: 'required',
                brand: 'required',
                categories: 'required',
                sub_categories: 'required',
            },
            messages: {
                title: 'Please enter item name',
                brand: 'Please enter brand name',
                categories: 'Please select a category',
                sub_categories: 'Please select a sub category',
            },
            highlight: el => $(el).addClass('border-danger'),
            unhighlight: el => $(el).removeClass('border-danger'),
        });

        {{-- Replace your saveItem click handler with this --}}

        $('.saveItem').on('click', function() {
            if ($('#itemForm').valid()) {
                showLoader();
                $('#itemForm').ajaxSubmit({
                    dataType: 'json', // jquery.form auto-parses the JSON response
                    success: function(result) {
                        // result is already a plain object — DO NOT use JSON.parse()
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            hideLoader();
                            itemTable.draw();
                            $('#itemForm')[0].reset();
                            $('#imagePreviewGrid').empty();
                            newFiles = [];
                            $('#organizationModal').modal('hide');
                        } else {
                            showNotification(result.message, 'error');
                            hideLoader();
                        }
                    },
                    error: function(xhr) {
                        // Handles Laravel 422 validation errors and 500 server errors
                        var msg = 'Something went wrong.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(
                                    '\n');
                            }
                        }
                        showNotification(msg, 'error');
                        hideLoader();
                    }
                });
            }
        });

    });
</script>
