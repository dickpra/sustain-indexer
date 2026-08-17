<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                <x-filament::icon alias="panels::pages.upload.submit" icon="heroicon-o-cpu-chip" class="w-5 h-5 me-2" />
                Scan & Index XML
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>