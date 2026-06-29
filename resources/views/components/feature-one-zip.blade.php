@php
    $s = $section ?? \App\Models\CmsTerritoryZipSection::singleton();
@endphp
<section class="alignfull bg-surface-alt" id="territory">
    <div class="container section">
        <div class="viu-split viu-split--reverse">
            <div class="viu-split__media viu-reveal viu-reveal--left">
                <figure class="viu-figure viu-figure--tall">
                    <img src="{{ $s->left_visual_image_url }}" alt="Territory ownership map" width="1024" height="1024" loading="lazy" decoding="async" />
                    <div class="viu-card viu-figure__card viu-figure__card--wide">
                        <div class="viu-card__head">
                            <span class="viu-icon-box viu-icon-box--orange viu-icon-box--md">
                                <img class="viu-icon viu-icon--lg" src="{{ $s->left_card_icon_url }}" alt="" width="24" height="24" loading="lazy" />
                            </span>
                            <div class="viu-card__meta">
                                <span class="viu-card__eyebrow">{{ $s->card_kicker }}</span>
                                <span class="viu-card__title">{{ $s->card_title }}</span>
                            </div>
                        </div>
                        <div class="viu-checklist">
                            @foreach($s->checklistLines() as $line)
                                <div class="viu-checklist__item">
                                    <span class="viu-checklist__mark">
                                        <svg class="viu-icon viu-icon--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10.656V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12.344"/><path d="m9 11 3 3L22 4"/></svg>
                                    </span>
                                    <span class="viu-checklist__label">{{ $line }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </figure>
            </div>

            <div class="viu-split__content viu-split__content--pad-l viu-reveal viu-reveal--right" style="--viu-reveal-delay:150ms">
                <div class="viu-intro viu-intro--mb">
                    <span class="viu-badge viu-badge--orange">{{ $s->badge_text }}</span>
                    <h2 class="viu-h2">{{ $s->headline_primary }}<br><span class="viu-accent">{{ $s->headline_accent }}</span></h2>
                    <p class="viu-intro__text">{{ $s->intro }}</p>
                </div>

                <div class="viu-exclusivity">
                    @foreach($s->featureList() as $idx => $feat)
                        <div class="viu-exclusivity__item viu-reveal viu-reveal--up" style="--viu-reveal-delay:{{ ($idx + 2) * 100 }}ms">
                            <span class="viu-icon-box viu-icon-box--warm viu-icon-box--md">
                                <img class="viu-icon viu-icon--lg viu-icon--thin" src="{{ $s->featureIconUrl($feat, $idx) }}" alt="" width="24" height="24" loading="lazy" />
                            </span>
                            <h3 class="viu-h3 viu-exclusivity__label">{{ $feat['label'] ?? '' }}</h3>
                        </div>
                    @endforeach
                </div>

                <div class="viu-reveal viu-reveal--up" style="--viu-reveal-delay:500ms">
                    <div class="viu-quote">
                        <div class="viu-quote__inner">
                            <span class="viu-icon-box viu-icon-box--orange viu-icon-box--md">
                                <img class="viu-icon viu-icon--lg" src="{{ $s->quote_icon_url }}" alt="" width="24" height="24" loading="lazy" />
                            </span>
                            <p class="viu-quote__text">"{{ $s->quote_text }}"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
