

              

              <div class="col-lg-9 col-md-8">

				<div class="page-title mb-1">

					<h4>{{ $title}}</h4>

				</div>

				<div class="">

	<form id="updateProfileForm" enctype="multipart/form-data">

    @csrf

    <div class="mb-3">

        <label for="name" class="form-label">Name :</label>

        <input type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" required>

    </div>



    <div class="mb-4">

        <label for="email" class="form-label">Email :</label>

        <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" required readonly>

    </div>



    <div class="mb-4">

        <label for="profile_picture" class="form-label">Profile Picture:</label>

        

        {{-- Show current profile picture --}}

        <div class="mb-3">

            <img id="profilePreview" 

                 src="{{ asset('/storage/' . Auth::user()->profile_picture) }}" 

                 class="img-thumbnail" 

                 width="150">

        </div>



        {{-- File input for new profile picture --}}

        <input type="file" class="form-control" name="profile_picture" accept="image/*" id="profilePictureInput">

    </div>



    <button type="submit" class="btn btn-primary rounded-2 editProfileBtn" tabindex="4">Update Profile</button>

</form>





				</div>

			</div>







<!--For updating the Data-->

<script>

    document.getElementById('updateProfileForm').addEventListener('submit', function(event) {

        event.preventDefault();



        let formData = new FormData(this);

    

        // Disable the button and show a loading indicator

        let editProfileBtn = document.querySelector('.editProfileBtn');

        editProfileBtn.disabled = true;

        editProfileBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

        

        fetch("{{ route('profileUpdate') }}", {

            method: "POST",

            body: formData,

            headers: {

                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value

            }

        })

        .then(response => response.json())

        .then(data => {

            if (data.status === 1) {

                toastr.success(data.message);

                // Enable button after 2 seconds

                setTimeout(() => {

                    editProfileBtn.disabled = false;

                    editProfileBtn.innerHTML = 'Update Profile';

                    location.reload(); // Reload page after success

                }, 2000);

            } else {

                toastr.error(data.message);

                // Enable button after 2 seconds

                setTimeout(() => {

                    editProfileBtn.disabled = false;

                    editProfileBtn.innerHTML = 'Update Profile';

                }, 2000);

                

            }

        })

        .catch(error => { 

            console.error("Error:", error);

            setTimeout(() => {

                editProfileBtn.disabled = false;

                editProfileBtn.innerHTML = 'Update Profile';

            }, 2000);

        });

    });

</script>

<!--For Previewing the Image-->

<script>

    document.getElementById('profilePictureInput').addEventListener('change', function(event) {

        const file = event.target.files[0];

        if (file) {

            const reader = new FileReader();

            reader.onload = function(e) {

                document.getElementById('profilePreview').src = e.target.result;

            };

            reader.readAsDataURL(file);

        }

    });

</script>

