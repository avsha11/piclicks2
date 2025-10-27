@extends('front.layout.front-layout')
{{-- @push('title', 'Home') --}}
@section('content')
    <style>
        .sign-up-btn,
        .sign-up-btn:hover {
            background: #2f5389;
            color: #fff;
            padding: 5px 10px;
            border-radius: 8px;
        }

        .sign-in-btn,
        .sign-in-btn:hover {
            background: #45b749;
            color: #fff;
            padding: 5px 12px;
            border-radius: 8px;
            margin-top: 8px;
        }

        .header_area.sticky {
            position: fixed;
        }

        .transparent_header {
            position: absolute;
            top: 0px;
            left: 0;
            width: 100%;
            z-index: 337;
        }

        .brand_logo img {
            -webkit-filter: invert(100%);
            /* Safari/Chrome */
            filter: invert(100%);
        }

        .mobile_header .menu-icon {
            color: #fff;
        }

        .sticky .brand_logo img {
            -webkit-filter: invert(0%);
            /* Safari/Chrome */
            filter: invert(0%);
        }

        .sticky .mobile_header .menu-icon {
            color: #000;
        }
    </style>
    <section class="top-video-section">
        <div class="main-video-setion">
            <video autoplay loop muted preload>
                <source src="{{ asset('assets/video/slider-video1.mp4') }}">
            </video>
        </div>
        <div class="text-onvideo">
            <div class="container">
                <h1 class="mb-3">You can Create Collage<br>I can Print!!</h1>
                <a href="{{ route('front.upload-photos') }}" class="btn btn-primary btn-lg w-30">Let's Start</a>
            </div>
        </div>
        <!--div class="container-fluid banner-bottom"></div-->
    </section>


    <section class="piclicks_topsell">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="top-sell-title">
                        <h2>Top selling products</h2>
                        <p>When unknow printer took a gallery of type and scramblted it to make a type specimen book</p>
                    </div>
                </div>
            </div>
            <div class="topsell_slider_content">
                <div class="single_project cat1 cat5">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/gallery1.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info art-info">
                            <h4><a href="#">Wall art title</a></h4>
                            <h3>$540</h3>
                            <a href="" class="btn btn-primary btn-sm">Buy now</a>
                        </div>
                    </div>
                </div>
                <div class="single_project cat1 cat5">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/gallery2.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info art-info">
                            <h4><a href="#">Wall art title</a></h4>
                            <h3>$540</h3>
                            <a href="" class="btn btn-primary btn-sm">Buy now</a>
                        </div>
                    </div>
                </div>
                <div class="single_project cat2 cat4">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/gallery3.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info art-info">
                            <h4><a href="#">Wall art title</a></h4>
                            <h3>$540</h3>
                            <a href="" class="btn btn-primary btn-sm">Buy now</a>
                        </div>
                    </div>
                </div>
                <div class="single_project cat3 cat1">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/gallery4.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info art-info">
                            <h4><a href="#">Wall art title</a></h4>
                            <h3>$540</h3>
                            <a href="" class="btn btn-primary btn-sm">Buy now</a>
                        </div>
                    </div>
                </div>
                <div class="single_project cat1 cat5">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/gallery1.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info art-info">
                            <h4><a href="#">Wall art title</a></h4>
                            <h3>$540</h3>
                            <a href="" class="btn btn-primary btn-sm">Buy now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <hr>
    <section class="piclicks_about about_v1">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="piclicks_img_box">
                        <video autoplay loop muted preload>
                            <source src="{{ asset('assets/video/video200.mp4') }}">
                        </video>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="piclicks_content_box">
                        <h2>Welcome to PICLICKS</h2>
                        <p>It is a long established fact that a reader will be distracted by the readable content of a page
                            when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal
                            distribution of letters</p>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been
                            the industry's standard dummy text ever since the 1500s</p>
                        <a href="#" class="piclicks_btn">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-piclicks">
        <!--div class="container-fluid banner-top"></div-->
        <video autoplay loop muted preload>
            <source src="{{ asset('assets/video/section_bg.mp4') }}">
            </source>
        </video>
        <div class="video_why">
            <div class="container text-center">

                <div class="col-md-8 mx-auto text-center heading">
                    <h2 class="">Why Choose US</h2>
                    <p class="text-center mb-4">Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown
                        printer took a galley of type and scrambled it to make a type specimen book.
                    </p>
                </div>

                <div class="row text-left">

                    <div class="col-lg-6 col-md-6 px-3 mb-4">
                        <div class="card text-left">
                            <img class="text-left" src="{{ asset('assets/images/award.png') }}" alt="">
                            <h4>Award-winning design</h4>
                            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 px-3 mb-4">
                        <div class="card text-left">
                            <img class="text-left" src="{{ asset('assets/images/collage-image.png') }}" alt="">
                            <h4>Custom-made in any desired size</h4>
                            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 px-3 mb-4">
                        <div class="card text-left">
                            <img class="text-left" src="{{ asset('assets/images/guarantee.png') }}" alt="">
                            <h4>100% satisfaction guaranteed</h4>
                            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 px-3 mb-4">
                        <div class="card text-left">
                            <img class="text-center" src="{{ asset('assets/images/rating.png') }}" alt="">
                            <h4>Perfect rating - 10,000 reviews</h4>
                            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                        </div>
                    </div>



                </div>

            </div>
        </div>
        </div>


        <!--div class="container-fluid banner-bottom"></div-->
    </section>



    <section class="piclicks_community community_v1">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section_title text-center">
                        <h2>Our Community</h2>
                        <p>When unknow printer took a gallery of type and scramblted it to make a type specimen book</p>
                    </div>
                </div>
            </div>
            <div class="community_slider_content">
                <div class="single_project cat1 cat5">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/comu1.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info">
                            <h4><a href="#">Photo collage</a></h4>
                            <p>Black 20"x20"</p>
                        </div>
                    </div>
                </div>
                <div class="single_project cat2 cat4">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/comu2.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info">
                            <h4><a href="#">Photo collage</a></h4>
                            <p>Black 20"x20"</p>
                        </div>
                    </div>
                </div>
                <div class="single_project cat3 cat1">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <img src="{{ asset('assets/images/comu3.png') }}" style="width:100%;">
                        </div>
                        <div class="piclicks_info">
                            <h4><a href="#">Photo collage</a></h4>
                            <p>Black 20"x20"</p>
                        </div>
                    </div>
                </div>
                <div class="single_project cat1 cat5">
                    <div class="grid_item">
                        <div class="piclicks_img">
                            <video mute autoplay preload loop>
                                <source src="{{ asset('assets/video/community1.mp4') }}" alt>
                                </source>
                        </div>
                        <div class="piclicks_info">
                            <h4><a href="#">Design & Develop</a></h4>
                            <p>Design</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!--section class="piclicks_achivement">
                            <div class="container">
                            <div class="row align-items-center">
                            <div class="col-lg-6">
                            <div class="section_title">
                            <h2>We Offering you</h2>
                            </div>
                            <div class="piclicks_content_box">
                            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p>
                            <p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. </p>
                            </div>
                            </div>
                            <div class="col-lg-6">
                            <div class="achivment_wrapper">
                            <div class="single_achivment">
                            <div class="icon">
                            <img class="img-fluid" src="images/collage1.png" alt="" width="100">
                            </div>
                            <p>Create Collage</p>
                            </div>
                            <div class="single_achivment">
                            <div class="icon">
                            <img src="images/gift-card.png" class="img-fluid" alt="" width="80">
                            </div>
                            <p>Custom Gift Cards</p>
                            </div>
                            </div>
                            </div>
                            </div>
                            </div>
                            </section-->


    <section class="piclicks_faqs">
        <div class="container">
            <div class="faq-title">
                <h2>FAQs</h2>
            </div>
            <div class="collapse-faq">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                How big are the tiles?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                Sizes range from 8''x8'' to 27''x36'', plus a unique 22''x44'' option. Available in various
                                materials and frame colors, including frameless and canvas options
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                How long does shipping take?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                Usually about a week. Expedited options are available in some countries. We will update you
                                with a tracking number after your purchase
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Is it easy to move the tiles around?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                Super easy! They’re designed to be repositioned multiple times without any damage
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                And they won't hurt my walls?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                Nope, no damage
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Do you ship internationally?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                Yes, to most countries in the world!
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                Dummy Question
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p class="mb-3">Lorem Ipsum is simply dummy text of the printing and typesetting
                                    industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                                    when an unknown printer took a galley of type and scrambled it to make a type specimen
                                    book.</p>
                                <a href="" class="btn btn-primary">Go to Shop</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection


@push('js')


@endpush
