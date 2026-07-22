@if ($type == 'error')
    <div class="modal-header">
        <h1 class="modal-title fs-5">Error</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        {{ $message }}
    </div>
@else
    @php
        $purchaseTypeLabels = [
            'trading'                     => 'Trading',
            'non_trading_capitalized'     => 'Non-Trading-Capitalized',
            'non_trading_non_capitalized' => 'Non-Trading-Non-Capitalized',
        ];
    @endphp

    <div class="modal-header">
        <h5 class="modal-title">View Purchase Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="text-muted small">Date</div>
                <div>{{ \Carbon\Carbon::parse($voucherDetail->voucher_date)->format('Y-m-d') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Bill / Voucher No.</div>
                <div>{{ $voucherDetail->voucher_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Purchase Type</div>
                <div>{{ $purchaseTypeLabels[$voucherDetail->purchase_type] ?? $voucherDetail->purchase_type }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Vendor</div>
                <div>{{ $voucherDetail->vendor_name }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">PAN</div>
                <div>{{ $voucherDetail->pan ?? '-' }}</div>
            </div>
            <div class="col-md-9">
                <div class="text-muted small">Remarks</div>
                <div>{{ $voucherDetail->remarks ?? '-' }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Item</th>
                        <th>Variation</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th>VAT</th>
                        <th>Excise Amt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($voucherDetail->items as $i => $line)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $line->variation_product_code ?: ($line->item_product_code ?: '-') }}</td>
                            <td>{{ $line->item_title }}</td>
                            <td>{{ $line->variation_value ? ($line->variation_attribute . ': ' . $line->variation_value) : '-' }}</td>
                            <td>{{ $line->qty }}</td>
                            <td>{{ number_format($line->unit_rate, 2) }}</td>
                            <td>{{ number_format($line->amount, 2) }}</td>
                            <td>{{ rtrim(rtrim(number_format($line->vat_percent, 2), '0'), '.') }}%</td>
                            <td>{{ number_format($line->excise_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th>Subtotal</th>
                            <td class="text-end">{{ number_format($voucherDetail->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Bill Discount ({{ $voucherDetail->bill_discount_percent }}%)</th>
                            <td class="text-end">{{ number_format($voucherDetail->bill_discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Excise Amount</th>
                            <td class="text-end">{{ number_format($voucherDetail->excise_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Taxable Amount</th>
                            <td class="text-end">{{ number_format($voucherDetail->taxable_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>VAT Amount</th>
                            <td class="text-end">{{ number_format($voucherDetail->vat_amount, 2) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <th>Total</th>
                            <td class="text-end">{{ number_format($voucherDetail->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif
