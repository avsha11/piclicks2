@if (session('giftcard_action') && session('giftcard_action') == 'not_logged_in')
    <script>
        $(document).ready(function() {
            $("#giftCardRedirectModal").modal('show');
        })
    </script>
@endif
@if (session('giftcard_error'))
    <script>
        Swal.fire({
            title: 'Oops!!',
            text: "{{ session('giftcard_error') }}",
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif
@if (session('giftcard_success'))
    <script>
        Swal.fire({
            title: 'Yups !!',
            text: "{{ session('giftcard_success') }}",
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif
<script>
    let loggedIn = parseInt("{{ auth()->check() ? 1 : 0 }}");
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
                    $('#signupButton').prop('disabled', false).html('Sign Up');
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#' + field + 'Error').html(errors[field].join(', '));
                    }
                    $('#signupButton').prop('disabled', false).html('Sign Up');
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


        $('#signinmodal form').on('submit', async function(e) {
            e.preventDefault();
            // Clear previous error messages
            $('.error-message').html('');
            $('#showLoginError').html('').show();
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
                    console.log('response1', response);
                    if (response.status === 'success') {
                        // Update the UI with user info
                        if (response.user) {
                            toastr.success(response.message, '', {
                                closeButton: true,
                                timeOut: 5000,
                                extendedTimeOut: 0
                            });
                            $('#signinmodal').modal('hide');
                            // Display logged-in user's name and photo in the offcanvas
                            $('#user-name').text(response.user.name);
                            updateCsrfToken(response.new_csrf_token);
                            $('#sub-offmenu').html(response.sub_offmenu);
                            $('#top_tray').html(response.top_tray);
                            $('#user-photo').attr('src', response.user
                                .profile_photo_url);
                            $('#user-profile').show();
                            // Hide login/signup section
                            $('#login-signup').hide();
                            loggedIn = 1;
                            //update the cart item details
                            if (parseFloat(response.cartSummary.total) > 0) {
                                $('.cart-items-updated').html(response.cartSummary
                                    .cartItem);
                                $(".button_cart").html(response.cartSummary.cartTotal);
                                $(".checkout-shopping-cart").html(response.cartSummary
                                    .checkoutshoppingCart);
                                $(".checkout-order-summary,.checkout-review").html(
                                    response
                                    .cartSummary.checkoutOrderSummary);
                            }
                            if (parseFloat(response.cartSummary.total) <= 0) {
                                $(".purchase-item").hide();
                                $(".action-button").attr("style",
                                    "display: none !important;");
                            }
                            if (response.giftcard) {
                                if (response.giftcard.message) {
                                    Swal.fire({
                                        title: 'Alert',
                                        text: response.giftcard.message,
                                        icon: response.giftcard.status,
                                        confirmButtonText: 'OK'
                                    });
                                }
                            }
                        } else {
                            toastr.error(response.message, '', {
                                closeButton: true,
                                timeOut: 5000,
                                extendedTimeOut: 0
                            });
                        }
                        // alert('1');
                    } else if (response.status === 'error') {
                        // alert('2');
                        updateCsrfToken(response.new_csrf_token);
                        $('#sub-offmenu').html(response.sub_offmenu);
                        $('#top_tray').html(response.top_tray);
                        $('#showLoginError').html('<div class="text-danger mt-2">' +
                            response.message + '</div>');
                        setTimeout(function() {
                            $('#showLoginError').fadeOut();
                        }, 5000);
                    } else {
                        // alert('3');
                        $loginButton.prop('disabled', false);
                        $('#showLoginError').html('<div class="text-danger mt-2">' +
                            response.message + '</div>');
                        // Hide error message after 5 seconds
                        setTimeout(function() {
                            $('#showLoginError').fadeOut();
                        }, 5000);
                    }
                    $loginButton.prop('disabled', false).html('Continue');
                },
                error: async function(xhr) {
                    $loginButton.prop('disabled', false).html('Continue');
                    // alert('4');
                    // if (xhr.status === 419) {
                    //     alert('5');
                    //     // CSRF token mismatch — refresh token and retry
                    //     console.log('a');
                    //     let rtn = await refreshTokenAndRetry();
                    //     console.log('after await refreshTokenAndRetry ', rtn);
                    //     if (rtn === true) {
                    //         console.log('inside true');
                    //         console.log('aa');
                    //         $('#signinmodal form').submit();
                    //         return;
                    //     }
                    // }

                    let response = xhr.responseJSON;
                    if (response && response.message) {
                        toastr.error(response.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        }); // Display general error message
                    } else {
                        toastr.error('Invalid login credentials. Please try again.',
                            '', {
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
                }
            });
        });


        $('#forgotPasswordForm').on('submit', function(e) {
            e.preventDefault();
            // Clear previous error messages
            $('.forgotEmailError').removeClass('is-invalid');
            $('.error-message').remove(); // Remove any existing error messages'); 
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
                        // toastr.success(response.message, '', {
                        //     closeButton: true,
                        //     timeOut: 5000,
                        //     extendedTimeOut: 0
                        // });
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#forgotPasswordModal').modal('hide');
                            $('#signinmodal').modal('show');
                        });
                    } else {
                        // toastr.error(response.message, '', {
                        //     closeButton: true,
                        //     timeOut: 5000,
                        //     extendedTimeOut: 0
                        // });
                        // $('#forgotPasswordError').html(response.message).show();
                        // $('.forgotEmailError').addClass('is-invalid');
                        $('input[name="emailForgot"]').after(
                            '<div class="text-danger mt-1 error-message">' + response
                            .message + '</div>'
                        ).addClass('is-invalid');
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text('Send Reset Password Email');
                    // toastr.error('Something went wrong in forget', '', {
                    //     closeButton: true,
                    //     timeOut: 5000,
                    //     extendedTimeOut: 0
                    // });
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
                    $('#hideShi').css('display', 'none');
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
    $('#signinmodal').on('hidden.bs.modal', function() {
        // Reset form fields
        $(this).find('form')[0].reset();
        // Remove error messages
        $('.error-message').remove();
        // Enable submit button
        $('#signinmodal button[type="submit"]').prop('disabled', false).html('Continue');
    });
    $('#signupmodal').on('shown.bs.modal', function() {
        // Ensure submit button is enabled
        $('#signupmodal button[type="submit"]').prop('disabled', false).html('Sign Up');
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
            let button = $(this); // Store the clicked button reference
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // let latitude = 31.0461; //31.0461 ,position.coords.latitude
                        // let longitude = 34.8516; //34.8516 ,position.coords.longitude
                        let latitude = position.coords.latitude;
                        let longitude = position.coords.longitude;
                        console.log("Latitude: " + latitude + ", Longitude: " + longitude);
                        // Call Google API to get country code, then proceed with AJAX
                        getCountryFromLatLng(latitude, longitude, function(countryCode) {
                            addToCart(button, countryCode);
                        });
                    },
                    function(error) {
                        if (error.code === error.PERMISSION_DENIED) {
                            // alert(
                            //     "Location access is required! Please enable it in your browser settings and refresh the page."
                            // );
                        } else {
                            console.error("Error getting location: " + error.message);
                        }
                        // Proceed with default cart addition if location is denied
                        addToCart(button, null);
                    }
                );
            } else {
                // alert("Geolocation is not supported by this browser.");
                addToCart(button, null);
            }
        });

        function getCountryFromLatLng(lat, lng, callback) {
            let apiKey = "{{ env('GOOGLE_API_KEY') }}";
            let url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === "OK") {
                        let countryComponent = data.results[0].address_components.find(component =>
                            component.types.includes("country")
                        );
                        if (countryComponent) {
                            let countryCode = countryComponent
                                .short_name; // Get country code (e.g., "US", "IN", "GB")
                            console.log("Detected Country Code: ", countryCode);
                            callback(countryCode); // Pass country code to callback
                        } else {
                            console.error("Country not found in API response.");
                            callback(null);
                        }
                    } else {
                        console.error("Geocoding API error:", data.status);
                        callback(null);
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    callback(null);
                });
        }

        function addToCart(button, countryCode) {
            let productId = button.data("id");
            let productName = button.data("name");
            let productPrice = button.data("price");
            let size_horiz = $(".layout-horiz-size").text().trim();
            let size_vert = $(".layout-vert-size").text().trim();
            let selectedOption = $("input[name='options-outlined']:checked").val();
            let priceId = $("#priceId").val();
            checkUserAuth(function(isLoggedIn) {
                let requestData = {
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1,
                    size_horiz: size_horiz,
                    size_vert: size_vert,
                    frame: selectedOption,
                    priceId: priceId,
                };
                if (countryCode) {
                    requestData.countryCode = countryCode; // Add country if available
                }
                $.ajax({
                    url: "{{ route('front.cart.add') }}",
                    type: "POST",
                    data: requestData,
                    success: function(response) {
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
            });
        }

        function loadCartItems() {
            $.ajax({
                url: "{{ route('front.cart.items') }}",
                type: "GET",
                success: function(response) {
                    $(".address_payment").html(response.address_payment);
                    $(".button_cart").html(response.button_cart);
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
                async: false,
                success: function(response) {
                    if (response.authenticated) {
                        console.log("User is logged in:", response.user);
                        $("#auth-status").text("Logged in as " + response.user.name);
                        return true;
                    } else {
                        console.log("User is not logged in.");
                        $("#auth-status").text("Not Logged In");
                        return false;
                    }
                },
                error: function(xhr) {
                    console.error("Error checking authentication:", xhr);
                    return false;
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
<script>
    function updateQuantity(cartItemId, action, itemamount) {
        // let cartId = $(this).data("id");
        // let action = $(this).data("action");
        $.ajax({
            url: "{{ route('front.cart.update') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: cartItemId,
                action: action
            },
            success: function(response) {
                if (response.status == 1) {
                    $('.item-quantity' + cartItemId).text(response.cartSummary.updatedQuantity);
                    $('.cart-items-updated').html(response.cartSummary.cartItem);
                    // $('.cart-shipping').html(response.cartSummary.cartView);
                    // $('.cartTotals').html(response.cartSummary.cartTotal);
                    $(".button_cart").html(response.cartSummary.cartTotal);
                    $(".checkout-shopping-cart").html(response.cartSummary.checkoutshoppingCart);
                    // $(".checkout-overview").html(response.cartSummary.checkOutOverview);
                    // $(".checkout-cart-items").html(response.cartSummary.checkoutCartItems);
                    $(".checkout-order-summary,.checkout-review").html(response.cartSummary
                        .checkoutOrderSummary);
                    toastr.success(response.success);
                    // if (parseFloat(response.cartSummary.total) <= 0) {
                    //     $(".purchase-item").hide();
                    //     $(".action-button").attr("style", "display: none !important;");
                    // }
                } else {
                    toastr.error(response.success);
                }
            },
            error: function(res) {
                if (res.responseJSON.error) {
                    toastr.error(res.responseJSON.error);
                } else {
                    toastr.error(res.responseJSON.message);
                }
            }
        });
    }

    function deleteCartItem(cartItemId) {
        $.ajax({
            url: "{{ route('front.cart.update') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: cartItemId,
                action: "delete"
            },
            success: function(response) {
                if (response.status == 1) {
                    console.log('response', response);
                    $('.cart-items-updated').html(response.cartSummary.cartItem);
                    // $('.item-quantity' + cartItemId).text(response.cartSummary.updatedQuantity);
                    // $('.cart-shipping').html(response.cartSummary.cartView);
                    // $('.cartTotals').html(response.cartSummary.cartTotal);
                    $(".button_cart").html(response.cartSummary.cartTotal);
                    $(".checkout-shopping-cart").html(response.cartSummary.checkoutshoppingCart);
                    // $(".checkout-overview").html(response.cartSummary.checkOutOverview);
                    // $(".checkout-cart-items").html(response.cartSummary.checkoutCartItems);
                    $(".checkout-order-summary,.checkout-review").html(response.cartSummary
                        .checkoutOrderSummary);
                    toastr.success(response.success);
                    // Check if cart total is 0, then hide .purchase-item
                    if (parseFloat(response.cartSummary.total) <= 0) {
                        // $('.checkout-cart-items').html('Your Cart is Empty');
                        // $(".purchase-item").hide();
                        $(".cart_pro").hide();
                        $(".action-button").attr("style", "display: none !important;");
                    }
                } else {
                    toastr.error(response.success);
                }
            }
        });
    }

    function getOrderDetail(orderId) {
        $.ajax({
            url: "{{ route('order-detail') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                orderId: orderId,
            },
            success: function(response) {
                if (response.status == 1) {
                    $('#orderDetailDivId').html(response.data);
                    $('#orderDetail').modal('show');
                } else {
                    toastr.error(response.success);
                }
            }
        });
    }

    function checkoutItems() {
        $.ajax({
            url: "{{ route('auth.check') }}",
            type: "GET",
            success: function(response) {
                if (response.authenticated) {
                    $(window).off('beforeunload');
                    window.location.href = "{{ route('checkout') }}"
                } else {
                    console.log("User is not logged in.");
                    let err = "Kindly sign-up or sign-in";
                    toastr.error(err);
                    $('#signinmodal').modal('show');
                }
            },
            error: function(xhr) {
                console.error("Error checking authentication:", xhr);
            }
        });
        return false;
    }
    // refresh product item
    function refreshProductFunction(productId, priceId, type) {
        $.ajax({
            url: "{{ route('front.refresh-product-item') }}",
            type: 'POST',
            data: {
                type: type,
                productId: productId,
                priceId: priceId,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            beforeSend: function() {
                $('#refresh_btn').prop('disabled', true);
            },
            success: function(res) {
                $('#refresh_btn').prop('disabled', false);
                if (res.status == 1) {
                    toastr.success(res.message);
                    if (type == 'preview') {
                        location.reload();
                    } else {
                        updateAmounts();
                    }
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(error) {
                $('#refresh_btn').prop('disabled', false);
                console.log(error);
            }
        });
        return false;
    }

    function updateAmounts() {
        var selectedCountry = $('#shopping_country').val();
        $.ajax({
            url: "{{ route('front.cart.getCountryShppingAmount') }}", // Replace with your route
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                country: selectedCountry
            },
            dataType: "json",
            success: function(response) {
                if (response.status === 1) {
                    $(".button_cart").html(response.cartSummary.cartTotal);
                    $('.cart-items-updated').html(response.cartSummary.cartItem);
                    $("#hidden_final_amount").val(response.cartSummary.total);
                    $("#hidden_total_quantity").val(response.cartSummary.cartTotalQuantity);
                    $(".checkout-shopping-cart").html(response.cartSummary.checkoutshoppingCart);
                    $(".checkout-order-summary,.checkout-review").html(response.cartSummary
                        .checkoutOrderSummary);
                } else {
                    toastr.error("Error fetching cart details. Please try again.");
                }
            },
            error: function() {
                toastr.error("An error occurred. Please try again.");
            }
        });
    }

    async function refreshTokenAndRetry() {
        console.log('inside refreshTokenAndRetry');
        await $.get("{{ route('front.refresh-token') }}")
            .done(function(response) {
                const newToken = response.csrf_token;
                console.log('newToken ', newToken);
                updateCsrfToken(newToken);
                return true;
            })
            .fail(function() {
                console.log('newToken fail ');
                toastr.error("Session expired. Please reload the page and try again.");
                return false;
            });
    }

    
</script>
