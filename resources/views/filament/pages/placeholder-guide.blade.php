<x-filament::page>
    <x-filament::card>
        <h2 class="text-lg font-bold">Available Placeholders</h2>
        <table class="table-auto w-full border-collapse border border-gray-200 mt-4">
            <thead>
                <tr class="">
                    <th class="border border-gray-300 px-4 py-2">Placeholder</th>
                    <th class="border border-gray-300 px-4 py-2">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->placeholders as $key => $description)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 font-mono">{{ $key }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::card>
</x-filament::page>
