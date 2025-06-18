// Import the styles for the homepage
import '../styles/home.scss';

// Home page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Домашна страница инициализирана');
    
    // Smooth scrolling for anchor links
    initSmoothScrolling();
    
    // Scroll indicator functionality
    initScrollIndicator();
    
    // Scroll-triggered animations
    initScrollAnimations();
    
    // Enhanced hover effects
    initHoverEffects();
    
    // Loading animations
    initLoadingAnimations();
    
    // Preload critical images only when needed
    const heroSection = document.querySelector('.hero-section');
    const propertyImages = document.querySelectorAll('.property-image, .vip-property-image');
    
    // Only preload images that are actually visible on the page
    if (heroSection && document.querySelector('[style*="hero-bg.jpg"]')) {
        const heroImageLink = document.createElement('link');
        heroImageLink.rel = 'preload';
        heroImageLink.href = '/images/hero-bg.jpg';
        heroImageLink.as = 'image';
        document.head.appendChild(heroImageLink);
    }
    
    // Enhanced image loading with fade-in effect
    const images = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('skeleton-loader');
                    img.classList.add('fade-in');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    // Performance monitoring
    if (typeof performance !== 'undefined' && performance.mark) {
        performance.mark('home-js-loaded');
        
        window.addEventListener('load', () => {
            performance.mark('home-fully-loaded');
            
            // Log Core Web Vitals if available
            if (typeof PerformanceObserver !== 'undefined') {
                try {
                    const observer = new PerformanceObserver((list) => {
                        list.getEntries().forEach((entry) => {
                            console.log(`${entry.name}: ${entry.value}ms`);
                        });
                    });
                    observer.observe({entryTypes: ['measure']});
                } catch (e) {
                    // PerformanceObserver not supported
                }
            }
        });
    }
});

function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Проверяваме дали href не е празен или само "#"
            if (!href || href === '#' || href.length <= 1) {
                return; // Не правим нищо за празни селектори
            }
            
            e.preventDefault();
            
            try {
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            } catch (error) {
                console.warn('Invalid selector:', href);
            }
        });
    });
}

function initScrollIndicator() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    const propertyTypesSection = document.querySelector('.property-types');
    
    if (scrollIndicator && propertyTypesSection) {
        // Click handler for scroll indicator
        scrollIndicator.addEventListener('click', function() {
            propertyTypesSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
        
        // Hide scroll indicator when user starts scrolling
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            
            if (scrolled > 100) {
                scrollIndicator.style.opacity = '0';
                scrollIndicator.style.transform = 'translateX(-50%) translateY(20px)';
            } else {
                scrollIndicator.style.opacity = '1';
                scrollIndicator.style.transform = 'translateX(-50%) translateY(0)';
            }
            
            // Clear timeout and set a new one
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (scrolled < 50) {
                    scrollIndicator.style.opacity = '1';
                }
            }, 1000);
        });
        
        // Add cursor pointer
        scrollIndicator.style.cursor = 'pointer';
    }
}

function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    // Add fade-in-up class to elements that should animate
    const animateElements = document.querySelectorAll('.type-card, .property-card, .section-header');
    animateElements.forEach(el => {
        el.classList.add('fade-in-up');
        observer.observe(el);
    });
}

function initHoverEffects() {
    // Enhanced property card hover effects
    const propertyCards = document.querySelectorAll('.property-card, .type-card');
    propertyCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Button ripple effect
    const buttons = document.querySelectorAll('.btn-industrial');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple-effect');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

function initLoadingAnimations() {
    // Simulate loading for images
    const images = document.querySelectorAll('.property-image, .vip-property-image');
    images.forEach(img => {
        if (!img.complete) {
            const placeholder = document.createElement('div');
            placeholder.className = 'skeleton-loader';
            placeholder.style.height = img.style.height || '220px';
            img.parentNode.insertBefore(placeholder, img);
            
            img.addEventListener('load', function() {
                placeholder.remove();
                this.style.opacity = '0';
                this.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    this.style.opacity = '1';
                }, 10);
            });
        }
    });
}

// Add ripple effect styles
const style = document.createElement('style');
style.textContent = `
    .btn-industrial {
        position: relative;
        overflow: hidden;
    }
    
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Progressive enhancement for modern browsers
if (CSS.supports('backdrop-filter', 'blur(10px)')) {
    document.documentElement.classList.add('supports-backdrop-filter');
}