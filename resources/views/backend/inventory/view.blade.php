@if ($type == 'error')
    <div class="modal-header">
        <h5 class="modal-title">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger">{{ $message }}</div>
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">View Inventory</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th>Product</th>
                    <td>{{ $inventoryDetails->item_title ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Variation</th>
                    <td>{{ $inventoryDetails->variation_value ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Vendor</th>
                    <td>{{ $inventoryDetails->vendor_name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Stock</th>
                    <td>{{ $inventoryDetails->stock ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Reorder Level</th>
                    <td>{{ $inventoryDetails->reorder_level ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Unit Cost</th>
                    <td>{{ $inventoryDetails->unit_cost ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Selling Price</th>
                    <td>{{ $inventoryDetails->selling_price ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Manufacture Date</th>
                    <td>{{ $inventoryDetails->manufacturedatead ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Expiry Date</th>
                    <td>{{ $inventoryDetails->expirydatead ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif