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
            <li class="breadcrumb-item" aria-current="page">Profile Update</li>
        </ol>
    </nav>


    <div class="mt-4">
        <div class="card">
            <div class="px-4 py-3 border-bottom">
                <h5 class="card-title fw-semibold mb-0">Profile Update</h5>
            </div>
            <div class="card-body p-4 border-bottom">
                <!-- <h5 class="fs-4 fw-semibold mb-4">Account Details</h5> -->
                <form id="updateProfileForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6" id="updateFormContainer">
                            <input type="text" name="adminOldImage" value="{{ $admin->admin_profile }}" hidden>
                            <!-- Admin Name -->
                            <div class="mb-4">
                                <label for="adminName" class="form-label fw-semibold">Name</label>
                                <input type="text" class="form-control" id="adminName" name="adminName"
                                    placeholder="Enter Name" value="{{ $admin->name }}">
                            </div>

                            <!-- Admin Email -->
                            <div class="mb-4">
                                <label for="adminEmail" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="adminEmail" name="adminEmail"
                                    placeholder="Enter Email" value="{{ $admin->email }}">
                            </div>

                            <!-- Admin Profile Picture -->
                            <div class="mb-4">
                                <label for="adminProfile" class="form-label fw-semibold">Profile</label>
                                <input type="file" class="form-control" id="adminProfile" name="adminProfile"
                                    placeholder="Upload Profile Picture">
                            </div>

                            <!-- Admin Email -->
                            <div class="mb-4">
                                <label for="shippingPercentage" class="form-label fw-semibold">Shipping Percentage</label>
                                <input type="number" class="form-control" id="shippingPercentage" name="shippingPercentage"
                                    placeholder="Enter Percentage" value="{{ $admin->shipping }}">
                            </div>

                            <!-- Admin Email -->
                            <div class="mb-4">
                                <label for="taxPercentage" class="form-label fw-semibold">Tax Percentage</label>
                                <input type="number" class="form-control" id="taxPercentage" name="taxPercentage"
                                    placeholder="Enter Percentage" value="{{ $admin->sale_tax }}">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <button class="btn btn-primary" type="submit" id="updateProfileBtn">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#updateProfileForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#updateProfileBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.updateProfile') }}",
                type: 'POST',
                data: new FormData($('#updateProfileForm')[0]),
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    // Handle success response
                    if (response.status === 1) {
                        $('#updateProfileBtn').prop('disabled',
                            false);
                        toastr.success('Admin profile successfully update.');

                        setTimeout(function() {
                            window.location.href =
                                "{{ route('admin.updateProfile') }}";
                        }, 2000);

                    } else {
                        $('#updateProfileBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.error('Admin Profile failed to update.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updateProfileBtn').prop('disabled',
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
                            $('#updateProfileForm').find('input[name="' + field + '"]')
                                .after('<div class="error-message text-danger mt-1">' +
                                    errors[field].join(', ') + '</div>');
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

@include('admin.include.footer')