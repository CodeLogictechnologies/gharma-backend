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
                            <th scope="row">Store Name</th>
                            <td>{{ $orgDetails->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Email</th>
                            <td>{{ $orgDetails->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Phone</th>
                            <td>{{ $orgDetails->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Address</th>
                            <td>{{ $orgDetails->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">City</th>
                            <td>{{ $orgDetails->city ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Country</th>
                            <td>{{ $orgDetails->country ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Latitude</th>
                            <td>{{ $orgDetails->latitude ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Longitude</th>
                            <td>{{ $orgDetails->longitude ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif