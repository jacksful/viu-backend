@php
    $seo = $page->seo($preview ?? false);
@endphp

@if ($description = $seo->metaDescription())
    <meta name="description" content="{{ $description }}">
@endif

@if ($keywords = $seo->metaKeywords())
    <meta name="keywords" content="{{ $keywords }}">
@endif

<meta name="robots" content="{{ $seo->robots() }}">
<link rel="canonical" href="{{ $seo->canonicalUrl() }}">

<meta property="og:type" content="website">
<meta property="og:url" content="{{ $seo->canonicalUrl() }}">
<meta property="og:title" content="{{ $seo->ogTitle() }}">
@if ($ogDescription = $seo->ogDescription())
    <meta property="og:description" content="{{ $ogDescription }}">
@endif
@if ($ogImage = $seo->ogImageUrl())
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $seo->twitterImageUrl() ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo->twitterTitle() }}">
@if ($twitterDescription = $seo->twitterDescription())
    <meta name="twitter:description" content="{{ $twitterDescription }}">
@endif
@if ($twitterImage = $seo->twitterImageUrl())
    <meta name="twitter:image" content="{{ $twitterImage }}">
@endif

@foreach ($seo->customMetaTags() as $tag)
    <meta {{ $tag['type'] }}="{{ $tag['key'] }}" content="{{ $tag['value'] }}">
@endforeach
