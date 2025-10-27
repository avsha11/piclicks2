@include('admin.include.header')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted " href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a class="text-muted " href="{{ route('admin.orderList') }}">Orders List</a></li>
            <li class="breadcrumb-item" aria-current="page">Order Details </li>
        </ol>
    </nav>
    <div class="card mt-4">
        <div class="px-4 py-4 border-bottom">
            <h5 class="card-title fw-semibold mb-0">Order Details
                <a href="{{ route('admin.orderList') }}" class="btn btn-primary mx-3 btn-sm">Back</a>
            </h5>
        </div>
        <div class="card-body p-3 border-bottom">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="p-3 border-bottom">
                            <h5 class="card-title fw-semibold mb-0">Basic Details</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Order Id :</label>
                                <label class="">#{{ $order->internal_order_id }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Order Status :</label>
                                <label class="">{{ $order->order_status }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Total Quantity :</label>
                                <label class="">{{ $order->total_quantity }}</label>
                            </div>
                            <div class="mb-3">
                                <table class="table table-bordered">
                                    <thead>
                                        <th>Particulars</th>
                                        <th>Amount</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <label class="form-label fw-semibold">Sub Total :</label>
                                            </td>
                                            <td>
                                                <label class="">{{ config('app.default_currency') }}
                                                    {{ $order->sub_total }}</label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-label fw-semibold">Shipping Amount :</label>
                                            </td>
                                            <td>
                                                <label class="">{{ config('app.default_currency') }}
                                                    {{ $order->shipping_amount }}</label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-label fw-semibold">VAT Amount :</label>
                                            </td>
                                            <td>
                                                <label class="">{{ config('app.default_currency') }}
                                                    {{ $order->vat_amount }}</label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-label fw-semibold">Discount Amount :</label>
                                                <br>
                                                <label class="">Code : {!! stripos($order->coupon_code, ',') ? str_replace(',', '<br>', $order->coupon_code) : $order->coupon_code !!}</label>
                                            </td>
                                            <td>
                                                <label class="">{{ config('app.default_currency') }}
                                                    {{ $order->discount_amount }} </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-label fw-semibold">Giftcard Amount :</label>
                                                <br>
                                                <label class="">Code : {!! stripos($order->giftcard_code, ',')
                                                    ? str_replace(',', '<br>', $order->giftcard_code)
                                                    : $order->giftcard_code !!}</label>
                                            </td>
                                            <td>
                                                <label class="">{{ config('app.default_currency') }}
                                                    {{ $order->giftcard_amount }} </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-label fw-semibold">Total Amount :</label>
                                            </td>
                                            <td>
                                                <label class="">{{ config('app.default_currency') }}
                                                    {{ $order->total_amount }}</label>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- <div class="mb-4">
                                <label class="form-label fw-semibold">Profile Image</label>
                                <br>
                                @if ($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Images"
                                        class="img-fluid" style="max-width: 150px;">
                                @else
                                    <img src="{{ asset('storage/profileImage/user.png') }}" alt="Profile Image"
                                        class="img-fluid" style="max-width: 150px;">
                                @endif
                            </div> --}}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="p-3 border-bottom">
                            <h5 class="card-title fw-semibold mb-0">Shipping Details</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name :</label>
                                <label class="">{{ $order->shipping_fullname }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone :</label>
                                <label class="">{{ $order->shipping_phone }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email :</label>
                                <label class="">{{ $order->shipping_email }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Company Name :</label>
                                <label class="">{{ $order->shipping_company_name }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address :</label>
                                <label class="">{{ $order->shipping_address }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address (Optional) :</label>
                                <label class="">{{ $order->shipping_address_opt }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">City :</label>
                                <label class="">{{ $order->shipping_city }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">State :</label>
                                <label class="">{{ $order->shipping_state }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Country :</label>
                                <label class="">{{ $order->shipping_country }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Postal Code :</label>
                                <label class="">{{ $order->shipping_postalcode }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="p-3 border-bottom">
                            <h5 class="card-title fw-semibold mb-0">Billing Details</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name :</label>
                                <label class="">{{ $order->billing_fullname }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone :</label>
                                <label class="">{{ $order->billing_phone }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email :</label>
                                <label class="">{{ $order->billing_email }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Company Name :</label>
                                <label class="">{{ $order->billing_company_name }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address :</label>
                                <label class="">{{ $order->billing_address }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address (Optional) :</label>
                                <label class="">{{ $order->billing_address_opt }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">City :</label>
                                <label class="">{{ $order->billing_city }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">State :</label>
                                <label class="">{{ $order->billing_state }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Country :</label>
                                <label class="">{{ $order->billing_country }}</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Postal Code :</label>
                                <label class="">{{ $order->billing_postalcode }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="p-3 border-bottom">
                            <h5 class="card-title fw-semibold mb-0">Product Details</h5>
                        </div>
                        <div class="card-body p-3">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Item</th>
                                        <th>Total Rows</th>
                                        <th>Total Columns</th>
                                        <th>Filter</th>
                                        <th>Quantity</th>
                                        <th>Amount(US$)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($order->orderdetail_data) && count($order->orderdetail_data) > 0)
                                        @foreach ($order->orderdetail_data as $key => $orderData)
                                            @if ($orderData->name == 'collage' || $orderData->name == 'artgallery')
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>
                                                        <a href="{{ $orderData->design_collage_master->image_path ? asset('storage/' . $orderData->design_collage_master->image_path) : asset('assets/images/collage-image.png') }}"
                                                            target="_blank">
                                                            <img src="{{ $orderData->design_collage_master->image_path ? asset('storage/' . $orderData->design_collage_master->image_path) : asset('assets/images/collage-image.png') }}"
                                                                style="width: 60px; height: 60px;">
                                                        </a>
                                                    </td>
                                                    <td>{{ $orderData->name == 'collage' ? 'Collage' : 'Art Gallery' }}
                                                        {{ $orderData->design_collage_master->total_tiles ?? 0 }} tiles
                                                        ({{ $orderData->design_collage_master->height ?? 0 }} X
                                                        {{ $orderData->design_collage_master->width ?? 0 }} cm)
                                                    </td>
                                                    <td>{{ $orderData->design_collage_master->grid_rows ?? 0 }}</td>
                                                    <td>{{ $orderData->design_collage_master->grid_columns ?? 0 }}</td>
                                                    <td>{{ $orderData->design_collage_master->filter ?? '-' }}</td>
                                                    <td>{{ $orderData->quantity ?? 0 }}</td>
                                                    <td>{{ $orderData->amount ?? 0.0 }}</td>
                                                    <td>
                                                        <button id="download-btn-{{ $key }}"
                                                            class="btn btn-success btn-sm"
                                                            onclick='makeImagesZipByOrder("{{ $orderData->collage_unique_id }}", "download-btn-{{ $key }}")'>
                                                            <span class="btn-text">Download Zip</span>
                                                            <span class="spinner-border spinner-border-sm d-none"
                                                                role="status" aria-hidden="true"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @elseif ($orderData->name == 'giftcard')
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    {{-- <td class="minicart-giftcard">
                                                        @include('front.component.gift-card-component', [
                                                            'giftCardType' => $orderData->giftcard_type,
                                                            'giftCardValue' => $orderData->amount,
                                                        ])
                                                    </td> --}}
                                                    <td>
                                                        <a href="{{ asset('storage/' . $orderData->giftcard_data->image) }}"
                                                            target="_blank">
                                                            <img src="{{ asset('storage/' . $orderData->giftcard_data->image) }}"
                                                                style="width: 60px; height: 60px;">
                                                        </a>
                                                    </td>
                                                    <td>Giftcard</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>{{ $orderData->quantity ?? 0 }}</td>
                                                    <td>{{ $orderData->amount ?? 0.0 }}</td>
                                                    <td>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-center">No products found</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="p-3 border-bottom">
                            <h5 class="card-title fw-semibold mb-0">Order Tracking</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="badge bg-info my-3">{{ $order->order_status ?? '' }}</div>
                            <div class="timeline">
                                <div class="purchase-item">
                                    <div class="wrapper">
                                        @php
                                            $statuses = json_decode($order->order_tracking, true);
                                        @endphp
                                        <ul class="sessions mt-0">
                                            @if (isset($statuses) && count($statuses) > 0)
                                                @foreach ($statuses as $status)
                                                    <li>
                                                        <div class="time">
                                                            {{ \Carbon\Carbon::parse($status['date'])->format('M d') }}
                                                        </div>
                                                        <p>{{ $status['status'] }}</p>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li>
                                                    <div class="time">No Status</div>
                                                    <p>No status available</p>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script type="text/javascript">
    const baseUrl = "{{ url('/storage') }}";
    // for make zip file of images
    function makeImagesZip(designCollage, btnId) {
        const button = document.getElementById(btnId);
        const btnText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.spinner-border');
        // Disable button and show spinner
        button.disabled = true;
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        const zip = new JSZip();
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        const folderName = "design_collages_" + formattedDate;
        const folder = zip.folder(folderName);
        const validImages = designCollage.filter(item => item.empty == 0 && item.is_deleted == 0);
        if (validImages.length === 0) {
            alert("No valid images found.");
            // Restore button
            button.disabled = false;
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
            return;
        }
        let count = 0;
        validImages.forEach((item, index) => {
            let imageUrl = `${baseUrl}/${item.image_edited}`;
            // Extract file extension from the image path
            let extension = item.image_edited.split('.').pop().toLowerCase();
            let filename = `tile_${index + 1}.${extension}`;
            fetch(imageUrl)
                .then(res => res.blob())
                .then(blob => {
                    folder.file(filename, blob);
                    count++;
                    if (count === validImages.length) {
                        zip.generateAsync({
                            type: "blob"
                        }).then(content => {
                            saveAs(content, "design_collage_print_files.zip");
                            // Restore button
                            button.disabled = false;
                            btnText.classList.remove('d-none');
                            spinner.classList.add('d-none');
                        });
                    }
                })
                .catch(err => {
                    console.error("Error downloading image:", imageUrl, err);
                    // Restore button on error
                    button.disabled = false;
                    btnText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                });
        });
    }

    function makeImagesZipByOrder(orderId, btnId) {
        const button = document.getElementById(btnId);
        const btnText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.spinner-border');

        // Disable button and show spinner
        button.disabled = true;
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');

        let route = "{{ route('admin.get-design-collage-images', ['order_id'=>'__id']) }}";
        route = route.replace('__id', orderId);


        // fetch(`/get-design-collage-images/${orderId}`)
        fetch(route)
            .then(response => response.json())
            .then(data => {
                if (!data.status || !data.images || data.images.length === 0) {
                    alert("No valid images found.");
                    button.disabled = false;
                    btnText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                    return;
                }

                const zip = new JSZip();
                const today = new Date();
                const formattedDate = today.toISOString().split('T')[0];
                const folderName = "design_collages_" + formattedDate;
                const folder = zip.folder(folderName);

                let count = 0;
                data.images.forEach((item, index) => {
                    let imageUrl = `${baseUrl}/${item.image_edited}`;
                    // Extract file extension from the image path
                    let extension = item.image_edited.split('.').pop().toLowerCase();
                    let filename = `tile_${index + 1}.${extension}`;
                    fetch(imageUrl)
                        .then(res => res.blob())
                        .then(blob => {
                            folder.file(filename, blob);
                            count++;
                            if (count === data.images.length) {
                                zip.generateAsync({
                                    type: "blob"
                                }).then(content => {
                                    saveAs(content, "design_collage_print_files.zip");
                                    button.disabled = false;
                                    btnText.classList.remove('d-none');
                                    spinner.classList.add('d-none');
                                });
                            }
                        })
                        .catch(err => {
                            console.error("Error downloading image:", imageUrl, err);
                            button.disabled = false;
                            btnText.classList.remove('d-none');
                            spinner.classList.add('d-none');
                        });
                });
            })
            .catch(err => {
                console.error("AJAX error:", err);
                alert("Failed to fetch image data.");
                button.disabled = false;
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
            });
    }

    function updateStatus(order_id, order_status, delivery_date) {
        $("#pop_order_id").val(order_id);
        $("#pop_status").val(order_status);
        $("#pop_delivery_date").val(delivery_date);
        $("#updateOrderModal").modal('show');
    }
    $("#updateOrderBtn").click(function() {
        let btn = $(this);
        btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('admin.update-order-status') }}",
            type: 'POST',
            dataType: 'json',
            data: new FormData($('#shippingForm')[0]),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                btn.prop('disabled', false);
                if (res.status === 1) {
                    toastr.success(res.message);
                    $('#dataTable').DataTable().ajax.reload();
                    $("#updateOrderModal").modal('hide');
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false);
                let response = xhr.responseJSON;
                if (response && response.message) {
                    toastr.error(response.message);
                } else {
                    toastr.error('Something went wrong.');
                }
            }
        });
    })
</script>
@include('admin.include.footer')
