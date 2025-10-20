<style>

</style>

<!-- Sidebar Start -->
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="{{ route('admin.dashboard') }}" class="text-nowrap logo-img">
                <img src="{{ asset(env('LOGO_PATH', 'assets/images/logo.png')) }}" class="dark-logo" width="180" alt="" />
                <!-- <img src="dist/images/logo.png" class="light-logo"  width="180" alt="" /> -->
            </a>
            <div class="close-btn d-lg-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-8 text-muted"></i>
            </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <ul id="sidebarnav">


                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-chart-infographic"></i>
                        </span>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                                <div class="round-16 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-circle"></i>
                                </div>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-folder"></i>
                        </span>
                        <span class="hide-menu">Frames Menu</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a href="{{ route('admin.framesPage') }}" class="sidebar-link">
                <div class="round-16 d-flex align-items-center justify-content-center">
                    <i class="ti ti-circle"></i>
                </div>
                <span class="hide-menu">Frames</span>
                </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.tilesPage') }}" class="sidebar-link">
                        <div class="round-16 d-flex align-items-center justify-content-center">
                            <i class="ti ti-circle"></i>
                        </div>
                        <span class="hide-menu">Tiles</span>
                    </a>
                </li>
            </ul>
            </li> --}}

            <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-folder"></i>
                    </span>
                    <span class="hide-menu">User Menu</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.allUsersPage') }}" class="sidebar-link">
                            <div class="round-16 d-flex align-items-center justify-content-center">
                                <i class="ti ti-circle"></i>
                            </div>
                            <span class="hide-menu">Users</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.countryShippingAmountList') }}" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-folder"></i>
                    </span>
                    <span class="hide-menu">Country Amount Management</span>
                </a>
            </li>



            <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-folder"></i>
                    </span>
                    <span class="hide-menu">Orders Management</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.orderList') }}" class="sidebar-link">
                            <div class="round-16 d-flex align-items-center justify-content-center">
                                <i class="ti ti-circle"></i>
                            </div>
                            <span class="hide-menu">Orders List</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('admin.giftcard') }}" aria-expanded="false">
                            <span class="d-flex">
                                <i class="ti ti-folder"></i>
                            </span>
                            <span class="hide-menu">Giftcard Lists.</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-folder"></i>
                    </span>
                    <span class="hide-menu">Art Gallery Management</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.galleryList') }}" class="sidebar-link">
                            <div class="round-16 d-flex align-items-center justify-content-center">
                                <i class="ti ti-circle"></i>
                            </div>
                            <span class="hide-menu">Art Gallery Management</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.collection') }}" class="sidebar-link">
                            <div class="round-16 d-flex align-items-center justify-content-center">
                                <i class="ti ti-circle"></i>
                            </div>
                            <span class="hide-menu">Collection Management</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.tag') }}" class="sidebar-link">
                            <div class="round-16 d-flex align-items-center justify-content-center">
                                <i class="ti ti-circle"></i>
                            </div>
                            <span class="hide-menu">Tag Management</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.couponList') }}" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-folder"></i>
                    </span>
                    <span class="hide-menu">Coupon Management</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.price') }}" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-folder"></i>
                    </span>
                    <span class="hide-menu">Product/Price Management.</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.discount') }}" aria-expanded="false">
                    <span class="d-flex">
                        <i class="ti ti-folder"></i>
                    </span>
                    <span class="hide-menu">Discount Management.</span>
                </a>
            </li>
             <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('admin.Giftcardlist') }}" aria-expanded="false">
                        <span class="d-flex">
                            <i class="ti ti-folder"></i>
                        </span>
                        <span class="hide-menu">Giftcard Management.</span>
                    </a>
                </li>

            </ul>

        </nav>
        <div class="fixed-profile p-3 bg-light-secondary rounded sidebar-ad mt-3">
            <div class="hstack gap-3">
                <div class="john-img">
                    <img src="dist/images/profile/user-1.jpg" class="rounded-circle" width="40" height="40"
                        alt="">
                </div>
                <div class="john-title">
                    <h6 class="mb-0 fs-4 fw-semibold">Mathew</h6>
                    <span class="fs-2 text-dark">Designer</span>
                </div>
                <button class="border-0 bg-transparent text-primary ms-auto" tabindex="0" type="button"
                    aria-label="logout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="logout">
                    <i class="ti ti-power fs-6"></i>
                </button>
            </div>
        </div>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
<!--  Sidebar End -->