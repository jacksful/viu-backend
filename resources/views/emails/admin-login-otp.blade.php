@php
    use App\Support\SiteSettings;

    $logoLightUrl = SiteSettings::logoLightUrl();
    $logoDarkUrl = SiteSettings::logoDarkUrl();
    $supportEmail = SiteSettings::supportEmail() ?? 'support@fullviu.com';
    $address = SiteSettings::address() ?? 'Billings, Montana, USA';
    $siteTagline = SiteSettings::siteTagline() ?? 'Own the market before they sell';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin login verification code</title>
</head>
<body style="margin:0; padding:32px 12px; background-color:#F1F2F7; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; margin:0 auto; background:#FFFFFF; border:1px solid #E5E7EB;">
    <tr>
      <td style="background-color:#1A1C4F; padding:34px 40px;">
        <img src="{{ $logoLightUrl }}" alt="VIU" width="54" height="34" style="display:block;">
        <div style="font-size:11px; letter-spacing:3px; color:#F57F20; text-transform:uppercase; margin-top:22px;">Admin login</div>
        <div style="font-family:ui-monospace,monospace; font-size:42px; letter-spacing:8px; color:#F8F9FD; margin-top:12px;">{{ $code }}</div>
      </td>
    </tr>
    <tr>
      <td style="padding:38px 40px;">
        <h1 style="margin:0 0 18px; font-size:28px; color:#1A1C4F;">Verify your login</h1>
        <p style="margin:0 0 24px; font-size:16px; line-height:26px; color:#3A3D4D;">
          Hi {{ $firstName }}, use the verification code above to finish signing in to the VIU admin panel.
        </p>
        <p style="margin:0; padding:20px 24px; border-left:3px solid #F57F20; background:#FAFAFC; color:#3A3D4D; font-size:14px; line-height:22px;">
          This code expires in {{ $codeExpiryMinutes }} {{ str('minute')->plural($codeExpiryMinutes) }}.
          If you did not attempt to sign in, you can safely ignore this email.
        </p>
      </td>
    </tr>
    <tr>
      <td style="padding:24px 40px 32px; text-align:center; color:#5F6677; font-size:12px;">
        <img src="{{ $logoDarkUrl }}" alt="VIU" width="44" height="28" style="display:inline-block; margin-bottom:12px;">
        <div>{{ $siteTagline }}<br>{{ $address }}</div>
        <div style="margin-top:12px;">
          Need help? <a href="mailto:{{ $supportEmail }}" style="color:#5F6677;">{{ $supportEmail }}</a>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
