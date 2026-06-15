@if ($type == 'error')
    <div class="modal-header">
        <h5 class="modal-title">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">{{ $message }}</div>
@else
    <div class="modal-header">
        <h5 class="modal-title">View Coupon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <table class="table table-bordered">
            <tbody>

                <tr>
                    <th style="width: 35%;">Coupon Code</th>
                    <td><span class="badge bg-dark fs-6">{{ strtoupper($coupon->coupon_code ?? '-') }}</span></td>
                </tr>

                @if (!empty($coupon->value))
                <tr>
                    <th>Discount Value</th>
                    <td>Rs {{ $coupon->value }}</td>
                </tr>
                @endif

                <tr>
                    <th>Applies To</th>
                    <td>{{ ucfirst($coupon->applies_to ?? '-') }}</td>
                </tr>

                @if (!empty($coupon->item_id))
                <tr>
                    <th>Item</th>
                    <td>{{ $coupon->item_title ?? $coupon->item_id }}</td>
                </tr>
                @endif

                @if (!empty($coupon->variation_id))
                <tr>
                    <th>Variation</th>
                    <td>{{ $coupon->variation_label ?? $coupon->variation_id }}</td>
                </tr>
                @endif

                <tr>
                    <th>Minimum Requirement</th>
                    <td>{{ ucfirst($coupon->min_requirement ?? 'none') }}</td>
                </tr>

                @if (!empty($coupon->min_value))
                <tr>
                    <th>Minimum Value</th>
                    <td>{{ $coupon->min_value }}</td>
                </tr>
                @endif

                <tr>
                    <th>Usage Limit Type</th>
                    <td>{{ ucfirst(str_replace('_', ' ', $coupon->usage_limit_type ?? 'once')) }}</td>
                </tr>

                @if (!empty($coupon->usage_limit))
                <tr>
                    <th>Total Usage Limit</th>
                    <td>{{ $coupon->usage_limit }}</td>
                </tr>
                @endif

                @if (!empty($coupon->usage_limit_per_user))
                <tr>
                    <th>Uses Per Customer</th>
                    <td>{{ $coupon->usage_limit_per_user }}</td>
                </tr>
                @endif

                <tr>
                    <th>Used Count</th>
                    <td>{{ $coupon->used_count ?? 0 }}</td>
                </tr>

                <tr>
                    <th>Active Date</th>
                    <td>{{ !empty($coupon->starts_at) ? \Carbon\Carbon::parse($coupon->starts_at)->format('d M Y') : '-' }}</td>
                </tr>

                <tr>
                    <th>End Date</th>
                    <td>{{ !empty($coupon->ends_at) ? \Carbon\Carbon::parse($coupon->ends_at)->format('d M Y') : '-' }}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        @if (($coupon->status ?? '') === 'Y')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif