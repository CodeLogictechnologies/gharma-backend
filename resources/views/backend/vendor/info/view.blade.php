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
        <h5 class="modal-title">View Vendor Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th scope="row">Vendor Name</th>
                    <td>{{ $vendorDetail->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Email</th>
                    <td>{{ $vendorDetail->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Phone</th>
                    <td>{{ $vendorDetail->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Address</th>
                    <td>{{ $vendorDetail->address ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">City</th>
                    <td>{{ $vendorDetail->city ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Company Name</th>
                    <td>{{ $vendorDetail->company_name ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">PAN Number</th>
                    <td>{{ $vendorDetail->tax_number ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Registration Number</th>
                    <td>{{ $vendorDetail->registration_number ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">PAN Image</th>
                    <td>
                        @if (!empty($vendorDetail->pan_image))
                            <img src="{{ asset('storage/vendor/' . $vendorDetail->pan_image) }}"
                                style="max-height:160px; max-width:160px; border:1px solid #ccc;">
                        @else
                            <span class="text-muted">No image</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th scope="row">Registration Image</th>
                    <td>
                        @if (!empty($vendorDetail->registration_number_image))
                            <img src="{{ asset('storage/vendor/' . $vendorDetail->registration_number_image) }}"
                                style="max-height:160px; max-width:160px; border:1px solid #ccc;">
                        @else
                            <span class="text-muted">No image</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif