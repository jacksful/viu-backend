@php
    $home = url('/');
@endphp
<header class="site-header" id="site-header" data-viu-header>
    <div class="container site-header__inner">
        <a href="{{ $home }}" class="viu-nav__logo" aria-label="VIU home">
            <img src="{{ asset('viu/assets/images/logo-white.svg') }}" alt="{{ config('app.name', 'VIU') }}" />
        </a>

        <nav class="viu-nav__links" aria-label="Primary">
            <a class="viu-nav__link" href="{{ $home }}#advantage">The advantage</a>
            <a class="viu-nav__link" href="{{ $home }}#territory">Territory</a>
            <a class="viu-nav__link" href="{{ $home }}#exclusivity">Exclusivity</a>
            <a class="viu-nav__link" href="{{ $home }}#pricing">Pricing</a>
            <a class="viu-nav__link" href="{{ route('about') }}">About</a>
        </nav>

        <div class="viu-nav__cta">
            <button type="button" class="viu-btn viu-btn--primary viu-btn--sm" data-viu-modal-open>Check territory</button>
        </div>

        <button class="viu-nav__toggle" type="button" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu" data-viu-nav-toggle>
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="viu-nav__menu" id="mobile-menu">
        <nav class="container viu-nav__menu-inner" aria-label="Mobile">
            <a class="viu-nav__link" href="{{ $home }}#advantage" data-viu-nav-close>The advantage</a>
            <a class="viu-nav__link" href="{{ $home }}#territory" data-viu-nav-close>Territory</a>
            <a class="viu-nav__link" href="{{ $home }}#exclusivity" data-viu-nav-close>Exclusivity</a>
            <a class="viu-nav__link" href="{{ $home }}#pricing" data-viu-nav-close>Pricing</a>
            <a class="viu-nav__link" href="{{ route('about') }}" data-viu-nav-close>About</a>
            <button type="button" class="viu-btn viu-btn--primary viu-btn--sm viu-btn--full" data-viu-modal-open data-viu-nav-close>Check territory</button>
        </nav>
    </div>
</header>

@include('components.zip-modal')
@include('components.contact-modal')
