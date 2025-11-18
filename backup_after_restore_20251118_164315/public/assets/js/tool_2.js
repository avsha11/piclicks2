
// Single image

document.querySelectorAll(".edit_btn").forEach((image) => {
    image.addEventListener("click", function () {
        // Open the off-canvas
        const offcanvas = new bootstrap.Offcanvas(
            document.getElementById("offcanvasRight")
        );
        offcanvas.show();
    });
});

const image = document.getElementById("image_edit");
const overflowHidden = document.querySelector(".image-container");
// const toolMain = document.querySelector(".tool-main"); // Parent container for the image
let scale = 1;
let rotation = 0;
let isDragging = false;
let startX, startY, initialX, initialY;

// Zoom in
// document.getElementById("zoom-in").addEventListener("click", () => {
//     scale = Math.min(2, scale + 0.1); // Max zoom of 2
//     updateTransform();
// });

// // Zoom out
// document.getElementById("zoom-out").addEventListener("click", () => {
//     scale = Math.max(0.6, scale - 0.1); // Min zoom of 0.6
//     updateTransform();
// });

// // Rotate
// document.getElementById("rotate").addEventListener("click", () => {
//     rotation += 90; // Rotate by 90 degrees
//     updateTransform();
// });

// Update the transformation
function updateTransform() {
    image.style.transform = `scale(${scale}) rotate(${rotation}deg)`;
}

// Dragging functionality with bounds
image.addEventListener("mousedown", (e) => {
    isDragging = true;
    startX = e.clientX;
    startY = e.clientY;
    initialX = image.offsetLeft;
    initialY = image.offsetTop;
    e.preventDefault();
});

document.addEventListener("mousemove", (e) => {
    if (!isDragging) return;
    if (scale <= 1) {
        image.style.left = `0px`;
        image.style.top = `0px`;
        return false;
    }

    // Calculate new position
    let dx = e.clientX - startX;
    let dy = e.clientY - startY;

    let newX = initialX + dx;
    let newY = initialY + dy;

    // Get bounds for the image inside the overflowHidden container
    const parentRect = overflowHidden.getBoundingClientRect();
    const imageRect = image.getBoundingClientRect();

    // Allow dragging left and top until the image edges reach the container bounds
    if (newX > 0) {
        // newX = 0; // Prevent leaving the left side
        // if (newX - imageRect.width < parentRect.width) {
        //     newX = Math.abs(parentRect.width - imageRect.width);
        // }
    } else if (newX + imageRect.width < parentRect.width) {
        newX = parentRect.width - imageRect.width; // Prevent leaving the right side
    }

    if (newY > 0) {
        // newY = 0; // Prevent leaving the top side
        newY = Math.abs(parentRect.height - imageRect.height);
    } else if (newY + imageRect.height < parentRect.height) {
        newY = parentRect.height - imageRect.height; // Prevent leaving the bottom side
    }
    console.log(newX, newY);

    // Apply the constrained position
    image.style.left = `${newX}px`;
    image.style.top = `${newY}px`;
});


document.addEventListener("mouseup", () => {
    isDragging = false;
});

// Crop
// Initialize global variables for cropper instance and image element
let cropper;
const originalImageContainer = document.getElementById("image_edit");

// Open modal and initialize cropper on the image inside the modal
// document.getElementById("open-crop-modal").addEventListener("click", () => {
//     const modal = document.getElementById("crop-modal");
//     const cropImage = document.getElementById("crop-image");

//     // Set the source of the modal image to be the original image's source
//     cropImage.src = originalImageContainer.src;

//     // Show the modal
//     modal.style.display = "flex";

//     // Wait for the image to load before adjusting size
//     cropImage.onload = () => {
//         // Set the image height to 400px for the preview
//         cropImage.style.height = "400px";

//         // Initialize cropper after image is loaded and resized
//         cropper = new Cropper(cropImage, {
//             aspectRatio: NaN,
//             viewMode: 1,
//             autoCrop: true,
//             responsive: true, // Ensure it adjusts based on window size
//             ready: function () {
//                 // Ensure the cropper is ready after initialization
//                 cropper.setCropBoxData({
//                     left: 0,
//                     top: 0,
//                     width: cropImage.width,
//                     height: cropImage.height,
//                 });
//             },
//         });
//     };
// });

// // Handle crop button click to apply the cropped area
// document.getElementById("crop-button").addEventListener("click", () => {
//     // Get the cropped canvas from cropper
//     const croppedCanvas = cropper.getCroppedCanvas();

//     // Convert the canvas to a data URL and update the original image
//     originalImageContainer.src = croppedCanvas.toDataURL();

//     // Close the modal and destroy the cropper instance
//     document.getElementById("crop-modal").style.display = "none";
//     cropper.destroy();
// });

// Close modal when 'X' is clicked
document.querySelector(".close-modal").addEventListener("click", () => {
    document.getElementById("crop-modal").style.display = "none";
    if (cropper) {
        cropper.destroy(); // Destroy cropper to free resources
    }
});

// save
// document.querySelector(".save_btn").addEventListener("click", () => {
//     const selectedImage = document.querySelector(".image-item.selected");

//     if (!selectedImage) {
//         alert("No image selected for editing");
//         return;
//     }

//     // Apply all transformations (zoom, rotation, and crop) to the image.
//     const transformedImage = selectedImage.cloneNode(true);

//     // Get the transformed image data (cropped, zoomed, and rotated)
//     const canvas = document.createElement("canvas");
//     const ctx = canvas.getContext("2d");

//     // Set the canvas size to the size of the transformed image
//     const imgRect = selectedImage.getBoundingClientRect();
//     canvas.width = imgRect.width;
//     canvas.height = imgRect.height;

//     // Draw the transformed image on the canvas (including zoom and rotation)
//     ctx.save();
//     ctx.translate(canvas.width / 2, canvas.height / 2);
//     ctx.rotate((rotation * Math.PI) / 180);
//     ctx.scale(scale, scale);
//     ctx.drawImage(
//         selectedImage,
//         -imgRect.width / 2,
//         -imgRect.height / 2,
//         imgRect.width,
//         imgRect.height
//     );
//     ctx.restore();

//     // Get the resulting image data as a Data URL (base64)
//     const updatedImageData = canvas.toDataURL();

//     // Update the source of the selected image with the new data
//     selectedImage.src = updatedImageData;

//     // Reset transformations (if needed) to avoid reapplying when opening in modal
//     scale = 1;
//     rotation = 0;
//     updateTransform(); // Reset any transform-related styles


//     $("#singleImageModal").modal('hide');
//     const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasRight"));
//     if (offcanvas) {
//         offcanvas.hide(); // Close offcanvas editor
//     }

//     document.getElementById("crop-modal").style.display = "none";

//     if (cropper) {
//         cropper.destroy(); // Destroy cropper to free resources
//     }

//     // Ensure the updated image is reflected in the preview grid if it's selected
//     updatePreviewGrid(selectedImage, updatedImageData);
// });

// // Function to update the selected image in the preview grid
// function updatePreviewGrid(selectedImage, updatedImageData) {
//     const imageGrid = document.getElementById("preview-grid");
//     const selectedImageIndex = Array.from(imageGrid.getElementsByClassName("image-item")).indexOf(selectedImage);

//     if (selectedImageIndex > -1) {
//         const allImages = imageGrid.getElementsByClassName("image-item");
//         const imageToUpdate = allImages[selectedImageIndex];
//         imageToUpdate.src = updatedImageData; // Update the src of the selected image
//     }
// }


/**
 * Flip an image horizontally or vertically.
 * @param {string} src - The image source (URL or Base64 string).
 * @param {string} flipType - The flip type: 'horizontal' or 'vertical'.
 * @param {function} callback - A callback to handle the flipped image as a Base64 string.
 */
function flipImage(src, flipType, callback) {
    const img = new Image();

    // Ensure the callback is triggered after the image loads
    img.onload = function () {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Set canvas size to the image size
        canvas.width = img.width;
        canvas.height = img.height;

        // Flip the image
        ctx.save();
        if (flipType === 'horizontal') {
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1); // Flip horizontally
        } else if (flipType === 'vertical') {
            ctx.translate(0, canvas.height);
            ctx.scale(1, -1); // Flip vertically
        }
        ctx.drawImage(img, 0, 0);
        ctx.restore();

        // Convert the canvas content back to Base64
        const flippedImage = canvas.toDataURL();

        // Call the callback with the flipped image
        callback(flippedImage);
    };

    // Handle image loading error
    img.onerror = function () {
        console.error('Failed to load image.');
    };

    // Set the image source
    img.src = src;
}





let popup_image_croppie;
let zoom_croppie = 0;
let min_zoom_croppie = 0;
let max_zoom_croppie = 0;
let rotation_croppie = 0;
let viewportwidth = 570;
let viewportheight = 500;
let originalSrcOnPopup = '';
document.addEventListener("DOMContentLoaded", function () {

    let window_width = $(window).width();
    if (window_width < 575) {
        viewportwidth = 342;
        viewportheight = 300;
    } else if (window_width < 767) {
        viewportwidth = 456;
        viewportheight = 400;
    }

    $('.image-container').css('height', viewportheight + 'px');
    $('.image-container').css('width', viewportwidth + 'px');
    // console.log(window_width, viewportwidth, viewportheight);

});

if (document.querySelector(".toolzoomrow")) {
    document.querySelector(".toolzoomrow").addEventListener("click", function (event) {
        const previewGrid = document.getElementById("preview-grid");
        if (!previewGrid.contains(event.target)) { // Check if the click is outside the preview-grid
            document.querySelectorAll(".image-div").forEach((div) => div.classList.remove("selected"));
            toggleSortable();
            if (!document.querySelector('.upload-image').classList.contains('disabled')) {
                document.querySelector('.upload-image').classList.add('disabled');
            }
            if (!document.querySelector('.delete-image').classList.contains('disabled')) {
                document.querySelector('.delete-image').classList.add('disabled');
            }
        }
    });
}

let gridIsDragging = false;
let gridDragStartTime = 0;
let lastTapTime = 0;
const doubleTapThreshold = 300;

const toolzoom = document.getElementById("toolzoom");
// Common drag handling
const onDragStart = (event) => {
    gridDragStartTime = Date.now();
    gridIsDragging = false; // Reset dragging state
};

const onDragMove = () => {
    gridIsDragging = true; // Dragging state active
};

const onDragEnd = () => {
    setTimeout(() => {
        gridIsDragging = false; // Reset dragging state after a small delay
    }, 50);
};

// Attach drag handlers for mouse events
if (toolzoom) {

    toolzoom.addEventListener("mousedown", onDragStart);
    toolzoom.addEventListener("mousemove", onDragMove);
    toolzoom.addEventListener("mouseup", onDragEnd);

    // Attach drag handlers for touch events
    toolzoom.addEventListener("touchstart", onDragStart);
    toolzoom.addEventListener("touchmove", onDragMove);
    toolzoom.addEventListener("touchend", onDragEnd);
}



let isModalOpening = false;
let textModalOpening = false;
// Ensure that the image is updated in the preview grid after cropping and saving
if (document.getElementById("preview-grid")) {
    document.getElementById("preview-grid").addEventListener("click", function (event) {
        console.log('isModalOpening ' + isModalOpening, 'gridIsDragging ' + gridIsDragging);
        if (isModalOpening) return;
        if (textModalOpening) return;
        if (gridIsDragging) {
            event.preventDefault(); // Prevent click event if dragging occurred
            return;
        }

        const now = new Date().getTime();
        // Remove 'selected' class from all images
        document.querySelectorAll(".image-div").forEach((div) => div.classList.remove("selected"));

        let parentDiv = event.target.closest(".image-div");
        if (parentDiv) {
            parentDiv.classList.add("selected");
            toggleSortable();

            if (event.target.classList.contains("open-edit-pop")) {
                document.querySelector('.delete-image').classList.remove('disabled');
                if (!document.querySelector('.upload-image').classList.contains('disabled')) {
                    document.querySelector('.upload-image').classList.add('disabled');
                }
            }
            if (event.target.classList.contains("select-image-pop")) {
                document.querySelector('.upload-image').classList.remove('disabled');
                if (!document.querySelector('.delete-image').classList.contains('disabled')) {
                    document.querySelector('.delete-image').classList.add('disabled');
                }
            }


            if (now - lastTapTime < doubleTapThreshold) {
                console.log("Double-tap detected");
            } else {
                lastTapTime = now;
                return;
            }
            lastTapTime = now;

            if (event.target.classList.contains("open-edit-pop")) {

                // let originalImage = parentDiv.querySelector(".image-item-original");
                let originalImage = parentDiv.querySelector(".image-item");
                if (originalImage) {
                    originalSrcOnPopup = originalImage.src;
                    // let originalSrc = event.target.src;

                    $("#mainLoader").show();
                    // console.log(originalSrc);
                    isModalOpening = true;
                    // console.log('originalSrcOnPopup', originalSrcOnPopup);

                    setCroppieImage(originalSrcOnPopup);
                }
            }
        }
    });
}

function setCroppieImage(originalSrc) {
    let imageEdit = $("#image_edit");
    imageEdit.attr('src', originalSrc);

    imageEdit.off('load').on('load', async function () {


        popup_image_croppie = $("#image_edit").croppie({
            viewport: {
                width: viewportwidth,
                height: viewportheight,
                // type: 'square'
            },
            boundary: {
                width: viewportwidth,
                height: viewportheight
            },
            enableOrientation: true,
            // enableExif: false,
            mouseWheelZoom: false,
            showZoomer: false,
        });

        await new Promise(resolve => setTimeout(resolve, 500)); // Adjust timeout as needed

        const tempImage = new Image();
        tempImage.src = originalSrc;

        tempImage.onload = function () {
            let imgWidth = tempImage.naturalWidth;
            let imgHeight = tempImage.naturalHeight;

            // Calculate the initial zoom
            let scaleMin = Math.max(
                viewportwidth / imgWidth, // Scale for width
                viewportheight / imgHeight // Scale for height
            );
            scaleMin = parseFloat(scaleMin.toFixed(2));
            console.log('scaleMin ', scaleMin);


            popup_image_croppie.croppie('bind', {
                url: originalSrc
            }).then(async function () {

                // ----- store rotate in hidden to show again rotated image when open again -----
                // rotation_croppie = parseInt(parentDiv.querySelector(".image-item-rotate").value);
                // // if(rotation_croppie !== 1) {
                //     for (let r = 1; r < rotation_croppie; r++) {
                //         popup_image_croppie.croppie('rotate', -90);
                //     }
                // // }
                popup_image_croppie.croppie('rotate', -90);
                popup_image_croppie.croppie('rotate', 90);

                // ----- logic to store zoom - previously stored in hidden of original image -----
                // let zooom = parentDiv.querySelector(".image-item-zoom").value;
                // if (zooom !== '0') {

                //     zooomarr = zooom.split("|");
                //     zoom_croppie = parseFloat(zooomarr[0]);
                //     min_zoom_croppie = parseFloat(zooomarr[1]);
                //     popup_image_croppie.croppie('setZoom', zoom_croppie);
                // } else {

                popup_image_croppie.croppie('setZoom', scaleMin);
                min_zoom_croppie = scaleMin;
                zoom_croppie = scaleMin;
                if (scaleMin > 1) {
                    max_zoom_croppie = scaleMin * 2;
                } else {
                    max_zoom_croppie = 1.5;
                }
                // }

                // ----- solution of issue when image is small then viewport -----
                $('.cr-slider').attr('max', max_zoom_croppie);
                await new Promise(resolve => setTimeout(resolve, 500));
                popup_image_croppie.croppie('setZoom', scaleMin);

                console.log('Base64 image bound successfully');

                if (isModalOpening == true) {
                    $("#singleImageModal").modal('show');
                    const offcanvas = new bootstrap.Offcanvas(document.getElementById("offcanvasRight"));
                    if (offcanvas) {
                        offcanvas.show();
                    }
                }
                $("#imgLoader, #mainLoader").hide();

                isModalOpening = false;
            });
        };
    });
}

document.getElementById("rotate").addEventListener("click", () => {
    rotation_croppie += 1;
    rotation_croppie = rotation_croppie === 4 ? 1 : rotation_croppie;
    popup_image_croppie.croppie('rotate', -90);
});

document.getElementById("zoom-in").addEventListener("click", () => {
    if (zoom_croppie >= max_zoom_croppie) {
        return false;
    }
    zoom_croppie += 0.1;
    // console.log('zoom-', zoom_croppie);
    popup_image_croppie.croppie('setZoom', zoom_croppie);
});

// Zoom out
document.getElementById("zoom-out").addEventListener("click", () => {
    if (zoom_croppie <= min_zoom_croppie) {
        zoom_croppie = min_zoom_croppie;
        popup_image_croppie.croppie('setZoom', zoom_croppie);
        return false;
    }
    zoom_croppie -= 0.1;
    // console.log('zoom+', zoom_croppie);
    popup_image_croppie.croppie('setZoom', zoom_croppie);

});

document.getElementById("horizontal-flip").addEventListener("click", () => {
    $("#imgLoader").show();
    popup_image_croppie.croppie('destroy');
    flipImage(originalSrcOnPopup, 'horizontal', function (flippedImage) {
        // console.log('Flipped Image (Horizontal):', flippedImage);

        originalSrcOnPopup = flippedImage;
        setCroppieImage(originalSrcOnPopup);
        // $(".imgLoader").hide(); 
    });
});

document.getElementById("vertical-flip").addEventListener("click", () => {
    $("#imgLoader").show();
    popup_image_croppie.croppie('destroy');
    flipImage(originalSrcOnPopup, 'vertical', function (flippedImage) {
        // console.log('Flipped Image (vertical):', flippedImage);

        originalSrcOnPopup = flippedImage;
        setCroppieImage(originalSrcOnPopup);
    });
});



document.querySelector(".save_btn").addEventListener("click", () => {

    popup_image_croppie.croppie('result', {
        type: 'base64',
        size: 'original'
    }).then(function (img) {

        $(".image-div.selected").find(".open-edit-pop").attr("src", img);
        document.querySelectorAll(".image-div").forEach((div) => div.classList.remove("selected"));
    });
    $(".image-div.selected").find(".image-item-zoom").val(zoom_croppie + "|" + min_zoom_croppie);
    $(".image-div.selected").find(".image-item-rotate").val(rotation_croppie);

    popup_image_croppie.croppie('destroy');


    $("#singleImageModal").modal('hide');
    var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasRight"));
    if (offcanvas) {
        offcanvas.hide();
    }
});


document.querySelector(".delete_btn").addEventListener("click", () => {
    if (!confirm("Are you sure ?")) {
        return false;
    }
    let selected = $(".image-div.selected")
    selected.find(".image-item").removeClass("open-edit-pop");
    selected.find(".image-item").attr("src", greyImage);
    selected.find(".image-item-original").attr("src", greyImage);
    selected.find(".image-item-zoom").val(1);
    selected.find(".image-item-rotate").val(1);

    document.querySelectorAll(".image-div").forEach((div) => div.classList.remove("selected"));
    popup_image_croppie.croppie('destroy');

    $("#singleImageModal").modal('hide');

    var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasRight"));
    if (offcanvas) {
        offcanvas.hide();
    }
});


document.querySelector('#offcanvasRight #closeButton').addEventListener('click', function () {

    $("#singleImageModal").modal('hide');
    var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasRight"));
    if (offcanvas) {
        offcanvas.hide();
    }
    popup_image_croppie.croppie('destroy');
});


let panzoomInstance;
let panzoomInstance1;

let initialXY = [0, 0];
let zoom_grid = 0;
let start_zoom_grid = 0;
let min_zoom_grid = 0.1;
let max_zoom_grid = 3;

// function to re arrange image orders
let sortableElement = document.querySelector('.sortable');
let sortableObj;
let gridObj;
$(function () {

    // sortableElement.sortable({
    //     disabled: true, // Disabled initially
    //     start: function (event, ui) {
    //         console.log("Sorting start");

    //         const toolZoom = $("#toolzoom");
    //         const currentScale = toolZoom.css("transform");
    //         toolZoom.data("originalTransform", currentScale); // Store the current transform
    //         toolZoom.css("transform", "none");
    //     },
    //     stop: function (event, ui) {
    //         console.log("Sorting stop");

    //         const toolZoom = $("#toolzoom");
    //         const originalTransform = toolZoom.data("originalTransform");
    //         if (originalTransform) {
    //             toolZoom.css("transform", originalTransform);
    //         }
    //     },
    // });

    // sortableObj = Sortable.create(sortableElement, {
    //     animation: 150,
    //     swap: true,
    //     swapClass: 'highlight',
    //     // onChoose: function (evt) {
    //     //     console.log("Sorting chosen");

    //     // },
    //     onStart: function (evt) {
    //         // console.log("Sorting start");
    //     },
    //     onEnd: function (evt) {
    //         console.log("Sorting stop");
    //         // console.log(`Moved item ${evt.item.id} from ${evt.oldIndex} to ${evt.newIndex}`);
    //         divPositionCalc();
    //     },
    // });

    calcGridDimension()

    gridObj = new Muuri('.grid', {
        dragEnabled: true,
        layoutOnInit: true,
        // sortData: {
        //     id: function (item, element) {
        //         return parseFloat(element.children[0].textContent);
        //     }
        // }
    });

});

function toggleSortable() {
    const hasSelected = $(".sortable .image-div.selected").length > 0;

    if (hasSelected) {
        panzoomInstance.pause();
        // sortableElement.sortable("enable");
        // sortableObj.option("disabled", false);
        console.log("Sortable enabled");
    } else {
        panzoomInstance.resume();
        // sortableElement.sortable("disable");
        // sortableObj.option("disabled", true);
        console.log("Sortable disabled");

        if ($(".text-overlay.selected").length > 0) {
            panzoomInstance.pause();
            // sortableObj.option("disabled", true);
            console.log("text functionality enabled in sortable disabled");
        }
    }
}

function toggleGridDragSortonText() {
    const hasSelected = $(".text-overlay.selected").length > 0;

    if (hasSelected) {
        panzoomInstance.pause();
        // sortableObj.option("disabled", true);
        console.log("text functionality enabled");
    } else {
        panzoomInstance.resume();
        // sortableObj.option("disabled", false);
        console.log("text functionality disabled");
    }
}



document.addEventListener("DOMContentLoaded", async function () {

    // // @panzoom
    // const elem = document.getElementById('toolzoom')
    // panzoomInstance1 = Panzoom(elem, {
    //     // minScale: 0,
    //     maxScale: 10,
    //     contain: 'inside',
    //     disablePan: true
    // })
    // // elem.panzoomInstance1 = panzoomInstance1;
    // // panzoom.pan(10, 10)
    // // panzoom.zoom(2, { animate: true });

    // // button.addEventListener('click', panzoom.zoomIn)
    // elem.parentElement.addEventListener('wheel', panzoomInstance1.zoomWithWheel)


    // Set initial zoom based on window width

    let window_width = $(window).width();
    if (window_width < 435) { // mobile
        zoom_grid = 0.6;
        initialXY = [280, 240]
    } else if (window_width < 575) { // tablet
        zoom_grid = 0.55;
        initialXY = [250, 220]
    } else if (window_width < 767) { // tablet
        zoom_grid = 1;
        initialXY = [400, 350]
    }
    else if (window_width < 992) { // tablet
        zoom_grid = 0.8;
        initialXY = [350, 300]
    }
    else if (window_width < 1370) { // behlah pc
        zoom_grid = 0.60;
        initialXY = [270, 300]
    }
    else if (window_width < 1700) { // arnav pc
        zoom_grid = 0.65;
        initialXY = [500, 400]
    } else { // utkarsh pc
        zoom_grid = 0.80;
        initialXY = [400, 300]
    }
    start_zoom_grid = zoom_grid;

    const elem = document.getElementById('toolzoom');
    if(elem) {
    panzoomInstance = panzoom(elem, {
        zoomSpeed: 0.065, // 6.5% per mouse wheel event
        pinchSpeed: 2,
        maxZoom: 10,
        bounds: true,
        // transformOrigin: {x: 50, y: 50},
        // boundsPadding: 1,
        initialX: initialXY[0],
        initialY: initialXY[1],
        initialZoom: zoom_grid,
        onTouch: function (e) {
            console.log('pantouch ', e);
            return false; // tells the library to not preventDefault.
        },
        onDoubleClick: function (e) {
            console.log('pandoubleclick ', e);
            return false;
        }
    });
    
    var origin = panzoomInstance.getTransform();
    initialXY = [parseFloat(origin.x.toString(10)), parseFloat(origin.y.toString(10))];
    }



    // toggleSortable();

    // setTimeout(() => {
    //     $("#toolzoom").css("transform-origin", "center");
    // }, 2000);


    // const zoomist = new Zoomist("#toolzoom", {
    //     slider: false,
    //     zoomer: false,
    //     maxScale: 4,
    //     draggable: false,
    // });

});



document.addEventListener("DOMContentLoaded", function () {

    let toolzoom = document.getElementById('toolzoom');


    // // Set initial zoom based on window width
    // let window_width = $(window).width();
    // if (window_width < 575) {
    //     zoom_grid = 1;
    // } else if (window_width < 767) {
    //     zoom_grid = 1;
    // }
    // else if (window_width < 992) {
    //     zoom_grid = 1;
    // }
    // else if (window_width < 1370) {
    //     zoom_grid = 0.45;
    // }
    // else {
    //     zoom_grid = 0.62;
    // }
    // start_zoom_grid = zoom_grid;
    // toolzoom.style.transform = `scale(${zoom_grid})`;
    // toolzoom.style.transformOrigin = "50% 50%";


    // Add mouse wheel zoom functionality
    // document.getElementById('toolzoom').addEventListener('wheel', function (event) {
    //     event.preventDefault();

    //     // Get bounding box of the toolzoom container
    //     const rect = toolzoom.getBoundingClientRect();

    //     // Calculate mouse position relative to the toolzoom
    //     const offsetX = event.clientX - rect.left;
    //     const offsetY = event.clientY - rect.top;

    //     // Calculate percentages for transform-origin
    //     const percentX = (offsetX / rect.width) * 100;
    //     const percentY = (offsetY / rect.height) * 100;

    //     // Adjust zoom level
    //     if (event.deltaY < 0) {
    //         zoom_grid = Math.min(zoom_grid + 0.1, max_zoom_grid);
    //     } else {
    //         zoom_grid = Math.max(zoom_grid - 0.1, min_zoom_grid);
    //     }

    //     // Update transform-origin and scale
    //     toolzoom.style.transformOrigin = `${percentX}% ${percentY}%`;
    //     toolzoom.style.transform = `scale(${zoom_grid})`;

    //     // centerViewport();
    // });


    // toolzoom.addEventListener("wheel", function (event) {
    //     event.preventDefault();

    //     // Calculate new zoom level
    //     const zoomFactor = event.deltaY < 0 ? 1.1 : 0.9; // Zoom in or out
    //     const newZoomGrid = Math.min(max_zoom_grid, Math.max(min_zoom_grid, zoom_grid * zoomFactor));
    //     if (newZoomGrid === zoom_grid) return; // Prevent exceeding bounds

    //     // Get mouse position relative to the toolzoom element
    //     const rect = toolzoom.getBoundingClientRect();
    //     const mouseX = (event.clientX - rect.left) / rect.width;
    //     const mouseY = (event.clientY - rect.top) / rect.height;

    //     // Update transform origin based on mouse position
    //     const transformOriginX = mouseX * 100;
    //     const transformOriginY = mouseY * 100;
    //     toolzoom.style.transformOrigin = `${transformOriginX}% ${transformOriginY}%`;

    //     // Apply zoom
    //     zoom_grid = newZoomGrid;
    //     toolzoom.style.transform = `scale(${zoom_grid})`;
    // });


    // Zoom in button
    $('.grid-zoom-in').on('click', function () {
        zoom_grid = Math.min(zoom_grid + 0.1, max_zoom_grid);
        // $('#toolzoom').css('transform', 'scale(' + zoom_grid + ')');

        panzoomInstance.moveTo(initialXY[0], initialXY[1]);
        panzoomInstance.zoomAbs(
            initialXY[0], initialXY[1], zoom_grid
        );
    });

    // Zoom out button
    $('.grid-zoom-out').on('click', function () {
        if (zoom_grid <= min_zoom_grid) {
            zoom_grid = min_zoom_grid;
            return false;
        }
        zoom_grid = Math.max(zoom_grid - 0.1, min_zoom_grid);
        // $('#toolzoom').css('transform', 'scale(' + zoom_grid + ')');

        panzoomInstance.moveTo(initialXY[0], initialXY[1]);
        panzoomInstance.zoomAbs(
            initialXY[0], initialXY[1], zoom_grid
        );
    });

    // reset zoom button
    $('.grid-zoom-init').on('click', function () {
        zoom_grid = start_zoom_grid;
        // $('#toolzoom').css('transform', 'scale(' + zoom_grid + ')');
        // $('#toolzoom').css('transform-origin', '50% 50%');

        panzoomInstance.moveTo(initialXY[0], initialXY[1]);
        panzoomInstance.zoomAbs(
            initialXY[0], initialXY[1], zoom_grid
        );
    });


});



let image_div_html;
document.addEventListener("DOMContentLoaded", function () {
    image_div_html = `
    <div class="image-div">
        <img src="`+ greyImage + `" alt=""
            class="image-item-original d-none">
        <img src="`+ greyImage + `" alt=""
            class="select-image-pop image-item">
        <input type="hidden" class="image-item-zoom" value="0">
        <input type="hidden" class="image-item-rotate" value="1">
    </div>`;

    // calcGridDimension()
    // divPositionCalc();
});


let grid_columns = parseInt(document.getElementById("grid_columns").value);
let grid_rows = parseInt(document.getElementById("grid_rows").value);

// Add a blank row at the top
$(".add-top").click(function () {
    grid_rows += 1;
    $("#grid_rows").val(grid_rows);

    let row_html = '';
    for (let i = 0; i < grid_columns; i++) {
        row_html += image_div_html;
    }

    $("#preview-grid").prepend(row_html); // Add the row at the beginning
    calcGridDimension()
    divPositionCalc()
});

// Add a blank row at the bottom
$(".add-bottom").click(function () {
    grid_rows += 1;
    $("#grid_rows").val(grid_rows);

    let row_html = '';
    for (let i = 0; i < grid_columns; i++) {
        row_html += image_div_html;
    }

    $("#preview-grid").append(row_html); // Add the row at the end
    calcGridDimension()
    divPositionCalc()
});

// Add a blank column on the left
$(".add-left").click(function () {

    // Add a blank cell to the beginning of each row
    $("#preview-grid .image-div").each(function (index, element) {
        if (index % grid_columns === 0) {
            $(this).before(image_div_html);
        }
    });
    grid_columns += 1;
    $("#grid_columns").val(grid_columns);

    // Update the grid-template-columns style
    $("#preview-grid").css("grid-template-columns", `repeat(${grid_columns}, 1fr)`);
    calcGridDimension()
    divPositionCalc()
});

// Add a blank column on the right
$(".add-right").click(function () {

    // Add a blank cell to the end of each row
    $("#preview-grid .image-div").each(function (index, element) {
        if ((index + 1) % grid_columns === 0) {
            $(this).after(image_div_html);
        }
    });
    grid_columns += 1;
    $("#grid_columns").val(grid_columns);

    // Update the grid-template-columns style
    $("#preview-grid").css("grid-template-columns", `repeat(${grid_columns}, 1fr)`);
    calcGridDimension()
    divPositionCalc()
});

// Remove a row from the top
$(".minus-top").click(function () {
    let edit_pop = false;
    $("#preview-grid .image-div").slice(0, grid_columns).each(function () {
        if ($(this).find("img.open-edit-pop").length > 0) {
            edit_pop = true;
        }
    });
    if (edit_pop) {
        Swal.fire({
            text: "Cannot remove the top row as it contains images !",
            icon: "error",
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timer: 3000
        });
        return;
    }

    $("#preview-grid .image-div").slice(0, grid_columns).remove();
    grid_rows -= 1;
    $("#grid_rows").val(grid_rows);
    calcGridDimension()
    divPositionCalc()
});

// Remove a row from the bottom
$(".minus-bottom").click(function () {
    let edit_pop = false;

    $("#preview-grid .image-div").slice(-grid_columns).each(function () {
        if ($(this).find("img.open-edit-pop").length > 0) {
            edit_pop = true;
        }
    });
    if (edit_pop) {
        Swal.fire({
            text: "Cannot remove the bottom row as it contains images !",
            icon: "error",
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timer: 3000
        });
        return;
    }

    $("#preview-grid .image-div").slice(-grid_columns).remove();
    grid_rows -= 1;
    $("#grid_rows").val(grid_rows);
    calcGridDimension()
    divPositionCalc()
});

// Remove a column from the left
$(".minus-left").click(function () {
    let edit_pop = false;

    $("#preview-grid .image-div").filter((index) => index % grid_columns === 0).each(function () {
        if ($(this).find("img.open-edit-pop").length > 0) {
            edit_pop = true;
        }
    });
    if (edit_pop) {
        Swal.fire({
            text: "Cannot remove the left column as it contains images !",
            icon: "error",
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timer: 3000
        });
        return;
    }

    $("#preview-grid .image-div").filter((index) => index % grid_columns === 0).remove();
    grid_columns -= 1;
    $("#grid_columns").val(grid_columns);

    $("#preview-grid").css("grid-template-columns", `repeat(${grid_columns}, 1fr)`);
    calcGridDimension()
    divPositionCalc()
});

// Remove a column from the right
$(".minus-right").click(function () {
    let edit_pop = false;

    $("#preview-grid .image-div").filter((index) => (index + 1) % grid_columns === 0).each(function () {
        if ($(this).find("img.open-edit-pop").length > 0) {
            edit_pop = true;
        }
    });
    if (edit_pop) {
        Swal.fire({
            text: "Cannot remove the right column as it contains images !",
            icon: "error",
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timer: 3000
        });
        return;
    }

    $("#preview-grid .image-div").filter((index) => (index + 1) % grid_columns === 0).remove();
    grid_columns -= 1;
    $("#grid_columns").val(grid_columns);

    $("#preview-grid").css("grid-template-columns", `repeat(${grid_columns}, 1fr)`);
    calcGridDimension()
    divPositionCalc()
});

let actualWidth = 91;
let actualHeight = 80;
let actualMargin = 2;
let visibleWidth = 14.37;
let visibleHeight = 12.6;

function calcGridDimension() {
    let columns = parseInt(document.getElementById("grid_columns").value);
    let rows = parseInt(document.getElementById("grid_rows").value);

    $(".layout-horiz-size span").html((visibleWidth * columns).toFixed(0));
    $(".layout-vert-size span").html((visibleHeight * rows).toFixed(0));

    $("#preview-grid").css("width", ((actualWidth + actualMargin + actualMargin) * columns) + "px");
    $("#preview-grid").css("height", ((actualHeight + actualMargin + actualMargin) * rows) + "px");

    $(".image-div:nth-child(2)").css("width", actualWidth * 2 + "px");
    $(".image-div:nth-child(2)").css("height", actualHeight * 2 + "px");

    $(".image-div:nth-child(7)").css("width", actualWidth * 2 + "px");
    $(".image-div:nth-child(13),.image-div:nth-child(14),.image-div:nth-child(15),.image-div:nth-child(16)").css("display", "none");
    // $("#preview-grid").css('position', 'relative')

}

function divPositionCalc() {
    let columns = parseInt(document.getElementById("grid_columns").value);
    let rows = parseInt(document.getElementById("grid_rows").value);

    let curr_col = 1;
    let curr_row = 1;
    for (let i = 1; i <= (columns * rows); i++) {

        let wid = parseInt($(".image-div:nth-child(" + i + ")").css("width").slice(0, -2));
        let hei = parseInt($(".image-div:nth-child(" + i + ")").css("height").slice(0, -2));

        let x = 0;
        let y = 0;
        let margin = 2;
        // if (wid == actualWidth && hei == actualHeight) {
        $(".image-div:nth-child(" + i + ")").css('position', 'absolute')
        x = ((curr_col - 1) * wid) + (margin * curr_col - 1);
        y = ((curr_row - 1) * hei) + (margin * curr_row - 1);
        // x = (x === 1) ? 0 : x * wid;
        // y = (y === 1) ? 0 : y * hei;
        $(".image-div:nth-child(" + i + ")").css("top", y + "px");
        $(".image-div:nth-child(" + i + ")").css("left", x + "px");
        // $(".image-div:nth-child(" + i + ")").css("transform", "translate(" + x + "px, " + y + "px)");
        // } else {

        // }
        console.log(i, wid, hei, x, y, curr_col, curr_row);

        if ((i % columns) === 0) {
            curr_row++;
            curr_col = 1;
        } else {
            curr_col++;
        }


    }
}


if(document.querySelector(".delete-image")) {
document.querySelector(".delete-image").addEventListener("click", () => {

    if ($(".image-div.selected").find(".image-item.open-edit-pop").length === 1) {
        Swal.fire({
            title: "Are you sure ?",
            icon: "info",
            html: `You want to remove the image ?`,
            showCloseButton: true,
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: `Yes`,
            cancelButtonText: `No`,
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {

                let selected = $(".image-div.selected")

                selected.removeClass("success-black-outlined success-white-outlined");
                filters.forEach((filter) => selected.find(".image-item").removeClass(filter));
                selected.find(".image-item").removeClass("open-edit-pop");
                selected.find(".image-item").addClass("select-image-pop");
                selected.find(".image-item").attr("src", greyImage);
                selected.find(".image-item-original").attr("src", greyImage);
                selected.find(".image-item-zoom").val(1);
                selected.find(".image-item-rotate").val(1);

                document.querySelectorAll(".image-div").forEach((div) => div.classList.remove("selected"));

            }
        });
    }
})
}

let selectedElementForUpload;
if(document.getElementById("image-input")) {
document.getElementById("image-input").addEventListener("change", function (event) {
    const files = Array.from(event.target.files);

    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            let imgsrc = e.target.result;

            selectedElementForUpload.removeClass("success-black-outlined success-white-outlined");
            if (currentFrame !== '') {
                selectedElementForUpload.addClass(currentFrame);
            }
            if (currentFilter !== '') {
                selectedElementForUpload.find(".image-item").addClass(currentFilter);
            }
            selectedElementForUpload.find(".image-item").addClass("open-edit-pop");
            selectedElementForUpload.find(".image-item").removeClass("select-image-pop");
            selectedElementForUpload.find(".image-item").attr("src", imgsrc);
            selectedElementForUpload.find(".image-item-original").attr("src", imgsrc);
            selectedElementForUpload.find(".image-item-zoom").val(1);
            selectedElementForUpload.find(".image-item-rotate").val(1);

            document.querySelectorAll(".image-div").forEach((div) => div.classList.remove("selected"));
        };
        reader.readAsDataURL(file);
    });
});
}

if(document.querySelector(".upload-image")) {
document.querySelector(".upload-image").addEventListener("click", () => {

    if ($(".image-div.selected").find(".image-item.select-image-pop").length === 1) {
        selectedElementForUpload = $(".image-div.selected");
        $("#image-input").click();
    }
})
}






