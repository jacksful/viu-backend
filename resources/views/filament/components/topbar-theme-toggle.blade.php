@php
    use Filament\Support\Icons\Heroicon;
@endphp

<div class="fi-topbar-theme-toggle">
    <x-filament::icon-button
        color="gray"
        :icon="Heroicon::OutlinedMoon"
        icon-size="lg"
        label="{{ __('filament-panels::layout.actions.theme_switcher.dark.label') }}"
        x-cloak
        x-show="$store.theme === 'light'"
        x-on:click="$dispatch('theme-changed', 'dark')"
        class="fi-topbar-theme-toggle-btn"
    />

    <x-filament::icon-button
        color="gray"
        :icon="Heroicon::OutlinedSun"
        icon-size="lg"
        label="{{ __('filament-panels::layout.actions.theme_switcher.light.label') }}"
        x-cloak
        x-show="$store.theme === 'dark'"
        x-on:click="$dispatch('theme-changed', 'light')"
        class="fi-topbar-theme-toggle-btn"
    />
</div>
