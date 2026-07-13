@php
    use App\Support\SiteSettings;

    $logoLightUrl = SiteSettings::logoLightUrl();
    $logoDarkUrl = SiteSettings::logoDarkUrl();
    $supportEmail = SiteSettings::supportEmail() ?? 'support@fullviu.com';
    $phoneNumber = SiteSettings::phoneNumber() ?? '+1 (406) 861-6520';
    $address = SiteSettings::address() ?? 'Montana markets, USA';
    $siteTagline = SiteSettings::siteTagline() ?? 'Own the market before they sell';
    $phoneHref = 'tel:'.preg_replace('/\D+/', '', $phoneNumber);
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="color-scheme" content="light">
  <meta name="supported-color-schemes" content="light">
  <title>A few items left to launch your brand</title>
  <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body,table,td,a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table,td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
    img { -ms-interpolation-mode:bicubic; border:0; line-height:100%; outline:none; text-decoration:none; }
    table { border-collapse:collapse !important; }
    body { margin:0 !important; padding:0 !important; width:100% !important; height:100% !important; }
    a { text-decoration:none; }
    .body-font { font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; }
    .display-font { font-family:'Inter','Helvetica Neue',Helvetica,Arial,sans-serif; }
    .mono-font { font-family:ui-monospace,'SFMono-Regular',Menlo,Consolas,monospace; }
    @media only screen and (max-width:600px) {
      .container { width:100% !important; }
      .px { padding-left:24px !important; padding-right:24px !important; }
      .zip-num { font-size:38px !important; letter-spacing:6px !important; }
      .h1 { font-size:28px !important; line-height:34px !important; }
      .stack { display:block !important; width:100% !important; }
      .stack-pad { padding:18px 24px !important; }
      .ticket-divider { display:none !important; }
    }
    a[x-apple-data-detectors] { color:inherit !important; text-decoration:none !important; }
  </style>
