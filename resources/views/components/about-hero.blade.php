@props(['section' => null])

@php
    $hero = $section ?? \App\Cms\Presenters\AboutHeroPresenter::from([]);
    $titleLines = method_exists($hero, 'titleLines') ? $hero->titleLines() : [];
@endphp

<section class="alignfull bg-primary viu-legal__hero">
    <div class="container">
        <span class="viu-badge viu-badge--white">{{ $hero->badge_text }}</span>
        <h1 class="viu-legal__title">
            @foreach ($titleLines as $index => $line)
                @if ($index > 0)<br>@endif{{ $line }}
            @endforeach
        </h1>
        <p class="viu-about__lead">{{ $hero->lead }}</p>
    </div>
</section>
