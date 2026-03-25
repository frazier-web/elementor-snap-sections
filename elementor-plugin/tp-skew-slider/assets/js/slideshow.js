/**
 * TP Skew Slider — Slideshow engine
 * Ported verbatim from the original theme skew-slider/slideshow.js
 * with one addition: accepts a container element argument so multiple
 * slider instances on the same page work independently.
 */

const NEXT = 1;
const PREV = -1;

class TPSkewSlideshow {

    DOM = {
        el:          null,
        slides:      null,
        slidesInner: null,
        slideNumber: null,
    };

    current      = 0;
    slidesTotal  = 0;
    isAnimating  = false;

    constructor( containerEl ) {
        this.DOM.el          = containerEl;
        this.DOM.slides      = [ ...this.DOM.el.querySelectorAll( '.slide' ) ];
        this.DOM.slidesInner = this.DOM.slides.map( item => item.querySelector( '.slide__img' ) );
        this.DOM.slideNumber = containerEl
            .closest( '.skew-slider-area' )
            .querySelector( '.slides-numbers .active' );

        gsap.set( this.DOM.el, { perspective: 1000 } );

        this.DOM.slides[ this.current ].classList.add( 'slide--current' );
        this.slidesTotal = this.DOM.slides.length;
        this._updateSlideNumber();
    }

    next() { this._navigate( NEXT ); }
    prev() { this._navigate( PREV ); }

    _navigate( direction ) {
        if ( this.isAnimating ) return false;
        this.isAnimating = true;

        const previous   = this.current;
        this.current     = direction === NEXT
            ? ( this.current < this.slidesTotal - 1 ? ++this.current : 0 )
            : ( this.current > 0 ? --this.current : this.slidesTotal - 1 );

        this._updateSlideNumber();

        const currentSlide  = this.DOM.slides[ previous ];
        const currentInner  = this.DOM.slidesInner[ previous ];
        const upcomingSlide = this.DOM.slides[ this.current ];
        const upcomingInner = this.DOM.slidesInner[ this.current ];

        gsap.timeline( {
            defaults: {
                duration : 1.2,
                ease     : 'power3.inOut',
            },
            onStart: () => {
                this.DOM.slides[ this.current ].classList.add( 'slide--current' );
                gsap.set( upcomingSlide, { zIndex: 99 } );
            },
            onComplete: () => {
                this.DOM.slides[ previous ].classList.remove( 'slide--current' );
                gsap.set( upcomingSlide, { zIndex: 1 } );
                this.isAnimating = false;
            },
        } )
        .addLabel( 'start', 0 )
        .to( currentSlide, {
            yPercent: -direction * 100,
        }, 'start' )
        .fromTo( upcomingSlide, {
            yPercent  : 0,
            autoAlpha : 0,
            rotationX : 140,
            scale     : 0.1,
            z         : -1000,
        }, {
            autoAlpha : 1,
            rotationX : 0,
            z         : 0,
            scale     : 1,
        }, 'start+=0.1' )
        .fromTo( upcomingInner, {
            scale: 1.8,
        }, {
            scale: 1,
        }, 'start+=0.17' );
    }

    _updateSlideNumber() {
        if ( this.DOM.slideNumber ) {
            this.DOM.slideNumber.innerHTML = this._pad( this.current + 1 );
        }
    }

    _pad( num ) {
        return num < 10 ? `${ num }` : num.toString();
    }
}

window.TPSkewSlideshow = TPSkewSlideshow;
