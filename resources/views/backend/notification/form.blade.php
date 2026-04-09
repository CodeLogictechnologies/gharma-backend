<div class="modal-header">
    <h5 class="modal-title">
        {{ @$id ? 'Edit Notification' : 'Add Notification' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="noticeForm" action="{{ route('notification.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ @$id ?? '' }}">

    <div class="modal-body">

        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" data-required>
                    <option value="">-- Select Type --</option>
                    <option value="notification" {{ (@$type ?? '') == 'notification' ? 'selected' : '' }}>Notification
                    </option>
                    <option value="sms" {{ (@$type ?? '') == 'sms' ? 'selected' : '' }}>SMS</option>
                </select>
                <div class="invalid-feedback">Type is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Select User <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" data-required>
                    <option value="">-- Select User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ (@$user_id ?? '') == $user->id ? 'selected' : '' }}>
                            {{ $user->username }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">User is required.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Enter Title"
                    value="{{ @$title ?? '' }}" data-required />
                <div class="invalid-feedback">Title is required.</div>
            </div>

        </div>

        <div class="row g-3 mb-3">

            <div class="col-md-12">
                <label class="form-label">Message <span class="text-danger">*</span></label>
                <textarea name="message" class="form-control" rows="5" data-required>{{ @$message ?? '' }}</textarea>
                <div class="invalid-feedback">Message is required.</div>
            </div>

        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="bx {{ @$id ? 'bx-save' : 'bx-plus' }} me-1"></i>
            {{ @$id ? 'Update' : 'Save' }}
        </button>
    </div>
</form>
