@extends('backend.layouts.main')

@section('title')
    Item
@endsection

@section('main-content')
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div class="my-auto">
            <h5 class="page-title fs-21 mb-1">Item</h5>
        </div>
        <div class="d-flex my-xl-auto right-content">
            <div class="pe-1 mb-xl-0">
                <button type="button" class="btn btn-primary addItemButton">
                    <i class="fa fa-add"></i> Add
                </button>
            </div>
        </div>
    </div>

    <!-- Add / Edit Modal -->
    <div class="modal fade" id="itemModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="itemModelLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                {{-- Loaded via AJAX --}}
            </div>
        </div>
    </div>

    <!-- Item Table -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Item List</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="itemTable" class="table table-bordered text-nowrap w-100 dataTable no-footer mt-3">
                            <thead>
                                <tr>
                                    <th width="4%">S.No</th>
                                    <th width="20%">Item Name</th>
                                    <th width="15%">Category</th>
                                    <th width="15%">Sub Category</th>
                                    <th width="16%">Description</th>
                                    <th width="8%">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var itemTable;

        $(document).ready(function() {

            // ── Open Add modal ────────────────────────────────────────────────
            $('.addItemButton').on('click', function() {
                $.get('{{ route('item.form') }}', function(response) {
                    $('#itemModel .modal-content').html(response);
                    $('#itemModel').modal('show');
                });
            });

            // ── DataTable ─────────────────────────────────────────────────────
            itemTable = $('#itemTable').DataTable({
                sPaginationType: 'full_numbers',
                lengthMenu: [
                    [5, 10, 15, 20, 25, -1],
                    [5, 10, 15, 20, 25, 'All']
                ],
                iDisplayLength: 15,
                sDom: 'ltipr',
                bAutoWidth: false,
                aaSorting: [
                    [0, 'desc']
                ],
                bSort: false,
                bProcessing: true,
                bServerSide: true,
                oLanguage: {
                    sEmptyTable: "<p class='no_data_message'>No data available.</p>"
                },

                // Columns must match aoColumns keys AND thead order exactly
                aoColumns: [{
                        data: 'sno'
                    }, // [0]
                    {
                        data: 'name'
                    }, // [1] — searchable (column index 1)
                    {
                        data: 'category'
                    }, // [2] — searchable (column index 2)
                    {
                        data: 'sub_category'
                    }, // [3]
                    {
                        data: 'description'
                    },
                    {
                        data: 'action',
                        bSortable: false
                    }, // [6]
                ],

                ajax: {
                    url: '{{ route('item.list') }}',
                    type: 'POST',
                    data: function(d) {
                        d._token = '{{ csrf_token() }}';
                    }
                },

                // Search inputs in column headers for Item Name (col 1) and Category (col 2)
                initComplete: function() {
                    this.api().columns([1, 2]).every(function() {
                        var col = this;
                        var label = col.header().innerText.trim();
                        var input = $('<input type="text" placeholder="' + label +
                                '" style="width:100%">')
                            .addClass('search-input-highlight');
                        $(col.header()).empty().append(input);
                        input.on('keyup change', function() {
                            col.search(this.value).draw();
                        });
                    });
                }
            });

            // ── Edit ─────────────────────────────────────────────────────────
            $(document).on('click', '.editNews', function() {
                var id = $(this).data('id');
                var url = '{{ route('item.form') }}';
                $.post(url, {
                    id: id,
                    _token: '{{ csrf_token() }}'
                }, function(response) {
                    $('#itemModel .modal-content').html(response);
                    $('#itemModel').modal('show');
                });
            });

            // ── View ─────────────────────────────────────────────────────────
            $(document).on('click', '.view', function() {
                var id = $(this).data('id');
                var url = '{{ route('item.view') }}';
                $.post(url, {
                    id: id,
                    _token: '{{ csrf_token() }}'
                }, function(response) {
                    $('#itemModel .modal-content').html(response);
                    $('#itemModel').modal('show');
                });
            });

            // ── Delete ────────────────────────────────────────────────────────
            $(document).on('click', '.deleteNews', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure you want to delete this item?',
                    text: "You won't be able to revert it!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DB1F48',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete it!'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        showLoader();
                        $.post(
                            '{{ route('item.delete') }}', {
                                id: id,
                                _token: '{{ csrf_token() }}'
                            },
                            function(response) {
                                var res = JSON.parse(response);
                                if (res.type === 'success') {
                                    showNotification(res.message, 'success');
                                    itemTable.draw();
                                } else {
                                    showNotification(res.message, 'error');
                                }
                                hideLoader();
                            }
                        );
                    }
                });
            });

        });
    </script>
@endsection
