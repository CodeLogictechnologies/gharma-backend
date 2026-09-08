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
        @php
            $isAbbreviated = $voucherDetail->bill_type === 'abbreviated_bill';
            $colspan = $isAbbreviated ? 6 : 8;
            $billTypeLabel = $isAbbreviated ? 'Abbreviated Bill' : 'VAT Bill';
        @endphp

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
            <div class="col-md-3">
                <div class="text-muted small">Bill Type</div>
                <div>
                    <span class="badge {{ $isAbbreviated ? 'bg-secondary' : 'bg-primary' }}">
                        {{ $billTypeLabel }}
                    </span>
                </div>
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
                        <th>Product Code</th>
                        <th>Item</th>
                        <th>Variation</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        @if (!$isAbbreviated)
                            <th>Excise</th>
                            <th>VAT</th>
                        @endif
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
                            <td>{{ $line->variation_product_code ?: ($line->item_product_code ?: '-') }}</td>
                            <td>{{ $line->title }}</td>
                            <td>{{ $line->value ?? '-' }}</td>
                            <td>{{ $line->quantity }}</td>
                            <td>{{ number_format($line->price, 2) }}</td>
                            <td>{{ number_format($amount, 2) }}</td>
                            @if (!$isAbbreviated)
                                <td>{{ $exciseLabel }}</td>
                                <td>{{ rtrim(rtrim(number_format($line->vat_percent, 2), '0'), '.') }}%</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $beforeDiscountTotal = 0;
                        $discountTotal = 0;
                        $extraDiscountTotal = 0;
                        $afterDiscountTotal = 0;
                        $exciseTotal = 0;
                        $vatTotal = 0;
                        $grandTotal = 0;

                        foreach ($orderDetail as $line) {
                            $qty = (float) $line->quantity;
                            $price = (float) $line->price;

                            // Price before discount
                            $beforeDiscount = $price * $qty;

                            // Main Discount
                            $discount = 0;

                            if ($line->discount_type == 'percentage') {
                                $discount = ($beforeDiscount * (float) ($line->discount_amount ?? 0)) / 100;
                            } elseif ($line->discount_type == 'fixed') {
                                $discount = (float) ($line->discount_amount ?? 0) * $qty;
                            }

                            // Extra Discount
                            $extraDiscount = (float) ($line->extra_discount ?? 0);

                            // Price after all discounts — this is the excise/VAT-free base,
                            // used directly as the line total for abbreviated bills below.
                            $afterDiscount = $beforeDiscount - $discount - $extraDiscount;

                            // Stored values (only meaningful for VAT bills)
                            $excise = (float) $line->excise_amount;
                            $vat = (float) $line->vat_amount;

                            // Final Total per line:
                            // - VAT bill: use the stored order_detail_total_price (already
                            //   includes excise + VAT from saveData()).
                            // - Abbreviated bill: recompute from afterDiscount so the total
                            //   can NEVER include excise/VAT, even if those got saved on the
                            //   line for some reason (bug-proof against SalesVoucher::saveData()
                            //   not zeroing excise_amount/vat_amount for abbreviated rows).
                            $lineTotal = (float) $line->order_detail_total_price;

                            // Sum
                            $beforeDiscountTotal += $beforeDiscount;
                            $discountTotal += $discount;
                            $extraDiscountTotal += $extraDiscount;
                            $afterDiscountTotal += $afterDiscount;
                            $exciseTotal += $excise;
                            $vatTotal += $vat;
                            $grandTotal += $lineTotal;
                        }
                    @endphp

                    <tr>
                        <th colspan="{{ $colspan }}" class="text-end">Before Discount</th>
                        <th>{{ number_format($beforeDiscountTotal, 2) }}</th>
                    </tr>

                    <tr>
                        <th colspan="{{ $colspan }}" class="text-end">Discount</th>
                        <th>{{ number_format($discountTotal, 2) }}</th>
                    </tr>

                    <tr>
                        <th colspan="{{ $colspan }}" class="text-end">Extra Discount</th>
                        <th>{{ number_format($extraDiscountTotal, 2) }}</th>
                    </tr>

                    <tr>
                        <th colspan="{{ $colspan }}" class="text-end">After Discount</th>
                        <th>{{ number_format($afterDiscountTotal, 2) }}</th>
                    </tr>

                    @if (!$isAbbreviated)
                        <tr>
                            <th colspan="{{ $colspan }}" class="text-end">Excise</th>
                            <th>{{ number_format($exciseTotal, 2) }}</th>
                        </tr>

                        <tr>
                            <th colspan="{{ $colspan }}" class="text-end">VAT</th>
                            <th>{{ number_format($vatTotal, 2) }}</th>
                        </tr>
                    @endif

                    <tr class="table-primary fw-bold">
                        <th colspan="{{ $colspan }}" class="text-end">Grand Total</th>
                        <th>{{ number_format($grandTotal, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif