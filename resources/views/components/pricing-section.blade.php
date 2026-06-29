@props(['zipcodes', 'section' => null])

@php
    $p = $section ?? \App\Models\CmsPricingSection::singleton();
@endphp

<section class="alignfull bg-surface" id="pricing">
    <div class="container section--lg">
        <div class="viu-split viu-split--reverse viu-split--gap-40">
            <div class="viu-split__media viu-reveal viu-reveal--left">
                <figure class="viu-figure viu-figure--pricing">
                    <img src="{{ $p->left_image_url }}" alt="Secure territory" width="1024" height="1024" loading="lazy" decoding="async" />
                    <div class="viu-card viu-figure__card viu-price-card">
                        <div class="viu-price-card__head">
                            <span class="viu-price-card__label">{{ $p->card_label_starting }}</span>
                            <div class="viu-price-card__amount">
                                <span class="viu-price-card__value">{{ $p->card_price_display }}</span>
                                <span class="viu-price-card__per">{{ $p->card_price_period }}</span>
                            </div>
                            <span class="viu-price-card__unit">{{ $p->card_per_label }}</span>
                        </div>
                        <p class="viu-price-card__note">{{ $p->card_footer_note }}</p>
                    </div>
                </figure>
            </div>

            <div class="viu-split__content viu-split__content--pad-l viu-reveal viu-reveal--right" style="--viu-reveal-delay:150ms">
                <div class="viu-intro viu-intro--mb">
                    <span class="viu-badge viu-badge--orange">{{ $p->badge_text }}</span>
                    <h2 class="viu-h2">{{ $p->heading }}</h2>
                    <p class="viu-intro__text">{{ $p->intro }}</p>
                </div>

                <div class="viu-pricing-list">
                    @foreach ($p->featureLines() as $idx => $line)
                        <div class="viu-pricing-list__item viu-reveal viu-reveal--up" style="--viu-reveal-delay:{{ ($idx + 2) * 100 }}ms">
                            <span class="viu-pricing-list__mark">
                                <svg class="viu-icon viu-icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
                            </span>
                            <span class="viu-pricing-list__text">{{ $line }}</span>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="viu-btn viu-btn--primary viu-btn--md viu-btn--full" data-viu-modal-open>{{ $p->cta_label }}</button>
            </div>
        </div>
    </div>
</section>
