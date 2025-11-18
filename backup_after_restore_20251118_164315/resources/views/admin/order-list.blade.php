@include('admin.include.header')
<style>
    .text-nowrap {
        white-space: nowrap !important;
    }
</style>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Orders Management</h3>
    </div>
</div>

<div class="container mt-5">
    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Order Id</th>
                <th>Address</th>
                <th>Country</th>
                <th>Order Date</th>
                <th>Delivery Date</th>
                <th>Amount (US$)</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="orderTableBody">

        </tbody>
    </table>
</div>

<div class="modal fade" id="updateOrderModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Order Status</h5>
            </div>
            <form id="shippingForm" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="pop_order_id" value="">
                    <div class="form-group ">
                        <label>Select Status</label>
                        <select class="form-control" name="order_status" id="pop_status" required>
                            <option value="">Select Status</option>
                            <option>Ordered</option>
                            <option>Preparing</option>
                            <option>Prepared</option>
                            <option>Packed</option>
                            <option>Shipped</option>
                            <option>Out of Delivery</option>
                            <option>Delivered</option>
                            <option>Order Completed</option>
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label>Delivery Date</label>
                        <input type="date" class="form-control" name="delivery_date" id="pop_delivery_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="updateOrderBtn">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.13.1/sorting/date-eu.js"></script>


<script type="text/javascript">
    $(document).ready(function() {

        $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.getOrderList') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'internal_order_id',
                    name: 'internal_order_id'
                },
                {
                    data: 'shipping_address',
                    name: 'shipping_address'
                },
                {
                    data: 'shipping_country_name',
                    name: 'shipping_country_name'
                },
                // {
                //     data: 'created_at',
                //     name: 'created_at'
                // },
                // {
                //     data: 'delivery_date',
                //     name: 'delivery_date'
                // },
                // {
                //     data: 'created_at',
                //     name: 'created_at',
                //     className: 'text-nowrap',
                //     render: {
                //         _: 'display',
                //         sort: 'timestamp'
                //     }
                // },
                // {
                //     data: 'delivery_date',
                //     name: 'delivery_date',
                //     className: 'text-nowrap',
                //     render: {
                //         _: 'display',
                //         sort: 'timestamp'
                //     }
                // },

                {
                    data: 'created_at_display',
                    name: 'created_at_display',
                    className: 'text-nowrap',
                    orderData: [9] // must match raw timestamp column index
                },
                {
                    data: 'delivery_date_display',
                    name: 'delivery_date_display',
                    className: 'text-nowrap',
                    orderData: [10] // must match raw timestamp column index
                },

                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'order_status',
                    name: 'order_status',
                    // render: function(data, type, row) {
                    //     return data == 0 ? 'Ordered' : '';
                    // }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }, 
                {
                    data: 'created_at_raw',
                    name: 'created_at_raw',
                    visible: false,
                    searchable: false
                },
                {
                    data: 'delivery_date_raw',
                    name: 'delivery_date_raw',
                    visible: false,
                    searchable: false
                }
            ],
            order: [
                [9, 'desc']
            ]
        });


        $(document).on('click', '.block-user-btn', function() {
            let btn = $(this);
            var url = btn.data('url'); // Get the URL from the button

            btn.prop('disabled', true);

            $.ajax({
                url: url, // Use the URL passed from Blade
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 1) {
                        if (response.user_status === 1) {
                            $('#dataTable').DataTable().ajax.reload();
                            btn.prop('disabled', false);
                            btn.html('Activate');

                            toastr.success('User status changed to active successfully.');
                        }

                        if (response.user_status === 2) {
                            $('#dataTable').DataTable().ajax.reload();
                            btn.prop('disabled', false);
                            btn.html('Block');

                            toastr.success('User status changed to blocked successfully.');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled',
                        false);

                    let response = xhr.responseJSON;
                    if (response && response.message) {
                        toastr.error(response.message);
                    } else {
                        toastr.error('Something went wrong.');
                    }
                }
            });
        });
    });

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
