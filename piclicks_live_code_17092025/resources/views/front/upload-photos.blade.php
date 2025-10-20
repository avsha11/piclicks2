@extends('front.layout.front-layout')
{{-- @push('title', 'Home') --}}
@section('content')
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
    

        @media screen and (min-width: 768px) and (max-width: 1200px) {
    section.section_collage {
        height: 72vh;
    }
}
    </style>
    <!-- https://codepen.io/OptimalLearner/pen/QWMGyZj -->
    <section class="section_collage">
        <div class="container">
            <div class="col-md-9 ms-auto">
                <div class="section_title text-center">
                    <h2>Upload Photos</h2>
                    <p>When unknown printer took a gallery of type and scrambled it to make a type specimen book</p>
                    @if (session('message_error'))
                        <div class="alert alert-danger">
                            {{ session('message_error') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="tab-section-upload">
                <div class="d-flex flex-wrap flex-md-nowrap align-items-start row">
                    <div class="nav flex-column nav-pills upload-nav-tab col-md-4 col-lg-3" id="v-pills-tab" role="tablist"
                        aria-orientation="vertical">
                        <a class="nav-link active" id="pc-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home"
                            type="button" role="tab" aria-controls="v-pills-home" aria-selected="true"><i
                                class="fa-solid fa-desktop fa-fw"></i> Your PC photos</a>
                        <!--a class="nav-link" id="Galleryimg-tab" data-bs-toggle="pill" data-bs-target="#Galleryimg" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">Gallery</a-->
                        <a class="nav-link" id="Dropbox-tab" data-bs-toggle="pill" data-bs-target="#Dropbox" type="button"
                            role="tab" aria-controls="v-pills-messages" aria-selected="false"><i
                                class="fa-brands fa-dropbox fa-fw"></i> Dropbox</a>
                        <a class="nav-link" id="iCloudimg-tab" data-bs-toggle="pill" data-bs-target="#iCloudimg"
                            type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i
                                class="fa-solid fa-cloud fa-fw"></i> iCloud</a>
                        <a class="nav-link" id="googleimg-tab" data-bs-toggle="pill" data-bs-target="#googleimg"
                            type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false"><i
                                class="fa-brands fa-google-drive fa-fw"></i> Google Cloud</a>
                    </div>
                    <div class="tab-content col-md-8 col-lg-9" id="v-pills-tabContent">
                        <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                            aria-labelledby="v-pills-home-tab">
                            <form class="form-container" enctype='multipart/form-data'>
                                <div class="upload-files-container">
                                    <div class="drag-file-area" style="padding: 20px;">
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
                                    <button type="button" class="upload-button btn-primary" id="uploadBtn"> Create
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
@endsection
@push('js')
    <script>
        let empty_msg = "{{ session('images_empty_error') ? session('images_empty_error') : '' }}";
        let error_msg = "{{ session('error') ? session('error') : '' }}";
        
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
        
        if (error_msg != '') {
            Swal.fire({
                icon: 'error',
                title: 'Preview Error',
                text: error_msg,
                confirmButtonText: 'OK'
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
                formData.append('user_type', 'user');
                for (var i = 0; i < files.length; i++) {
                    formData.append('files[]', files[i].file); // Access the file object directly
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
                        // var mbRemaining = (mbTotal - mbUploaded).toFixed(2);
                        // var timeRemaining = ((e.total - e.loaded) / (e.loaded / e.timeStamp / 1000))
                        // .toFixed(2); // estimate in seconds
                        // Update progress bar and info display
                        // $('#progressBar').css('width', percentComplete + '%').text(percentComplete + '%');
                        // $('#progressInfo').html(
                        //     `Uploaded: ${mbUploaded} MB / ${mbTotal} MB<br>` +
                        //     // `Remaining: ${mbRemaining} MB<br>` +
                        //     `Time Left: ${timeRemaining} sec`
                        // );
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
                                // Swal.fire({
                                //     icon: 'success',
                                //     title: "Success",
                                //     text: res.message,
                                // }).then(function() {
                                // });
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
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));
                xhr.send(formData);
            }
        });
        // var isAdvancedUpload = function() {
        //     var div = document.createElement('div');
        //     return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window &&
        //         'FileReader' in window;
        // }();
        // let draggableFileArea = document.querySelector(".drag-file-area");
        // let browseFileText = document.querySelector(".browse-files");
        // let uploadIcon = document.querySelector(".upload-icon");
        // let dragDropText = document.querySelector(".dynamic-message");
        // let fileInput = document.querySelector(".default-file-input");
        // let cannotUploadMessage = document.querySelector(".cannot-upload-message");
        // let cancelAlertButton = document.querySelector(".cancel-alert-button");
        // let uploadedFile = document.querySelector(".file-block");
        // let fileName = document.querySelector(".file-name");
        // let fileSize = document.querySelector(".file-size");
        // let progressBar = document.querySelector(".progress-bar");
        // let removeFileButton = document.querySelector(".remove-file-icon");
        // let uploadButton = document.querySelector(".upload-button");
        // let fileFlag = 0;
        // fileInput.addEventListener("click", () => {
        //     fileInput.value = '';
        //     console.log(fileInput.value);
        // });
        // fileInput.addEventListener("change", e => {
        //     console.log(" > " + fileInput.value)
        //     uploadIcon.innerHTML = '<i class="fa-solid fa-circle-check fa-fw"></i>';
        //     dragDropText.innerHTML = 'File Dropped Successfully!';
        //     document.querySelector(".label").innerHTML = `drag & drop or <span class="browse-files"> <input type="file" class="default-file-input" style="" multiple/> 
    // <span class="browse-files-text" style="top: 0;"> browse file</span></span>`;
        //     uploadButton.innerHTML = `Upload`;
        //     fileName.innerHTML = fileInput.files[0].name;
        //     fileSize.innerHTML = (fileInput.files[0].size / 1024).toFixed(1) + " KB";
        //     uploadedFile.style.cssText = "display: flex;";
        //     progressBar.style.width = 0;
        //     fileFlag = 0;
        // });
        // uploadButton.addEventListener("click", () => {
        //     let isFileUploaded = fileInput.value;
        //     if (isFileUploaded != '') {
        //         if (fileFlag == 0) {
        //             fileFlag = 1;
        //             var width = 0;
        //             var id = setInterval(frame, 50);
        //             function frame() {
        //                 if (width >= 100) {
        //                     clearInterval(id);
        //                     uploadButton.innerHTML =
        //                         `<span class="material-icons-outlined upload-button-icon"> <i class="fa-solid fa-circle-check fa-fw"></i> </span> Uploaded`;
        //                 } else {
        //                     width += 5;
        //                     progressBar.style.width = width + "%";
        //                 }
        //             }
        //         }
        //     } else {
        //         cannotUploadMessage.style.cssText = "display: flex; animation: fadeIn linear 1.5s;";
        //     }
        // });
        // cancelAlertButton.addEventListener("click", () => {
        //     cannotUploadMessage.style.cssText = "display: none;";
        // });
        // if (isAdvancedUpload) {
        //     ["drag", "dragstart", "dragend", "dragover", "dragenter", "dragleave", "drop"].forEach(evt =>
        //         draggableFileArea.addEventListener(evt, e => {
        //             e.preventDefault();
        //             e.stopPropagation();
        //         })
        //     );
        //     ["dragover", "dragenter"].forEach(evt => {
        //         draggableFileArea.addEventListener(evt, e => {
        //             e.preventDefault();
        //             e.stopPropagation();
        //             uploadIcon.innerHTML = 'file_download';
        //             dragDropText.innerHTML = 'Drop your file here!';
        //         });
        //     });
        //     draggableFileArea.addEventListener("drop", e => {
        //         uploadIcon.innerHTML = '<i class="fa-solid fa-circle-check fa-fw"></i>';
        //         dragDropText.innerHTML = 'File Dropped Successfully!';
        //         document.querySelector(".label").innerHTML =
        //             `drag & drop or <span class="browse-files"> <input type="file" class="default-file-input" style=""/> <span class="browse-files-text" style="top: -23px; left: -20px;"> browse file</span> </span>`;
        //         uploadButton.innerHTML = `Upload`;
        //         let files = e.dataTransfer.files;
        //         fileInput.files = files;
        //         console.log(files[0].name + " " + files[0].size);
        //         console.log(document.querySelector(".default-file-input").value);
        //         fileName.innerHTML = files[0].name;
        //         fileSize.innerHTML = (files[0].size / 1024).toFixed(1) + " KB";
        //         uploadedFile.style.cssText = "display: flex;";
        //         progressBar.style.width = 0;
        //         fileFlag = 0;
        //     });
        // }
        // removeFileButton.addEventListener("click", () => {
        //     uploadedFile.style.cssText = "display: none;";
        //     fileInput.value = '';
        //     uploadIcon.innerHTML = '<i class="fa-solid fa-arrow-up-from-bracket fa-fw"></i>';
        //     dragDropText.innerHTML = 'Drag & drop any file here';
        //     document.querySelector(".label").innerHTML =
        //         `or <span class="browse-files"> <input type="file" class="default-file-input"/> <span class="browse-files-text">browse file</span> <span>from device</span> </span>`;
        //     uploadButton.innerHTML = `Upload`;
        // });
    </script>
@endpush()
