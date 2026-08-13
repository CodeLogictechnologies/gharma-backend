<!-- jQuery first (KEEP THIS ORDER) -->
@extends('layouts.main')
@section('title', 'Loyalty Setup')
<!-- jQuery FIRST -->
<script src="/assets/vendor/libs/jquery/jquery.js"></script>
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
        background: rgba(0, 0, 0, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        border-radius: 0 0 10px 10px;
    }
</style>
<!-- jQuery Validate -->
@section('content')

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Loyalty</h5>
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

                    <h5 class="mb-3">Loyalty Setup</h5>
                    @canany(['add.loyalty', 'edit.loyalty'])
                    <form action="{{ route('loyalty.save') }}" method="POST" id="loyaltyForm"
                        enctype="multipart/form-data">

                        <div class="mb-3">
                            <input type="hidden" name="id" value="" id="id">
                            <label class="form-label" for="minprice">Min Order Price<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="minprice" id="minprice"
                                placeholder="Example: 1000 " />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="maxprice">Max Order Price<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="maxprice" id="maxprice"
                                placeholder="Example: 2000" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="percentage">Percentage<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="percentage" id="percentage"
                                placeholder="Example: 2 " />
                        </div>

                        <button type="button" class="btn btn-primary saveLoyalty">Save</button>
                    </form>
                    @endcan

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

                                <table class="table" id="loyaltyTable" aria-describedby="datatable-basic_info">
                                    <thead class="table-light">
                                        <tr class="align-middle">
                                            <th width="5%">
                                                S.No
                                            </th>

                                            <th width="28%">Min Order Price</th>
                                            <th width="28%"">Max Order Price</th>
                                                <th width=" 28"">Percentage</th>
                                            <th width="15%"">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class=" table-border-bottom-0">

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
    var loyaltyTable;

    $(document).ready(function() {

        // ── CSRF setup ────────────────────────────────────────────────
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ── DataTable ─────────────────────────────────────────────────
        loyaltyTable = $('#loyaltyTable').dataTable({
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

            ajax: {
                url: '{{ route('loyalty.list') }}',
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
                    data: "minprice"
                },
                {
                    data: "maxprice"
                },
                {
                    data: "percentage"
                },
                {
                    data: "action"
                }
            ],

            initComplete: function() {
                this.api().columns([1, 2, 3]).every(function() {
                    var column = this;
                    var header = $(column.header()).text()
                        .trim();
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


        $('#loyaltyForm').validate({
            rules: {
                minprice: "required",
                maxprice: "required",
                percentage: "required"
            },
            message: {
                minprice: {
                    required: "This field is required."
                },
                maxprice: {
                    required: "This field is required."
                },
                percentage: {
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

        $('.saveLoyalty').off('click').on('click', function() {
            if ($('#loyaltyForm').valid()) {

                let form = document.getElementById('loyaltyForm');
                let formData = new FormData(form);

                $.ajax({
                    url: "{{ route('loyalty.save') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        let result = typeof response === 'string' ? JSON.parse(response) :
                            response;

                        if (result.type === 'success') {
                            showNotification(result.message, 'success');

                            loyaltyTable.fnDraw();

                            $('#loyaltyForm')[0].reset();
                            $('#id').val('');
                            $('#minprice').val('');
                            $('#maxprice').val('');
                            $('#percentage').val('');
                            $('.saveLoyalty').html('<i class="fa fa-save"></i> Save');
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


        $(document).on('click', '.editLoyalty', function(e) {
            e.preventDefault();

            let id = $(this).data('id');
            let minprice = $(this).data('minprice');
            let maxprice = $(this).data('maxprice');
            let percentage = $(this).data('percentage');

            $('#id').val(id);
            $('#minprice').val(minprice);
            $('#maxprice').val(maxprice);
            $('#percentage').val(percentage);

            $('.saveLoyalty').html('<i class="fas fa-save"></i> Update');
        });

        var deleteId = null;

        $(document).on('click', '.deleteLoyalty', function(e) {
            e.preventDefault();
            deleteId = $(this).data('id');
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });

        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            $.post('{{ route('loyalty.delete') }}', {
                        id: deleteId,
                        _token: '{{ csrf_token() }}'
                    })
                .done(function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.type === 'success') {
                        showNotification(result.message, 'success');
                        loyaltyTable.fnDraw(); // ✅ old-style API
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