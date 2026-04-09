<link rel="stylesheet" href="{{ asset('css/cropper/cropper.css') }}" />
<style>
    .cropper-container {
        width: 100% !important;
    }

    .modal-header {
        position: relative;
    }

    .modal-header .closeCrop {
        position: absolute;
        top: 13px;
        right: 15px;
    }
</style>
<div class="row">
    <form action="{{ $data['saveurl'] }}" id="qrcodesetupform" method="post">
        <div class="col-md-3">

            <input type="hidden" name="orgid" value="{{ $data['orgid'] }}">
            <div class="form-group select-style">
                <label for="qrcode">QR Image <span class="requiredfield">*</span></label>
                <input type="file" name="qrcode" class="qrcode" id="qrcode">
                <div class="profileReview">
                    <img src="" id="profileImage" loading="lazy" style="border:1px solid grey; width: 70px;">
                </div>
                <input type="hidden" class="form-control croppedImg" id="croppedImg" name="croppedImg">

            </div>
            <div class="form-group select-style">
                <label for="bankname">Bank Name <span style="color:red;">*</span></label><br>
                <input type="text" name="bankname" class="bankname form-control" id="bankname">
            </div>

            <div class="form-group select-style opt-verification" style="display:none;">
                <label for="otpcheck">OTP <span style="color:red;">*</span></label><br>
                <input type="text" name="otpcheck" class="otpcheck form-control" id="otpcheck">
            </div>
            <p id="timer" style="display:none;">Session will end in <span class="js-timeout">2:00</span>.</p>
            <div class="form-group text-right">
                <button type="button" class="btn_btn save_btn saveData" data-table="qrcode"
                    data-formid="rcodesetupform"><i class="fa fa-save"></i>Save
                </button>
            </div>

            <div class="message"></div>
        </div> <!-- end choose -->
        <input type="hidden" name="submittype" id="submittype" value="">
    </form>
    @if (isset($data['qrcode']))
        <div class="col-md-9 common_layout thead_input_search">
            <div id="no-more-tables">
                <table class="table-bordered table-striped table-condensed cf" id="billinosetup_table" width="100%">
                    <thead class="cf">
                        <tr>
                            <th>S.No.</th>
                            <th>Bank Name</th>
                            <th>QR CODE Image</th>

                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @php($i = 1)

                        @foreach ($data['qrcode'] as $key => $value)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $value->bankname }}</td>
                                <td><img src="{{ url('/storage/images/qrcode/' . $data['orgid'] . '/' . $value->qrcodeimage) }}"
                                        alt=""></td>
                                <td><button class="btn btn-danger" id="deleteqrcode"
                                        data-qrcodeid = "{{ $value->qrcodelogid }}"
                                        data-qrcodeimage = "{{ $value->qrcodeimage }}"><i class="fa fa-trash"></i>
                                        Delete</button></td>
                            </tr>
                            @php($i++)
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
<div class="modal cropModel fade" id="cropModel" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Crop Image</h5>
                <button type="button" class="closeCrop" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <div class="row">
                        <div class="col-md-12">
                            <img id="image" src="#" style="height: 300px; width: 300px;">
                        </div>
                        <div class="col-md-12">
                            <div class="preview"></div>
                        </div>
                    </div>
                </div>
                <div id="controls">
                    <button id="rotateLeft">Rotate Left</button>
                    <button id="rotateRight">Rotate Right</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn_btn cancel_btn cancelCrop" id="cancelCrop">Cancel</button>
                <button type="button" class="btn_btn submit_btn" id="cropImage">Crop</button>
            </div>
        </div>
    </div>
</div>



