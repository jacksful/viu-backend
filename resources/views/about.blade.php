@extends('layouts.app')

@section('title', 'About | ' . config('app.name', 'VIU'))

@section('body_class', 'about-page')

@section('content')
    @php
        $hero = $aboutHero ?? \App\Models\CmsAboutHeroSection::singleton();
        $mission = $aboutMission ?? \App\Models\CmsAboutMissionSection::singleton();
        $principles = $aboutPrinciples ?? \App\Models\CmsAboutPrinciplesSection::singleton();
        $titleLines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $hero->title ?? ''), fn ($line) => $line !== ''));
        if ($titleLines === []) {
            $titleLines = ['We put your brand in front of the market', 'before it moves'];
        }
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

    <section class="alignfull bg-surface-alt">
        <div class="container section">
            <div class="viu-intro viu-intro--mb" style="max-width:640px;">
                <span class="viu-badge viu-badge--orange">{{ $principles->badge_text }}</span>
                <h2 class="viu-h2">{{ $principles->heading }}</h2>
            </div>
            <div class="viu-about__grid">
                @foreach ($principles->principleList() as $principle)
                    <div class="viu-about__card">
                        <span class="viu-icon-box viu-icon-box--orange viu-icon-box--md">
                            <svg class="viu-icon viu-icon--lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                        </span>
                        <h3 class="viu-h3">{{ $principle['title'] ?? '' }}</h3>
                        <p>{{ $principle['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('components.cta-banner')
@endsection
