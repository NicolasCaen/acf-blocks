/*!
 * Dependencies: gsap
 * 
 * @format
 * Pour ajouter des dépendances, listez-les ci-dessus séparées par des virgules
 * Exemple: gsap, jquery
 * Ces dépendances doivent être enregistrées dans WordPress via wp_register_script()
 */

class GSAPSlider {
    constructor(element, options = {}) {
        if (!element) {
            console.error('Element not found');
            return;
        }

        // Trouver la classe de base
        this.baseClass = 'wp-block-ng1-slider-gsap';

        // Elements avec les classes dynamiques
        this.slider = element;
        this.wrapper = element.querySelector(`.${this.baseClass}__wrapper`);
        this.slides = element.querySelectorAll(`.${this.baseClass}__slide`);
        this.prevBtn = element.querySelector(`.${this.baseClass}__prev`);
        this.nextBtn = element.querySelector(`.${this.baseClass}__next`);

        // Vérifier si les éléments requis existent
        if (!this.wrapper || !this.slides.length) {
            console.error(`Required slider elements not found for ${this.baseClass}`);
            return;
        }

        // Options par défaut
        this.options = {
            autoplay: element.dataset.autoplay !== 'false',
            autoplaySpeed: parseInt(element.dataset.autoplaySpeed) || 5000,
            slideSpeed: 0.8,
            slideEase: 'power2.out',
            ...options
        };

        // États
        this.currentSlide = 0;
        this.isAnimating = false;
        this.isHovered = false;
        this.autoplayInterval = null;
        this.currentLightboxIndex = 0;

        // Initialisation
        this.init();
    }

    init() {
        this.setupInitialState();
        this.createLightbox();
        this.bindEvents();
        this.startAutoplay();
    }

    setupInitialState() {
        gsap.set(this.wrapper, { x: 0 });
    }

    createLightbox() {
        this.lightbox = document.createElement('div');
        this.lightbox.className = `${this.baseClass}__lightbox`;
        this.lightbox.innerHTML = `
            <div class="${this.baseClass}__lightbox-overlay"></div>
            <div class="${this.baseClass}__lightbox-content">
                <button class="${this.baseClass}__lightbox-close">&times;</button>
                <button class="${this.baseClass}__lightbox-prev">&lt;</button>
                <button class="${this.baseClass}__lightbox-next">&gt;</button>
                <img src="" alt="" class="${this.baseClass}__lightbox-image">
            </div>
        `;
        document.body.appendChild(this.lightbox);
    }

    bindEvents() {
        // Navigation slider
        this.prevBtn.addEventListener('click', () => this.prevSlide());
        this.nextBtn.addEventListener('click', () => this.nextSlide());

        // Autoplay et hover
        this.wrapper.addEventListener('mouseenter', () => this.handleHover(true));
        this.wrapper.addEventListener('mouseleave', () => this.handleHover(false));

        // Touch events pour le slider
        this.bindTouchEvents(this.wrapper, this.handleSliderTouch.bind(this));

        // Lightbox events
        this.bindLightboxEvents();
    }

    bindLightboxEvents() {
        const overlay = this.lightbox.querySelector(`.${this.baseClass}__lightbox-overlay`);
        const closeBtn = this.lightbox.querySelector(`.${this.baseClass}__lightbox-close`);
        const prevBtn = this.lightbox.querySelector(`.${this.baseClass}__lightbox-prev`);
        const nextBtn = this.lightbox.querySelector(`.${this.baseClass}__lightbox-next`);
        const content = this.lightbox.querySelector(`.${this.baseClass}__lightbox-content`);

        overlay.addEventListener('click', () => this.closeLightbox());
        closeBtn.addEventListener('click', () => this.closeLightbox());
        prevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.prevLightboxImage();
        });
        nextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.nextLightboxImage();
        });

        // Touch events pour la lightbox
        this.bindTouchEvents(content, this.handleLightboxTouch.bind(this));

        // Click sur les images pour ouvrir la lightbox
        this.slides.forEach((slide, index) => {
            const img = slide.querySelector('img');
            img.addEventListener('click', () => this.openLightbox(index));
        });
    }

    bindTouchEvents(element, handler) {
        let touchStartX = 0;
        let touchEndX = 0;

        element.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        element.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handler(touchStartX - touchEndX);
        }, { passive: true });
    }

    handleSliderTouch(diff) {
        if (Math.abs(diff) > 50) {
            diff > 0 ? this.nextSlide() : this.prevSlide();
        }
    }

    handleLightboxTouch(diff) {
        if (Math.abs(diff) > 50) {
            diff > 0 ? this.nextLightboxImage() : this.prevLightboxImage();
        }
    }

    // Navigation methods
    nextSlide() {
        if (this.isAnimating) return;
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        this.animateSlider();
    }

    prevSlide() {
        if (this.isAnimating) return;
        this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.animateSlider();
    }

    animateSlider() {
        this.isAnimating = true;
        gsap.to(this.wrapper, {
            x: -this.currentSlide * 100 + '%',
            duration: this.options.slideSpeed,
            ease: this.options.slideEase,
            onComplete: () => {
                this.isAnimating = false;
            }
        });
    }

    // Autoplay methods
    startAutoplay() {
        if (!this.options.autoplay || this.isHovered) return;
        this.stopAutoplay();
        this.autoplayInterval = setInterval(() => {
            if (!this.isAnimating) this.nextSlide();
        }, this.options.autoplaySpeed);
    }

    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }

    handleHover(isHovered) {
        this.isHovered = isHovered;
        isHovered ? this.stopAutoplay() : this.startAutoplay();
    }

    // Lightbox methods
    updateLightboxImage(index) {
        const lightboxImg = this.lightbox.querySelector(`.${this.baseClass}__lightbox-image`);
        const img = this.slides[index].querySelector('img');
        
        lightboxImg.classList.add('transitioning');
        
        setTimeout(() => {
            lightboxImg.src = img.dataset.fullSrc || img.src;
            lightboxImg.alt = img.alt;
            this.currentLightboxIndex = index;
            
            lightboxImg.onload = () => {
                lightboxImg.classList.remove('transitioning');
            };
        }, 300);
    }

    openLightbox(index) {
        this.updateLightboxImage(index);
        this.lightbox.classList.add('active');
        this.stopAutoplay();
    }

    closeLightbox() {
        this.lightbox.classList.remove('active');
        this.startAutoplay();
    }

    nextLightboxImage() {
        this.updateLightboxImage((this.currentLightboxIndex + 1) % this.slides.length);
    }

    prevLightboxImage() {
        this.updateLightboxImage((this.currentLightboxIndex - 1 + this.slides.length) % this.slides.length);
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    const sliders = document.querySelectorAll('.wp-block-ng1-slider-gsap__container');
    
    if (sliders.length === 0) return;

    sliders.forEach(slider => {
        try {
            new GSAPSlider(slider);
        } catch (error) {
            console.error('Error initializing slider:', error);
        }
    });
});