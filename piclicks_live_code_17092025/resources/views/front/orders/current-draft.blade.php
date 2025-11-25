@extends('front.layout.front-layout')

{{-- @push('title', 'My Draft(s) ') --}}

@section('content')

 <style>

	.transparent_header {

		border-bottom: 1px solid #e1e1e1;

	}



	.product-main .slick-prev {

		left: 10px;

		z-index: 99

	}



	.product-main .slick-next {

		right: 10px;

		z-index: 99

	}



	body {

		background: #121212 !important;

	}



	.brand_logo img {

		-webkit-filter: invert(100%) !important;

		filter: invert(100%) !important;

	}



	.mobile_header .menu-icon {

		color: #fff !important;

	}



	.transparent_header {

		border-color: #3c3c3c !important;

	}



	.page-title {

		background: #ffffff00 !important;

	}



	h1,

	h2,

	h3,

	h4,

	h5,

	h6 {

		color: #fff !important;

	}



	.left-side-menu {

		background: #000 !important;

	}



	.left-side-menu p a,

	table a {

		color: #fff !important;

	}



	td,

	th {

		color: #fff !important;

	}



	.left-side-menu p a:hover {

		background: #2c2c2c;

	}



	.col-sm-9 {

		background: #000;

		padding: 20px;

		border-radius: 10px;



	}



	.table>:not(caption)>*>* {

		border-color: #3c3c3c;

	}



	.transparent_header {

		background: #000 !important;

	}



	

</style>

<section class="create_galley cart-detail">

	<div class="container shop-page">

		<div class="page-title">

			<h2>{{ $title }}(s)</h2>

		</div>

		<div class="row mx-0 mx-md-auto">

			@include('front.partials.sidebar', ['title' => 'Card Title'])

			<div class="col-lg-9 col-md-8">

				<div class="page-title mb-1">

					<h4>{{ $title}}(s)</h4>

				</div>

				<div class="">

					<div class="table-responsive">

						<table class="table vertical-align-middle">

							<tr class="bg-primary text-light">

								<th>ID</th>

								<th>Image</th>

                                <th>Image Size</th>

								<th>Total Tiles</th>

								<th>Created date</th>

								<th>Action</th>

							</tr>

							@if(!$orders->isEmpty())
								@php $count = 1 @endphp
                                @foreach($orders as $order)
									<tr>

										<td>#{{ $count++}}</td>

										<td>

											<div class="post_wrap d-flex align-items-center">

												<div class="post_img me-2">
													@php
														// Use PreviewRenderer image (same as Preview page) instead of html2canvas image
														$uniqueId = $order['unique_id'] ?? null;
														$fallbackPath = $order['image_path'] ?? null;
														$thumbnailUrl = getCollagePreviewImagePath($uniqueId, $fallbackPath);
													@endphp
													<img src="{{ $thumbnailUrl }}" class="img-fluid" alt="" style="width:50px; height:50px;">
													<!--@if(isset($order['collage_images']) && count($order['collage_images']) > 0)-->

													<!--	@foreach($order['collage_images'] as $collageImage)-->
															

													<!--		@break {{-- Show only the first image --}}-->

													<!--	@endforeach-->

													<!--@else-->

													<!--	<span>No collage image available</span>-->

													<!--@endif-->

												</div>

											</div>

										</td>

										<td>{{$order['width']}}cm X {{$order['height']}}cm</td>

										<td>{{$order['total_tiles']}}</td>

										<td>{{ \Carbon\Carbon::parse($order['created_at'])->format('F d, Y') }}</td>

		

										<td> 

											<a href="{{ route('front.design-collage', ['unique_id' => $order['unique_id']]) }}" class="btn btn-primary">

												Open Draft

											</a>

										</td>

									</tr>
                                @endforeach

                            @else

                               <tr><td style="border:none;"></td><td style="border:none;"></td><td style="border:none;"></td><td style="border:none;"><p style="color: red;">No Data Found !</p></td></tr>    

                            @endif 

						

						</table>

					</div>

				</div>

			</div>

			

		</div>

	</div>

</section>

@endsection

