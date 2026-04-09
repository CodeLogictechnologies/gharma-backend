{{-- resources/views/backend/organization/view.blade.php --}}

@if ($type == 'error')
    <div class="modal-header">
        <h1 class="modal-title fs-5">Error</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        {{ $message }}
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">View Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="card-inner">
            <div class="nk-block">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th scope="row">Category</th>
                            <td>{{ $inventoryDetails->categorytitle ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Sub Category</th>
                            <td>{{ $inventoryDetails->subcategorytitle ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Product</th>
                            <td>{{ $inventoryDetails->title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Stock</th>
                            <td>{{ $inventoryDetails->stock ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Quantity Sold</th>
                            <td>{{ $inventoryDetails->soldqty ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Remaning Stock</th>
                            <td>{{ $inventoryDetails->remainingqty ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