</head>
<body style="margin:0; padding:0; background-color:#F1F2F7;">
  <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all; font-family:sans-serif;">
    We're holding your production slot for ZIP {{ $zipCode }}. We just need your intake details to start building.
    &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F1F2F7;">
    <tr>
      <td align="center" style="padding:32px 12px;">
        <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
        <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">

          <tr>
            <td>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFFFFF; border:1px solid #E5E7EB; border-radius:0; overflow:hidden;">

                <tr>
                  <td style="background-color:#1A1C4F; padding:34px 40px 30px 40px;" class="px">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px 0;">
                      <tr>
                        <td align="left" valign="middle">
                          <img src="{{ $logoLightUrl }}" alt="VIU" width="54" height="34" style="display:block; border:0; outline:none; text-decoration:none; height:34px; width:54px; color:#FFFFFF; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:24px; font-weight:800; letter-spacing:2px;">
                        </td>
                        <td align="right" valign="middle" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:10px; letter-spacing:2.5px; color:#A9ADD8; text-transform:uppercase;">Territory&nbsp;Exclusivity</td>
                      </tr>
                    </table>
                    <div aria-hidden="true" style="height:1px; line-height:1px; font-size:0; background-color:#34376F; margin:0 0 22px 0;">&nbsp;</div>
                    <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:11px; letter-spacing:3px; color:#F57F20; text-transform:uppercase; margin-bottom:14px;">One step from launch</div>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #3D40A0; border-radius:0;">
                      <tr>
                        <td class="stack stack-pad" style="padding:20px 24px;" valign="middle">
                          <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:9px; letter-spacing:2.5px; color:#A9ADD8; text-transform:uppercase; margin-bottom:8px;">Territory</div>
                          <div class="zip-num mono-font" style="font-family:ui-monospace,'SFMono-Regular',Menlo,Consolas,monospace; font-size:42px; font-weight:500; letter-spacing:8px; color:#F8F9FD; line-height:1;">{{ $zipCode }}</div>
                        </td>
                        <td class="ticket-divider" aria-hidden="true" width="1" style="border-left:1px solid #3D40A0; font-size:0; line-height:0;">&nbsp;</td>
                        <td class="stack stack-pad" width="186" align="left" style="padding:20px 24px;" valign="middle">
                          <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:9px; letter-spacing:2.5px; color:#A9ADD8; text-transform:uppercase; margin-bottom:9px;">Status</div>
                          <span style="display:inline-block; border:1px solid #F57F20; padding:6px 11px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:10px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#F57F20;">Awaiting intake</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td class="px" style="padding:38px 40px 8px 40px;">
                    <h1 class="h1 display-font" style="margin:0 0 18px 0; font-family:'Inter','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:33px; line-height:40px; font-weight:800; color:#1A1C4F; letter-spacing:-0.3px;">
                      We&rsquo;re holding your slot. Let&rsquo;s start building.
                    </h1>
                    <p class="body-font" style="margin:0 0 16px 0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:16px; line-height:26px; color:#3A3D4D;">Hi {{ $firstName }},</p>
                    <p class="body-font" style="margin:0 0 28px 0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:16px; line-height:26px; color:#3A3D4D;">
                      Just checking in. We&rsquo;re holding the production slot for ZIP <strong style="color:#1A1C4F;">{{ $zipCode }}</strong> and still need your intake details before we can start building your brand.
                    </p>
                  </td>
                </tr>

                <tr>
                  <td class="px" style="padding:0 40px 0 40px;" align="center">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="border-radius:0;" bgcolor="#F57F20">
                          <!--[if mso]>
                          <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $intakeUrl }}" style="height:52px;v-text-anchor:middle;width:330px;" arcsize="0%" stroke="f" fillcolor="#F57F20">
                          <w:anchorlock/><center style="color:#1A1C4F;font-family:Helvetica,Arial,sans-serif;font-size:15px;font-weight:bold;">Pick up where you left off &rarr;</center>
                          </v:roundrect>
                          <![endif]-->
                          <!--[if !mso]><!-- -->
                          <a href="{{ $intakeUrl }}" style="display:inline-block; background-color:#F57F20; color:#1A1C4F; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; font-weight:700; line-height:52px; text-align:center; text-decoration:none; border-radius:0; padding:0 40px;">Pick up where you left off &nbsp;<span aria-hidden="true">&rarr;</span></a>
                          <!--<![endif]-->
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td class="px" style="padding:30px 40px 0 40px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFFFFF; border:1px solid #E5E7EB; border-radius:0;">
                      <tr>
                        <td style="padding:20px 24px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; line-height:23px; color:#3A3D4D;">
                          If something&rsquo;s blocking you (a file, a question, a detail you&rsquo;re unsure about), <strong style="color:#1A1C4F;">just reply.</strong> We&rsquo;ll sort it out together.
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td class="px" style="padding:30px 40px 38px 40px;">
                    <p class="body-font" style="margin:0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; line-height:22px; color:#3A3D4D;">
                      Geoff Crutcher<br>
                      <span style="color:#5F6677;">VIU Onboarding</span><br>
                      <a href="mailto:{{ $supportEmail }}" style="color:#1A1C4F; text-decoration:underline;">{{ $supportEmail }}</a><br><a href="{{ $phoneHref }}" style="color:#1A1C4F; text-decoration:underline;">{{ $phoneNumber }}</a>
                    </p>
                  </td>
                </tr>

              </table>
            </td>
          </tr>

          <tr>
            <td class="px" style="padding:26px 40px 8px 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center" style="padding-bottom:14px;">
                    <img src="{{ $logoDarkUrl }}" alt="VIU" width="44" height="28" style="display:inline-block; border:0; outline:none; text-decoration:none; height:28px; width:44px; color:#1A1C4F; font-family:'Inter','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:17px; font-weight:800; letter-spacing:2px;">
                  </td>
                </tr>
                <tr><td align="center" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; line-height:19px; color:#5F6677;">{{ $siteTagline }}<br>{{ $address }}</td></tr>
                <tr><td align="center" style="padding-top:14px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; line-height:19px; color:#5F6677;">You&rsquo;re receiving this because you inquired about a VIU territory.<br><a href="{{ $unsubscribeUrl }}" style="color:#5F6677; text-decoration:underline;">Unsubscribe</a>&nbsp; &bull; &nbsp;<a href="mailto:{{ $supportEmail }}" style="color:#5F6677; text-decoration:underline;">Contact us</a></td></tr>
              </table>
            </td>
          </tr>

        </table>
        <!--[if mso]></td></tr></table><![endif]-->
      </td>
    </tr>
  </table>
</body>
</html>
