@props(['section' => null])

@php
    $s = $section ?? \App\Cms\Presenters\StatsBarPresenter::from([]);
    $items = method_exists($s, 'items') ? $s->items() : [];
@endphp
<section class="viu-stats alignfull bg-primary" aria-label="Key metrics">
    <div class="container">
        <div class="viu-stats__row">
            @foreach ($items as $idx => $item)
                <div class="viu-stats__cell">
                    @if ($idx > 0)
                        <div class="viu-stats__divider"></div>
                    @endif
                    <div class="viu-stats__item viu-reveal viu-reveal--up" @if($idx > 0) style="--viu-reveal-delay:{{ $idx * 200 }}ms" @endif>
                        <span class="viu-stats__value" @if(is_numeric($item['value'] ?? null)) data-viu-count="{{ $item['value'] }}" @endif>{{ $item['value'] ?? '' }}</span>
                        <span class="viu-stats__label">{{ $item['label'] ?? '' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
