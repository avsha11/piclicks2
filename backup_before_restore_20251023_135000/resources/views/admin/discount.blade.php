@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Discount Management</h3>

    </div>
</div>
<div class="mb-2">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#tileModal">Add Discount</button>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="tileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Discount</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="discountForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product">Product Name</label>
                        <select class="form-control" name="price_management_id" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Enter description"></textarea>
                    </div>

                    <div class="form-group mt-3">
                        <label for="discount_percent">Discount (%)</label>
                        <input type="number" step="0.01" class="form-control" name="discount_percent" placeholder="Enter discount percentage" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="tile_from">Tile From</label>
                        <input type="number" class="form-control" name="tile_from" placeholder="Enter starting tile number" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="tile_to">Tile To</label>
                        <input type="number" class="form-control" name="tile_to" placeholder="Enter ending tile number" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="discountBtn">Add Discount</button>
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
                <h5 class="modal-title" id="exampleModalLabel">Update Discount</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateTileForm" method="POST">
                @csrf
                <input type="hidden" name="discountId" id="discountId">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="priceManagementId">Product Name</label>
                        <select class="form-control" id="priceManagementId" name="priceManagementId" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" placeholder="Enter description"></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label for="discountPercent">Discount (%)</label>
                        <input type="number" step="0.01" class="form-control" id="discountPercent" name="discountPercent" placeholder="Enter discount percent">
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileFrom">Tile From</label>
                        <input type="number" class="form-control" id="tileFrom" name="tileFrom" placeholder="Enter tile from">
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileTo">Tile To</label>
                        <input type="number" class="form-control" id="tileTo" name="tileTo" placeholder="Enter tile to">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updatediscountBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>






<div class="container">
    <table id="dataTable" class="table table-striped">
        <thead>
            <tr>
                <th>S.NO</th>
                <th>Product Name</th>
                <th>Discount</th>
                <th>Tile From</th>
                <th>Tile To</th>
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
    ajax: '{{ route("admin.getdiscount") }}',
    columns: [
        {
            data: 'DT_RowIndex', // <-- This will be your serial number
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
        },
        {
            data: 'product_name',
            name: 'product_name'
        },
        {
            data: 'discount_percent',
            name: 'discount_percent'
        },
        {
            data: 'tile_from',
            name: 'tile_from'
        },
        {
            data: 'tile_to',
            name: 'tile_to'
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
                url: tileUrl,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (response && response.data) {
                        $('#discountId').val(response.data.id);

                        // Set the dropdown to the product ID
                        $('#priceManagementId').val(response.data.price_management_id);

                        $('#description').val(response.data.description);
                        $('#discountPercent').val(response.data.discount_percent);
                        $('#tileFrom').val(response.data.tile_from);
                        $('#tileTo').val(response.data.tile_to);
                        $('#tileUpdateModal').modal('show');
                    } else {
                        console.log('Error: No discount data found.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });


        $(document).on('click', '.delete-tile-btn', function() {
            var tileUrl = $(this).data('url');
            var confirmation = confirm('Are you sure you want to delete this Discount?');
            if (confirmation) {
                $.ajax({
                    url: `${tileUrl}`, // Adjust URL based on your routing
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response) {
                            toastr.success('Successfully deleted Discount.');

                            $('#dataTable').DataTable().ajax.reload();
                        } else {
                            console.log('Error: No Discount data found.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error: ' + error); // Handle any error
                    }
                });
            } else {
                return false;
            }
        });

        $('#discountForm').on('submit', function(e) {
            e.preventDefault();

            // Remove previous error messages
            $('.error-message').remove();

            // Disable submit button
            $('#discountBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.storeDiscount') }}",
                type: 'POST',
                data: new FormData(this),
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#discountBtn').prop('disabled', false);
                    if (response.status === 1) {
                        toastr.success('Successfully created new discount.');
                        $('#tileModal').modal('hide');

                        setTimeout(function() {
                            window.location.href = "{{ route('admin.discount') }}";
                        }, 1000);
                    } else {
                        toastr.error('Failed to create discount.');
                    }
                },
                error: function(xhr) {
                    $('#discountBtn').prop('disabled', false);

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;

                        // Loop through each error and display message next to relevant input/select/textarea
                        for (let field in errors) {
                            let fieldElement = $('#discountForm').find(`[name="${field}"]`);

                            if (fieldElement.length) {
                                // Remove any old error for this field before adding
                                fieldElement.next('.error-message').remove();

                                // Insert error message after the field
                                fieldElement.after(
                                    `<div class="error-message text-danger mt-1">${errors[field].join(', ')}</div>`
                                );
                            }
                        }
                    } else {
                        toastr.error('An unexpected error occurred.');
                    }
                }
            });
        });


        $('#updateTileForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#updatediscountBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.updateDiscount') }}",
                type: 'POST',
                data: new FormData($('#updateTileForm')[0]),
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
                },
                success: function(response) {
                    if (response.status === 1) {
                        $('#updatediscountBtn').prop('disabled', false);
                        toastr.success('Successfully updated discount.');

                        $('#tileUpdateModal').modal('hide');

                        setTimeout(function() {
                            window.location.href = "{{ route('admin.discount') }}";
                        }, 1000);

                    } else {
                        $('#updatediscountBtn').prop('disabled', false);
                        toastr.error('Failed to update discount.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updatediscountBtn').prop('disabled', false);

                    let response = xhr.responseJSON;
                    if (response && response.errors) {
                        for (let field in response.errors) {
                            let inputElem = $('#updateTileForm').find('[name="' + field + '"]');
                            if (inputElem.length) {
                                inputElem.parent()
                                    .append('<div class="error-message text-danger mt-1">' + response.errors[field].join(', ') + '</div>');
                            }
                        }
                    }
                }
            });
        });

    });
</script>

@include('admin.include.footer')