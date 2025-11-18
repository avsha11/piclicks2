@extends('front.layout.front-layout')
@push('title', 'Design Collage')
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
            opacity: 0.6;
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
            border-radius: 5px;
        }

        .success-white-outlined {
            border: 3px solid #fff;
            border-radius: 5px;
        }

        @media (max-width: 767px) {
            .success-black-outlined {
                border: 2px solid #000 !important;
                border-radius: 5px;
            }
        }



        /* .grid {
            position: relative;
        } */

        .item.muuri-item-dragging {
            z-index: 3;
        }

        .item.muuri-item-releasing {
            z-index: 2;
        }

        .item.muuri-item-hidden {
            z-index: 0;
        }

        .item-content {
            position: relative;
            width: 100%;
            height: 100%;
        }
    </style>
    <section class="section_collage_tool">
        <div class="banter-loader" id="mainLoader">
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
            <div class="banter-loader__box"></div>
        </div>
        <div class="container">
            <div class="grid" id="preview-grid">
                @foreach ($grid_images as $key => $item)
                    <div class="image-div">
                        <div class="item-content">
                            <img src="{{ $item['image'] }}" alt=""
                                class="{{ $item['empty'] === 0 ? 'open-edit-pop' : 'select-image-pop' }} image-item">

                        </div>
                    </div>
                @endforeach
            </div>


            <input type="hidden" id="grid_columns" value="{{ $grid_columns }}">
            <input type="hidden" id="grid_rows" value="{{ $grid_rows }}">

            {{-- <div class="mt-4 row toolzoomrow">
                <input type="file" id="image-input" class="btn btn-primary mx-auto mb-3 d-none" accept="image/*">
                <div class="px-0 m-auto py-3 tool-main-container">
                    <div class="tool-main" id="toolzoom" style="">
                        <div class="mb-2 layout-horiz-size"><span></span> cm</div>
                        <div class="d-flex gap-2">
                            <div class="mb-2 layout-vert-size"><span></span> cm</div>
                            <div class="left">
                                <div class="add-left"><i class="fas fa-plus"></i></div>
                                <div class="minus-left"><i class="fas fa-minus"></i></div>
                            </div>
                            <div class="middle">

                                <div class="middle-top">
                                    <div class="mb-2 add-top"><i class="fas fa-plus"></i></div>
                                    <div class="mb-2 minus-top"><i class="fas fa-minus"></i></div>
                                </div>
                                <div class="tool-inner" id="image" style="padding: 10px;">

                                    <div id="preview-grid" class="sortable">
                                        @foreach ($grid_images as $item)
                                            <div class="image-div">
                                                    <img src="{{ $item['image'] }}" alt=""
                                                        class="image-item-original d-none">
                                                    <img src="{{ $item['image'] }}" alt=""
                                                        class="{{ $item['empty'] === 0 ? 'open-edit-pop' : 'select-image-pop' }} image-item">
                                                    <input type="hidden" class="image-item-zoom" value="0">
                                                    <input type="hidden" class="image-item-rotate" value="1">

                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="middle-bottom">
                                    <div class="mt-2 add-bottom"><i class="fas fa-plus"></i></div>
                                    <div class="mt-2 minus-bottom"><i class="fas fa-minus"></i></div>
                                </div>
                            </div>
                            <div class="right">
                                <div class="add-right"><i class="fas fa-plus"></i></div>
                                <div class="minus-right"><i class="fas fa-minus"></i></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div> --}}
            {{-- <div class="fix-bottom-tool flex-column gap-3 gap-md-0">
                <div class="d-flex justify-content-between align-items-center mb-0 mb-md-3 gap-4 fix-bottom-tool-upper">
                    <div class="box-tool-b m-0">
                        <div class="set-modals" data-bs-toggle="modal" data-bs-target="#Material_popup">
                            <img src="{{ asset('assets/images/svg/material.svg') }}">
                            <p>Frame</p>
                        </div>
                        <div class="set-modals" data-bs-toggle="modal" data-bs-target="#Filter_popup">
                            <img src="{{ asset('assets/images/svg/filter.svg') }}">
                            <p>Filter</p>
                        </div>
                        <div class="set-modals" data-bs-toggle="modal" data-bs-target="#Layout_popup">
                            <img src="{{ asset('assets/images/svg/layout.svg') }}">
                            <p>Layout</p>
                        </div>
                        <div class="set-modals" onclick="return openTextPopup();">
                            <img src="{{ asset('assets/images/svg/text.svg') }}">
                            <p>Text</p>
                        </div>
                        <div class="upload-image disabled">
                            <img src="{{ asset('assets/images/upload-svgrepo-com.svg') }}">
                            <p>Upload</p>
                        </div>
                        <div class="delete-image disabled">
                            <img src="{{ asset('assets/images/svg/delete.svg') }}">
                            <p>Delete</p>
                        </div>
                    </div>
                    <div class="zoom_btn_box d-flex gap-2 align-items-center">
                        <a class="zoombtn grid-zoom-in"><i class="fas fa-search-plus"></i></a>
                        <a class="zoombtn grid-zoom-out"><i class="fas fa-search-minus"></i></a>
                        <a class="zoombtn grid-zoom-init"><i class="fa-solid fa-expand"></i></a>
                    </div>
                </div>
                <div class="d-flex gap-2 gap-md-3">
                    <div class="finish_btn ps-2">
                        <div>
                            <a href="javascript:;" style="color: #fff;" onclick="return refreshPage();">
                                <i class="fa-solid fa-rotate-left"></i>
                                <p>Undo</p>
                            </a>
                        </div>
                    </div>
                    <div class="finish_btn ps-2">
                        <div class="" data-bs-toggle="offcanvas" href="#" role="button">
                            <a href="{{ route('front.upload-photos') }}" style="color: #fff;">
                                <i class="fa-solid fa-arrow-left"></i>
                                <p>Back</p>
                            </a>
                        </div>
                    </div>
                    <div class="finish_btn ps-2">
                        <div class="" data-bs-toggle="offcanvas" href="#add-to-cart" role="button"
                            aria-controls="offcanvasExample">
                            <i class="fa-solid fa-file-pen"></i>
                            <p>Save as draft</p>
                        </div>
                    </div>
                    <div class="finish_btn btn-success">
                        <div class="" data-bs-toggle="offcanvas" href="#add-to-cart" role="button"
                            aria-controls="offcanvasExample">
                            <i class="fa-regular fa-circle-check fa-fw"></i>
                            <p>Finish</p>
                        </div>
                    </div>
                </div>
            </div> --}}




        </div>
    </section>
    <!-- Modal Material-->
    <div class="modal fade bottom-open frame-popup" id="Material_popup" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header popup-head">
                    <h5 class="modal-title" id="exampleModalLabel">Frame</h5>
                    <a href="javascript:;" data-bs-dismiss="modal">Done</a>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-4 col-4">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="options-outlined" id="none-outlined"
                                    autocomplete="off" checked>
                                <label class="add-active" for="none-outlined">
                                    <img src="{{ asset('assets/images/frame-without.png') }}">
                                    <div class="con_framebox">
                                        <h5>No Frame<br>US$0</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-4 col-4">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="options-outlined"
                                    id="success-black-outlined" autocomplete="off">
                                <label class="add-active" for="success-black-outlined">
                                    <img src="{{ asset('assets/images/frame.png') }}">
                                    <div class="con_framebox">
                                        <h5>Classic Black Frame<br>US$149</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-4 col-4">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="options-outlined"
                                    id="success-white-outlined" autocomplete="off">
                                <label class="add-active" for="success-white-outlined">
                                    <img src="{{ asset('assets/images/frame-white.jpg') }}">
                                    <div class="con_framebox">
                                        <h5>Classic White Frame<br>US$149</h5>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Material-->
    <!-- Modal Layout-->
    <div class="modal fade bottom-open grid-popup" id="Layout_popup" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header popup-head">
                    <h5 class="modal-title" id="exampleModalLabel">Layout</h5>
                    <a href="javascript:;" data-bs-dismiss="modal">Done</a>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6 col-6">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="grid"
                                    autocomplete="off" checked>
                                <label class="add-active" for="grid">
                                    <img src="{{ asset('assets/images/layout-grid.png') }}">
                                    <div class="con_framebox">
                                        <h5>Grid</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-6">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="playful"
                                    autocomplete="off" checked>
                                <label class="add-active" for="playful">
                                    <img src="{{ asset('assets/images/layout-play.png') }}">
                                    <div class="con_framebox">
                                        <h5>Playful Grid</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Layout-->
    <!-- Modal Text-->
    <div class="modal fade bottom-open" id="Text_popup" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header popup-head ">
                    <h5 class="modal-title" id="exampleModalLabel">Text Editor</h5>
                    <a href="javascript:;" data-bs-dismiss="modal">Done</a>
                </div>
                <div class="modal-body">
                    <div class="text-input d-flex justify-content-between">
                        <input type="text" id="text-input" placeholder="Enter text here" class="w-100">
                        <button type="button" id="add-text-btn" class="btn btn-primary"
                            style="white-space: nowrap;">Add Text</button>
                    </div>
                    <div class="row text-color-edit my-3">
                        <div class="col d-flex justify-content-start align-items-center gap-2">
                            <label for="color-picker">Text Color:</label>
                            <input type="color" id="color-picker" class="ms-0" value="#000000">
                            <div class="text-end">
                                <button id="clear-color-btn"
                                    class="btn btn-secondary btn-sm p-2 py-1 flex-grow-1">clear</button>
                            </div>
                        </div>
                    </div>

                    <div class="row text-center mt-3">
                        <!-- <div class="col d-flex justify-content-start align-items-center gap-2">
                                    <label for="font-option">Font style:</label>
                                    <select class="ms-0" id="font-option">
                                        <option class="font-stylenone" data-font="Arial, sans-serif">None</option>
                                        <option class="font-style1" data-font="'Brush Script MT', cursive">Handwriting</option>
                                        <option class="font-style2" data-font="Georgia, serif">Classic</option>
                                        <option class="font-style3" data-font="Arial, sans-serif; font-weight: bold">Bold</option>
                                    </select>
                                </div> -->
                        <div class="dropdown">
                            <a class="bg-transparent border-0 btn btn-secondary dropdown-toggle px-0 text-black text-start w-100"
                                href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                for="font-option">
                                Font style: <span id="selected-font">None</span>
                            </a>

                            <ul class="dropdown-menu" id="font-option">
                                <li><a class="dropdown-item font-stylenone" data-font="Arial, sans-serif">None</a></li>
                                <li><a class="dropdown-item font-style1"
                                        data-font="'Brush Script MT', cursive">Handwriting</a></li>
                                <li><a class="dropdown-item font-style2" data-font="Georgia, serif">Classic</a></li>
                                <li><a class="dropdown-item font-style3"
                                        data-font="Arial, sans-serif; font-weight: bold">Bold</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="row text-center mt-3">
                        <div class="col d-flex justify-content-start align-items-center gap-2">
                            <label for="font-option">Font size:</label>
                            <input type="number" id="font-size" class="ms-0" value="20">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Text-->
    <!-- Modal Filter-->
    <div class="modal fade bottom-open " id="Filter_popup" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header popup-head">
                    <h5 class="modal-title" id="exampleModalLabel">Filter</h5>
                    <a href="javascript:;" data-bs-dismiss="modal">Done</a>
                </div>
                <div class="modal-body">
                    <div class="set-pics text-center">
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-original"
                                    autocomplete="off" checked>
                                <label class="add-active" for="filter-original">
                                    <div class="con_framebox filter-style filter-original">
                                        <img src="{{ asset('assets/images/puppy.jpg') }}">
                                        <h5>Original</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-noir"
                                    autocomplete="off">
                                <label class="add-active" for="filter-noir">
                                    <div class="con_framebox filter-style filter-noir">
                                        <img src="{{ asset('assets/images/puppy.jpg') }}">
                                        <h5>Noir</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-nordic"
                                    autocomplete="off">
                                <label class="add-active" for="filter-nordic">
                                    <div class="con_framebox filter-style filter-nordic">
                                        <img src="{{ asset('assets/images/puppy.jpg') }}">
                                        <h5>Nordic</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-Scandi"
                                    autocomplete="off">
                                <label class="add-active" for="filter-Scandi">
                                    <div class="con_framebox filter-style">
                                        <img src="{{ asset('assets/images/puppy.jpg') }}">
                                        <h5>Scandi</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-capri"
                                    autocomplete="off">
                                <label class="add-active" for="filter-capri">
                                    <div class="con_framebox filter-style filter-capri">
                                        <img src="{{ asset('assets/images/puppy.jpg') }}">
                                        <h5>Capri</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-belveder"
                                    autocomplete="off">
                                <label class="add-active" for="filter-belveder">
                                    <div class="con_framebox filter-style filter-belveder">
                                        <img src="{{ asset('assets/images/puppy.jpg') }}">
                                        <h5>Belveder</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-stark"
                                    autocomplete="off">
                                <label class="add-active" for="filter-stark">
                                    <div class="con_framebox filter-style filter-stark">
                                        <img src="{{ asset('assets/images/puppy.jpg') }}">
                                        <h5>Stark</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- right edit tool  -->
    <!-- <button class="btn btn-primary" type="button" >Toggle right offcanvas</button>  -->
    <div class="offcanvas offcanvas-end image-edit" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Edit Image</h5>
            <button type="button" class="btn-close" id="closeButton" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-0">
            <!-- HTML Elements for Zoom and Rotate Controls -->
            <div class="d-flex gap-1 mb-3 align-items-center options" id="zoom-in"><i
                    class="fa-solid fa-magnifying-glass-plus pe-2"></i>Zoom in</div>
            <div class="d-flex gap-1 mb-3 align-items-center options" id="zoom-out"><i
                    class="fa-solid fa-magnifying-glass-minus pe-2"></i>Zoom out</div>
            <div class="d-flex gap-1 mb-3 align-items-center options" id="rotate"><i
                    class="fa-solid fa-rotate pe-2"></i>Rotate</div>
            <div class="d-flex gap-1 mb-3 align-items-center options" id="horizontal-flip"><i
                    class="fa-solid fa-arrows-left-right"></i>Flip horizontal</div>
            <div class="d-flex gap-1 mb-3 align-items-center options" id="vertical-flip"><i
                    class="fa-solid fa-arrows-up-down"></i></i>Flip vertical</div>
            {{-- <div class="d-flex gap-1 mb-3 align-items-center options" id="open-crop-modal"><i
                    class="fa-solid fa-crop pe-2"></i>Crop</div> --}}
        </div>
        <div class="d-flex justify-content-end m-2 m-md-4 offcanvas-footer gap-3">
            <button type="button" class="btn btn-danger delete_btn d-none" id="">Delete</button>
            <button type="button" class="btn btn-primary save_btn" id="">Save</button>
        </div>
    </div>
    <!-- Button trigger modal -->
    <!-- Modal -->
    <div class="modal fade" id="singleImageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="image-container">
                        <img src="{{ asset('assets/images/grey-back.png') }}" class="pop-get-image" id="image_edit"
                            style="">
                        <div class="banter-loader" id="imgLoader">
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                            <div class="banter-loader__box"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script></script>
    <!-- Modal Structure -->
    <div id="crop-modal" class="modal p-3">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center p-2">
                <h3>Crop Image</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="crop-container ">
                <img id="crop-image" src="" alt="Crop Image">
            </div>
            <button id="crop-button" class="btn btn-primary mx-auto my-3">Crop</button>
        </div>
    </div>
