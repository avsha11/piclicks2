@extends('front.layout.front-layout')
@push('title', 'Gift cards')
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
        /* gift cards css */
        .product-main .product-image img {
            width: 100%;
        }

        .product-main {
            padding: 20px;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 12px;
            border-radius: 8px;
            margin-bottom: 0px;
            height: 100%;
        }

        .product-main .product-detail h6 {
            margin-bottom: 6px;
        }

        .product-main .product-detail p {
            line-height: 1.2;
        }

        /* cards css end */
        /* modal css */
        .giftamount-card {
            background: #f6f6f6;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        /* css end */
    </style>
    <section class="create_galley ">
        <div class="container shop-page px-0">
            <div class="row mb-3">
                <div class="col-sm-12">
                    <h2>Gift cards</h2>
                </div>
            </div>
            <div class="row">
                 @if ($giftcards->isNotEmpty())
                            @foreach ($giftcards as $value)
                                <div class="col-lg-4 col-xs-12 col-md-6 mb-3">
                                    <div class="product-main">
                                        <div class="product-image">
                                            <img src="{{ asset('storage/' . $value->image) }}" class="img-responsive"
                                                loading="lazy">
                                        </div>
                                        <div class="product-detail">
                                            <a href="javascript:;">
                                                <div class="detial_small">
                                                    <h4 class="mb-2"> {{ $value->title ?? '' }}</h4>
                                                    <p><span>{{ $value->short_description ?? '' }}</span></p>
                                                </div>
                                                <div class="my-2 fw-normal">{{ config('app.default_currency') }}
                                                    {{ $value->amount }}</div>
                                            </a>
                                            <a href="javascript:;" class="btn btn-primary"
                                                onclick="openPurchasePopup('{{ $value->amount }}', '{{ $value->id }}');">Buy
                                                now</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
            </div>
        </div>
    </section>
    <div class="modal fade" id="purchaseGiftcardModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-black">Giftcard Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card-frame giftamount-card">
                        <!-- <h4>Giftcard Amount</h4> -->
                        <label for="" id="lbl_giftcard_amount" class="fw-bold"></label>
                        <input type="hidden" id="giftcard_amount" value="0">
                        <input type="hidden" id="giftcard_id" value="">
                    </div>
                    <div class="card-frame">
                        <h4>Who's the lucky recipient?</h4>
                        <input class="form-control" id="recipientName" type="text" placeholder="Recipient's name">
                        <span class="text-danger" id="recipientNameError"></span>
                    </div>
                    <div class="card-frame">
                        <h4>Email</h4>
                        <input class="form-control" id="recipientEmail" type="text" placeholder="Email">
                        <span class="text-danger" id="recipientEmailError"></span>
                    </div>
                    <div class="card-frame">
                        <h4>Message</h4>
                        <textarea class="form-control" id="gift_card_message"></textarea>
                    </div>
                    <div class="card-frame">
                        <button type="button" id="purchase_button" onclick="return purchaseGiftCard()"
                            class="btn btn-primary btn-lg w-100">Purchase</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        function openPurchasePopup(amount, giftcard_id) {
            $("#giftcard_amount").val(amount);
            $("#lbl_giftcard_amount").html("{{ config('app.default_currency') }} " + amount);
            $("#giftcard_id").val(giftcard_id);
            $("#purchaseGiftcardModal").modal('show');
        }

        function purchaseGiftCard() {
            var giftcard_id = $('#giftcard_id').val();
            var gitCardCost = $('#giftcard_amount').val();
            var recipientName = $('#recipientName').val();
            if (recipientName == '') {
                $("#recipientNameError").html('Please enter Recipient Name');
                return false;
            }
            $("#recipientNameError").html('');

            var recipientEmail = $('#recipientEmail').val();
            var giftCardMessage = $('#gift_card_message').val();
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (recipientEmail == '') {
                $("#recipientEmailError").html('Please enter recipient email');
                return false;
            } else if (!emailPattern.test(recipientEmail)) {
                $("#recipientEmailError").html('Please enter a valid email address');
                return false;
            }
            $("#recipientEmailError").html('');

            $.ajax({
                url: "{{ route('front.purchaseGiftCard') }}",
                type: 'POST',
                data: {
                    giftcard_id: giftcard_id,
                    gitCardCost: gitCardCost,
                    recipientName: recipientName,
                    recipientEmail: recipientEmail,
                    giftCardMessage: giftCardMessage,
                    token: "{{ csrf_token() }}",
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#purchase_button').prop('disabled', true);
                    $('#purchase_button').text('Processing..');
                },
                success: function(res) {
                    if (res.status === 1) {
                        toastr.success(res.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                        setTimeout(() => {
                            window.location.href = "{{ route('checkout') }}";
                        }, 2000);
                    } else {
                        $('#purchase_button').prop('disabled', false);
                        $('#purchase_button').text('Purchase');
                        toastr.error(res.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                    }
                },
                error: function(res) {
                    console.log(res);
                    if (res.responseJSON && res.responseJSON.message) {
                        toastr.error(res.responseJSON.message, '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                    } else {
                        toastr.error('Something went wrong', '', {
                            closeButton: true,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                    }
                }
            });
            return false;
        }
    </script>
@endpush