<script src="{{ asset('js/cropper/cropper.js') }}"></script>
<script>
    var cropper;
    $(document).ready(function() {


        //cancel Crop Model --Start
        $(document).off('click', '.cancelCrop');
        $(document).on('click', '.cancelCrop', function() {
            var file = $('.qrcode')[0].files[0];
            if (file) {
                $('.qrcode').html(file.name);
            }
            $('#cropModel').modal('hide');
        });
        //close Model --End

        //to pass image url to crop model ---Start
        $(document).off('change', '.qrcode');
        $(document).on("change", ".qrcode", function(e) {
            var files = e.target.files;
            var done = function(url) {
                $('#image').attr('src', url);
                $('#cropModel').modal('show');
            };
            var reader;
            var file;
            var url;

            if (files && files.length > 0) {
                file = files[0];
                if (URL) {
                    done(URL.createObjectURL(file));
                }
            }
        });
        //to pass image url to crop model ---End

        //to crop images---Start
        $(document).off('shown.bs.modal', '#cropModel');
        $(document).on('shown.bs.modal', '#cropModel', function() {
            var image = document.getElementById('image');

            cropper = new Cropper(image, {
                initailAspectRatio: 1,
                aspectRatio: 1,
                viewMode: 1,
                moveable: false,
                zoomOnWheel: false,

                preview: '.preview',
            });

            $("#rotateRight").on("click", e => {
                cropper.rotate(90);
            });

            $("#rotateLeft").on("click", e => {
                cropper.rotate(-90);
            });
        }).on('hidden.bs.modal', function() {
            cropper.destroy();
            cropper = null;
        });
        //to crop images---End

        //save crop image ---Start
        var base64data;
        $(document).off('click', '#cropImage');
        $(document).on('click', "#cropImage", function() {

            canvas = cropper.getCroppedCanvas({
                width: 160,
                height: 160,
            });
            canvas.toBlob(function(blob) {
                url = URL.createObjectURL(blob);
                $('#profileImage').attr('src', url);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function() {
                    base64data = reader.result;
                    $('#cropModel').modal('hide');
                    $('#croppedImg').val(base64data);
                }
            })
        });
        //save crop image ---End

        // --------------save qr code ---------------------------
        $(document).off('click', '.saveData');
        $(document).on('click', '.saveData', function() {
            $('#loading').show();
            $('#qrcodesetupform').ajaxSubmit({
                dataType: 'json',
                success: function(response) {
                    if (response.key == 0) {
                        if (response.type == 'success') {
                            $.notify(response.message, 'success');
                            $('.opt-verification').show();
                            $('#submittype').val('confirmation');
                            $('#loading').hide();
                            $('#timer').css('display', 'block');
                            $('.js-timeout').text("15:00");
                            countdown();
                        } else {
                            $.notify(response.message, 'error');
                            $('#loading').hide();
                        }
                    } else if (response.key == 1) {
                        if (response.type == 'success') {
                            $.notify(response.message, 'success');
                            $('#qrcodesetup-tab').trigger('click');
                            $('.opt-verification').hide();
                            $('.opt-verification').val('');
                            $('#submittype').val('');
                            $('#qrcodesetupform')[0].reset();
                            $('#profileImage').attr("src", '');
                            $('#loading').hide();
                        } else {
                            $.notify(response.message, 'error');
                            if (response.response == 'reload') {
                                $('#qrcodesetup-tab').trigger('click');
                            }
                            $('#loading').hide();
                        }
                    }
                }
            })
        });

        var interval;

        function countdown() {
            clearInterval(interval);
            interval = setInterval(function() {
                var timer = $('.js-timeout').html();

                timer = timer.split(':');
                var minutes = timer[0];
                var seconds = timer[1];
                seconds -= 1;
                if (minutes < 0) {
                    return;
                } else if (seconds < 0 && minutes != 0) {
                    minutes -= 1;
                    seconds = 59;
                } else if (seconds < 10 && length.seconds != 2) {
                    seconds = '0' + seconds;
                }

                $('.js-timeout').html(minutes + ':' + seconds);

                if (minutes == 0 && seconds == 0) {
                    clearInterval(interval);
                    $('#qrcodesetup-tab').trigger('click');
                };
            }, 1000);
        }


        $(document).off('click', '#deleteqrcode');
        $(document).on('click', '#deleteqrcode', function() {
            var qrcodeid = $(this).data('qrcodeid');
            var qrcodeimage = $(this).data('qrcodeimage');
            var infodata = {
                qrcodeid: qrcodeid,
                qrcodeimage: qrcodeimage
            };
            var url = baseurl + "/studentfee/feesetup/qrcodesetup/deleteqrcode";
            Swal.fire({
                title: 'Do you want to delete this?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(url, infodata, function(response) {
                        var result = JSON.parse(response);
                        if (result.type == 'success') {
                            $.notify(result.message, 'success');
                            $('#qrcodesetup-tab').trigger('click');
                        } else {
                            $.notify(result.message, 'error');
                        }
                    });
                }
            })
        });

    });
</script>
