@include('admin.include.header')

<section>
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a class="text-muted " href="{{ route('admin.couponList') }}">Coupon List</a>
                </li>
                <li class="breadcrumb-item" aria-current="page">Add Coupon</li>
            </ol>
        </nav>


        <div class="mt-4">
            <div class="card">
                <div class="px-4 py-3 border-bottom">
                    <h5 class="card-title fw-semibold mb-0">Add Coupon</h5>
                </div>
                <div class="card-body p-4 border-bottom">
                    <!-- <h5 class="fs-4 fw-semibold mb-4">Account Details</h5> -->
                    <form id="addCouponForm" onsubmit="return addCoupon()">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6" id="addCouponFormContainer">


                                <!-- Coupon Code -->
                                <div class="mb-4">
                                    <label for="coupon_code" class="form-label fw-semibold">Coupon Code</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="coupon_code" name="coupon_code"
                                            placeholder="Enter Coupon Code" oninput="formatCouponCode(this)"
                                            onkeydown="preventSpace(event)">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="generateCoupon()">Generate</button>
                                    </div>
                                </div>

                                <!-- Discount Value -->
                                <div class="row mb-4">
                                    <div class="col-8">
                                        <label for="Discount Value" class="form-label fw-semibold">Discount</label>
                                        <input type="number" class="form-control" min="0" max="100"
                                            id="discount" name="discount" placeholder="Enter Discount">
                                    </div>
                                    <div class="col-4" style="margin-top: 1.8rem !important;">
                                        <select class="form-control" name="discount_type">
                                            <option value="percent">%</option>
                                            <option value="value">value</option>
                                        </select>
                                    </div>
                                </div>

                                <!--Usage Limit -->
                                <div class="mb-4">
                                    <label for="Usage Limit" class="form-label fw-semibold">Usage Limit (no of
                                        users)</label>
                                    <input type="number" class="form-control" id="usage_limit" name="usage_limit"
                                        placeholder="Enter Usage Limit" value="0" min="0">
                                </div>

                                <!-- Min Purchase Amount
                                <div class="mb-4">
                                    <label for="Min Purchase Amount" class="form-label fw-semibold">Min Purchase Amount</label>
                                    <input type="number" class="form-control" min="0" step="0.01" id="min_purchase_amount" name="min_purchase_amount"
                                        placeholder="Enter Min Purchase Amount">
                                </div> -->

                                <!-- Valid From -->
                                <div class="mb-4">
                                    <label for="Valid From" class="form-label fw-semibold">Valid From</label>
                                    <input type="date" class="form-control" id="valid_from" name="valid_from"
                                        placeholder="Select Valid From Date">
                                </div>

                                <!-- Valid From -->
                                <div class="mb-4">
                                    <label for="Valid To" class="form-label fw-semibold">Valid To</label>
                                    <input type="date" class="form-control" id="valid_to" name="valid_to"
                                        placeholder="Select Valid To Date">
                                </div>

                                <!-- Valid From -->
                                <div class="mb-4">
                                    <label for="Status" class="form-label fw-semibold">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="Description" class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control" id="description" name="description" placeholder="Enter Description"></textarea>
                                </div>

                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-3">
                            <button class="btn btn-primary" type="submit" id="addCouponBtn">Add</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

@include('admin.include.footer')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('valid_from').setAttribute('min', today);
        document.getElementById('valid_to').setAttribute('min', today);
    });

    function generateCoupon() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let coupon = '';
        for (let i = 0; i < 8; i++) {
            coupon += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('coupon_code').value = coupon;
    }

    function formatCouponCode(input) {
        input.value = input.value.toUpperCase().replace(/\s/g, '');
    }

    function preventSpace(event) {
        if (event.key === ' ') {
            event.preventDefault();
        }
    }

    function addCoupon() {
        $.ajax({

            url: "{{ route('admin.storeCoupon') }}",

            type: 'POST',

            data: new FormData($('#addCouponForm')[0]),

            dataType: 'json',

            cache: false,

            contentType: false,

            processData: false,

            beforeSend: function() {

                $('#addCouponBtn').prop('disabled', true);

                $('#addCouponBtn').text('Processing..');

            },

            success: function(res) {

                $('#addCouponBtn').prop('disabled', false);

                $('#addCouponBtn').text('Add');

                if (res.status == 1) {

                    Swal.fire({

                        icon: 'success',

                        title: 'Success',

                        text: res.message,

                    }).then(function() {

                        window.location.href = "{{ route('admin.couponList') }}";

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
                $('#addCouponBtn').prop('disabled', false);
                $('#addCouponBtn').text('Add');
                $('.text-danger').remove();
                if (error.responseJSON && error.responseJSON.errors) {
                    var errors = error.responseJSON.errors;
                    for (var err in errors) {
                        if (errors.hasOwnProperty(err)) {
                            var errorMessage = errors[err][0];
                            $("[name='" + err + "']").next('.text-danger')
                        .remove(); // Remove previous error message
                            $("[name='" + err + "']").after("<div class='text-danger'>" + errorMessage +
                                "</div>");
                        }
                    }
                } else {
                    Swal.fire({

                        icon: 'error',

                        title: 'Error',

                        text: error.responseJSON.message,

                    });

                }

            }

        });
        return false;

    }
</script>
