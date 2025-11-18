<script>
    let loggedIn = parseInt("{{auth()->check() ? 1 : 0}}");
    $(document).ready(function() {
        $('#signupButton').on('click', function(e) {
            e.preventDefault();

            // Clear previous error messages
            $('.error-message').html('');
            $('.success-message').remove(); // Remove any previous success messages

            // Get form data
            var password = $('#password').val().trim();
            var passwordConfirmation = $('#password_confirmation').val().trim();
            var fullname = $('#fullname').val().trim();
            var email = $('#email').val().trim();

            // Email Validation Regex
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            var isValid = true;

            // Disable button and show spinner
            $('#signupButton').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Signing up...');

            // Check if fields are filled
            if (!fullname) {
                $('#fullnameError').html('Full Name is required.');
                isValid = false;
            }

            // Validate Email
            if (!email) {
                $('#emailError').html('Email is required.');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                $('#emailError').html('Enter a valid email address.');
                isValid = false;
            }

            if (!password) {
                $('#passwordError').html('Password is required.');
                isValid = false;
            }

            if (!passwordConfirmation) {
                $('#passwordConfirmationError').html('Please confirm your password.');
                isValid = false;
            }

            // Check if passwords match
            if (password !== passwordConfirmation) {
                $('#passwordConfirmationError').html('Passwords do not match.');
                isValid = false;
            }

            if (!isValid) {
                $('#signupButton').prop('disabled', false).html('Sign Up'); // Re-enable button
                return;
            }

            let data = {
                name: fullname,
                email: email,
                password: password,
                password_confirmation: passwordConfirmation,
                _token: $('meta[name="csrf-token"]').attr('content'),
            };

            $.ajax({
                url: '{{ route('signup') }}',
                method: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Close the signup modal
                        $('#signupmodal').modal('hide');

                        // Show success message with Toastr
                        // toastr.success(response.message);
                        Swal.fire({
                            title: "Alert",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: true,
                        });

                        // Open the signin modal
                        $('#signinmodal').modal('show');

                        // Reset the form
                        $('#signupForm')[0].reset();
                    } else {
                        toastr.error(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#' + field + 'Error').html(errors[field].join(', '));
                    }
                },
                complete: function(Aaa) {
                    let response = Aaa.responseJSON;
                    // Re-enable button after success/error
                    $('#signupButton').prop('disabled', false).html('Sign Up');
                    if (response.status === 'error') {
                        toastr.error(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        }); // Display general error message
                    }
                }
            });
        });

        $('#signinmodal form').on('submit', function(e) {
            e.preventDefault();

            // Clear previous error messages
            $('.error-message').remove();

            var email = $('#floatingEmail').val().trim();
            var password = $('#Password').val().trim();

            var isValid = true;
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Disable button & show loading spinner
            let $loginButton = $('#signinmodal button[type="submit"]');
            $loginButton.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Logging in...');

            // Validation
            if (!email) {
                $('#floatingEmail').after(
                    '<div class="error-message text-danger mt-1">Email is required.</div>');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                $('#floatingEmail').after(
                    '<div class="error-message text-danger mt-1">Enter a valid email address.</div>'
                );
                isValid = false;
            }

            if (!password) {
                $('#Password').after(
                    '<div class="error-message text-danger mt-1">Password is required.</div>');
                isValid = false;
            }

            if (!isValid) {
                $loginButton.prop('disabled', false).html('Continue'); // Re-enable button
                return;
            }

            $.ajax({
                url: '{{ route('login') }}',
                method: 'POST',
                data: {
                    email: email,
                    password: password,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Update the UI with user info
                        if (response.user) {

                            toastr.success(response.message, '', {
                                closeButton: true,
                                timeOut: 5000,
                                extendedTimeOut: 0
                            });

                            $('#signinmodal').modal('hide'); // Close login modal on success

                            // Display logged-in user's name and photo in the offcanvas
                            $('#user-name').text(response.user.name);
                            updateCsrfToken(response.new_csrf_token);
                            $('#sub-offmenu').html(response.sub_offmenu);
                            $('#top_tray').html(response.top_tray);
                            $('#user-photo').attr('src', response.user.profile_photo_url);
                            $('#user-profile').show(); // Show the user's profile details

                            // Hide login/signup section
                            $('#login-signup').hide();
                            loggedIn = 1;
                        } else {
                            toastr.error(response.message, '', {
                                closeButton: true,
                                timeOut: 5000,
                                extendedTimeOut: 0
                            });
                        }
                    } else {
                        toastr.error(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                    }
                },
                error: function(xhr) {
                    let response = xhr.responseJSON;
                    if (response && response.message) {
                        toastr.error(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        }); // Display general error message
                    } else {
                        toastr.error('Invalid login credentials. Please try again.', '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        }); // Fallback error message
                    }

                    if (response.errors) {
                        for (let field in response.errors) {
                            $('#' + field).after(
                                '<div class="error-message text-danger mt-1">' +
                                response.errors[field].join(', ') + '</div>');
                        }
                    }
                },
                complete: function(Aaa) {
                    let response = Aaa.responseJSON;
                    $loginButton.prop('disabled', false).html('Continue');
                    if (response.status === 'error') {
                        toastr.error(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                        updateCsrfToken(response.new_csrf_token);
                        $('#sub-offmenu').html(response.sub_offmenu);
                        $('#top_tray').html(response.top_tray);
                    }
                }
            });

        });

        $('#forgotPasswordForm').on('submit', function(e) {
            e.preventDefault();

            let email = $('input[name="emailForgot"]').val();
            let $button = $('#forgotPasswordModal button[type="submit"]');

            $button.prop('disabled', true).text('Sending...');

            $.ajax({
                url: '{{ route('forgot-password') }}',
                type: 'POST',
                data: {
                    email: email,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {

                        toastr.success(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                        $('#forgotPasswordModal').modal('hide');
                        $('#signinmodal').modal('show');

                    } else {
                        toastr.error(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });

                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text('Send Reset Password Email');
                    toastr.error('Something went wrong in forget', '', {
                        closeButton: true,
                        timeOut: 5000,
                        extendedTimeOut: 0
                    });

                }
            });
        });

        // Live validation for email & password fields
        $('#floatingEmail, #Password').on('input', function() {
            $(this).next('.error-message').remove(); // Remove existing error
        });


        // Password Toggle
        $('.toggle-password').click(function() {
            let passwordField = $('#Password');
            let icon = $(this).find('i');

            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text'); // Show password
                icon.removeClass('fa-eye').addClass('fa-eye-slash'); // Change icon
            } else {
                passwordField.attr('type', 'password'); // Hide password
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });

    $('#logoutButton').on('click', function() {
        $("#logoutButton").prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm"></span>Processing...');

        $.ajax({
            url: '{{ route('logout') }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {

                updateCsrfToken(response.new_csrf_token);
                $("#logoutButton").prop('disabled', false).html('Logout');

                if (response.status === 'success') {
                    loggedIn = 0;

                    toastr.success(response.message);

                    // Hide user details and show login/signup button
                    $('#user-profile').hide();
                    $('#login-signup').show();
                    $('#sub-offmenu').html(response.sub_offmenu);
                    $('#top_tray').html(response.top_tray);

                    if (window.location.href.includes('design-collage')) {
                    // Do nothing (stay on the same page)
                    } else {
                        window.location.href = "{{ route('front.index') }}";
                    }

                } else {
                    toastr.success('Something went wrong', '', {
                        closeButton: true,
                        timeOut: 5000,
                        extendedTimeOut: 0
                    });
                }
            },
            error: function() {
                $("#logoutButton").prop('disabled', false).html('Logout');
                toastr.error('Logout failed, please try again.', '', {
                    closeButton: true,
                    timeOut: 5000,
                    extendedTimeOut: 0
                });
            }
        });
    });
</script>
<!--Update the CSRF Token-->
<script>
    function updateCsrfToken(newToken) {
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
    }
</script>


<!--Carting JS-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        loadCartItems();

        function checkUserAuth(callback) {
            $.ajax({
                url: "{{ route('auth.check') }}",
                type: "GET",
                success: function(response) {
                    callback(response.authenticated); // Send auth status to callback
                },
                error: function() {
                    callback(false); // Assume not authenticated on error
                }
            });
        }

        $(document).on("click", ".add-to-cart", function() {
            let productId = $(this).data("id");
            let productName = $(this).data("name");
            let productPrice = $(this).data("price");
            let size_horiz = $(".layout-horiz-size").text().trim(); // Gets "58 cm"
            let size_vert = $(".layout-vert-size").text().trim(); // Gets "58 cm"
            let selectedOption = $("input[name='options-outlined']:checked").val();

            checkUserAuth(function(isLoggedIn) {
                if (!isLoggedIn) {
                    //     toastr.error("You must be logged in to add items to the cart."); // Display general error message
                    //   $('#signinmodal').modal('show');
                    $.ajax({
                        url: "{{ route('front.cart.add') }}",
                        type: "POST",
                        data: {
                            id: productId,
                            name: productName,
                            price: productPrice,
                            quantity: 1,
                            size_horiz: size_horiz,
                            size_vert: size_vert,
                            frame: selectedOption
                        },
                        success: function(response) {

                            // Example Usage:
                            loadCartItems();
                            toastr.success(response.message);
                            setTimeout(function() {
                                openOffcanvas("#add-to-cart");
                            }, 1000);

                        },
                        error: function(xhr) {
                            alert("Error: " + xhr.responseJSON.message);
                        }
                    });
                } else {
                    $.ajax({
                        url: "{{ route('front.cart.add') }}",
                        type: "POST",
                        data: {
                            id: productId,
                            name: productName,
                            price: productPrice,
                            quantity: 1,
                            size_horiz: size_horiz,
                            size_vert: size_vert,
                            frame: selectedOption
                        },
                        success: function(response) {

                            // Example Usage:
                            loadCartItems();
                            toastr.success(response.message);
                            setTimeout(function() {
                                openOffcanvas("#add-to-cart");
                            }, 1000);

                        },
                        error: function(xhr) {
                            alert("Error: " + xhr.responseJSON.message);
                        }
                    });
                }
            });
        });

        function loadCartItems() {
            $.ajax({
                url: "{{ route('front.cart.items') }}",
                type: "GET",
                success: function(response) {
                    $(".address_payment").html(response.address_payment);
                    $(".button_cart").html(response.button_cart +
                        ' <i class="fa-solid fa-cart-shopping fa-fw"></i>');

                },
                error: function(xhr) {
                    console.error("Error loading cart:", xhr.responseText);
                    $("#cart-items").html("<p>Failed to load cart items.</p>");
                }
            });
        }


        $(document).on("click", ".remove-from-cart", function() {
            $.ajax({
                url: "{{ route('front.cart.remove') }}",
                type: "POST",
                data: {
                    id: $(this).data("id")
                },
                success: function(response) {
                    loadCartItems();
                }
            });
        });

        $("#clear-cart").click(function() {
            $.post("{{ route('front.cart.clear') }}", function(response) {
                alert(response.message);
                loadCartItems();
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        function checkUserAuth() {
            $.ajax({
                url: "{{ route('auth.check') }}",
                type: "GET",
                success: function(response) {
                    if (response.authenticated) {
                        console.log("User is logged in:", response.user);
                        $("#auth-status").text("Logged in as " + response.user.name);
                    } else {
                        console.log("User is not logged in.");
                        $("#auth-status").text("Not Logged In");
                    }
                },
                error: function(xhr) {
                    console.error("Error checking authentication:", xhr);
                }
            });
        }

        checkUserAuth(); // Run when the page loads

        $("#check-auth-btn").click(function() {
            checkUserAuth(); // Run when the button is clicked
        });
    });
</script>
<script>
    function openOffcanvas(selector) {
        let offcanvasElement = new bootstrap.Offcanvas($(selector)[0]);
        offcanvasElement.show();
    }


    document.addEventListener("DOMContentLoaded", function() {
        Livewire.on('cartUpdated', function() {
            console.log('Cart was updated!');
            // Optionally, update UI elements like cart count
        });
    });
</script>
<script>
    function showComingSoon() {
        Swal.fire({
            title: "Coming Soon!",
            text: "We're working on this feature.",
            icon: "info",
            confirmButtonText: "OK"
        });
    }
</script>
