@if ($type == 'error')
    <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">
            Error
        </h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        {{ $message }}
    </div>
@else
    <div class="modal-header">
        <h5 class="modal-title">View Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <em class="icon ni ni-cross"></em>
        </a>
    </div>
    <div class="modal-body">
        <div class="card-inner">
            <div class="nk-block">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Body</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Organization Name</th>
                            <td>{{ $orgDetails->name }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Phone number</th>
                            <td>{!! $orgDetails->phone !!}</td>
                        </tr>
                        <tr>
                            <th scope="row">User Name</th>
                            <td>{!! $orgDetails->username !!}</td>
                        </tr>
                        <tr>
                            <th scope="row">Email</th>
                            <td>{!! $orgDetails->email !!}</td>
                        </tr>
                        <tr>
                            <th scope="row">Address</th>
                            <td>{!! $orgDetails->address !!}</td>
                        </tr>
                        <tr>
                            <th scope="row">Organization Logo</th>
                            <?php
                            $photo = asset('images/no-image.jpg');
                            if (!empty($orgDetails->logo)) {
                                $photo = asset('storage/organization/' . $orgDetails->logo);
                            }
                            ?>
                            <td><img src="{{ $photo }}" height="100px" alt="Image">
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
