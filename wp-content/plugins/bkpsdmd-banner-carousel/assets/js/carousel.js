/**
 * BKPSDMD Banner Carousel — Frontend JS
 * Vanilla JS, no dependencies
 * Supports: fade | slide | zoom + autoplay + dots + arrows + progress + touch/swipe
 */
(function () {
    'use strict';

    document.querySelectorAll('.bkbc-carousel').forEach(function (carousel) {
        // ── Config dari data attributes ───────────────────────────────────────
        const animation  = carousel.dataset.animation  || 'slide';
        const duration   = parseInt( carousel.dataset.duration, 10 ) || 600;
        const autoplay   = carousel.dataset.autoplay   === 'true';
        const interval   = parseInt( carousel.dataset.interval, 10 ) || 5000;

        // ── DOM ───────────────────────────────────────────────────────────────
        const slides    = carousel.querySelectorAll('.bkbc-slide');
        const dots      = carousel.querySelectorAll('.bkbc-dot');
        const prevBtn   = carousel.querySelector('.bkbc-prev');
        const nextBtn   = carousel.querySelector('.bkbc-next');
        const progressBar = carousel.querySelector('.bkbc-progress-bar');

        if ( slides.length < 2 ) {
            // Hanya 1 slide: aktifkan saja tanpa carousel logic
            if ( slides[0] ) slides[0].classList.add('active');
            return;
        }

        // ── State ─────────────────────────────────────────────────────────────
        let current     = 0;
        let isAnimating = false;
        let autoTimer   = null;
        let progressTimer = null;

        // ── Set CSS variable untuk durasi ─────────────────────────────────────
        carousel.style.setProperty( '--bkbc-dur', (duration / 1000) + 's' );
        carousel.style.setProperty( '--bkbc-interval', (interval / 1000) + 's' );

        // ── Go to slide ───────────────────────────────────────────────────────
        function goTo( index, direction ) {
            if ( isAnimating ) return;
            if ( index === current ) return;
            isAnimating = true;

            const prev   = current;
            const next   = ( index + slides.length ) % slides.length;
            const dir    = direction || ( next > prev ? 'next' : 'prev' );

            // SLIDE animation: tambahkan class before animating
            if ( animation === 'slide' ) {
                // Reset semua ke posisi default dulu
                slides.forEach(function (s, i) {
                    if ( i !== prev ) {
                        s.style.transform = i < prev ? 'translateX(-100%)' : 'translateX(100%)';
                    }
                });

                // Posisi awal slide baru
                slides[next].style.transform = dir === 'next' ? 'translateX(100%)' : 'translateX(-100%)';
                slides[next].classList.add('active');
                slides[next].setAttribute('aria-hidden', 'false');

                // Trigger reflow
                slides[next].getBoundingClientRect();

                // Animasikan keduanya
                requestAnimationFrame(function () {
                    slides[prev].style.transform = dir === 'next' ? 'translateX(-100%)' : 'translateX(100%)';
                    slides[next].style.transform = 'translateX(0)';
                });

            } else {
                // FADE & ZOOM: toggle class active saja
                slides[next].classList.add('active');
                slides[next].setAttribute('aria-hidden', 'false');
            }

            // Update dots
            dots.forEach(function (d, i) {
                d.classList.toggle('active', i === next);
                d.setAttribute('aria-selected', i === next ? 'true' : 'false');
            });

            // Setelah transisi: bersihkan
            setTimeout(function () {
                slides[prev].classList.remove('active', 'leaving');
                slides[prev].setAttribute('aria-hidden', 'true');
                if ( animation === 'slide' ) {
                    slides[prev].style.transform = '';
                    slides[next].style.transform = '';
                }
                current = next;
                isAnimating = false;
            }, duration + 20 );
        }

        function next() { goTo( current + 1, 'next' ); }
        function prev() { goTo( current - 1, 'prev' ); }

        // ── Progress bar ──────────────────────────────────────────────────────
        function resetProgress() {
            if ( ! progressBar ) return;
            progressBar.classList.remove('running');
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
        }
        function startProgress() {
            if ( ! progressBar || ! autoplay ) return;
            // Force reflow agar transisi berjalan dari 0
            void progressBar.offsetWidth;
            progressBar.classList.add('running');
        }

        // ── Autoplay ──────────────────────────────────────────────────────────
        function startAutoplay() {
            if ( ! autoplay ) return;
            stopAutoplay();
            resetProgress();
            startProgress();
            autoTimer = setInterval(function () {
                next();
                resetProgress();
                setTimeout(startProgress, 50);
            }, interval);
        }
        function stopAutoplay() {
            clearInterval(autoTimer);
            resetProgress();
        }

        // Pause saat hover / focus
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);
        carousel.addEventListener('focusin',    stopAutoplay);
        carousel.addEventListener('focusout',   startAutoplay);

        // ── Arrow buttons ─────────────────────────────────────────────────────
        if ( prevBtn ) {
            prevBtn.addEventListener('click', function () {
                stopAutoplay();
                prev();
                setTimeout(startAutoplay, 200);
            });
        }
        if ( nextBtn ) {
            nextBtn.addEventListener('click', function () {
                stopAutoplay();
                next();
                setTimeout(startAutoplay, 200);
            });
        }

        // ── Dots ──────────────────────────────────────────────────────────────
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                stopAutoplay();
                goTo(i);
                setTimeout(startAutoplay, 200);
            });
        });

        // ── Touch / swipe ─────────────────────────────────────────────────────
        let touchStartX = 0;
        let touchStartY = 0;
        let isDragging  = false;

        carousel.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].clientX;
            touchStartY = e.changedTouches[0].clientY;
            isDragging  = false;
        }, { passive: true });

        carousel.addEventListener('touchmove', function (e) {
            const dx = Math.abs( e.changedTouches[0].clientX - touchStartX );
            const dy = Math.abs( e.changedTouches[0].clientY - touchStartY );
            if ( dx > dy && dx > 10 ) isDragging = true;
        }, { passive: true });

        carousel.addEventListener('touchend', function (e) {
            if ( ! isDragging ) return;
            const dx = e.changedTouches[0].clientX - touchStartX;
            stopAutoplay();
            if ( dx < -40 ) next();
            else if ( dx > 40 ) prev();
            setTimeout(startAutoplay, 200);
        });

        // ── Keyboard navigation ────────────────────────────────────────────────
        carousel.setAttribute('tabindex', '0');
        carousel.addEventListener('keydown', function (e) {
            if ( e.key === 'ArrowLeft'  ) { stopAutoplay(); prev(); setTimeout(startAutoplay, 200); }
            if ( e.key === 'ArrowRight' ) { stopAutoplay(); next(); setTimeout(startAutoplay, 200); }
        });

        // ── Init ──────────────────────────────────────────────────────────────
        // Pastikan slide pertama visible
        slides.forEach(function (s, i) {
            if ( i !== 0 ) {
                s.classList.remove('active');
                s.setAttribute('aria-hidden', 'true');
                if ( animation === 'slide' ) {
                    s.style.transform = 'translateX(100%)';
                }
            }
        });

        startAutoplay();
    });

}());
