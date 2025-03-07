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

                    <!-- Current Position Banner (Hidden initially) -->
                    <div id="positionBanner" class="{{ $countdownCompleted ? '' : 'hidden' }}">
                        <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded-lg mb-8">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @php
                                        $position = $userScore->getCurrentPosition();
                                        $emoji = match(true) {
                                            $position === 1 => '🏆',
                                            $position === 2 => '🥈',
                                            $position <= 10 => '🎯',
                                            default => '💪'
                                        };
                                        
                                        $suffix = match(true) {
                                            $position % 100 >= 11 && $position % 100 <= 13 => 'th',
                                            $position % 10 === 1 => 'st',
                                            $position % 10 === 2 => 'nd',
                                            default => 'th'
                                        };
                                    @endphp
                                    <span class="text-3xl">{{ $emoji }}</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-blue-800">
                                        You are currently in {{ $position }}{{ $suffix }} place!
                                    </h3>
                                    <p class="text-sm text-blue-600">
                                        @if($position === 1)
                                            Congratulations! You're in first place!
                                        @elseif($position === 2)
                                            Great job! You're in second place!
                                        @elseif($position <= 10)
                                            You're in the top 10!
                                        @else
                                            Keep practicing to improve your ranking!
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Winner Declaration Countdown -->
                    <div id="countdownSection" class="bg-gray-50 rounded-lg p-6 mb-8" {!! $countdownCompleted ? 'style="display: none;"' : '' !!}>
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
                        <a href="{{ route('leaderboard') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            View Leaderboard
                        </a>
                        @endif
                    </div>

                    <!-- Prizes Section -->
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-6 mb-8 mt-8">
                        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Available Prizes</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- First Prize -->
                            <div class="bg-white rounded-lg p-4 shadow-md transform hover:scale-105 transition-transform duration-200">
                                <div class="text-center">
                                    <img src="{{ asset('images/Laravel-Cloud-Black.png') }}" 
                                         alt="Udemy Courses" 
                                         class="w-32 h-32 mx-auto mb-4 object-contain">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">First Prize</h3>
                                    <div class="text-gray-400 text-2xl font-bold mb-2">🏆</div>
                                    <p class="text-gray-700">Laravel Cloud</p>
                                    <p class="text-sm text-gray-500 mt-2">3 Month Subscription</p>
                                </div>
                            </div>

                            <!-- Second Prize -->
                            <div class="bg-white rounded-lg p-4 shadow-md transform hover:scale-105 transition-transform duration-200">
                                <div class="text-center">
                                    <img src="{{ asset('images/phpstorm.png') }}" 
                                         alt="PhpStorm License" 
                                         class="w-32 h-32 mx-auto mb-4 object-contain">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Second Prize</h3>
                                    <div class="text-yellow-600 text-2xl font-bold mb-2">🥈</div>
                                    <p class="text-gray-700">PhpStorm License</p>
                                    <p class="text-sm text-gray-500 mt-2">1 Year Professional License</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $puzzleId = $puzzle->id;
    $currentUserId = auth()->id();
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countdownSection = document.getElementById('countdownSection');
    const resultsSection = document.getElementById('resultsSection');
    const positionBanner = document.getElementById('positionBanner');
    let timeRemaining = parseInt(document.getElementById('countdown').dataset.remaining);

    // If countdown is already completed, fetch leaderboard immediately
    if (timeRemaining === 0) {
        fetchLeaderboard();
    }

    function updateCountdown() {
        if (timeRemaining <= 0) {
            // Hide countdown section
            countdownSection.classList.add('hidden');
            
            // Show position banner and results
            positionBanner.classList.remove('hidden');
            fetchLeaderboard();
            resultsSection.classList.remove('hidden');
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

    function fetchLeaderboard() {
        fetch(`/puzzles/${puzzleId}/leaderboard`)
            .then(response => response.json())
            .then(data => {
                const leaderboardBody = document.getElementById('leaderboardBody');
                const finalPositionText = document.getElementById('finalPositionText');
                const finalPositionMessage = document.getElementById('finalPositionMessage');
                const finalPositionEmoji = document.getElementById('finalPositionEmoji');
                
                leaderboardBody.innerHTML = '';
                
                // Find user's position and update display
                let userPosition = -1;
                data.forEach((entry, index) => {
                    if (entry.user_id === currentUserId) {
                        userPosition = index + 1;
                    }
                    
                    // Create leaderboard row
                    const row = document.createElement('tr');
                    const position = index + 1;
                    const isCurrentUser = entry.user_id === currentUserId;
                    
                    row.className = isCurrentUser ? 'bg-blue-50' : (index % 2 ? 'bg-gray-50' : 'bg-white');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${isCurrentUser ? 'text-blue-900' : 'text-gray-900'}">
                            ${position}${getPositionSuffix(position)}
                            ${position <= 3 ? getPositionEmoji(position) : ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm ${isCurrentUser ? 'text-blue-900 font-medium' : 'text-gray-500'}">
                            ${entry.user_name}
                            ${isCurrentUser ? ' (You)' : ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm ${isCurrentUser ? 'text-blue-900' : 'text-gray-500'}">
                            ${entry.score}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm ${isCurrentUser ? 'text-blue-900' : 'text-gray-500'}">
                            ${formatTime(entry.completion_time)}
                        </td>
                    `;
                    leaderboardBody.appendChild(row);
                });

                // Update final position display
                if (userPosition > 0) {
                    finalPositionText.textContent = `You finished in ${userPosition}${getPositionSuffix(userPosition)} place!`;
                    
                    if (userPosition === 1) {
                        finalPositionEmoji.textContent = '🏆';
                        finalPositionMessage.textContent = 'Congratulations! You\'ve won first place!';
                    } else if (userPosition === 2) {
                        finalPositionEmoji.textContent = '🥈';
                        finalPositionMessage.textContent = 'Amazing! You\'ve secured second place!';
                    } else if (userPosition === 3) {
                        finalPositionEmoji.textContent = '🥉';
                        finalPositionMessage.textContent = 'Excellent! You\'ve earned third place!';
                    } else if (userPosition <= 10) {
                        finalPositionEmoji.textContent = '🎯';
                        finalPositionMessage.textContent = 'Great job making it to the top 10!';
                    } else {
                        finalPositionEmoji.textContent = '💪';
                        finalPositionMessage.textContent = 'Thanks for participating!';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching leaderboard:', error);
            });
    }

    function getPositionSuffix(position) {
        if (position > 3 && position < 21) return 'th';
        switch (position % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    }

    function getPositionEmoji(position) {
        switch(position) {
            case 1: return ' 🏆';
            case 2: return ' 🥈';
            default: return '';
        }
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}:${String(remainingSeconds).padStart(2, '0')}`;
    }

    // Start the countdown
    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
@endpush

@section('title', 'Crossword Puzzle Complete')
@endsection 