<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($puzzle) ? 'Edit Puzzle: ' . $puzzle->title : 'Create New Puzzle' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ isset($puzzle) ? route('admin.puzzles.update', $puzzle) : route('admin.puzzles.store') }}">
                        @csrf
                        @if(isset($puzzle))
                            @method('PUT')
                        @endif

                        <div class="space-y-6">
                            <!-- Title -->
                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" 
                                             name="title" 
                                             type="text" 
                                             class="mt-1 block w-full" 
                                             :value="old('title', $puzzle->title ?? '')" 
                                             required 
                                             autofocus />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <!-- Description -->
                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description"
                                          name="description"
                                          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                          rows="3">{{ old('description', $puzzle->description ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Grid Size -->
                                <div>
                                    <x-input-label for="grid_size" :value="__('Grid Size')" />
                                    <select id="grid_size"
                                            name="grid_size"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        @foreach(range(5, 20) as $size)
                                            <option value="{{ $size }}" 
                                                    {{ old('grid_size', $puzzle->grid_size ?? 15) == $size ? 'selected' : '' }}>
                                                {{ $size }}x{{ $size }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('grid_size')" class="mt-2" />
                                </div>

                                <!-- Time Limit -->
                                <div>
                                    <x-input-label for="time_limit" :value="__('Time Limit (minutes)')" />
                                    <x-text-input id="time_limit"
                                                 name="time_limit"
                                                 type="number"
                                                 class="mt-1 block w-full"
                                                 :value="old('time_limit', $puzzle->time_limit ?? '')"
                                                 min="1" />
                                    <x-input-error :messages="$errors->get('time_limit')" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div>
                                    <x-input-label for="is_active" :value="__('Status')" />
                                    <select id="is_active"
                                            name="is_active"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="1" {{ old('is_active', $puzzle->is_active ?? true) ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="0" {{ old('is_active', $puzzle->is_active ?? true) ? '' : 'selected' }}>
                                            Inactive
                                        </option>
                                    </select>
                                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <x-secondary-button type="button" 
                                                  onclick="window.location.href='{{ route('admin.puzzles.index') }}'"
                                                  class="mr-3">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-primary-button>
                                    {{ isset($puzzle) ? __('Update Puzzle') : __('Create Puzzle') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>