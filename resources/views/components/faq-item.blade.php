@props(['number' => '', 'question', 'answer', 'open' => false])

<details class="faq-item border-bottom border-secondary border-opacity-25 bg-transparent" @if ($open) open @endif>
    <summary class="d-flex align-items-center justify-content-between gap-3 py-3 list-unstyled">
        <span class="fw-bold text-viu-navy mb-0 pe-2 text-uppercase small" style="letter-spacing: 0.04em; line-height: 1.35;">
            @if ($number)
                <strong class="text-viu-orange me-2">{{ $number }}</strong>
            @endif
            {{ $question }}
        </span>
        <span class="faq-item__toggle flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-circle" aria-hidden="true">
            <img src="{{ asset('image/plus.svg') }}" alt="" width="16" height="16" class="faq-item__toggle-icon">
        </span>
    </summary>
    <div class="pb-3 text-secondary small">
        <p class="mb-0">{{ $answer }}</p>
    </div>
</details>
