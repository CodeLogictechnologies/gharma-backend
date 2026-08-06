<style>
.subcat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    color: #4a4a6a;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.subcat-label .req { color: #ff4d4f; margin-left:2px; }

.subcat-form-control {
    border: 1.5px solid #e0e0ef;
    border-radius: 8px;
    font-size: 14px;
    color: #3a3a5c;
    padding: 9px 14px;
    transition: border-color .2s, box-shadow .2s;
    width: 100%;
    background: #fff;
    appearance: none;
    -webkit-appearance: none;
}
.subcat-form-control:focus {
    outline: none;
    border-color: #696cff;
    box-shadow: 0 0 0 3px rgba(105,108,255,.12);
}
.subcat-form-control.is-invalid { border-color:#ff4d4f !important; }

/* Dropdown wrapper with arrow */
.subcat-select-wrap {
    position: relative;
}
.subcat-select-wrap::after {
    content: '\f078';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    pointer-events: none;
    font-size: 11px;
}

.subcat-btn-save {
    background: #696cff;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 9px 22px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, box-shadow .2s;
}
.subcat-btn-save:hover { background: #5a5de8; box-shadow: 0 4px 12px rgba(105,108,255,.35); }
.subcat-btn-save:disabled { opacity:.65; cursor:not-allowed; }

.subcat-preview-img {
    width: 80px; height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1.5px solid #e0e0ef;
    margin-top: 8px;
}

.subcat-divider {
    width: 1px;
    background: #ebebf5;
    align-self: stretch;
    margin: 0 8px;
}

#subCategoryTable thead th {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: #4a4a6a;
    border-bottom: 1.5px solid #ebebf5;
    padding-bottom: 6px;
    vertical-align: middle;
}
#subCategoryTable thead th input.dt-search-input {
    border: 1.5px solid #e0e0ef;
    border-radius: 6px;
    font-size: 12px;
    padding: 5px 10px;
    margin-top: 6px;
    width: 100%;
    color: #3a3a5c;
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
}
#subCategoryTable thead th input.dt-search-input:focus {
    outline: none;
    border-color: #696cff;
    box-shadow: 0 0 0 3px rgba(105,108,255,.1);
}
#subCategoryTable tbody td {
    font-size: 13.5px;
    color: #3a3a5c;
    vertical-align: middle;
    border-bottom: 1px solid #f4f4fb;
    padding: 10px 12px;
}
#subCategoryTable tbody tr:hover td { background: #f8f8ff; }
</style>

