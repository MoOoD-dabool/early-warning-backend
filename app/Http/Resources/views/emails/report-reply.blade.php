<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>رد على بلاغك</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: Tahoma, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#27ae60; padding:20px; text-align:center;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">وصلك رد على بلاغك</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:14px; color:#555555; margin:0 0 8px;">مرحباً {{ $report->user->first_name }}،</p>
                            <p style="font-size:14px; color:#555555; margin:0 0 16px;">بلاغك الأصلي كان:</p>
                            <div style="background-color:#f8f9fa; border-radius:6px; padding:16px; font-size:14px; color:#666666; line-height:1.6; margin-bottom:16px;">
                                {{ $report->message }}
                            </div>
                            <p style="font-size:14px; color:#555555; margin:0 0 8px;"><strong>رد فريق الدعم:</strong></p>
                            <div style="background-color:#eafaf1; border-radius:6px; padding:16px; font-size:15px; color:#2c3e50; line-height:1.6;">
                                {{ $report->admin_reply }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>