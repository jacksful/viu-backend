@props(['number' => '', 'question', 'answer', 'open' => false])

<div class="viu-faq__item @if($open) is-open @endif">
    <button class="viu-faq__trigger" type="button" aria-expanded="{{ $open ? 'true' : 'false' }}">
        <span class="viu-faq__q">{{ $question }}</span>
        <span class="viu-faq__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </span>
    </button>
    <div class="viu-faq__answer">
        <div class="viu-faq__answer-inner">
            <p>{{ $answer }}</p>
        </div>
    </div>
</div>
