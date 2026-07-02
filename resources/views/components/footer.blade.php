@php
    $home = url('/');
    $siteName = \App\Support\SiteSettings::siteName();
    $siteTagline = \App\Support\SiteSettings::siteTagline();
    $supportEmail = \App\Support\SiteSettings::supportEmail();
    $phoneNumber = \App\Support\SiteSettings::phoneNumber();
    $address = \App\Support\SiteSettings::address();
    $footerNavLinks = $footerNavLinks ?? \App\Cms\Support\SiteNavigation::footerLinks();
    $copyrightNavLinks = $copyrightNavLinks ?? \App\Cms\Support\SiteNavigation::copyrightLinks();
@endphp
<footer class="site-footer" id="contact">
    <div class="container">
        <div class="viu-footer__grid">
            <div class="viu-footer__brand">
                <img src="{{ \App\Support\SiteSettings::footerLogoUrl() }}" alt="{{ $siteName }}" />
                @if ($siteTagline)
                    <p class="viu-footer__desc">{{ $siteTagline }}</p>
                @else
                    <p class="viu-footer__desc">
                        Predictive brand positioning for elite real estate professionals. Our
                        technology identifies intent before search patterns emerge, securing your
                        territory while others are still waiting for listings.
                    </p>
                @endif
                @include('components.social-links')
            </div>

            <nav class="viu-footer__col" aria-label="Footer">
                <h3 class="viu-footer__col-title">Explore</h3>
                @foreach ($footerNavLinks as $link)
                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="viu-footer__col">
                <h3 class="viu-footer__col-title">Contact</h3>
                @if ($supportEmail)
                    <a href="mailto:{{ $supportEmail }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg> {{ $supportEmail }}</a>
                @endif
                @if ($phoneNumber)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $phoneNumber) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> {{ $phoneNumber }}</a>
                @endif
                @if ($address)
                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg> {{ $address }}</span>
                @endif
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg> Secure licensing</span>
            </div>
        </div>

        <div class="viu-footer__bottom">
            <p class="viu-footer__copy">© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            <div class="viu-footer__legal">
                @foreach ($copyrightNavLinks as $link)
                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
