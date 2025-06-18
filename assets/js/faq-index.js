// FAQ page functionality
import '../styles/components/faq/index.scss';

console.log('FAQ page script loading...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('FAQ DOM fully loaded, initializing accordion...');
    
    // Open accordion if there's a hash in URL
    const hash = window.location.hash;
    if (hash) {
        console.log('Found hash in URL:', hash);
        const targetAccordion = document.querySelector(hash);
        if (targetAccordion && targetAccordion.classList.contains('accordion-collapse')) {
            console.log('Opening accordion for hash:', hash);
            const button = document.querySelector(`[data-bs-target="${hash}"]`);
            if (button && button.classList.contains('collapsed')) {
                button.click();
                
                setTimeout(() => {
                    targetAccordion.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 300);
            }
        }
    }    // Listen for Bootstrap collapse events instead of click events
    document.querySelectorAll('.accordion-collapse').forEach(collapse => {
        // Ensure the collapse element has a valid ID
        if (!collapse.id) {
            console.warn('Accordion collapse element missing ID:', collapse);
            return;
        }
        
        const accordionId = '#' + collapse.id;
        
        // When accordion shows (opens)
        collapse.addEventListener('shown.bs.collapse', function() {
            console.log('Accordion opened:', accordionId);
            history.replaceState(null, null, accordionId);
        });
        
        // When accordion hides (closes)  
        collapse.addEventListener('hidden.bs.collapse', function() {
            console.log('Accordion closed:', accordionId);
            // Only remove hash if it matches this accordion
            if (window.location.hash === accordionId) {
                history.replaceState(null, null, window.location.pathname + window.location.search);
            }
        });
    });
    
    console.log('FAQ accordion functionality initialized successfully');
});
