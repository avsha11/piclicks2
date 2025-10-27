<ul class="sub-offmenu">

    <!--<li>-->

    <!--    @if (Auth::check())
-->

    <!--    <a href="{{ route('profile') }}">My Account</a>-->

<!--    @else-->

    <!--    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#signinmodal">My Account</a>-->

    <!--
@endif-->

    <!--</li>-->

    {{-- <li><a href="javascript:;" data-bs-toggle="offcanvas" href="#add-to-cart" aria-controls="offcanvasExample"
            role="button">My Cart</a></li> --}}

    <li>
        @if (Auth::check())
            <a href="{{ route('order') }}">My Orders</a>
        @endif
    </li>

    <li><a href="contact-us.php">Help Center</a></li>

    <li><a href="privacy.php">Privacy Policy</a></li>

    <li><a href="terms.php">Terms of Use</a></li>

</ul>
