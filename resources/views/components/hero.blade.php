@php
    $heroImageUrl = $hero->image_url ?? asset('viu/assets/images/hero-bg.jpg');
    $titleLines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $hero->title ?? ''), fn ($line) => $line !== ''));
    if ($titleLines === []) {
        $titleLines = ['Own the market', 'before they sell'];
    }
@endphp
<section class="viu-hero" id="hero">
    <div class="viu-hero__bg">
        <img class="viu-hero__bg-img" src="{{ $heroImageUrl }}" alt="" width="1024" height="1024" fetchpriority="high" data-viu-parallax />
        <div class="viu-hero__bg-gradient"></div>
    </div>

    <div class="container viu-hero__inner">
        <div class="viu-hero__content">
            <h1 class="viu-display viu-hero__title">
                @foreach ($titleLines as $line)
                    <span>{{ $line }}</span>
                @endforeach
            </h1>

            <p class="viu-hero__text">
                {{ $hero->description ?? 'Viu uses predictive modeling to place your brand in front of homeowners up to 90 days before they decide to start the selling process.' }}
            </p>

            <form class="viu-hero__form" id="hero-zip" data-viu-hero-form>
                <div class="viu-hero__row">
                    <div class="viu-hero__input-group">
                        <svg class="viu-icon viu-icon--md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
                        <label class="u-visually-hidden" for="hero-zip-input">ZIP code</label>
                        <input class="viu-hero__input" id="hero-zip-input" name="zipCode" type="text" inputmode="numeric" maxlength="5" placeholder="Enter ZIP code" data-viu-zip-input data-viu-hero-zip />
                    </div>
                    <button class="viu-btn viu-btn--primary viu-btn--md" type="submit">Secure territory</button>
                </div>

                <div class="viu-hero__trust">
                    <div class="viu-hero__trust-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
                        <span>1 agent per ZIP</span>
                    </div>
                    <div class="viu-hero__trust-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
                        <span>Exclusive access</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
