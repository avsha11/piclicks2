<!DOCTYPE html>
<html lang="en">

<head>
    <!--  Title -->
    <title>PicLicks</title>
    <!--  Required Meta Tag -->


    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="handheldfriendly" content="true" />
    <meta name="MobileOptimized" content="width" />
    <meta name="description" content="abafon" />
    <meta name="author" content="" />
    <meta name="keywords" content="abafon" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!--  Favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('admin/images/logos/favicon.ico') }}" />
    <!-- Core Css -->
    <link id="themeColors" rel="stylesheet" href="{{ asset('admin/css/style.min.css') }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>


    <style type="text/css">
        body,
        canvas {
            position: absolute;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            top: 0;
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <img src="{{ asset('admin/images/logos/favicon.ico') }}" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <!-- Preloader -->
    <div class="preloader">
        <img src="{{ asset('admin/images/logos/favicon.ico') }}" alt="loader" class="lds-ripple img-fluid" />
    </div>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <div class="position-relative overflow-hidden radial-gradient">
            <div class="position-relative z-index-5">
                <div class="container">


                    <div class="row align-items-center justify-content-center min-vh-100">

                        <div class="col-md-4">
                            <div class="authentication-login bg-body p-4" style="border-radius: 15px;">
                                <div class="">
                                    <div class="position-relative text-center my-4">
                                        <img src="{{asset(env('LOGO_PATH', 'assets/images/logo.png'))}}" style="height: 70px;">
                                    </div>
                                    <h2 class="mb-1 fs-3 fw-bolder text-black text-center">Forgot Password?</h2>



                                    <form method="POST" id="forgotPasswordForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="exampleInputEmail1" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="exampleInputEmail1"
                                                name="email" aria-describedby="emailHelp" placeholder="enter email">
                                            <span class="text-danger" id="email-error"></span>

                                        </div>

                                        <button type="submit" class="btn btn-primary" id="forgotPasswordBtn">Send
                                            Password</button>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!--  Import Js Files -->
    <script src="{{ asset('admin/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <!--  core files -->
    <script src="{{ asset('admin/js/app.min.js') }}"></script>
    <script src="{{ asset('admin/js/app.init.js') }}"></script>
    <script src="{{ asset('admin/js/app-style-switcher.js') }}"></script>
    <script src="{{ asset('admin/js/sidebarmenu.js') }}"></script>

    <script src="{{ asset('admin/js/custom.js') }}"></script>
</body>

</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#forgotPasswordForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#forgotPasswordBtn').prop('disabled', true);

            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route('admin.forgotPasswordSubmit') }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    if (response.status === 1) {
                        $('#forgotPasswordBtn').prop('disabled',
                            false);

                        toastr.success('Your Temporary Password Successfully sent.');



                        setTimeout(function() {
                            window.location.href =
                            "{{ route('admin.loginPage') }}";
                        }, 2000);
                    } else {
                        $('#updateProfileBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.success('Admin Profile failed to update.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#forgotPasswordBtn').prop('disabled',
                        false);

                    let response = xhr.responseJSON;
                    if (response && response.message) {
                        toastr.error(response.message); // Display general error message
                    } else {
                        toastr.error('Something went wrong.'); // Fallback error message
                    }
                    let errors = response.errors;
                    if (response.errors) {
                        for (let field in errors) {
                            $('#forgotPasswordForm').find('input[name="' + field + '"]')
                                .after('<div class="error-message text-danger mt-1">' +
                                    errors[field].join(', ') + '</div>');
                        }
                    }
                }
            });
        });

    });


    // init
    var maxx = document.body.clientWidth;
    var maxy = document.body.clientHeight;
    var halfx = maxx / 2;
    var halfy = maxy / 2;
    var canvas = document.createElement("canvas");
    document.body.appendChild(canvas);
    canvas.width = maxx;
    canvas.height = maxy;
    var context = canvas.getContext("2d");
    var dotCount = 200;
    var dots = [];
    // create dots
    for (var i = 0; i < dotCount; i++) {
        dots.push(new dot());
    }

    // dots animation
    function render() {
        context.fillStyle = "#2f2e5b";
        context.fillRect(0, 0, maxx, maxy);
        for (var i = 0; i < dotCount; i++) {
            dots[i].draw();
            dots[i].move();
        }
        requestAnimationFrame(render);
    }

    // dots class
    // @constructor
    function dot() {

        this.rad_x = 2 * Math.random() * halfx + 1;
        this.rad_y = 1.2 * Math.random() * halfy + 1;
        this.alpha = Math.random() * 360 + 1;
        this.speed = Math.random() * 100 < 50 ? 1 : -1;
        this.speed *= 0.1;
        this.size = Math.random() * 2 + 1;
        this.color = Math.floor(Math.random() * 256);

    }

    // drawing dot
    dot.prototype.draw = function() {

        // calc polar coord to decart
        var dx = halfx + this.rad_x * Math.cos(this.alpha / 180 * Math.PI);
        var dy = halfy + this.rad_y * Math.sin(this.alpha / 180 * Math.PI);
        // set color
        // context.fillStyle = "rgb(" + this.color + "," + this.color + "," + this.color + ")";

        if (this.color % 10 === 0) {
            context.fillStyle = "rgb(" + 247 + "," + 98 + "," + 13 + ")";
        } else {
            context.fillStyle = "white";
        }

        // draw dot
        context.fillRect(dx, dy, this.size, this.size);



    };

    // calc new position in polar coord
    dot.prototype.move = function() {

        this.alpha += this.speed;
        // change color
        if (Math.random() * 100 < 50) {
            this.color += 1;
        } else {
            this.color -= 1;
        }

    };



    // start animation
    render();
</script>
