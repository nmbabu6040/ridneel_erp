<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('subject', 'Ridneel DMS ERP')</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f3f6; font-family: 'Segoe UI', Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f3f6; padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06);">
                <tr>
                    <td style="background-color:#0d6efd; padding:20px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="color:#ffffff; font-size:18px; font-weight:700;">Ridneel DMS ERP</td>
                                <td align="right" style="color:#e7f0ff; font-size:12px;">Ridneel Trade International</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px; background-color:#f8f9fa; border-top:1px solid #e9ecef;">
                        <p style="margin:0; font-size:12px; color:#868e96;">
                            এটি একটি স্বয়ংক্রিয় নোটিফিকেশন ইমেইল — Ridneel DMS ERP থেকে পাঠানো হয়েছে।
                            এই ইমেইলে সরাসরি রিপ্লাই করবেন না।
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
