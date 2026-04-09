{{-- resources/views/backend/organization/view.blade.php --}}

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
        <h5 class="modal-title">View Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="card-inner">
            <div class="nk-block">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th scope="row">User Name</th>
                            <td>{{ $orderDetails->username ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Phone Number</th>
                            <td>{{ $orderDetails->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Category</th>
                            <td>{{ $orderDetails->categorytitle ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Sub Category</th>
                            <td>{{ $orderDetails->subcategorytitle ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Product</th>
                            <td>{{ $orderDetails->title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">QTY</th>
                            <td>{{ $orderDetails->qty ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Price</th>
                            <td>{{ $orderDetails->price ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
