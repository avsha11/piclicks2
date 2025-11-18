@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Tils Management</h3>

    </div>
</div>
<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#tileModal">Add Tile</button>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="tileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add New Tile</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="tileForm" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Form inside the modal -->
                    <div class="form-group ">
                        <label for="tileNumber">Number of Tile</label>
                        <input type="text" class="form-control" name="tileNumber" placeholder="Enter tile number">
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileCost">Cost ({{ env('DEFAULT_CURRENCY') }})</label>
                        <input type="number" class="form-control" name="cost" placeholder="Enter tile cost">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="tileBtn">Add Tile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="tileUpdateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Upate Tile</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateTileForm" method="POST">
                @csrf

                <div class="modal-body">
                    <!-- Form inside the modal -->
                    <input type="text" name="tileId" id="tileId" hidden>
                    <div class="form-group ">
                        <label for="tileNumber">Number of Tile</label>
                        <input type="text" class="form-control" name="tileNumber" id="tileNumber"
                            placeholder="Enter tile number" readonly>
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileCost">Cost ({{ env('DEFAULT_CURRENCY') }})</label>
                        <input type="number" class="form-control" name="tileCost" id="tileCost"
                            placeholder="Enter tile cost">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updateTileBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>





<div class="container mt-5">
    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Number of Tile</th>
                <th>Cost ({{ env('DEFAULT_CURRENCY') }})</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="framesTableBody">

        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<script type="text/javascript">
    $(document).ready(function() {


        $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.getAllTiles') }}', // The route to fetch the data
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'tileNumber',
                    name: 'numberOfTile'
                },
                {
                    data: 'cost',
                    name: 'cost'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $(document).on('click', '.edit-tile-btn', function() {
            var tileUrl = $(this).data('url');

            $.ajax({
                url: `${tileUrl}`, // Adjust URL based on your routing
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.status === 1 && response.data) {
                        // Populate the modal with the data
                        $('#tileId').val(response.data.id);
                        $('#tileNumber').val(response.data.tileNumber);
                        $('#tileCost').val(response.data.cost);
                    } else {
                        console.log('Error: No frame data found.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error); // Handle any error
                }
            });
        });

        $(document).on('click', '.delete-tile-btn', function() {
            var tileUrl = $(this).data('url');
            var confirmation = confirm('Are you sure you want to delete this tile?');
            if (confirmation) {
                $.ajax({
                    url: `${tileUrl}`, // Adjust URL based on your routing
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.status === 1 && response.data) {
                            toastr.success('Successfully deleted tile.');

                            $('#dataTable').DataTable().ajax.reload();
                        } else {
                            console.log('Error: No frame data found.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error: ' + error); // Handle any error
                    }
                });
            } else{
                return false;
            }
        });

        $('#tileForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#tileBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.tilesSubmit') }}",
                type: 'POST',
                data: new FormData($('#tileForm')[0]),
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    // Handle success response
                    if (response.status === 1) {
                        $('#tileBtn').prop('disabled',
                            false);
                        toastr.success('Successfully created new tile.');

                        $('#tileModal').modal('hide');

                        setTimeout(function() {
                            window.location.href =
                                "{{ route('admin.tilesPage') }}";
                        }, 1000);

                    } else {
                        $('#tileBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.error('failed to create new tile.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#tileBtn').prop('disabled',
                        false);

                    let response = xhr.responseJSON;


                    let errors = response.errors;
                    if (response.errors) {
                        for (let field in errors) {
                            $('#tileForm').find('input[name="' + field + '"]')
                                .parent() // Select the parent of the input
                                .after('<div class="error-message text-danger mt-1">' +
                                    errors[field].join(', ') +
                                    '</div>');
                        }
                    }
                }
            });
        });

        $('#updateTileForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#updateTileBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.updateTile') }}",
                type: 'POST',
                data: new FormData($('#updateTileForm')[0]),
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    // Handle success response
                    if (response.status === 1) {
                        $('#updateTileBtn').prop('disabled',
                            false);
                        toastr.success('Successfully updated tile.');

                        $('#tileUpdateModal').modal('hide');

                        setTimeout(function() {
                            window.location.href =
                                "{{ route('admin.tilesPage') }}";
                        }, 1000);

                    } else {
                        $('#updateTileBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.error('failed to update tile.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updateTileBtn').prop('disabled',
                        false);

                    let response = xhr.responseJSON;
                    let errors = response.errors;
                    if (response.errors) {
                        for (let field in errors) {
                            $('#updateTileForm').find('input[name="' + field + '"]')
                                .parent() // Select the parent of the input
                                .after('<div class="error-message text-danger mt-1">' +
                                    errors[field].join(', ') +
                                    '</div>');
                        }
                    }
                }
            });
        });
    });
</script>

@include('admin.include.footer')
