@include('admin.include.header')



<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Frame Management</h3>
    </div>
</div>




<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Frame</h5>
                <button type="button" class="btn btn-danger close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateFrameForm" method="POST">
            @csrf
            <div class="modal-body">
                <!-- Form inside the modal -->

                <input type="text" id="frameId" name="frameId" hidden>
                    <div class="form-group ">
                        <label for="frameName">Name</label>
                        <input type="text" class="form-control" name="frameName" id="frameName" placeholder="Enter frame name">
                    </div>
                    <div class="form-group mt-3">
                        <label for="frameCost">Cost</label>
                        <input type="number" class="form-control" name="frameCost" id="frameCost" placeholder="Enter frame cost">
                    </div>
                    <div class="form-group mt-3">
                        <label for="frameStatus">Status</label>
                        <select name="frameStatus" class="form-control" id="frameStatus">
                            <option value="1">Active</option>
                            <option value="0">Deactive</option>
                        </select>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary"  id="updateFrameBtn">Update</button>
            </div>
           </form>
        </div>
    </div>
</div>


<div class="container mt-5">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Cost</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="framesTableBody">

        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        $.ajax({
            url: "{{ route('admin.getAllFrames') }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                if (response && response.status === 1 && response.data.length > 0) {
                    var tableBody = $('#framesTableBody');

                    response.data.forEach(function(frame, index) {
                        var row = `<tr>
                    <td>${index + 1}</td>
                    <td>${frame.name}</td>
                    <td>${frame.cost}</td>
                    <td>${frame.status ? 'Active' : 'Deactive' }</td>
                    <td>
                        <a href="javascript:void(0);" data-id="${frame.id}" data-toggle="modal" data-target="#exampleModal" class="btn btn-primary edit-frame-btn">Update</a>
                    </td>

                    </tr>`;
                        tableBody.append(row);
                    });
                } else {
                    console.log('No frames found or error in response.');
                }
            },
            error: function(xhr, status, error) {

                console.log('Error: ' + error); // Handle the error
            }
        });

        $(document).on('click', '.edit-frame-btn', function() {
            var frameId = $(this).data('id');

            // Make an AJAX request to fetch the frame details
            //url: `/piclicks/admin/getsingle-frame/${frameId}`
            $.ajax({
                url: `/piclicks/admin/getsingle-frame/${frameId}`, // Adjust URL based on your routing
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.status === 1 && response.data) {
                        // Populate the modal with the data
                        $('#frameId').val(response.data.id);
                        $('#frameName').val(response.data.name);
                        $('#frameCost').val(response.data.cost);
                        $('#frameStatus').val(response.data.status ? 1 : 0);
                        // Set any other fields you need for the modal
                    } else {
                        console.log('Error: No frame data found.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error); // Handle any error
                }
            });
        });

        $('#updateFrameForm').on('submit', function(e) {
            e.preventDefault();

            $('.error-message').remove();

            $('#updateFrameBtn').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.updateFrame') }}",
                type: 'POST',
                data: new FormData($('#updateFrameForm')[0]),
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
                        $('#updateFrameBtn').prop('disabled',
                            false);
                        toastr.success('Admin profile successfully update.');

                        setTimeout(function() {
                            window.location.href =
                            "{{ route('admin.framesPage') }}";
                        }, 2000);

                    } else {
                        $('#updateFrameBtn').prop('disabled',
                            false); // Re-enable the submit button

                        toastr.error('Admin Profile failed to update.');
                    }
                },
                error: function(xhr, status, error) {
                    $('#updateFrameBtn').prop('disabled',
                        false);

                    let response = xhr.responseJSON;
                    if (response && response.message) {
                        toastr.error(response.message); // Display general error message
                    } else {
                        toastr.error('Something went wrong.'); // Fallback error message
                    }


                }
            });
        });
    });
</script>




@include('admin.include.footer')
