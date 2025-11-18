<div class="col-lg-3 col-md-4 px-0 pe-md-3 mb-3 mb-md-0">
    <div class="left-side-menu sticky-top">
        <p class="{{ request()->routeIs('order') ? 'active' : '' }}">
            <a href="{{ route('order') }}">Orders</a>
        </p>

        <p class="{{ request()->routeIs('current-draft') ? 'active' : '' }}">
            <a href="{{ route('current-draft') }}">Current Drafts</a>
        </p>

        <p class="{{ request()->routeIs('change-password') ? 'active' : '' }}">
            <a href="{{ route('change-password') }}">Change Password</a>
        </p>

        <p class="{{ request()->routeIs('profile') ? 'active' : '' }}">
            <a href="{{ route('profile') }}">Edit Profile</a>
        </p>

        {{-- <p><a href="javacript:void();">History</a></p> --}}
        <p class="{{ request()->routeIs('received-giftcards') ? 'active' : '' }}">
            <a href="{{ route('received-giftcards') }}">Received Gift
                Cards</a>
        </p>
        <!--<p><a href="javacript:void();">Draft Listing</a></p>-->
        <p><a href="{{ route('logout') }}">
                <button type="button" id="logoutButton" class="btn btn-danger">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </button>
            </a>
        </p>
    </div>
</div>
