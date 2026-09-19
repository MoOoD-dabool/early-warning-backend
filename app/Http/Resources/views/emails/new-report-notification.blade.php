<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <title>بلاغ جديد</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: Tahoma, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding: 32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#2c3e50; padding:20px; text-align:center;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">بلاغ جديد وصل</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:14px; color:#555555; margin:0 0 8px;"><strong>من:</strong> {{ $report->user->first_name }} {{ $report->user->last_name }} ({{ $report->user->email }})</p>
                            <p style="font-size:14px; color:#555555; margin:0 0 16px;"><strong>التاريخ:</strong> {{ $report->created_at->format('Y-m-d H:i') }}</p>
                            <div style="background-color:#f8f9fa; border-radius:6px; padding:16px; font-size:15px; color:#333333; line-height:1.6;">
                                {{ $report->message }}
                            </div>
                            <p style="font-size:13px; color:#888888; margin:20px 0 0;">
                                افتح لوحة الأدمن للرد على هذا البلاغ.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>