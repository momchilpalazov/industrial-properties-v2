/**
 * Simple gallery test script
 */
console.log('Gallery test script loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Check if gallery elements exist
    const galleryLinks = document.querySelectorAll("[data-fancybox='property-gallery']");
    console.log('Found gallery links:', galleryLinks.length);
    
    // List all gallery links
    galleryLinks.forEach((link, index) => {
        console.log(`Gallery link ${index + 1}:`, link.href);
    });
    
    // Check if Fancybox is loaded
    console.log('Fancybox available:', typeof Fancybox !== 'undefined');
    
    if (typeof Fancybox !== 'undefined') {
        console.log('Initializing Fancybox...');
        
        try {
            Fancybox.bind("[data-fancybox='property-gallery']", {
                // Simple configuration
                l10n: {
                    CLOSE: "Затвори",
                    NEXT: "Следваща", 
                    PREV: "Предишна",
                }
            });
            
            console.log('✅ Fancybox initialized successfully!');
        } catch (error) {
            console.error('❌ Fancybox initialization failed:', error);
        }
    } else {
        console.log('⏳ Waiting for Fancybox...');
        
        // Try again after 2 seconds
        setTimeout(() => {
            if (typeof Fancybox !== 'undefined') {
                console.log('Fancybox now available, initializing...');
                try {
                    Fancybox.bind("[data-fancybox='property-gallery']", {
                        l10n: {
                            CLOSE: "Затвори",
                            NEXT: "Следваща", 
                            PREV: "Предишна",
                        }
                    });
                    console.log('✅ Fancybox initialized after delay!');
                } catch (error) {
                    console.error('❌ Delayed Fancybox initialization failed:', error);
                }
            } else {
                console.error('❌ Fancybox still not available after 2 seconds');
            }
        }, 2000);
    }
});
