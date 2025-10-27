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
            <li class="breadcrumb-item" aria-current="page">User Profile</li>
        </ol>
    </nav>


    <div class="mt-4">
        <div class="card">
            <div class="px-4 py-3 border-bottom">
                <h5 class="card-title fw-semibold mb-0">User Profile</h5>
            </div>
            <div class="card-body p-4 border-bottom">
                <!-- <h5 class="fs-4 fw-semibold mb-4">Account Details</h5> -->

                <div class="container mt-4">
                    <div class="row">
                        <div class="col-lg-6" id="updateFormContainer">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Profile Image</label>
                                <br>
                                @if ($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}"
                                alt="Profile Images" class="img-fluid" style="max-width: 150px;">
                                @else
                                <img src="{{ asset('storage/profileImage/user.png') }}"
                                alt="Profile Image" class="img-fluid" style="max-width: 150px;">
                                @endif

                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Name</label>
                                <div class="form-control-plaintext">{{ $user->name }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="adminEmail" class="form-label fw-semibold">Email</label>
                                <div class="form-control-plaintext">
                                    {{ $user->email }}
                                    @if ($user->email_verified_at)
                                        <span class="text-success "> Verified</span>
                                    @else
                                        <span class="text-danger"> Unverified</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="adminEmail" class="form-label fw-semibold">User Status</label>
                                @if ($user->user_status === 1)
                                    <div class="form-control-plaintext">Active</div>
                                @else
                                    <div class="form-control-plaintext">Blocked</div>
                                @endif
                            </div>

                            <a href="{{ route('admin.allUsersPage') }}" class="btn btn-primary mt-3">Back</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div>


</div>





<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script type="text/javascript">
    $("#rReservation").change(function() {
        if ($(this).val() == "1") {
            $('.change_form').addClass('show');
        } else {
            $('.change_form').removeClass('show');
        }
    });
</script>

@include('admin.include.footer')
