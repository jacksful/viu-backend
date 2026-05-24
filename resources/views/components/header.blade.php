<header class="site-header">
    <div class="container">
        <div class="site-header__bar d-flex align-items-center py-2 py-md-0" style="min-height: var(--site-header-height, 72px);">
            {{-- Left: logo --}}
            <div class="flex-grow-1 d-flex justify-content-start align-items-center" style="flex-basis: 0; min-width: 0;">
                <a href="{{ url('/') }}" class="site-header__brand text-decoration-none d-inline-flex align-items-center">
                    <img src="{{ asset('image/viu-header-logo.png') }}" alt="{{ config('app.name', 'Viu') }}" class="site-header__logo-img" width="200" height="40" loading="eager">
                </a>
            </div>

            {{-- Center: navigation --}}
            <nav class="site-header__nav d-none d-lg-flex align-items-center justify-content-center gap-4 gap-xl-5 flex-shrink-0 mx-2" aria-label="Primary">
                <a href="#solutions" class="site-header__link text-decoration-none">The Advantage</a>
                <a href="#territory" class="site-header__link text-decoration-none">Territory</a>
                <a href="#exclusivity" class="site-header__link text-decoration-none">Exclusivity</a>
                <a href="#pricing" class="site-header__link text-decoration-none">Pricing</a>
            </nav>

            {{-- Right: CTA + mobile toggle --}}
            <div class="flex-grow-1 d-flex justify-content-end align-items-center gap-2 gap-md-3" style="flex-basis: 0; min-width: 0;">
                <a href="#hero-zip" class="btn site-header__cta d-none d-lg-inline-flex align-items-center text-decoration-none">
                    Check territory
                </a>
                <button class="d-lg-none btn btn-link text-white p-1 border-0" id="mobile-menu-button" type="button" aria-label="Open menu" aria-expanded="false">
                    <i class="bi bi-list fs-2"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="d-none px-3 pb-3 site-header__mobile-panel" id="mobile-menu">
        <div class="rounded-0 p-3 mt-0 border-top border-secondary border-opacity-25" style="background: rgba(0,0,0,0.65);">
            <div class="d-flex flex-column gap-3">
                <a href="#solutions" class="site-header__link text-decoration-none d-block">The Advantage</a>
                <a href="#territory" class="site-header__link text-decoration-none d-block">Territory</a>
                <a href="#exclusivity" class="site-header__link text-decoration-none d-block">Exclusivity</a>
                <a href="#pricing" class="site-header__link text-decoration-none d-block">Pricing</a>
                <a href="#hero-zip" class="btn site-header__cta w-100 mt-1 text-center text-decoration-none">Check territory</a>
            </div>
        </div>
    </div>
</header>

@include('components.contact-modal')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', function() {
                menu.classList.toggle('d-none');
                const open = !menu.classList.contains('d-none');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }
    });
</script>
@endpush
