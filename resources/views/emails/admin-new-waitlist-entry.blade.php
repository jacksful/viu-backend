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
  <title>New waitlist entry: {{ $waitlist->name }}</title>
</head>
<body style="margin:0; padding:32px 12px; background-color:#F1F2F7; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; margin:0 auto; background:#FFFFFF; border:1px solid #E5E7EB;">
    <tr>
      <td style="background-color:#1A1C4F; padding:34px 40px;">
        <img src="{{ $logoLightUrl }}" alt="VIU" width="54" height="34" style="display:block;">
        <div style="font-size:11px; letter-spacing:3px; color:#F57F20; text-transform:uppercase; margin-top:22px;">New waitlist entry</div>
        <div style="font-family:ui-monospace,monospace; font-size:42px; letter-spacing:8px; color:#F8F9FD; margin-top:12px;">{{ $waitlist->zip_code }}</div>
      </td>
    </tr>
    <tr>
      <td style="padding:38px 40px;">
        <h1 style="margin:0 0 18px; font-size:28px; color:#1A1C4F;">Someone joined the waitlist.</h1>
        <p style="margin:0 0 24px; font-size:16px; line-height:26px; color:#3A3D4D;">Review the submission and follow up from the admin panel.</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E5E7EB;">
          <tr><td style="padding:14px 20px; color:#5F6677;">Name</td><td align="right" style="padding:14px 20px; font-weight:600; color:#1A1C4F;">{{ $waitlist->name }}</td></tr>
          <tr><td style="padding:14px 20px; color:#5F6677;">Email</td><td align="right" style="padding:14px 20px; font-weight:600; color:#1A1C4F;">{{ $waitlist->email }}</td></tr>
          @if(filled($waitlist->phone))
          <tr><td style="padding:14px 20px; color:#5F6677;">Phone</td><td align="right" style="padding:14px 20px; font-weight:600; color:#1A1C4F;">{{ $waitlist->phone }}</td></tr>
          @endif
          <tr><td style="padding:14px 20px; color:#5F6677;">ZIP code</td><td align="right" style="padding:14px 20px; font-weight:600; color:#1A1C4F;">{{ $waitlist->zip_code }}</td></tr>
        </table>
        @if(filled($waitlist->message))
        <p style="margin:24px 0 0; padding:20px 24px; border-left:3px solid #F57F20; background:#FAFAFC; color:#3A3D4D;"><strong style="color:#1A1C4F;">Message:</strong><br>{{ $waitlist->message }}</p>
        @endif
        <p style="margin:28px 0 0; text-align:center;">
          <a href="{{ $adminUrl }}" style="display:inline-block; background:#F57F20; color:#1A1C4F; font-weight:700; text-decoration:none; padding:14px 32px;">View in admin</a>
        </p>
      </td>
    </tr>
    <tr>
      <td style="padding:24px 40px 32px; text-align:center; color:#5F6677; font-size:12px;">
        <img src="{{ $logoDarkUrl }}" alt="VIU" width="44" height="28" style="display:inline-block; margin-bottom:12px;">
        <div>{{ $siteTagline }}<br>{{ $address }}</div>
        <div style="margin-top:12px;">
          Geoff Crutcher<br>
          VIU Territory Specialist<br>
          <a href="mailto:geoff@fullviu.com" style="color:#5F6677;">geoff@fullviu.com</a>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
