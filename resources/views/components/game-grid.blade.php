@php
use Illuminate\Support\Facades\Storage;
@endphp

<section class="relative w-full" style="padding-top: 2rem; padding-bottom: 10rem;">
    <!-- Background Image and Overlay - Full Width -->
    <div class="absolute inset-0 z-0 pointer-events-none" style="left: 0; right: 0; width: 100vw; margin-left: calc(50% - 50vw);">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ Storage::url('home_page/degaultbanner.webp') }}');"></div>
        <div class="absolute inset-0" style="background-color: rgba(15, 17, 27, 1);"></div>
    </div>
    
    <!-- Content -->
    <div class="relative z-10 flex flex-col items-center px-4 mx-auto w-full max-w-7xl sm:px-6 xl:px-8">
        <h2 class="text-4xl font-bold text-white font-display mb-4">{{ __('messages.popular_games') }}</h2>
        
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 w-full mt-4">
            @foreach($games as $index => $game)
                @include('components.game-card', ['game' => $game, 'imageIndex' => $index])
            @endforeach
        </div>
    </div>
</section>

@push('styles')
<style>
.game-card-group {
    transform: translateY(0) scale(1);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.game-card-group:hover {
    transform: translateY(-8px) scale(1.05);
    z-index: 10;
}

.game-card-image-wrapper {
    width: 100%;
    aspect-ratio: 2 / 3;
    border-radius: 0.375rem;
    overflow: hidden;
    border: 2px solid transparent;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    background-color: #0f111b;
}

.game-card-group:hover .game-card-image-wrapper {
    border-color: rgba(59, 130, 246, 0.6);
    border-width: 2px;
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3), 0 8px 10px -6px rgba(59, 130, 246, 0.2);
}

.game-card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.game-card-group:hover .game-card-image {
    border-radius: 0.5rem;
    transform: scale(1.05);
}

.bg-card {
    background-color: #0f111b;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.game-card-group:hover .bg-card {
    opacity: 0.3;
}

.text-danger-foreground {
    color: #dc2626;
}

.bg-danger {
    background-color: rgba(220, 38, 38, 0.1);
}

.ring-danger-ring {
    border-color: rgba(220, 38, 38, 0.2);
}

@media (max-width: 640px) {
    .game-card-image-wrapper {
        aspect-ratio: 2 / 3;
    }
    
    .game-card-group:hover {
        transform: translateY(-4px) scale(1.03);
    }
}
</style>
@endpush


