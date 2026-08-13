<style>
    .category-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 8px;
        max-height: 260px;
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

    .category-checkbox-item input[type="checkbox"]:checked+span {
        font-weight: 600;
        color: #0d6efd;
    }

    .category-checkbox-item:has(input:checked) {
        border-color: #0d6efd;
        background: #e8f0fe;
    }

    .select-all-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: .85rem;
        color: #6c757d;
    }
</style>

<div class="modal-header">
    <h1 class="modal-title fs-5">{{ empty($data['id']) ? 'Add Home Tab' : 'Edit Home Tab' }}</h1>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <form id="homeTabForm" action="{{ route('hometab.save') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}">

        {{-- Tab Name + Order --}}
        <div class="row mb-3">
            <div class="col-8">
                <label class="form-label fw-semibold">
                    Tab Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="tab_name" id="tabNameInput" class="form-control"
                    placeholder="Enter tab name..."
                    value="{{ $data['tab_name'] ?? '' }}">
                <div class="invalid-feedback" id="tabNameError">Please enter tab name.</div>
            </div>
            <div class="col-4">
                <label class="form-label fw-semibold">
                    Order <span class="text-danger">*</span>
                </label>
                <input type="number" name="tab_order" id="tabOrderInput" class="form-control"
                    placeholder="0" min="0" step="1"
                    value="{{ $data['tab_order'] ?? '' }}">
                <div class="invalid-feedback" id="tabOrderError">Please enter a valid order (0 or above).</div>
            </div>
        </div>

        {{-- Icon Name --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">
                Icon Name <span class="text-danger">*</span>
            </label>
            <input type="text" name="icon_name" id="iconNameInput" class="form-control"
                placeholder="e.g. bx bx-home"
                value="{{ $data['icon_name'] ?? '' }}">
            <div class="form-text text-muted">
                Enter a Boxicons class e.g. <code>bx bx-home</code> —
                preview: <i id="iconPreview" class="{{ $data['icon_name'] ?? 'bx bx-smile' }}"></i>
            </div>
            <div class="invalid-feedback" id="iconNameError">Please enter an icon name.</div>
        </div>

        {{-- Background Color --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">
                Background Color 
                <!-- <span class="text-danger">*</span> -->
            </label>
            <div class="d-flex align-items-center gap-2">
                <input type="color" name="bg_color" id="bgColorPicker" class="form-control form-control-color"
                    value="{{ $data['bg_color'] ?? '#ffffff' }}" title="Pick a color">
                <input type="text" id="bgColorHex" name="bg_color" class="form-control" style="max-width:120px;"
                    placeholder="#ffffff"
                    value="{{ $data['bg_color'] ?? '#ffffff' }}">
            </div>
            <div class="invalid-feedback d-block d-none" id="bgColorError">Please enter a valid hex color.</div>
        </div>
        {{-- Categories --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">
                Categories <span class="text-danger">*</span>
            </label>

            <div class="select-all-bar">
                <input type="checkbox" id="selectAllCats" class="form-check-input mt-0">
                <label for="selectAllCats" class="mb-0" style="cursor:pointer;">Select All</label>
                <span class="ms-auto" id="selectedCount">0 selected</span>
            </div>

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

    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="saveHomeTab">
        <i class="fa fa-save"></i> {{ empty($data['id']) ? 'Save' : 'Update' }}
    </button>
</div>

<script>
    $(document).ready(function() {

        // ── Selected count ──────────────────────────────────────────────────
        function updateCount() {
            var count = $('.cat-checkbox:checked').length;
            $('#selectedCount').text(count + ' selected');
            $('#selectAllCats').prop('indeterminate',
                count > 0 && count < $('.cat-checkbox').length
            );
            $('#selectAllCats').prop('checked', count === $('.cat-checkbox').length);
        }

        $('.cat-checkbox').on('change', function() {
            updateCount();
            if ($(this).is(':checked')) $('#categoryError').addClass('d-none');
        });

        $('#selectAllCats').on('change', function() {
            $('.cat-checkbox').prop('checked', $(this).is(':checked'));
            updateCount();
        });

        // Init count on edit
        updateCount();

        // ── Tab name live validation ────────────────────────────────────────
        $('#tabNameInput').on('input', function() {
            $(this).removeClass('is-invalid');
        });

        // ── Order live validation ───────────────────────────────────────────
        $('#tabOrderInput').on('input', function() {
            $(this).removeClass('is-invalid');
        });

        // ── Icon live preview ───────────────────────────────────────────────
        $('#iconNameInput').on('input', function() {
            $('#iconPreview').attr('class', $(this).val().trim() || 'bx bx-smile');
            $(this).removeClass('is-invalid');
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

        // ── Save ────────────────────────────────────────────────────────────
        $('#saveHomeTab').on('click', function() {
            var valid = true;
            var tabName = $('#tabNameInput').val().trim();
            var tabOrder = $('#tabOrderInput').val().trim();
            var iconName = $('#iconNameInput').val().trim();
            var bgColor = $('#bgColorHex').val().trim();
            var hexRegex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
            var catChecked = $('.cat-checkbox:checked').length;

            // Tab name
            if (!tabName) {
                $('#tabNameInput').addClass('is-invalid');
                valid = false;
            } else {
                $('#tabNameInput').removeClass('is-invalid');
            }

            // Order — required, whole number, 0 or above
            if (tabOrder === '' || isNaN(tabOrder) || !Number.isInteger(Number(tabOrder)) || Number(tabOrder) < 0) {
                $('#tabOrderInput').addClass('is-invalid');
                valid = false;
            } else {
                $('#tabOrderInput').removeClass('is-invalid');
            }

            // Icon name
            if (!iconName) {
                $('#iconNameInput').addClass('is-invalid');
                valid = false;
            } else {
                $('#iconNameInput').removeClass('is-invalid');
            }

            // BG color
            if (!hexRegex.test(bgColor)) {
                $('#bgColorError').removeClass('d-none');
                valid = false;
            } else {
                $('#bgColorError').addClass('d-none');
            }

            // Categories
            if (catChecked === 0) {
                $('#categoryError').removeClass('d-none');
                valid = false;
            } else {
                $('#categoryError').addClass('d-none');
            }

            if (!valid) return;

            showLoader();

            $.ajax({
                url: '{{ route('hometab.save') }}',
                type: 'POST',
                data: $('#homeTabForm').serialize(),
                dataType: 'json',
                success: function(result) {
                    hideLoader();
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        bootstrap.Modal.getInstance(
                            document.getElementById('homeTabModal')
                        ).hide();
                        if (window.homeTabTable) window.homeTabTable.fnDraw();
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