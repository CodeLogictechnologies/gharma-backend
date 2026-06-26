<style>
.cat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    color: #4a4a6a;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.cat-label .req { color: #ff4d4f; margin-left:2px; }

.cat-form-control {
    border: 1.5px solid #e0e0ef;
    border-radius: 8px;
    font-size: 14px;
    color: #3a3a5c;
    padding: 9px 14px;
    transition: border-color .2s, box-shadow .2s;
    width: 100%;
    background: #fff;
}
.cat-form-control:focus {
    outline: none;
    border-color: #696cff;
    box-shadow: 0 0 0 3px rgba(105,108,255,.12);
}
.cat-form-control.is-invalid { border-color:#ff4d4f !important; }

.cat-btn-save {
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
.cat-btn-save:hover { background: #5a5de8; box-shadow: 0 4px 12px rgba(105,108,255,.35); }
.cat-btn-save:disabled { opacity:.65; cursor:not-allowed; }

.cat-preview-img {
    width: 80px; height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1.5px solid #e0e0ef;
    margin-top: 8px;
}

/* Divider between form and table */
.cat-divider {
    width: 1px;
    background: #ebebf5;
    align-self: stretch;
    margin: 0 8px;
}

/* Table styles */
#categoryTable_wrapper .dataTables_length select {
    border: 1.5px solid #e0e0ef;
    border-radius: 6px;
    font-size: 13px;
    padding: 4px 8px;
    color: #3a3a5c;
}
#categoryTable thead th {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: #4a4a6a;
    border-bottom: 1.5px solid #ebebf5;
    padding-bottom: 6px;
    vertical-align: middle;
}
#categoryTable thead th input.dt-search-input {
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
#categoryTable thead th input.dt-search-input:focus {
    outline: none;
    border-color: #696cff;
    box-shadow: 0 0 0 3px rgba(105,108,255,.1);
}
#categoryTable tbody td {
    font-size: 13.5px;
    color: #3a3a5c;
    vertical-align: middle;
    border-bottom: 1px solid #f4f4fb;
    padding: 10px 12px;
}
#categoryTable tbody tr:hover td { background: #f8f8ff; }

