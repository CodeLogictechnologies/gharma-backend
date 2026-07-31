@extends('layouts.main')
@section('title', 'Categories')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.image-upload-box {
    width: 125px;
    height: 125px;
    border: 2px dashed #a0aec0;
    border-radius: 12px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}
#img_preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
    border-radius: 10px;
}
#cameraOverlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px;
    border-radius: 0 0 10px 10px;
}
</style>

@section('content')

{{-- Delete Modal --}}
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
        <div class="tab-content mt-4" id="nav-tabContent">
            <div class="row g-4">

                {{-- ── LEFT: Form ──────────────────────────────────────── --}}
                <div class="col-12 col-lg-4">

                    <h5 class="mb-3" id="formHeading">Add Category</h5>

                    <form action="{{ route('category.save') }}" method="POST" id="categoryForm"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="catId">

                        {{-- Title --}}
                        <div class="mb-3">
                            <label class="form-label" for="catName">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" id="catName"
                                   placeholder="e.g. Electronics">
                        </div>

                        {{-- Category (top level) --}}
                        <div class="mb-3">
                            <label class="form-label" for="catCategory">Parent Category</label>
                            <select name="category_id" id="catCategory" class="form-control">
                                <option value="">-- None (Top Level) --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional. Assign a category to make this a sub-category.</small>
                        </div>

                        {{-- Sub Category (children of selected Category) --}}
                        <div class="mb-3">
                            <label class="form-label" for="catSubCategory">Category</label>
                            <select name="subcategory_id" id="catSubCategory" class="form-control">
                                <option value="">-- None (Top Level) --</option>
                            </select>
                            <small class="text-muted">Optional. Only enabled once a Parent Category is selected — pick one to make this a sub-sub-category.</small>
                        </div>

                        {{-- Image --}}
                        <div class="mb-3">
                            <label class="form-label">Image <span class="text-danger">*</span></label>

                            <input type="file" id="catImage" name="image" accept="image/*"
                                   style="display:none;">

                            <div class="image-upload-box" id="imageUploadBox">
                                <img id="img_preview" src="{{ asset('no-image.jpg') }}">
                                <div id="cameraOverlay">
                                    <i class="fas fa-camera" style="color:#fff;font-size:18px;"></i>
                                </div>
                            </div>

                            <small class="text-muted d-block mt-1">
                                Accepted: jpg/jpeg/png. Max 2MB.
                            </small>
                        </div>

                        {{-- Buttons --}}
                        <button type="button" class="btn btn-primary saveCategory">
                            <i class="fa fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-1" id="resetCatBtn">
                            Reset
                        </button>
                    </form>
                </div>

                {{-- ── RIGHT: Table ────────────────────────────────────── --}}
                <div class="col-12 col-lg-8" style="flex:1;">
                    <div class="table-responsive text-nowrap">
                        <table class="table" id="categoryTable">
                            <thead class="table-light">
                                <tr class="align-middle">
                                    <th>S.No</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Parent</th>
                                    <th>Photo</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0"></tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- /row --}}
        </div>
    </div>
</div>

@endsection

@section('main-scripts')
<script>
var catTable;

