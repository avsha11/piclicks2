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
                    <h5 class="card-title fw-semibold mb-0">Edit Coupon</h5>
                </div>
                <div class="card-body p-4 border-bottom">
                    <!-- <h5 class="fs-4 fw-semibold mb-4">Account Details</h5> -->
                    <form id="editCouponForm" onsubmit="return editCoupon()">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6" id="editCouponFormContainer">


                                <!-- Coupon Code -->
                                <div class="mb-4">
                                    <label for="coupon_code" class="form-label fw-semibold">Coupon Code</label>
                                    <div class="input-group">
                                        <input type="hidden" name="id" value="{{ $couponsData['id'] ?? '' }}" />
                                        <input type="text" class="form-control" id="coupon_code" name="coupon_code"
                                            placeholder="Enter Coupon Code"
                                            value="{{ $couponsData['coupon_code'] ?? '' }}" readonly>
                                    </div>
                                </div>

                                <!-- Discount Value -->
                                <div class="row mb-4">
                                    <div class="col-8">
                                        <label for="Discount Value" class="form-label fw-semibold">Discount</label>
                                        <input type="number" class="form-control" min="0" max="100"
                                            id="discount" name="discount" placeholder="Enter Discount"
                                            value="{{ $couponsData['discount'] ?? '' }}">
                                    </div>
                                    <div class="col-4" style="margin-top: 1.8rem !important;">
                                        <select class="form-control" name="discount_type">
                                            <option value="percent" {{ $couponsData['discount_type'] == 'percent' ? 'selected' : '' }}>%</option>
                                            <option value="value" {{ $couponsData['discount_type'] == 'value' ? 'selected' : '' }}>value</option>
                                        </select>
                                    </div>
                                </div>

                                <!--Usage Limit -->
                                <div class="mb-4">
                                    <label for="Usage Limit" class="form-label fw-semibold">Usage Limit (no of
                                        users)</label>
                                    <input type="number" class="form-control" id="usage_limit" name="usage_limit"
                                        placeholder="Enter Usage Limit" min="0"
                                        value="{{ $couponsData['usage_count'] ?? '' }}">
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
                                        placeholder="Select Valid From Date"
                                        value="{{ $couponsData['valid_from'] ?? '' }}">
                                </div>

                                <!-- Valid From -->
                                <div class="mb-4">
                                    <label for="Valid To" class="form-label fw-semibold">Valid To</label>
                                    <input type="date" class="form-control" id="valid_to" name="valid_to"
                                        placeholder="Select Valid To Date" value="{{ $couponsData['valid_to'] ?? '' }}">
                                </div>

                                <!-- Valid From -->
                                <div class="mb-4">
                                    <label for="Status" class="form-label fw-semibold">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Select Status</option>
                                        <option value="1" {{ $couponsData['status'] == 1 ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0" {{ $couponsData['status'] == 0 ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                </div>
                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="Description" class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control" id="description" name="description" placeholder="Enter Description">{{ $couponsData['description'] ?? '' }}</textarea>
                                </div>

                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-3">
                            <button class="btn btn-primary" type="submit" id="editCouponBtn">Edit</button>
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

    function editCoupon() {
        $.ajax({

            url: "{{ route('admin.updateCoupon') }}",

            type: 'POST',

            data: new FormData($('#editCouponForm')[0]),

            dataType: 'json',

            cache: false,

            contentType: false,

            processData: false,

            beforeSend: function() {

                $('#editCouponBtn').prop('disabled', true);

                $('#editCouponBtn').text('Processing..');

            },

            success: function(res) {

                $('#editCouponBtn').prop('disabled', false);

                $('#editCouponBtn').text('Edit');

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
                $('#editCouponBtn').prop('disabled', false);
                $('#editCouponBtn').text('Edit');
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
