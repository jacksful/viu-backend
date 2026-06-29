@props(['section' => null])

@php
    $q = $section ?? \App\Models\CmsQaSection::singleton();
    $openIdx = $q->defaultOpenFaqIndex();
@endphp

<section class="alignfull bg-surface-alt-2" id="faq">
    <div class="container section--lg">
        <div class="viu-faq-grid">
            <div class="viu-faq-aside viu-reveal viu-reveal--left">
                <div class="viu-intro">
                    <span class="viu-badge viu-badge--orange">{{ $q->badge_text }}</span>
                    <h2 class="viu-h2">{{ $q->heading }}</h2>
                    <p class="viu-intro__text">{{ $q->intro }}</p>
                </div>
                <div class="viu-faq-support">
                    <span class="viu-icon-box viu-icon-box--subtle viu-icon-box--md">
                        <svg class="viu-icon viu-icon--md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                    </span>
                    <div>
                        <span class="viu-faq-support__label">{{ $q->support_label }}</span><br>
                        <a class="viu-faq-support__email" href="mailto:{{ $q->support_email }}">{{ $q->support_email }}</a>
                    </div>
                </div>
            </div>

            <div class="viu-faq__list viu-reveal viu-reveal--right" style="--viu-reveal-delay:150ms" data-viu-faq>
                @foreach ($q->faqList() as $idx => $item)
                    <x-faq-item
                        :open="$idx === $openIdx"
                        :question="$item['question'] ?? ''"
                        :answer="$item['answer'] ?? ''"
                    />
                @endforeach
            </div>
        </div>
    </div>
</section>
