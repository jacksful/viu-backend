<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'VIU') . ' - Real Estate Intelligence Platform')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

        <style>
            :root {
                --viu-navy: #1A1E4B;
                --viu-navy-mid: #252a63;
                --viu-orange: #F28527;
                --viu-gray: #F4F5F7;
                --viu-text: #1A1E4B;
            }

            body.home-page {
                font-family: 'Montserrat', system-ui, sans-serif;
                color: var(--viu-text);
                --site-header-height: 72px;
            }

            .site-header {
                position: sticky;
                top: 0;
                z-index: 1030;
                width: 100%;
                background: rgba(0, 0, 0, 0.05);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            }

            .site-header__logo-img {
                display: block;
                height: 40px;
                width: auto;
                max-width: min(220px, 42vw);
                object-fit: contain;
                object-position: left center;
            }

            .site-header__link {
                color: rgba(255, 255, 255, 0.98) !important;
                font-size: 0.875rem;
                font-weight: 500;
                white-space: nowrap;
            }

            .site-header__link:hover {
                color: #fff !important;
                opacity: 0.88;
            }

            .site-header__cta {
                background-color: #ff8a1a;
                color: #000 !important;
                font-weight: 800;
                font-size: 0.7rem;
                letter-spacing: 0.07em;
                text-transform: uppercase;
                padding: 0.65rem 1.1rem;
                border-radius: 0;
                border: none;
                line-height: 1.2;
                transition: filter 0.2s ease, background-color 0.2s ease;
            }

            .site-header__cta:hover {
                color: #000 !important;
                filter: brightness(1.05);
                background-color: #ff9a35;
            }

            /* Hero sits under sticky bar (full-bleed background) */
            .home-page .home-hero {
                margin-top: calc(-1 * var(--site-header-height));
            }

            .btn-viu-orange {
                background-color: var(--viu-orange);
                border: none;
                color: #fff !important;
                font-weight: 700;
                font-size: 0.75rem;
                letter-spacing: 0.06em;
                padding: 0.65rem 1.35rem;
                border-radius: 6px;
                text-transform: uppercase;
                transition: background-color 0.2s ease, transform 0.15s ease;
            }

            .btn-viu-orange:hover {
                background-color: #e07820;
                color: #fff !important;
            }

            .home-page .main-content {
                margin-top: 0;
            }

            .faq-item summary {
                cursor: pointer;
                list-style: none;
            }

            .faq-item summary::-webkit-details-marker {
                display: none;
            }

            .faq-item summary::marker {
                display: none;
            }

            .faq-section__badge {
                background-color: #ffe8dc;
                letter-spacing: 0.12em;
            }

            .faq-section__email-icon {
                width: 48px;
                height: 48px;
                background-color: #e8eaef;
            }

            .faq-section__panel .faq-item:last-child {
                border-bottom: 0 !important;
            }

            .faq-item__toggle {
                width: 32px;
                height: 32px;
                background-color: #e8eaef;
                transition: background-color 0.2s ease;
            }

            .faq-item__toggle-icon {
                transition: transform 0.25s ease;
            }

            .faq-item[open] .faq-item__toggle-icon {
                transform: rotate(45deg);
            }

            .text-viu-navy {
                color: var(--viu-navy) !important;
            }

            .text-viu-orange {
                color: var(--viu-orange) !important;
            }

            .bg-viu-navy {
                background-color: var(--viu-navy) !important;
            }

            .bg-viu-gray {
                background-color: var(--viu-gray) !important;
            }

            .home-page .btn-primary {
                background-color: var(--viu-orange);
                border-color: var(--viu-orange);
                color: #fff;
            }

            .home-page .btn-primary:hover {
                background-color: #e07820;
                border-color: #e07820;
                color: #fff;
            }

            @media (min-width: 992px) {
                #mobile-menu {
                    display: none !important;
                }
            }

            .home-page #solutions,
            .home-page #territory,
            .home-page #exclusivity,
            .home-page #pricing,
            .home-page #faq,
            .home-page #contact,
            .home-page #hero-zip {
                scroll-margin-top: calc(var(--site-header-height, 72px) + 12px);
            }
        </style>

        @stack('styles')
    </head>
    <body class="bg-white @yield('body_class')">
        @include('components.header')

        <main class="main-content">
            @yield('content')
        </main>

        @include('components.footer')

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        @stack('scripts')
    </body>
</html>
