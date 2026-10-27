{{-- Smart Holidays branded email. Sections: preheader, eyebrow, title, content. Inline styles keep it intact in Gmail and Outlook. --}}
@php($logo = isset($message) ? $message->embed(public_path('images/smart-holidays-logo.png')) : asset('images/smart-holidays-logo.png'))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('title')</title>
</head>
<body style="margin: 0; padding: 0; background-color: #eef3fa; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0f2747; -webkit-text-size-adjust: 100%;">
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0; color: #eef3fa;">@yield('preheader')</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eef3fa;">
        <tr>
            <td align="center" style="padding: 32px 14px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 560px;">
                    {{-- Brand header --}}
                    <tr>
                        <td bgcolor="#12305e" style="padding: 28px 32px; border-radius: 22px 22px 0 0; background-color: #12305e; background-image: linear-gradient(120deg, #0d2a55 0%, #1d4fa8 60%, #2f74e0 100%);">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right: 14px; vertical-align: middle;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td bgcolor="#ffffff" style="padding: 6px; border-radius: 14px; background-color: #ffffff;">
                                                    <img src="{{ $logo }}" width="44" height="44" alt="Smart Holidays" style="display: block; width: 44px; height: 44px; border: 0; object-fit: contain;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="color: #ffffff; font-size: 17px; font-weight: 800; letter-spacing: 1.5px;">SMART HOLIDAYS</div>
                                        <div style="color: #bcd6ff; font-size: 11px; font-weight: 700; letter-spacing: 1.6px; margin-top: 3px;">ROYSTAR TRAVEL &amp; TOURS</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Message card --}}
                    <tr>
                        <td bgcolor="#ffffff" style="padding: 34px 32px 30px; background-color: #ffffff; border-radius: 0 0 22px 22px; box-shadow: 0 18px 40px rgba(18, 52, 110, 0.10);">
                            <div style="color: #2364d2; font-size: 11px; font-weight: 800; letter-spacing: 2px;">@yield('eyebrow')</div>
                            <h1 style="margin: 10px 0 0; color: #0f2747; font-size: 25px; line-height: 1.25; font-weight: 800;">@yield('title')</h1>

                            @yield('content')

                            <p style="margin: 26px 0 0; color: #0f2747; font-size: 15px; line-height: 1.6;">
                                Warm regards,<br>
                                <strong>The Smart Holidays team</strong>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding: 24px 20px 6px; color: #66748a; font-size: 12px; line-height: 1.7;">
                            Need help? WhatsApp us at
                            <a href="https://wa.me/60165500590" style="color: #2364d2; font-weight: 700; text-decoration: none;">+60 16-550 0590</a>
                            or visit <a href="{{ url('/') }}" style="color: #2364d2; font-weight: 700; text-decoration: none;">our website</a>.<br>
                            © {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
