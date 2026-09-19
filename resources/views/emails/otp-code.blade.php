<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>رمز التحقق</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: Tahoma, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#c0392b; padding:20px; text-align:center;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">تطبيق الإنذار المبكر</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px; text-align:center;">
                            <p style="font-size:16px; color:#333333; margin:0 0 16px;">رمز التحقق الخاص بك هو:</p>
                            <div style="font-size:32px; font-weight:bold; letter-spacing:8px; color:#c0392b; margin:16px 0;">
                                {{ $otpCode }}
                            </div>
                            <p style="font-size:13px; color:#888888; margin:16px 0 0;">
                                هذا الرمز صالح لمدة 10 دقائق فقط. إذا لم تطلب هذا الرمز، تجاهل هذه الرسالة.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>