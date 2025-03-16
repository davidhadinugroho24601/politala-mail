<x-filament-panels::page>
    <form wire:submit.prevent="verify" class="space-y-4">
        {{ $this->form }}
        <x-filament::button type="submit">Verify PDF</x-filament::button>
    </form>

    @if ($verificationResult)
    <div class="mt-4 p-4 border rounded bg-gray-100 dark:bg-gray-800 ">
        {{ $verificationResult }}
    </div>
@endif


    @if ($record && $record->pdf_path)
        <div class="mt-4">
            <h2 class="text-lg font-semibold">Matching PDF:</h2>
            <iframe id="googleDocFrame" src="{{ asset($record->pdf_path) }}" width="100%" height="600px" style="border: none;"></iframe>
        </div>
    @endif
</x-filament-panels::page>
