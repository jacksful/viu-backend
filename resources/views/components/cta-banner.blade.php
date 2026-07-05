@props(['section' => null])

@php
    $s = $section ?? \App\Cms\Presenters\CtaBannerPresenter::from([]);
@endphp
<section class="alignfull bg-primary viu-cta">
    <div class="container section--lg">
        <div class="viu-cta__inner">
            <span class="viu-badge viu-badge--white">{{ $s->badge_text }}</span>
            <h2 class="viu-h2 viu-cta__title">{{ $s->title }}</h2>
            <p class="viu-cta__text">{{ $s->text }}</p>
            <div class="viu-cta__actions">
                <button class="viu-btn viu-btn--primary viu-btn--lg" type="button" data-viu-modal-open>{{ $s->primary_button_label }}</button>
                <button class="viu-btn viu-btn--outline viu-btn--lg" type="button" data-viu-contact-open>{{ $s->secondary_button_label }}</button>
            </div>
        </div>
    </div>
</section>
