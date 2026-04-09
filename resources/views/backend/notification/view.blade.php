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
                            <th scope="row">Title</th>
                            <td>{{ $noticeDetail->title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Type</th>
                            <td>{{ $noticeDetail->type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Message</th>
                            <td>{{ $noticeDetail->message ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">To</th>
                            <td>{{ $noticeDetail->username ?? '-' }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
