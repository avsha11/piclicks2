@extends('front.layout.front-layout')

@push('title', 'Received Gift Cards')

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

                <h2>Received Gift Cards</h2>

            </div>

            <div class="row mx-0 mx-md-auto">

                @include('front.partials.sidebar')

                <div class="col-lg-9 col-md-8">

                    <div class="page-title mb-1">

                        <h5 class="text-success"><i class="fas fa-exclamation-circle"></i> Your Gift Card will automatically

                            load at the next checkout, so you don't have to do anything.</h5>

                    </div>

                    <div class="">

                        <div class="table-responsive">

                            <table class="table vertical-align-middle">

                                @php

                                    $available_balance = 0;

                                @endphp

                                @if ($giftCards->isNotEmpty())

                                    @foreach ($giftCards as $order)

                                        @php

                                            if ($order->status === 2) {

                                                $available_balance += $order->total_amt;

                                            }



                                        @endphp



                                        <tr>

                                            <td class="receive-giftcard">

                                                {{-- <div class="gift-card giftCartClass_{{ $order->type }} row">

                                                    <div class="col d-flex flex-column justify-content-between">

                                                        <div class="text-content">

                                                            <h1>Gift</h1>

                                                            <p>Card</p>

                                                        </div>



                                                        <div class="amount">

                                                            {{ config('app.default_currency') }} <span

                                                                class="gift-card-value"

                                                                id="gitCardCostSpan">{{ $order->total_amt }}</span>

                                                        </div>

                                                    </div>



                                                    <div class="col d-flex align-items-end">

                                                        <div class="gift-icon">

                                                            🎁

                                                        </div>

                                                    </div>





                                                </div> --}}

                                                <img src="{{ asset('storage/' . $order->giftcard_data->image) }}"

                                                    class="img-fluid rounded"

                                                    alt="{{ $order->giftcard_data->title ?? null }}"

                                                    style="width: 60px; height: 60px;">

                                            </td>

                                            <td>

                                                #REF{{ $order->id }}

                                            </td>

                                            <td>Sent By: {{ $order->order_data->user_data->name }}, on

                                                {{ \Carbon\Carbon::parse($order->created_at)->format('F d, Y') }}</td>

                                            <td>

                                                @if ($order->status === 1)

                                                    <span class="badge bg-secondary">Used</span>

                                                @elseif($order->status === 2)

                                                    <span class="badge bg-success">Redeemed</span>

                                                @endif

                                            </td>





                                        </tr>

                                    @endforeach

                                @else

                                    <tr>

                                        <td colspan="4" style="border:none;">

                                            <p style="color: red;">No gift cards Found !</p>

                                        </td>

                                    </tr>

                                @endif

                            </table>

                        </div>

                        <div class="row">

                            <h4>Total Balance: {{ config('app.default_currency') }} {{ $available_balance }}</h4>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        </div>

    </section>



@endsection

