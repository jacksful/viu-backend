@props(['section' => null])

@php
    $hero = $section ?? \App\Cms\Presenters\LegalHeroPresenter::from([]);
@endphp

<section class="alignfull bg-primary viu-legal__hero">
    <div class="container">
        <div class="viu-legal__col">
            <span class="viu-badge viu-badge--white">{{ $hero->badge_text }}</span>
            <h1 class="viu-legal__title">{{ $hero->title }}</h1>
            @if ($hero->last_updated)
                <p class="viu-legal__meta">Last updated {{ $hero->last_updated }}</p>
            @endif
        </div>
    </div>
</section>
