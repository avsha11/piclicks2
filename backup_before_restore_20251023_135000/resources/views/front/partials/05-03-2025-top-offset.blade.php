@if (Auth::check())
    <!--<span class="mb-2"><b id="user-name">{{ Auth::check() ? Auth::user()->name : '' }} </b> </span>-->
    {{-- <div id="top_tray"> --}}
        <a href="{{ route('order') }}">

            <img id="user-photo" src="{{ asset('/storage/' . Auth::user()->profile_picture) }}" alt="User Photo"
                class="rounded-circle" width="40" height="40">

        </a>
    {{-- </div> --}}

    <div class="text-end menu-icon " data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample">

        <div id="top_tray">

            <i class="fa-solid fa-bars fa-fw"></i>

        </div>



    </div>
@else
    <div class="text-end menu-icon " data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample">



        <i class="fa-solid fa-bars fa-fw"></i>





    </div>
@endif
