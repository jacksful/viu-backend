<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        document.documentElement.classList.remove('no-js');
        document.documentElement.classList.add('js');
    </script>
    <title>@yield('title', config('app.name', 'VIU') . ' | Own the market before they sell')</title>
    <meta name="description" content="Viu uses predictive modeling to place your brand in front of homeowners up to 90 days before they decide to sell, securing your position before search even begins." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" />
    <link rel="icon" href="{{ asset('viu/assets/images/logo-dark.svg') }}" type="image/svg+xml" />

    <link rel="stylesheet" href="{{ asset('viu/assets/css/01-tokens.css') }}" />
    <link rel="stylesheet" href="{{ asset('viu/assets/css/02-base.css') }}" />
    <link rel="stylesheet" href="{{ asset('viu/assets/css/03-components.css') }}" />
    <link rel="stylesheet" href="{{ asset('viu/assets/css/04-sections.css') }}" />
    <link rel="stylesheet" href="{{ asset('viu/assets/css/05-utilities.css') }}" />

    @stack('styles')
</head>
<body class="@yield('body_class')">
    @include('components.header')

    <main class="site-main">
        @yield('content')
    </main>

    @include('components.footer')

    <script>
        window.VIU_CONFIG = {
            csrfToken: @json(csrf_token()),
            zipCheckUrl: @json(route('leads.check-availability')),
            leadStoreUrl: @json(route('leads.store')),
            stripeCheckoutUrl: @json(route('stripe.checkout')),
            contactStoreUrl: @json(route('contacts.store')),
        };
    </script>
    <script src="{{ asset('viu/assets/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
