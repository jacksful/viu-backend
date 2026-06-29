@php
    $s = $section ?? \App\Models\CmsStrategicWindowSection::singleton();
    $pct = min(100, max(0, (int) $s->card_metric_percent));
@endphp
<section class="alignfull bg-surface" id="advantage">
    <div class="container section">
        <div class="viu-split">
            <div class="viu-split__content viu-split__content--pad-r viu-reveal viu-reveal--left">
                <div class="viu-intro viu-intro--snug viu-intro--mb-snug">
                    <span class="viu-badge viu-badge--orange">{{ $s->badge_text }}</span>
                    <h2 class="viu-h2">{{ $s->headline_primary }}<br><span class="viu-accent">{{ $s->headline_accent }}</span></h2>
                    <p class="viu-intro__text">{{ $s->intro }}</p>
                </div>

                <div class="viu-feature-list">
                    @foreach($s->featureList() as $idx => $feature)
                        <div class="viu-feature viu-reveal viu-reveal--up" @if($idx > 0) style="--viu-reveal-delay:{{ $idx * 120 }}ms" @endif>
                            <span class="viu-icon-box viu-icon-box--orange viu-icon-box--md">
                                <img class="viu-icon viu-icon--lg" src="{{ $s->featureIconUrl($feature, $idx) }}" alt="" width="24" height="24" loading="lazy" />
                            </span>
                            <div class="viu-feature__body">
                                <h3 class="viu-h3 viu-feature__title">{{ $feature['title'] ?? '' }}</h3>
                                <p class="viu-feature__text">{{ $feature['description'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="viu-split__media viu-reveal viu-reveal--right" style="--viu-reveal-delay:200ms">
                <figure class="viu-figure viu-figure--square">
                    <img src="{{ $s->visual_image_url }}" alt="Real estate market landscape at dawn" width="1280" height="853" loading="lazy" decoding="async" />
                    <div class="viu-card viu-figure__card">
                        <div class="viu-card__head viu-card__head--top">
                            <span class="viu-icon-box viu-icon-box--orange viu-icon-box--md">
                                <img class="viu-icon viu-icon--lg" src="{{ $s->card_icon_url }}" alt="" width="24" height="24" loading="lazy" />
                            </span>
                            <div class="viu-card__meta">
                                <span class="viu-card__eyebrow">{{ $s->card_kicker }}</span>
                                <span class="viu-card__title">{{ $s->card_title }}</span>
                            </div>
                        </div>
                        <div class="viu-card__meta">
                            <div class="viu-card__row">
                                <span class="viu-card__stat">{{ $s->card_metric_label }}</span>
                                <span class="viu-card__stat">{{ $pct }}%</span>
                            </div>
                            <div class="viu-progress" data-viu-progress style="--viu-progress-value:{{ $pct }}%"><div class="viu-progress__fill"></div></div>
                            <p class="viu-card__quote">"{{ $s->card_quote }}"</p>
                        </div>
                    </div>
                </figure>
            </div>
        </div>
    </div>
</section>
