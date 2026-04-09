<!-- jQuery first (KEEP THIS ORDER) -->
@extends('layouts.main')
@section('title', 'Brand')
<!-- jQuery FIRST -->
<script src="/assets/vendor/libs/jquery/jquery.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- jQuery Validate -->
@section('content')

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Brand</h5>
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
                    <div class="col-12 col-lg-4">

                        <h5 class="mb-3">Add Brand</h5>
                        <form action="{{ route('brand.save') }}" method="POST" id="brandForm"
                            enctype="multipart/form-data">

                            <div class="mb-3">
                                <input type="hidden" name="id" value="" id="id">
                                <label class="form-label" for="name">Brand Name</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    placeholder="Example: " />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="name">Description</label>
                                <textarea name="description" class="form-control" id="description" cols="10" rows="10"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Image <span class="text-danger">*</span></label>

                                <!-- Hidden file input -->
                                <input type="file" id="thumbnail_image" name="image" accept="image/*"
                                    style="display:none;" @if (empty($id)) required @endif>

                                <!-- Clickable image preview box -->
                                <div class="image-upload-box" id="imageUploadBox"
                                    style="
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
        ">

                                    <!-- Preview image -->
                                    <img id="img_preview" src="{{ asset('/no-image.jpg') }}"
                                        style="
                width: 100%;
                height: 100%;
                object-fit: cover;
                position: absolute;
                top: 0; left: 0;
                border-radius: 10px;
            ">

                                    <!-- Camera icon overlay -->
                                    <div id="cameraOverlay"
                                        style="
                position: absolute;
                bottom: 0; left: 0; right: 0;
                background: rgba(0,0,0,0.45);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 6px;
                border-radius: 0 0 10px 10px;
            ">
                                        <i class="fas fa-camera" style="color:#fff; font-size:18px;"></i>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-1">
                                    Accepted: jpg/jpeg/png. Recommended: 300x475px.
                                </small>
                            </div>

                            <button type="button" class="btn btn-primary saveBrand">Save</button>
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

                                    <table class="table" id="brandTable" aria-describedby="datatable-basic_info">
                                        <thead class="table-light">
                                            <tr class="align-middle">
                                                <th data-dt-column="1" class="">
                                                    S.No
                                                </th>
                                                <th class="fs-6">
                                                    <input type="text" class="form-control" id="defaultFormControlInput"
                                                        placeholder="Leave Type"
                                                        aria-describedby="defaultFormControlHelp" />
                                                </th>
                                                <th class="">Logo</th>
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
            </div>
        </div>
    </div>
    <!-- delete -->
@endsection
<script>
    var brandTable;

    $(document).ready(function() {
        // Open file picker when clicking the box
        $('#imageUploadBox').on('click', function() {
            $('#thumbnail_image').click();
        });

        // Preview selected image
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
        brandTable = $('#brandTable').dataTable({
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
                url: '{{ route('brand.list') }}',
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
                    data: "name"
                },
                {
                    data: "image"
                },
                {
                    data: "action"
                }
            ],

            // ✅ COLUMN FILTER
            initComplete: function() {
                this.api().columns([1]).every(function() {
                    var column = this;

                    var input = $(
                            '<input type="text" placeholder="Search title" style="width:100%;" />'
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

        $('#brandForm').validate({
            rules: {
                name: "required",
                image: {
                    required: function() {
                        return $('#id').val() === '';
                    }
                }
            },
            message: {
                name: {
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

        $('.saveBrand').off('click').on('click', function() {
            if ($('#brandForm').valid()) {

                let form = document.getElementById('brandForm');
                let formData = new FormData(form);

                $.ajax({
                    url: "{{ route('brand.save') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        let result = typeof response === 'string' ? JSON.parse(response) :
                            response;

                        if (result.type === 'success') {
                            showNotification(result.message, 'success');

                            brandTable.fnDraw();

                            $('#brandForm')[0].reset();
                            $('#id').val('');
                            $('#img_preview').attr('src', '/no-image.jpg');
                            $('.saveBrand').html('<i class="fa fa-save"></i> Save');
                        } else {
                            showNotification(result.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Something went wrong!', 'error');
                    }
                });
            }
        });


        $(document).on('click', '.editBrand', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let name = $(this).data('name');
            let image = $(this).data('image');
            let desc = $(this).data('description');

            $('#id').val(id);
            $('#name').val(name);
            $('#description').val(desc);
            $('.saveBrand').html('<i class="fas fa-save"></i> Update');

            // ✅ Reset file input properly
            let oldInput = $('#thumbnail_image');
            let newInput = oldInput.clone(true);
            oldInput.replaceWith(newInput);

            // ✅ Re-bind change event on new input
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

            $('#img_preview').attr('src', image ? '/uploads/brands/' + image : '/no-image.jpg');
        });

        var deleteId = null;

        $(document).on('click', '.deleteBrand', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });

        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            $.post('{{ route('brand.delete') }}', {
                    id: deleteId,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        brandTable.fnDraw(); // ✅ old-style API
                    } else {
                        showNotification(result.message, 'error');
                    }
                })
                .fail(function() {
                    showNotification('Delete failed. Please try again.', 'error');
                })
                .always(function() {
                    deleteId = null;
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                });
        });
    });
</script>
