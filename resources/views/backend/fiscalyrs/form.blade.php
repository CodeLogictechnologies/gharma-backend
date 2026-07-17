<div class="modal-header">
    <h5 class="modal-title">{{ $fiscalyear ? 'Edit' : 'Add' }} Fiscal Year</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="fiscalyearForm">
    @csrf
    <input type="hidden" name="id" value="{{ $fiscalyear->id ?? '' }}">
    <input type="hidden" name="code" id="code_hidden" value="{{ $fiscalyear->code ?? '' }}">

    <div class="modal-body">

        <div class="mb-3">
            <label class="form-label">Start Date (BS)</label>
            <div class="input-group">
                <label class="input-group-text" for="start_date_np" style="cursor:pointer;">
                    <i class="bx bx-calendar"></i>
                </label>
                <input type="text" name="start_date" id="start_date_np" class="form-control" placeholder="2079-04-01"
                    autocomplete="off" value="{{ $fiscalyear->start_date ?? '' }}" required readonly>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">End Date (BS)</label>
            <div class="input-group">
                <label class="input-group-text" for="end_date_np" style="cursor:pointer;">
                    <i class="bx bx-calendar"></i>
                </label>
                <input type="text" name="end_date" id="end_date_np" class="form-control" placeholder="2080-03-32"
                    autocomplete="off" value="{{ $fiscalyear->end_date ?? '' }}" required readonly>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Fiscal Year Code</label>
            <input type="text" id="code_preview" class="form-control" value="{{ $fiscalyear->code ?? '' }}" disabled
                placeholder="Auto-generated from dates">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="is_current" value="Y"
                {{ ($fiscalyear->is_current ?? 'N') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label">Mark as Current Fiscal Year</label>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="status" value="Y"
                {{ ($fiscalyear->status ?? 'Y') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>

</form>