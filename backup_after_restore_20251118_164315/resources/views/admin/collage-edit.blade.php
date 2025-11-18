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
        <h3 class="mt-0 fw-bold">View Art Gallery</h3>
    </div>
</div>
<section class="section_collage">
    <div class="container">
        <div class="row">
            <div class="col-lg-6" id="updateFormContainer">
                <div class="mb-4"> 
                    <label class="form-label fw-semibold">Profile Image</label>
                    <br>
                    @if ($designCollagePreviewData->image_path)
                        <a href="{{ asset('storage/' . $designCollagePreviewData->image_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $designCollagePreviewData->image_path) }}"
                                alt="Profile Images" class="img-fluid" style="max-width: 150px;">
                        </a>
                    @endif

                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Grid Rows:{{ $designCollagePreviewData->grid_rows }}</label>

                </div>

                <div class="mb-4">
                    <label for="adminEmail" class="form-label fw-semibold">Grid
                        Columns:{{ $designCollagePreviewData->grid_columns }}</label>

                </div>

                <div class="mb-4">
                    <label for="adminEmail" class="form-label fw-semibold">Image Size:@if (!empty($designCollagePreviewData->height) && !empty($designCollagePreviewData->width))
                            {{ $designCollagePreviewData->height . ' x ' . $designCollagePreviewData->width . ' cm' }}
                        @else
                            Not specified
                        @endif
                    </label>

                </div>
                <div class="mb-4">
                    <label for="adminEmail" class="form-label fw-semibold">Total
                        Tiles:{{ $designCollagePreviewData->total_tiles }}</label>

                </div>


            </div>
        </div>


        <div class="tab-section-upload">
            @if (session('error_message'))
                <div id="error-alert" class="alert alert-danger">
                    {{ session('error_message') }}
                </div>
            @endif
            <div class="d-flex flex-wrap flex-md-nowrap align-items-start">

                <form class="form-container" enctype='multipart/form-data'>
                    <input type="hidden" value="{{ $collageAdmin->id }}" name="id">
                    <input type="hidden" value="{{ $collageAdmin->unique_id }}" name="unique_id">
                    <div class="upload-files-container">
                        <div class="row">
                            <div class="col-3 mt-4">
                                <label for="tag_id">Tags</label>
                                <select name="tag_id[]" class="form-control select-tags" multiple required>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}"
                                            {{ in_array($tag->id, $selectedTagIds ?? []) ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-3 mt-3">
                                <label for="collection_id">Collection</label>
                                <select name="collection_id" class="form-control" required>
                                    @foreach ($collections as $collection)
                                        <option value="{{ $collection->id }}"
                                            {{ $collageAdmin->collection_id == $collection->id ? 'selected' : '' }}>
                                            {{ $collection->name }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-3 mt-3">
                                <label for="title">Title</label>
                                <input type="text" name="title" class="form-control" required
                                    value="{{ old('title', $collageAdmin->title ?? '') }}">

                            </div>

                            <div class="col-3 mt-3">
                                <label for="short_description">Short Description</label>
                                <textarea name="short_description" class="form-control" required>{{ old('short_description', $collageAdmin->short_description ?? '') }}</textarea>

                            </div>

                            <div class="col-3 mt-3">
                                <label for="designer_name">Artist Name</label>
                                <input type="text" name="designer_name" class="form-control" required
                                    value="{{ old('designer_name', $collageAdmin->designer_name ?? '') }}">
                            </div>

                            <div class="col-3 mt-3">
                                <label for="amount">Amount(US$)</label>
                                <input type="number" name="amount" class="form-control" step="0.01" required
                                    value="{{ old('amount', $collageAdmin->amount ?? '') }}">

                            </div>
                        </div>


                        <!--button type="button" class="upload-button btn-primary"> Upload </button-->
                        <div class="mt-2 d-flex gap-3">
                            <a href="{{ route('admin.galleryList') }}" class="btn btn-primary"> Back</a>

                            <a href="{{ route('front.design-collage', ['unique_id' => $collageAdmin->unique_id, 'type' => 'admin']) }}"
                                class="btn btn-primary"> Edit Collage
                            </a>


                            <button type="button" class="btn btn-primary" id="uploadBtn"> Update

                            </button>
                        </div>





                    </div>

                </form>


            </div>


            <div class="mb-3 mt-2 p-3 row" style="background-color: #ccc;">
                <?php
                
                foreach ($images as $tile) {
                    if ($tile['empty'] === 0) {
                        $image_bleed = json_decode($tile['image_with_bleed'], true);
                        if (is_array($image_bleed) && !empty($image_bleed)) {
                            foreach ($image_bleed as $image) {
                                echo '<div class="col-3 file-preview mb-3"><img src="' . asset('storage/' . $image) . '" alt="Image" class="img-fluid"></div>';
                            }
                        }
                    }
                }
                ?>
            </div>

        </div>







    </div>
</section>

<!-- pre loder model -->

<div class="modal fade" id="preloaderModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
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








    // Drag over and drop events









    // Upload button click event

    $('#uploadBtn').on('click', function(e) {
        e.preventDefault();

        let $btn = $(this);
        $btn.prop('disabled', true);


        let formData = new FormData();
        formData.append('user_type', 'admin');
        formData.append('id', $('input[name="id"]').val());
        formData.append('unique_id', $('input[name="unique_id"]').val());
        formData.append('collection_id', $('select[name="collection_id"]').val());
        formData.append('title', $('input[name="title"]').val());
        formData.append('short_description', $('textarea[name="short_description"]').val());
        formData.append('designer_name', $('input[name="designer_name"]').val());
        formData.append('amount', $('input[name="amount"]').val());

        let selectedTags = $('select[name="tag_id[]"]').val();
        if (selectedTags) {
            selectedTags.forEach(tagId => {
                formData.append('tag_id[]', tagId);
            });
        }

        $.ajax({
            url: "{{ route('front.update-collage') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.status === 1) {
                    $('#fileList').empty();
                    fileList = [];

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message || 'Update successfull!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                    });

                    setTimeout(function() {
                        window.location.href = "{{ route('admin.galleryList') }}";
                    }, 1000); // delay for 1 second to show the toast
                } else {
                    $btn.prop('disabled', false);
                    $("#preloaderModal").modal('hide');
                    Swal.fire({
                        icon: 'warning',
                        title: "Alert",
                        text: res.message,
                    });
                }
            },

            error: function(xhr) {
                $btn.prop('disabled', false);
                $("#preloaderModal").modal('hide');

                let errorMessage = "Something went wrong.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                });
            }
        });
    });
</script>
