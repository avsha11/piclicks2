<!DOCTYPE html>
<html lang="en">

<head>
    <!--  Title -->
    <title>Abafon</title>
    <!--  Required Meta Tag -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="handheldfriendly" content="true" />
    <meta name="MobileOptimized" content="width" />
    <meta name="description" content="Abafon" />
    <meta name="author" content="" />
    <meta name="keywords" content="Abafon" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!--  Favicon -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('admin/images/logos/favicon.ico') }}" />
    <!-- Owl Carousel  -->
    <link rel="stylesheet" href="{{ asset('admin/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">

    <!-- Core Css -->
    <!-- <link rel="stylesheet" href="dist/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css"> -->
    <!-- <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet"> -->
    <link id="themeColors" rel="stylesheet"
        href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css" />
    <link id="themeColors" rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" />
    <link id="themeColors" rel="stylesheet" href="{{ asset('admin/css/style.min.css') }}" />
</head>
<style>
    table#zero_config {
        width: 100% !important;
    }
</style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-theme="blue_theme" data-layout="vertical" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!--  Main wrapper -->
        <div class="body-wrapper">
            @include('admin.include.header')

            @yield('content')

            @include('admin.include.footer')
        </div>
    </div>


    <!--  Import Js Files -->
    <script src="{{ asset('admin/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <!--  core files -->
    <script src="{{ asset('admin/js/app.min.js') }}"></script>
    <script src="{{ asset('admin/js/app.init.js') }}"></script>
    <script src="{{ asset('admin/js/app-style-switcher.js') }}"></script>
    <script src="{{ asset('admin/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('admin/js/custom.js') }}"></script>
    <!--  current page js files -->
    <script src="{{ asset('admin/owl.carousel/dist/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('admin/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('admin/js/dashboard5.js') }}"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>
