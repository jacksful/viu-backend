@if ($content = \App\Support\TrackingSocialSettings::googleSearchConsoleVerification())
    <meta name="google-site-verification" content="{{ $content }}">
@endif

@if ($content = \App\Support\TrackingSocialSettings::facebookDomainVerification())
    <meta name="facebook-domain-verification" content="{{ $content }}">
@endif
