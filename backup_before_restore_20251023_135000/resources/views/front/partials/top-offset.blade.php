@if (Auth::check())
    <!--<span class="mb-2"><b id="user-name">{{ Auth::check() ? Auth::user()->name : '' }} </b> </span>-->
    {{-- <div id="top_tray"> --}}
        <a href="{{ route('order') }}">

            <img id="user-photo" src="{{ asset('/storage/' . Auth::user()->profile_picture) }}" alt="User Photo"
                class="rounded-circle" width="40" height="40">

        </a>
    {{-- </div> --}}

    <a class="btn" style="color: #ffffff;padding:5px;" data-bs-toggle="offcanvas" href="#add-to-cart" role="button" aria-controls="offcanvasExample">
      <i class="fa-solid fa-cart-shopping fa-fw"></i>
    </a>
    
    <div class="text-end menu-icon " data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample" style="margin-left: 10px;">

        <div id="top_tray">

            <i class="fa-solid fa-bars fa-fw"></i>

        </div>



    </div>
@else
    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#signinmodal">
        <img id="user-photo" src="{{ asset('assets/images/user.png') }}" alt="User Photo" class="rounded-circle" width="40" height="40">
    </a>
   
    @if (!Request::is('design-collage/*') && !Request::is('preview-design-collage/*'))
        <a class="btn"  id="hideShi"style="color: #ffffff;padding:5px;" data-bs-toggle="offcanvas" href="#add-to-cart" 
            role="button" aria-controls="offcanvasExample">
            <i class="fa-solid fa-cart-shopping fa-fw"></i>
        </a>
    @endif

    <div class="text-end menu-icon " data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
        aria-controls="offcanvasExample" style="margin-left: 10px;">
        <i class="fa-solid fa-bars fa-fw"></i>

    </div>
@endif
