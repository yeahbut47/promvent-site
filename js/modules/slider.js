// ========== СЛАЙДЕР ГЛАВНЫЙ ==========

export function initHeroSlider() {
    const track = document.getElementById('sliderTrack');
    const slides = document.querySelectorAll('.hero__slide');
    const dots = document.querySelectorAll('.hero__dot');
    const prevBtn = document.getElementById('heroPrev');
    const nextBtn = document.getElementById('heroNext');
    
    if (!track || !slides.length) return;
    
    let currentIndex = 0;
    let intervalId = null;
    let isTransitioning = false;
    
    const TOTAL_SLIDES = slides.length;
    const AUTO_INTERVAL = 6000;
    const TRANSITION_DURATION = 800;
    
    function updateSliderPosition(index, instant = false) {
        const offset = -index * 100;
        
        if (instant) {
            track.style.transition = 'none';
            track.style.transform = `translateX(${offset}%)`;
            track.offsetHeight;
            track.style.transition = `transform ${TRANSITION_DURATION}ms cubic-bezier(0.4, 0, 0.2, 1)`;
        } else {
            track.style.transform = `translateX(${offset}%)`;
        }
    }
    
    function updateDots(index) {
        dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('active-dot');
            } else {
                dot.classList.remove('active-dot');
            }
        });
    }
    
    function goToSlide(index, instant = false) {
        if (isTransitioning) return;
        if (index < 0) index = TOTAL_SLIDES - 1;
        if (index >= TOTAL_SLIDES) index = 0;
        if (currentIndex === index && !instant) return;
        
        isTransitioning = true;
        
        updateSliderPosition(index, instant);
        updateDots(index);
        currentIndex = index;
        
        setTimeout(() => {
            isTransitioning = false;
        }, TRANSITION_DURATION);
    }
    
    function nextSlide() {
        if (!isTransitioning) goToSlide(currentIndex + 1);
    }
    
    function prevSlide() {
        if (!isTransitioning) goToSlide(currentIndex - 1);
    }
    
    function startAutoPlay() {
        if (intervalId) clearInterval(intervalId);
        intervalId = setInterval(() => {
            if (!isTransitioning) nextSlide();
        }, AUTO_INTERVAL);
    }
    
    function stopAutoPlay() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopAutoPlay();
            startAutoPlay();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopAutoPlay();
            startAutoPlay();
        });
    }
    
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            if (!isTransitioning && currentIndex !== idx) {
                goToSlide(idx);
                stopAutoPlay();
                startAutoPlay();
            }
        });
    });
    
    updateSliderPosition(0, true);
    updateDots(0);
    currentIndex = 0;
    startAutoPlay();
    
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopAutoPlay();
        else startAutoPlay();
    });
}