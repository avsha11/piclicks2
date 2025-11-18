<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" name="mjqemailid">
        <tr>
            <td align="center" valign="top">
                <table border="0" cellpadding="10" cellspacing="0" width="100%" style="border:1px solid #ddd;margin:50px 0px 100px 0px;text-align:center;color:#363636;font-family: 'Montserrat', Arial, Helvetica, sans-serif; background-color:white">
                    <tr>
                        <td align="center" valign="top" style="padding:0px; background: #a0c3ff; border-bottom: 2px solid #a0c3ff;">
                            <table border="0" cellpadding="0" cellspacing="10" width="100%">
                                <tr>
                                    <td align="center" style="text-align: center;" valign="middle">
                                        <a style="font-family: 'Ubuntu', sans-serif; color:#ff3000; font-weight:300; display:block; letter-spacing:-1.5px; text-decoration:none; margin-top:2px" href="#">
                                            <img src="{{ asset(env('LOGO_PATH', 'assets/images/logo.png')) }}" style="padding-top:0; display:inline-block; vertical-align:middle; margin-right:0px; height:55px">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="10" width="100%">
                                <tr>
                                    <td align="left" valign="top" style="color:#444; font-size:14px">
                                        <p>Hello,</p>
                                        <p>This is an automated message. If you did not recently initiate the Forgot Password process,please disgard this email.</p>
                                        <p>This is your new temperory password <b>{{ $tempPassword }} </b> for login, use the password please do not share the password.</p>
                                        <p style="margin:0; padding:10px 0px">{{ __('message.kindly') }},<br>{{ __('message.the') }} {{ env('APP_NAME') }} {{ __('message.Team') }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" valign="top" style="background-color:#a0c3ff;color:white">
                            <table border="0" cellpadding="0" cellspacing="10" width="100%">
                                <tr>
                                    <td align="center" valign="top" width="80%">
                                        <div style="margin:0;padding:0;color:#fff;font-size:13px">Copyright © {{ date('Y') }} <a href="#" style="color:white;text-decoration:none"> {{ env('APP_NAME') }} </a>. All rights reserved.</div>
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


{{--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <h1>Forgot Your Password?</h1>
    <p>Your temprory password is: <span style="{color:green;}">{{ $mailData['password'] }}</span></p>
    <p>Please use this temprory password to reset your password.</p>
</body>
</html> --}}
