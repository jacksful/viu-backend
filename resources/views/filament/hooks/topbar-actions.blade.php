<div class="fi-topbar-actions">
    @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
        @include('filament.components.topbar-theme-toggle')
    @endif

    @livewire(\App\Filament\Livewire\AdminNotificationsDropdown::class)
</div>
