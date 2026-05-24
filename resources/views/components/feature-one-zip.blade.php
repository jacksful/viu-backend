@php
    $s = $section ?? \App\Models\CmsTerritoryZipSection::singleton();
@endphp
<section id="territory" class="territory-zip py-5" style="background-color: #F8FAFC;">
    <div class="container">
        <div class="row align-items-stretch g-4 g-lg-5">
            {{-- Left: photo + status card --}}
            <div class="col-lg-6">
                <div class="territory-zip__visual position-relative overflow-hidden h-100">
                    <img
                        src="{{ $s->left_visual_image_url }}"
                        alt=""
                        class="territory-zip__bg-img position-absolute top-0 start-0 w-100 h-100"
                        width="900"
                        height="700"
                        loading="lazy"
                    >
                    <div class="territory-zip__visual-inner position-relative d-flex align-items-center justify-content-center p-3 p-md-5 py-5">
                        <article class="territory-zip__card">
                            <header class="territory-zip__card-head d-flex align-items-start gap-3 mb-3 mb-md-4">
                                <div class="territory-zip__card-icon d-flex align-items-center justify-content-center flex-shrink-0">
                                    <img src="{{ $s->left_card_icon_url }}" alt="" width="28" height="28" loading="lazy">
                                </div>
                                <div class="min-w-0">
                                    <p class="territory-zip__card-kicker mb-1">{{ $s->card_kicker }}</p>
                                    <h3 class="territory-zip__card-title mb-0">{{ $s->card_title }}</h3>
                                </div>
                            </header>
                            <ul class="territory-zip__checklist list-unstyled mb-0">
                                @foreach($s->checklistLines() as $line)
                                    <li class="territory-zip__check-row d-flex align-items-center gap-2">
                                        <img src="{{ $s->checklist_check_icon_url }}" alt="" class="territory-zip__check-ico flex-shrink-0" width="20" height="20" loading="lazy">
                                        <span class="territory-zip__check-text">{{ $line }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    </div>
                </div>
            </div>

            {{-- Right: copy + icons + quote --}}
            <div class="col-lg-6 d-flex flex-column">
                <span class="territory-zip__badge d-inline-block text-uppercase fw-bold mb-3 align-self-start">
                    {{ $s->badge_text }}
                </span>

                <h2 class="territory-zip__headline fw-bold mb-3">
                    <span class="d-block text-viu-navy">{{ $s->headline_primary }}</span>
                    <span class="d-block text-viu-orange">{{ $s->headline_accent }}</span>
                </h2>

                <p class="territory-zip__lead text-muted mb-4">
                    {{ $s->intro }}
                </p>

                <div class="territory-zip__feats row row-cols-1 row-cols-md-3 g-4 align-items-start ">
                    @foreach($s->featureList() as $idx => $feat)
                        <div class="col">
                            <div class="territory-zip__feat">
                                <div class="territory-zip__feat-ring d-flex align-items-center justify-content-center mx-auto">
                                    <img src="{{ $s->featureIconUrl($feat, $idx) }}" alt="" width="32" height="32" loading="lazy">
                                </div>
                                <span class="territory-zip__feat-label d-block text-center">{{ $feat['label'] ?? '' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="territory-zip__quote d-flex align-items-center gap-3 p-3 bg-white mt-5">
                    <div class="territory-zip__quote-icon d-flex align-items-center justify-content-center flex-shrink-0">
                        <img src="{{ $s->quote_icon_url }}" alt="" width="24" height="24" loading="lazy">
                    </div>
                    <p class="territory-zip__quote-text text-secondary fw-bold text-uppercase mb-0 small">
                        {{ $s->quote_text }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .territory-zip__visual {
        min-height: 380px;
        box-shadow: 0 12px 40px rgba(26, 30, 75, 0.1);
    }
    .territory-zip__bg-img {
        object-fit: cover;
        object-position: center;
    }
    .territory-zip__visual-inner {
        min-height: 380px;
        z-index: 1;
    }
    @media (min-width: 992px) {
        .territory-zip__visual,
        .territory-zip__visual-inner {
            min-height: 480px;
        }
    }
    .territory-zip__card {
        width: 100%;
        padding: 1.5rem 1.5rem 1.45rem;
        background: #ffffff;
        box-shadow:
            0 4px 6px -1px rgba(45, 49, 107, 0.06),
            0 12px 28px -8px rgba(15, 23, 42, 0.12),
            0 0 0 1px rgba(45, 49, 107, 0.04);
    }
    .territory-zip__card-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #ffe8d9;
    }
    .territory-zip__card-icon img {
        object-fit: contain;
    }
    .territory-zip__card-kicker {
        font-size: 0.6875rem;
        font-weight: 500;
        letter-spacing: 0.08em;
        color: #6b7280;
        line-height: 1.3;
    }
    .territory-zip__card-title {
        font-size: 1.0625rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        line-height: 1.2;
        color: #2d316b;
    }
    .territory-zip__checklist {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .territory-zip__check-row {
        background: #f9fafb;
        border-radius: 4px;
        padding: 32px 25px;
        min-height: 2.5rem;
    }
    .territory-zip__check-ico {
        width: 20px;
        height: 20px;
        object-fit: contain;
        display: block;
    }
    .territory-zip__check-text {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #6b7280;
        line-height: 1.4;
        text-align: left;
    }
    .territory-zip__badge {
        font-size: 0.65rem;
        letter-spacing: 0.1em;
        color: #4a4a55;
        background: #efe6d8;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
    }
    .territory-zip__headline {
        font-size: clamp(1.75rem, 3.5vw, 2.25rem);
        line-height: 1.12;
        letter-spacing: 0.02em;
    }
    .territory-zip__lead {
        font-size: 0.95rem;
        line-height: 1.65;
        max-width: 28rem;
    }
    .territory-zip__feats {
        --territory-feat-navy: #2d316b;
    }
    .territory-zip__feat {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.875rem;
        max-width: 200px;
        margin-left: auto;
        margin-right: auto;
    }
    .territory-zip__feat-ring {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #faf5f0;
        box-shadow: inset 0 0 0 1px rgba(242, 133, 39, 0.12);
    }
    .territory-zip__feat-ring img {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }
    .territory-zip__feat-label {
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        line-height: 1.3;
        color: var(--territory-feat-navy);
        font-family: 'Montserrat', system-ui, sans-serif;
    }
    .territory-zip__quote-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #ffe8d9;
    }
    .territory-zip__quote-text {
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        line-height: 1.45;
    }
</style>
@endpush
