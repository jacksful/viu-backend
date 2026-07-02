@props(['section' => null])

@php
    $mission = $section ?? \App\Cms\Presenters\AboutMissionPresenter::from([]);
@endphp

<section class="alignfull bg-surface">
    <div class="container section">
        <div class="viu-split">
            <div class="viu-split__content viu-split__content--pad-r">
                <div class="viu-intro viu-intro--snug viu-intro--mb-snug">
                    <span class="viu-badge viu-badge--orange">{{ $mission->badge_text }}</span>
                    <h2 class="viu-h2">{{ $mission->headline }}</h2>
                    <p class="viu-intro__text">{{ $mission->intro_text }}</p>
                </div>
                @if ($mission->body_middle)
                    <p class="viu-intro__text" style="margin-bottom: var(--viu-space-4);">{{ $mission->body_middle }}</p>
                @endif
                @if ($mission->body_last)
                    <p class="viu-intro__text">{{ $mission->body_last }}</p>
                @endif
            </div>
            <div class="viu-split__media">
                <figure class="viu-figure viu-figure--square">
                    <img src="{{ $mission->image_url }}" alt="Aerial view of a market landscape at dawn" width="1280" height="853" loading="lazy" decoding="async" />
                </figure>
            </div>
        </div>
    </div>
</section>
