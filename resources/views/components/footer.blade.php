<footer id="contact" class="site-footer text-white">
    <div class="container py-5 pb-lg-5">
        <div class="row align-items-stretch g-4 g-xl-5 pb-5 pb-lg-5">
            <div class="col-lg-5 col-xl-4">
                <a href="{{ url('/') }}" class="d-inline-block mb-3 mb-lg-4">
                    <img src="{{ asset('image/viu-header-logo.png') }}" alt="{{ config('app.name', 'VIU') }}" class="site-footer__logo" width="200" height="40" loading="lazy">
                </a>
                <p class="site-footer__lead mb-4 mb-lg-4">
                    Predictive brand positioning for elite real estate professionals. Our technology identifies intent before search patterns emerge, securing your territory while others are still waiting for listings.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="site-footer__social d-inline-flex align-items-center justify-content-center text-decoration-none" aria-label="LinkedIn">
                        <img src="{{ asset('image/Link.png') }}" alt="" width="20" height="20" class="site-footer__social-img">
                    </a>
                    <a href="#" class="site-footer__social d-inline-flex align-items-center justify-content-center text-decoration-none" aria-label="Twitter">
                        <img src="{{ asset('image/twitter.png') }}" alt="" width="20" height="20" class="site-footer__social-img">
                    </a>
                    <a href="#" class="site-footer__social d-inline-flex align-items-center justify-content-center text-decoration-none" aria-label="Facebook">
                        <img src="{{ asset('image/facebook.png') }}" alt="" width="20" height="20" class="site-footer__social-img">
                    </a>
                </div>
            </div>
            <div class="col-lg-7 col-xl-8 d-flex">
                <div class="site-footer__cta-card w-100 d-flex flex-column justify-content-center p-4 p-lg-5">
                    <h2 class="site-footer__cta-headline text-uppercase text-white mb-4">
                        The best time to be known is before you're needed
                    </h2>
                    <a href="#pricing" class="site-footer__cta-btn align-self-start text-uppercase text-decoration-none fw-bold">
                        Claim your zip now
                    </a>
                </div>
            </div>
        </div>

        <div class="site-footer__bar pt-4 pt-lg-4 border-top border-white border-opacity-10">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 g-lg-4 align-items-center small text-white text-opacity-85">
                <div class="col text-center text-xl-start">
                    <p class="mb-0 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.06em;">
                        © {{ date('Y') }} VIU Real Estate Solutions. All rights reserved.
                    </p>
                </div>
                <div class="col text-center text-xl-start">
                    <span class="d-inline-flex align-items-center justify-content-center justify-content-xl-start gap-2">
                        <i class="bi bi-geo-alt flex-shrink-0 opacity-90"></i>
                        <span>Montana Markets, USA</span>
                    </span>
                </div>
                <div class="col text-center text-xl-start">
                    <span class="d-inline-flex align-items-center justify-content-center justify-content-xl-start gap-2">
                        <i class="bi bi-envelope flex-shrink-0 opacity-90"></i>
                        <a href="mailto:support@viu.com" class="text-white text-decoration-none text-opacity-90">support@viu.com</a>
                    </span>
                </div>
                <div class="col text-center text-xl-end">
                    <span class="d-inline-flex align-items-center justify-content-center justify-content-xl-end gap-2">
                        <i class="bi bi-shield-check flex-shrink-0 opacity-90"></i>
                        <span>Secure Licensing</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <style>
        .site-footer {
            background-color: #1A1C4F;
            font-family: 'Montserrat', system-ui, sans-serif;
        }

        .site-footer__logo {
            display: block;
            height: auto;
            max-height: 40px;
            width: auto;
            max-width: min(220px, 55vw);
            object-fit: contain;
            object-position: left center;
        }

        .site-footer__lead {
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.9rem;
            line-height: 1.65;
            max-width: 26rem;
        }

        .site-footer__social {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            transition: background-color 0.2s ease;
        }

        .site-footer__social:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        .site-footer__social-img {
            display: block;
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        .site-footer__cta-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .site-footer__cta-headline {
            font-size: clamp(1.15rem, 2.5vw, 1.65rem);
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: 0.02em;
            margin-bottom: 0;
        }

        .site-footer__cta-btn {
            background-color: #f28531;
            color: #141432 !important;
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            padding: 0.75rem 1.35rem;
            border-radius: 2px;
            transition: filter 0.2s ease, background-color 0.2s ease;
        }

        .site-footer__cta-btn:hover {
            color: #141432 !important;
            filter: brightness(1.06);
        }

        .site-footer__bar {
            margin-top: 0;
        }
    </style>
</footer>
