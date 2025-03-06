@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Puzzle</h1>
        <a href="{{ route('admin.puzzles.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">
            Back to Puzzles
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.puzzles.update', $puzzle) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $puzzle->title) }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $puzzle->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="grid_size" class="block text-sm font-medium text-gray-700">Grid Size</label>
                        <select name="grid_size" id="grid_size" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @for($i = 5; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ old('grid_size', $puzzle->grid_size) == $i ? 'selected' : '' }}>{{ $i }}x{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="time_limit" class="block text-sm font-medium text-gray-700">Time Limit (minutes)</label>
                        <input type="number" name="time_limit" id="time_limit" value="{{ old('time_limit', $puzzle->time_limit) }}" min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Clues</label>
                    <div id="clues-container" class="space-y-4">
                        @foreach($puzzle->clues as $index => $clue)
                            <div class="clue-entry grid grid-cols-2 gap-4 p-4 border rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Question</label>
                                    <input type="text" name="clues[{{ $index }}][question]" value="{{ old('clues.'.$index.'.question', $clue->question) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Answer</label>
                                    <input type="text" name="clues[{{ $index }}][answer]" value="{{ old('clues.'.$index.'.answer', $clue->answer) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                @if(!$loop->first)
                                    <button type="button" onclick="removeClue(this)" class="text-red-600 hover:text-red-800 col-span-2 text-right">
                                        Remove Clue
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addClue()" class="mt-2 text-blue-600 hover:text-blue-800">
                        + Add Another Clue
                    </button>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $puzzle->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                        Update Puzzle
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let clueCount = {{ count($puzzle->clues) }};

function addClue() {
    const container = document.getElementById('clues-container');
    const newClue = document.createElement('div');
    newClue.className = 'clue-entry grid grid-cols-2 gap-4 p-4 border rounded-lg';
    newClue.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700">Question</label>
            <input type="text" name="clues[${clueCount}][question]" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Answer</label>
            <input type="text" name="clues[${clueCount}][answer]" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <button type="button" onclick="removeClue(this)" class="text-red-600 hover:text-red-800 col-span-2 text-right">
            Remove Clue
        </button>
    `;
    container.appendChild(newClue);
    clueCount++;
}

function removeClue(button) {
    button.parentElement.remove();
}
</script>
@endsection 