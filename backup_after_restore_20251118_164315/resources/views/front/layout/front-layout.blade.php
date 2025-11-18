<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @if (trim($__env->yieldPushContent('title')))
            @stack('title'), {{ env('APP_NAME') }}
        @else
            {{ env('APP_NAME') }}
        @endif
    </title>
    <!--csrf_token-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--Toaster Css-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    @livewireStyles
    <link rel="shortcut icon" href="images/favicon.ico" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slick-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sidebar-menu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?time={{ rand('2', '34') }}">
</head>


<body>

    @include('front.layout.front-header')

    @yield('content')

    @include('front.layout.front-footer')
    @include('front.layout.front-model')

    <!-- Load jQuery and libraries FIRST before custom scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--Toaster JS-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Isotope for filtering/masonry layouts -->
    <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
    <!-- WOW.js for scroll animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <script src="{{ asset('assets/js/slick.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/sidebar-menu.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/custom.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/common/ajaxSubmission.js') }}?time={{ rand('2', '34') }}"></script>
    <script src="{{ url('livewire/livewire.js') }}" data-turbo-eval="false" data-turbolinks-eval="false"></script>
    
    <!-- Load custom scripts AFTER libraries -->
    @include('front.layout.front-script')
    
    @stack('js')

    
<script>
    // Enable Bootstrap popover
    const refreshLink = document.getElementById('refreshLink');
    const popover = new bootstrap.Popover(refreshLink, {
        trigger: 'focus',
        placement: 'right',
        customClass: 'custom-popover',
        html: true
    });

    // Optional: Toggle popover on click (ensure it closes when clicked again)
    refreshLink.addEventListener('click', function(e) {
        e.preventDefault();
        popover.toggle();
    });
</script>

<script>
    var myModal = new bootstrap.Modal(document.getElementById('exampleModal'), {
        backdrop: false
    });
    // myModal.show();
</script>

</body>

</html>
