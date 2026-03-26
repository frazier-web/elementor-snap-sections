( function () {

    const NEXT = 1;
    const PREV = -1;

    class TPSkewSlideshow {

        DOM = {
            el: null,
            slides: null,
            slidesInner: null,
            slideNumber: null
        };

        current = 0;
        slidesTotal = 0;
        isAnimating = false;

        constructor( DOM_el ) {
            this.DOM.el          = DOM_el;
            this.DOM.slides      = [ ...this.DOM.el.querySelectorAll( '.slide' ) ];
            this.DOM.slidesInner = this.DOM.slides.map( item => item.querySelector( '.slide__img' ) );
            this.DOM.slideNumber = document.querySelector( '.slides-numbers .active' );

            gsap.set( this.DOM.el, { perspective: 1000 } );

            this.DOM.slides[ this.current ].classList.add( 'slide--current' );
            this.slidesTotal = this.DOM.slides.length;
            this.updateSlideNumber();
        }

        next() { this.navigate( NEXT ); }
        prev() { this.navigate( PREV ); }

        navigate( direction ) {
            if ( this.isAnimating ) return false;
            this.isAnimating = true;

            const previous = this.current;
            this.current = direction === 1
                ? this.current < this.slidesTotal - 1 ? ++this.current : 0
                : this.current > 0 ? --this.current : this.slidesTotal - 1;

            this.updateSlideNumber();

            const currentSlide  = this.DOM.slides[ previous ];
            const upcomingSlide = this.DOM.slides[ this.current ];
            const upcomingInner = this.DOM.slidesInner[ this.current ];

            gsap.timeline( {
                defaults: { duration: 1.2, ease: 'power3.inOut' },
                onStart: () => {
                    this.DOM.slides[ this.current ].classList.add( 'slide--current' );
                    gsap.set( upcomingSlide, { zIndex: 99 } );
                },
                onComplete: () => {
                    this.DOM.slides[ previous ].classList.remove( 'slide--current' );
                    gsap.set( upcomingSlide, { zIndex: 1 } );
                    this.isAnimating = false;
                }
            } )
            .addLabel( 'start', 0 )
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
            }, 'start+=0.1' )
            .fromTo( upcomingInner, { scale: 1.8 }, { scale: 1 }, 'start+=0.17' );
        }

        updateSlideNumber() {
            if ( this.DOM.slideNumber ) {
                this.DOM.slideNumber.innerHTML = this.current + 1 < 10
                    ? '' + ( this.current + 1 )
                    : ( this.current + 1 ).toString();
            }
        }
    }

    window.TPSkewSlideshow = TPSkewSlideshow;

} )();
