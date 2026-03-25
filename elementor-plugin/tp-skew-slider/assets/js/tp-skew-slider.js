/**
 * TP Skew Slider — Frontend initialiser
 *
 * scroll-mode="hijack"   — slider sticks to viewport, each wheel tick
 *                          advances one slide, releases after last/first.
 * scroll-mode="freeflow" — no scroll hijack; buttons + swipe only.
 */

( function ( $ ) {

    'use strict';

    function initSlider( area ) {
        const wrap = area.querySelector( '.skew-slider-wrap' );
        if ( ! wrap ) return;

        if ( typeof window.TPSkewSlideshow === 'undefined' ) {
            console.warn( 'TP Skew Slider: TPSkewSlideshow not found — check slideshow.js loaded.' );
            return;
        }

        const widget     = area.closest( '.tp-skew-slider-widget' );
        const scrollMode = area.dataset.scrollMode || 'hijack';
        const tolerance  = parseInt( area.dataset.tolerance, 10 ) || 10;
        const slideshow  = new window.TPSkewSlideshow( wrap );

        if ( widget ) {
            widget.classList.add( 'tp-js-ready' );
        }

        /* ---- Button navigation ---------------------------------------- */
        const prevBtn = area.querySelector( '.skew-slider-prev' );
        const nextBtn = area.querySelector( '.skew-slider-next' );

        if ( prevBtn ) prevBtn.addEventListener( 'click', () => slideshow.prev() );
        if ( nextBtn ) nextBtn.addEventListener( 'click', () => slideshow.next() );

        /* ---- Touch swipe ---------------------------------------------- */
        let touchStartY = null;

        area.addEventListener( 'touchstart', function ( e ) {
            touchStartY = e.touches[ 0 ].clientY;
        }, { passive: true } );

        area.addEventListener( 'touchend', function ( e ) {
            if ( touchStartY === null ) return;
            const delta = touchStartY - e.changedTouches[ 0 ].clientY;
            if ( Math.abs( delta ) > tolerance ) {
                delta > 0 ? slideshow.next() : slideshow.prev();
            }
            touchStartY = null;
        }, { passive: true } );

        /* ---- Scroll hijack -------------------------------------------- */
        if ( scrollMode === 'hijack' ) {
            _initHijack( area, slideshow, tolerance );
        }
    }

    /* -------------------------------------------------------------------- */
    function _initHijack( area, slideshow, tolerance ) {

        let hijacked      = false;
        let accumulatedDY = 0;
        let wheelCooldown = false;

        function lock() {
            if ( hijacked ) return;
            hijacked = true;
            document.body.style.overflow = 'hidden';
        }

        function unlock() {
            if ( ! hijacked ) return;
            hijacked = false;
            accumulatedDY = 0;
            document.body.style.overflow = '';
        }

        /* Engage immediately if slider starts in view */
        function checkVisibility() {
            const rect = area.getBoundingClientRect();
            const inView = rect.top >= -1 && rect.bottom <= window.innerHeight + 1;
            if ( inView ) {
                lock();
            } else {
                unlock();
            }
        }

        checkVisibility();

        /* Re-check on scroll (when released, scroll brings it back into view) */
        window.addEventListener( 'scroll', checkVisibility, { passive: true } );

        /* Wheel handler — attached to window so it fires even when body scroll is locked */
        window.addEventListener( 'wheel', function ( e ) {
            if ( ! hijacked ) return;

            e.preventDefault();

            if ( wheelCooldown ) return;

            accumulatedDY += e.deltaY;
            if ( Math.abs( accumulatedDY ) < tolerance ) return;

            const direction   = accumulatedDY > 0 ? 1 : -1;
            accumulatedDY     = 0;

            const atEnd   = slideshow.current === slideshow.slidesTotal - 1;
            const atStart = slideshow.current === 0;

            if ( direction === 1 && atEnd ) {
                unlock();
                /* Nudge the page scroll forward one tick so it actually moves */
                window.scrollBy( { top: 80, behavior: 'auto' } );
                return;
            }

            if ( direction === -1 && atStart ) {
                unlock();
                window.scrollBy( { top: -80, behavior: 'auto' } );
                return;
            }

            /* Brief cooldown so rapid wheel events don't skip multiple slides */
            wheelCooldown = true;
            setTimeout( function () { wheelCooldown = false; }, 800 );

            direction === 1 ? slideshow.next() : slideshow.prev();

        }, { passive: false } );
    }

    /* -------------------------------------------------------------------- */
    function initAll() {
        document.querySelectorAll( '.skew-slider-area' ).forEach( initSlider );
    }

    /* Run after DOM + scripts are ready */
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initAll );
    } else {
        initAll();
    }

    /* Elementor live preview — re-init when widget renders in editor */
    $( window ).on( 'elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/tp-skew-slider.default',
            function ( $scope ) {
                const area = $scope[ 0 ].querySelector( '.skew-slider-area' );
                if ( area ) initSlider( area );
            }
        );
    } );

} )( jQuery );
