@if (\App\Support\TrackingSocialSettings::googleTagManagerEnabled())
    @include('components.tracking.google-tag-manager-body', [
        'containerId' => \App\Support\TrackingSocialSettings::googleTagManagerId(),
    ])
@endif
