@php
    $fieldWrapperView = $getFieldWrapperView();
    $siteKey = $getSiteKey();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    @if (filled($siteKey))
        @once
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
        @endonce

        <div
            wire:ignore
            x-data="{
                widgetId: null,
                siteKey: @js($siteKey),
                statePath: @js($statePath),
                init() {
                    this.waitForTurnstile(() => this.mountWidget());
                },
                waitForTurnstile(callback) {
                    if (typeof turnstile !== 'undefined') {
                        callback();
                        return;
                    }

                    window.setTimeout(() => this.waitForTurnstile(callback), 50);
                },
                mountWidget() {
                    if (this.widgetId !== null) {
                        return;
                    }

                    this.widgetId = turnstile.render(this.$refs.widget, {
                        sitekey: this.siteKey,
                        callback: (token) => $wire.set(this.statePath, token),
                        'expired-callback': () => $wire.set(this.statePath, null),
                        'error-callback': () => $wire.set(this.statePath, null),
                    });
                },
                reset() {
                    if (this.widgetId !== null && typeof turnstile !== 'undefined') {
                        turnstile.reset(this.widgetId);
                    }

                    $wire.set(this.statePath, null);
                },
            }"
            x-on:turnstile-reset.window="reset()"
        >
            <div x-ref="widget"></div>
        </div>
    @endif
</x-dynamic-component>
