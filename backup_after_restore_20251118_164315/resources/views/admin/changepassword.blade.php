@include('admin.include.header')


<style type="text/css">
    .change_form {
        display: none;
    }

    .change_form.show {
        display: flex;
    }
</style>


<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted " href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">Change Password</li>
        </ol>
    </nav>


    <div class="mt-4">
        <div class="card">
            <div class="px-4 py-3 border-bottom">
                <h5 class="card-title fw-semibold mb-0">Change Password</h5>
            </div>
            <div class="card-body p-4 border-bottom">
                <!-- <h5 class="fs-4 fw-semibold mb-4">Account Details</h5> -->


                <form id="changePasswordForm">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">

                            <div class="mb-4">
                                <label for="adminName" class="form-label fw-semibold">Current Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="currentPassword"
                                        name="currentPassword">
                                    <button type="button" class="btn btn-outline-secondary togglePassword">
                                        <i
                                            class="fa-solid fa-eye"></i><!-- FontAwesome or Bootstrap Icons for eye icon -->
                                    </button>
                                </div>

                            </div>

                            <div class="mb-4">
                                <label for="adminEmail" class="form-label fw-semibold">New Password</label>
                                <div class="input-group ">
                                    <input type="password" class="form-control" id="newPassword" name="password">
                                    <button type="button" class="btn btn-outline-secondary togglePassword">
                                        <i class="fa-solid fa-eye"></i>
                                        <!-- FontAwesome or Bootstrap Icons for eye icon -->
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmPassword"
                                        name="password_confirmation">
                                    <button type="button" class="btn btn-outline-secondary togglePassword">
                                        <i class="fa-solid fa-eye"></i>
                                        <!-- FontAwesome or Bootstrap Icons for eye icon -->
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <button class="btn btn-primary" type="submit" id="changePasswordBtn">Submit</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
</div>


</div>



@include('admin.include.footer')


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.togglePassword').click(function() {
            // Get the corresponding password input field for the clicked button
            const passwordField = $(this).siblings('input')[0];

            // Toggle the input type between 'password' and 'text'
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;


            if (type === 'password') {
                $(this).html('<i class="fa-solid fa-eye"></i>');
            } else {
                $(this).html('<i class="fa-solid fa-eye-slash"></i>');
            }
        });

        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the form from submitting normally

            $('.error-message').remove();


            $('#changePasswordBtn').prop('disabled', true);

            var formData = $(this).serialize(); // Serialize the form data

            $.ajax({
                url: '{{ route('admin.changePassword') }}', // Change this to the correct URL for your server
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content') // Add CSRF token to headers
                },
                success: function(response) {
                    // Handle success response
                    if (response.status === 1) {
                        $('#changePasswordBtn').prop('disabled',
                            false);

                        toastr.success('Admin password successfully changed.');

                        setTimeout(function() {
                            window.location.href =
                                "{{ route('admin.changePassword') }}";
                        }, 2000);

                    } else {
                        $('#changePasswordBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.error('Admin password  failed to change.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#changePasswordBtn').prop('disabled',
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
                            $('#changePasswordForm').find('input[name="' + field + '"]')
                                .parent() // Select the parent of the input
                                .after('<div class="error-message text-danger mt-1">' +
                                    errors[field].join(', ') +
                                    '</div>');
                        }
                    }
                }
            });
        });
    });

    $("#rReservation").change(function() {
        if ($(this).val() == "1") {
            $('.change_form').addClass('show');
        } else {
            $('.change_form').removeClass('show');
        }
    });
</script>
