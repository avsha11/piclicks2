@extends('front.layout.front-layout')
@push('title', 'Wall arts')
@section('content')
    <style>
        .transparent_header {
            border-bottom: 1px solid #e1e1e1;
        }

        .tool-page-menu {
            display: block;
        }

        .product-image {
            position: relative;
        }

        .btn-heart {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        /* checkbox css */
        input[type="checkbox"] {
            display: none;
        }

        .collection-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .img-container {
            position: relative;
            width: 45px;
            height: 45px;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;

        }

        .checkmark {
            position: absolute;
            width: 18px;
            height: 18px;
            background-color: #0d6efd;
            color: white;
            font-size: 12px;
            text-align: center;
            line-height: 18px;
            border-radius: 50%;
            top: -4px;
            right: -4px;
            display: none;
        }

        input[type="checkbox"]:checked+label .img-container img {
            /* border-color: #0d6efd; */
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #60a5fa, 0 0 0 0 transparent;
        }

        input[type="checkbox"]:checked+label .checkmark {
            display: block;
        }



        .badge-toggle input[type="checkbox"] {
            display: none;
        }

        /* Style for badges */
        .badge-toggle label {
            cursor: pointer;
            margin: 2px;
        }

        /* Hover effect */
        .badge-toggle label:hover {
            background-color: #265e91 !important;
            /* Bootstrap's secondary */
            color: #fff;
        }

        /* Active (checked) effect */
        .badge-toggle input[type="checkbox"]:checked+label {
            background-color: #265e91 !important;
            /* Highlight color */
            color: #fff;
        }

        .custom-sort-dropdown .sort-btn {
            padding: 8px 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: transparent;
            color: #333;
            transition: 0.2s;
        }

        .custom-sort-dropdown .sort-btn:hover,
        .custom-sort-dropdown .sort-btn:focus,
        .custom-sort-dropdown .sort-btn:active,
        .custom-sort-dropdown .sort-btn.show {
            background-color: #265e91;
            /* Bootstrap red */
            color: #fff;
            border-color: #265e91;
        }

        /* checkbox css end */
    </style>
    <section class="create_galley">
        <div class="container shop-page">
            <div class="row mb-3">
                <div class="col-sm-3">
                    <h2>Wall arts</h2>
                </div>
                <div class="col-sm-9">
                    <div class="d-flex gap-3">
                        <input class="form-control" placeholder="Search">


                        <div class="custom-sort-dropdown dropdown">
                            <button class="sort-btn dropdown-toggle" type="button" id="sortDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Sort by
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                <li><a class="dropdown-item" href="#">Price: Low to High</a></li>
                                <li><a class="dropdown-item" href="#">Price: High to Low</a></li>
                                <li><a class="dropdown-item" href="#">Newest First</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <div class="main-filterbox sticky-top">
                        <!-- <div class="accordion" id="accordionPanelsStayOpenExample">
              <div class="accordion-item">
               <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                 Featured
                </button>
               </h2>
               <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                 <div class="filter-box">
                  <div class="filter-check">
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="New">
                    <label class="form-check-label" for="New">
                     New
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Bestsellers" checked>
                    <label class="form-check-label" for="Bestsellers">
                     Bestsellers
                    </label>
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
              <div class="accordion-item">
               <h2 class="accordion-header" id="panelsStayOpen-headingFive">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFive" aria-expanded="true" aria-controls="panelsStayOpen-collapseFive">
                 Themes
                </button>
               </h2>
               <div id="panelsStayOpen-collapseFive" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingFive">
                <div class="accordion-body">
                 <div class="filter-box">
                  <div class="filter-check">
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Animals">
                    <label class="form-check-label" for="Animals">
                     Animals & Insects
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Architecture" checked>
                    <label class="form-check-label" for="Architecture">
                     Architecture
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Baby" checked>
                    <label class="form-check-label" for="Baby">
                     Baby
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Birds" checked>
                    <label class="form-check-label" for="Birds">
                     Birds
                    </label>
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
              <div class="accordion-item">
               <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                 Shape
                </button>
               </h2>
               <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
                <div class="accordion-body">
                 <div class="filter-box">
                  <div class="filter-check">
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Landscape">
                    <label class="form-check-label" for="Landscape">
                     Landscape
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Square" checked>
                    <label class="form-check-label" for="Square">
                     Square
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Portrait" checked>
                    <label class="form-check-label" for="Portrait">
                     Portrait
                    </label>
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
              <div class="accordion-item">
               <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                 Color
                </button>
               </h2>
               <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
                <div class="accordion-body">
                 <div class="filter-box">
                  <div class="filter-check">
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Blackw">
                    <label class="form-check-label" for="Blackw">
                     Black and white
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Colorful" checked>
                    <label class="form-check-label" for="Colorful">
                     Colorful
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Darkshades" checked>
                    <label class="form-check-label" for="Darkshades">
                     Dark shades
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Lightshades" checked>
                    <label class="form-check-label" for="Lightshades">
                     Light shades
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Pastelshades" checked>
                    <label class="form-check-label" for="Pastelshades">
                     Pastel colors
                    </label>
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
              <div class="accordion-item">
               <h2 class="accordion-header" id="panelsStayOpen-headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="false" aria-controls="panelsStayOpen-collapseFour">
                 Style
                </button>
               </h2>
               <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingFour">
                <div class="accordion-body">
                 <div class="filter-box">
                  <div class="filter-check">
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Abstract">
                    <label class="form-check-label" for="Abstract">
                     Abstract
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Art2" checked>
                    <label class="form-check-label" for="Art2">
                     Art deco
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Painters" checked>
                    <label class="form-check-label" for="Painters">
                     Artists & Painters
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Classic" checked>
                    <label class="form-check-label" for="Classic">
                     Classic
                    </label>
                   </div>
                   <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="Graphic" checked>
                    <label class="form-check-label" for="Graphic">
                     Graphic Design
                    </label>
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
             </div> -->
                        <div class="work-collection">
                            <h4>Collections</h4>


                            <div class="d-flex flex-column mt-3">

                                <input type="checkbox" id="new">
                                <label for="new" class="collection-label">
                                    <div class="img-container">
                                        <img src="{{ asset('assets/images/tiles (1).jpg') }}" class="">

                                        <div class="checkmark">✓</div>
                                    </div>
                                    <div class="collection-name">New</div>
                                </label>

                                <input type="checkbox" id="artists">
                                <label for="artists" class="collection-label">
                                    <div class="img-container">
                                        <img src="{{ asset('assets/images/tiles (2).jpg') }}" class="">
                                        <div class="checkmark">✓</div>
                                    </div>
                                    <div class="collection-name">Artists 2.0</div>
                                </label>

                                <input type="checkbox" id="bestsellers">
                                <label for="bestsellers" class="collection-label">
                                    <div class="img-container">
                                        <img src="{{ asset('assets/images/tiles (3).jpg') }}" class="">
                                        <div class="checkmark">✓</div>
                                    </div>
                                    <div class="collection-name">Bestsellers</div>
                                </label>

                            </div>
                        </div>



                        <div class="work-tags mt-4">
                            <h4>Tags</h4>

                            <div class="mt-3 d-flex flex-wrap">
                                <!-- Repeat structure for each badge -->
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag1" />
                                    <label class="badge bg-dark fw-normal" for="tag1">FallVibes</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag2" />
                                    <label class="badge bg-dark fw-normal" for="tag2">AutumnArt</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag3" />
                                    <label class="badge bg-dark fw-normal" for="tag3">SeasonalInspiration</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag4" />
                                    <label class="badge bg-dark fw-normal" for="tag4">CozyAesthetic</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag5" />
                                    <label class="badge bg-dark fw-normal" for="tag5">FallColors</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag6" />
                                    <label class="badge bg-dark fw-normal" for="tag6">GoldenHour</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag7" />
                                    <label class="badge bg-dark fw-normal" for="tag7">VintageCollage</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag8" />
                                    <label class="badge bg-dark fw-normal" for="tag8">Surrealism</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag9" />
                                    <label class="badge bg-dark fw-normal" for="tag9">DreamyArt</label>
                                </div>
                                <div class="badge-toggle">
                                    <input type="checkbox" id="tag10" />
                                    <label class="badge bg-dark fw-normal" for="tag10">NostalgicVibes</label>
                                </div>

                                <!-- ... repeat for others ... -->
                            </div>














                        </div>


                    </div>
                </div>
                <div class="col-sm-9 mt-4 mt-md-0">
                    <div class="row">
                        @if ($designCollages->isNotEmpty())
                            @foreach ($designCollages as $value)
                                <div class="col-sm-4 col-xs-12 col-6">
                                    <div class="product-main">
                                        <div class="product-image">
                                            <img src="{{ asset('storage/'.$value->image_path) }}" class="img-responsive">
                                            <div class="product-sale">
                                                <button class="btn btn-heart"><i
                                                        class="fa-solid fa-heart"></i></button>
                                                {{-- <button class="btn btn-heart active"><i
                                                        class="fa-solid fa-heart"></i></button> --}}
                                            </div>
                                        </div>
                                        <div class="product-detail">
                                            <a href="artwork-detail.php">
                                                <div class="detial_small">
                                                    <h6>I fell in love</h6>
                                                    <p><span>Frank Moth</span></p>
                                                </div>
                                                <div class=""></div>
                                            </a>
                                            <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (2).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>Flower - lilac</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (3).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (4).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (5).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (6).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (7).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (8).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/tiles (9).jpg') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/art4.png') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/art5.png') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12 col-6">
                            <div class="product-main">
                                <div class="product-image">
                                    <img src="{{ asset('assets/images/art1.png') }}" class="img-responsive">
                                    <div class="product-sale">
                                        <button class="btn btn-heart"><i class="fa-solid fa-heart"></i></button>
                                    </div>
                                </div>
                                <div class="product-detail">
                                    <a href="artwork-detail.php">
                                        <div class="detial_small">
                                            <h6>I fell in love with Fall because of you</h6>
                                            <p><span>Lena Addink</span></p>
                                        </div>
                                        <div class=""></div>
                                    </a>
                                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
