@extends('backend.layouts.main')

@section('title')
    Permission
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
            <h5 class="page-title fs-21 mb-1">Permission </h5>
        </div>
    </div>
    <!-- Page Header Close -->

    <!-- Start::row-1 -->
    <div class="row">
        <div class="col-xl-4">
            <div class="card custom-card">
                <form action="{{ route('permission.save') }}" method="POST" id="permissionForm" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="row gy-4">

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                <input type="hidden" name="id" value="" id="id">
                                <label for="name" class="form-label">Permission <span
                                        class="required-field">*</span></label>
                                <input type="text" class="form-control" id="name" placeholder="Enter permission"
                                    name="name">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-primary savePermission"><i class="fa fa-save"></i>
                            Save</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Permission List
                    </div>
                    {{-- <div class="row ms-0">
                        <div class="form-check col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <input class="form-check-input" type="checkbox" value="Y" id="trashed_file"
                                name="trashed_file">
                            <label class="form-check-label" for="trashed_file">
                                View Trashed
                            </label>
                        </div>
                    </div> --}}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="datatable-basic_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-12 mb-3">
                                    <div class="dataTables_length" id="datatable-basic_length">
                                        <table id="permissionTable"
                                            class="table table-bordered text-nowrap w-100 dataTable no-footer mt-3"
                                            aria-describedby="datatable-basic_info">
                                            <thead>
                                                <tr>
                                                    <th width="5%">S.No</th>
                                                    <th width="75%">Permssion</th>
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
        var permissionTable;
        $(document).ready(function() {
            permissionTable = $('#permissionTable').DataTable({
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
                        "data": "name"
                    },
                    {
                        "data": "action"
                    },
                ],
                "ajax": {
                    "url": '{{ route('permission.list') }}',
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

            $('#permissionForm').validate({
                rules: {
                    name: "required"
                },
                message: {
                    name: {
                        required: "This field is required."
                    },
                },
                highlight: function(element) {
                    $(element).addClass("border-danger")
                },
                unhighlight: function(element) {
                    $(element).removeClass("border-danger")
                },
            });

            // Save permission
            $('.savePermission').off('click');
            $('.savePermission').on('click', function() {
                if ($('#permissionForm').valid()) {
                    showLoader();
                    $('#permissionForm').ajaxSubmit(function(response) {
                        var result = JSON.parse(response);
                        if (result) {
                            if (result.type === 'success') {
                                $('.savePermission').html('<i class="fa fa-save"></i> Save');
                                showNotification(result.message, 'success');
                                hideLoader();
                                permissionTable.draw();
                                $('#permissionForm')[0].reset();
                                $('#id').val('');

                            } else {
                                showNotification(result.message, 'error');
                                hideLoader();
                            }
                        } else {
                            hideLoader();
                        }
                    });
                }
            });

            // update permission
            $(document).off('click', '.editPermission');
            $(document).on('click', '.editPermission', function(e) {
                e.preventDefault();
                $('#id').val($(this).data('id'));
                $('.savePermission').html('<i class="fa fa-save"></i> Update');
                $('#name').val($(this).data('name'));
            });


            // view trashed items-start
            $('#trashed_file').off('change');
            $('#trashed_file').on('change', function(e) {
                permissionTable.draw();
            });
            // view trashed items-ends

            // Delete permission
            $(document).off('click', '.deletePermission');
            $(document).on('click', '.deletePermission', function() {

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
                        var url = '{{ route('permission.delete') }}';
                        $.post(url, data, function(response) {
                            var rep = JSON.parse(response);
                            if (rep) {
                                showNotification(rep.message, rep.type);
                                if (rep.type === 'success') {
                                    permissionTable.draw();
                                    $('#permissionForm')[0].reset();
                                    $('#id').val('');
                                }
                            }
                        });
                    }
                });
            });


        });
    </script>
@endsection
