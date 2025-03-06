@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Available Puzzles') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($puzzles as $puzzle)
                            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                                <h3 class="text-xl font-semibold mb-2">{{ $puzzle->title }}</h3>
                                <p class="text-gray-600 mb-4">{{ Str::limit($puzzle->description, 100) }}</p>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-500">
                                        <span class="font-medium">{{ $puzzle->grid_size }}x{{ $puzzle->grid_size }}</span> grid
                                        @if($puzzle->time_limit)
                                            • {{ $puzzle->time_limit }} minutes
                                        @endif
                                    </div>
                                    <a href="{{ route('puzzles.play', $puzzle) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Play Now
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <h3 class="text-lg font-medium text-gray-900">No puzzles available</h3>
                                <p class="mt-2 text-sm text-gray-500">Check back later for new puzzles!</p>
                            </div>
                        @endforelse
                    </div>

                    @if($puzzles->hasPages())
                        <div class="mt-6">
                            {{ $puzzles->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection 