@extends('front.layout.front-layout')
{{-- @push('title', 'My Order(s) ') --}}
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

        .badge {
            line-height: 1.5;
        }

        .swal2-title {
            color: #000 !important;
        }
    </style>
    <section class="create_galley cart-detail">
        <div class="container shop-page">
            <div class="page-title">
                <h2>All Orders</h2>
            </div>
            <div class="row mx-0 mx-md-auto">
                @include('front.partials.sidebar')

                <div class="col-lg-9 col-md-8">
                    <div class="orders-container">
                        @if (session('reorder_error'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                {{ session('reorder_error') }}
                            </div>
                        @endif
                        @if ($orders->isNotEmpty())
                            @foreach ($orders as $okey => $order)
                                <div class="order-card border rounded p-4 mt-4 mb-3 shadow-sm">
                                    <!-- Order Header -->
                                    <div
                                        class="order-header bg-primary text-light p-2 rounded d-flex justify-content-between">
                                        <strong>Order ID: #{{ $order->internal_order_id }}</strong>
                                        @php
                                            switch ($order->order_status) {
                                                case 'Ordered':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                                case 'Preparing':
                                                case 'Prepared':
                                                case 'Packed':
                                                    $badgeClass = 'bg-info';
                                                    break;
                                                case 'Shipped':
                                                case 'Out of Delivery':
                                                    $badgeClass = 'bg-warning text-dark';
                                                    break;
                                                case 'Delivered':
                                                case 'Order Completed':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-dark';
                                            }
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">{{ $order->order_status }}</span>

                                    </div>

                                    <!-- Order Products -->
                                    <div class="order-items mt-3">
                                        @foreach ($order->orderdetail_data as $product)
                                            @if ($product->name == 'collage' || $product->name == 'artgallery')
                                                @php
                                                    $productData = DB::table('design_collage_master')
                                                        ->where('unique_id', $product->collage_unique_id)
                                                        ->first();
                                                    
                                                    // Use PreviewRenderer image (same as Preview page) instead of html2canvas image
                                                    $uniqueId = $product->collage_unique_id ?? null;
                                                    $fallbackPath = $productData->image_path ?? null;
                                                    $thumbnailUrl = getCollagePreviewImagePath($uniqueId, $fallbackPath);
                                                @endphp

                                                <div class="d-flex align-items-center border-bottom pb-2 mb-2">
                                                    <!-- Product Image -->
                                                    <div class="product-img me-3">
                                                        <img src="{{ $thumbnailUrl }}"
                                                            class="img-fluid rounded"
                                                            alt="{{ $productData->name ?? null }}"
                                                            style="width: 60px; height: 60px;">
                                                    </div>


                                                    <!-- Product Details -->
                                                    <div class="product-info flex-grow-1">
                                                        <small>{{ $product->name == 'collage' ? 'Collage' : 'Art Gallery' }}</small>
                                                        <small>Qty: {{ $product->quantity ?? 0 }}</small>
                                                    </div>

                                                    <!-- Product Price -->
                                                    <div class="product-price text-end">
                                                        @if ($order->order_status == 'Order Completed')
                                                            <button type="button" class="btn btn-sm btn-success me-md-3"
                                                                onclick="return reorderCollage('{{ $product->collage_unique_id }}');"
                                                                style="padding: 7px;">
                                                                Re-order
                                                            </button>
                                                        @endif
                                                        <strong>{{ config('app.default_currency') }}
                                                            {{ $product->amount ?? 0 }}</strong>
                                                    </div>
                                                </div>
                                            @elseif ($product->name == 'giftcard')
                                                @php

                                                    $giftcardData = DB::table('giftcards')
                                                        ->where('id', $product->giftcard_id)
                                                        ->first();
                                                @endphp
                                                <div class="d-flex align-items-center border-bottom pb-2 mb-2">

                                                    <div class="product-img me-3">
                                                        {{-- <div class="minicart-giftcard">
                                                            @include(
                                                                'front.component.gift-card-component',
                                                                [
                                                                    'giftCardType' => $product->giftcard_type,
                                                                    'giftCardValue' => $product->amount,
                                                                ]
                                                            )
                                                        </div> --}}
                                                        <img src="{{ asset('storage/' . $giftcardData->image) }}"
                                                            class="img-fluid rounded"
                                                            alt="{{ $productData->title ?? null }}"
                                                            style="width: 60px; height: 60px;">
                                                    </div>


                                                    <!-- Product Details -->
                                                    <div class="product-info flex-grow-1">
                                                        <small>{{ 'Giftcard' }}</small>
                                                        <small>Qty: {{ $product->quantity ?? 0 }}</small>
                                                    </div>

                                                    <!-- Product Price -->
                                                    <div class="product-price text-end">
                                                        <strong>{{ config('app.default_currency') }}
                                                            {{ $product->amount ?? 0 }}</strong>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <!-- Order Summary -->
                                    <div class="order-footer mt-3 d-flex justify-content-between">
                                        <span><strong>Order Date:</strong>
                                            {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</span>

                                        <span><strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}</span>
                                    </div>

                                    <!-- Order Action -->
                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-primary"
                                            onclick="getOrderDetail('{{ $order->id }}')">
                                            View Details
                                        </button>

                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center p-4 border rounded bg-light">
                                <p>No orders available</p>
                            </div>
                        @endif
                    </div>

                </div>


            </div>
        </div>
    </section>
@endsection


@push('js')
    <script>
        function reorderCollage(unique_id) {
            Swal.fire({
                title: 'Re-order Confirmation',
                text: "Are you sure you want to re-order this collage?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reorder it!'
            }).then((result) => {
                if (result.isConfirmed) {

                    const url =
                        "{{ route('front.reorder', ['unique_id' => '__id__']) }}"
                        .replace('__id__', unique_id);
                    window.location.href = url;
                }
            });
        }
    </script>
@endpush
