// Blog Main JavaScript
// Industrial Properties v2

import '../styles/components/blog.scss';

// Blog functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Blog JS loaded');
    
    // Initialize category animations
    initCategoryAnimations();
    
    // Initialize card hover effects
    initCardHoverEffects();
    
    // Initialize scroll animations
    initScrollAnimations();
});

function initCategoryAnimations() {
    const navLinks = document.querySelectorAll('.blog-categories .nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active from all
            navLinks.forEach(l => l.classList.remove('active'));
            // Add active to current
            this.classList.add('active');
        });
    });
}

function initCardHoverEffects() {
    const blogCards = document.querySelectorAll('.blog-card');
    
    blogCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, {
        threshold: 0.1
    });
    
    document.querySelectorAll('.blog-card').forEach(card => {
        observer.observe(card);
    });
} 