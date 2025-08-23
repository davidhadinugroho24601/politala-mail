<x-filament-panels::page>
    <div class="max-w-xl mx-auto mt-8 space-y-6">
        <x-filament::section>
            <x-slot name="header">
                <div class="flex items-center space-x-3">
                    <x-filament::avatar user="Admin" size="md" />
                    <div>
                        <h2 class="text-xl font-semibold">Database Export</h2>
                        <p class="text-sm text-gray-500">Backup your database securely.</p>
                    </div>
                </div>
            </x-slot>
            
            <div class="my-4">
                <x-filament::badge color="info" class="px-3 py-2">Backup</x-filament::badge>
            </div>
            
            <p class="text-gray-600 pb-20">Enter a filename for your database backup and export it.</p>
            
            <form wire:submit.prevent="exportDatabase" class="space-y-4">
                <x-filament::card>
                    <x-filament::grid>
                        <x-filament::input
                            wire:model.defer="backupFileName"
                            label="Backup File Name"
                            placeholder="Enter backup filename..."
                            required
                        />
                    </x-filament::grid>
                </x-filament::card>

                <div class="flex justify-center">
                    <x-filament::button type="submit" color="success" icon="heroicon-o-arrow-down" class="w-full">
                        Export Database
                    </x-filament::button>
                </div>
                
                <div wire:loading wire:target="exportDatabase" class="flex justify-center mt-4">
                    <x-filament::loading-indicator />
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>
