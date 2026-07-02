@props(['section' => null])

@php
    $content = $section ?? \App\Cms\Presenters\LegalContentPresenter::from([]);
@endphp

<section class="alignfull bg-surface">
    <div class="container section--lg">
        <article class="viu-legal__prose">
            @if ($content->lead)
                <p class="viu-legal__lead">{{ $content->lead }}</p>
            @endif
            {!! $content->bodyHtml() !!}
        </article>
    </div>
</section>
