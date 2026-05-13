@if ($type == 'error')
    <div class="modal-header">
        <h1 class="modal-title fs-5">Error</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        {{ $message }}
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">View Refund Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <table class="table table-bordered">
            <tbody>

                <tr>
                    <th>Product</th>
                    <td>{{ $refundDetails->title ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Variation</th>
                    <td>{{ $refundDetails->value ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Quantity</th>
                    <td>{{ $refundDetails->quantity ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Total Price</th>
                    <td>{{ $refundDetails->order_detail_total_price ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Refund Status</th>
                    <td>{{ $refundDetails->refund_status ?? '-' }}</td>
                </tr>

                <tr>
                    <th>User</th>
                    <td>{{ $refundDetails->username ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ $refundDetails->email ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Reason</th>
                    <td>{{ $refundDetails->reason ?? '-' }}</td>
                </tr>

            </tbody>
        </table>
    </div>
@endif