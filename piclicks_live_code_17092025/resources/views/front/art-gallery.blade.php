@extends('front.layout.front-layout')
@push('title', 'Art Gallery')
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
        .collection-label .img-container button {
    height: 45px;
    width: 45px;
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

        .btn-heart2 {
            position: absolute;
            /* top: 10px;
                right: 10px; */
            background: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            padding: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff0000;
            width: 100%;
            height: 100%;
            background: transparent;
        }


        
@media (max-width: 992px) {

  .img-container{
    width: 35px;
    height: 35px;
}

}
    </style>
    <section class="create_galley">
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
        <div class="container shop-page">
            <div class="row mb-3">
                <div class="col-sm-3">
                    <h2>Art Gallery</h2>
                </div>
                <div class="col-sm-9">
                    <div class="d-flex gap-3">
                        <input class="form-control" placeholder="Search" id="searchInput">
                        <button class="search-btn btn" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>

                        <div class="custom-sort-dropdown dropdown">
                            <button class="sort-btn dropdown-toggle" type="button" id="sortDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Sort by
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                <li><a class="dropdown-item sort-option" data-sort="price_asc" href="javascript:;">Price:
                                        Low to High</a></li>
                                <li><a class="dropdown-item sort-option" data-sort="price_desc" href="javascript:;">Price:
                                        High to Low</a></li>
                                <li><a class="dropdown-item sort-option" data-sort="newest" href="javascript:;">Newest
                                        First</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <div class="main-filterbox sticky-top">

                        <div class="work-collection">
                            <h4>Collections</h4>


                            <div class="d-flex flex-column mt-3">

                                <input type="checkbox" id="coll-0" name="collections" class="collection-filter"
                                    value="0">
                                <label for="coll-0" class="collection-label">
                                    <div class="img-container">
                                        <button class="btn btn-heart active" style="top: 0;right: 0;"><i class="fa-solid fa-heart"></i></button>

                                        <div class="checkmark">✓</div>
                                    </div>
                                    <div class="collection-name">Loved it</div>
                                </label>
                                @foreach ($collections as $value)
                                    <input type="checkbox" id="coll-{{ $value->id }}" name="collections"
                                        class="collection-filter" value="{{ $value->id }}">
                                    <label for="coll-{{ $value->id }}" class="collection-label">
                                        <div class="img-container">
                                            <img src="{{ asset('storage/' . $value->logo) }}" class="">

                                            <div class="checkmark">✓</div>
                                        </div>
                                        <div class="collection-name">{{ $value->name }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </div>



                        <div class="work-tags mt-4">
                            <h4>Tags</h4>

                            <div class="mt-3 d-flex flex-wrap">
                                @foreach ($tags as $value)
                                    <div class="badge-toggle">
                                        <input type="checkbox" id="tag-{{ $value->id }}" name="tags"
                                            class="tag-filter" value="{{ $value->id }}" />
                                        <label class="badge bg-dark fw-normal"
                                            for="tag-{{ $value->id }}">{{ $value->name }}</label>
                                    </div>
                                @endforeach

                            </div>


                        </div>


                    </div>
                </div>
                <div class="col-sm-9 mt-4 mt-md-0">
                    <div class="row" id="collageContainer">
                        @include('front.partials.art-gallery-grid', ['designCollages' => $designCollages])
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

    <script>
        function fetchFilteredCollages() {
            $("#previewLoader").show();
            const selectedCollections = $('.collection-filter:checked').map(function() {
                return this.value;
            }).get();
            const selectedTags = $('.tag-filter:checked').map(function() {
                return this.value;
            }).get();
            const search = $('#searchInput').val();
            const sort = $('.sort-option.active').data('sort') || '';

            $.ajax({
                url: "{{ route('front.get-filtered-artgallery') }}",
                method: "GET",
                data: {
                    collections: selectedCollections,
                    tags: selectedTags,
                    search: search,
                    sort: sort,
                },
                success: function(res) {
                    $("#previewLoader").hide();
                    if (res.status === 1) {
                        $('#collageContainer').html(res.data);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(res) {
                    $("#previewLoader").hide();
                    toastr.error(res.responseJSON.message);
                }
            });
        }

        $(document).ready(function() {
            $('.collection-filter, .tag-filter').on('change', fetchFilteredCollages);
            $('#searchInput').on('keyup', _.debounce(fetchFilteredCollages, 500)); // lodash debounce recommended
            $('.sort-option').on('click', function(e) {
                e.preventDefault();
                $('.sort-option').removeClass('active');
                $(this).addClass('active');
                fetchFilteredCollages();
            });
            $('#searchButton').on('click', function(e) {
                fetchFilteredCollages();
            });
        });

        $(document).on('click', '.toggle-fav', function() {
            const btn = $(this);
            const uniqueId = btn.data('unique-id');

            $.post({
                url: "{{ route('front.toggle-gallery-favorite') }}",
                data: {
                    unique_id: uniqueId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    btn.toggleClass('active');
                }
            });
        });
    </script>
@endpush
