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
        <h5 class="modal-title">View Sales Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="text-muted small">Date</div>
                <div>{{ \Carbon\Carbon::parse($voucherDetail->created_at)->format('Y-m-d') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Bill / Voucher No.</div>
                <div>{{ $voucherDetail->voucher_number }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Customer</div>
                <div>{{ $voucherDetail->name }}</div>
            </div>
            {{-- <div class="col-md-9">
                <div class="text-muted small">Remarks</div>
                <div>{{ $voucherDetail->remarks ?? '-' }}</div>
            </div> --}}
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Variation</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th>Excise</th>
                        <th>VAT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderDetail as $i => $line)
                        @php
                            $amount = $line->price * $line->quantity;
                            $exciseLabel =
                                $line->excise_type === 'percentage'
                                    ? number_format($line->excise_percent, 2) . '%'
                                    : number_format($line->excise_amount, 2);
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $line->title }}</td>
                            <td>{{ $line->value ?? '-' }}</td>
                            <td>{{ $line->quantity }}</td>
                            <td>{{ number_format($line->price, 2) }}</td>
                            <td>{{ number_format($amount, 2) }}</td>
                            <td>{{ $exciseLabel }}</td>
                            <td>{{ rtrim(rtrim(number_format($line->vat_percent, 2), '0'), '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $totalAmount = $orderDetail->sum(fn($line) => $line->price * $line->quantity);
                    @endphp
                    <tr class="fw-bold table-light">
                        <td colspan="5" class="text-end">Total</td>
                        <td>{{ number_format($totalAmount, 2) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif
