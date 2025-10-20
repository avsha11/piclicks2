@if ($designCollages->isNotEmpty())
    @foreach ($designCollages as $value)
    
        <div class="col-sm-4 col-xs-12 col-6">
            <div class="product-main">
                <div class="product-image">
                    <a href="{{ route('front.preview-design-collage', ['unique_id' => $value->unique_id]) }}">
                    <img src="{{ asset('storage/' . $value->image_path) }}" class="img-responsive">
                    <div class="product-sale">
                        <button class="btn btn-heart toggle-fav {{ $value->favorites_data->isNotEmpty() ? 'active' : '' }}" 
                            data-unique-id="{{ $value->unique_id }}">
                            <i class="fa-solid fa-heart"></i></button>
                        {{-- <button class="btn btn-heart active"><i
                                                        class="fa-solid fa-heart"></i></button> --}}
                    </div>
                    </a>
                </div>
                <div class="product-detail">
                    <a href="{{ route('front.preview-design-collage', ['unique_id' => $value->unique_id]) }}">
                        <div class="detial_small">
                            <h6>{{ $value->admin_data->title ?? '' }}</h6>
                            <p><span>{{ $value->admin_data->designer_name ?? '' }}</span></p>
                        </div>
                        <div class=""></div>
                    </a>
                    <!--<a href="" class="btn btn-primary">Buy now</a>-->
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="col-sm-12 col-xs-12 col-6">
        <div class="product-main">
            <div class="product-image" style="text-align: center;">
                No arts found in the gallery
            </div>
        </div>
    </div>
@endif
