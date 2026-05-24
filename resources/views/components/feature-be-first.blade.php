@php
    $s = $section ?? \App\Models\CmsStrategicWindowSection::singleton();
    $pct = min(100, max(0, (int) $s->card_metric_percent));
@endphp
<section id="solutions" class="solutions-advantage bg-white py-5">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6 order-2 order-lg-1">
                <span class="solutions-advantage__pill d-inline-block text-uppercase fw-bold mb-3">
                    {{ $s->badge_text }}
                </span>

                <h2 class="solutions-advantage__headline fw-bold mb-3">
                    <span class="d-block text-viu-navy">{{ $s->headline_primary }}</span>
                    <span class="d-block text-viu-orange">{{ $s->headline_accent }}</span>
                </h2>

                <p class="solutions-advantage__lead text-muted mb-4 mb-lg-5">
                    {{ $s->intro }}
                </p>

                <ul class="list-unstyled mb-0">
                    @foreach($s->featureList() as $idx => $feature)
                        <li class="solutions-advantage__item d-flex gap-3 @if(! $loop->last) mb-4 @endif">
                            <div class="solutions-advantage__icon-ring flex-shrink-0 d-flex align-items-center justify-content-center">
                                <img src="{{ $s->featureIconUrl($feature, $idx) }}" alt="" width="28" height="28" loading="lazy">
                            </div>
                            <div>
                                <strong class="solutions-advantage__item-title d-block text-uppercase text-viu-navy mb-1">{{ $feature['title'] ?? '' }}</strong>
                                <p class="solutions-advantage__item-desc text-muted small mb-0">
                                    {{ $feature['description'] ?? '' }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-lg-6 order-1 order-lg-2">
                <div class="solutions-advantage__visual position-relative">
                    <img
                        src="{{ $s->visual_image_url }}"
                        alt=""
                        class="solutions-advantage__photo w-100 d-block"
                        width="900"
                        height="600"
                        loading="lazy"
                    >
                    <div class="solutions-advantage__card-wrap position-absolute bottom-0 start-50 translate-middle-x w-100 px-5 pb-3 pb-md-4">
                        <div class="solutions-advantage__card bg-white mx-auto">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="solutions-advantage__card-icon d-flex align-items-center justify-content-center flex-shrink-0">
                                    <img src="{{ $s->card_icon_url }}" alt="" width="22" height="22" loading="lazy">
                                </div>
                                <div class="min-w-0">
                                    <p class="solutions-advantage__card-kicker text-viu-navy text-uppercase fw-bold mb-0 small">{{ $s->card_kicker }}</p>
                                    <p class="solutions-advantage__card-title text-viu-navy fw-bold mb-0">{{ $s->card_title }}</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <span class="small fw-bold text-viu-navy text-uppercase">{{ $s->card_metric_label }}</span>
                                <span class="small fw-bold text-viu-navy">{{ $pct }}%</span>
                            </div>
                            <div class="solutions-advantage__progress rounded-pill mb-3">
                                <div class="solutions-advantage__progress-fill rounded-pill" style="width: {{ $pct }}%;" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <hr class="solutions-advantage__rule my-3">
                            <p class="solutions-advantage__quote text-muted small mb-0">
                                {{ $s->card_quote }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .solutions-advantage__pill {
        font-size: 0.65rem;
        letter-spacing: 0.1em;
        color: var(--viu-navy);
        background: #ffe8d9;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
    }
    .solutions-advantage__headline {
        font-size: clamp(1.75rem, 3.5vw, 2.35rem);
        line-height: 1.15;
        letter-spacing: 0.02em;
    }
    .solutions-advantage__lead {
        font-size: 0.95rem;
        line-height: 1.65;
        max-width: 32rem;
    }
    .solutions-advantage__icon-ring {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #ffe8d9;
    }
    .solutions-advantage__icon-ring img {
        object-fit: contain;
    }
    .solutions-advantage__item-title {
        font-size: 0.8rem;
        letter-spacing: 0.06em;
    }
    .solutions-advantage__item-desc {
        line-height: 1.55;
    }
    .solutions-advantage__visual {
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(26, 30, 75, 0.12);
    }
    .solutions-advantage__photo {
        min-height: 280px;
        object-fit: cover;
        aspect-ratio: 4 / 3;
    }
    @media (min-width: 992px) {
        .solutions-advantage__photo {
            min-height: 420px;
        }
    }
    .solutions-advantage__card {
       width: 100%;
        padding: 1.1rem 1.25rem 1.15rem;
        border-radius: 4px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
    }
    .solutions-advantage__card-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #ffe8d9;
    }
    .solutions-advantage__card-kicker {
        font-size: 0.6rem;
        letter-spacing: 0.08em;
    }
    .solutions-advantage__card-title {
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .solutions-advantage__progress {
        height: 8px;
        background: #e8e8ec;
        overflow: hidden;
    }
    .solutions-advantage__progress-fill {
        height: 100%;
        background: var(--viu-orange);
    }
    .solutions-advantage__rule {
        border-color: rgba(0, 0, 0, 0.08);
        opacity: 1;
    }
    .solutions-advantage__quote {
        line-height: 1.5;
    }
</style>
@endpush
