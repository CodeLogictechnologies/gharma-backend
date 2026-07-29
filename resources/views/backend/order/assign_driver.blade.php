<div class="modal-header">
    <h5 class="modal-title">{{ isset($is_assigned) && $is_assigned ? 'Reassign Driver' : 'Assign Driver' }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="assignDriverForm" action="{{ route('assign.driver.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="ordermasterid" value="{{ $ordermasterid ?? '' }}">

    <div class="modal-body">
        @isset($order)
            <div class="d-flex align-items-start gap-2 border rounded p-2 mb-3 bg-body-tertiary">
                <i class="bx bx-package fs-4 text-primary"></i>
                <div>
                    <div class="fw-semibold">{{ $order->customer_name ?? '-' }}</div>
                    <small class="text-muted d-block">{{ $order->customer_phone ?? '-' }}</small>
                    <small class="text-muted">{{ $order->address_name ?? 'No address on file' }}</small>
                </div>
            </div>
        @endisset

        <div class="row g-3 mb-3">

            <div class="col-md-6">
                <label class="form-label" for="driverSelect">Select Driver</label>
                <select name="driver_id" class="form-select" id="driverSelect" data-required>
                    <option value="">-- Select Driver --</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}"
                            {{ (isset($assigned_driver) && $assigned_driver == $driver->id) ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="deliveryDateInput">Assign Date (B.S.)</label>
                <input type="text" name="delivery_date" id="deliveryDateInput" class="form-control" autocomplete="off"
                    data-required value="{{ old('delivery_date', $assigned_date ?? '') }}">
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