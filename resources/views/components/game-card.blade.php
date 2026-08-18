@php
use Illuminate\Support\Facades\Storage;

$artworkPath = config('game_artwork.' . $game->slug);
$hasArtwork = $artworkPath && Storage::disk('public')->exists($artworkPath);
$imageUrl = $hasArtwork ? '/storage/'.$artworkPath : null;
$isPlayable = (bool) $game->is_active;
@endphp

@if($isPlayable)
    <a href="/games/{{ $game->slug }}" draggable="false" class="relative flex flex-col items-start w-auto gap-3 p-1 px-1.5 leading-5 game-card-group group" data-game-slug="{{ $game->slug }}">
        <div class="relative game-card-image-wrapper">
            @if($hasArtwork)
                <img 
                    src="{{ $imageUrl }}" 
                    loading="{{ $imageIndex < 7 ? 'eager' : 'lazy' }}" 
                    alt="{{ $game->name }}" 
                    class="object-cover !h-full game-card-image group-hover:rounded-md"
                >
            @else
                <div class="game-card-placeholder">
                    <span>{{ $game->name }}</span>
                </div>
            @endif
            <div class="flex absolute inset-0 justify-center items-center h-full bg-card"></div>
        </div>
    </a>
@else
    <div class="relative flex flex-col items-start w-auto gap-3 p-1 px-1.5 leading-5 opacity-75 cursor-not-allowed" data-game-slug="{{ $game->slug }}" style="pointer-events: none;">
        <div class="relative game-card-image-wrapper" style="border: 2px solid rgba(45, 44, 49, 0.5);">
            @if($hasArtwork)
                <img 
                    src="{{ $imageUrl }}" 
                    loading="{{ $imageIndex < 7 ? 'eager' : 'lazy' }}" 
                    alt="{{ $game->name }}" 
                    class="object-cover !h-full game-card-image"
                >
            @else
                <div class="game-card-placeholder">
                    <span>{{ $game->name }}</span>
                </div>
            @endif
            <div class="flex absolute inset-0 justify-center items-center h-full bg-card"></div>
            <!-- Coming Soon Overlay -->
            <div class="absolute inset-0 flex items-center justify-center bg-black/70 rounded-lg z-10">
                <div class="text-center px-4">
                    <span class="text-white font-bold text-base sm:text-lg md:text-xl">{{ __('messages.coming_soon') ?? 'Coming Soon' }}</span>
                </div>
            </div>
        </div>
    </div>
@endif
