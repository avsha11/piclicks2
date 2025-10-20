
// handleAjaxFormSubmit function
function handleAjaxFormSubmit(formId, url, dynamicUrl = null, successRedirect = null, successMessage = 'Operation Successful!', submitButtonText = 'Submit') {
    $(formId).on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        let formData = new FormData(this);
        const $submitButton = $form.find(':submit');

        // If dynamicUrl is provided, replace the URL
        if (dynamicUrl) {
            url = dynamicUrl;
        }

        // Disable button to prevent multiple clicks
        $submitButton.prop('disabled', true).text('Processing...');

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.status === 1) {
                    Swal.fire({
                        title: 'Success!',
                        text: res.message || successMessage,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        if (successRedirect) {
                            window.location.href = successRedirect;
                        } else {
                            window.location.reload(); // Reload page on success
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function (key, value) {
                        $('#' + key + 'Error').text(value[0]);
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
                }
            },
            complete: function () {
                $submitButton.prop('disabled', false).text(submitButtonText);
            }
        });
    });
}
