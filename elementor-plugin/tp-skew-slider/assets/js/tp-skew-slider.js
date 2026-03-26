( function ( $ ) {

    'use strict';

    function initSlider( wrap ) {
        if ( typeof window.TPSkewSlideshow === 'undefined' ) {
            console.warn( 'TP Skew Slider: TPSkewSlideshow not found.' );
            return;
        }

        if ( typeof Observer === 'undefined' ) {
            console.warn( 'TP Skew Slider: GSAP Observer not found.' );
            return;
        }

        const slideshow = new window.TPSkewSlideshow( wrap );

        const area    = wrap.closest( '.skew-slider-area' );
        const prevBtn = area ? area.querySelector( '.skew-slider-prev' ) : null;
        const nextBtn = area ? area.querySelector( '.skew-slider-next' ) : null;

        if ( prevBtn ) prevBtn.addEventListener( 'click', () => slideshow.prev() );
        if ( nextBtn ) nextBtn.addEventListener( 'click', () => slideshow.next() );

        Observer.create( {
            target    : document,
            type      : 'wheel,touch,pointer',
            onDown    : () => slideshow.prev(),
            onUp      : () => slideshow.next(),
            wheelSpeed: -1,
            tolerance : 10,
            preventDefault: true,
        } );
    }

    function initAll() {
        document.querySelectorAll( '.skew-slider-wrap' ).forEach( initSlider );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initAll );
    } else {
        initAll();
    }

    $( window ).on( 'elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/tp-skew-slider.default',
            function ( $scope ) {
                const wrap = $scope[ 0 ].querySelector( '.skew-slider-wrap' );
                if ( wrap ) initSlider( wrap );
            }
        );
    } );

} )( jQuery );
