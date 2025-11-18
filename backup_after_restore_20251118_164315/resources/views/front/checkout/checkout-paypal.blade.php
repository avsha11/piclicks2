<script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&components=buttons&currency=USD"

    data-user-id-token="{{ $accessToken }}"></script>

<script>

    function schedulingFormBtnClick() {

        // $("#paymentPopup").modal('show');

    }



    // Render the Venmo button into #paypal-button-container

    paypal.Buttons({

        style: {

            // layout: 'horizontal',

            tagline: false,

        },

        onClick: (data) => {

            fundingSource = data.fundingSource

            // $("#paypal_button_name").val(data.fundingSource);

            // $("#paypallogs").append(`<div class="col-md-12">onClick(data): ${JSON.stringify(data)}</div>`);

        },

        async createOrder(data, actions) {

            // if ($("#hidden_final_amount").val() === "") {

            //     return Promise.reject(new Error("Invalid amount"));

            // }

            

            // alert('coming soon');

            // return false;

            // $("#paypal_button_name").val(data.paymentSource);

            // $("#paypallogs").append(

            //     `<div class="col-md-12">createOrder(data): ${JSON.stringify(data)}</div>`);

            return fetch("{{ route('paypal_create_order') }}", {

                    method: "POST",

                    headers: {

                        "Content-Type": "application/json",

                        "X-CSRF-TOKEN": "{{ csrf_token() }}",

                    },

                    body: JSON.stringify({

                        cart: [{

                            sku: "Collage",

                            quantity: $("#hidden_final_amount").val(),

                        }],

                        shopping_fullname: $("#shopping_fullname").val(),

                        total_quantity: $("#hidden_total_quantity").val(),

                        first_name: document.getElementById('shopping_fullname').value,

                        last_name: document.getElementById('shopping_fullname').value,

                        email: document.getElementById('shopping_email').value,

                        phone: document.getElementById('shopping_phone').value,

                        address_line_1: document.getElementById('shopping_address').value,

                        address_line_2: document.getElementById('shopping_address_opt').value,

                        city: document.getElementById('shopping_city').value,

                        state: document.getElementById('shopping_state').value,

                        postal_code: document.getElementById('shopping_postalcode').value,

                        country_code: document.getElementById('shopping_country').value

                    })

                })

                .then((response) => response.json())

                .then((order) => order.id)

                .catch(err => {

                    console.error("PayPal createOrder error:", err);

                });

        },

        async onApprove(data) {

            // $("#paypallogs").append(`<div class="col-md-12">onApprove(data): ${JSON.stringify(data)}</div>`);

            let approvertn = await fetch("{{ route('paypal_capture_order') }}", {

                    method: "POST",

                    headers: {

                        "Content-Type": "application/json",

                        "X-CSRF-TOKEN": "{{ csrf_token() }}",

                    },

                    body: JSON.stringify({

                        orderID: data.orderID

                    })

                })

                .then((response) => response.json())

                .then(async (orderData) => {

                    // console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

                    // const transaction = orderData.purchase_units[0].payments.captures[0];

                    // alert(

                    //     `Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`

                    // );

                    if (orderData.status == "COMPLETED") {

                        var formData = new FormData($('#msform')[0]);

                        formData.append('orderID', orderData.id);

                        formData.append('status', orderData.status);

                        await fetch("{{ route('paypal_success_payment') }}", {

                                method: "POST",

                                headers: {

                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",

                                },

                                body: formData

                            })

                            .then((response) => response.json())

                            .then(async (response) => {

                                // $("#paymentPopup").modal('hide');

                                if (response.status === 1) {

                                    Swal.fire({

                                        icon: 'success',

                                        title: 'Order Confirmation',

                                        text: response.message,

                                    }).then(function() {

                                        window.location.href = response.data.url;

                                    });

                                } else {

                                    Swal.fire({

                                        icon: 'error',

                                        title: 'Order Confirmation',

                                        text: response.message,

                                    }).then(function() {});

                                }

                            });

                    }

                });

            return approvertn;

        },

        onCancel(data) {

            // $("#paypallogs").append(

            //     `<div class="col-md-12">onCancel.(data): ${JSON.stringify(data)}</div>`);

            Swal.fire({

                icon: 'error',

                title: 'Order Status',

                text: 'Payment cancelled, try again.',

            }).then(function() {

                // $("#paymentPopup").modal('hide');

            });

        },

        onError(err) {

            // $("#paypallogs").append(

            //     `<div class="col-md-12">onError.(data): ${JSON.stringify(data)}</div>`);

            Swal.fire({

                icon: 'error',

                title: 'Order Status',

                text: 'An error occured in payment, try again.',

            }).then(function() {

                // $("#paymentPopup").modal('hide');

            });

        }

    }).render('#paypal-button-container')

</script>

