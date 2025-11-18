@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Collection Management</h3>

    </div>
</div>
<div class="mb-2">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#tileModal">Add Collection</button>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="tileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Collection</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="collectionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter name" required>

                    </div>

                    <div class="form-group mt-3">
                        <label for="Logo">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="collectionBtn">Add Collection</button>
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
                <h5 class="modal-title" id="exampleModalLabel">Update Collection</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateTileForm" method="POST">
                @csrf
                <input type="hidden" name="id" id="collectionId">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="product">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Enter name" required>

                    </div>

                    <div class="form-group mt-3">
                        <label for="description">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*">
                        <div class="mt-2">
                            <img id="logoPreview" src="" alt="Logo Preview" style="max-width: 150px; display: none;">
                        </div>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updatecollectionBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>






<div class="container">
    <table id="usersTable" class="table table-striped">
        <thead>
            <tr>
                <th>S.NO</th>
                <th>Name</th>
                <th>Logo</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $collection)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $collection->name }}</td>
                <td>
                    @if($collection->logo)
                    <img src="{{ asset('storage/' . $collection->logo) }}" alt="Logo" width="60">
                    @else
                    N/A
                    @endif
                </td>
                <td>
                    <button class="btn btn-danger delete-btn" data-id="{{ $collection->id }}">Delete</button>
                    <button type="button" class="btn btn-primary show-data" data-toggle="modal" data-plan-id="{{ $collection->id }}">
                        Edit
                    </button>
                </td>
            </tr>
            @endforeach

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



        $('#usersTable').DataTable({
            "paging": true,
            "searching": true,
            "lengthChange": true,
            "pageLength": 10,
            "language": {
                "search": "Search Collection:",
                "lengthMenu": "Display _MENU_ Collection per page"
            }
        });

        $('.show-data').on('click', function() {

            console.log('Fetching plan data...');
            var planId = $(this).data('plan-id');
            var url = '{{ route("admin.getSingleCollection", ":id") }}';
            url = url.replace(':id', planId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        $('#collectionId').val(response.data.id);
                        $('#name').val(response.data.name);
                        if (response.data.logo) {
                            let logoPath = "{{ asset('storage') }}/" + response.data.logo;
                            $('#logoPreview').attr('src', logoPath).show();
                        } else {
                            $('#logoPreview').hide();
                        }
                        $('#tileUpdateModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch data'
                    });
                }
            });

        });




        $('.delete-btn').on('click', function() {
            var button = $(this);
            var itemId = button.data('id');
            var itemName = button.data('name') || 'Collection';

            Swal.fire({
                title: 'Are you sure?',
                text: `Do you really want to delete this ${itemName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true).text('Deleting...');

                    $.ajax({
                        url: '{{ route("admin.deleteCollection", ":id") }}'.replace(':id', itemId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response) {
                                toastr.success('Successfully deleted Collection.');

                                button.closest('tr').remove();
                              
                            window.location.href = "{{ route('admin.collection') }}";
                        
                            } else {
                                console.log('Error: No Collection data found.');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('An error occurred while deleting the item.');
                        },
                        complete: function() {
                            button.prop('disabled', false).text('Delete');
                        }
                    });
                }
            });
        });

        $('#collectionForm').on('submit', function(e) {
            e.preventDefault();

            // Remove previous error messages
            $('.error-message').remove();

            // Disable submit button
            $('#collectionBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.storeCollection') }}",
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
                    $('#collectionBtn').prop('disabled', false);
                    if (response.status === 1) {
                        toastr.success('Successfully created new Collection.');
                        $('#tileModal').modal('hide');

                        setTimeout(function() {
                            window.location.href = "{{ route('admin.collection') }}";
                        }, 1000);
                    } else {
                        toastr.error('Failed to create Collection.');
                    }
                },
                error: function(xhr) {
                    $('#collectionBtn').prop('disabled', false);

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;

                        // Loop through each error and display message next to relevant input/select/textarea
                        for (let field in errors) {
                            let fieldElement = $('#collectionForm').find(`[name="${field}"]`);

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

            $('#updatecollectionBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.updateCollection') }}",
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
                        $('#updatecollectionBtn').prop('disabled', false);
                        toastr.success('Successfully updated Collection.');

                        $('#tileUpdateModal').modal('hide');

                        setTimeout(function() {
                            window.location.href = "{{ route('admin.collection') }}";
                        }, 1000);

                    } else {
                        $('#updatecollectionBtn').prop('disabled', false);
                        toastr.error('Failed to update Collection.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updatecollectionBtn').prop('disabled', false);

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