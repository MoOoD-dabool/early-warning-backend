<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}

        <div class="mt-6">
            {{ $this->sendAction }}
        </div>
    </form>
</x-filament-panels::page>
