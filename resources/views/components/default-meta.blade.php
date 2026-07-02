@php
    use App\Support\SiteSettings;
    use App\Support\TrackingSocialSettings;

    $siteTitle = SiteSettings::siteName() . (SiteSettings::siteTagline() ? ' | ' . SiteSettings::siteTagline() : '');
    $title = TrackingSocialSettings::defaultMetaTitle() ?? $siteTitle;
    $description = TrackingSocialSettings::defaultMetaDescription()
        ?? 'Viu uses predictive modeling to place your brand in front of homeowners up to 90 days before they decide to sell, securing your position before search even begins.';
    $keywords = TrackingSocialSettings::defaultMetaKeywords();
    $robots = TrackingSocialSettings::defaultRobots();
    $ogImage = TrackingSocialSettings::defaultOgImageUrl();
    $canonical = url()->current();
@endphp

@if ($description)
    <meta name="description" content="{{ $description }}">
@endif

@if ($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif

<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
@if ($description)
    <meta property="og:description" content="{{ $description }}">
@endif
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
@if ($description)
    <meta name="twitter:description" content="{{ $description }}">
@endif
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
