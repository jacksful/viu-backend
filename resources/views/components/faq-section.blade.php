@props(['section' => null])

@php
    $q = $section ?? \App\Models\CmsQaSection::singleton();
    $openIdx = $q->defaultOpenFaqIndex();
@endphp

<section id="faq" class="py-5" style="background-color: #F9FAFB;">
    <div class="container py-lg-2">
        <div class="row g-5 align-items-start">
            <div class="col-lg-5">
                <p class="faq-section__badge d-inline-block text-viu-navy text-uppercase small fw-bold mb-3 px-3 py-2 rounded-pill">
                    {{ $q->badge_text }}
                </p>
                <h2 class="fw-bold text-viu-navy mb-3 text-uppercase fs-2">{{ $q->heading }}</h2>
                <p class="text-secondary mb-4 mb-lg-5" style="max-width: 28rem;">
                    {{ $q->intro }}
                </p>
                <div class="d-flex gap-3 align-items-center">
                    <div class="faq-section__email-icon flex-shrink-0 d-flex align-items-center justify-content-center rounded-3">
                        <img src="{{ $q->support_icon_url }}" alt="" width="22" height="22" loading="lazy">
                    </div>
                    <div>
                        <p class="text-secondary text-uppercase small mb-1 fw-semibold" style="letter-spacing: 0.08em;">{{ $q->support_label }}</p>
                        <a href="mailto:{{ $q->support_email }}" class="fw-bold text-viu-navy text-decoration-none">{{ $q->support_email }}</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="faq-section__panel bg-white p-4 p-lg-5">
                    <div class="d-flex flex-column">
                        @foreach ($q->faqList() as $idx => $item)
                            <x-faq-item
                                :open="$idx === $openIdx"
                                number=""
                                :question="$item['question'] ?? ''"
                                :answer="$item['answer'] ?? ''"
                            />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
