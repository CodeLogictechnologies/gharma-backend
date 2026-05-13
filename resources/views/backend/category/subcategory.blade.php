<!-- jQuery first (KEEP THIS ORDER) -->

<!-- ✅ FIXED: Correct jQuery Validation Plugin CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

<!-- jQuery Form Plugin for ajaxSubmit -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Font Awesome 6 (Recommended - Modern) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- OR Font Awesome 5 (if you prefer older version) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@yield('main-scripts')
<div class="row g-4">
    <div class="col-12 col-lg-4">

        <h5 class="mb-3">Add Sub Category</h5>
        <form action="{{ route('subcategory.save') }}" method="POST" id="subCategoryForm" enctype="multipart/form-data">

            <div class="mb-3">
                <input type="hidden" name="id" value="" id="id">
                <label class="form-label" for="name">Sub Category Name</label>
                <input type="text" class="form-control" name="title" id="name" placeholder="Example: " />
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="thumbnail_image" name="image" accept="image/*"
                    @if (empty($id)) required @endif>
                <div class="mt-2">

                    <img style="width: 125px !important" src="{{ asset('/no-image.jpg') }}" id="img_preview"
                        class="_image">
                </div>
                <small class="text-muted">Accepted formats: jpg/jpeg/png. Recommended size:
                    300x475px.</small>
            </div>

            <button type="button" class="btn btn-primary saveSubCategory">Save</button>
        </form>

    </div>
    <div class="col-12 col-lg-8" style="flex: 1;">
        <div class="table-header mb-3 d-flex justify-content-between align-items-center">
            <div class="dt-length">
                <label class="d-flex align-items-center gap-2">

                </label>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <div id="datatable-basic_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">

                <div class="dataTables_length" id="datatable-basic_length">

                    <table class="table" id="subCategoryTable" aria-describedby="datatable-basic_info">
                        <thead class="table-light">
                            <tr class="align-middle">
                                <th data-dt-column="1" class="">
                                    S.No
                                </th>
                                <th>Sub Category Name</th>
                                <th class="">Image</th>
                                <th class="">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- delete -->

<script>
    var subCategoryTable;

    $(document).ready(function() {
        $('#thumbnail_image').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#img_preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
        // ── CSRF setup ────────────────────────────────────────────────
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ── DataTable ─────────────────────────────────────────────────
        subCategoryTable = $('#subCategoryTable').dataTable({
            sPaginationType: 'full_numbers',
            bSearchable: false,

            language: {
                paginate: {
                    first: '<i class="bx bx-chevrons-left"></i>',
                    previous: '<i class="bx bx-chevron-left"></i>',
                    next: '<i class="bx bx-chevron-right"></i>',
                    last: '<i class="bx bx-chevrons-right"></i>'
                }
            },

            lengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, 'All']
            ],

            iDisplayLength: 5,
            sDom: 'ltipr',
            bAutoWidth: false,

            aaSorting: [
                [0, 'desc']
            ],

            bProcessing: true,
            bServerSide: true,

            // ✅ KEEP ONLY THIS AJAX
            ajax: {
                url: '{{ route('subcategory.list') }}',
                type: 'POST',
                data: function(d) {
                    d.type = $('#trashed_file').is(':checked') ? 'trashed' : 'nottrashed';
                }
            },

            oLanguage: {
                sEmptyTable: "<p class='no_data_message'>No data available.</p>"
            },

            aoColumnDefs: [{
                bSortable: false,
                aTargets: [2, 3]
            }],

            aoColumns: [{
                    data: "sno"
                },
                {
                    data: "title"
                },
                {
                    data: "image"
                },
                {
                    data: "action"
                }
            ],

            initComplete: function() {
                this.api().columns([1]).every(function() {
                    var column = this;
                    var header = $(column.header()).text()
                        .trim(); // ← gets column header name

                    var input = $(
                            '<input type="text" class="form-control" placeholder="' +
                            header + '..." style="width:100%;" />'
                        )
                        .appendTo($(column.header()).empty())
                        .on('keyup change', function() {
                            column.search(this.value).draw();
                        });
                });
            }
        });


        // ── Image preview (delegated - works on AJAX loaded content) ──
        $(document).on('change', '#image', function() {
            var file = this.files[0];
            if (file) $('#img_preview').attr('src', URL.createObjectURL(file));
        });

        $('#subCategoryForm').validate({
            rules: {
                title: "required",
                image: {
                    required: function() {
                        return $('#id').val() === '';
                    }
                }
            },
            message: {
                title: {
                    required: "This field is required."
                },
                image: {
                    required: "Please Select Image."
                },
            },
            highlight: function(element) {
                $(element).addClass("border-danger")
            },
            unhighlight: function(element) {
                $(element).removeClass("border-danger")
            },
        });

        $('.saveSubCategory').off('click').on('click', function() {
            if ($('#subCategoryForm').valid()) {
                $('#subCategoryForm').ajaxSubmit({
                    // ✅ THIS is the key fix — forces multipart so files are included
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    success: function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (result.type === 'success') {
                            $('.saveSubCategory').html('<i class="fa fa-save"></i> Save');
                            showNotification(result.message, 'success');
                            subCategoryTable.fnDraw();
                            $('#subCategoryForm')[0].reset();
                            $('#id').val('');
                            // ✅ Reset preview back to default after save
                            $('#img_preview').attr('src', '/no-image.jpg');
                        } else {
                            showNotification(result.message, 'error');
                        }
                    }
                });
            }
        });


        $(document).on('click', '.editSubCategory', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let title = $(this).data('title');
            let image = $(this).data('image');

            $('#id').val(id);
            $('#name').val(title);
            $('.saveSubCategory').html(
                '<i class="fa fa-save"></i> Update');

            var fileInput = $('#thumbnail_image');
            fileInput.val('').clone(true).insertAfter(
                fileInput);
            fileInput.remove();

            $('#img_preview').attr('src', image ? '/uploads/categories/' + image : '/no-image.jpg');
        });

        var deleteId = null;

        $(document).on('click', '.deleteSubCategory', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });

        $('#confirmDelete').off('click').on('click', function() {

            $('#confirmDelete').on('click', function() {
                if (!deleteId) return;

                $.post('{{ route('subcategory.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                    .done(function(response) {
                        var result = typeof response === 'string' ? JSON.parse(response) :
                            response;
                        if (result.type === 'success') {
                            showNotification(result.message, 'success');
                            subCategoryTable.fnDraw(); // ✅ old-style API
                        } else {
                            showNotification(result.message, 'error');
                        }
                    })
                    .fail(function() {
                        showNotification('Delete failed. Please try again.', 'error');
                    })
                    .always(function() {
                        deleteId = null;
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal'))
                            .hide();
                    });
            });
        });
    });
</script>