@endsection
@push('js')
    <link rel="stylesheet" href="{{ asset('assets/css/tool_2.css') }}" />
    {{-- <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
    <script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.css">
    <script src="{{ asset('assets/js/tool_2.js') }}"></script>
    {{-- ?v={{ date('YmdHis') }} --}}

    {{-- timmywil/panzoom --}}
    {{-- <script src="https://unpkg.com/@panzoom/panzoom@4.4.0/dist/panzoom.min.js"></script> --}}

    {{-- anvaka/panzoom --}}
    <script src='https://unpkg.com/panzoom@9.4.0/dist/panzoom.min.js'></script>

    {{-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="{{ asset('assets/js/jquery.ui.touch-punch.js') }}"></script> --}}

    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/zoomist@2/zoomist.css" />
    <script src="https://cdn.jsdelivr.net/npm/zoomist@2/zoomist.umd.js"></script> --}}

    <script src="https://raw.githack.com/SortableJS/Sortable/master/Sortable.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/web-animations/2.3.2/web-animations.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/muuri@0.9.5/dist/muuri.min.js"></script>


    <script>
        let greyImage = "{{ asset('assets/images/grey-back.png') }}";

        let currentFrame = "";
        let currentFilter = "";
        let currentLayout = "";

        function refreshPage() {
            Swal.fire({
                title: "Are you sure ?",
                icon: "info",
                html: `You want to set the page to original state ?`,
                showCloseButton: true,
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: `Yes`,
                cancelButtonText: `No`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        }
    </script>


@endpush
