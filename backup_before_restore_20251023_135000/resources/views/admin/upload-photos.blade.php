@include('admin.include.header')
<style>
    .transparent_header {

        border-bottom: 1px solid #e1e1e1;

    }



    #v-pills-tab .nav-link {

        max-width: 180px;



    }



    #v-pills-tab .nav-link.active {

        max-width: 180px;

    }



    .btn-primary,

    .nav-pills .nav-link.active,

    .nav-pills .show>.nav-link {

        background: rgb(38 94 145);

    }



    .section_title h2 {

        color: rgb(38 94 145);

    }



    .drag-file-area {

        margin: 0;

    }



    .file-item {

        position: relative;

        width: 100%;

        /* height: 220px; */

        padding: 5px !important;

        margin: 0px !important;

    }



    .file-item img {

        width: 100% !important;

        height: 100% !important;

        object-fit: cover;

        object-position: top;

        aspect-ratio: 1 / 1;

    }



    .file-item:hover .delete-btn {

        opacity: 1;

        transition: 0.5s ease-in;

    }



    .file-item .delete-btn {

        position: absolute;

        width: calc(100% - 10px);

        height: calc(100% - 10px);

        left: 5px;

        top: 5px;

        opacity: 0;

        transition: 0.5s ease-in;

        background: #00000050;

        margin: 0px !important;

        border-radius: 0px;

        padding: 0px;

        border: 0px;

    }



    div#fileList {

        display: grid;

        grid-template-columns: repeat(4, 1fr);

        margin-top: 25px;

    }
</style>
<div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <h3 class="mt-0 fw-bold">Add Art Gallery</h3>
    </div>
</div>
<section class="section_collage">

    <div class="container">



        <div class="tab-section-upload">
            @if (session('error_message'))
            <div id="error-alert" class="alert alert-danger">
                {{ session('error_message') }}
            </div>
            @endif
            <div class="d-flex flex-wrap flex-md-nowrap align-items-start">
                <div class="tab-content col-sm-9" id="v-pills-tabContent">

                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"

                        aria-labelledby="v-pills-home-tab">

                        <form class="form-container" enctype='multipart/form-data'>

                            <div class="upload-files-container">
                                <div class="row">
                                    <div class="col-3 mt-4">
                                        <label for="tag_id">Tags</label>
                                        <select name="tag_id[]" class="form-control select-tags" multiple required>
                                            @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                         @error('tag_id')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-3 mt-3">
                                        <label for="collection_id">Collection</label>
                                        <select name="collection_id" class="form-control" required>
                                            @foreach($collections as $collection)
                                            <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('collection_id')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-3 mt-3">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                        @error('title')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-3 mt-3">
                                        <label for="short_description">Short Description</label>
                                        <textarea name="short_description" class="form-control" required></textarea>
                                        @error('short_description')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-3 mt-3">
                                        <label for="designer_name">Artist Name</label>
                                        <input type="text" name="designer_name" class="form-control" required>
                                        @error('designer_name')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-3 mt-3">
                                        <label for="amount">Amount(US$)</label>
                                        <input type="number" name="amount" class="form-control" step="0.01" required>
                                        @error('amount')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-9 ms-auto">

                                    <div class="section_title text-center">

                                        <!-- <h2>Upload Photos</h2>

                <p>When unknow printer took a gallery of type and scramblted it to make a type specimen book</p> -->

                                        @if (session('message_error'))

                                        <div class="alert alert-danger">

                                            {{ session('message_error') }}

                                        </div>

                                        @endif



                                    </div>

                                </div>
                                <div class="drag-file-area mt-3" style="padding: 20px;">

                                    <span class="material-icons-outlined upload-icon"><i

                                            class="fa-solid fa-arrow-up-from-bracket fa-fw"></i></span>

                                    <!--h3 class="dynamic-message"> Drag & drop any file here </h3-->

                                    <label class="label  mt-3" style="display:inherit"><!-- or --><span

                                            class="browse-files"> <input type="file" class="default-file-input"

                                                multiple> <span class="browse-files-text">Get started now</span>

                                            <span>from device</span> </span> </label>

                                </div>

                                <span class="cannot-upload-message"> <span class="material-icons-outlined"></span>

                                    Please select a file first <span

                                        class="material-icons-outlined cancel-alert-button">cancel</span> </span>

                                <div class="file-block">

                                    <div class="file-info"> <span class="material-icons-outlined file-icon"></span>

                                        <span class="file-name"> </span> | <span class="file-size"> </span>

                                    </div>

                                    <span class="material-icons remove-file-icon">delete</span>

                                    <div class="progress-bar"> </div>

                                </div>

                                <div id="fileList" class="row" style="width: 100%;"></div>





                                <!--button type="button" class="upload-button btn-primary"> Upload </button-->

                                <button type="button"  class="upload-button btn-primary" id="uploadBtn"> Create

                                </button>



                                <div class="progress">

                                    <div id="progressBar" class="progress-bar" role="progressbar"

                                        style="width: 0%;height: 14px;" aria-valuenow="0" aria-valuemin="0"

                                        aria-valuemax="100">0%</div>

                                </div>

                                <div id="progressInfo"></div>



                            </div>

                        </form>

                    </div>

                    <!--div class="tab-pane fade" id="Galleryimg" role="tabpanel" aria-labelledby="Galleryimg-tab">...</div-->

                    <div class="tab-pane fade" id="Dropbox" role="tabpanel" aria-labelledby="Dropbox-tab">

                        <div class="box-inpute">

                            <h1><i class="fa-brands fa-dropbox fa-fw"></i> </h1>

                            <h3>Please Enter your dropbox url</h3>

                            <input class="form-control" placeholder="Enter Dropbox Link">

                            <a href="uploaded.php" class="upload-button btn-primary"> Upload </a>

                        </div>



                    </div>

                    <div class="tab-pane fade" id="iCloudimg" role="tabpanel" aria-labelledby="iCloudimg-tab">

                        <div class="box-inpute">

                            <h1><i class="fa-solid fa-cloud fa-fw"></i> </h1>

                            <h3>Please Enter your iCloud url</h3>

                            <input class="form-control" placeholder="Enter iCloud Link">

                            <a href="uploaded.php" class="upload-button btn-primary"> Upload </a>

                        </div>

                    </div>

                    <div class="tab-pane fade" id="googleimg" role="tabpanel" aria-labelledby="googleimg-tab">

                        <div class="box-inpute">

                            <h1><i class="fa-brands fa-google-drive fa-fw"></i> </h1>

                            <h3>Please Enter your Google Cloud url</h3>

                            <input class="form-control" placeholder="Enter Google Cloud Link">

                            <a href="uploaded.php" class="upload-button btn-primary"> Upload </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>







    </div>
