<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Available Puzzles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($puzzles as $puzzle)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2">{{ $puzzle->title }}</h3>
                            <p class="text-gray-600 mb-4">{{ $puzzle->description }}</p>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Grid Size:</span> {{ $puzzle->grid_size }}x{{ $puzzle->grid_size }}
                                    @if($puzzle->time_limit)
                                        <br>
                                        <span class="font-medium">Time Limit:</span> {{ $puzzle->time_limit }} minutes
                                    @endif
                                </div>
                                <a href="{{ route('puzzles.play', $puzzle) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Play Now
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $puzzles->links() }}
            </div>
        </div>
    </div>
</x-app-layout> 