$(document).ready(function () {

    // ── Image upload box ──────────────────────────────────────────
    $('#imageUploadBox').on('click', function () {
        $('#catImage').click();
    });
    $('#catImage').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#img_preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // ── CSRF ──────────────────────────────────────────────────────
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── Cascading dropdown: Category → Sub Category ─────────────────
    // Loads the Sub Category options for whatever Category is selected.
    // `excludeId` hides the item currently being edited from the list
    // (so it can never be picked as its own parent).
    function loadSubCategories(categoryId, excludeId, selectedValue) {
        if (!categoryId) {
            $('#catSubCategory').html('<option value="">-- None (Top Level) --</option>').prop('disabled', true);
            return;
        }
        $('#catSubCategory').prop('disabled', true);
        $.get('{{ route('category.subcategories') }}', { category_id: categoryId, exclude: excludeId }, function (res) {
            $('#catSubCategory').html(res.options).prop('disabled', false);
            if (selectedValue) {
                $('#catSubCategory').val(selectedValue);
            }
        });
    }

    $(document).on('change', '#catCategory', function () {
        var excludeId = $('#catId').val() || null;
        loadSubCategories($(this).val(), excludeId, null);
    });

    // Sub Category starts disabled until a Category is chosen
    $('#catSubCategory').prop('disabled', true);

    // ── DataTable ─────────────────────────────────────────────────
    catTable = $('#categoryTable').dataTable({
        sPaginationType: 'full_numbers',
        bSearchable: false,
        language: {
            paginate: {
                first:    '<i class="bx bx-chevrons-left"></i>',
                previous: '<i class="bx bx-chevron-left"></i>',
                next:     '<i class="bx bx-chevron-right"></i>',
                last:     '<i class="bx bx-chevrons-right"></i>'
            }
        },
        lengthMenu:    [[5, 10, 30, 50, -1], [5, 10, 30, 50, 'All']],
        iDisplayLength: 10,
        sDom:          'ltipr',
        bAutoWidth:    false,
        aaSorting:     [[0, 'desc']],
        bProcessing:   true,
        bServerSide:   true,
        ajax: {
            url:  '{{ route('category.list') }}',
            type: 'POST',
            data: function (d) {
                d._token = '{{ csrf_token() }}';
            }
        },
        oLanguage: {
            sEmptyTable: "<p class='no_data_message'>No data available.</p>"
        },
        aoColumnDefs: [{ bSortable: false, aTargets: [2, 3, 4, 5] }],
        aoColumns: [
            { data: 'sno'         },
            { data: 'title'       },
            { data: 'type'        },
            { data: 'parent_name' },
            { data: 'image'       },
            { data: 'action'      }
        ],
        initComplete: function () {
            this.api().columns([1]).every(function () {
                var col = this;
                var hdr = $(col.header()).text().trim();
                $('<input type="text" class="form-control" placeholder="' + hdr + '..." style="width:100%;margin-top:4px;">')
                    .appendTo($(col.header()).empty())
                    .on('keyup change', function () { col.search(this.value).draw(); });
            });
        }
    });

    // ── Save ──────────────────────────────────────────────────────
    $('.saveCategory').off('click').on('click', function () {
        var name = $('#catName').val().trim();
        if (!name) {
            showNotification('Title is required.', 'error');
            $('#catName').addClass('border-danger').focus();
            return;
        }
        var isNew = !$('#catId').val();
        if (isNew && !$('#catImage')[0].files.length) {
            showNotification('Please select an image.', 'error');
            return;
        }

        var $btn = //$(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        let formData = new FormData(document.getElementById('categoryForm'));

        $.ajax({
            url:         '{{ route('category.save') }}',
            type:        'POST',
            data:        formData,
            contentType: false,
            processData: false,
            success: function (response) {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                showNotification(result.message, result.type);
                if (result.type === 'success') {
                    resetForm();
                    catTable.fnDraw();
                    // Refresh the Category dropdown (a new top-level category may exist now)
                    if (result.categoryOptions) {
                        $('#catCategory').html(result.categoryOptions);
                    }
                }
            },
            error: function () { showNotification('Something went wrong!', 'error'); },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            }
        });
    });

    // ── Reset ─────────────────────────────────────────────────────
    function resetForm() {
        $('#categoryForm')[0].reset();
        $('#catId').val('');
        $('#img_preview').attr('src', '{{ asset('no-image.jpg') }}');
        $('#formHeading').text('Add Category');
        $('.saveCategory').html('<i class="fa fa-save"></i> Save');
        $('#catName').removeClass('border-danger');
        $('#catSubCategory').html('<option value="">-- None (Top Level) --</option>').prop('disabled', true);
    }
    $('#resetCatBtn').on('click', resetForm);

    // ── Edit ──────────────────────────────────────────────────────
    $(document).on('click', '.editCategory', function (e) {
        e.preventDefault();
        var id            = $(this).data('id');
        var title         = $(this).data('title');
        var image         = $(this).data('image');
        var categoryId    = $(this).data('category_id');
        var subcategoryId = $(this).data('subcategory_id');

        $('#catId').val(id);
        $('#catName').val(title);
        $('#formHeading').text('Edit Category');
        $('.saveCategory').html('<i class="fas fa-save"></i> Update');

        // Reload the Category dropdown excluding self, then set its value
        $.get('{{ route('category.top-level') }}', { exclude: id }, function (res) {
            $('#catCategory').html(res.options);
            if (categoryId) {
                $('#catCategory').val(categoryId);
                // Then load Sub Category options for that category, excluding self
                loadSubCategories(categoryId, id, subcategoryId);
            } else {
                $('#catSubCategory').html('<option value="">-- None (Top Level) --</option>').prop('disabled', true);
            }
        });

        $('#img_preview').attr('src', image ? '/storage/categories/' + image : '{{ asset('no-image.jpg') }}');

        // Reset file input
        var oldInput = $('#catImage');
        var newInput = oldInput.clone(true);
        oldInput.replaceWith(newInput);
        $('#catImage').on('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => $('#img_preview').attr('src', e.target.result);
                reader.readAsDataURL(file);
            }
        });

        $('html,body').animate({ scrollTop: $('#categoryForm').offset().top - 80 }, 250);
    });

    // ── Delete ────────────────────────────────────────────────────
    var deleteId = null;

    $(document).on('click', '.deleteCategory', function (e) {
        e.preventDefault();
        deleteId = $(this).data('id');
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });

    $('#confirmDelete').on('click', function () {
        if (!deleteId) return;
        var $btn = $(this).prop('disabled', true).html('Deleting...');

        $.post('{{ route('category.delete') }}', { id: deleteId, _token: '{{ csrf_token() }}' })
            .done(function (response) {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                showNotification(result.message, result.type === 'success' ? 'success' : 'error');
                if (result.type === 'success') {
                    catTable.fnDraw();
                    // Refresh the Category dropdown (a top-level item may have been removed)
                    $.get('{{ route('category.top-level') }}', function (res2) {
                        $('#catCategory').html(res2.options);
                    });
                }
            })
            .fail(function () { showNotification('Delete failed. Please try again.', 'error'); })
            .always(function () {
                deleteId = null;
                $btn.prop('disabled', false).html('Yes, Delete');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide();
            });
    });

});
</script>
@endsection