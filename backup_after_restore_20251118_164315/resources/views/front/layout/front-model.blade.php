<div class="modal fade" id="preloaderModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body text-center py-5">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <p class="fs-4 my-3" id="preloaderModalText"></p>
            </div>
        </div>
    </div>
</div>





<!--signup modal-->
<div class="modal fade" id="signupmodal"data-bs-backdrop="static" tabindex="-1" aria-labelledby="signupmodalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
        <div class="modal-content border-0">
            <div class="modal-header border-0 align-items-start">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <h4 class="m-0 lable text-black">Sign Up</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="h-100 position-relative" style="max-width: 80%; margin: 0 auto;">
                    <!-- Login Form -->
                    <form id="signupForm" method="POST" action="{{ route('signup') }}">
                        @csrf
                        <div class="w-100 w-md-100 signup-banner">
                            <!-- Full Name -->
                            <div class="form-floating mb-3">
                                <input name="fullname" id="fullname" class="form-control"
                                    placeholder="Enter your full name" required="">
                                <label for="fullname">Full Name</label>
                                <div id="fullnameError" class="error-message text-danger"></div>
                            </div>

                            <!-- Email -->
                            <div class="form-floating mb-3">
                                <input name="email" id="email" class="form-control" placeholder="Email"
                                    required="">
                                <label for="email">Email</label>
                                <div id="emailError" class="error-message text-danger"></div>
                            </div>

                            <!-- Password -->
                            <div class="form-floating mb-3">
                                <input name="password" id="password" type="password" class="form-control"
                                    placeholder="Password" required="">
                                <label for="password">Password</label>
                                <div id="passwordError" class="error-message text-danger"></div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-floating mb-3">
                                <input name="password_confirmation" id="password_confirmation" type="password"
                                    class="form-control" placeholder="Confirm Password" required="">
                                <label for="password_confirmation">Confirm Password</label>
                                <div id="passwordConfirmationError" class="error-message text-danger"></div>
                            </div>

                            <!-- Sign Up Button -->
                            <button id="signupButton" type="button"
                                class="mb-3 default-btn btn btn-primary w-100 btn-lg">
                                Sign Up
                            </button>

                        </div>
                    </form>



                    <div id="signupMessage"></div> <!-- To display success/error messages -->



                </div>
            </div>
        </div>
    </div>
</div>


