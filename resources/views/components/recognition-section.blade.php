@php
    $s = $section ?? \App\Models\CmsRecognitionSection::singleton();
    $pct = $s->progressPercentClamped();
@endphp
<section class="alignfull bg-primary" id="exclusivity">
    <div class="container section">
        <div class="viu-split">
            <div class="viu-split__content viu-split__content--pad-r viu-reveal viu-reveal--left">
                <div class="viu-intro viu-intro--snug viu-intro--mb-snug">
                    <span class="viu-badge viu-badge--white">{{ $s->badge_text }}</span>
                    <h2 class="viu-h2 viu-on-dark">
                        {{ $s->headline_line_1 }}<br>
                        {{ $s->headline_line_2 }}<br>
                        {{ $s->headline_line_3 }}
                    </h2>
                    <p class="viu-intro__text viu-intro__text--muted">{{ $s->intro }}</p>
                </div>

                <div class="viu-authority">
                    <div class="viu-authority__row">
                        <div class="viu-authority__card"><p>{{ $s->box_top_left }}</p></div>
                        <div class="viu-authority__card"><p>{{ $s->box_top_right }}</p></div>
                    </div>
                    <div class="viu-reveal viu-reveal--up" style="--viu-reveal-delay:200ms">
                        <div class="viu-authority__card">
                            <p>{{ $s->box_wide_body }}</p>
                            <span class="viu-authority__tag">{{ $s->box_wide_accent }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="viu-split__media viu-reveal viu-reveal--right" style="--viu-reveal-delay:200ms">
                <figure class="viu-figure viu-figure--square">
                    <img src="{{ $s->right_image_url }}" alt="Brand authority visualization" width="490" height="1024" loading="lazy" decoding="async" />
                    <div class="viu-card viu-figure__card">
                        <div class="viu-card__head viu-card__head--stacked">
                            <img src="{{ $s->card_logo_url }}" alt="VIU" class="viu-card__logo" />
                            <div class="viu-card__meta">
                                <span class="viu-card__eyebrow">{{ $s->card_kicker }}</span>
                                <span class="viu-card__title">{{ $s->card_title }}</span>
                            </div>
                        </div>
                        <div class="viu-card__meta">
                            <div class="viu-card__row">
                                <span class="viu-card__stat">{{ $s->card_progress_label_left }}</span>
                                <span class="viu-card__stat">{{ $s->card_progress_label_right }}</span>
                            </div>
                            <div class="viu-progress" data-viu-progress style="--viu-progress-value:{{ $pct }}%"><div class="viu-progress__fill"></div></div>
                        </div>
                    </div>
                </figure>
            </div>
        </div>
    </div>
</section>