<div class="row g-0">

    {{-- ── LEFT: Form ──────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-4 pe-lg-4">
        <h5 class="fw-bold mb-4" id="subCatFormHeading">Add Sub Category</h5>

        <form id="subCategoryForm" action="{{ route('subcategory.save') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="subCategoryId">

            {{-- Category Dropdown --}}
            <div class="mb-4">
                <label class="subcat-label">Category <span class="req">*</span></label>
                <div class="subcat-select-wrap">
                    <select name="category" id="subCatCategory" class="subcat-form-control">
                        <option value="">-- Select Category --</option>
                        @if (!empty($categories))
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="invalid-feedback d-block" id="subCatCategoryErr" style="font-size:12px;"></div>
            </div>

            {{-- Sub Category Name --}}
            <div class="mb-4">
                <label class="subcat-label">Sub Category Name <span class="req">*</span></label>
                <input type="text" name="title" id="subCatName" class="subcat-form-control" placeholder="e.g. Smartphones">
                <div class="invalid-feedback d-block" id="subCatNameErr" style="font-size:12px;"></div>
            </div>

            {{-- Image --}}
            <div class="mb-4">
                <label class="subcat-label">Image <span class="req">*</span></label>
                <input type="file" name="image" id="subCatImage" class="subcat-form-control" accept="image/jpeg,image/jpg,image/png">
                <img id="subCatPreview" src="{{ asset('no-image.jpg') }}" class="subcat-preview-img" alt="Preview">
                <div class="mt-1" style="font-size:11px;color:#aaa;">JPG/PNG · Recommended 300×300px</div>
                <div class="invalid-feedback d-block" id="subCatImageErr" style="font-size:12px;"></div>
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-2 mt-2">
                <button type="button" id="saveSubCatBtn" class="subcat-btn-save">
                    <i class="fas fa-save me-1"></i> Save
                </button>
                <button type="button" id="resetSubCatBtn"
                        style="background:transparent;border:1.5px solid #e0e0ef;border-radius:8px;padding:9px 18px;font-size:14px;color:#6c757d;cursor:pointer;">
                    Reset
                </button>
            </div>
        </form>
    </div>

    {{-- Vertical divider --}}
    <div class="d-none d-lg-block subcat-divider"></div>

    {{-- ── RIGHT: Table ─────────────────────────────────────────────── --}}
    <div class="col-12 col-lg mt-4 mt-lg-0 ps-lg-4">
        <div class="table-responsive">
            <table id="subCategoryTable" class="table w-100" style="border-collapse:separate; border-spacing:0;">
                <thead>
                    <tr>
                        <th style="width:55px;">S.NO</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th style="width:80px;">Image</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(function () {

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ── Image preview ─────────────────────────────────────────────────
    $(document).on('change', '#subCatImage', function () {
        if (this.files[0]) {
            const r = new FileReader();
            r.onload = e => $('#subCatPreview').attr('src', e.target.result);
            r.readAsDataURL(this.files[0]);
        }
    });

    // ── DataTable ─────────────────────────────────────────────────────
    var subCatTable = $('#subCategoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('subcategory.list') }}',
            type: 'POST',
            data: d => { d._token = $('meta[name="csrf-token"]').attr('content'); }
        },
        columns: [
            { data: 'sno',          orderable: false, width: '55px' },
            { data: 'category_name' },
            { data: 'title' },
            { data: 'image',        orderable: false, width: '80px' },
            { data: 'action',       orderable: false, width: '100px' }
        ],
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        dom: '<"d-flex align-items-center justify-content-between mb-3"l>t<"d-flex align-items-center justify-content-between mt-3"ip>',
        language: {
            emptyTable: '<span style="color:#aaa;font-size:13px;">No data available.</span>',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: { first: '«', previous: '‹', next: '›', last: '»' }
        },
        initComplete: function () {
            this.api().columns([1, 2]).every(function () {
                var col = this;
                var hdr = $(col.header()).text().trim();
                $('<input class="dt-search-input" type="text" placeholder="' + hdr + '...">')
                    .appendTo($(col.header()).empty())
                    .on('keyup change', function () { col.search(this.value).draw(); });
            });
        }
    });

    // ── Validation ────────────────────────────────────────────────────
    function clearSubErrors() {
        $('#subCatCategoryErr, #subCatNameErr, #subCatImageErr').text('');
        $('#subCatCategory, #subCatName, #subCatImage').removeClass('is-invalid');
    }
    function showSubError(fieldId, errId, msg) {
        $('#' + errId).text(msg);
        $('#' + fieldId).addClass('is-invalid');
    }
    function validateSubCatForm() {
        clearSubErrors();
        var ok = true;
        if (!$('#subCatCategory').val()) {
            showSubError('subCatCategory', 'subCatCategoryErr', 'Please select a category.');
            ok = false;
        }
        if (!$('#subCatName').val().trim()) {
            showSubError('subCatName', 'subCatNameErr', 'Sub category name is required.');
            ok = false;
        } else if ($('#subCatName').val().trim().length < 3) {
            showSubError('subCatName', 'subCatNameErr', 'Minimum 3 characters required.');
            ok = false;
        }
        if (!$('#subCategoryId').val() && !$('#subCatImage')[0].files.length) {
            showSubError('subCatImage', 'subCatImageErr', 'Please select an image.');
            ok = false;
        }
        return ok;
    }

    // ── Save / Update ─────────────────────────────────────────────────
    $(document).on('click', '#saveSubCatBtn', function () {
        if (!validateSubCatForm()) return;
        // var $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

        $('#subCategoryForm').ajaxSubmit({
            contentType: false,
            processData: false,
            type: 'POST',
            success: function (res) {
                var r = typeof res === 'string' ? JSON.parse(res) : res;
                showNotification(r.message, r.type);
                if (r.type === 'success') {
                    resetSubCatForm();
                    subCatTable.ajax.reload(null, false);
                }
            },
            error: function () { showNotification('Something went wrong.', 'error'); },
            complete: function () { $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save'); }
        });
    });

    // ── Reset ─────────────────────────────────────────────────────────
    function resetSubCatForm() {
        $('#subCategoryForm')[0].reset();
        $('#subCategoryId').val('');
        $('#subCatPreview').attr('src', '{{ asset('no-image.jpg') }}');
        $('#subCatFormHeading').text('Add Sub Category');
        $('#saveSubCatBtn').html('<i class="fas fa-save me-1"></i> Save');
        clearSubErrors();
    }
    $(document).on('click', '#resetSubCatBtn', resetSubCatForm);

    // ── Edit ──────────────────────────────────────────────────────────
    $(document).on('click', '.editSubCategory', function () {
        $('#subCategoryId').val($(this).data('id'));
        $('#subCatName').val($(this).data('title'));
        $('#subCatCategory').val($(this).data('category'));
        $('#subCatFormHeading').text('Edit Sub Category');
        $('#saveSubCatBtn').html('<i class="fas fa-save me-1"></i> Update');
        var img = $(this).data('image');
        $('#subCatPreview').attr('src', img ? '/storage/subcategories/' + img : '{{ asset('no-image.jpg') }}');
        $('#subCatImage').val('');
        clearSubErrors();
        $('html,body').animate({ scrollTop: 0 }, 250);
    });

    // ── Delete ────────────────────────────────────────────────────────
    var delSubId = null;
    $(document).on('click', '.deleteSubCategory', function () {
        delSubId = $(this).data('id');
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
    $(document).off('click.subDel').on('click.subDel', '#confirmDelete', function () {
        if (!delSubId) return;
        var $btn = $(this).prop('disabled', true).html('Deleting...');
        $.post('{{ route('subcategory.delete') }}', { id: delSubId, _token: '{{ csrf_token() }}' })
            .done(function (res) {
                var r = typeof res === 'string' ? JSON.parse(res) : res;
                showNotification(r.message, r.type);
                if (r.type === 'success') subCatTable.ajax.reload(null, false);
            })
            .fail(function () { showNotification('Delete failed.', 'error'); })
            .always(function () {
                delSubId = null;
                $btn.prop('disabled', false).html('Yes, Delete');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide();
            });
    });
});
</script>
