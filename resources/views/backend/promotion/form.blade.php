<style>
    .category-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 8px;
        max-height: 220px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        background: #f8f9fa;
    }

    .category-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 6px 10px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
    }

    .category-checkbox-item:hover {
        border-color: #0d6efd;
        background: #e8f0fe;
    }

    .category-checkbox-item:has(input:checked) {
        border-color: #0d6efd;
        background: #e8f0fe;
    }
</style>

<div class="modal-header">
    <h1 class="modal-title fs-5">{{ empty($data['id']) ? 'Add Promotion' : 'Edit Promotion' }}</h1>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <form id="promotionForm" action="{{ route('promotion.save') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}">

        {{-- Name --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">
                Banner Name <span class="text-danger">*</span>
            </label>
            <input type="text" name="name" id="nameInput" class="form-control"
                placeholder="e.g. Dashain Sale"
                value="{{ $data['name'] ?? '' }}">
            <div class="invalid-feedback" id="nameError">Please enter a banner name.</div>
        </div>

        {{-- Image --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Banner Image</label>
            <input type="file" name="image" id="imageInput" class="form-control" accept=".jpg,.jpeg,.png">
            <div class="form-text text-muted">JPG or PNG, max 2MB.</div>
            <div class="mt-2">
                <img id="imagePreview" src="{{ $data['image_url'] ?? '' }}"
                    style="max-width:120px;max-height:120px;border-radius:6px;{{ empty($data['image_url']) ? 'display:none;' : '' }}">
            </div>
        </div>

        {{-- Background Color --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Background Color</label>
            <div class="d-flex align-items-center gap-2">
                <input type="color" id="bgColorPicker" class="form-control form-control-color"
                    value="{{ $data['bg_color'] ?? '#ffffff' }}" title="Pick a color">
                <input type="text" id="bgColorHex" name="bg_color" class="form-control" style="max-width:120px;"
                    placeholder="#ffffff"
                    value="{{ $data['bg_color'] ?? '#ffffff' }}">
            </div>
            <div class="invalid-feedback d-block d-none" id="bgColorError">Please enter a valid hex color.</div>
        </div>

        {{-- Sort Order --}}
        <div class="mb-3">
            <label class="form-label fw-semibold" for="sortOrderInput">Sort Order</label>
            <input type="number" name="sort_order" id="sortOrderInput" class="form-control"
                min="0" max="9999" step="1" style="max-width:120px;"
                placeholder="0"
                value="{{ $data['sort_order'] ?? 0 }}">
            <div class="form-text text-muted">Lower numbers appear first in the carousel.</div>
            <div class="invalid-feedback d-block d-none" id="sortOrderError">Please enter a whole number between 0 and 9999.</div>
        </div>

        {{-- Applies To --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Applies To <span class="text-danger">*</span></label>
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="applies_to" id="appliesToCategory"
                        value="category" {{ ($data['applies_to'] ?? 'category') === 'category' ? 'checked' : '' }}>
                    <label class="form-check-label" for="appliesToCategory">Category</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="applies_to" id="appliesToItem"
                        value="item" {{ ($data['applies_to'] ?? '') === 'item' ? 'checked' : '' }}>
                    <label class="form-check-label" for="appliesToItem">Specific Items</label>
                </div>
            </div>
        </div>

        {{-- Categories --}}
        <div class="mb-3" id="categoryBlock">
            <label class="form-label fw-semibold">Categories <span class="text-danger">*</span></label>
            <div class="category-checkbox-grid" id="categoryGrid">
                @foreach ($categories as $cat)
                <label class="category-checkbox-item">
                    <input type="checkbox"
                        name="category_ids[]"
                        value="{{ $cat->id }}"
                        class="form-check-input mt-0 cat-checkbox"
                        {{ in_array($cat->id, $data['category_ids'] ?? []) ? 'checked' : '' }}>
                    <span>{{ $cat->title }}</span>
                </label>
                @endforeach
            </div>
            <div class="text-danger mt-1 d-none" id="categoryError" style="font-size:.85rem;">
                Please select at least one category.
            </div>
        </div>

        {{-- Items --}}
        <div class="mb-3 d-none" id="itemBlock">
            <label class="form-label fw-semibold">Items <span class="text-danger">*</span></label>
            <select name="item_ids[]" id="itemSelect" class="form-select" multiple style="width:100%;">
                @foreach ($items as $item)
                <option value="{{ $item->itemid }}"
                    {{ in_array($item->itemid, $data['item_ids'] ?? []) ? 'selected' : '' }}>
                    {{ $item->itemname }}
                </option>
                @endforeach
            </select>
            <div class="text-danger mt-1 d-none" id="itemError" style="font-size:.85rem;">
                Please select at least one item.
            </div>
        </div>

    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="savePromotion">
        <i class="fa fa-save"></i> {{ empty($data['id']) ? 'Save' : 'Update' }}
    </button>
</div>

<script>
    $(document).ready(function() {

        $('#itemSelect').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Select Items --',
            width: '100%',
            dropdownParent: $('#promotionModalContent'),
        });

        // ── Toggle applies_to blocks ────────────────────────────────────────
        function toggleAppliesTo() {
            if ($('input[name="applies_to"]:checked').val() === 'item') {
                $('#categoryBlock').addClass('d-none');
                $('#itemBlock').removeClass('d-none');
            } else {
                $('#itemBlock').addClass('d-none');
                $('#categoryBlock').removeClass('d-none');
            }
        }
        $('input[name="applies_to"]').on('change', toggleAppliesTo);
        toggleAppliesTo();

        // ── Name live validation ────────────────────────────────────────────
        $('#nameInput').on('input', function() {
            $(this).removeClass('is-invalid');
        });

        // ── Image preview ───────────────────────────────────────────────────
        $('#imageInput').on('change', function() {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        });

        // ── Color picker <-> hex input sync ────────────────────────────────
        $('#bgColorPicker').on('input', function() {
            $('#bgColorHex').val($(this).val());
            $('#bgColorError').addClass('d-none');
        });

        $('#bgColorHex').on('input', function() {
            var hex = $(this).val().trim();
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex)) {
                $('#bgColorPicker').val(hex);
                $('#bgColorError').addClass('d-none');
            }
        });

        $('.cat-checkbox').on('change', function() {
            if ($(this).is(':checked')) $('#categoryError').addClass('d-none');
        });

        $('#sortOrderInput').on('input', function() {
            $(this).removeClass('is-invalid');
            $('#sortOrderError').addClass('d-none');
        });

        // ── Save ────────────────────────────────────────────────────────────
        $('#savePromotion').on('click', function() {
            var valid = true;
            var name = $('#nameInput').val().trim();
            var bgColor = $('#bgColorHex').val().trim();
            var hexRegex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
            var appliesTo = $('input[name="applies_to"]:checked').val();
            var sortOrderRaw = $('#sortOrderInput').val().trim();

            if (!name) {
                $('#nameInput').addClass('is-invalid');
                valid = false;
            } else {
                $('#nameInput').removeClass('is-invalid');
            }

            if (bgColor && !hexRegex.test(bgColor)) {
                $('#bgColorError').removeClass('d-none');
                valid = false;
            } else {
                $('#bgColorError').addClass('d-none');
            }

            if (sortOrderRaw !== '' &&
                (!/^\d+$/.test(sortOrderRaw) || parseInt(sortOrderRaw, 10) > 9999)) {
                $('#sortOrderInput').addClass('is-invalid');
                $('#sortOrderError').removeClass('d-none');
                valid = false;
            } else {
                $('#sortOrderInput').removeClass('is-invalid');
                $('#sortOrderError').addClass('d-none');
            }

            if (appliesTo === 'category') {
                if ($('.cat-checkbox:checked').length === 0) {
                    $('#categoryError').removeClass('d-none');
                    valid = false;
                } else {
                    $('#categoryError').addClass('d-none');
                }
            }

            if (appliesTo === 'item') {
                if (!$('#itemSelect').val() || $('#itemSelect').val().length === 0) {
                    $('#itemError').removeClass('d-none');
                    valid = false;
                } else {
                    $('#itemError').addClass('d-none');
                }
            }

            if (!valid) return;

            showLoader();

            var formData = new FormData($('#promotionForm')[0]);

            $.ajax({
                url: '{{ route('promotion.save') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(result) {
                    hideLoader();
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        bootstrap.Modal.getInstance(
                            document.getElementById('promotionModal')
                        ).hide();
                        if (window.promotionTable) window.promotionTable.fnDraw();
                    } else {
                        showNotification(result.message, 'error');
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    var msg = 'Something went wrong.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showNotification(msg, 'error');
                }
            });
        });
    });
</script>