.cat-action-btn { font-size:15px; padding:0 5px; }
.dataTables_info { font-size:13px; color:#888; }
.dataTables_paginate .paginate_button {
    border-radius:6px !important;
    font-size:13px !important;
    padding: 4px 10px !important;
}
.dataTables_paginate .paginate_button.current {
    background: #696cff !important;
    color: #fff !important;
    border-color: #696cff !important;
}
</style>

<div class="row g-0">

    {{-- ── LEFT: Form ──────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-4 pe-lg-4">
        <h5 class="fw-bold mb-4" style="font-size:16px; color:#2d2d2d;" id="categoryFormHeading">Add Category</h5>

        <form id="categoryForm" action="{{ route('category.save') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="categoryId">

            {{-- Category Name --}}
            <div class="mb-4">
                <label class="cat-label">Category Name <span class="req">*</span></label>
                <input type="text" name="name" id="categoryName" class="cat-form-control" placeholder="e.g. Electronics">
                <div class="invalid-feedback d-block" id="categoryNameErr" style="font-size:12px;"></div>
            </div>

            {{-- Image --}}
            <div class="mb-4">
                <label class="cat-label">Image <span class="req">*</span></label>
                <input type="file" name="image" id="categoryImage" class="cat-form-control" accept="image/jpeg,image/jpg,image/png">
                <img id="categoryPreview" src="{{ asset('no-image.jpg') }}" class="cat-preview-img" alt="Preview">
                <div class="mt-1" style="font-size:11px;color:#aaa;">JPG/PNG · Recommended 300×300px</div>
                <div class="invalid-feedback d-block" id="categoryImageErr" style="font-size:12px;"></div>
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-2 mt-2">
                <button type="button" id="saveCategoryBtn" class="cat-btn-save">
                    <i class="fas fa-save me-1"></i> Save
                </button>
                <button type="button" id="resetCategoryBtn"
                        style="background:transparent;border:1.5px solid #e0e0ef;border-radius:8px;padding:9px 18px;font-size:14px;color:#6c757d;cursor:pointer;">
                    Reset
                </button>
            </div>
        </form>
    </div>

    {{-- Vertical divider (desktop only) --}}
    <div class="d-none d-lg-block cat-divider"></div>

    {{-- ── RIGHT: Table ─────────────────────────────────────────────── --}}
    <div class="col-12 col-lg mt-4 mt-lg-0 ps-lg-4">
        <div class="d-flex align-items-center justify-content-between mb-3" id="categoryTableTopBar">
            {{-- DataTables length control injects here --}}
        </div>
        <div class="table-responsive">
            <table id="categoryTable" class="table w-100" style="border-collapse:separate; border-spacing:0;">
                <thead>
                    <tr>
                        <th style="width:60px;">S.NO</th>
                        <th>Category Name</th>
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

    // ── Image preview ────────────────────────────────────────────────
    $(document).on('change', '#categoryImage', function () {
        if (this.files[0]) {
            const r = new FileReader();
            r.onload = e => $('#categoryPreview').attr('src', e.target.result);
            r.readAsDataURL(this.files[0]);
        }
    });

    // ── DataTable ────────────────────────────────────────────────────
    var catTable = $('#categoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('category.list') }}',
            type: 'POST',
            data: d => { d._token = $('meta[name="csrf-token"]').attr('content'); }
        },
        columns: [
            { data: 'sno',    orderable: false, width: '60px' },
            { data: 'title' },
            { data: 'image',  orderable: false, width: '80px' },
            { data: 'action', orderable: false, width: '100px' }
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
            this.api().columns([1]).every(function () {
                var col = this;
                var hdr = $(col.header()).text().trim();
                $('<input class="dt-search-input" type="text" placeholder="' + hdr + '...">')
                    .appendTo($(col.header()).empty())
                    .on('keyup change', function () { col.search(this.value).draw(); });
            });
        }
    });

    // ── Validation helper ────────────────────────────────────────────
    function clearErrors() {
        $('#categoryNameErr, #categoryImageErr').text('');
        $('#categoryName, #categoryImage').removeClass('is-invalid');
    }
    function showError(field, msg) {
        $('#' + field + 'Err').text(msg);
        $('#' + field).addClass('is-invalid');
    }
    function validateCategoryForm() {
        clearErrors();
        var ok = true;
        if (!$('#categoryName').val().trim()) {
            showError('categoryName', 'Category name is required.');
            ok = false;
        } else if ($('#categoryName').val().trim().length < 3) {
            showError('categoryName', 'Minimum 3 characters required.');
            ok = false;
        }
        if (!$('#categoryId').val() && !$('#categoryImage')[0].files.length) {
            showError('categoryImage', 'Please select an image.');
            ok = false;
        }
        return ok;
    }

    // ── Save / Update ────────────────────────────────────────────────
    $(document).on('click', '#saveCategoryBtn', function () {
        if (!validateCategoryForm()) return;

        var $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

        $('#categoryForm').ajaxSubmit({
            contentType: false,
            processData: false,
            type: 'POST',
            success: function (res) {
                var r = typeof res === 'string' ? JSON.parse(res) : res;
                showNotification(r.message, r.type);
                if (r.type === 'success') {
                    resetCategoryForm();
                    catTable.ajax.reload(null, false);
                }
            },
            error: function () { showNotification('Something went wrong.', 'error'); },
            complete: function () { $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save'); }
        });
    });

    // ── Reset ────────────────────────────────────────────────────────
    function resetCategoryForm() {
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#categoryPreview').attr('src', '{{ asset('no-image.jpg') }}');
        $('#categoryFormHeading').text('Add Category');
        $('#saveCategoryBtn').html('<i class="fas fa-save me-1"></i> Save');
        clearErrors();
    }
    $(document).on('click', '#resetCategoryBtn', resetCategoryForm);

    // ── Edit ─────────────────────────────────────────────────────────
    $(document).on('click', '.editCategory', function () {
        $('#categoryId').val($(this).data('id'));
        $('#categoryName').val($(this).data('title'));
        $('#categoryFormHeading').text('Edit Category');
        $('#saveCategoryBtn').html('<i class="fas fa-save me-1"></i> Update');
        var img = $(this).data('image');
        $('#categoryPreview').attr('src', img ? '/storage/categories/' + img : '{{ asset('no-image.jpg') }}');
        $('#categoryImage').val('');
        clearErrors();
        $('html,body').animate({ scrollTop: 0 }, 250);
    });

    // ── Delete ───────────────────────────────────────────────────────
    var delCatId = null;
    $(document).on('click', '.deleteCategory', function () {
        delCatId = $(this).data('id');
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
    $(document).off('click.catDel').on('click.catDel', '#confirmDelete', function () {
        if (!delCatId) return;
        var $btn = $(this).prop('disabled', true).html('Deleting...');
        $.post('{{ route('category.delete') }}', { id: delCatId, _token: '{{ csrf_token() }}' })
            .done(function (res) {
                var r = typeof res === 'string' ? JSON.parse(res) : res;
                showNotification(r.message, r.type);
                if (r.type === 'success') catTable.ajax.reload(null, false);
            })
            .fail(function () { showNotification('Delete failed.', 'error'); })
            .always(function () {
                delCatId = null;
                $btn.prop('disabled', false).html('Yes, Delete');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide();
            });
    });
});
</script>