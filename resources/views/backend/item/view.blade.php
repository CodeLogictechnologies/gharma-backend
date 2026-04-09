@if ($type == 'error')
    <div class="modal-header">
        <h1 class="modal-title fs-5">Error</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger mb-0">{{ $message }}</div>
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="fa fa-box me-2 text-primary"></i> Item Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">

        {{-- ── Basic Info ─────────────────────────────────────────────── --}}
        <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.75rem;letter-spacing:.06em;">
            Basic Info
        </h6>
        <table class="table table-bordered table-sm mb-4">
            <tbody>
                <tr>
                    <th width="35%">Item Name</th>
                    <td>{{ $itemDetails->title ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Brand</th>
                    <td>{{ $itemDetails->brand ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>
                        @php $type = $itemDetails->type ?? '-'; @endphp
                        <span
                            class="badge
                            {{ $type === 'Featured' ? 'bg-warning text-dark' : ($type === 'Special' ? 'bg-info text-dark' : 'bg-secondary') }}">
                            {{ $type }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $itemDetails->category_title ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Sub Category</th>
                    <td>{{ $itemDetails->subcategory_title ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{!! $itemDetails->description ?? '-' !!}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $itemDetails->created_at ? \Carbon\Carbon::parse($itemDetails->created_at)->format('d M Y, h:i A') : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- ── Product Images ──────────────────────────────────────────── --}}
        <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.75rem;letter-spacing:.06em;">
            Product Images
        </h6>

        @if (!empty($itemDetails->images) && count($itemDetails->images) > 0)
            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach ($itemDetails->images as $img)
                    <div class="position-relative">
                        <img src="{{ asset('uploads/items/' . $img->image) }}" alt="product image"
                            style="width:100px;height:90px;object-fit:cover;border-radius:8px;
                                    border: 2px solid ;">

                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-4"><em>No images uploaded.</em></p>
        @endif

        {{-- ── Variations ──────────────────────────────────────────────── --}}
        <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.75rem;letter-spacing:.06em;">
            Variations
        </h6>

        @if (!empty($itemDetails->variations) && count($itemDetails->variations) > 0)
            <div class="table-responsive mb-2">
                <table class="table table-bordered table-sm text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Attribute</th>
                            <th>Value</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDetails->variations as $i => $v)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $v->attribute ?? '-' }}</td>
                                <td>{{ $v->value ?? '-' }}</td>
                                <td>{{ number_format($v->price ?? 0, 2) }}</td>
                                <td>{{ $v->stock ?? 0 }}</td>
                                <td>
                                    @php $status = $v->status ?? 'N'; @endphp
                                    <span class="badge {{ $status === 'Y' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $status === 'Y' ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-2"><em>No variations added.</em></p>
        @endif

    </div>{{-- /modal-body --}}

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif
