<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($clue) ? 'Edit Clue' : 'Create New Clue' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ isset($clue) ? route('admin.clues.update', $clue) : route('admin.clues.store') }}">
                        @csrf
                        @if(isset($clue))
                            @method('PUT')
                        @endif

                        <div class="space-y-6">
                            <!-- Puzzle -->
                            <div>
                                <x-input-label for="puzzle_id" :value="__('Puzzle')" />
                                <select id="puzzle_id"
                                        name="puzzle_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select a puzzle</option>
                                    @foreach($puzzles as $id => $title)
                                        <option value="{{ $id }}" {{ old('puzzle_id', $clue->puzzle_id ?? '') == $id ? 'selected' : '' }}>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('puzzle_id')" class="mt-2" />
                            </div>

                            <!-- Question -->
                            <div>
                                <x-input-label for="question" :value="__('Question')" />
                                <x-text-input id="question"
                                             name="question"
                                             type="text"
                                             class="mt-1 block w-full"
                                             :value="old('question', $clue->question ?? '')"
                                             required />
                                <x-input-error :messages="$errors->get('question')" class="mt-2" />
                            </div>

                            <!-- Answer -->
                            <div>
                                <x-input-label for="answer" :value="__('Answer')" />
                                <x-text-input id="answer"
                                             name="answer"
                                             type="text"
                                             class="mt-1 block w-full uppercase"
                                             :value="old('answer', $clue->answer ?? '')"
                                             required />
                                <x-input-error :messages="$errors->get('answer')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Direction -->
                                <div>
                                    <x-input-label for="direction" :value="__('Direction')" />
                                    <select id="direction"
                                            name="direction"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="across" {{ old('direction', $clue->direction ?? '') == 'across' ? 'selected' : '' }}>
                                            Across
                                        </option>
                                        <option value="down" {{ old('direction', $clue->direction ?? '') == 'down' ? 'selected' : '' }}>
                                            Down
                                        </option>
                                    </select>
                                    <x-input-error :messages="$errors->get('direction')" class="mt-2" />
                                </div>

                                <!-- Number -->
                                <div>
                                    <x-input-label for="number" :value="__('Clue Number')" />
                                    <x-text-input id="number"
                                                 name="number"
                                                 type="number"
                                                 class="mt-1 block w-full"
                                                 :value="old('number', $clue->number ?? '')"
                                                 required
                                                 min="1" />
                                    <x-input-error :messages="$errors->get('number')" class="mt-2" />
                                </div>

                                <!-- Position -->
                                <div>
                                    <x-input-label :value="__('Starting Position')" />
                                    <div class="grid grid-cols-2 gap-2 mt-1">
                                        <div>
                                            <x-text-input id="start_position_x"
                                                         name="start_position_x"
                                                         type="number"
                                                         class="block w-full"
                                                         :value="old('start_position_x', $clue->start_position_x ?? '')"
                                                         required
                                                         min="0"
                                                         placeholder="X" />
                                        </div>
                                        <div>
                                            <x-text-input id="start_position_y"
                                                         name="start_position_y"
                                                         type="number"
                                                         class="block w-full"
                                                         :value="old('start_position_y', $clue->start_position_y ?? '')"
                                                         required
                                                         min="0"
                                                         placeholder="Y" />
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('start_position_x')" class="mt-2" />
                                    <x-input-error :messages="$errors->get('start_position_y')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <x-secondary-button type="button" 
                                                  onclick="window.location.href='{{ route('admin.clues.index') }}'"
                                                  class="mr-3">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-primary-button>
                                    {{ isset($clue) ? __('Update Clue') : __('Create Clue') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 