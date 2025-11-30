@php
use Illuminate\Support\Facades\Storage;

// Map game slugs to image filenames that actually exist in game_cards_images folder
$slugToImageMap = [
    'mlbb' => 'ml.webp', // Will use home_games
    'battlefield' => 'btf.webp',
    'call-of-duty' => 'cod.webp',
    'cod' => 'cod.webp',
    'counter-strike-2' => 'cs2.webp',
    'cs2' => 'cs2.webp',
    'dota-2' => 'dota2.webp',
    'dota2' => 'dota2.webp',
    'apex-legends' => 'apex.webp',
    'apex' => 'apex.webp',
    'bleach-brave-souls' => 'bbs.webp',
    'bbs' => 'bbs.webp',
    'free-fire' => 'ff.webp',
    'ff' => 'ff.webp',
    'fc-26' => 'fc26.webp',
    'fc26' => 'fc26.webp',
    'call-of-duty-mobile' => 'codm.webp',
    'cod-mobile' => 'codm.webp',
    'codm' => 'codm.webp',
    'genshin-impact' => 'gi.webp',
    'fortnite' => 'fn.webp',
    'coc' => 'coc.webp',
    'clash-of-clans' => 'coc.webp',
];

// Get image filename based on game slug
$imageFilename = $slugToImageMap[$game->slug] ?? null;

// If no mapping found, try to construct from slug
if (!$imageFilename) {
    $imageFilename = str_replace(['-', '_'], '', $game->slug) . '.webp';
}

// For MLBB, use home_games folder
if ($game->slug === 'mlbb' && $imageFilename === 'ml.webp') {
    $imageUrl = asset('storage/home_games/ml.webp');
    $imageExists = Storage::disk('public')->exists('home_games/ml.webp');
} else {
    $imageUrl = asset('storage/game_cards_images/' . $imageFilename);
    $imageExists = Storage::disk('public')->exists('game_cards_images/' . $imageFilename);
}

// Fallback to a default image if file doesn't exist
if (!$imageExists) {
    // Use a placeholder or first available image
    $imageUrl = asset('storage/game_cards_images/btf.webp'); // Fallback to Battlefield
}
@endphp

@php
    $isMLBB = $game->slug === 'mlbb';
@endphp

@if($isMLBB)
    <a href="/games/{{ $game->slug }}" draggable="false" class="relative flex flex-col items-start w-auto gap-3 p-1 px-1.5 leading-5 game-card-group group" data-game-slug="{{ $game->slug }}">
        <div class="relative game-card-image-wrapper">
            <img 
                src="{{ $imageUrl }}" 
                loading="{{ $imageIndex < 7 ? 'eager' : 'lazy' }}" 
                alt="Game Art" 
                class="object-cover !h-full game-card-image group-hover:rounded-md"
                onerror="this.onerror=null; this.src='{{ asset('storage/game_cards_images/btf.webp') }}';"
            >
            <div class="flex absolute inset-0 justify-center items-center h-full bg-card"></div>
        </div>
    </a>
@else
    <div class="relative flex flex-col items-start w-auto gap-3 p-1 px-1.5 leading-5 opacity-75 cursor-not-allowed" data-game-slug="{{ $game->slug }}" style="pointer-events: none;">
        <div class="relative game-card-image-wrapper" style="border: 2px solid rgba(45, 44, 49, 0.5);">
            <img 
                src="{{ $imageUrl }}" 
                loading="{{ $imageIndex < 7 ? 'eager' : 'lazy' }}" 
                alt="Game Art" 
                class="object-cover !h-full game-card-image"
                onerror="this.onerror=null; this.src='{{ asset('storage/game_cards_images/btf.webp') }}';"
            >
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

