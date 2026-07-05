@php
    use App\Support\TrackingSocialSettings;

    $socialLinks = TrackingSocialSettings::socialProfileLinks();
@endphp

@if (count($socialLinks) > 0)
    <div class="viu-footer__social">
        @foreach ($socialLinks as $link)
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $link['label'] }}">
                @include('components.social-icons.' . $link['platform'])
            </a>
        @endforeach
    </div>
@endif
