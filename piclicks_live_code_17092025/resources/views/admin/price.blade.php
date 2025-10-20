@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Price Management</h3>
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
<div class="container mt-2">
    <form method="post" action="#">
        <div id="ethencity-container">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h6>Product Name</h6>
                </div>
                <div class="col-md-6">
                    <h6>Price ({{ env('DEFAULT_CURRENCY') }})</h6>
                </div>
            </div>

            @foreach ($data as $data)
            <div class="row ethencity-row" data-id="{{ $data->id }}">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="name[]"
                            id="name" value="{{ $data->name }}" readonly />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="price[]"
                            id="price" placeholder="Per photo tiles price ({{ env('DEFAULT_CURRENCY') }})" value="{{ $data->price }}" />
                    </div>
                </div>
            </div>
            @endforeach
        </div>



        <div class="col-12">
            <div class="d-md-flex align-items-center mt-3">
                <div class="mt-3 mt-md-0">
                    <button type="submit" id="update_btn" class="btn btn-primary">
                        <div class="d-flex align-items-center">
                            <span id="button-text">Update</span>
                            <span id="loader" style="display:none;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>


    </form>
</div>

<div class="container mt-5">
    <h5 class="fw-semibold">Calculate Final Price</h5>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="product_id">Select Product</label>
            <select class="form-control" id="product_id">
                <option value="">-- Select Product --</option>
                @foreach($product as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="tile_count">Number of Tiles</label>
            <input type="number" class="form-control" id="tile_count" placeholder="Enter tile count">
        </div>
        <div class="col-md-4 mb-3">
            <label for="country_id">Select Country</label>
            <select class="form-control" id="country_id">
                <option value="">-- Select Country --</option>
                @foreach($countries as $country)
                <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 mt-3">
            <button class="btn btn-success" id="calculate_price_btn">Calculate Price</button>
        </div>
        <!-- <div class="col-12 mt-4">
            <h5>Final Price: {{ config('app.default_currency') }}<span id="final_price_display">--</span></h5>
        </div> -->
        <div class="col-12 mt-4">
            <h5>Final Price: <span id="final_price_wrapper" style="display: none;">{{ config('app.default_currency') }} <span id="final_price_display"></span></span></h5>
        </div>

    </div>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#update_btn').click(function(event) {
            event.preventDefault();
            var priceData = [];
            $(".ethencity-row").each(function() {
                var row = $(this);
                var id = row.data('id');
                var name = row.find("input[name='name[]']").val();
                var price = row.find("input[name='price[]']").val();

                priceData.push({
                    id: id,
                    name: name,
                    price: price
                });
            });

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('admin.storePrice') }}",
                type: 'POST',
                data: {
                    prices: priceData
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#update_btn').prop('disabled', true);
                    $('#button-text').hide();
                    $('#loader').show();
                },
                success: function(res) {
                    $('#update_btn').prop('disabled', false);
                    $('#button-text').show();
                    $('#loader').hide();
                    if (res.status == 1) {
                        console.log('success status');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message,
                        }).then(function() {

                        });
                    }
                },
                error: function(error) {
                    $('#update_btn').prop('disabled', false);
                    $('#button-text').show();
                    $('#loader').hide();
                    if (error.responseJSON && error.responseJSON.errors) {
                        $('.text-danger').remove();
                        $.each(error.responseJSON.errors, function(field, messages) {
                            var match = field.match(/names\.(\d+)\.name/);
                            if (match) {
                                var index = match[1];
                                var inputField = $("input[name='gender_name[]']")
                                    .eq(index);

                                if (inputField.length > 0) {
                                    inputField.next(".text-danger").remove();
                                    inputField.after("<div class='text-danger'>" +
                                        messages[0] + "</div>");
                                } else {
                                    console.warn('No input field found for index:',
                                        index);
                                }
                            } else {
                                console.warn('No matching input field for field:',
                                    field);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.responseJSON.message,
                        }).then(function() {

                        });
                    }
                }
            });

        });
        $('#calculate_price_btn').on('click', function() {
            let product_id = $('#product_id').val();
            let tile_count = $('#tile_count').val();
            let country_id = $('#country_id').val();

            if (!product_id || !tile_count || !country_id) {
                alert('Please fill all fields.');
                return;
            }

            $.ajax({
                url: "{{ route('admin.calculateFinalPrice') }}",
                type: "GET",
                data: {
                    product_id: product_id,
                    tile_count: tile_count,
                    country_id: country_id
                },
                success: function(response) {
                    const price = response.final_price;

                    if (price && !isNaN(price)) {
                        $('#final_price_display').text(price);
                        $('#final_price_wrapper').show();
                    } else {
                        $('#final_price_display').text('');
                        $('#final_price_wrapper').hide();
                    }
                },
                error: function() {
                    $('#final_price_display').text('');
                    $('#final_price_wrapper').hide();
                }
            });
        });

    });
</script>
@include('admin.include.footer')