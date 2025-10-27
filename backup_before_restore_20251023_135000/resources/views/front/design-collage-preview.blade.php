@extends('front.layout.front-layout')
@push('title', 'Collage Preview')
@section('content')
    <style>
        #toolzoom {
            cursor: grab;
            /* Indicate draggable area */
        }

        #toolzoom:active {
            cursor: grabbing;
            /* Change cursor on drag */
        }

        .disabled {
            opacity: 0.1;
            cursor: not-allowed;
        }

        #Text_popup .dropdown-toggle::after {
            position: absolute;
            right: 14px;
            top: 22px;
        }

        #Text_popup .dropdown .btn-secondary:focus,
        #Text_popup .dropdown .btn-secondary:active:focus {
            box-shadow: none !important;
            outline: none;
        }

        .success-black-outlined {
            border: 3px solid #000;
            border-radius: 8px;
        }

        .success-white-outlined {
            border: 3px solid #fff;
            border-radius: 8px;
        }

        @media (max-width: 767px) {
            .success-black-outlined {
                border: 2px solid #000 !important;
                border-radius: 7px;
            }
        }

        /* Clip-path support for stretched images */
        .image-div {
            position: relative;
            overflow: visible;
        }
        
        /* Background color that shows through gaps in clipped tiles 
           Note: This background is for UI/display purposes only. */
        #preview-grid {
            background: #f1f1f1;
            /* Remove gaps between tiles on preview page to show continuous stretched images */
            gap: 0 !important;
        }
        
        /* Ensure text overlay is clipped by the grid container on preview page */
        .tool-inner {
            overflow: hidden !important;
            position: relative;
        }

        .text-content {
            word-spacing: -3px;
        }

        .carousel-caption {
            /* top: 10%;
                                                                                                                            position: absolute;
                                                                                                                            right: 15%;
                                                                                                                            left: 15%; */
            /* bottom: 1.25rem; */
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
            color: #fff;
            text-align: center;
        }

        /* .carousel-caption img {
                                                                                                                            width:300px;
                                                                                                                            height:150px;
                                                                                                                        } */
        /* .carousel-item > img{
                                                                                                                            height:900px ;
                                                                                                                            object-fit: cover;} */
        .Reviews-module--reviews--ae1f3 .Reviews-module--link--62198 {
            align-items: center;
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            flex-wrap: wrap;
            font-size: 1rem;
            gap: .75rem;
            line-height: 1.5rem;
            padding: 0;
            row-gap: .5rem;
            text-decoration: none;
        }

        .Reviews-module--reviews--ae1f3 .Reviews-module--stars--2de26 img {
            height: 18px;
            margin-bottom: 2px;
            max-height: unset;
            width: auto;
        }

        .preview_collage_list ul {
            padding-left: 16px;
        }

        .preview_collage_list ul li {
            list-style: circle;
            font-size: 16px;
        }

        /* From Uiverse.io by andrew-demchenk0 */
        .rating:not(:checked)>input {
            position: absolute;
            appearance: none;
        }

        .rating:not(:checked)>label {
            float: right;
            cursor: pointer;
            font-size: 30px;
            color: #666;
        }

        .rating:not(:checked)>label:before {
            content: '★';
        }

        .rating>input:checked+label:hover,
        .rating>input:checked+label:hover~label,
        .rating>input:checked~label:hover,
        .rating>input:checked~label:hover~label,
        .rating>label:hover~input:checked~label {
            color: #e58e09;
        }

        .rating:not(:checked)>label:hover,
        .rating:not(:checked)>label:hover~label {
            color: #ff9e0b;
        }

        .rating>input:checked~label {
            color: #ffa723;
        }
    </style>
    <section>
        <div class="container-fluid">
            <div class="row">
                @php
                    $tile_width = 91;
                    $tile_height = 80;
                    // These come from your dynamic data
                    $actual_cols = $designCollagePreviewData['grid_columns'] ?? 5;
                    $actual_rows = $designCollagePreviewData['grid_rows'] ?? 7;
                    // Actual image size calculation
                    $img_width = $tile_width * $actual_cols;
                    $img_height = $tile_height * $actual_rows;
                @endphp
                <div class="col-lg-7 col-xl-8">
                    <div id="collage-container" data-raw-width="{{ $img_width }}" data-raw-height="{{ $img_height }}"
                        data-cols="{{ $actual_cols }}" data-rows="{{ $actual_rows }}"></div>
                    <div id="carouselExampleCaptions" class="carousel slide" data-bs-interval="false">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="{{ $mergedImage1 ?? '' }}?v={{ date('YmdHis') }}" class="d-block w-100"
                                     alt="Preview" loading="lazy">
                                {{-- <img src="{{ asset('/assets/images/previewbg2.png') }}" class="d-block w-100"
                                    alt="...">
                                <div class="carousel-caption">
                                    <div class="collage-wrapper">
                                        <img src="{{ asset('storage/' . $designCollagePreviewData['image_path']) }}"
                                            alt="" class="collage-image">
                                    </div>
                                </div> --}}
                            </div>
                            <div class="carousel-item">
                                <img src="{{ $mergedImage2 ?? '' }}?v={{ date('YmdHis') }}" class="d-block w-100"
                                     alt="Preview" loading="lazy">
                                {{-- <img src="{{ asset('/assets/images/previewbg1.png') }}" class="d-block w-100"
                                    alt="...">
                                <div class="carousel-caption">
                                    <div class="collage-wrapper">
                                        <img src="{{ asset('storage/' . $designCollagePreviewData['image_path']) }}"
                                            alt="" class="collage-image">
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <!-- Right Side: Product Details -->
                <div class="col-lg-5 col-xl-4">
                    <div class="d-flex flex-column gap-3 px-0 p-lg-5 py-3">
                        <h3 class="fw-bold">Summary</h3>
                        <div class="mb-2 layout-horiz-size" style="display: none;">
                            <span>{{ $designCollagePreviewData['height'] ?? '' }}</span> cm
                        </div>
                        <div class="mb-2 layout-vert-size" style="display: none;"><span>
                                {{ $designCollagePreviewData['width'] ?? '' }}</span> cm</div>
                        <h4 class="">{{ $designCollagePreviewData['total_tiles'] ?? '' }} tiles
                            ({{ $designCollagePreviewData['height'] ?? '' }} X
                            {{ $designCollagePreviewData['width'] ?? '' }} cm)</h4>
                        <h5 class="product-quality">
                            <span
                                class="text-success">{{ $designCollagePreviewData['price_id'] == 1 || $designCollagePreviewData['price_id'] == 2 ? 'Including Wall fasteners' : 'Tiles only, no fasteners' }}</span>
                            <button type="button"
                                class="ms-3 {{ $designCollagePreviewData['price_id'] == 1 || $designCollagePreviewData['price_id'] == 2 ? 'btn-refresh' : 'btn-refresh-green' }}"
                                id="refresh_btn"
                                onclick="return refreshProductFunction({{ $designCollagePreviewData['id'] }},{{ $designCollagePreviewData['price_id'] }},'preview')">
                                <!-- <i
                                                                                                class="fa-solid fa-arrows-rotate "></i> -->
                                <img src=" {{ asset('assets/images/refreshicon.png') }}" alt="" class="refreshimg">
                            </button>
                        </h5>
                        <h6>{{ env('DEFAULT_CURRENCY') }}
                            {{ getUserItemItems($designCollagePreviewData['total_tiles'] ?? 0, $designCollagePreviewData['price_id'])->cost }}
                        </h6>
                        <input type="hidden" name="priceId" value="{{ $designCollagePreviewData['price_id'] }}"
                            id="priceId" />
                        <div class="Reviews-module--reviews--ae1f3">
                            <a href="javascript:;" class="Reviews-module--link--62198">
                                <span class="Reviews-module--score--6e0f7">0 / 5.0</span>
                                <div class="rating">
                                    <input value="5" name="rate" id="star5" type="radio">
                                    <label title="text" for="star5"></label>
                                    <input value="4" name="rate" id="star4" type="radio">
                                    <label title="text" for="star4"></label>
                                    <input value="3" name="rate" id="star3" type="radio">
                                    <label title="text" for="star3"></label>
                                    <input value="2" name="rate" id="star2" type="radio">
                                    <label title="text" for="star2"></label>
                                    <input value="1" name="rate" id="star1" type="radio">
                                    <label title="text" for="star1"></label>
                                </div>
                                <span class="Reviews-module--amount--49e00">0 reviews</span>
                            </a>
                        </div>
                        <div class="preview_collage_list">
                            <ul>
                                <li>
                                    <p>High-Quality Wall Decor</p>
                                </li>
                                <li>
                                    <p>A Design masterpiece</p>
                                </li>
                                <li>
                                    <p>Custom-Made</p>
                                </li>
                                <li>
                                    <p>All (Hanging) Tools Included</p>
                                </li>
                                <li>
                                    <p>Delivered within 3-5 business days</p>
                                </li>
                                <li>
                                    <p>Satisfaction Guaranteed</p>
                                </li>
                            </ul>
                        </div>
                        <p class="text-muted">
                            This is a short description of the product. It provides key details about the design,
                            material, and special features.
                        </p>
                        <!-- Buttons -->
                        <div class="d-flex flex-column flex-md-row gap-3">
                            @if ($designCollagePreviewData['user_id'] === 1 && $designCollagePreviewData['user_type'] === 'admin')
                                <a href="javascript:;" class="btn btn-dark editGalleryCollage"
                                    data-unique-id="{{ $designCollagePreviewData['unique_id'] }}">
                                    <i class="fa-solid fa-arrow-left"></i> Edit the collage
                                </a>
                                <button class="btn btn-success add-to-cart"
                                    data-id="{{ $designCollagePreviewData['unique_id'] ?? '' }}" data-name="artgallery"
                                    data-price="0">
                                    <i class="fa-regular fa-circle-check"></i>Checkout
                                </button>
                            @else
                                <a href="{{ route('front.design-collage', ['unique_id' => $designCollagePreviewData['unique_id'] ?? '']) }}"
                                    class="btn btn-dark">
                                    <i class="fa-solid fa-arrow-left"></i> Go Back
                                </a>
                                <button class="btn btn-success add-to-cart"
                                    data-id="{{ $designCollagePreviewData['unique_id'] ?? '' }}"
                                    data-name="{{ $designCollagePreviewData['artgallery_unique_id'] !== null ? 'artgallery' : 'collage' }}"
                                    data-price="0">
                                    <i class="fa-regular fa-circle-check"></i>Checkout
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
@push('js')
    <link rel="stylesheet" href="{{ asset('assets/css/tool.css') }}" />
    {{-- <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
    <script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.css"> --}}
    {{-- <script src="{{ asset('assets/js/tool.js') }}"></script> --}}
    {{-- ?v={{ date('YmdHis') }} --}}
    {{-- timmywil/panzoom --}}
    {{-- <script src="https://unpkg.com/@panzoom/panzoom@4.4.0/dist/panzoom.min.js"></script> --}}
    {{-- anvaka/panzoom --}}
    {{-- <script src='https://unpkg.com/panzoom@9.4.0/dist/panzoom.min.js'></script> --}}
    {{-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="{{ asset('assets/js/jquery.ui.touch-punch.js') }}"></script> --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/zoomist@2/zoomist.css" />
    <script src="https://cdn.jsdelivr.net/npm/zoomist@2/zoomist.umd.js"></script> --}}
    {{-- <script src="https://raw.githack.com/SortableJS/Sortable/master/Sortable.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script> --}}
    @if (session('gallery_edit_error'))
        <script>
            toastr.error("{{ session('gallery_edit_error') }}");
        </script>
    @endif
    <script>
        console.log('=== PREVIEW PAGE LOADED ===');
        console.log('jQuery available:', typeof $ !== 'undefined');
        console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
        
        function setCollageScale() {
            const container = document.getElementById("collage-container");
            // const img = document.querySelector(".collage-image");
            const image = $(".collage-image");
            const rawWidth = parseFloat(container.dataset.rawWidth);
            const rawHeight = parseFloat(container.dataset.rawHeight);
            const cols = parseFloat(container.dataset.cols);
            const rows = parseFloat(container.dataset.rows);
            const totalTiles = cols * rows;
            console.log(`Cols: ${cols}, Rows: ${rows}, Total Tiles: ${totalTiles}`);
            let scaleFactor = 1.00;
            let img_class = '';
            let varr = 0;
            if (cols > rows) {
                varr = cols;
                img_class = 'max-width-img'
            } else {
                varr = rows;
                img_class = 'max-height-img'
            }
            const width = window.innerWidth;
            if (width <= 400) {
                if (varr >= 1 && varr <= 5) scaleFactor = 0.36;
                else if (varr >= 6 && varr <= 8) scaleFactor = 0.264;
                else scaleFactor = 0;
            } else if (width <= 575) {
                if (varr >= 1 && varr <= 5) scaleFactor = 0.39;
                else if (varr >= 6 && varr <= 8) scaleFactor = 0.24;
                else scaleFactor = 0;
            } else if (width <= 767) {
                if (varr >= 1 && varr <= 5) scaleFactor = 0.6;
                else if (varr >= 6 && varr <= 8) scaleFactor = 0.528;
                else scaleFactor = 0;
            } else if (width <= 992) {
                if (varr >= 1 && varr <= 5) scaleFactor = 0.72;
                else if (varr >= 6 && varr <= 8) scaleFactor = 0.576;
                else scaleFactor = 0;
            } else if (width <= 1200) {
                if (varr >= 1 && varr <= 5) scaleFactor = 0.78;
                else if (varr >= 6 && varr <= 8) scaleFactor = 0.636;
                else scaleFactor = 0;
            } else if (width <= 1366) {
                if (varr >= 1 && varr <= 5) scaleFactor = 0.7;
                else if (varr >= 6 && varr <= 8) scaleFactor = 0.5;
                else scaleFactor = 0;
            } else {
                if (varr >= 1 && varr <= 5) scaleFactor = 0.73;
                else if (varr >= 6 && varr <= 8) scaleFactor = 0.55;
                else scaleFactor = 0;
            }
            // $(".collage-wrapper").css('height', finalHeight + "px");
            // if(Number.isInteger(scaleFactor)) {
            //     image.css('transform', "scale("+scaleFactor+")");
            // } else {
            //     image.addClass(scaleFactor, "inherit");
            // }
            if (scaleFactor !== 0) {
                image.css('transform', "scale(" + scaleFactor + ")");
            } else {
                image.addClass(img_class);
                // if (img_class === 'max-width-img') {
                //     image.css('max-width', '400px');
                // } else {
                //     image.css('max-height', '400px');
                // }
            }
        }
        // Run once on page load
        // window.addEventListener('DOMContentLoaded', setCollageScale);
        // window.addEventListener('resize', setCollageScale);
        $(".editGalleryCollage").click(function() {
            let unique_id = $(this).data('unique-id');
            $.ajax({
                url: "{{ route('auth.check') }}",
                type: "GET",
                success: function(response) {
                    if (response.authenticated) {
                        const url =
                            "{{ route('front.art-gallery-edit', ['unique_id' => '__id__']) }}"
                            .replace(
                                '__id__', unique_id);
                        window.location.href = url;
                    } else {
                        toastr.error('Kindly, login to continue.', '', {
                            closeButton: true,
                            progressBar: false,
                            timeOut: 2000,
                            extendedTimeOut: 0
                        });
                    }
                },
                error: function(xhr) {
                    toastr.error('Something went wrong.');
                }
            });
        });
    </script>
@endpush
