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
        <h5 class="modal-title">View Discount</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="card-inner">
            <div class="nk-block">
                <table class="table table-bordered">
                    <tbody>

                        <tr>
                            <th scope="row" style="width: 35%;">Title</th>
                            <td>{{ $discount->title ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th scope="row">Type</th>
                            <td>{{ ucfirst($discount->type ?? '-') }}</td>
                        </tr>

                        @if (!empty($discount->type))
                            @if ($discount->type === 'percentage')
                                <tr>
                                    <th scope="row">Percentage</th>
                                    <td>{{ $discount->percentage ?? '-' }} %</td>
                                </tr>
                            @elseif ($discount->type === 'fixed')
                                <tr>
                                    <th scope="row">Fixed Amount</th>
                                    <td>Rs {{ $discount->value ?? '-' }}</td>
                                </tr>
                            @endif
                        @endif

                        <tr>
                            <th scope="row">Applies To</th>
                            <td>{{ ucfirst($discount->applies_to ?? '-') }}</td>
                        </tr>

                        @if (!empty($discount->item_id))
                            <tr>
                                <th scope="row">Item</th>
                                <td>{{ $discount->item_title ?? $discount->item_id }}</td>
                            </tr>
                        @endif

                        @if (!empty($discount->variation_id))
                            <tr>
                                <th scope="row">Variation</th>
                                <td>{{ $discount->variation_label ?? $discount->variation_id }}</td>
                            </tr>
                        @endif

                        <tr>
                            <th scope="row">Minimum Requirement</th>
                            <td>{{ ucfirst($discount->min_requirement ?? 'none') }}</td>
                        </tr>

                        @if (!empty($discount->min_value))
                            <tr>
                                <th scope="row">Minimum Value</th>
                                <td>{{ $discount->min_value }}</td>
                            </tr>
                        @endif

                        <tr>
                            <th scope="row">Usage Limit Type</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $discount->usage_limit_type ?? 'once')) }}</td>
                        </tr>

                        @if (!empty($discount->usage_limit))
                            <tr>
                                <th scope="row">Total Usage Limit</th>
                                <td>{{ $discount->usage_limit }}</td>
                            </tr>
                        @endif

                        @if (!empty($discount->usage_limit_per_user))
                            <tr>
                                <th scope="row">Uses Per Customer</th>
                                <td>{{ $discount->usage_limit_per_user }}</td>
                            </tr>
                        @endif

                        <tr>
                            <th scope="row">Used Count</th>
                            <td>{{ $discount->used_count ?? 0 }}</td>
                        </tr>

                        <tr>
                            <th scope="row">Active Date</th>
                            <td>{{ !empty($discount->starts_at) ? \Carbon\Carbon::parse($discount->starts_at)->format('d M Y') : '-' }}</td>
                        </tr>

                        <tr>
                            <th scope="row">End Date</th>
                            <td>{{ !empty($discount->ends_at) ? \Carbon\Carbon::parse($discount->ends_at)->format('d M Y') : '-' }}</td>
                        </tr>

                        <tr>
                            <th scope="row">Status</th>
                            <td>
                                @if (($discount->status ?? '') === 'Y')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Discount Type (Category)</th>
                            <td>{{ $discount->discount_type ?? '-' }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif