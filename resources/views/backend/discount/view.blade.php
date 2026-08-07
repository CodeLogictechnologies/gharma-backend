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
         <h6 class="mb-3">{{ $discount_title ?? '-' }}</h6>
        <div class="card-inner">
            <div class="nk-block">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Variation</th>
                            <th>Discount Type</th>
                            <th>Discount Amount</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($discounts as $discount)
                            <tr>
                                <td>{{ $discount->title }}</td>
                                <td>{{ $discount->attribute }} : {{ $discount->value }}</td>
                                <td>{{ ucfirst($discount->discount_type) }}</td>
                                <td>{{ number_format($discount->discount_amount, 2) }}</td>
                                <td>{{ number_format($discount->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif
