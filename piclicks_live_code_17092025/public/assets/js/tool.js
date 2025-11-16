// file upload
// document
//     .getElementById("image-input")
//     .addEventListener("change", function (event) {
//         const previewGrid = document.getElementById("preview-grid");
//         const files = Array.from(event.target.files);
//         // Determine grid layout based on number of images
//         if (files.length <= 8) {
//             previewGrid.className = "grid-2";
//         } else if (files.length <= 20) {
//             previewGrid.className = "grid-2x4";
//         } else {
//             previewGrid.className = "grid-3";
//         }
//         // Clear any existing images
//         previewGrid.innerHTML = "";
//         // Create image previews and add click event to open off-canvas editor
//         files.forEach((file) => {
//             const reader = new FileReader();
//             reader.onload = function (e) {
//                 const img = document.createElement("img");
//                 img.src = e.target.result;
//                 img.classList.add("image-item");
//                 img.addEventListener("click", () => openOffCanvas(img)); // Open editor on click
//                 previewGrid.appendChild(img);
//             };
//             reader.readAsDataURL(file);
//         });
//     });
// file upload end
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
let maxViewportWidth = 650;
let maxViewportHeight = 570;
let viewportWidth = 0;
let viewportHeight = 0;
let originalSrcOnPopup = '';
document.querySelector(".toolzoomrow").addEventListener("click", function (event) {
    const previewGrid = document.getElementById("preview-grid");
    if (!previewGrid.contains(event.target)) { // Check if the click is outside the preview-grid
        document.querySelectorAll(".image-div").forEach((div) => {
            div.classList.remove("selected");
            $(".resize-handle").remove();
        });
        toggleSortable();
        $(".upload-image, .delete-image, .size-image").addClass('disabled');
    }
});
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
toolzoom.addEventListener("mousedown", onDragStart);
toolzoom.addEventListener("mousemove", onDragMove);
toolzoom.addEventListener("mouseup", onDragEnd);
// Attach drag handlers for touch events
toolzoom.addEventListener("touchstart", onDragStart);
toolzoom.addEventListener("touchmove", onDragMove);
toolzoom.addEventListener("touchend", onDragEnd);
let isModalOpening = false;
let textModalOpening = false;
// Ensure that the image is updated in the preview grid after cropping and saving
document.addEventListener("DOMContentLoaded", function () {
    let window_width = $(window).width();
    if (window_width < 575) {
        maxViewportWidth = 342;
        maxViewportHeight = 300;
    } else if (window_width < 767) {
        maxViewportWidth = 456;
        maxViewportHeight = 400;
    }
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
        document.querySelectorAll(".image-div").forEach((div) => {
            div.classList.remove("selected");
            $(".resize-handle").remove();
        });
        let parentDiv = event.target.closest(".image-div");
        if (parentDiv) {
            parentDiv.classList.add("selected");
            toggleSortable();
            $(".size-image").addClass('disabled');
            if (event.target.classList.contains("open-edit-pop")) {
                showResizeHandle(parentDiv);
                $(".delete-image, .size-image").removeClass('disabled');
                $(".upload-image").addClass('disabled');
            }
            if (event.target.classList.contains("select-image-pop")) {
                $(".upload-image").removeClass('disabled');
                $(".delete-image").addClass('disabled');
            }
            if (now - lastTapTime < doubleTapThreshold) {
                console.log("Double-tap detected");
            } else {
                lastTapTime = now;
                return;
            }
            lastTapTime = now;
            if (event.target.classList.contains("open-edit-pop")) {
                let originalImage = parentDiv.querySelector(".image-item-original");
                // let originalImage = parentDiv.querySelector(".image-item");
                if (originalImage) {
                    originalSrcOnPopup = originalImage.src;
                    isModalOpening = true;
                    let divWidth = parentDiv.offsetWidth;
                    let divHeight = parentDiv.offsetHeight;
                    console.log(`Clicked div size: ${divWidth}x${divHeight}`);
                    if (divWidth == 91 && divHeight == 80) {
                        if (window_width < 575) {
                            viewportWidth = 342;
                            viewportHeight = 300;
                        } else if (window_width < 767) {
                            viewportWidth = 456;
                            viewportHeight = 400;
                        } else {
                            viewportWidth = 570;
                            viewportHeight = 500;
                        }
                    } else {
                        // Calculate viewport dimensions
                        viewportWidth = Math.min(divWidth * 2, maxViewportWidth); // Scale up to 2x the div width, max 500px
                        viewportHeight = Math.min(divHeight * 2, maxViewportHeight); // Scale up to 2x the div height, max 700px
                        // Maintain the 1.14:1 aspect ratio if needed
                        const aspectRatio = divWidth / divHeight;
                        if (viewportWidth / viewportHeight > aspectRatio) {
                            viewportWidth = viewportHeight * aspectRatio;
                        } else {
                            viewportHeight = viewportWidth / aspectRatio;
                        }
                        console.log(`Viewport size: ${viewportWidth}x${viewportHeight}`);
                    }
                    // Set image container dimensions
                    $(".image-container").css({
                        width: `${viewportWidth}px`,
                        height: `${viewportHeight}px`,
                    });
                    let wid = parseInt($(parentDiv).css("width").replace("px", "") / actualWidth);
                    let hei = parseInt($(parentDiv).css("height").replace("px", "") / actualHeight);
                    $(".image-container").find("svg").remove();
                    if (wid !== 1 || hei !== 1) {
                        if (currentFrame !== '') {
                            $(".image-container").find("svg").remove();
                        }
                        // Apply clip-path for image editor modal
                        const { svg, clipPathId } = createTileClipPath(
                            viewportWidth,
                            viewportHeight,
                            hei,
                            wid,
                            { mode: "container" }
                        );
                        $(".image-container").append(svg);
                        $(".image-container")[0].style.clipPath = `url(#${clipPathId})`;
                        $(".image-container")[0].dataset.clipPathId = clipPathId;
                    }
                    $("#mainLoader").show();
                    setCroppieImage(originalSrcOnPopup);
                }
            }
        }
    });
});
function setCroppieImage(originalSrc) {
    let imageEdit = $("#image_edit");
    imageEdit.attr('src', originalSrc);
    let parentDiv = $(".image-div.selected")[0];
    imageEdit.off('load').on('load', async function () {
        popup_image_croppie = $("#image_edit").croppie({
            viewport: {
                width: viewportWidth,
                height: viewportHeight,
                // type: 'square'
            },
            boundary: {
                width: viewportWidth,
                height: viewportHeight
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
                viewportWidth / imgWidth, // Scale for width
                viewportHeight / imgHeight // Scale for height
            );
            scaleMin = parseFloat(scaleMin.toFixed(2));
            console.log('scaleMin ', scaleMin);
            popup_image_croppie.croppie('bind', {
                url: originalSrc
            }).then(async function () {
                // ----- store rotate in hidden to show again rotated image when open again -----
                rotation_croppie = parseInt(parentDiv.querySelector(".image-item-rotate").value);
                // if(rotation_croppie !== 1) {
                for (let r = 1; r < rotation_croppie; r++) {
                    popup_image_croppie.croppie('rotate', -90);
                }
                // }
                popup_image_croppie.croppie('rotate', -90);
                popup_image_croppie.croppie('rotate', 90);
                // ----- logic to store zoom - previously stored in hidden of original image -----
                let zooom = parentDiv.querySelector(".image-item-zoom").value;
                if (zooom !== '0') {
                    zooomarr = zooom.split("|");
                    zoom_croppie = parseFloat(zooomarr[0]);
                    min_zoom_croppie = parseFloat(zooomarr[1]);
                    popup_image_croppie.croppie('setZoom', zoom_croppie);
                } else {
                    popup_image_croppie.croppie('setZoom', scaleMin);
                    min_zoom_croppie = scaleMin;
                    zoom_croppie = scaleMin;
                }
                if (scaleMin > 1) {
                    max_zoom_croppie = scaleMin * 2;
                } else {
                    max_zoom_croppie = 1.5;
                }
                // ----- solution of issue when image is small then viewport -----
                $('.cr-slider').attr('max', max_zoom_croppie);
                // await new Promise(resolve => setTimeout(resolve, 500));
                // popup_image_croppie.croppie('setZoom', scaleMin);
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
    zoom_croppie += 0.03;
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
    zoom_croppie -= 0.03;
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
        document.querySelectorAll(".image-div").forEach((div) => {
            div.classList.remove("selected")
            $(".resize-handle").remove();
        });
    });
    $(".image-div.selected").find(".image-item-zoom").val(zoom_croppie + "|" + min_zoom_croppie);
    $(".image-div.selected").find(".image-item-rotate").val(rotation_croppie);
    popup_image_croppie.croppie('destroy');
    $(".upload-image, .delete-image, .size-image").addClass('disabled');
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
    document.querySelectorAll(".image-div").forEach((div) => {
        div.classList.remove("selected")
        $(".resize-handle").remove();
    });
    popup_image_croppie.croppie('destroy');
    $("#singleImageModal").modal('hide');
    var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasRight"));
    if (offcanvas) {
        offcanvas.hide();
    }
});
document.querySelector('#offcanvasRight #closeButton').addEventListener('click', function () {
    popup_image_croppie.croppie('destroy');
    $(".upload-image, .delete-image, .size-image").addClass('disabled');
    document.querySelectorAll(".image-div").forEach((div) => {
        div.classList.remove("selected");
        $(".resize-handle").remove();
    });
    $("#singleImageModal").modal('hide');
    var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasRight"));
    if (offcanvas) {
        offcanvas.hide();
    }
});

let panzoomInstance;
let panzoomInstance1;
let initialXY = [0, 0];
let zoom_grid = 0;
let start_zoom_grid = 0;
let min_zoom_grid = 0.1;
let max_zoom_grid = 5;
// function to re arrange image orders
let sortableElement = document.querySelector('.sortable');
let sortableObj;

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
    
    // Only initialize sortableObj if sortableElement exists
    if (sortableElement) {
        sortableObj = Sortable.create(sortableElement, {
            animation: 150,
            swap: true,
            swapClass: 'highlight',
            // onChoose: function (evt) {
            //     console.log("Sorting chosen");
            // },
            onStart: function (evt) {
                // console.log("Sorting start");
            },
            onEnd: async function (evt) {
                console.log("Sorting stop");
                // console.log(`Moved item ${evt.item.id} from ${evt.oldIndex} to ${evt.newIndex}`);
                await calcGridDimension()
                placeTiles();
            },
        });
    } else {
        console.warn("Sortable element not found, sortableObj not initialized");
    }
});

function toggleSortable() {
    const hasSelected = $(".sortable .image-div.selected").length > 0;
    if (hasSelected) {
        panzoomInstance.pause();
        // sortableElement.sortable("enable");
        if (sortableObj) {
            sortableObj.option("disabled", false);
            console.log("Sortable enabled");
        }
    } else {
        panzoomInstance.resume();
        // sortableElement.sortable("disable");
        if (sortableObj) {
            sortableObj.option("disabled", true);
            console.log("Sortable disabled");
        }
        if ($(".text-overlay.selected").length > 0) {
            panzoomInstance.pause();
            if (sortableObj) {
                sortableObj.option("disabled", true);
                console.log("text functionality enabled in sortable disabled");
            }
        }
    }
}

function toggleGridDragSortonText() {
    const hasSelected = $(".text-overlay.selected").length > 0;
    if (hasSelected) {
        panzoomInstance.pause();
        if (sortableObj) {
            sortableObj.option("disabled", true);
            console.log("text functionality enabled");
        }
    } else {
        panzoomInstance.resume();
        if (sortableObj) {
            sortableObj.option("disabled", false);
            console.log("text functionality disabled");
        }
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
    if (window_width < 400) { // mobile
        zoom_grid = 0.55;
        initialXY = [330, 300]
    }
    else if (window_width < 435) { // tablet
        zoom_grid = 0.7;
        initialXY = [350, 240]
    }
    else if (window_width < 575) { // tablet
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
    else if (window_width < 1280) { // behlah pc
        zoom_grid = 0.55;
        initialXY = [270, 300]
    }
    else if (window_width < 1400) { // mid sizes pc
        zoom_grid = 0.58;
        initialXY = [270, 300]
    }
    else if (window_width < 1480) { // sakshi & client pc
        zoom_grid = 0.75;
        initialXY = [270, 300]
    }
    else if (window_width < 1580) { // client pc
        zoom_grid = 0.9;
        initialXY = [270, 300]
    }
    else if (window_width < 1700) { // utkarsh pc
        zoom_grid = 0.75;
        initialXY = [500, 400]
    } else { // utkarsh pc
        zoom_grid = 0.90;
        initialXY = [200, 300]
    }
    start_zoom_grid = zoom_grid;
    const elem = document.getElementById('toolzoom');
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
    toggleSortable();
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

let actualWidth = 91;
let actualHeight = 80;
let actualMargin = 2;
// let visibleWidth = 14.37;
// let visibleHeight = 12.6;
let visibleWidth = 14;
let visibleHeight = 12;
let visibleMargin = 0.5;
let totalloop = [];
let image_div_html;

document.addEventListener("DOMContentLoaded", function () {
    // <svg width="91" height="80" class="tile-svg" viewBox="0 0 91 80" preserveAspectRatio="xMidYMid slice">
    //     <line x1="0" x2="0" y1="0" y2="80" stroke-width="1" stroke="white" opacity="0.5"/>
    //     <line x1="91" x2="91" y1="0" y2="80" stroke-width="1" stroke="white" opacity="0.5"/>
    //     <line x1="0" x2="91" y1="0" y2="0" stroke-width="1" stroke="white" opacity="0.5"/>
    //     <line x1="0" x2="91" y1="80" y2="80" stroke-width="1" stroke="white" opacity="0.5"/>
    // </svg>
    image_div_html = `
    <div class="image-div">
        <img src="`+ greyImage + `" alt=""
            class="image-item-original d-none">
        <img src="`+ greyImage + `" alt=""
            class="select-image-pop image-item">
        <input type="hidden" class="image-item-zoom" value="0">
        <input type="hidden" class="image-item-rotate" value="1">
        <input type="hidden" class="image-item-id" value="0">
    </div>`;
    let tiles = document.querySelectorAll(".image-div");
    tiles.forEach((tile) => {
        let tileWidth = parseInt(window.getComputedStyle(tile).width);
        let tileHeight = parseInt(window.getComputedStyle(tile).height);
        let grid_cols = Math.round(tileWidth / actualWidth);
        let grid_rows = Math.round(tileHeight / actualHeight);
        if (grid_cols !== 1 || grid_rows !== 1) {
            // Apply clip-path for stretched images
            const { svg, clipPathId } = createTileClipPath(tileWidth, tileHeight, grid_rows, grid_cols);
            $(tile).find('svg').remove(); // Remove old SVG if exists
            $(tile).append(svg);
            tile.style.clipPath = `url(#${clipPathId})`;
            tile.dataset.clipPathId = clipPathId;
        }
    });
    calcGridDimension()
    placeTiles();
    test_tile = 1;
});

let test_tile = 0;
async function calcGridDimension() {
    console.log('calcGridDimension started');
    let columns = parseInt(document.getElementById("grid_columns").value);
    let rows = parseInt(document.getElementById("grid_rows").value);
    let colmargins = visibleMargin * (columns - 1);
    let rowmargins = visibleMargin * (rows - 1);
    $(".layout-horiz-size span").html((visibleWidth * columns + colmargins).toFixed(0));
    $(".layout-vert-size span").html((visibleHeight * rows + rowmargins).toFixed(0));
    $("#preview-grid").css("width", `${actualMargin + ((actualWidth + actualMargin) * columns)}px`);
    $("#preview-grid").css("height", `${actualMargin + ((actualHeight + actualMargin) * rows)}px`);
    $("#preview-grid").css('position', 'relative')
    if (test_tile === 0) {
        // $("#preview-grid .image-div:nth-child(24)").css('width', '277px')
        // $("#preview-grid .image-div:nth-child(24)").css('height', '162px')
        test_tile = 1;
    }
    $("#preview-grid .image-div").each(function (index, element) {
        $(element).removeClass(function (indexx, className) {
            return (className.match(/\btile-\d+\b/g) || []).join(" ");
        });
    });
    totalloop = [];
    let tileindex = 1;
    await $("#preview-grid .image-div").each(function (index, element) {
        let wid = parseInt($(element).css("width").replace("px", "") / actualWidth);
        let hei = parseInt($(element).css("height").replace("px", "") / actualHeight);
        if (wid === 1 && hei === 1) {
            // console.log("if ", tileindex, element);
            while (totalloop.hasOwnProperty(tileindex)) {
                tileindex++; // Keep incrementing until we find an unused index
            }
            totalloop[tileindex] = 0;
            $(element).addClass("tile-" + tileindex);
            tileindex++;
        } else {
            // console.log("else ", tileindex, element);
            totalloop[tileindex] = 0;
            $(element).addClass("tile-" + tileindex);
            tileindex++;
            let widloop = wid - 1;
            for (let w1 = 1; w1 <= widloop; w1++) {
                totalloop[tileindex] = 1;
                tileindex++;
            }
            let heiloop = hei - 1;
            let skipindex = columns - wid;
            let heiindex = tileindex;
            for (let h1 = 1; h1 <= heiloop; h1++) {
                heiindex = heiindex + skipindex;
                for (let w2 = 1; w2 <= wid; w2++) {
                    totalloop[heiindex] = 1;
                    heiindex++;
                }
            }
        }
    });
    console.log(totalloop);
}
function placeTiles() {
    console.log('placeTiles started');
    let columns = parseInt(document.getElementById("grid_columns").value);
    let rows = parseInt(document.getElementById("grid_rows").value);
    // Initialize grid mapping with false (empty)
    let grid_map = Array.from({ length: rows }, () => Array(columns).fill(false));
    let tiles = document.querySelectorAll(".image-div");
    let margin = actualMargin; // Defined globally as 2px
    // console.log("placeTiles started");
    tiles.forEach((tile, index) => {
        let tileWidth = parseInt(window.getComputedStyle(tile).width);
        let tileHeight = parseInt(window.getComputedStyle(tile).height);
        // Convert pixel dimensions to grid size
        let grid_width = Math.round(tileWidth / actualWidth);
        let grid_height = Math.round(tileHeight / actualHeight);
        // Find first empty position that fits the tile
        let found = false;
        let startRow = 0, startCol = 0;
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < columns; c++) {
                if (canFit(grid_map, r, c, grid_width, grid_height, rows, columns)) {
                    // console.log("Fits at", r, c);
                    startRow = r;
                    startCol = c;
                    found = true;
                    break;
                } else {
                    // console.log("Doesn't fit at", r, c);
                }
            }
            if (found) break;
        }
        // Mark occupied space in grid_map
        for (let r = startRow; r < startRow + grid_height; r++) {
            for (let c = startCol; c < startCol + grid_width; c++) {
                grid_map[r][c] = true;
            }
        }
        // Convert grid position to pixel values
        let x = startCol * (actualWidth + margin);
        let y = startRow * (actualHeight + margin);
        // if (x === 0 && y === 0 && index !== 0) {
        //     $(".add-bottom").click();
        //     // $(".add-right").click();
        //     console.log('add bottom and right run placeTiles');
        //     placeTiles();
        // }
        // console.log(tile, x, y, startCol, startRow, grid_width, grid_height);
        // Apply positioning
        tile.style.position = "absolute";
        tile.style.left = `${x}px`;
        tile.style.top = `${y}px`;
        // Remove previous border classes
        tile.classList.remove("top-b", "right-b", "bottom-b", "left-b");
        // Check if tile is on any border and add respective class
        if (startRow === 0) tile.classList.add("top-b"); // Top border
        if (startCol + grid_width === columns) tile.classList.add("right-b"); // Right border
        if (startRow + grid_height === rows) tile.classList.add("bottom-b"); // Bottom border
        if (startCol === 0) tile.classList.add("left-b"); // Left border
    });
    console.log("placeTiles finished");
    // // COMMENTED BECAUSE ACCORIDNG TO ME(BEHLAH) TOOL IS SLOWED DOWN 25-04-2025. NOT REPORTED BY CLIENT TILL NOW
    // saveCollage('auto'); 
}
// Function to check if a tile fits in the available grid space
function canFit(grid_map, startRow, startCol, width, height, maxRows, maxCols) {
    if (startRow + height > maxRows || startCol + width > maxCols) return false;
    for (let r = startRow; r < startRow + height; r++) {
        for (let c = startCol; c < startCol + width; c++) {
            if (grid_map[r][c]) return false; // Already occupied
        }
    }
    return true;
}
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
    placeTiles()
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
    placeTiles()
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
    placeTiles()
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
    placeTiles()
});
// Add a blank column on the left
$(".add-left").click(function () {
    // // Add a blank cell to the beginning of each row
    // $("#preview-grid .image-div").each(function (index, element) {
    //     if (index % grid_columns === 0) {
    //         $(this).before(image_div_html);
    //     }
    // });
    let newCells = []; // Store newly added cells
    let newCell = $(image_div_html).insertBefore($(".image-div.tile-1"));
    newCells.push(newCell);
    for (let z = 1; z <= totalloop.length; z++) {
        if (z % grid_columns === 0) {
            let y = z + 1;
            if (totalloop[y] === 1) {
                let aa = y;
                for (; aa <= totalloop.length; aa++) {
                    if (totalloop[aa] === 0) {
                        break;
                    }
                }
                let newCell = $(image_div_html).insertBefore($(".image-div.tile-" + aa));
                newCells.push(newCell);
            } else {
                let newCell = $(image_div_html).insertBefore($(".image-div.tile-" + (y)));
                newCells.push(newCell);
            }
        }
    }
    // return false;
    // Add a blank cell to the end of each row
    // $("#preview-grid .image-div.left-b").each(function (index, element) {
    //     let newCell = $(image_div_html).insertBefore($(this)); // Insert after existing column
    //     newCells.push(newCell);
    // });
    // Apply CSS styling only to the newly added elements
    let colors = ['red', 'blue', 'yellow', 'green', 'orange', 'purple', 'pink', 'brown', 'black', 'white'];
    let newc = colors[Math.floor(Math.random() * colors.length)];
    newCells.forEach((cell, index) => {
        cell.css({
            // "border": "2px solid " + newc,
        });
    });
    grid_columns += 1;
    $("#grid_columns").val(grid_columns);
    calcGridDimension()
    placeTiles()
});
// Remove a column from the left
$(".minus-left").click(function () {
    let edit_pop = false;
    // $("#preview-grid .image-div").filter((index) => index % grid_columns === 0).each(function () {
    //     if ($(this).find("img.open-edit-pop").length > 0) {
    //         edit_pop = true;
    //     }
    // });
    // $("#preview-grid .image-div").filter((index) => index % grid_columns === 0).remove();
    $("#preview-grid .image-div.left-b").each(function () {
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
    $("#preview-grid .image-div.left-b").remove();
    grid_columns -= 1;
    $("#grid_columns").val(grid_columns);
    calcGridDimension()
    placeTiles()
});
// Add a blank column on the right
$(".add-right").click(function () {
    let newCells = []; // Store newly added cells
    // Add a blank cell to the end of each row
    for (let z = 1; z <= totalloop.length; z++) {
        if (z % grid_columns === 0) {
            if (totalloop[z] === 1) {
                let aa = z;
                for (; aa >= 1; aa--) {
                    if (totalloop[aa] === 0) {
                        break;
                    }
                }
                let newCell = $(image_div_html).insertAfter($(".image-div.tile-" + aa));
                newCells.push(newCell);
            } else {
                let newCell = $(image_div_html).insertAfter($(".image-div.tile-" + z));
                newCells.push(newCell);
            }
        }
    }
    // return false;
    // // Add a blank cell to the end of each row
    // $("#preview-grid .image-div").each(function (index, element) {
    //     if ((index + 1) % grid_columns === 0) {
    //         $(this).after(image_div_html);
    //     }
    // });
    // Add a blank cell to the end of each row
    // $("#preview-grid .image-div.right-b").each(function (index, element) {
    //     let newCell = $(image_div_html).insertAfter($(this)); // Insert after existing column
    //     newCells.push(newCell);
    // });
    // Apply CSS styling only to the newly added elements
    let colors = ['red', 'blue', 'yellow', 'green', 'orange', 'purple', 'pink', 'brown', 'black', 'white'];
    let newc = colors[Math.floor(Math.random() * colors.length)];
    newCells.forEach((cell, index) => {
        cell.css({
            // "border": "2px solid " + newc,
        });
    });
    // for (let i = 0; i < rows; i++) {
    //     let newCell = $(image_div_html);
    //     $("#preview-grid .image-div").eq(i * columns + (columns - 1)).after(newCell);
    //     newCells.push(newCell);
    // }
    grid_columns += 1;
    $("#grid_columns").val(grid_columns);
    calcGridDimension()
    placeTiles()
});
// Remove a column from the right
$(".minus-right").click(function () {
    let edit_pop = false;
    // $("#preview-grid .image-div").filter((index) => (index + 1) % grid_columns === 0).each(function () {
    //     if ($(this).find("img.open-edit-pop").length > 0) {
    //         edit_pop = true;
    //     }
    // });
    // $("#preview-grid .image-div").filter((index) => (index + 1) % grid_columns === 0).remove();
    $("#preview-grid .image-div.right-b").each(function (index, element) {
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
    $("#preview-grid .image-div.right-b").remove();
    grid_columns -= 1;
    $("#grid_columns").val(grid_columns);
    calcGridDimension()
    placeTiles()
});
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
                let wid = parseInt(selected.css("width").replace("px", "") / actualWidth);
                let hei = parseInt(selected.css("height").replace("px", "") / actualHeight);
                let left = selected.css("left").replace("px", "");
                let top = selected.css("top").replace("px", "");
                if (wid === 1 && hei === 1) {
                    $(image_div_html).insertAfter(selected);
                    selected.remove();
                } else {
                    selected.remove();
                    for (let i = 1; i <= (wid * hei); i++) {
                        $("#preview-grid").append(image_div_html);
                    }
                }
                document.querySelectorAll(".image-div").forEach((div) => {
                    div.classList.remove("selected");
                    $(".resize-handle").remove();
                });
                placeTiles();
            }
        });
    }
})
let selectedElementForUpload;
document.getElementById("image-input").addEventListener("change", function (event) {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            let imgsrc = e.target.result;
            selectedElementForUpload.removeClass("success-black-outlined success-white-outlined");
            document.getElementById("preview-grid").classList.remove("grid-framed");
            if (currentFrame !== '') {
                selectedElementForUpload.addClass(currentFrame);
                document.getElementById("preview-grid").classList.add("grid-framed");
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
            document.querySelectorAll(".image-div").forEach((div) => {
                div.classList.remove("selected");
                $(".resize-handle").remove();
            });
        };
        reader.readAsDataURL(file);
    });
});

document.querySelector(".upload-image").addEventListener("click", () => {
    if ($(".image-div.selected").find(".image-item.select-image-pop").length === 1) {
        selectedElementForUpload = $(".image-div.selected");
        $("#image-input").click();
    }
})


let selectedElementForSize = null;
document.querySelector(".size-image").addEventListener("click", () => {
    if ($(".image-div.selected").length === 1) {
        selectedElementForSize = $(".image-div.selected");
        $("#tile-width,#tile-height").html('');
        let grid_columns = parseInt(document.getElementById("grid_columns").value);
        let grid_rows = parseInt(document.getElementById("grid_rows").value);
        let selectedLeft = parseInt(selectedElementForSize.css("left")) / (actualWidth + actualMargin);
        let selectedTop = parseInt(selectedElementForSize.css("top")) / (actualHeight + actualMargin);
        let selectedForSizeWidth = parseInt(selectedElementForSize.css("width"));
        let selectedForSizeHeight = parseInt(selectedElementForSize.css("height"));
        let startCol = Math.round(selectedLeft);
        let startRow = Math.round(selectedTop);
        let maxWidthFactor = grid_columns - startCol;
        let maxHeightFactor = grid_rows - startRow;
        for (let i = 1; i <= maxWidthFactor; i++) {
            let widthOption = (actualWidth * i) + ((i - 1) * actualMargin);
            $("#tile-width").append(`<option value="${actualWidth * i}" ${selectedForSizeWidth === widthOption ? "selected" : ""}>${visibleWidth * i} cm</option>`);
        }
        for (let i = 1; i <= maxHeightFactor; i++) {
            let heightOption = (actualHeight * i) + ((i - 1) * actualMargin);
            $("#tile-height").append(`<option value="${actualHeight * i}" ${selectedForSizeHeight === heightOption ? "selected" : ""}>${visibleHeight * i} cm</option>`);
        }
        $("#TileSize_popup").modal('show');
    }
})



document.querySelector("#change-size-btn").addEventListener("click", () => {
    let selectedForSizeWidth = parseInt(selectedElementForSize.css("width").replace("px", ""));
    let selectedForSizeHeight = parseInt(selectedElementForSize.css("height").replace("px", ""));
    let selectedForSizeMargin = selectedElementForSize.data("margin");
    if (selectedForSizeMargin !== '' && selectedForSizeMargin !== undefined) {
        let mar = selectedForSizeMargin.split('|');
        selectedForSizeWidth = selectedForSizeWidth - parseInt(mar[0]);
        selectedForSizeHeight = selectedForSizeHeight - parseInt(mar[1]);
    }
    let setW = parseInt($("#tile-width").val());
    let setH = parseInt($("#tile-height").val());
    let oldW = selectedForSizeWidth / actualWidth;
    let oldH = selectedForSizeHeight / actualHeight;
    let oldGrid = oldW * oldH;
    let newW = setW / actualWidth;
    let newH = setH / actualHeight;
    let newGrid = newW * newH;
    let changeCount = oldGrid - newGrid;
    console.log("Change count:", changeCount);
    if (changeCount < 0) {
        let absChange = parseInt(Math.abs(changeCount).toFixed(0));
        let removed = 0;
        // Remove empty tiles first
        $("#preview-grid .image-div").get().reverse().forEach(el => {
            if (removed < absChange) {
                if ($(el).find(".select-image-pop").length === 1) {
                    $(el).remove();
                    removed++;
                }
            }
        });
        // If not enough empty tiles, remove filled tiles from the bottom
        if (removed < absChange) {
            $("#preview-grid .image-div").get().reverse().forEach(el => {
                if (removed < absChange) {
                    $(el).remove();
                    removed++;
                }
            });
        }
    } else if (changeCount > 0) {
        for (let i = 0; i < changeCount; i++) {
            $("#preview-grid").append(image_div_html);
        }
    }
    // selectedElementForSize.animate({
    //     width: (actualWidth * newW) + "px",
    //     height: (actualHeight * newH) + "px"
    // }, 300)
    // setTimeout(() => {
    //     placeTiles(); // Recalculate grid layout
    // }, 400);
    let marginW, marginH = 0;
    // if (changeCount < 0) {
    marginW = (newW - 1) * actualMargin;
    marginH = (newH - 1) * actualMargin;
    // }
    selectedElementForSize.css("width", (actualWidth * newW) + marginW);
    selectedElementForSize.css("height", (actualHeight * newH) + marginH);
    selectedElementForSize.attr("data-margin", marginW + '|' + marginH);
    selectedElementForSize.find("svg").remove();
    
    // Check what frame class is currently applied (before any changes)
    const hasBlackFrame = selectedElementForSize.classList.contains('success-black-outlined');
    const hasWhiteFrame = selectedElementForSize.classList.contains('success-white-outlined');
    
    if (newW !== 1 || newH !== 1) {
        // Apply clip-path for resized stretched image
        const { svg, clipPathId } = createTileClipPath((actualWidth * newW) + marginW, (actualHeight * newH) + marginH, newH, newW);
        $(selectedElementForSize).append(svg);
        selectedElementForSize.style.clipPath = `url(#${clipPathId})`;
        selectedElementForSize.dataset.clipPathId = clipPathId;
        
        // Ensure frame class remains applied (don't remove it)
        if (hasBlackFrame) {
            selectedElementForSize.classList.add('success-black-outlined');
        }
        if (hasWhiteFrame) {
            selectedElementForSize.classList.add('success-white-outlined');
        }
    } else {
        // Single tile - remove clip-path
        selectedElementForSize.style.clipPath = 'none';
        delete selectedElementForSize.dataset.clipPathId;
        
        // Ensure frame class remains applied for single tile
        if (hasBlackFrame) {
            selectedElementForSize.classList.add('success-black-outlined');
        }
        if (hasWhiteFrame) {
            selectedElementForSize.classList.add('success-white-outlined');
        }
    }
    setTimeout(async () => {
        await calcGridDimension()
        placeTiles(); // Recalculate grid layout
    }, 500);
});

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
                // Remove all frame classes
                imgDivElement.classList.remove("success-black-outlined", "success-white-outlined");
                // Remove any previous SVG
                const svgEl = imgDivElement.querySelector("svg");
                if (svgEl) svgEl.remove();

                // Get grid size
                let tileWidth = parseInt(window.getComputedStyle(imgDivElement).width);
                let tileHeight = parseInt(window.getComputedStyle(imgDivElement).height);
                let grid_cols = Math.round(tileWidth / actualWidth);
                let grid_rows = Math.round(tileHeight / actualHeight);

                // Only apply clip-path if grid_cols > 1 || grid_rows > 1
                if ((grid_cols > 1 || grid_rows > 1)) {
                    // Apply clip-path for stretched images with or without frame
                    const { svg, clipPathId } = createTileClipPath(tileWidth, tileHeight, grid_rows, grid_cols);
                    imgDivElement.appendChild(svg);
                    imgDivElement.style.clipPath = `url(#${clipPathId})`;
                    imgDivElement.dataset.clipPathId = clipPathId;
                    
                    // Apply frame class for border styling
                    if (frameId !== "none-outlined") {
                        imgDivElement.classList.add(frameId);
                    }
                } else if (frameId !== "none-outlined") {
                    // Single tile - add frame class, no clip-path needed
                    imgDivElement.classList.add(frameId);
                    imgDivElement.style.clipPath = 'none';
                    delete imgDivElement.dataset.clipPathId;
                } else {
                    // Single tile, no frame
                    imgDivElement.style.clipPath = 'none';
                    delete imgDivElement.dataset.clipPathId;
                }
            }
        });
        // Update currentFrame after all SVGs are created
        if (frameId == "none-outlined") {
            currentFrame = "";
            document.getElementById("preview-grid").classList.remove("grid-framed");
        } else {
            currentFrame = frameId;
            document.getElementById("preview-grid").classList.add("grid-framed");
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
    async function applyLayout(layoutId) {
        if (layoutId === "normal-grid") {
            $('#normal-grid').parent().find('.loader').removeClass('d-none');
            // Shrink all multi-tile tiles to 1x1
            $("#preview-grid .image-div").each(async function () {
                let tile = $(this);
                let selectedForSizeWidth = parseInt(tile.css("width"));
                let selectedForSizeHeight = parseInt(tile.css("height"));
                if (selectedForSizeWidth > actualWidth || selectedForSizeHeight > actualHeight) {
                    let selectedForSizeMargin = tile.data("margin");
                    if (selectedForSizeMargin !== '' && selectedForSizeMargin !== undefined) {
                        let mar = selectedForSizeMargin.split('|');
                        selectedForSizeWidth = selectedForSizeWidth - parseInt(mar[0]);
                        selectedForSizeHeight = selectedForSizeHeight - parseInt(mar[1]);
                    }
                    let setW = 91;
                    let setH = 80;
                    let oldW = selectedForSizeWidth / actualWidth;
                    let oldH = selectedForSizeHeight / actualHeight;
                    let oldGrid = oldW * oldH;
                    let newW = setW / actualWidth;
                    let newH = setH / actualHeight;
                    let newGrid = newW * newH;
                    let changeCount = oldGrid - newGrid;
                    console.log("Change count:", changeCount);
                    if (changeCount < 0) {
                        let absChange = parseInt(Math.abs(changeCount).toFixed(0));
                        let removed = 0;
                        // Remove empty tiles first
                        $("#preview-grid .image-div").get().reverse().forEach(el => {
                            if (removed < absChange) {
                                if ($(el).find(".select-image-pop").length === 1) {
                                    $(el).remove();
                                    removed++;
                                }
                            }
                        });
                        // If not enough empty tiles, remove filled tiles from the bottom
                        if (removed < absChange) {
                            $("#preview-grid .image-div").get().reverse().forEach(el => {
                                if (removed < absChange) {
                                    $(el).remove();
                                    removed++;
                                }
                            });
                        }
                    } else if (changeCount > 0) {
                        for (let i = 0; i < changeCount; i++) {
                            $("#preview-grid").append(image_div_html);
                        }
                    }
                    let marginW, marginH = 0;
                    // if (changeCount < 0) {
                    marginW = (newW - 1) * actualMargin;
                    marginH = (newH - 1) * actualMargin;
                    // }
                    tile.css("width", (actualWidth * newW) + marginW);
                    tile.css("height", (actualHeight * newH) + marginH);
                    tile.attr("data-margin", marginW + '|' + marginH);
                    console.log('style ', tile.attr('style'));
                    // calcGridDimension()
                    // placeTiles(); // Recalculate grid layout
                    tile.find("svg").remove();
                    if (newW !== 1 || newH !== 1) {
                        // Apply clip-path for layout stretched images
                        const { svg, clipPathId } = createTileClipPath((actualWidth * newW) + marginW, (actualHeight * newH) + marginH, newH, newW);
                        tile.append(svg);
                        tile[0].style.clipPath = `url(#${clipPathId})`;
                        tile[0].dataset.clipPathId = clipPathId;
                    } else {
                        // Single tile - remove clip-path
                        tile[0].style.clipPath = 'none';
                        delete tile[0].dataset.clipPathId;
                    }
                }
            });
            console.log('normal-grid loop finished');
            setTimeout(async () => {
                await calcGridDimension();
                placeTiles();
                $('#normal-grid').parent().find('.loader').addClass('d-none');
            }, 500);
        } else if (layoutId === "dynamic-grid") {
            $('#dynamic-grid').parent().find('.loader').removeClass('d-none');
            // Randomly enlarge some tiles with .open-edit-pop
            // Get all tiles with .open-edit-pop inside .image-div
            const tilesWithOpenEdit = Array.from(document.querySelectorAll('.image-div .open-edit-pop'))
                .map(img => img.closest('.image-div'));
            // Shuffle the array
            for (let i = tilesWithOpenEdit.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [tilesWithOpenEdit[i], tilesWithOpenEdit[j]] = [tilesWithOpenEdit[j], tilesWithOpenEdit[i]];
            }
            // Pick any 2 random tiles (if at least 2 exist)
            const randomTiles = tilesWithOpenEdit.slice(0, 2);
            randomTiles.forEach(function (tile) {
                let $tile = $(tile);
                let $img = $tile.find(".open-edit-pop");
                if ($img.length > 0) {
                    $("#tile-width,#tile-height").html('');
                    let grid_columns = parseInt(document.getElementById("grid_columns").value);
                    let grid_rows = parseInt(document.getElementById("grid_rows").value);
                    let selectedLeft = parseInt($tile.css("left")) / (actualWidth + actualMargin);
                    let selectedTop = parseInt($tile.css("top")) / (actualHeight + actualMargin);
                    let selectedForSizeWidth = parseInt($tile.css("width"));
                    let selectedForSizeHeight = parseInt($tile.css("height"));
                    let startCol = Math.round(selectedLeft);
                    let startRow = Math.round(selectedTop);
                    let maxWidthFactor = grid_columns - startCol;
                    let maxHeightFactor = grid_rows - startRow;
                    let widthOptions = [];
                    for (let i = 1; i <= maxWidthFactor; i++) {
                        // let widthOption = (actualWidth * i) + ((i - 1) * actualMargin);
                        widthOptions.push((actualWidth * i));
                        // $("#tile-width").append(`<option value="${actualWidth * i}" ${selectedForSizeWidth === widthOption ? "selected" : ""}>${visibleWidth * i} cm</option>`);
                    }
                    let heightOptions = [];
                    for (let i = 1; i <= maxHeightFactor; i++) {
                        // let heightOption = (actualHeight * i) + ((i - 1) * actualMargin);
                        heightOptions.push((actualHeight * i));
                        // $("#tile-height").append(`<option value="${actualHeight * i}" ${selectedForSizeHeight === heightOption ? "selected" : ""}>${visibleHeight * i} cm</option>`);
                    }
                    // Get possible widths and heights from the dropdowns
                    // let widthOptions = $("#tile-width option").map(function () { return parseInt($(this).val()); }).get();
                    // let heightOptions = $("#tile-height option").map(function () { return parseInt($(this).val()); }).get();
                    // Pick a random width/height, but not the smallest (to ensure enlargement)
                    // let randomW = widthOptions.length > 1 ? widthOptions[Math.floor(Math.random() * (widthOptions.length - 1)) +  1] : widthOptions[0];
                    // let randomH = heightOptions.length > 1 ? heightOptions[Math.floor(Math.random() * (heightOptions.length - 1)) + 1] : heightOptions[0];
                    // let currentWIndex = widthOptions.indexOf(selectedForSizeWidth);
                    let currentWIndexx = widthOptions.filter(function (value, i) {
                        return value === (actualWidth * i) + ((i - 1) * actualMargin);
                    });
                    // let currentHIndex = heightOptions.indexOf(selectedForSizeHeight);
                    let currentHIndexx = heightOptions.filter(function (value, i) {
                        return value === (actualHeight * i) + ((i - 1) * actualMargin);
                    });
                    let randomW = widthOptions[Math.min(currentWIndexx + 1, widthOptions.length - 1)];
                    let randomH = heightOptions[Math.min(currentHIndexx + 1, heightOptions.length - 1)];
                    // Set new size and margin
                    let newW = randomW / actualWidth;
                    let newH = randomH / actualHeight;
                    let marginW = (newW - 1) * actualMargin;
                    let marginH = (newH - 1) * actualMargin;
                    $tile.css({
                        width: (actualWidth * newW) + marginW + "px",
                        height: (actualHeight * newH) + marginH + "px"
                    });
                    $tile.attr("data-margin", marginW + "|" + marginH);
                    $tile.find("svg").remove();
                    if (newW !== 1 || newH !== 1) {
                        // Apply clip-path for dynamic layout stretched images
                        const { svg, clipPathId } = createTileClipPath(
                            (actualWidth * newW) + marginW,
                            (actualHeight * newH) + marginH,
                            newH,
                            newW
                        );
                        $tile.append(svg);
                        $tile.style.clipPath = `url(#${clipPathId})`;
                        $tile.dataset.clipPathId = clipPathId;
                    } else {
                        // Single tile - remove clip-path
                        $tile.style.clipPath = 'none';
                        delete $tile.dataset.clipPathId;
                    }
                }
            });
            console.log('dynamic-grid loop finished');
            setTimeout(async () => {
                await calcGridDimension();
                placeTiles();
                $('#dynamic-grid').parent().find('.loader').addClass('d-none');
            }, 500);
        }
        currentLayout = layoutId;
        console.log('currentLayout ', currentLayout);
    }
    // Event listeners for layout options
    document
        .querySelectorAll('input[name="grid-Layout"]')
        .forEach((layoutOption) => {
            layoutOption.addEventListener("change", (e) => {
                applyLayout(e.target.id);
            });
        });
});
function dismissFrameModel() {
    saveCollage('auto');
}
function dismissFilterModel() {
    saveCollage('auto');
}
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
        saveCollage('auto');
    }
    // Event listeners for filter options
    document
        .querySelectorAll('.filter-opt input[type="radio"]')
        .forEach((filterOption) => {
            filterOption.addEventListener("change", (e) => {
                // alert('b');
                applyFilter(e.target.id);
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
    $txtModal = $("#Text_popup");
    function addTextOverlay() {
        if (!textInput.value) return;
        if (selectedOverlay) return;
        const textOverlay = document.createElement("div");
        textOverlay.classList.add("text-overlay");
        textOverlay.style.position = "absolute";
        textOverlay.style.top = "50%";
        textOverlay.style.left = "50%";
        textOverlay.style.transform = "translate(-50%, -50%)";
        textOverlay.style.fontFamily = "Arial, sans-serif";
        textOverlay.style.fontSize = "20px";
        textOverlay.style.color = colorPicker.value || "#000000";
        textOverlay.style.backgroundColor = "transparent";
        textOverlay.style.whiteSpace = "pre-wrap"; // Ensure text respects line breaks and spaces
        // Create a span for the text
        const textSpan = document.createElement("span");
        textSpan.classList.add("text-content");
        // textSpan.innerHTML = textInput.value.replace(/\n/g, "<br>").replace(/ /g, "&nbsp;") || "";
        textSpan.innerHTML = textInput.value;
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
            $txtModal.modal('hide');
        };
        textOverlay.appendChild(removeIcon);
        toolmiddle.appendChild(textOverlay);
        makeDraggableAndRotatable(textOverlay, rotateHandle);
        selectOverlay(textOverlay);
        // Reset text input
        // textInput.value = "";
        toggleGridDragSortonText();
    }
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
    // Function to select an overlay
    function selectOverlay(overlay) {
        $(".text-overlay").removeClass("selected");
        selectedOverlay = overlay;
        selectedOverlay.classList.add("selected");
        $(selectedOverlay).find('.rotate-handle, .text-remove').show()
        let html = $(selectedOverlay).find('span').html();
        $("#text-input").val(html);
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
        // saveCollage('auto');
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
    function restoreTextOverlays() {
        if (!storedTextOverlays || storedTextOverlays.length === 0) return;
        storedTextOverlays.forEach(overlay => {
            const textOverlay = document.createElement("div");
            textOverlay.classList.add("text-overlay");
            // textOverlay.style.position = "absolute";
            // textOverlay.style.top = overlay.top;
            // textOverlay.style.left = overlay.left;
            // textOverlay.style.fontFamily = overlay.fontFamily;
            // textOverlay.style.fontSize = overlay.fontSize;
            // textOverlay.style.color = overlay.color;
            // textOverlay.style.transform = overlay.transform;
            // textOverlay.style.backgroundColor = "transparent";
            // textOverlay.style.whiteSpace = "pre-wrap";
            textOverlay.setAttribute('style', overlay.styles);
            const textSpan = document.createElement("span");
            textSpan.classList.add("text-content");
            textSpan.innerHTML = overlay.text;
            textOverlay.appendChild(textSpan);
            // Add rotate handle
            const rotateHandle = document.createElement("div");
            rotateHandle.classList.add("rotate-handle");
            rotateHandle.style.display = "none";
            textOverlay.appendChild(rotateHandle);
            const rotateIcon = document.createElement("i");
            rotateIcon.classList.add("fa-rotate", "fas", "text-rotate");
            rotateIcon.title = "Rotate Text";
            rotateHandle.appendChild(rotateIcon);
            // Add remove icon
            const removeIcon = document.createElement("i");
            removeIcon.classList.add("fa-remove", "fas", "text-remove");
            removeIcon.title = "Remove Text";
            removeIcon.style.display = "none";
            removeIcon.onclick = () => {
                textOverlay.remove();
            };
            textOverlay.appendChild(removeIcon);
            toolmiddle.appendChild(textOverlay);
            makeDraggableAndRotatable(textOverlay, rotateHandle);
        });
    }
    // Restore overlays when the page loads
    restoreTextOverlays();
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

function createTileClipPath(width, height, rows, columns, options = {}) {
    const { mode = "tiles" } = options;
    // Generate unique clipPath ID
    const clipPathId = `tile-clip-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    
    const svgNS = "http://www.w3.org/2000/svg";
    const cornerRadius = 8; // R=8px per tile (matches SPEC.md clear area radius)
    
    // Create hidden SVG with clipPath definition
    const svg = document.createElementNS(svgNS, "svg");
    svg.setAttribute("width", "0");
    svg.setAttribute("height", "0");
    svg.style.position = "absolute";
    svg.style.pointerEvents = "none";
    
    const defs = document.createElementNS(svgNS, "defs");
    const clipPath = document.createElementNS(svgNS, "clipPath");
    clipPath.setAttribute("id", clipPathId);
    clipPath.setAttribute("clipPathUnits", "userSpaceOnUse");
    
    if (mode === "container") {
        const rect = document.createElementNS(svgNS, "rect");
        rect.setAttribute("x", 0);
        rect.setAttribute("y", 0);
        rect.setAttribute("width", width);
        rect.setAttribute("height", height);
        rect.setAttribute("rx", cornerRadius);
        rect.setAttribute("ry", cornerRadius);
        clipPath.appendChild(rect);
    } else {
        // Use absolute tile dimensions (not proportional division)
        // This creates natural gaps between tiles
        const tileW = actualWidth; // 91px
        const tileH = actualHeight; // 80px
        const gap = actualMargin; // 2px

        // Create a rounded rect for each tile in the grid
        // Position each tile at exact pixel coordinates with gaps
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < columns; c++) {
                const rect = document.createElementNS(svgNS, "rect");
                // Absolute positioning: each tile at (col × (tileW + gap), row × (tileH + gap))
                rect.setAttribute("x", c * (tileW + gap));
                rect.setAttribute("y", r * (tileH + gap));
                rect.setAttribute("width", tileW);
                rect.setAttribute("height", tileH);
                rect.setAttribute("rx", cornerRadius);
                rect.setAttribute("ry", cornerRadius);
                clipPath.appendChild(rect);
            }
        }
    }
    
    defs.appendChild(clipPath);
    svg.appendChild(defs);
    
    return { svg, clipPathId };
}



// function createDynamicSVG(width, height, rows, columns) {
//     console.log(width, height, rows, columns);

//     const svgNS = "http://www.w3.org/2000/svg";
//     // Calculate cell size dynamically
//     const cellWidth = width / columns;
//     const cellHeight = height / rows;
//     // Circle radius
//     const circleRadius = 1.6;
//     // Padding around the circles
//     const padding = 8; // Adjust this value to set the padding
//     // Create SVG element
//     const svg = document.createElementNS(svgNS, "svg");
//     svg.setAttribute("width", width);
//     svg.setAttribute("height", height);
//     svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
//     svg.setAttribute("preserveAspectRatio", "xMidYMid slice");
//     svg.setAttribute("class", "tile-svg");
//     // Add border-radius to the SVG element using inline styles
//     svg.style.borderRadius = "7px"; // Apply 15px border-radius
//     // Draw vertical grid lines
//     for (let x = 0; x <= columns; x++) {
//         let stroke = "#f1f1f1";
//         if (x === 0 || x === columns) {
//             stroke = "transparent";
//         }
//         const line = document.createElementNS(svgNS, "line");
//         const xPos = x * cellWidth;
//         line.setAttribute("x1", xPos.toFixed(1));
//         line.setAttribute("x2", xPos.toFixed(1));
//         line.setAttribute("y1", 0);
//         line.setAttribute("y2", height);
//         line.setAttribute("stroke-width", 3);
//         line.setAttribute("stroke", stroke);
//         line.setAttribute("opacity", 0.7);
//         svg.appendChild(line);
//     }
//     // Draw horizontal grid lines
//     for (let y = 0; y <= rows; y++) {
//         let stroke = "#f1f1f1";
//         if (y === 0 || y === rows) {
//             stroke = "transparent";
//         }
//         const line = document.createElementNS(svgNS, "line");
//         const yPos = y * cellHeight;
//         line.setAttribute("x1", 0);
//         line.setAttribute("x2", width);
//         line.setAttribute("y1", yPos.toFixed(1));
//         line.setAttribute("y2", yPos.toFixed(1));
//         line.setAttribute("stroke-width", 3);
//         line.setAttribute("stroke", stroke);
//         line.setAttribute("opacity", 0.7);
//         svg.appendChild(line);
//     }
//     // // Add four circles in each corner of every grid cell with padding
//     // for (let row = 0; row < rows; row++) {
//     //     for (let col = 0; col < columns; col++) {
//     //         // Calculate the four corners of each grid cell with padding
//     //         const topLeft = {
//     //             cx: col * cellWidth + padding,
//     //             cy: row * cellHeight + padding
//     //         };
//     //         const topRight = {
//     //             cx: (col + 1) * cellWidth - padding,
//     //             cy: row * cellHeight + padding
//     //         };
//     //         const bottomLeft = {
//     //             cx: col * cellWidth + padding,
//     //             cy: (row + 1) * cellHeight - padding
//     //         };
//     //         const bottomRight = {
//     //             cx: (col + 1) * cellWidth - padding,
//     //             cy: (row + 1) * cellHeight - padding
//     //         };
//     //         // Create circles for each corner
//     //         const corners = [topLeft, topRight, bottomLeft, bottomRight];
//     //         corners.forEach(({ cx, cy }) => {
//     //             const circle = document.createElementNS(svgNS, "circle");
//     //             circle.setAttribute("cx", cx.toFixed(1));
//     //             circle.setAttribute("cy", cy.toFixed(1));
//     //             circle.setAttribute("r", circleRadius);
//     //             circle.setAttribute("fill", "#fff");
//     //             svg.appendChild(circle);
//     //         });
//     //     }
//     // }
//     return svg;
// }

// async function getCroppedBase64FromImageElement(imgElement) {
//     return new Promise((resolve) => {
//         if (!imgElement || !imgElement.src) return resolve(null);
//         const canvas = document.createElement('canvas');
//         const ctx = canvas.getContext('2d');
//         const divWidth = imgElement.clientWidth;
//         const divHeight = imgElement.clientHeight;
//         canvas.width = divWidth;
//         canvas.height = divHeight;
//         const img = new Image();
//         img.crossOrigin = 'anonymous';
//         img.src = imgElement.src;
//         img.onload = () => {
//             const style = getComputedStyle(imgElement);
//             const objectFit = style.objectFit || 'cover';
//             const objectPosition = style.objectPosition || 'top';
//             const [posXKeyword, posYKeyword] = objectPosition.trim().split(' ');
//             const imgRatio = img.width / img.height;
//             const divRatio = divWidth / divHeight;
//             let drawWidth, drawHeight;
//             if (objectFit === 'cover') {
//                 if (imgRatio > divRatio) {
//                     // Image is wider than container
//                     drawHeight = divHeight;
//                     drawWidth = img.width * (divHeight / img.height);
//                 } else {
//                     // Image is taller than container
//                     drawWidth = divWidth;
//                     drawHeight = img.height * (divWidth / img.width);
//                 }
//                 // Default offsets (center)
//                 let offsetX = (drawWidth - divWidth) / 2;
//                 let offsetY = (drawHeight - divHeight) / 2;
//                 // Adjust offsetX
//                 if (posXKeyword === 'left') offsetX = 0;
//                 else if (posXKeyword === 'right') offsetX = drawWidth - divWidth;
//                 // Adjust offsetY
//                 if (posYKeyword === 'top') offsetY = 0;
//                 else if (posYKeyword === 'bottom') offsetY = drawHeight - divHeight;
//                 ctx.drawImage(img, -offsetX, -offsetY, drawWidth, drawHeight);
//             } else {
//                 // Fallback for non-cover (just stretch to fit)
//                 ctx.drawImage(img, 0, 0, divWidth, divHeight);
//             }
//             const base64 = canvas.toDataURL('image/jpeg');
//             resolve(base64);
//         };
//         img.onerror = () => resolve(null);
//     });
// }
async function getCroppedBase64FromImageElement(imgElement) {
    return new Promise((resolve) => {
        if (!imgElement || !imgElement.src) return resolve(null);
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const divWidth = imgElement.clientWidth;
        const divHeight = imgElement.clientHeight;
        // // Use native image dimensions
        // const imgNaturalWidth = imgElement.naturalWidth;
        // const imgNaturalHeight = imgElement.naturalHeight;
        // // Get how the image was displayed
        // const imgDispWidth = imgElement.clientWidth;
        // const imgDispHeight = imgElement.clientHeight;
        // // Calculate target output resolution (higher quality)
        // const scaleX = imgNaturalWidth / imgElement.width;
        // const scaleY = imgNaturalHeight / imgElement.height;
        // const divWidth = imgDispWidth * scaleX;
        // const divHeight = imgDispHeight * scaleY;
        canvas.width = divWidth;
        canvas.height = divHeight;
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = imgElement.src;
        img.onload = () => {
            const objectFit = 'cover'; // force it
            const posXKeyword = 'left'; // force top-left
            const posYKeyword = 'top';
            const imgRatio = img.width / img.height;
            const divRatio = divWidth / divHeight;
            let drawWidth, drawHeight;
            if (objectFit === 'cover') {
                if (imgRatio > divRatio) {
                    drawHeight = divHeight;
                    drawWidth = img.width * (divHeight / img.height);
                } else {
                    drawWidth = divWidth;
                    drawHeight = img.height * (divWidth / img.width);
                }
                let offsetX = 0;
                let offsetY = 0;
                // Only calculate if you want center or others — for "top left", both offsets stay zero.
                ctx.drawImage(img, -offsetX, -offsetY, drawWidth, drawHeight);
            } else {
                // Fallback: just stretch image
                ctx.drawImage(img, 0, 0, divWidth, divHeight);
            }
            const base64 = canvas.toDataURL('image/jpeg');
            resolve(base64);
        };
        img.onerror = () => resolve(null);
    });
}
async function saveCollage(type) {

    // if (type == 'auto') {
    //     return false;
    // }
    let btnn = $("#saveBtn");
    let btnn_txt = btnn.html();
    let userType = $("#user_type").val();
    switch (type) {
        case 'auto':
        case 'manual':
            btnn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            break;
        case 'preview':
        case 'manual_admin':
            btnn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $("#previewLoader").show();
            break;
        default:
            return false;
    }

    document.querySelectorAll(".image-div").forEach((div) => {
        div.classList.remove("selected");
        $(".resize-handle").remove();
    });

    const formData = new FormData();

    const $metaToken = $('meta[name="csrf-token"]').attr('content');
    const $grid = $("#preview-grid");
    const $gridMiddle = $grid.closest('.middle');

    formData.append('_token', $metaToken);
    formData.append('unique_id', $("#unique_id").val());
    formData.append('type', type);
    formData.append('user_type', userType);
    formData.append('grid_columns', $("#grid_columns").val());
    formData.append('grid_rows', $("#grid_rows").val());
    formData.append('width', $(".layout-horiz-size span").html());
    formData.append('height', $(".layout-vert-size span").html());
    formData.append('frame', currentFrame);
    formData.append('filter', currentFilter);
    // Collect all .text-overlay elements and store them in an array

    // Text overlays
    const textOverlays = $(".text-overlay").map(function () {
        return {
            text: $(this).find(".text-content").html(),
            styles: $(this).attr("style")
        };
    }).get();
    formData.append("text_editors", JSON.stringify(textOverlays));

    // let tiles = [];
    // const $previewGrid = $("#preview-grid");
    // const $imageDivs = $previewGrid.find(".image-div");
    // if ($("#preview-grid .image-div").length > 0) {
    //     const imageDivs = $("#preview-grid .image-div").toArray();
    //     // $("#preview-grid .image-div").each(async function (index) {
    //     for (let index = 0; index < imageDivs.length; index++) {
    //         const div = $(imageDivs[index]);
    //         let imageDivStyle = div.attr("style");
    //         let imageDivDataMargin = div.attr("data-margin") ?? null;
    //         let image_original = div.find('.image-item-original').attr('src');
    //         let zoom = div.find('.image-item-zoom').val();
    //         let rotate = div.find('.image-item-rotate').val();
    //         let id = div.find('.image-item-id').val();
    //         // console.log();
    //         let image_edited = div.find('.open-edit-pop').attr('src') ?? null;
    //         // if (type == 'preview' || type == 'manual_admin') {
    //         //     if (image_edited && !image_edited.startsWith('data:image/')) {
    //         //         let editedImg = div.find('.open-edit-pop')[0];
    //         //         // console.log(editedImg);
    //         //         // let editedImg = div.find('.image-item-original')[0];
    //         //         image_edited = await getCroppedBase64FromImageElement(editedImg);
    //         //     } else {
    //         //         image_edited = div.find('.open-edit-pop').attr('src') ?? null;
    //         //     }
    //         // }
    //         console.log('asd ', index, id, imageDivDataMargin, image_edited);
    //         tiles.push({
    //             seq: index + 1,
    //             id,
    //             imageDivStyle,
    //             imageDivDataMargin,
    //             image_original,
    //             image_edited,
    //             zoom,
    //             rotate,
    //         });
    //     }
    //     // });
    // }
    // formData.append("tiles", JSON.stringify(tiles));

    // Tiles
    const tiles = $grid.find(".image-div").map(function (index) {
        const $div = $(this);

        let image_edited = $div.find('.open-edit-pop').attr('src') ?? null;
        let image_edited_key = null;

        // If image_edited is a dataURL, convert to File and append to FormData
        if (image_edited && image_edited.startsWith('data:image/')) {
            image_edited_key = `image_edited_${index + 1}`;
            const file = dataURLToFile(image_edited, `${image_edited_key}.png`);
            formData.append(image_edited_key, file);
            image_edited = image_edited_key; // Reference key in tiles
        }
        return {
            seq: index + 1,
            id: $div.find('.image-item-id').val(),
            imageDivStyle: $div.attr("style"),
            imageDivDataMargin: $div.attr("data-margin") ?? null,
            image_original: $div.find('.image-item-original').attr('src'),
            image_edited: image_edited,
            zoom: $div.find('.image-item-zoom').val(),
            rotate: $div.find('.image-item-rotate').val(),
        };
    }).get();
    // console.log(tiles);
    // return false;

    formData.append("tiles", JSON.stringify(tiles));

    // Hide unnecessary elements before image capture
    const $selectImages = $grid.find("img.select-image-pop");

    if (type == 'preview' || type == 'manual_admin') {

        $selectImages.hide();
        $gridMiddle.find('.middle-top, .middle-bottom').hide();
        $gridMiddle.find('.tool-inner').css('border', 'none');
        if (type == 'preview' || type == 'manual_admin') {
            $(".text-overlay").each(function () {
                let top = $(this).css("top").replace("px", "");
                $(this).data("top", top);
                $(this).css("top", 'calc(' + top + 'px - 46px)'); // Adjust for middle-top hide unhide
            });
        }

        // // Ensure all images have proper object-fit styles before capture
        // $grid.find("img.image-item").each(function() {
        //     const $img = $(this);
        //     $img.css({
        //         'object-fit': 'cover',
        //         'object-position': 'top left',
        //         'width': '100%',
        //         'height': '100%'
        //     });
        // });

        // $grid.find(".image-div").each(function () {
        //     const $div = $(this);
        //     const $img = $div.find('.image-item');
        //     const $bgDiv = $div.find('.image-item-div');
        //     if ($img.length && $bgDiv.length) {
        //         let imgUrl = $img.attr('src');
        //         $img.hide();
        //         $bgDiv.css({
        //             'background-image': `url('${imgUrl}')`,
        //             'display': 'block'
        //         });
        //     }
        // });
    }

    // return false;
    requestIdleCallback(() => {
        // Prepare images for capture
        // prepareImagesForCapture();
        // Small delay to ensure all styles are applied
        setTimeout(() => {
            // // NOT CREATING GOOD QUALITY IMAGE
            // domtoimage.toPng($gridMiddle[0], { bgcolor: 'transparent', quality: 1 })
            // // SLOW TO CREATE IMAGE
            // htmlToImage.toPng($gridMiddle[0], {
            //     pixelRatio: 2,
            //     backgroundColor: 'transparent'
            // })
            // .then(function (dataUrl) {
            //     const img = new Image();
            //     img.src = dataUrl;
            // FAST TO CREATE IMAGE BUT MANY CSS NOT WORKING
            html2canvas($gridMiddle[0], {
                backgroundColor: null, // transparent
                scale: 2, // higher scale for better quality
                letterRendering: 1,
                allowTaint: true,
            }).then(function (canvas) {
                const dataUrl = canvas.toDataURL("image/png");


                formData.append("collage_image", dataURLToFile(dataUrl, "captured-image.png"));
                // downloadImage(dataUrl, "captured-image.png");
                // console.log(dataURLToFile(dataUrl, "captured-image.png"));
                // btnn.prop('disabled', false).html(btnn_txt);
                // return false;
                $.ajax({
                    url: saveCollageUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        setTimeout(() => {
                            btnn.prop('disabled', false).html(btnn_txt);
                            if (type === 'preview' || type === 'manual_admin') $("#previewLoader").hide();
                        }, 2000);
                        if (type == 'preview' || type == 'manual_admin') {
                            $selectImages.show();
                            $gridMiddle.find('.middle-top, .middle-bottom').show();
                            $gridMiddle.find('.tool-inner').css('border', '1px solid lightgray');
                            $(".text-overlay").each(function () {
                                let top = $(this).data("top");
                                $(this).css("top", top + 'px');
                            })

                            // $grid.find(".image-div").each(function () {
                            //     const $div = $(this);
                            //     const $img = $div.find('.image-item');
                            //     const $bgDiv = $div.find('.image-item-div');
                            //     if ($img.length && $bgDiv.length) {
                            //         $img.show();
                            //         $bgDiv.css({
                            //             'background-image': '',
                            //             'display': ''
                            //         });
                            //     }
                            // });
                        }
                        if (res.status === 1) {
                            if (type == 'preview') {
                                $(window).off('beforeunload');
                                window.location.href = previewCollageUrl;
                                return;
                            }
                            if (type == "manual" || type == 'manual_admin') {
                                toastr.success(res.message, '', {
                                    closeButton: true,
                                    progressBar: false,
                                    timeOut: 5000,
                                    extendedTimeOut: 0
                                });
                            }
                            if (res.data.updateIds?.length > 0) {
                                $grid.find(".image-div").each(function (i) {
                                    const update = res.data.updateIds[i];
                                    const $this = $(this);
                                    $this.find('.image-item-id').val(update.id);
                                    $this.find('.image-item-original').attr('src', update.image);
                                    if ($this.find('.open-edit-pop').length > 0) {
                                        $this.find('.open-edit-pop').attr('src', update.image_edited);
                                    }
                                });
                            }
                        } else {
                            if (res.data?.login === 1) {
                                $('#signinmodal').modal('show');
                            }
                            toastr.error(res.message, '', {
                                closeButton: true,
                                progressBar: false,
                                timeOut: 5000,
                                extendedTimeOut: 0
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        btnn.prop('disabled', false).html(btnn_txt);
                        if (type == 'preview' || type == 'manual_admin') $("#previewLoader").hide();
                        if (type == 'preview' || type == 'manual_admin') {
                            $selectImages.show();
                            $gridMiddle.find('.middle-top, .middle-bottom').show();
                            $gridMiddle.find('.tool-inner').css('border', '1px solid lightgray');

                            // $grid.find(".image-div").each(function () {
                            //     const $div = $(this);
                            //     const $img = $div.find('.image-item');
                            //     const $bgDiv = $div.find('.image-item-div');
                            //     if ($img.length && $bgDiv.length) {
                            //         $img.show();
                            //         $bgDiv.css({
                            //             'background-image': '',
                            //             'display': ''
                            //         });
                            //     }
                            // });
                        }
                        toastr.error('Failed to save data.', '', {
                            closeButton: true,
                            progressBar: false,
                            timeOut: 5000,
                            extendedTimeOut: 0
                        });
                    }
                });
            }).catch(function (error) {
                console.error('Error capturing image:', error);
            });
        }, 100); // Close the setTimeout
    });
}

function dataURLToFile(dataurl, filename) {
    let arr = dataurl.split(','),
        mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]),
        n = bstr.length,
        u8arr = new Uint8Array(n);
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new File([u8arr], filename, {
        type: mime
    });
}

function downloadImage(dataUrl, filename) {
    let link = document.createElement("a");
    link.href = dataUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Helper function to apply object-fit: cover manually for html2canvas
function applyObjectFitCover(img) {
    const parent = img.parentElement;
    if (!parent) return;
    const parentRect = parent.getBoundingClientRect();
    const imgRatio = img.naturalWidth / img.naturalHeight;
    const parentRatio = parentRect.width / parentRect.height;
    let newWidth, newHeight, offsetX = 0, offsetY = 0;
    if (imgRatio > parentRatio) {
        // Image is wider than container - fit to height
        newHeight = parentRect.height;
        newWidth = img.naturalWidth * (parentRect.height / img.naturalHeight);
        // For top-left positioning, no horizontal offset needed
    } else {
        // Image is taller than container - fit to width
        newWidth = parentRect.width;
        newHeight = img.naturalHeight * (parentRect.width / img.naturalWidth);
        // For top-left positioning, no vertical offset needed
    }
    // Apply the cover effect manually
    img.style.width = newWidth + 'px';
    img.style.height = newHeight + 'px';
    img.style.marginLeft = '0px';
    img.style.marginTop = '0px';
    img.style.objectFit = 'cover';
    img.style.objectPosition = 'top left';
}

// Function to ensure all CSS properties are properly applied for html2canvas
function prepareImagesForCapture() {
    const images = document.querySelectorAll('img.image-item');
    images.forEach(function (img) {
        // Force important CSS properties
        img.style.setProperty('object-fit', 'cover', 'important');
        img.style.setProperty('object-position', 'top left', 'important');
        img.style.setProperty('width', '100%', 'important');
        img.style.setProperty('height', '100%', 'important');
        img.style.setProperty('border-radius', '7px', 'important');
        // Ensure the image is properly loaded
        if (img.complete && img.naturalWidth > 0) {
            applyObjectFitCover(img);
        } else {
            img.onload = function () {
                applyObjectFitCover(img);
            };
        }
    });
}

function showResizeHandle(tile) {
    // Remove any existing handles
    $(".resize-handle").remove();
    // Create handle
    const handle = document.createElement("div");
    handle.className = "resize-handle";
    handle.style.position = "absolute";
    handle.style.right = "0";     // Move onto tile boundaries
    handle.style.bottom = "0";    // Move onto tile boundaries
    handle.style.width = "18px";
    handle.style.height = "18px";
    handle.style.background = "rgba(0,0,0,0.2)";
    handle.style.cursor = "nwse-resize";
    handle.style.zIndex = "1000";
    handle.style.borderRadius = "0 0 7px 0";
    handle.style.overflow = "hidden"; // Ensure content fits within bounds
    handle.style.display = "flex";
    handle.style.alignItems = "center";
    handle.style.justifyContent = "center";
    handle.innerHTML = `<img src="${iconStretch}" alt="" style="width: 16.8px; height: 16.8px; object-fit: contain;" />`;
    tile.appendChild(handle);
    // Add drag logic
    addResizeDrag(tile, handle);
}

function addResizeDrag(tile, handle) {
    let startX, startY, startWidth, startHeight;
    let grid_columns = parseInt(document.getElementById("grid_columns").value);
    let grid_rows = parseInt(document.getElementById("grid_rows").value);
    handle.onmousedown = function (e) {
        e.stopPropagation();
        e.preventDefault();
        startX = e.clientX;
        startY = e.clientY;
        startWidth = parseInt(window.getComputedStyle(tile).width, 10);
        startHeight = parseInt(window.getComputedStyle(tile).height, 10);
        document.onmousemove = function (e) {
            let dx = e.clientX - startX;
            let dy = e.clientY - startY;
            // Calculate new width/height in px, snap to tile size
            let newW = Math.round((startWidth + dx) / actualWidth);
            let newH = Math.round((startHeight + dy) / actualHeight);
            // Clamp to at least 1, and not more than available grid space
            let left = parseInt(tile.style.left || 0) / (actualWidth + actualMargin);
            let top = parseInt(tile.style.top || 0) / (actualHeight + actualMargin);
            let maxWidth = grid_columns - left;
            let maxHeight = grid_rows - top;
            newW = Math.max(1, Math.min(newW, maxWidth));
            newH = Math.max(1, Math.min(newH, maxHeight));
            // Set new size
            let marginW = (newW - 1) * actualMargin;
            let marginH = (newH - 1) * actualMargin;
            tile.style.width = (actualWidth * newW + marginW) + "px";
            tile.style.height = (actualHeight * newH + marginH) + "px";
            tile.setAttribute("data-margin", marginW + '|' + marginH);
            // Remove old SVG and add new one for updated size
            $(tile).find("svg").remove();
            
            // Check what frame class is currently applied (before any changes)
            const hasBlackFrame = tile.classList.contains('success-black-outlined');
            const hasWhiteFrame = tile.classList.contains('success-white-outlined');
            
            if (newW !== 1 || newH !== 1) {
                // Apply clip-path for drag-resized stretched images
                const { svg, clipPathId } = createTileClipPath(
                    (actualWidth * newW) + marginW,
                    (actualHeight * newH) + marginH,
                    newH,
                    newW
                );
                $(tile).append(svg);
                tile.style.clipPath = `url(#${clipPathId})`;
                tile.dataset.clipPathId = clipPathId;
                
                // Ensure frame class remains applied
                if (hasBlackFrame) {
                    tile.classList.add('success-black-outlined');
                }
                if (hasWhiteFrame) {
                    tile.classList.add('success-white-outlined');
                }
            } else {
                // Single tile - remove clip-path
                tile.style.clipPath = 'none';
                delete tile.dataset.clipPathId;
                
                // Ensure frame class remains applied for single tile
                if (hasBlackFrame) {
                    tile.classList.add('success-black-outlined');
                }
                if (hasWhiteFrame) {
                    tile.classList.add('success-white-outlined');
                }
            }
            // Optionally, update the size popup selects if open
            $("#tile-width").val(actualWidth * newW);
            $("#tile-height").val(actualHeight * newH);
        };
        document.onmouseup = function () {
            document.onmousemove = null;
            document.onmouseup = null;
            // After resize, re-calculate grid and place tiles
            calcGridDimension();
            placeTiles();
        };
    };
}