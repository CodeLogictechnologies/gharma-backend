<div class="modal-header">
    <h5 class="modal-title">Assign Driver</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="assignDriverForm" action="{{ route('assign.driver.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="ordermasterid" value="{{ $ordermasterid ?? '' }}">

    <div class="modal-body">
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Select Driver</label>
                <select name="driver_id" class="form-select" data-required>
                    <option value="">-- Select Driver --</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}"
                            {{ (isset($assigned_driver) && $assigned_driver == $driver->id) ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Assign Date</label>
                <input type="date" name="delivery_date" class="form-control" data-required
                    value="{{ old('delivery_date', $assigned_date ?? date('Y-m-d')) }}">
            </div>

        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="bx {{ isset($is_assigned) && $is_assigned ? 'bx-save' : 'bx-plus' }} me-1"></i>
            {{ isset($is_assigned) && $is_assigned ? 'Update' : 'Assign' }}
        </button>
    </div>
</form>