@props(['zipcodes', 'section' => null])

@php
    $p = $section ?? \App\Models\CmsPricingSection::singleton();
@endphp

<section id="pricing" class="bg-white py-5">
    <div class="container">
        <div class="row g-4 g-lg-0 align-items-stretch">
            <div class="col-lg-6">
                <div class="pricing-visual position-relative overflow-hidden shadow-sm h-100" style="min-height: min(52vh, 440px);">
                    <img
                        src="{{ $p->left_image_url }}"
                        alt=""
                        class="position-absolute top-0 start-0 w-100 h-100"
                        style="object-fit: cover;"
                        loading="lazy"
                        width="800"
                        height="900"
                    >
                    <div class="position-absolute bottom-0 start-0 end-0 p-3 p-md-4">
                        <div class="pricing-overlay-card bg-white rounded-3 shadow-sm p-3 p-md-4 text-center mx-auto" style="max-width: 320px;">
                            <p class="text-uppercase small text-secondary mb-1 mb-md-2" style="letter-spacing: 0.14em;">{{ $p->card_label_starting }}</p>
                            <p class="mb-1 mb-md-2">
                                <span class="display-6 fw-bold text-viu-navy lh-1">{{ $p->card_price_display }}</span><span class="text-secondary fw-semibold fs-5">{{ $p->card_price_period }}</span>
                            </p>
                            <p class="text-uppercase fw-bold text-viu-navy small mb-2 mb-md-3" style="letter-spacing: 0.08em;">{{ $p->card_per_label }}</p>
                            <p class="small text-secondary fst-italic mb-0">{{ $p->card_footer_note }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex">
                <div class="pricing-copy ps-lg-4 ps-xl-5 py-lg-2 d-flex flex-column justify-content-center w-100">
                    <span class="pricing-badge d-inline-block align-self-start text-uppercase fw-bold small mb-3 px-3 py-2 rounded-pill">{{ $p->badge_text }}</span>
                    <h2 class="fw-bold text-viu-navy text-uppercase mb-3" style="letter-spacing: 0.04em; font-size: clamp(2rem, 4vw, 2.75rem);">{{ $p->heading }}</h2>
                    <p class="text-secondary mb-4">
                        {{ $p->intro }}
                    </p>
                    <ul class="list-unstyled mb-4 mb-lg-5">
                        @foreach ($p->featureLines() as $line)
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <span class="pricing-check flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-circle bg-viu-navy text-white" aria-hidden="true">
                                    <i class="bi bi-check-lg"></i>
                                </span>
                                <span class="text-secondary">{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $p->cta_href }}" class="btn btn-pricing-cta text-uppercase fw-bold w-100 py-3">
                        {{ $p->cta_label }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="pricingLeadModal" tabindex="-1" aria-labelledby="pricingLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 fw-bold text-viu-navy" id="pricingLeadModalLabel">Territory &amp; waitlist</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-secondary small mb-4">
                    Select the ZIP codes you want to cover. Pricing updates as you add or remove territories—then submit your details to join the waitlist.
                </p>

                <form method="POST" action="{{ route('leads.store') }}" id="leadForm">
                    @csrf

                    @if (session('success'))
                        <div class="alert alert-success mb-4">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div id="selected-zipcodes-tags" class="d-flex flex-wrap gap-2 mb-3" style="display: none;"></div>

                    <div class="rounded-4 border p-3 p-md-4 mb-0" style="border-color: #e8e8e8 !important;">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="rounded-4 p-3 p-md-4 h-100 bg-viu-gray">
                                    <h3 class="h6 fw-bold text-viu-navy mb-3">Selected coverage</h3>
                                    <div id="selected-zipcodes">
                                        <p class="text-muted small mb-0">Use the search in the hero to add ZIP codes.</p>
                                    </div>
                                    <div id="contact-form" class="mt-4" style="display: none;">
                                        <h3 class="h6 fw-bold text-viu-navy mb-3">Contact information</h3>
                                        <div class="mb-3">
                                            <label for="name" class="form-label small fw-medium">Name *</label>
                                            <input type="text" id="name" name="name" required class="form-control" placeholder="Full name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label small fw-medium">Email *</label>
                                            <input type="email" id="email" name="email" required class="form-control" placeholder="you@example.com">
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label small fw-medium">Phone</label>
                                            <input type="tel" id="phone" name="phone" class="form-control" placeholder="(555) 123-4567">
                                        </div>
                                        <div class="mb-0">
                                            <label for="initial_notes" class="form-label small fw-medium">Notes (optional)</label>
                                            <textarea id="initial_notes" name="initial_notes" rows="3" class="form-control" placeholder="Tell us about your market"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h3 class="h6 fw-bold text-viu-navy mb-2">Total</h3>
                                <div id="total-price" class="display-6 fw-bold text-viu-navy mb-4">$0</div>
                                <button type="submit" id="submit-btn" class="btn btn-viu-orange w-100 py-3" disabled>
                                    Join the waiting list
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .pricing-badge {
        color: var(--viu-navy);
        background-color: #ffe8e0;
        letter-spacing: 0.12em;
        font-size: 0.65rem;
    }
    .pricing-check {
        width: 22px;
        height: 22px;
        font-size: 0.75rem;
        line-height: 1;
        margin-top: 0.15rem;
    }
    .btn-pricing-cta {
        background-color: #f8852d;
        border: none;
        color: var(--viu-navy) !important;
        font-size: 0.8rem;
        letter-spacing: 0.07em;
        border-radius: 6px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transition: filter 0.2s ease, transform 0.15s ease;
    }
    .btn-pricing-cta:hover {
        filter: brightness(1.03);
        color: var(--viu-navy) !important;
    }
    .zip-tag {
        color: var(--viu-navy) !important;
        background-color: rgba(242, 133, 39, 0.15) !important;
        border: 1px solid rgba(242, 133, 39, 0.35);
    }
</style>
@endpush