</section>

<!-- pre loder model -->

<div class="modal fade" id="preloaderModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body text-center py-5">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <p class="fs-4 my-3" id="preloaderModalText"></p>
            </div>
        </div>
    </div>
</div>

@include('admin.include.footer')

<script>
    $(document).ready(function() {
        $('.select-tags').select2({
            placeholder: "Select tags",
            width: '100%',
            closeOnSelect: false
        });
    });
    setTimeout(function() {
        let alertBox = document.getElementById('error-alert');
        if (alertBox) {
            alertBox.style.display = 'none';
        }
    }, 5000);

    let empty_msg = "{{ session('images_empty_error') ? session('images_empty_error') : '' }}";

    if (empty_msg != '') {

        Swal.fire({

            icon: 'warning',

            text: empty_msg,

            toast: true,

            position: "top-end",

            showConfirmButton: false,

            timer: 3000,

        });

    }



    var fileList = [];



    // Drag over and drop events

    $('.drag-file-area').on('dragover', function(e) {

        e.preventDefault();

        $(this).addClass('dragover');

    }).on('dragleave', function(e) {

        e.preventDefault();

        $(this).removeClass('dragover');

    }).on('drop', function(e) {

        e.preventDefault();

        $(this).removeClass('dragover');

        var files = e.originalEvent.dataTransfer.files;

        handleFiles(files);

    });



    // File input change event

    $('.default-file-input').on('change', function(e) {

        var files = e.target.files;

        handleFiles(files);

    });



    let filescount = 0;

    // Handle selected files

    function handleFiles(files) {

        for (var i = 0; i < files.length; i++) {

            var file = files[i];

            // Check if file type is valid

            if (file.type.match('image/jpeg') || file.type.match('image/jpg') || file.type.match('image/png') || file

                .type.match('image/jfif')) {

                let fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);



                const currentCount = filescount;

                fileList.push({

                    file: file,

                    id: "file" + currentCount

                });

                // Display thumbnail or preview

                var reader = new FileReader();

                reader.onload = function(e) {

                    $('#fileList').append(`<div class="file-item col-sm-4 col-md-3 my-2" data-index="${currentCount}">

                    <img src="${e.target.result}" alt="${file.name}" style="width: 100px; height: 100px;">

                    <button type="button" class="delete-btn btn btn-danger btn-sm mx-3" data-index="${currentCount}">

                        <i class="fa-regular fs-2 fa-trash-can"></i><br>

                        ${fileSizeMB} mb

                    </button>

                </div>`);

                }

                reader.readAsDataURL(file);

                filescount++

            } else {

                // Invalid file type

                // alert('Invalid file type: ' + file.name);

                Swal.fire({

                    icon: 'error',

                    text: "Invalid file type in " + file.name + ", only jpeg, jpg, png, jfif are allowed.",

                    toast: true,

                    position: "top-end",

                    showConfirmButton: false,

                    timer: 3000,

                });

            }

        }

    }



    $('#fileList').on('click', '.delete-btn', function() {

        var fileId = $(this).data('index');

        fileList = fileList.filter(item => item.id !== "file" + fileId);

        $(this).closest('.file-item').remove();

    });



    // Upload button click event

    $('#uploadBtn').on('click', function() {
        $('.text-danger').remove();
        $('.is-invalid').removeClass('is-invalid');

        let hasError = false;

        let collectionId = $('select[name="collection_id"]').val();
        let title = $('input[name="title"]').val().trim();
        let artistName = $('input[name="designer_name"]').val().trim();
        let amount = $('input[name="amount"]').val().trim();
        let selectedTags = $('select[name="tag_id[]"]').val();

        if (!collectionId) {
            $('select[name="collection_id"]').addClass('is-invalid')
                .after('<small class="text-danger">Please select a collection.</small>');
            hasError = true;
        }

        if (!title) {
            $('input[name="title"]').addClass('is-invalid')
                .after('<small class="text-danger">Title is required.</small>');
            hasError = true;
        }

        if (!artistName) {
            $('input[name="designer_name"]').addClass('is-invalid')
                .after('<small class="text-danger">Artist name is required.</small>');
            hasError = true;
        }

        if (!amount || isNaN(amount) || parseFloat(amount) <= 0) {
            $('input[name="amount"]').addClass('is-invalid')
                .after('<small class="text-danger">Please enter a valid amount.</small>');
            hasError = true;
        }

        if (!selectedTags || selectedTags.length === 0) {
            $('select[name="tag_id[]"]').addClass('is-invalid')
                .after('<small class="text-danger">At least one tag must be selected.</small>');
            hasError = true;
        }

        if (fileList.length < 4) {
            $('#fileList').after('<small class="text-danger">Minimum 4 images needed to continue.</small>');
            hasError = true;
        }

        if (hasError) return;
        if (fileList.length < 4) {

            Swal.fire({

                icon: 'warning',

                text: "Minimum 4 images needed to continue",

                toast: true,

                position: "top-end",

                showConfirmButton: false,

                timer: 3000,

            });



        } else {

            let files = fileList;



            $(this).prop('disabled', true);

            $("#preloaderModal").modal('show');

            $("#preloaderModalText").html('');



            var formData = new FormData();

            formData.append('user_type', 'admin');

            for (var i = 0; i < files.length; i++) {

                formData.append('files[]', files[i].file); // Access the file object directly

            }
            formData.append('collection_id', $('select[name="collection_id"]').val());
            formData.append('title', $('input[name="title"]').val());
            formData.append('short_description', $('textarea[name="short_description"]').val());
            formData.append('designer_name', $('input[name="designer_name"]').val());
            formData.append('amount', $('input[name="amount"]').val());
            let selectedTags = $('select[name="tag_id[]"]').val(); // returns array
            if (selectedTags) {
                selectedTags.forEach(tagId => {
                    formData.append('tag_id[]', tagId); // append each tag_id
                });
            }
            // formData.append('_token', "{{ csrf_token() }}");

            $('#uploadBtn').prop('disabled', true);



            // Create new XMLHttpRequest for progress tracking

            var xhr = new XMLHttpRequest();



            let startTime = Date.now(); // Store upload start time

            let lastLoaded = 0;



            // Track progress

            xhr.upload.addEventListener('progress', function(e) {

                if (e.lengthComputable) {

                    let currentTime = Date.now(); // Get current timestamp

                    let elapsedTime = (currentTime - startTime) / 1000; // Convert to seconds

                    var percentComplete = Math.round((e.loaded / e.total) * 100);

                    var mbUploaded = (e.loaded / (1024 * 1024)).toFixed(2);

                    var mbTotal = (e.total / (1024 * 1024)).toFixed(2);

                    let bytesUploaded = e.loaded - lastLoaded; // Bytes uploaded since last event

                    let timeInterval = (currentTime - startTime) / 1000; // Seconds elapsed since start

                    let speedMbps = ((bytesUploaded * 8) / (1024 * 1024) / timeInterval).toFixed(

                        2); // Convert to Mbps

                    lastLoaded = e.loaded;



                    $("#preloaderModalText").html(

                        `Uploaded: ${mbUploaded} / ${mbTotal} MB <br>` +

                        `Uploading: ${percentComplete}% @ ${speedMbps} mbps`

                    );

                }



            });



            xhr.onreadystatechange = function() {

                if (xhr.readyState == 4) {

                    if (xhr.status == 201) {

                        console.log('xhr resp ', xhr.responseText);

                        var res = JSON.parse(xhr.responseText);

                        if (res.status === 1) {

                            $('#fileList').empty();

                            fileList = [];

                            window.location.href = res.data.redirect_url;

                        } else {

                            $('#uploadBtn').prop('disabled', false);

                            $("#preloaderModal").modal('hide');

                            Swal.fire({

                                icon: 'warning',

                                title: "Alert",

                                text: res.message,

                            });

                        }

                    } else {

                        console.log('xhr resp11 ', xhr);

                        $('#uploadBtn').prop('disabled', false);

                        $("#preloaderModal").modal('hide');

                        Swal.fire({

                            icon: 'warning',

                            title: "Alert",

                            text: "Something went wrong11",

                        });

                    }

                }

            };



            // Start upload

            xhr.open('POST', "{{ route('front.save-upload-photos') }}", true);

            xhr.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}");

            xhr.send(formData);

        }

    });
</script>