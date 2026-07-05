@props(['section' => null])

@php
    $principles = $section ?? \App\Cms\Presenters\AboutPrinciplesPresenter::from([]);
@endphp

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
