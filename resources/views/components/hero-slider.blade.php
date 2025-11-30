<div class="hero-slider relative h-[500px] md:h-[600px] overflow-hidden">
    <div class="slider-container h-full">
        @foreach($slides as $index => $slide)
        <div class="slide absolute inset-0 transition-opacity duration-1000 {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}" data-slide="{{ $index }}">
            <!-- Content Overlay -->
            <div class="relative h-full flex items-center bg-black bg-opacity-30">
                <div class="container mx-auto px-4">
                    <div class="max-w-2xl">
                        <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 animate-fade-in">
                            {{ $slide['title'] }}
                        </h1>
                        <p class="text-xl md:text-2xl text-gray-200 mb-8 animate-fade-in-delay">
                            {{ $slide['subtitle'] }}
                        </p>
                        <a href="{{ $slide['link'] }}" class="inline-block bg-red-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-red-700 transition transform hover:scale-105 animate-fade-in-delay-2">
                            {{ $slide['button_text'] }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Navigation Dots -->
    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-2">
        @foreach($slides as $index => $slide)
        <button class="slider-dot w-3 h-3 rounded-full {{ $index === 0 ? 'bg-red-600' : 'bg-white opacity-50' }}" data-slide="{{ $index }}"></button>
        @endforeach
    </div>
    
    <!-- Navigation Arrows -->
    <button class="slider-prev absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-3 rounded-full transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    <button class="slider-next absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-3 rounded-full transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    let currentSlide = 0;
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('opacity-100'));
        slides.forEach(slide => slide.classList.add('opacity-0'));
        dots.forEach(dot => {
            dot.classList.remove('bg-red-600');
            dot.classList.add('bg-white', 'opacity-50');
        });
        
        slides[index].classList.remove('opacity-0');
        slides[index].classList.add('opacity-100');
        dots[index].classList.remove('bg-white', 'opacity-50');
        dots[index].classList.add('bg-red-600');
        
        currentSlide = index;
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }
    
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }
    
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });
    
    // Auto-play
    setInterval(nextSlide, 5000);
});
</script>
@endpush

