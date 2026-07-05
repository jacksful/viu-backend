@php
    $home = url('/');
    $siteName = \App\Support\SiteSettings::siteName();
    $headerNavLinks = $headerNavLinks ?? \App\Cms\Support\SiteNavigation::headerLinks();
@endphp
<header class="site-header" id="site-header" data-viu-header>
    <div class="container site-header__inner">
        <a href="{{ $home }}" class="viu-nav__logo" aria-label="{{ $siteName }} home">
            <img src="{{ \App\Support\SiteSettings::logoLightUrl() }}" alt="{{ $siteName }}" />
        </a>

        <nav class="viu-nav__links" aria-label="Primary">
            @foreach ($headerNavLinks as $link)
                <a class="viu-nav__link" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
            @endforeach
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
            @foreach ($headerNavLinks as $link)
                <a class="viu-nav__link" href="{{ $link['url'] }}" data-viu-nav-close>{{ $link['label'] }}</a>
            @endforeach
            <button type="button" class="viu-btn viu-btn--primary viu-btn--sm viu-btn--full" data-viu-modal-open data-viu-nav-close>Check territory</button>
        </nav>
    </div>
</header>

@include('components.zip-modal')
@include('components.contact-modal')
