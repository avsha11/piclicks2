
              
              <div class="col-lg-9 col-md-8">
				<div class="page-title mb-1">
					<h4>{{ $title}}</h4>
				</div>
				<div class="">
			<form method="POST" id="changePasswordForm" action="{{ route('changePasswordSubmit') }}">
    @csrf
    <div class="mb-3">
        <label for="currentPassword" class="form-label">Current Password</label>
        <input type="password" name="currentPassword" class="form-control" id="currentPassword">
        <span class="text-danger" id="currentPasswordError"></span>
    </div>
    <div class="mb-4">
        <label for="newPassword" class="form-label">New Password</label>
        <input type="password" name="password" class="form-control" id="newPassword">
        <span class="text-danger" id="passwordError"></span>
    </div>
    <div class="mb-4">
        <label for="confirmPassword" class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" id="confirmPassword">
        <span class="text-danger" id="password_confirmationError"></span>
    </div>
    <button type="submit" class="btn btn-primary rounded-2 changePassBtn" tabindex="4">Change Password</button>
</form>

				</div>
			</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
    
            let isValid = true;
            let currentPassword = document.getElementById('currentPassword').value.trim();
            let newPassword = document.getElementById('newPassword').value.trim();
            let confirmPassword = document.getElementById('confirmPassword').value.trim();
            
            let changePassBtn = document.querySelector('.changePassBtn'); // Select the button
            // Disable the button to prevent multiple clicks
            changePassBtn.disabled = true;
            changePassBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    
            // Clear previous error messages
            document.getElementById('currentPasswordError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            document.getElementById('password_confirmationError').textContent = '';
    
            // Validate Current Password
            if (currentPassword === '') {
                document.getElementById('currentPasswordError').textContent = 'Current password is required.';
                isValid = false;
            }
    
            // Validate New Password
            if (newPassword === '') {
                document.getElementById('passwordError').textContent = 'New password is required.';
                isValid = false;
            }
    
            // Validate Confirm Password
            if (confirmPassword === '') {
                document.getElementById('password_confirmationError').textContent = 'Confirm password is required.';
                isValid = false;
            }
    
            // Check if passwords match
            if (newPassword !== confirmPassword) {
                document.getElementById('password_confirmationError').textContent = 'Passwords do not match.';
                isValid = false;
            }
    
            if (!isValid) {
                changePassBtn.disabled = false; // Re-enable button if validation fails
                changePassBtn.innerHTML = 'Change Password';
                return; // Stop submission if validation fails
            }
    
            // Submit the form via Fetch API (AJAX)
            let formData = new FormData(this);
    
            fetch(this.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    toastr.success(data.message);
                    this.reset(); // Reset the form after success
                    // Enable button after 2 seconds
                    setTimeout(() => {
                        changePassBtn.disabled = false;
                        changePassBtn.innerHTML = 'Change Password';
                    }, 2000);
                } else {
                     toastr.error(data.message);
                    setTimeout(() => {
                        changePassBtn.disabled = false;
                        changePassBtn.innerHTML = 'Change Password';
                    }, 2000);
                }
            })
            .catch(error => {
                console.error("Error:", error)
                changePassBtn.disabled = false;
                changePassBtn.innerHTML = 'Change Password';
            });
        });
    });
</script>