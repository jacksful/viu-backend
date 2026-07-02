@if (\App\Support\TrackingSocialSettings::googleAnalyticsEnabled())
    @include('components.tracking.google-analytics', [
        'measurementId' => \App\Support\TrackingSocialSettings::googleAnalyticsMeasurementId(),
    ])
@endif

@if (\App\Support\TrackingSocialSettings::googleTagManagerEnabled())
    @include('components.tracking.google-tag-manager-head', [
        'containerId' => \App\Support\TrackingSocialSettings::googleTagManagerId(),
    ])
@endif

@if (\App\Support\TrackingSocialSettings::facebookPixelEnabled())
    @include('components.tracking.facebook-pixel', [
        'pixelId' => \App\Support\TrackingSocialSettings::facebookPixelId(),
    ])
@endif

@if (\App\Support\TrackingSocialSettings::tiktokPixelEnabled())
    @include('components.tracking.tiktok-pixel', [
        'pixelId' => \App\Support\TrackingSocialSettings::tiktokPixelId(),
    ])
@endif

@if (\App\Support\TrackingSocialSettings::linkedinInsightEnabled())
    @include('components.tracking.linkedin-insight', [
        'partnerId' => \App\Support\TrackingSocialSettings::linkedinInsightTagId(),
    ])
@endif

@if (\App\Support\TrackingSocialSettings::pinterestTagEnabled())
    @include('components.tracking.pinterest-tag', [
        'tagId' => \App\Support\TrackingSocialSettings::pinterestTagId(),
    ])
@endif

@if (\App\Support\TrackingSocialSettings::twitterPixelEnabled())
    @include('components.tracking.twitter-pixel', [
        'pixelId' => \App\Support\TrackingSocialSettings::twitterPixelId(),
    ])
@endif

@if (\App\Support\TrackingSocialSettings::snapchatPixelEnabled())
    @include('components.tracking.snapchat-pixel', [
        'pixelId' => \App\Support\TrackingSocialSettings::snapchatPixelId(),
    ])
@endif
