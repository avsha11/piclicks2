<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="background-color:#f8f9fa;font-family:Arial,sans-serif;margin:0;padding:0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#edf2f7;padding:20px;">
        <tr>
            <td align="center">
                <table width="570" cellpadding="0" cellspacing="0" style="background:#ffffff;padding:20px;border-radius:5px;">
                    <tr>
                        <td align="center">
                            <h1 style="color:#3d4852;">Password Reset Request</h1>
                            <p>We received a request to reset your password. Use the temporary password below to log in and set a new password.</p>
                            <p style="font-size:18px;font-weight:bold;background:#2d3748;color:#fff;padding:10px;border-radius:5px;">
                               {{ $tempPassword }}
                            </p>
                            <p>After logging in, please change your password for security reasons.</p>
                            <p>If you did not request a password reset, please ignore this email.</p>
                            <p>Regards,<br> PICLICKS</p>
                        </td>
                    </tr>
                </table>
                <p style="color:#b0adc5;font-size:12px;text-align:center;margin-top:20px;">© 2025 PICLICKS. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