document.addEventListener("DOMContentLoaded", () => {

    // Function to apply frame
    // function applyFrame(frameId) {
    //     if (frameId === "success-outlined") {
    //         imgElement.style.border = "10px solid #000"; // Example frame
    //         currentFrame = "Classic Frame";
    //     } else if (frameId === "danger-outlined") {
    //         imgElement.style.border = "none"; // Remove frame
    //         currentFrame = "No Frame";
    //     }
    // }

    function applyFrame(frameId) {
        let imgDivElements = document.querySelectorAll('.image-div');

        imgDivElements.forEach((imgDivElement) => {
            if (imgDivElement.querySelector('.open-edit-pop')) {
                imgDivElement.classList.remove("success-black-outlined", "success-white-outlined");
                if (frameId !== "none-outlined") {
                    imgDivElement.classList.add(frameId);
                }
                // if (frameId === "success-black-outlined") {
                //     imgDivElement.style.border = "3px solid #000"; // Black frame
                //     imgDivElement.style.borderRadius = "5px"; // Rounded corners
                // } else if (frameId === "success-white-outlined") {
                //     imgDivElement.style.border = "3px solid #fff"; // White frame
                //     imgDivElement.style.borderRadius = "5px"; // Rounded corners
                // } else if (frameId === "danger-outlined") {
                //     imgDivElement.style.border = "none"; // Remove frame
                //     imgDivElement.style.borderRadius = "0"; // Reset corners
                // }
            }
        });
        if (frameId == "none-outlined") {
            currentFrame = "";
        } else {
            currentFrame = frameId;
        }
    }
    // Event listeners for frame options
    document
        .querySelectorAll('input[name="options-outlined"]')
        .forEach((frameOption) => {
            frameOption.addEventListener("change", (e) => {
                applyFrame(e.target.id);
            });
        });


    // Function to apply layout
    function applyLayout(layoutId) {
        if (layoutId === "grid") {
            imgElement.classList.add("grid-layout");
            imgElement.classList.remove("playful-layout");
            currentLayout = "Grid";
        } else if (layoutId === "playful") {
            imgElement.classList.add("playful-layout");
            imgElement.classList.remove("grid-layout");
            currentLayout = "Playful Grid";
        }
        console.log('currentLayout ', currentLayout);
    }
    // Event listeners for layout options
    document
        .querySelectorAll('input[name="frame-Layout"]')
        .forEach((layoutOption) => {
            layoutOption.addEventListener("change", (e) => {
                applyLayout(e.target.id);
            });
        });


});

const filters = [
    "filter-original",
    "filter-nordic",
    "filter-noir",
    "filter-scandi",
    "filter-belveder",
    "filter-stark",
    "filter-capri",
];
// Function to apply filter
document.addEventListener("DOMContentLoaded", () => {

    function applyFilter(filterId) {
        const imgElement = document.querySelectorAll(".open-edit-pop"); // Main image element
        // alert(filterId);
        // Remove any existing filter classes

        imgElement.forEach((filterOption) => {
            filters.forEach((filter) => filterOption.classList.remove(filter));

            filterOption.classList.add(filterId);
        });
        currentFilter = filterId;
        console.log('currentFilter ', currentFilter);

    }
    // Event listeners for filter options
    document
        .querySelectorAll('.filter-opt input[type="radio"]')
        .forEach((filterOption) => {
            filterOption.addEventListener("change", (e) => {
                // alert('b');
                applyFilter(e.target.id.toLowerCase());
            });
        });
});




// textbox js

