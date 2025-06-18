/**
 * Property Show JavaScript
 * Handles Fancybox gallery, copy to clipboard, and general property page interactions
 */

class PropertyShow {
    constructor() {
        this.initialize();
    }

    initialize() {
        this.initializeFancybox();
        this.setupCopyToClipboard();
        this.setupPrintHandler();
        this.setupFormValidation();
    }

    /**
     * Robust Fancybox initialization with multiple fallbacks
     */
    initializeFancybox() {
        // Check if gallery elements exist
        const galleryElements = document.querySelectorAll("[data-fancybox='property-gallery']");
        console.log('Found gallery elements:', galleryElements.length);
        
        if (galleryElements.length === 0) {
            console.warn('No gallery elements found on page');
            return false;
        }
        
        if (typeof Fancybox !== 'undefined') {
            console.log('Fancybox found, initializing...');
            
            try {
                Fancybox.bind("[data-fancybox='property-gallery']", {
                    Toolbar: {
                        display: {
                            left: ["infobar"],
                            middle: ["zoomIn", "zoomOut", "rotateCCW", "rotateCW"],
                            right: ["slideshow", "thumbs", "close"],
                        },
                    },
                    Thumbs: {
                        autoStart: false,
                    },
                    l10n: {
                        CLOSE: "Затвори",
                        NEXT: "Следваща", 
                        PREV: "Предишна",
                        ERROR: "Грешка при зареждане",
                        IMAGE_ERROR: "Снимката не може да бъде заредена",
                    }
                });
                console.log('✓ Fancybox initialized successfully for', galleryElements.length, 'elements');
                return true;
            } catch (error) {
                console.error('Error initializing Fancybox:', error);
                return false;
            }
        } else {
            console.warn('Fancybox library not found');
        }
        return false;
    }

    /**
     * Setup copy to clipboard functionality
     */
    setupCopyToClipboard() {
        const copyButton = document.querySelector('.copy-link');
        if (copyButton && typeof ClipboardJS !== 'undefined') {
            const clipboard = new ClipboardJS(copyButton);
            
            clipboard.on('success', (e) => {
                const successMessage = e.trigger.dataset.successMessage || 'Копирано!';
                this.showToast(successMessage, 'success');
                e.clearSelection();
            });

            clipboard.on('error', (e) => {
                this.showToast('Грешка при копиране', 'error');
            });
        }
    }

    /**
     * Setup print handler
     */
    setupPrintHandler() {
        const printButton = document.querySelector('.print-property');
        if (printButton) {
            printButton.addEventListener('click', () => {
                window.print();
            });
        }
    }

    /**
     * Setup form validation
     */
    setupFormValidation() {
        const form = document.getElementById('inquiryForm');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Check GDPR consent
            const gdprConsent = document.getElementById('gdprConsent');
            if (!gdprConsent || !gdprConsent.checked) {
                const errorMessage = form.dataset.gdprError || 'Моля приемете условията за обработка на лични данни';
                this.showToast(errorMessage, 'error');
                return;
            }

            // Check reCAPTCHA
            if (typeof grecaptcha !== 'undefined') {
                const recaptchaResponse = grecaptcha.getResponse();
                if (!recaptchaResponse) {
                    const errorMessage = form.dataset.recaptchaError || 'Моля решете reCAPTCHA задачата';
                    this.showToast(errorMessage, 'error');
                    return;
                }
            }

            // If all validations pass, submit the form
            this.submitForm(form);
        });
    }

    /**
     * Submit form via AJAX
     */
    async submitForm(form) {
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Disable submit button and show loading state
        submitButton.disabled = true;
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Изпращане...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showToast(result.message || 'Запитването беше изпратено успешно!', 'success');
                form.reset();
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }
            } else {
                this.showToast(result.message || 'Възникна грешка при изпращането', 'error');
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showToast('Възникна грешка при изпращането', 'error');
        } finally {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            // Fallback to alert if toastr is not available
            alert(message);
        }
    }

    /**
     * Try initialization with fallbacks
     */
    initializeWithFallbacks() {
        console.log('DOM ready, attempting Fancybox initialization...');
        
        if (!this.initializeFancybox()) {
            console.log('Fancybox not ready, trying fallbacks...');
            
            // Fallback 1: Try after window load
            window.addEventListener('load', () => {
                console.log('Window loaded, trying Fancybox again...');
                if (!this.initializeFancybox()) {
                    
                    // Fallback 2: Try with delay
                    setTimeout(() => {
                        console.log('Delayed attempt...');
                        if (!this.initializeFancybox()) {
                            console.error('❌ All Fancybox initialization attempts failed');
                        }
                    }, 2000);
                }
            });
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const propertyShow = new PropertyShow();
    propertyShow.initializeWithFallbacks();
});

// Export for potential external use
window.PropertyShow = PropertyShow;
