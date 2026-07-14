@if(isset($type) && $type == 'error')
    <div class="modal-header">
        <h5 class="modal-title">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger">{{ $message }}</div>
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">View Wholesaler Price</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">

        <table class="table table-bordered mb-4">
            <tbody>
                <tr>
                    <th style="width: 180px;">Product</th>
                    <td>{{ $data['itemname'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Variation</th>
                    <td>{{ $data['variationname'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if(($data['status'] ?? '') == 'Y')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>VAT</th>
                    <td>
                        @if(($data['vat_status'] ?? 'N') == 'Y')
                            <span class="badge bg-success">Taxable ({{ rtrim(rtrim(number_format((float)($data['vat_percent'] ?? 0), 2), '0'), '.') }}%)</span>
                        @else
                            <span class="badge bg-secondary">Non-Taxable</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Excise Duty</th>
                    <td>
                        @if(($data['excise_status'] ?? 'N') == 'Y')
                            @if(($data['excise_type'] ?? '') === 'percentage')
                                <span class="badge bg-warning text-dark">{{ rtrim(rtrim(number_format((float)($data['excise_percentage'] ?? 0), 2), '0'), '.') }}%</span>
                            @else
                                <span class="badge bg-warning text-dark">Rs {{ number_format((float)($data['excise_value'] ?? 0), 2) }}</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">—</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $data['created_at'] ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <h6 class="fw-semibold mb-2">Price Details</h6>

        @if(!empty($data['wholesaler_price_details']))
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Min Qty</th>
                        <th>Max Qty</th>
                        <th>Price</th>
                        <th>Before Excise/Tax</th>
                        <th>After Excise/Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['wholesaler_price_details'] as $i => $detail)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $detail['min_qty'] ?? '-' }}</td>
                            <td>{{ $detail['max_qty'] ?? '-' }}</td>
                            <td>{{ $detail['price'] ?? '-' }}</td>
                            <td>{{ isset($detail['price_before_excise_tax']) ? number_format((float)$detail['price_before_excise_tax'], 2) : '-' }}</td>
                            <td>{{ isset($detail['price_after_excise_tax']) ? number_format((float)$detail['price_after_excise_tax'], 2) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No price details found.</p>
        @endif

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif