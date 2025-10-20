<header class="header_area header_v1 transparent_header">
    <div class="container">
        <div class="mobile_wrapper">
            <div class="mobile_header">
                <div class="row align-items-center">
                    <div class="col-4">
                        <div class="brand_logo">
                            <a href="{{ url('/') }}"><img src="{{ asset(env('LOGO_PATH', 'assets/images/logo.png')) }}" class="img-fluid"
                                    alt></a>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="text-end">
                            <div class="flex-set">
                                <div class="tool-page-menu">
                                    <a class="btn btn-primary btn-lg button_cart" data-bs-toggle="offcanvas"
                                        href="#add-to-cart" role="button" aria-controls="offcanvasExample">
                                        <span id="cartTotals">@include('front.partials.Cart.cart-total')</span>
                                    </a>
                                </div>
                                <div id="top_tray">
                                    @include('front.partials.top-offset', ['title' => 'Card Title'])
                                </div>

                            </div>
                        </div>
                        <div class="offcanvas offcanvas-start left_menu" tabindex="-1" id="offcanvasExample"
                            aria-labelledby="offcanvasExampleLabel">
                            <div class="offcanvas-header align-items-start">
                                <div class="d-flex justify-content-between w-100">
                                    <!-- Show if user is logged in -->


                                    <div id="user-profile"
                                        style="{{ Auth::check() ? 'display: block;' : 'display: none;' }}">
                                        <p class="mb-2">Welcome, <b
                                                id="user-name">{{ Auth::check() ? Auth::user()->name : '' }}</b>!</p>
                                        {{-- @if (Auth::check())
                                        <img id="user-photo" src="{{ asset('/storage/' . Auth::user()->profile_picture) }}"
                                        alt="User Photo" class="rounded-circle" width="40" height="40">
                                        @else
                                        <img id="user-photo" src="{{ asset('/storage/profileImage/user.png') }}"
                                            alt="User Photo" class="rounded-circle" width="40" height="40">
                                        @endif --}}
                                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="button" id="logoutButton" class="btn btn-danger">
                                                <i class="fa fa-sign-out-alt"></i> Logout
                                            </button>
                                        </form>
                                    </div>

                                    <div id="login-signup"
                                        style="{{ Auth::check() ? 'display: none;' : 'display: block;' }}">
                                        <p class="mb-2">Please <b>Sign Up</b>! It's Free and lets you place and track
                                            orders.</p>
                                        <a href="javascript:void(0)" data-bs-toggle="modal"
                                            data-bs-target="#signinmodal" class="btn btn-primary">Login or Signup</a>
                                    </div>


                                </div>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                            </div>


                            <div class="offcanvas-body">
                                <div class="set_body_off">
                                    <div class="off-menu">
                                        <ul class="main-offmenu">
                                            <li>
                                                <h3>Create your design</h3>
                                            </li>
                                            <li><a href="{{ route('front.upload-photos') }}"><i
                                                        class="fa-solid fa-border-all fa-fw me-2"></i>Design your
                                                    Collage</a></li>
                                            <li><a href="{{ route('front.art-gallery') }}"><i
                                                        class="fa-solid fa-table-cells fa-fw me-2"></i>
                                                    Art Gallery</a></li>
                                            <li>

                                                <a href="javascript:;" tabindex="0" id="refreshLink"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal">
                                                    <i class="fa-solid fa-arrows-rotate fa-fw me-2 "></i>
                                                    Refresh Photo Tiles

                                                </a>
                                            </li>
                                            <li><a href="{{ route('front.giftcard') }}"><i
                                                        class="fa-solid fa-gifts fa-fw me-2"></i>Gift
                                                    Cards</a></li>
                                        </ul>
                                        <hr>
                                        <!--Side Bar section Start-->
                                        <div id="sub-offmenu">

                                            @include('front.partials.home-left-offset', [
                                                'title' => 'Card Title',
                                            ])
                                        </div>
                                        <!--Side Bar section End-->
                                    </div>
                                </div>
                                <div class="logo_title text-center">
                                    <a href="{{ url('/') }}"><img src="{{ asset(env('LOGO_PATH', 'assets/images/logo.png')) }}"
                                            class="img-fluid" alt></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<div class="offcanvas offcanvas-end right_menu " tabindex="-1" id="add-to-cart"
    aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <div class="">
            <h3>Cart @if (!empty($cart))
                    <span>Cart ({{ count($cart) }})</span>
                @endif
            </h3>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="address_payment">

            @include('front.partials.Offsets.home-right-offset', ['title' => 'Card Title'])
        </div>

    </div>
</div>
<!-- add_address -->
<div class="modal fade" id="add_address" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>



<!-- Button trigger modal -->


<!-- Modal -->
<div class="modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog popup-modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 p-0">

                <button type="button" class="btn-close order-modal-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                </divv <div class="modal-body">
                <div class="on_hover text-center">
                    <p class="px-3 pt-4"><strong>When you're ready to refresh your wall art,</strong></p>
                    <p class="mb-3 px-3">it's simple to order just the photo tiles you need without the wall fasteners.
                    </p>
                    <p class="mb-2 px-3">Here's how: Add any item to your cart, then just click the “refresh” button
                        before checking out.</p>
                    <!-- <i class='fa-solid fa-arrows-rotate fa-fw fs-3 me-2 icon-green'></i> -->

                    <div class="text-center">
                        <img src=" {{ asset('assets/images/modalicon.png') }}" alt="" class="refreshimg">
                    </div>

                    <img src=" {{ asset('assets/images/timetorefil.gif') }}" alt=""
                        class="modalgif w-100 mt-2">
                </div>
            </div>

        </div>
    </div>
</div>
