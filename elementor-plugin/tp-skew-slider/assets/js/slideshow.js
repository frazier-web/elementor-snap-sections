/**
 * TP Skew Slider — Slideshow engine
 */

( function () {

    const NEXT = 1;
    const PREV = -1;

    class TPSkewSlideshow {

        constructor( containerEl ) {
            this.el          = containerEl;
            this.slides      = Array.from( containerEl.querySelectorAll( '.slide' ) );
            this.slidesInner = this.slides.map( s => s.querySelector( '.slide__img' ) );
            this.slideNumber = containerEl
                .closest( '.skew-slider-area' )
                .querySelector( '.slides-numbers .active' );

            this.current     = 0;
            this.slidesTotal = this.slides.length;
            this.isAnimating = false;

            if ( typeof gsap !== 'undefined' ) {
                gsap.set( this.el, { perspective: 1000 } );
            }

            this.slides[ this.current ].classList.add( 'slide--current' );
            this._updateSlideNumber();
        }

        next() { this._navigate( NEXT ); }
        prev() { this._navigate( PREV ); }

        _navigate( direction ) {
            if ( this.isAnimating ) return;
            if ( typeof gsap === 'undefined' ) return;

            this.isAnimating = true;

            const previous   = this.current;
            this.current     = direction === NEXT
                ? ( this.current < this.slidesTotal - 1 ? ++this.current : 0 )
                : ( this.current > 0 ? --this.current : this.slidesTotal - 1 );

            this._updateSlideNumber();

            const currentSlide  = this.slides[ previous ];
            const upcomingSlide = this.slides[ this.current ];
            const upcomingInner = this.slidesInner[ this.current ];

            const tl = gsap.timeline( {
                defaults: { duration: 1.2, ease: 'power3.inOut' },
                onStart: () => {
                    upcomingSlide.classList.add( 'slide--current' );
                    gsap.set( upcomingSlide, { zIndex: 99 } );
                },
                onComplete: () => {
                    currentSlide.classList.remove( 'slide--current' );
                    gsap.set( upcomingSlide, { zIndex: 1 } );
                    this.isAnimating = false;
                },
            } );

            tl.addLabel( 'start', 0 )
              .to( currentSlide, { yPercent: -direction * 100 }, 'start' )
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
                }, 'start+=0.1' );

            if ( upcomingInner ) {
                tl.fromTo( upcomingInner, { scale: 1.8 }, { scale: 1 }, 'start+=0.17' );
            }
        }

        _updateSlideNumber() {
            if ( this.slideNumber ) {
                this.slideNumber.textContent = this.current + 1;
            }
        }
    }

    window.TPSkewSlideshow = TPSkewSlideshow;

} )();
