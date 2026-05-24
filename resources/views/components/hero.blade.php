@php
    $heroImageUrl = $hero->image_url ?? asset('image/hero-banner.png');
    $titleLines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $hero->title ?? ''), fn ($line) => $line !== ''));
    if ($titleLines === []) {
        $titleLines = ['Own the market', 'before they sell'];
    }
@endphp
<section class="home-hero position-relative text-white overflow-hidden" aria-label="Hero">
    <div class="home-hero__bg" style="background-image: url('{{ $heroImageUrl }}');" role="img" aria-label=""></div>
    <div class="home-hero__overlay" aria-hidden="true"></div>

    <div class="container position-relative z-1">
        <div class="row align-items-center min-vh-100" style="min-height: min(92vh, 920px); padding-top: calc(var(--site-header-height, 72px) + 2.5rem); padding-bottom: 3rem;">
            <div class="col-lg-7 col-xl-6 text-start pe-lg-4">
                <h1 class="home-hero__title text-uppercase text-white mb-3 mb-lg-4">
                    @foreach ($titleLines as $line)
                        <span class="d-block">{{ $line }}</span>
                    @endforeach
                </h1>

                <p class="home-hero__lead text-white mb-4 mb-lg-5">
                    {{ $hero->description ?? '' }}
                </p>

                <div id="availability-messages-shower" class="w-100 mb-3"></div>

                <div id="hero-zip" class="home-hero__cta-wrap mb-4 mb-lg-5">
                    <div class="home-hero__cta d-flex flex-column flex-sm-row align-items-stretch">
                        <div class="home-hero__input-wrap d-flex align-items-center flex-grow-1 px-3 py-2">
                            <i class="bi bi-geo-alt home-hero__input-icon flex-shrink-0 me-2" aria-hidden="true"></i>
                            <input type="text" id="zipcode_search" name="zipcode_search" inputmode="numeric" autocomplete="postal-code"
                                class="form-control home-hero__input border-0 shadow-none flex-grow-1"
                                placeholder="Enter ZIP Code">
                        </div>
                        <button type="button" id="hero-zip-search-btn" onclick="checkAvailability()" class="btn home-hero__btn-cta text-uppercase fw-bold flex-shrink-0 px-4 py-3">
                            Secure territory
                        </button>
                    </div>
                </div>

                <ul class="home-hero__trust list-unstyled d-flex flex-column flex-sm-row flex-wrap gap-3 gap-sm-4 mb-0" role="list">
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check home-hero__trust-icon flex-shrink-0" aria-hidden="true"></i>
                        <span class="home-hero__trust-text text-uppercase">1 agent per ZIP</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check home-hero__trust-icon flex-shrink-0" aria-hidden="true"></i>
                        <span class="home-hero__trust-text text-uppercase">98% retention</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check home-hero__trust-icon flex-shrink-0" aria-hidden="true"></i>
                        <span class="home-hero__trust-text text-uppercase">Exclusive access</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .home-hero__bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }
    /* Left-heavy overlay: readable copy on left, landscape visible on right */
    .home-hero__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            90deg,
            rgba(18, 18, 45, 0.92) 0%,
            rgba(26, 30, 75, 0.78) 28%,
            rgba(26, 30, 75, 0.35) 55%,
            rgba(26, 30, 75, 0.08) 78%,
            transparent 100%
        );
        pointer-events: none;
    }
    @media (max-width: 767.98px) {
        .home-hero__overlay {
            background: linear-gradient(
                180deg,
                rgba(18, 18, 45, 0.88) 0%,
                rgba(26, 30, 75, 0.65) 45%,
                rgba(26, 30, 75, 0.4) 100%
            );
        }
    }
    .home-hero__title {
        font-weight: 800;
        letter-spacing: 0.02em;
        line-height: 1.05;
        font-size: clamp(2rem, 5vw, 3.35rem);
    }
    .home-hero__lead {
        font-size: clamp(0.95rem, 1.35vw, 1.125rem);
        line-height: 1.55;
        font-weight: 400;
        max-width: 38rem;
        opacity: 0.98;
    }
    .home-hero__cta {
        max-width: 36rem;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }
    .home-hero__input-wrap {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.45);
        border-radius: 6px 0 0 6px;
    }
    @media (min-width: 576px) {
        .home-hero__input-wrap {
            border-right: none;
        }
    }
    @media (max-width: 575.98px) {
        .home-hero__input-wrap {
            border-radius: 6px 6px 0 0;
        }
    }
    .home-hero__input-icon {
        font-size: 1.15rem;
        color: rgba(255, 255, 255, 0.95);
    }
    .home-hero__input {
        background: transparent !important;
        color: #fff !important;
        font-size: 1rem;
        font-weight: 500;
        min-height: 48px;
    }
    .home-hero__input::placeholder {
        color: rgba(255, 255, 255, 0.65);
    }
    .home-hero__input:focus {
        box-shadow: none;
        outline: none;
    }
    .home-hero__btn-cta {
        background-color: #F2822D;
        border: none;
        color: #fff !important;
        font-size: 0.8rem;
        letter-spacing: 0.06em;
        border-radius: 0 6px 6px 0;
        min-height: 48px;
        transition: background-color 0.2s ease;
    }
    .home-hero__btn-cta:hover {
        background-color: #e07828;
        color: #fff !important;
    }
    @media (max-width: 575.98px) {
        .home-hero__btn-cta {
            border-radius: 0 0 6px 6px;
        }
    }
    .home-hero__trust-icon {
        font-size: 1.125rem;
        color: rgba(255, 255, 255, 0.95);
    }
    .home-hero__trust-text {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }
</style>
@endpush
