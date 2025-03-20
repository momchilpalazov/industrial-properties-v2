/**
 * Navigation functionality
 */
export function initializeNavigation() {
    document.addEventListener('DOMContentLoaded', function() {
        // Add active class to current navigation item
        const currentLocation = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(function(link) {
            if (link.getAttribute('href') === currentLocation) {
                link.classList.add('active');
            }
        });
    });
} 