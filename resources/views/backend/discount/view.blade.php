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
                            <th scope="row">Organization Name</th>
                            <td>{{ $orgDetails->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">User Name</th>
                            <td>{{ $orgDetails->username ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Email</th>
                            <td>{{ $orgDetails->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Phone Number</th>
                            <td>{{ $orgDetails-> ?? '-' }}</td>
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
                            <th scope="row">Gender</th>
                            <td>{{ ucfirst($orgDetails->gender ?? '-') }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Roles</th>
                            <td>
                                @if (!empty($orgDetails->role_names))
                                    {{ implode(', ', $orgDetails->role_names) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Profile Image</th>
                            <td>
                                @php
                                    $photo = asset('no-image.jpg');
                                    if (!empty($orgDetails->image)) {
                                        $photo = asset('storage/profiles/' . $orgDetails->image);
                                    }
                                @endphp
                                <img src="{{ $photo }}" height="100px" alt="Profile Image">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
