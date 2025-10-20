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

        .success-black-outlined,
        .success-white-outlined {
            border-radius: 8px;
        }

        .success-black-outlined {
            border: 7px solid #000;
        }

        .success-white-outlined {
            border: 7px solid #fff;
        }

        .select-image-pop.image-item {
            border-radius: 7px;
        }

        .grid-framed .select-image-pop.image-item {
            border-radius: 8px;
        }

        .image-item {
            height: 100%;
            width: 100%;
            object-fit: cover;
            object-position: top left;
            border-radius: 7px;
        }

        .success-black-outlined .image-item,
        .success-white-outlined .image-item {
            border-radius: 1px;
        }

        /* @media (max-width: 767px) {
                                .success-black-outlined {
                                    border: 8px solid #000 !important;
                                    border-radius: 8px;
                                }
                                .success-white-outlined {
                                    border: 8px solid #fff;
                                    border-radius: 8px;
                                }
                            } */

        /* Clip-path support for stretched images */
        .image-div {
            position: relative;
            overflow: visible;
        }
        
        /* Background color that shows through gaps in clipped tiles 
           Note: This background is for UI/editor display only. 
           It is temporarily removed during canvas capture to ensure transparent output. */
        #preview-grid {
            background: #f1f1f1;
        }

        .text-content {
            word-spacing: -3px;
        }

        div#previewLoader:before {
            content: "";
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: #fff;
            opacity: 0.7;
            z-index: 99;
        }

        .banter-loader__box {
            z-index: 999;
        }

        .resize-handle {
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: auto;
        }

        .resize-handle i {
            color: #333;
            font-size: 14px;
        }

        .image-div {
            transition: width 0.18s cubic-bezier(0.4, 0, 0.2, 1), height 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .image-item-div {
            background-size: cover;
            background-position: top left;
            height: 100%;
            width: 100%;
            max-width: 100%;
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
        <div class="banter-loader" id="previewLoader">
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
            <div class="row toolzoomrow">
                <input type="file" id="image-input" class="btn btn-primary mx-auto mb-3 d-none" accept="image/*">
                <div class="px-0 m-auto py-3 tool-main-container">
                    <div class="tool-main" id="toolzoom" style="">
                        <input type="hidden" id="grid_columns" value="{{ $master->grid_columns }}">
                        <input type="hidden" id="grid_rows" value="{{ $master->grid_rows }}">
                        <input type="hidden" id="unique_id" value="{{ $unique_id }}">
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

                                    <!-- SVG clipping path for text -->
                                    <svg width="0" height="0" style="position: absolute;">
                                        <defs>
                                            <clipPath id="text-clip-path">
                                                <!-- Clipping path will be generated by JavaScript -->
                                            </clipPath>
                                        </defs>
                                    </svg>

                                    <div id="preview-grid" class="sortable {{ $master->frame != '' ? 'grid-framed' : '' }}"
                                        style="grid-template-columns: repeat({{ $master->grid_columns }}, 1fr);">
                                        @foreach ($images as $item)
                                            @php
                                                $other_settings = json_decode($item['other_settings']);
                                                $image_edited = '';
                                                if ($item['empty'] === 1) {
                                                    $image = asset($item['image']);
                                                } else {
                                                    $image = asset('storage/' . $item['image']);
                                                    if ($item['image_edited'] != '') {
                                                        $image_edited = asset('storage/' . $item['image_edited']);
                                                    }
                                                }
                                                $imageDivDataMargin = '';
                                                if (
                                                    isset($other_settings->imageDivDataMargin) &&
                                                    !empty($other_settings->imageDivDataMargin)
                                                ) {
                                                    $imageDivDataMargin =
                                                        'data-margin=' . $other_settings->imageDivDataMargin . '';
                                                }
                                            @endphp
                                            <div class="image-div {{ $item['empty'] === 0 ? $master->frame . ' has-image' : '' }}"
                                                style="{!! $other_settings->imageDivStyle ?? '' !!}" {{ $imageDivDataMargin }}>
                                                <img src="{{ $image }}" alt=""
                                                    class="image-item-original d-none">
                                                <img src="{{ !empty($image_edited) ? $image_edited : $image }}"
                                                    alt=""
                                                    class="{{ $item['empty'] === 0 ? 'open-edit-pop ' . $master->filter : 'select-image-pop' }} image-item">
                                                {{-- <div class="image-item-div"></div> --}}
                                                <input type="hidden" class="image-item-zoom"
                                                    value="{!! $other_settings->zoom ?? '0' !!}">
                                                <input type="hidden" class="image-item-rotate"
                                                    value="{!! $other_settings->rotate ?? '1' !!}">
                                                <input type="hidden" class="image-item-id" value="{{ $item['id'] }}">


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
            </div>
            <div class="fix-bottom-tool flex-column gap-3 gap-md-0">
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
                        <a class="undobtn" onclick="return refreshPage();">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                        <a class="zoombtn grid-zoom-in"><i class="fas fa-search-plus"></i></a>
                        <a class="zoombtn grid-zoom-init"><i class="fa-solid fa-expand"></i></a>
                        <a class="zoombtn grid-zoom-out"><i class="fas fa-search-minus"></i></a>
                        {{-- <a class="zoombtn size-image disabled"><i class="fa-solid fa-maximize"></i></a> --}}
                        <a class="zoombtn size-image disabled"><img src="{{ asset('assets/images/resize.png') }}"></a>


                    </div>
                </div>
                <div class="d-flex gap-2 gap-md-3">
                    <!-- <div class="finish_btn ps-2">
                                                                                        <div>
                                                                                            <a href="javascript:;" style="color: #fff;" onclick="return refreshPage();">
                                                                                                <i class="fa-solid fa-rotate-left"></i>
                                                                                                <p>Undo</p>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div> -->
                    <div class="finish_btn ps-2">
                        <div class="" data-bs-toggle="offcanvas" href="#" role="button">
                            <a href="{{ ($user_type ?? 'user') == 'user' ? route('front.upload-photos') : route('admin.view-design-collage', ['unique_id' => $unique_id]) }}"
                                style="color: #fff;">
                                <i class="fa-solid fa-arrow-left"></i>
                                <p>Go Back</p>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" id="user_type" value="{{ $user_type ?? 'user' }}">
                    @if (isset($user_type) && $user_type == 'user')
                        <div class="finish_btn ps-2">
                            <div class="saveBtn" onclick="return saveCollage('manual');">
                                <button type="button" class="saveBtn" id="saveBtn">
                                    <i class="fa-solid fa-floppy-disk saveBtn"></i>
                                    {{-- <p>Draft</p> --}}

                                </button>
                            </div>
                        </div>
                        <div class="finish_btn btn-success">
                            <!-- <div class="add-to-cart" data-id="{{ $unique_id }}" data-name="Bhapdoir" data-price="50"
                                                        href="#add-to-cart" role="button" aria-controls="offcanvasExample">
                                                        <i class="fa-regular fa-circle-check fa-fw"></i>
                                                        <p>Preview</p>
                                                    </div> -->

                            <div>
                                <a id="previewBtn" href="javascript:;" style="color: #fff;"
                                    onclick="return saveCollage('preview');">
                                    <i class="fa-regular fa-circle-check fa-fw"></i>
                                    <p>Preview</p>
                                </a>
                            </div>

                        </div>
                    @elseif(isset($user_type) && $user_type == 'admin')
                        <div class="finish_btn ps-2">
                            <div class="saveBtn" onclick="return saveCollage('manual_admin');">
                                <button type="button" class="saveBtn" id="saveBtn">
                                    <i class="fa-solid fa-floppy-disk saveBtn"></i>
                                    <p>Save</p>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>




        </div>
    </section>
    <!-- Modal Material start-->
    <div class="modal fade bottom-open frame-popup" id="Material_popup" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header popup-head">
                    <h5 class="modal-title" id="exampleModalLabel">Frame</h5>
                    <a href="javascript:;" data-bs-dismiss="modal" onclick="return dismissFrameModel();">Done</a>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @include('front.partials.Frame.frame')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Material end-->

    <!-- Modal Layout start-->
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
                                <input type="radio" class="btn-check" name="grid-Layout" id="normal-grid"
                                    autocomplete="off">
                                <label class="add-active" for="normal-grid">
                                    <img src="{{ asset('assets/images/layout-grid.png') }}">
                                    <div class="con_framebox">
                                        <h5>Normal Grid
                                            <span class="d-none loader" style="left: 20%;top: 39%;">
                                        </h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-6">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="grid-Layout" id="dynamic-grid"
                                    autocomplete="off">
                                <label class="add-active" for="dynamic-grid">
                                    <img src="{{ asset('assets/images/layout-play.png') }}">
                                    <div class="con_framebox">
                                        <h5>Dynamic Grid</h5>
                                        <span class="d-none loader" style="left: 69%;top: 38%;">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Layout end-->

    <!-- Modal Text start-->
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
                        <textarea id="text-input" placeholder="Enter text here" class="w-100" rows="1"></textarea>
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
    <!-- Modal Text end -->

    <!-- Modal Filter start -->
    <div class="modal fade bottom-open " id="Filter_popup" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header popup-head">
                    <h5 class="modal-title" id="exampleModalLabel">Filter</h5>
                    <a href="javascript:;" data-bs-dismiss="modal" onclick="return dismissFilterModel();">Done</a>
                </div>
                <div class="modal-body">
                    <div class="set-pics text-center">
                        <div class="filter-opt">
                            <div class="form-check frame-box">
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-original"
                                    autocomplete="off" {{ $master->filter == '' ? 'checked' : '' }}>
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
                                    autocomplete="off" {{ $master->filter == 'filter-noir' ? 'checked' : '' }}>
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
                                    autocomplete="off" {{ $master->filter == 'filter-nordic' ? 'checked' : '' }}>
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
                                <input type="radio" class="btn-check" name="frame-Layout" id="filter-scandi"
                                    autocomplete="off" {{ $master->filter == 'filter-scandi' ? 'checked' : '' }}>
                                <label class="add-active" for="filter-scandi">
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
                                    autocomplete="off" {{ $master->filter == 'filter-capri' ? 'checked' : '' }}>
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
                                    autocomplete="off" {{ $master->filter == 'filter-belveder' ? 'checked' : '' }}>
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
                                    autocomplete="off" {{ $master->filter == 'filter-stark' ? 'checked' : '' }}>
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
    <!-- Modal Filter end -->

    <!-- Modal Size start-->
    <div class="modal fade bottom-open" id="TileSize_popup" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header popup-head ">
                    <h5 class="modal-title" id="exampleModalLabel">Re-Size</h5>
                </div>
                <div class="modal-body">

                    <p>Tile Dimension:</p>
                    <div class="row">
                        <div class="col-6">
                            <label for="font-option">Width:</label>
                            <img src="{{ asset('assets/images/svg/arrow-horizontal.svg') }}"
                                style="width: 39px;top: -2px;position: relative;">
                            <select class="ms-0" id="tile-width">
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="font-option">Height:</label>
                            <img src="{{ asset('assets/images/svg/arrow-vertical.svg') }}"
                                style="height: 39px;position: relative;top: -3px;">
                            <select class="ms-0" id="tile-height">
                            </select>
                        </div>
                    </div>

                    <div class="row text-center mt-3">
                        <div class="col d-flex justify-content-start align-items-center gap-2">
                            <button type="button" id="change-size-btn" class="btn btn-primary btn-sm">Update</button>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Done</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Size end-->

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
    <link rel="stylesheet" href="{{ asset('assets/css/tool.css') }}" />
    {{-- <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.min.css">
    <script src="https://unpkg.com/cropperjs/dist/cropper.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.css">
    <script src="{{ asset('assets/js/tool.js') }}?v={{ date('YmdHis') }}"></script>
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

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> --}}
    <script src="{{ asset('assets/js/html2canvas.js') }}?v={{ date('YmdHis') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script> --}}
    {{-- <script src="https://unpkg.com/html-to-image@1.11.13/dist/html-to-image.js"></script> --}}
    {{-- <script src="https://unpkg.com/rasterizehtml/dist/rasterizeHTML.allinone.js"></script> --}}


    <script>
        let greyImage = "{{ asset('assets/images/grey-back.png') }}";
        let iconStretch = "{{ asset('assets/images/icon_strech.png') }}";
        let saveCollageUrl = "{{ route('front.save-collage') }}";
        let previewCollageUrl = "{{ route('front.preview-design-collage', ['unique_id' => $unique_id]) }}";

        let currentFrame = "{{ $master->frame }}";
        let currentFilter = "{{ $master->filter }}";
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

        $(window).on('beforeunload', function(e) {
            e.preventDefault();
            let message = (loggedIn === 1) ? "Are you sure ?" :
                "Sign-up to save your work, else it will be vanished !";
            e.returnValue = message; // Standard for modern browsers
            return message; // For older browsers
        });

        var storedTextOverlays = @json($master ? json_decode($master->text_editor, true) : []);
    </script>





@endpush
