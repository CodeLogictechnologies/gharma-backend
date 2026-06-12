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
        <h5 class="modal-title">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row g-3">

            {{-- Profile Image --}}
            @if(!empty($orgDetails->image))
            <div class="col-12 text-center mb-2">
                <img src="{{ asset('storage/profiles/' . $orgDetails->image) }}"
                     style="max-height:120px;max-width:120px;border-radius:8px;border:1px solid #dee2e6;" alt="Profile">
            </div>
            @endif

            {{-- Basic Info --}}
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Full Name</label>
                <p class="mb-0 fw-semibold">{{ $orgDetails->name ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Username</label>
                <p class="mb-0">{{ $orgDetails->username ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Gender</label>
                <p class="mb-0">{{ $orgDetails->gender ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Email</label>
                <p class="mb-0">{{ $orgDetails->email ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Phone</label>
                <p class="mb-0">{{ $orgDetails->profile_phone ?? $orgDetails->phone ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Address</label>
                <p class="mb-0">{{ $orgDetails->address ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Type</label>
                <p class="mb-0">{{ ucfirst($orgDetails->type ?? '-') }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Status</label>
                <p class="mb-0">
                    @if($orgDetails->user_status == 'Approve')
                        <span class="badge bg-success">Active</span>
                    @elseif($orgDetails->user_status == 'Reject')
                        <span class="badge bg-danger">Rejected</span>
                    @else
                        <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                </p>
            </div>

            {{-- Roles --}}
            @if(!empty($orgDetails->role_names))
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Roles</label>
                <p class="mb-0">
                    @foreach($orgDetails->role_names as $role)
                        <span class="badge bg-primary me-1">{{ $role }}</span>
                    @endforeach
                </p>
            </div>
            @endif

            {{-- Wholesaler Section --}}
            @if($orgDetails->is_wholesaler)
            <div class="col-12">
                <hr>
                <p class="text-muted fw-semibold mb-3" style="font-size:0.85rem;">Wholesaler Information</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Company Name</label>
                <p class="mb-0">{{ $orgDetails->company_name ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Tax Number</label>
                <p class="mb-0">{{ $orgDetails->tax_number ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Registration Number</label>
                <p class="mb-0">{{ $orgDetails->registration_number ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">PAN Number</label>
                <p class="mb-0">{{ $orgDetails->pan_number ?? '-' }}</p>
            </div>
            @if(!empty($orgDetails->pan_image))
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">PAN Image</label><br>
                <img src="{{ asset('storage/profiles/' . $orgDetails->pan_image) }}"
                     style="max-height:100px;max-width:160px;border:1px solid #dee2e6;border-radius:4px;" alt="PAN">
            </div>
            @endif
            @if(!empty($orgDetails->registration_number_image))
            <div class="col-md-4">
                <label class="form-label text-muted" style="font-size:0.75rem;">Registration Image</label><br>
                <img src="{{ asset('storage/profiles/' . $orgDetails->registration_number_image) }}"
                     style="max-height:100px;max-width:160px;border:1px solid #dee2e6;border-radius:4px;" alt="Registration">
            </div>
            @endif
            @endif

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
@endif