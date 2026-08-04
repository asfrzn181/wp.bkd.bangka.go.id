document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const carousel = document.getElementById('bkpsdmdHeroCarousel');
    if (!carousel) return;

    const slides = carousel.querySelectorAll('.parallax-slide');
    const indicators = carousel.querySelectorAll('.indicator');
    const prevBtn = carousel.querySelector('.prev-btn');
    const nextBtn = carousel.querySelector('.next-btn');
    const currentCounter = carousel.querySelector('.current-slide');
    const totalCounter = carousel.querySelector('.total-slides');

    let currentIndex = 0;
    const totalSlides = slides.length;
    let autoPlayTimer = null;
    const autoPlayDuration = 6000; // 6 seconds

    if (totalCounter) {
        totalCounter.textContent = String(totalSlides).padStart(2, '0');
    }

    // ── Mouse & Gyro 3D Parallax Engine ──────────────────────────────────────
    let mouseX = 0, mouseY = 0;
    let targetX = 0, targetY = 0;
    let isHovered = false;

    carousel.addEventListener('mousemove', function(e) {
        const rect = carousel.getBoundingClientRect();
        mouseX = (e.clientX - rect.left - rect.width / 2) / (rect.width / 2);
        mouseY = (e.clientY - rect.top - rect.height / 2) / (rect.height / 2);
        isHovered = true;
    });

    carousel.addEventListener('mouseleave', function() {
        mouseX = 0;
        mouseY = 0;
        isHovered = false;
    });

    function updateParallax() {
        // Smooth inertia interpolation
        targetX += (mouseX - targetX) * 0.06;
        targetY += (mouseY - targetY) * 0.06;

        const activeSlide = slides[currentIndex];
        if (activeSlide) {
            const parallaxElements = activeSlide.querySelectorAll('[data-depth]');
            parallaxElements.forEach(function(el) {
                const depth = parseFloat(el.getAttribute('data-depth')) || 0.2;
                const moveX = targetX * depth * 45; // Max 45px shift
                const moveY = targetY * depth * 30; // Max 30px shift
                const rotateX = -targetY * depth * 6; // 3D tilt
                const rotateY = targetX * depth * 6;

                el.style.transform = 'translate3d(' + moveX + 'px, ' + moveY + 'px, 0) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)';
            });
        }
        requestAnimationFrame(updateParallax);
    }
    requestAnimationFrame(updateParallax);

    // ── Scroll Parallax Effect ───────────────────────────────────────────────
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        if (scrolled <= carousel.offsetHeight) {
            const bgLayer = slides[currentIndex] ? slides[currentIndex].querySelector('.parallax-bg-layer') : null;
            if (bgLayer) {
                bgLayer.style.transform = 'translate3d(0, ' + (scrolled * 0.25) + 'px, 0)';
            }
        }
    });

    // ── Carousel Controls & Auto-Play ────────────────────────────────────────

    function goToSlide(index) {
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;

        slides.forEach(function(slide, i) {
            if (i === index) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });

        indicators.forEach(function(ind, i) {
            if (i === index) {
                ind.classList.add('active');
            } else {
                ind.classList.remove('active');
            }
        });

        if (currentCounter) {
            currentCounter.textContent = String(index + 1).padStart(2, '0');
        }

        currentIndex = index;
        resetAutoPlay();
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    function startAutoPlay() {
        stopAutoPlay();
        autoPlayTimer = setInterval(function() {
            if (!isHovered) {
                nextSlide();
            }
        }, autoPlayDuration);
    }

    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }

    function resetAutoPlay() {
        startAutoPlay();
    }

    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);

    indicators.forEach(function(ind) {
        ind.addEventListener('click', function() {
            const target = parseInt(this.getAttribute('data-goto'));
            goToSlide(target);
        });
    });

    // Touch Swap Support
    let touchStartX = 0;
    carousel.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    carousel.addEventListener('touchend', function(e) {
        const touchEndX = e.changedTouches[0].screenX;
        if (touchStartX - touchEndX > 50) {
            nextSlide();
        } else if (touchEndX - touchStartX > 50) {
            prevSlide();
        }
    }, { passive: true });

    // Start initial loop
    startAutoPlay();
});
