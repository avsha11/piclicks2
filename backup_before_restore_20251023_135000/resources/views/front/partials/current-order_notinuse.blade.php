<style>
    .badge {
        line-height: 1.5;
    }
</style>


<div class="col-sm-9">
    <div class="page-title mb-1">
        <h4>{{ $title }}(s)</h4>
    </div>

    <div class="orders-container">
        @if ($orders->isNotEmpty())
            @foreach ($orders as $order)
                <div class="order-card border rounded p-4 mt-4 mb-3 shadow-sm">
                    <!-- Order Header -->
                    <div class="order-header bg-primary text-light p-2 rounded d-flex justify-content-between">
                        <strong>Order ID: #{{ $order->internal_order_id }}</strong>
                        <span class="badge bg-danger">Pending</span>
                    </div>

                    <!-- Order Products -->
                    <div class="order-items mt-3">
                        @foreach ($order->orderdetail_data as $product)
                            @if ($product->name == 'collage')
                                @php
                                    $productData = DB::table('design_collage_master')
                                        ->where('unique_id', $product->collage_unique_id)
                                        ->first();
                                @endphp

                                <div class="d-flex align-items-center border-bottom pb-2 mb-2">
                                    <!-- Product Image -->
                                    <div class="product-img me-3">
                                        @if (isset($productData->image_path) && $productData->image_path != null)
                                            <img src="{{ asset('/storage/' . $productData->image_path) }}"
                                                class="img-fluid rounded" alt="{{ $productData->name ?? null }}"
                                                style="width: 60px; height: 60px;">
                                        @else
                                            <img src="{{ asset('assets/images/collage-image.png') }}"
                                                class="img-fluid rounded" style="width: 60px; height: 60px;">
                                        @endif
                                    </div>


                                    <!-- Product Details -->
                                    <div class="product-info flex-grow-1">
                                        <small>Quantity: {{ $product->quantity ?? 0 }}</small>
                                    </div>

                                    <!-- Product Price -->
                                    <div class="product-price text-end">
                                        <strong>{{ config('app.default_currency') }} {{ $product->amount ?? 0 }}</strong>
                                    </div>
                                </div>
                            @elseif ($product->name == 'giftcard')
                                <div class="d-flex align-items-center border-bottom pb-2 mb-2">
                                    <!-- Product Image -->
                                    {{-- <div class="product-img me-3">
                                        @if (isset($productData->image_path) && $productData->image_path != null)
                                            <img src="{{ asset('/storage/' . $productData->image_path) }}"
                                                class="img-fluid rounded" alt="{{ $productData->name ?? null }}"
                                                style="width: 60px; height: 60px;">
                                        @else
                                            <img src="{{ asset('assets/images/collage-image.png') }}"
                                                class="img-fluid rounded" style="width: 60px; height: 60px;">
                                        @endif

                                    </div> --}}
									<div class="product-img me-3">
                                    <div class="minicart-giftcard">
                                        @include('front.component.gift-card-component', [
                                            'giftCardType' => $product->giftcard_type,
                                            'giftCardValue' => $product->amount,
                                        ])
                                    </div>
                                    </div>


                                    <!-- Product Details -->
                                    <div class="product-info flex-grow-1">
                                        <small>Quantity: {{ $product->quantity ?? 0 }}</small>
                                    </div>

                                    <!-- Product Price -->
                                    <div class="product-price text-end">
                                        <strong>{{ config('app.default_currency') }} {{ $product->amount ?? 0 }}</strong>
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
                        <button type="button" class="btn btn-primary" onclick="getOrderDetail('{{ $order->id }}')">
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
