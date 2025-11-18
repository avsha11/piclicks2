<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Placed successfully</title>

</head>



<body>

    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" name="mjqemailid">

        <tr>

            <td align="center" valign="top">

                <table border="0" cellpadding="10" cellspacing="0" width="100%"

                    style="border:1px solid #ddd;margin:50px 0px 100px 0px;text-align:center;color:#363636;font-family: 'Montserrat', Arial, Helvetica, sans-serif; background-color:white">

                    <tr>

                        <td align="center" valign="top"

                            style="padding:0px; background: linear-gradient(135deg, #4a90e2, #6a5acd);">

                            <table border="0" cellpadding="0" cellspacing="10" width="100%" style="max-width:600px;margin:0 auto">

                                <tr>

                                    <td align="center" style="text-align: center;" valign="middle">

                                        <a style="font-family: 'Ubuntu', sans-serif; color:#ff3000; font-weight:300; display:block; letter-spacing:-1.5px; text-decoration:none; margin-top:2px"

                                            href="#">

                                            <img src="{{ asset(env('LOGO_PATH', 'assets/images/logo.png')) }}"

                                                style="padding-top:0; display:inline-block; vertical-align:middle; margin-right:0px; height:55px">

                                        </a>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                    <tr>

                        <td align="center" valign="top">

                            <table border="0" cellpadding="0" cellspacing="10" width="100%"   style="max-width:600px; margin:0 auto;">

                                <tr>

                                    <td align="left" valign="top"

                                        style="color:#444444; font-size:14px; font-family: Arial, sans-serif; line-height:1.6; padding:20px;">

                                        <h4 style="margin:0 0 16px;text-align:center;font-size:30px;font-weight:600">Dear <strong>{{ $user_data->name }}</strong>,</h4>



                                      <div style="background-color:#f6f6f6; width:80%; padding:30px; border-radius: 20px; margin: 0 auto;">
                                          <p style="margin: 0 0 16px; font-size: 22px; text-align:center;">

                                            Thank you for your order! We’re excited to process your custom tile collage.

                                        </p>



                                        <p style="margin: 0 0 16px; text-align: center; font-size: 16px;">

                                            Below are the details of your order:

                                        </p>



                                     <div style="text-align: center;">
                                           <p

                                            style="margin: 16px 0; font-size:16px; font-weight: bold; background: #f7f7f7; padding: 10px 15px; border: 1px solid #ccc; display: inline-block;">

                                            Order ID: #{{ $order_data->internal_order_id }}

                                        </p>
                                     </div>
                                      </div>



                                        <p style="margin: 16px 0; font-size: 16px; text-align: center;">

                                            You can track the status of your order anytime through our

                                            <a href="{{ route('front.index') }}"

                                                style="color: #007bff; text-decoration: none;">Customer Panel</a>.

                                        </p>



                                        <p style="margin: 16px 0; font-size: 16px; text-align: center;">

                                            If you have any questions, feel free to reach out. We appreciate your trust

                                            in us!

                                        </p>



                                        <p style="margin: 0 0 4px; font-size: 16px; text-align: center;">Best Regards,</p>

                                        <p style="margin: 0; font-size: 16px; text-align: center;"><strong>{{ env('APP_NAME') }}</strong></p>

                                    </td>



                                </tr>

                            </table>

                        </td>

                    </tr>



                    <tr>

                        <td align="center" valign="top" style="background:linear-gradient(135deg, #4a90e2, #6a5acd);color:white">

                            <table border="0" cellpadding="0" cellspacing="10" width="100%">

                                <tr>

                                    <td align="center" valign="top" width="80%">

                                        <div style="margin:0;padding:0;color:#fff;font-size:13px">Copyright ©

                                            {{ date('Y') }} <a href="#"

                                                style="color:white;text-decoration:none"> {{ env('APP_NAME') }} </a>.

                                            All rights reserved.</div>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                </table>

            </td>

        </tr>

    </table>

</body>



</html>

