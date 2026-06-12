@if (isset($type) && $type == 'error')
    <div class="modal-header">
        <h5 class="modal-title">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger">{{ $message }}</div>
    </div>

@else
    <div class="modal-header">
        <h5 class="modal-title">View Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th style="width:35%; background:#f8f9fa;">Store Name</th>
                    <td>{{ !empty($orgDetails->name) ? $orgDetails->name : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Email</th>
                    <td>{{ !empty($orgDetails->email) ? $orgDetails->email : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Phone</th>
                    <td>{{ !empty($orgDetails->phone) ? $orgDetails->phone : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Address</th>
                    <td>{{ !empty($orgDetails->address) ? $orgDetails->address : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">City</th>
                    <td>{{ !empty($orgDetails->city) ? $orgDetails->city : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Country</th>
                    <td>{{ !empty($orgDetails->country) ? $orgDetails->country : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Latitude</th>
                    <td>{{ !empty($orgDetails->latitude) ? $orgDetails->latitude : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Longitude</th>
                    <td>{{ !empty($orgDetails->longitude) ? $orgDetails->longitude : '-' }}</td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Radius (km)</th>
                    <td>{{ !empty($orgDetails->radius) ? $orgDetails->radius : '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif