@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Art Gallery Management</h3>

    </div>
</div>
<div class="container">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.uploadPhotos', ['type' => 'gallery']) }}"  class="btn btn-primary">Add Art Gallery Image</a>
    </div>
</div>

<div class="container mt-5">
    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
                <th>Sno</th>
                <th>Image</th>
                <th>Grid Rows</th>
                <th>Grid Columns</th>
                <th>Image Size</th>
                <th>Total Tiles</th>
                <th>Created Date</th>
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
        ajax: "{{ route('admin.getGalleryList') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            {
                data: 'image',
                name: 'image',
                render: function(data, type, row) {
                    if (data) {
                        return '<a href="' + data + '" target="_blank">' +
                            '<img src="' + data + '" alt="Image" style="width: 100px; height: 100px;" />' +
                            '</a>';
                    } else {
                        return 'No image';
                    }
                }
            },
            {
                data: 'grid_rows',
                name: 'grid_rows'
            },
            {
                data: 'grid_columns',
                name: 'grid_columns'
            },
            {
                data: 'image_size',
                name: 'image_size'
            },
            {
                data: 'total_tiles',
                name: 'total_tiles'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

});
$(document).on('click', '.delete-collage', function (e) {
    e.preventDefault();
    let button = $(this);
    let url = button.data('url');

    if (confirm('Are you sure you want to delete this collage?')) {
        $.ajax({
            url: url,
            type: 'GET', // since your route uses GET
            success: function (response) {
                // Optional: show a success message
                alert('Collage deleted successfully.');

                // Option 1: reload the DataTable if you're using it
                $('#dataTable').DataTable().ajax.reload();

                // Option 2: remove the row manually (if not using DataTable)
                // button.closest('tr').remove();
            },
            error: function (xhr) {
                alert('Something went wrong. Please try again.');
            }
        });
    }
});

</script>


