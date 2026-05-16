// ========== СЛАЙДЕР ПРОЕКТОВ ==========

export function initProjectsSlider() {
    const track = document.getElementById('projectsTrack');
    const slides = document.querySelectorAll('.projects__slide');
    const prevBtn = document.getElementById('projectsPrev');
    const nextBtn = document.getElementById('projectsNext');
    const dotsContainer = document.getElementById('projectsDots');
    
    if (!track || !slides.length) return;
    
    let currentIndex = 0;
    let intervalId = null;
    let isTransitioning = false;
    const TOTAL_SLIDES = slides.length;
    const AUTO_INTERVAL = 5000;
    const TRANSITION_DURATION = 500;
    
    function createDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        for (let i = 0; i < TOTAL_SLIDES; i++) {
            const dot = document.createElement('span');
            dot.classList.add('projects__dot');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(i));
            dotsContainer.appendChild(dot);
        }
    }
    
    function updateDots() {
        const dots = document.querySelectorAll('.projects__dot');
        dots.forEach((dot, i) => {
            if (i === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    
    function goToSlide(index, instant = false) {
        if (isTransitioning) return;
        if (index < 0) index = TOTAL_SLIDES - 1;
        if (index >= TOTAL_SLIDES) index = 0;
        if (currentIndex === index) return;
        
        isTransitioning = true;
        const offset = -index * 100;
        
        if (instant) {
            track.style.transition = 'none';
            track.style.transform = `translateX(${offset}%)`;
            track.offsetHeight;
            track.style.transition = `transform ${TRANSITION_DURATION}ms ease`;
        } else {
            track.style.transform = `translateX(${offset}%)`;
        }
        
        currentIndex = index;
        updateDots();
        
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
    
    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); stopAutoPlay(); startAutoPlay(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); stopAutoPlay(); startAutoPlay(); });
    
    createDots();
    goToSlide(0, true);
    startAutoPlay();
    
    const slider = document.querySelector('.projects__slider');
    if (slider) {
        slider.addEventListener('mouseenter', stopAutoPlay);
        slider.addEventListener('mouseleave', startAutoPlay);
    }
}