<!--signin modal-->
<div class="modal fade" id="signinmodal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="signinmodalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
        <div class="modal-content border-0">
            <div class="modal-header border-0 align-items-start">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <h4 class="m-0 lable text-black">Login</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="h-100 position-relative" style="max-width: 85%; margin: 0 auto;">
                    <!-- Login Form -->
                    <p id="showLoginError"></p>
                    <form>
                        <div class="w-100 w-md-100 signup-banner">
                            <div class="form-floating mb-3">
                                <input name="email" class="form-control" id="floatingEmail" placeholder="Email"
                                    required="">
                                <label for="floatingEmail">Email</label>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" name="password" class="form-control" id="Password"
                                    placeholder="Password" required>
                                <label for="Password">Password</label>
                                <span class="toggle-password position-absolute"
                                    style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>

                            <button type="submit" class="mb-3 default-btn btn btn-primary w-100 btn-lg">
                                Continue
                            </button>
                            <p
                                style="font-size:14px; line-height:18px; text-align:center; width:80%; margin:10px auto 0;">
                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                    data-bs-target="#forgotPasswordModal">Forgot Password</a>
                            </p>

                            <button type="button" class="btn btn-social  w-100 btn-lg mt-3"
                                onclick="alert('Coming Soon');" data-toggle="tooltip" data-placement="top"
                                title="Coming Soon">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 18 18" aria-hidden="true">
                                        <title>Google</title>
                                        <g fill="none" fill-rule="evenodd">
                                            <path fill="#4285F4"
                                                d="M17.64 9.2045c0-.6381-.0573-1.2518-.1636-1.8409H9v3.4814h4.8436c-.2086 1.125-.8427 2.0782-1.7959 2.7164v2.2581h2.9087c1.7018-1.5668 2.6836-3.874 2.6836-6.615z">
                                            </path>
                                            <path fill="#34A853"
                                                d="M9 18c2.43 0 4.4673-.806 5.9564-2.1805l-2.9087-2.2581c-.8059.54-1.8368.859-3.0477.859-2.344 0-4.3282-1.5831-5.036-3.7104H.9574v2.3318C2.4382 15.9832 5.4818 18 9 18z">
                                            </path>
                                            <path fill="#FBBC05"
                                                d="M3.964 10.71c-.18-.54-.2822-1.1168-.2822-1.71s.1023-1.17.2823-1.71V4.9582H.9573A8.9965 8.9965 0 0 0 0 9c0 1.4523.3477 2.8268.9573 4.0418L3.964 10.71z">
                                            </path>
                                            <path fill="#EA4335"
                                                d="M9 3.5795c1.3214 0 2.5077.4541 3.4405 1.346l2.5813-2.5814C13.4632.8918 11.426 0 9 0 5.4818 0 2.4382 2.0168.9573 4.9582L3.964 7.29C4.6718 5.1627 6.6559 3.5795 9 3.5795z">
                                            </path>
                                        </g>
                                    </svg>
                                </span>
                                <span style="transform: translateY(2px)">Sign in with Google</span>
                            </button>
                            <button type="button" class="btn btn-social  w-100 btn-lg mt-3 mb-3"
                                onclick="alert('Coming Soon');" data-toggle="tooltip" data-placement="top"
                                title="Coming Soon">
                                <span>
                                    <svg viewBox="0 0 24 24" width="18" aria-hidden="true"
                                        style="color: rgb(15, 20, 25);">
                                        <g>
                                            <path
                                                d="M16.365 1.43c0 1.14-.493 2.27-1.177 3.08-.744.9-1.99 1.57-2.987 1.57-.12 0-.23-.02-.3-.03-.01-.06-.04-.22-.04-.39 0-1.15.572-2.27 1.206-2.98.804-.94 2.142-1.64 3.248-1.68.03.13.05.28.05.43zm4.565 15.71c-.03.07-.463 1.58-1.518 3.12-.945 1.34-1.94 2.71-3.43 2.71-1.517 0-1.9-.88-3.63-.88-1.698 0-2.302.91-3.67.91-1.377 0-2.332-1.26-3.428-2.8-1.287-1.82-2.323-4.63-2.323-7.28 0-4.28 2.797-6.55 5.552-6.55 1.448 0 2.675.95 3.6.95.865 0 2.222-1.01 3.902-1.01.613 0 2.886.06 4.374 2.19-.13.09-2.383 1.37-2.383 4.19 0 3.26 2.854 4.42 2.955 4.45z">
                                            </path>
                                        </g>
                                    </svg>
                                </span>
                                <span style="transform: translateY(2px)">Sign in with Apple</span>
                            </button>
                            <div class="or-login">
                                <hr>
                                <span>Or</span>
                            </div>
                            <p style="font-size:20px; line-height:18px; text-align:center; margin:10px auto 0;">
                                Quickly <a href="javascript:void(0)" data-bs-toggle="modal"
                                    data-bs-target="#signupmodal"><strong style="color: green;font-size:25px;">Sign
                                        Up</strong> here to save your work !</a>
                            <p
                                style="font-size:14px; line-height:18px; text-align:center; width:80%; margin:20px auto;">
                                By signing up you agree to our <a href="">Terms of Use</a> and <a
                                    href="">Privacy Policy</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!--forgotPassword modal-->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-black">Forgot Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="forgotPasswordForm">
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="emailForgot" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Password Email</button>
                </form>
                <p class="text-danger mt-2" id="forgotPasswordError"></p>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="paymentDone" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body text-center py-5">
                <i class="fa-regular fa-circle-check text-success fs-2" style="font-size: 100px !important;"></i>
                <p class="fs-4 my-3">Your Payment has been successfull</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok</button>

            </div>
            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div> -->
        </div>
    </div>
</div>





<!-- Modal -->
<div class="modal fade" id="orderDetail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 align-items-start">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <h3>Order Detail</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body text-center pt-0">
                <div class="order-detail-shipping sticky-top" id="orderDetailDivId">

                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="couponDetail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-0 align-items-start">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body text-center pt-0">
                <i class="fa-regular fa-circle-check text-success fs-2" style="font-size: 100px !important;"></i>
                <p class="fs-4 my-3">Coupon claimed !</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok</button>

            </div>
            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div> -->
        </div>
    </div>
</div>



<div class="modal fade" id="giftCardRedirectModal" tabindex="-1" aria-labelledby="giftCardRedirectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header flex-column">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="{{ asset('assets/images/modalright.png') }}" style="width:120px;">
                <h5 class="modal-title" id="giftCardRedirectModalLabel">Congrats on your gift card!</h5>
                <p>Welcome to piclicks</p>

                <p class="text-center mt-3">To redeem it value and add it to your account, please choose below:</p>

            </div>
            <div class="modal-body">
                <div class="w-75 m-auto">
                    <div class="d-flex gap-2 align-items-center">
                        <a href="#" class="sign-up-btn" data-bs-target="#signupmodal" data-bs-toggle="modal">Sign up</a>
                        <p>if you're new to Piclicks.</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center ">
                        <a href="javascript" class="sign-in-btn" data-bs-target="#signinmodal" data-bs-toggle="modal">Sign in</a>
                        <p>if you already have an account.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
