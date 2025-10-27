@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Gift Card List</h3>
    </div>
</div>

<div class="container mt-5">
    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Ref Id</th>
                <th>Image</th>
                <th>Order Id</th>
                <th>Email</th>
                <th>Message</th>
                <th>Price (US$)</th>
                <th>Quantity</th>
                <th>Total Amount (US$)</th>
                <th>Redeem by</th>
                <th>Status</th>

            </tr>
        </thead>
        <tbody id="orderTableBody">

        </tbody>
    </table>
</div>





<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        const assetBasePath = "{{ asset('storage/') }}";
        $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.giftcardList') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    title: 'Sr. No.',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'ref_id',
                    name: 'ref_id'
                },
                {
                    data: 'image',
                    name: 'image',
                    render: function(data, type, row) {
                        let imgSrc = assetBasePath + '/' + data;
                        return '<img src="' + imgSrc + '" alt="' + data +
                            ' Gift Card" width="50" />';
                    }
                },
                {
                    data: 'internal_order_id',
                    name: 'internal_order_id'
                },
                {
                    data: 'email',
                    name: 'email'
                },


                {
                    data: 'message',
                    name: 'message'
                },
                {
                    data: 'price',
                    name: 'price'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'redeem_by_name',
                    name: 'redeem_by_name'
                },
                {
                    data: 'status',
                    render: function(data, type, row) {
                        if (data == 0) {
                            return 'Not Used';
                        } else if (data == 2) {
                            return 'Redeemed';
                        } else if (data == 1) {
                            return 'Used (#' + row.used_on_order_id + ')';
                        } else {
                            return '';
                        }
                    }
                }

            ]
        });



    });
</script>


@include('admin.include.footer')
