@include('admin.include.header')

<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Country Amount List</h3>

    </div>
</div>
<div class="container">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#shippingModal">Add Country Amount</button>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="shippingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Country Amount</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="shippingForm" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Form inside the modal -->
                    <div class="form-group ">
                        <label for="tileNumber">Select Country</label>
                        <select class="form-control" name="country" required>
                            <option value="" >Select country</option>
                            @if(!empty($countries))
                                @foreach($countries as $country)
                                    <option value="{{$country->id}}">{{$country->name}}</option>
                                @endforeach                            
                            @endif
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileCost">Cost ({{ env('DEFAULT_CURRENCY') }})</label>
                        <input type="number" class="form-control" step="0.01" name="cost" placeholder="Enter shipping cost" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileCost">VAT (%)</label>
                        <input type="number" class="form-control" min="0" max="100" name="vat" placeholder="Enter vat cost" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="shippingBtn">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateShippingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Upate Country Amount</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateShippingAmountForm" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="form-group ">
                        <label for="tileNumber">Select Country</label>
                        <select class="form-control" id="edit_country" name="country" disabled>
                            <option value=" ">Select country</option>
                            @if(!empty($updatedCountries))
                                @foreach($updatedCountries as $country)
                                    <option value="{{$country->id}}">{{$country->name}}</option>
                                @endforeach                            
                            @endif
                        </select>
                        <input type="hidden" name="country" id="edit_country_id"  />
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileCost">Cost ({{ env('DEFAULT_CURRENCY') }})</label>
                        <input type="number" class="form-control" step="0.01" name="cost" id="edit_cost" placeholder="Enter shipping cost" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="tileCost">VAT (%)</label>
                        <input type="number" class="form-control" step="0.01" name="vat" id="edit_vat" placeholder="Enter shipping cost" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updateShippingAmountBtn">Update</button>
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
                <th>Country</th>
                <th>Cost ({{ env('DEFAULT_CURRENCY') }})</th>
                <th>VAT (%)</th>
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
            ajax: '{{ route('admin.getCountryShippingAmount') }}', // The route to fetch the data
            columns: [
                { data: 'DT_RowIndex', name: '#', orderable: false, searchable: false },
                {
                    data: 'name',
                    name: 'country'
                },
                {
                    data: 'shipping_amount',
                    name: 'cost'
                },
                {
                    data: 'vat',
                    name: 'vat'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $(document).on('click', '.edit-shipping-amount-btn', function() {
            var shippingUrl = $(this).data('url');

            $.ajax({
                url: `${shippingUrl}`, // Adjust URL based on your routing
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.status === 1 && response.data) {
                        var countryId = response.data.id;

                        $("#edit_country option").removeAttr("selected"); // Remove previously selected option
                        $("#edit_country option[value='" + countryId + "']").attr("selected", "selected");
    
                        $('#edit_cost').val(response.data.shipping_amount);
                        $('#edit_vat').val(response.data.vat);
                        $('#edit_country_id').val(response.data.id);
                    } else {
                        console.log('Error: No data found.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error); // Handle any error
                }
            });
        });

        $(document).on('click', '.delete-shipping-amount-btn', function() {
            var tileUrl = $(this).data('url');
            var confirmation = confirm('Are you sure you want to delete this shipping amount?');
            if (confirmation) {
                $.ajax({
                    url: `${tileUrl}`, // Adjust URL based on your routing
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.status === 1 && response.data) {
                            toastr.success('Successfully deleted ahipping amount.');
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('admin.countryShippingAmountList') }}";
                            }, 1000);
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

        $('#shippingForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#shippingBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.addShippingAmount') }}",
                type: 'POST',
                data: new FormData($('#shippingForm')[0]),
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
                        $('#shippingBtn').prop('disabled',
                            false);
                        toastr.success('Successfully added shipping amount.');

                        $('#shippingModal').modal('hide');

                        setTimeout(function() {
                            window.location.href =
                                "{{ route('admin.countryShippingAmountList') }}";
                        }, 1000);

                    } else {
                        $('#tileBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.error('failed to added shipping amount.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#shippingBtn').prop('disabled',
                        false);

                    let response = xhr.responseJSON;


                    let errors = response.errors;
                    if (response.errors) {
                        for (let field in errors) {
                            $('#shippingForm').find('input[name="' + field + '"]')
                                .parent() // Select the parent of the input
                                .after('<div class="error-message text-danger mt-1">' +
                                    errors[field].join(', ') +
                                    '</div>');
                        }
                    }
                }
            });
        });

        $('#updateShippingAmountForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#updateShippingAmountBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.addShippingAmount') }}",
                type: 'POST',
                data: new FormData($('#updateShippingAmountForm')[0]),
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
                        $('#updateShippingAmountBtn').prop('disabled',
                            false);
                        toastr.success('Successfully updated shipping amount .');

                        $('#updateShippingModal').modal('hide');

                        setTimeout(function() {
                            window.location.href =
                                "{{ route('admin.countryShippingAmountList') }}";
                        }, 1000);

                    } else {
                        $('#updateShippingAmountBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.error('failed to update shipping amount.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updateShippingAmountBtn').prop('disabled',
                        false);

                    let response = xhr.responseJSON;
                    let errors = response.errors;
                    if (response.errors) {
                        for (let field in errors) {
                            $('#updateShippingAmountForm').find('input[name="' + field + '"]')
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
