@extends('backend.layouts.main')

@section('title')
    Sub Category
@endsection
<style>
    .iconpicker-popover.popover.bottom {
        opacity: 1;
    }

    input#trashed_file {
        border: 1px solid rgb(0, 99, 198) !important
    }
</style>
@section('main-content')
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div class="my-auto">
            <h5 class="page-title fs-21 mb-1">Sub Category</h5>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-4">
            <div class="card custom-card">
                <form action="{{ route('subcategory.save') }}" method="POST" id="subcategoryForm"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="row gy-4">

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <input type="hidden" name="id" value="" id="id">
                                <label for="title" class="form-label">Sub Category <span
                                        class="required-field">*</span></label>
                                <input type="text" class="form-control" id="title" placeholder="Enter category"
                                    name="title">
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <label for="category" class="form-label">Category <span
                                        class="required-field">*</span></label>
                                <select name="category" id="category" class="form-select">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="thumbnail_image" name="image"
                                    accept="image/*" @if (empty($id)) required @endif>
                                <div class="mt-2">

                                    <img style="width: 50% !important" src="{{ asset('/no-image.jpg') }}" id="img_preview"
                                        class="_image">
                                </div>
                                <small class="text-muted">Accepted formats: jpg/jpeg/png. Recommended size:
                                    300x475px.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-primary saveData"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Sub Category List
                    </div>

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="datatable-basic_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-12 mb-3">
                                    <div class="dataTables_length" id="datatable-basic_length">
                                        <table id="categoryTable"
                                            class="table table-bordered text-nowrap w-100 dataTable no-footer mt-3"
                                            aria-describedby="datatable-basic_info">
                                            <thead>
                                                <tr>
                                                    <th width="5%">S.No</th>
                                                    <th width="35%">Category</th>
                                                    <th width="35%">Sub Category</th>
                                                    <th width="20%">Image</th>
                                                    <th width="5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
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
    </div>
    <!--End::row-1 -->
@endsection

@section('script')
    <script>
        var categoryTable;
        $(document).ready(function() {
            categoryTable = $('#categoryTable').DataTable({
                "sPaginationType": "full_numbers",
                "bSearchable": false,
                "lengthMenu": [
                    [5, 10, 15, 20, 25, -1],
                    [5, 10, 15, 20, 25, "All"]
                ],
                'iDisplayLength': 15,
                "sDom": 'ltipr',
                "bAutoWidth": false,
                "aaSorting": [
                    [0, 'desc']
                ],
                "bSort": false,
                "bProcessing": true,
                "bServerSide": true,
                "oLanguage": {
                    "sEmptyTable": "<p class='no_data_message'>No data available.</p>"
                },
                "aoColumnDefs": [{
                    "bSortable": false,
                    "aTargets": [1]
                }],
                "aoColumns": [{
                        "data": "sno"
                    },
                    {
                        "data": "category_name"
                    },
                    {
                        "data": "title"
                    },
                    {
                        "data": "image"
                    },
                    {
                        "data": "action"
                    },
                ],
                "ajax": {
                    "url": '{{ route('subcategory.list') }}',
                    "type": "POST",
                    "data": function(d) {
                        var type = $('#trashed_file').is(':checked') == true ? 'trashed' :
                            'nottrashed';
                        d.type = type;
                    }
                },
                "initComplete": function() {
                    // Ensure text input fields in the header for specific columns with placeholders
                    this.api().columns([1]).every(function() {
                        var column = this;
                        var input = document.createElement("input");
                        var columnName = column.header().innerText.trim();
                        // Append input field to the header, set placeholder, and apply CSS styling
                        $(input).appendTo($(column.header()).empty())
                            .attr('placeholder', columnName).css('width',
                                '100%') // Set width to 100%
                            .addClass(
                                'search-input-highlight') // Add a CSS class for highlighting
                            .on('keyup change', function() {
                                column.search(this.value).draw();
                            });
                    });
                }
            });


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

            $('#subcategoryForm').validate({
                rules: {
                    title: "required",
                    category: "required",
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
                    category: {
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

            $('.saveData').off('click').on('click', function() {
                if ($('#subcategoryForm').valid()) {
                    $('#subcategoryForm').ajaxSubmit({
                        // ✅ THIS is the key fix — forces multipart so files are included
                        contentType: false,
                        processData: false,
                        type: 'POST',
                        success: function(response) {
                            var result = typeof response === 'string' ? JSON.parse(response) :
                                response;
                            if (result.type === 'success') {
                                $('.saveData').html('<i class="fa fa-save"></i> Save');
                                showNotification(result.message, 'success');
                                categoryTable.draw();
                                $('#subcategoryForm')[0].reset();
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

            // update Team Category          
            $(document).off('click', '.editSubCategory');
            $(document).on('click', '.editSubCategory', function(e) {
                e.preventDefault();

                let id = $(this).data('id');
                let title = $(this).data('title');
                let category = $(this).data('category');
                let image = $(this).data('image');

                $('#id').val(id);

                // ✅ FIXED
                $('#title').val(title);

                $('#category').val(category);

                $('.saveData').html('<i class="fa fa-save"></i> Update');

                // ✅ reset file input properly
                $('#thumbnail_image').val('');

                // ✅ show existing image
                if (image) {
                    $('#img_preview').attr('src', '/uploads/subcategories/' + image);
                } else {
                    $('#img_preview').attr('src', '/no-image.jpg');
                }
            });

            // Delete Team Category
            $(document).off('click', '.deleteSubCategory');
            $(document).on('click', '.deleteSubCategory', function() {

                var type = $('#trashed_file').is(':checked') == true ? 'trashed' :
                    'nottrashed';

                Swal.fire({
                    title: type === "nottrashed" ? "Are you sure you want to delete this item" :
                        "Are you sure you want to delete permanently  this item",
                    text: "You won't be able to revert it!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DB1F48",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, Delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        var id = $(this).data('id');
                        var data = {
                            id: id,
                            type: type,
                        };
                        var url = '{{ route('subcategory.delete') }}';
                        $.post(url, data, function(response) {
                            var rep = JSON.parse(response);
                            if (rep) {
                                showNotification(rep.message, rep.type);
                                if (rep.type === 'success') {
                                    categoryTable.draw();
                                    $('#subcategoryForm')[0].reset();
                                    $('#id').val('');
                                }
                            }
                        });
                        $('#img_preview').attr('src', '/no-image.jpg');

                    }
                });
            });

        });
    </script>
@endsection
