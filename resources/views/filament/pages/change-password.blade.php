<x-filament-panels::page>
    <div class="max-w-md mx-auto" style="max-width: 500px;">
        <form wire:submit="save">
            {{ $this->form }}

            <div class="flex justify-end gap-x-3 mt-6" style="margin-top: 20px;">
                <x-filament::button type="button" color="gray" wire:click="$dispatch('close-modal')" onclick="window.history.back()">
                    Cancel
                </x-filament::button>
                <x-filament::button type="submit">
                    Change Password
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>

