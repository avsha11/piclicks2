@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Coupon Management</h3>

    </div>
</div>
<div class="container">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.addCoupon') }}"  class="btn btn-primary">Add Coupon</a>
    </div>
</div>

<div class="container mt-5">
    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
                <th>Sno</th>
                <th>Coupon Code</th>
                <th>Description</th>
                <th>Discount</th>
                <th>Valid From</th>
                <th>Valid To</th>
                <th>Usage Limit</th>
                <th>Limit Used</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody >

        </tbody>
    </table>
</div>
@include('admin.include.footer')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.getCoupons') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'coupon_code', name: 'coupon_code' },
            { data: 'description', name: 'description' },
            { data: 'discount', name: 'discount' },
            { data: 'valid_from', name: 'valid_from' },
            { data: 'valid_to', name: 'valid_to' },
            { data: 'usage_count', name: 'usage_count' },
            { data: 'limit_used', name: 'limit_used'},
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    inactivePastCoupons();
});

function inactivePastCoupons(){
    $.ajax({
        type: 'POST',
        url: "{{ route('admin.inactivePastCoupons') }}",
        data: {
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function(res) {
            console.log(res);
        },
    });
    return false;
}

function deleteCoupons(id) {
    Swal.fire({
        title: "Are You Sure ?",
        text: "You wnat to delete coupon ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.deleteCoupon') }}",
                data: {
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#deleteCouponBtn'+id).prop('disabled', true);
                    $('#deleteCouponBtn'+id).text('Processing..');

                },
                success: function(res) {
                    $('#deleteCouponBtn'+id).prop('disabled', false);
                    $('#deleteCouponBtn'+id).text('Delete');
                    if (res.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                        }).then(function() {
                            window.location.href = "{{ route('admin.couponList')}}";
                        });
                    }else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message,
                        }).then(function() {
                            
                        });
                    }
                },
            });
        }
    });
}

</script>


