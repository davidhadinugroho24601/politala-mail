
<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($groups as $group)
            <x-filament::card 
                class="{{ session('groupID') == $group['id'] ? 'bg-blue-100 border-blue-500' : '' }}">
                <a href="{{ route('set.group.id', ['groupID' => $group['id']]) }}">
                    <h3 class="text-xl font-semibold">{{ $group['name'] }}</h3>
                    <p>{{ $group['description'] ?? 'No description available' }}</p>
                </a>
            </x-filament::card>
        @endforeach
    </div>
</x-filament-panels::page>
