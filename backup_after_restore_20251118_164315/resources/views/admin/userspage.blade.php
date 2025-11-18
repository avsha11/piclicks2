@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Users Management</h3>
    </div>
</div>

<div class="container mt-5">
    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">

        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.getAllUsers') }}', // The route to fetch the data
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'user_status',
                    name: 'user_status',
                    render: function(data, type, row) {
                        return data == 1 ? 'Active' : 'Blocked';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
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
                        if(response.user_status === 1){
                            $('#dataTable').DataTable().ajax.reload();
                            btn.prop('disabled', false);
                            btn.html('Activate');

                            toastr.success('User status changed to active successfully.');
                        }

                        if(response.user_status === 2){
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
</script>


@include('admin.include.footer')