document.addEventListener("DOMContentLoaded", () => {
    const textInput = document.getElementById("text-input");
    const addTextBtn = document.getElementById("add-text-btn");
    const fontOptions = document.getElementById("font-option");
    const selectedFontDisplay = document.getElementById("selected-font");
    const colorPicker = document.getElementById("color-picker");
    const toolmiddle = document.querySelector(".middle"); // Container for the image
    let selectedOverlay = null;

    function addTextOverlay() {
        if (!textInput.value) return;
        if (selectedOverlay) return;

        // Get the currently selected font from the dropdown
        let selectedFont = "Arial, sans-serif"; // Default
        const activeFontItem = fontOptions.querySelector(".dropdown-item.active");
        if (activeFontItem) {
            selectedFont = activeFontItem.getAttribute("data-font") || selectedFont;
        } else {
            // If no active item, use the first option as default
            const firstFontItem = fontOptions.querySelector(".dropdown-item");
            if (firstFontItem) {
                selectedFont = firstFontItem.getAttribute("data-font") || selectedFont;
            }
        }
        
        // Get the current font size from the input
        const fontSizeInput = document.getElementById("font-size");
        const fontSize = fontSizeInput ? (fontSizeInput.value + "px") : "20px";

        const textOverlay = document.createElement("div");
        textOverlay.classList.add("text-overlay");
        textOverlay.style.position = "absolute";
        textOverlay.style.top = "50%";
        textOverlay.style.left = "50%";
        textOverlay.style.transform = "translate(-50%, -50%)";
        textOverlay.style.fontFamily = selectedFont;
        textOverlay.style.fontSize = fontSize;
        textOverlay.style.color = colorPicker.value || "#000000";
        textOverlay.style.backgroundColor = "transparent";

        // Create a span for the text
        const textSpan = document.createElement("span");
        textSpan.classList.add("text-content");
        textSpan.textContent = textInput.value || "";
        textOverlay.appendChild(textSpan);

        // Add rotate handle
        const rotateHandle = document.createElement("div");
        rotateHandle.classList.add("rotate-handle");
        textOverlay.appendChild(rotateHandle);

        const rotateIcon = document.createElement("i");
        rotateIcon.classList.add("fa-rotate", "fas", "text-rotate");
        rotateIcon.title = "Rotate Text";
        rotateHandle.appendChild(rotateIcon);

        // Add remove icon
        const removeIcon = document.createElement("i");
        removeIcon.classList.add("fa-remove", "fas", "text-remove");
        removeIcon.title = "Remove Text";
        removeIcon.onclick = () => {
            textOverlay.remove();
        };
        textOverlay.appendChild(removeIcon);

        toolmiddle.appendChild(textOverlay);
        makeDraggableAndRotatable(textOverlay, rotateHandle);
        selectOverlay(textOverlay);

        // Reset text input
        // textInput.value = "";
        toggleGridDragSortonText();
    }

    // function makeDraggableAndRotatable(element, rotateHandle) {
    //     let isDragging = false;
    //     let isRotating = false;
    //     let offsetX, offsetY, centerX, centerY, initialAngle;

    //     // Dragging logic
    //     element.addEventListener("mousedown", (e) => {
    //         selectOverlay(element);
    //         textModalOpening = true;
    //         panzoomInstance.pause();
    //         sortableObj.option("disabled", true);

    //         if (e.target === rotateHandle) return;
    //         isDragging = true;
    //         offsetX = e.clientX - element.offsetLeft;
    //         offsetY = e.clientY - element.offsetTop;
    //     });

    //     document.addEventListener("mousemove", (e) => {
    //         if (isDragging) {
    //             element.style.left = `${e.clientX - offsetX}px`;
    //             element.style.top = `${e.clientY - offsetY}px`;
    //         } else if (isRotating) {
    //             const dx = e.clientX - centerX;
    //             const dy = e.clientY - centerY;
    //             const angle = Math.atan2(dy, dx) * (180 / Math.PI);
    //             const rotation = angle - initialAngle;
    //             element.style.transform = `translate(-50%, -50%) rotate(${rotation}deg)`;
    //         }
    //     });

    //     document.addEventListener("mouseup", () => {
    //         isDragging = false;
    //         isRotating = false;
    //         // not resuming the panzoomInstance and sortableObj disable false. bcz it will be done when popup will close
    //     });

    //     // Rotation logic
    //     rotateHandle.addEventListener("mousedown", (e) => {
    //         e.stopPropagation();
    //         isRotating = true;
    //         textModalOpening = true;
    //         panzoomInstance.pause();
    //         sortableObj.option("disabled", true);

    //         const rect = element.getBoundingClientRect();
    //         centerX = rect.left + rect.width / 2;
    //         centerY = rect.top + rect.height / 2;
    //         const dx = e.clientX - centerX;
    //         const dy = e.clientY - centerY;
    //         initialAngle = Math.atan2(dy, dx) * (180 / Math.PI);
    //     });

    //     // // Enable text editing on double-click
    //     // element.addEventListener("dblclick", () => {
    //     //     let textSpan = element.querySelector("span");
    //     //     textSpan.setAttribute("contenteditable", "true");
    //     //     textSpan.focus();
    //     // });

    //     // // Disable editing on blur
    //     element.addEventListener("blur", () => {
    //         // let textSpan = element.querySelector("span");
    //         // textSpan.setAttribute("contenteditable", "false");
    //     });
    // }

    function makeDraggableAndRotatable(element, rotateHandle) {
        console.log(element, rotateHandle);

        let isDragging = false;
        let isRotating = false;
        let offsetX, offsetY, centerX, centerY, initialAngle;

        // Unified start event for drag and rotation
        function startDrag(e) {
            e.preventDefault();
            const event = e.touches ? e.touches[0] : e;

            if (e.target === rotateHandle) {
                // startRotate(event);
                return;
            }
            isDragging = true;
            offsetX = event.clientX - element.offsetLeft;
            offsetY = event.clientY - element.offsetTop;

            selectOverlay(element);
            textModalOpening = true;
            panzoomInstance.pause();
            sortableObj.option("disabled", true);
        }

        // Unified move event for drag
        function dragMove(e) {
            if (!isDragging) return;

            const event = e.touches ? e.touches[0] : e;
            element.style.left = `${event.clientX - offsetX}px`;
            element.style.top = `${event.clientY - offsetY}px`;
        }

        // Start rotating
        function startRotate(e) {
            e.stopPropagation();
            isRotating = true;
            const event = e.touches ? e.touches[0] : e;

            const rect = element.getBoundingClientRect();
            centerX = rect.left + rect.width / 2;
            centerY = rect.top + rect.height / 2;

            const dx = event.clientX - centerX;
            const dy = event.clientY - centerY;
            initialAngle = Math.atan2(dy, dx) * (180 / Math.PI);

            textModalOpening = true;
            panzoomInstance.pause();
            sortableObj.option("disabled", true);
        }

        // Unified move event for rotation
        function rotateMove(e) {
            if (!isRotating) return;

            const event = e.touches ? e.touches[0] : e;
            const dx = event.clientX - centerX;
            const dy = event.clientY - centerY;
            const angle = Math.atan2(dy, dx) * (180 / Math.PI);
            const rotation = angle - initialAngle;

            element.style.transform = `translate(-50%, -50%) rotate(${rotation}deg)`;
        }

        // Stop drag or rotation
        function stopAction() {
            isDragging = false;
            isRotating = false;
            // not resuming the panzoomInstance and sortableObj disable false. bcz it will be done when popup will close
        }

        // Event Listeners
        // Drag and rotation events (Mouse and Touch)
        element.addEventListener("mousedown", startDrag);
        element.addEventListener("touchstart", startDrag, { passive: false });

        document.addEventListener("mousemove", dragMove);
        document.addEventListener("touchmove", dragMove, { passive: false });

        rotateHandle.addEventListener("mousedown", startRotate);
        rotateHandle.addEventListener("touchstart", startRotate, { passive: false });

        document.addEventListener("mousemove", rotateMove);
        document.addEventListener("touchmove", rotateMove, { passive: false });

        document.addEventListener("mouseup", stopAction);
        document.addEventListener("touchend", stopAction);
    }

    $txtModal = $("#Text_popup");
    // Function to select an overlay
    function selectOverlay(overlay) {
        $(".text-overlay").removeClass("selected");

        selectedOverlay = overlay;
        selectedOverlay.classList.add("selected");
        $(selectedOverlay).find('.rotate-handle, .text-remove').show()

        $("#text-input").val($(selectedOverlay).find('span').html());
        $("#add-text-btn").html("Update Text");
        $txtModal.modal('show');
        toggleGridDragSortonText();
    }


    $txtModal.on('shown.bs.modal', function () {
        //
    }).on('hidden.bs.modal', function () {
        selectedOverlay = null;
        // console.log($(".text-overlay"), $(".text-overlay.selected"));
        $(".text-overlay.selected").removeClass("selected");
        textModalOpening = false;
        toggleGridDragSortonText();

        $(".text-overlay .rotate-handle, .text-overlay .text-remove").hide();
    });


    // Add event listener for "Add Text" button
    addTextBtn.addEventListener("click", addTextOverlay);

    // // Apply selected font style to the selected text overlay
    // fontOptions.addEventListener("change", (event) => {
    //     if (selectedOverlay) {
    //         const selectedFont = event.target.options[event.target.selectedIndex].getAttribute(
    //             "data-font");
    //         selectedOverlay.style.fontFamily = selectedFont;
    //     }
    // });

    // Listen for clicks on the dropdown menu items
    fontOptions.addEventListener("click", (event) => {
        // Check if the clicked element is a dropdown item
        if (event.target.classList.contains("dropdown-item")) {
            if (selectedOverlay) {
                // Get the selected font and update the overlay
                const selectedFont = event.target.getAttribute("data-font");
                selectedOverlay.style.fontFamily = selectedFont;

                // Update the dropdown toggle text with the selected font
                const selectedText = event.target.textContent.trim();
                selectedFontDisplay.textContent = selectedText;

                // Optional: Highlight the selected font in the dropdown menu
                document.querySelectorAll("#font-option .dropdown-item").forEach(item => {
                    item.classList.remove("active"); // Remove active class from all items
                });
                event.target.classList.add("active"); // Add active class to the selected item
            }
        }
    });


    // Change text on selected text overlay
    textInput.addEventListener("input", () => {
        if (selectedOverlay) {
            $(selectedOverlay).find('span').html(textInput.value);
        }
    });

    // Change font size on selected text overlay
    let fontSize = document.getElementById("font-size");
    fontSize.addEventListener("input", () => {
        if (selectedOverlay) {
            selectedOverlay.style.fontSize = fontSize.value + 'px';
        }
    });

    // Change text color of the selected text overlay
    colorPicker.addEventListener("input", () => {
        if (selectedOverlay) {
            selectedOverlay.style.color = colorPicker.value;
        }
    });
    document.getElementById("clear-color-btn").addEventListener("click", () => {
        if (selectedOverlay) {
            selectedOverlay.style.color = "#000000";
        }
    });
});

function openTextPopup() {
    // if (selectedOverlay) {
    //     $("#text-input").val($(selectedOverlay).find('span').html());
    //     $("#add-text-btn").html("Update Text");
    // } else {
    // }
    $("#text-input").val('');
    $("#add-text-btn").html("Add Text");
    $("#Text_popup").modal('show');
}