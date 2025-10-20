@extends('front.layout.front-layout')
{{-- @push('title', 'Change Password') --}}
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
			<h2>{{ $title}}</h2>
		</div>
		<div class="row mx-0 mx-md-auto">
			@include('front.partials.sidebar', ['title' => 'Card Title'])
			@include('front.partials.change-password', ['title' => 'Change Password'])
			
		</div>
	</div>
</section>

@endsection
@section('scripts')

@endsection

