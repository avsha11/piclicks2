<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Placed successfully</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Heebo&display=swap');
    </style>
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

                            <table border="0" cellpadding="0" cellspacing="10" width="100%">

                                <tr>

                                    <td align="center" style="text-align: center;" valign="middle">

                                        <a style="font-family: 'Ubuntu', sans-serif; color:#ff3000; font-weight:300; display:block; letter-spacing:-1.5px; text-decoration:none; margin-top:2px"
                                            href="{{ route('front.index') }}">

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

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="left" valign="top"
                                        style="color:#444444; font-size:14px; font-family: Arial, sans-serif; line-height:1.6; padding:20px;">

                                        <h4
                                            style="margin: 0 0 16px; text-align: center; font-size: 30px; font-weight: 600;">
                                            Dear {{ $giftcard_data->name }},</h4>
                                        <div
                                            style="background-color:#f6f6f6; width:80%; padding:30px; border-radius: 20px; margin: 0 auto;">
                                            <p style="margin: 0 0 16px; text-align:center; font-size: 22px; ">You’ve
                                                been sent a special gift card! 🎁🎉</p>

                                            <p style="margin: 0 0 16px; text-align: center; font-size: 16px;">
                                                <strong>{{ $user_data->name }} has gifted you a card.</strong>
                                            </p>

                                            <p style="margin: 0 0 16px; text-align:center; font-size: 40px;">
                                                <img src="{{ asset('storage/' . $giftcard_data->giftcard_data->image) }}"
                                                    alt="Giftcard" style="width: 100%;">
                                            </p>




                                            <p
                                                style="margin: 0 0 16px; text-align: center; font-size: 18px; font-weight:600;font-family: 'Heebo', Arial, sans-serif;">
                                                {{ $giftcard_data->message }}
                                            </p>
                                        </div>

                                        <p
                                            style="font-size: 14px;color: #888888;text-align: center;margin: 16px 0 0 0;">
                                            Reference: #{{ $giftcard_data->id }}
                                        </p>

                                        <p style="margin: 16px 0; font-size: 16px; text-align: center;">
                                            Click on the link below to redeem your Gift Card.
                                        </p>

                                        <p style="margin: 16px 0; margin-top: 0px; text-align: center;">
                                            <a href="{{ route('front.redeem-giftcard', ['code' => $giftcard_data->code]) }}"
                                                target="_blank" rel="noopener noreferrer"
                                                style="color: #ffffff; background: green; padding: 14px 28px; border-radius: 50px; text-decoration: none; display: inline-block; font-weight: bold;">
                                                Redeem Gift Card
                                            </a>
                                        </p>

                                        <br>
                                        <p style="margin: 16px 0; margin-top:0px; text-align: center; font-size:16px;">
                                            If you have any questions, feel free to reach out. We appreciate your trust
                                            in us!
                                        </p>

                                        <p style="margin: 0 0 4px; font-size: 16px; text-align:center;">Best Regards,
                                        </p>
                                        <p style="margin: 0; font-size:16px; text-align: center;">
                                            <strong>{{ env('APP_NAME') }}</strong>
                                        </p>

                                    </td>
                                </tr>
                            </table>


                        </td>

                    </tr>



                    <tr>

                        <td align="center" valign="top"
                            style="background:linear-gradient(135deg, #4a90e2, #6a5acd);color:white">

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
