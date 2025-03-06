@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Congratulations!</h1>
                    <p class="text-lg text-gray-600">Thank you for completing the puzzle "{{ $puzzle->title }}"</p>
                </div>

                <div class="max-w-2xl mx-auto">
                    <!-- Score Card -->
                    <div class="bg-blue-50 rounded-lg p-6 mb-8">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-blue-600">{{ $userScore->score }}%</div>
                                <div class="text-sm text-gray-600 mt-1">Your Score</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-blue-600">
                                    {{ floor($userScore->completion_time / 60) }}:{{ str_pad($userScore->completion_time % 60, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Completion Time</div>
                            </div>
                        </div>
                    </div>

                    <!-- Winner Declaration Countdown -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Winner Declaration</h2>
                        <p class="text-gray-600 mb-4">
                            The winner will be declared in:
                        </p>
                        <div class="text-center" id="countdown" data-remaining="{{ $timeRemaining }}">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <div class="text-2xl font-bold text-gray-900" id="hours">00</div>
                                    <div class="text-sm text-gray-600">Hours</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-gray-900" id="minutes">00</div>
                                    <div class="text-sm text-gray-600">Minutes</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-gray-900" id="seconds">00</div>
                                    <div class="text-sm text-gray-600">Seconds</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-center space-x-4">
                        @if(auth()->user()->is_admin)
                        <a href="{{ route('leaderboard.puzzle', $puzzle) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            View Leaderboard
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countdownElement = document.getElementById('countdown');
    let timeRemaining = parseInt(countdownElement.dataset.remaining);

    function updateCountdown() {
        if (timeRemaining <= 0) {
            document.getElementById('hours').textContent = '00';
            document.getElementById('minutes').textContent = '00';
            document.getElementById('seconds').textContent = '00';
            return;
        }

        const hours = Math.floor(timeRemaining / 3600);
        const minutes = Math.floor((timeRemaining % 3600) / 60);
        const seconds = timeRemaining % 60;

        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

        timeRemaining--;
    }

    // Update countdown every second
    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
@endpush
@endsection 