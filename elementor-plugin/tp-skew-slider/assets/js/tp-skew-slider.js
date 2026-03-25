/**
 * TP Skew Slider — Frontend initialiser
 *
 * Supports two scroll modes set via data-scroll-mode on .skew-slider-area:
 *
 *   hijack   — Slider sticks to the viewport top. Each scroll tick
 *              advances one slide. After the last slide the page is
 *              released and normal scrolling resumes. Scrolling back
 *              up re-engages the slider at the last slide and walks
 *              backwards, releasing at the first slide.
 *
 *   freeflow — Slider scrolls with the page (no hijack). Arrow
 *              buttons and swipe still work.
 *
 * Multiple instances on one page are supported.
 */

( function ( $ ) {

    'use strict';

    /**
     * Preload background-image elements using native Promise + load events.
     * Falls back gracefully if imagesLoaded is not available.
     */
    function preloadSlideImages( wrap ) {
        const imgs = wrap.querySelectorAll( '.slide__img' );

        if ( ! imgs.length ) {
            return Promise.resolve();
        }

        const promises = Array.from( imgs ).map( el => {
            const urlMatch = ( el.style.backgroundImage || '' ).match( /url\(["']?([^"')]+)["']?\)/ );
            if ( ! urlMatch ) return Promise.resolve();

            return new Promise( resolve => {
                const img = new Image();
                img.onload  = resolve;
                img.onerror = resolve;
                img.src     = urlMatch[ 1 ];
            } );
        } );

        return Promise.all( promises );
    }

    /**
     * Initialise one slider area.
     * @param {HTMLElement} area  — .skew-slider-area
     */
    function initSlider( area ) {
        const wrap = area.querySelector( '.skew-slider-wrap' );
        if ( ! wrap ) return;

        const scrollMode = area.dataset.scrollMode  || 'hijack';
        const tolerance  = parseInt( area.dataset.tolerance, 10 ) || 10;

        const slideshow  = new window.TPSkewSlideshow( wrap );

        const prevBtn    = area.querySelector( '.skew-slider-prev' );
        const nextBtn    = area.querySelector( '.skew-slider-next' );

        if ( prevBtn ) prevBtn.addEventListener( 'click', () => slideshow.prev() );
        if ( nextBtn ) nextBtn.addEventListener( 'click', () => slideshow.next() );

        /* -- touch swipe support (no Observer dependency) --------------- */
        let touchStartY = null;

        wrap.addEventListener( 'touchstart', e => {
            touchStartY = e.touches[ 0 ].clientY;
        }, { passive: true } );

        wrap.addEventListener( 'touchend', e => {
            if ( touchStartY === null ) return;
            const delta = touchStartY - e.changedTouches[ 0 ].clientY;
            if ( Math.abs( delta ) > tolerance ) {
                delta > 0 ? slideshow.next() : slideshow.prev();
            }
            touchStartY = null;
        }, { passive: true } );

        /* -- GSAP Observer (if available — theme bundle already loads it) */
        if ( typeof Observer !== 'undefined' ) {
            Observer.create( {
                target    : scrollMode === 'hijack' ? window : area,
                type      : 'wheel,touch,pointer',
                onDown    : () => { if ( isActive ) slideshow.prev(); },
                onUp      : () => { if ( isActive ) slideshow.next(); },
                wheelSpeed: -1,
                tolerance : tolerance,
                preventDefault: scrollMode === 'hijack',
            } );
        }

        /* -- Scroll-hijack implementation --------------------------------
         *
         *  Strategy:
         *   1. Use GSAP ScrollTrigger (if available) OR a plain
         *      IntersectionObserver + wheel listener to detect when the
         *      slider is in the viewport.
         *   2. When fully in view, block the page scroll and route wheel
         *      events to the slideshow.
         *   3. After the last slide (going down) or the first slide
         *      (going up) the next wheel event releases the hijack and
         *      lets the page scroll continue.
         */

        let isActive = false;   /* whether the slider owns the scroll */

        if ( scrollMode === 'hijack' ) {
            _initHijackScroll( area, wrap, slideshow, tolerance, setActive );
        }

        function setActive( val ) {
            isActive = val;
        }
    }

    /* ------------------------------------------------------------------ */
    function _initHijackScroll( area, wrap, slideshow, tolerance, setActive ) {

        /* We keep track of whether the slider currently "owns" scrolling */
        let hijacked       = false;
        let releaseTimer   = null;
        let accumulatedDY  = 0;

        /* Lock / unlock body scroll */
        function lockScroll() {
            if ( hijacked ) return;
            hijacked = true;
            setActive( true );
            document.body.style.overflow = 'hidden';
        }

        function unlockScroll() {
            if ( ! hijacked ) return;
            hijacked = false;
            setActive( false );
            document.body.style.overflow = '';
        }

        /* Watch whether the slider area is in the viewport */
        const observer = new IntersectionObserver( entries => {
            entries.forEach( entry => {
                if ( entry.isIntersecting && entry.intersectionRatio >= 0.9 ) {
                    lockScroll();
                } else {
                    unlockScroll();
                }
            } );
        }, { threshold: 0.9 } );

        observer.observe( area );

        /* Wheel handler */
        function onWheel( e ) {
            if ( ! hijacked ) return;

            e.preventDefault();
            e.stopPropagation();

            accumulatedDY += e.deltaY;

            if ( Math.abs( accumulatedDY ) < tolerance ) return;

            const direction = accumulatedDY > 0 ? 1 : -1;
            accumulatedDY   = 0;

            const atStart = slideshow.current === 0;
            const atEnd   = slideshow.current === slideshow.slidesTotal - 1;

            if ( direction === 1 && atEnd ) {
                /* Release scroll downward */
                unlockScroll();
                return;
            }

            if ( direction === -1 && atStart ) {
                /* Release scroll upward */
                unlockScroll();
                return;
            }

            direction === 1 ? slideshow.next() : slideshow.prev();
        }

        window.addEventListener( 'wheel', onWheel, { passive: false } );

        /* Re-engage when scrolling brings the slider fully back into view */
        window.addEventListener( 'scroll', () => {
            if ( hijacked ) return;

            const rect = area.getBoundingClientRect();
            const fullyVisible =
                rect.top    >= 0 &&
                rect.bottom <= window.innerHeight;

            if ( fullyVisible ) {
                lockScroll();
            }
        }, { passive: true } );
    }

    /* ------------------------------------------------------------------ */
    function initAll() {
        const areas = document.querySelectorAll( '.skew-slider-area' );
        areas.forEach( area => {
            preloadSlideImages( area ).then( () => {
                area.classList.remove( 'loading' );
                area.classList.add( 'tp-slides-ready' );
            } );
            initSlider( area );
        } );
    }

    /* -- Run on DOMContentLoaded, and also hook Elementor editor/preview  */
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initAll );
    } else {
        initAll();
    }

    /* Elementor frontend hook — re-init when a widget is rendered live */
    $( window ).on( 'elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/tp-skew-slider.default',
            function ( $scope ) {
                const area = $scope[ 0 ].querySelector( '.skew-slider-area' );
                if ( area ) {
                    preloadSlideImages( area ).then( () => {
                        area.classList.remove( 'loading' );
                        area.classList.add( 'tp-slides-ready' );
                    } );
                    initSlider( area );
                }
            }
        );
    } );

} )( jQuery );
