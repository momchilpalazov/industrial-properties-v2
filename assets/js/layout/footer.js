/**
 * Footer functionality
 */
export function initializeFooter() {
    document.addEventListener('DOMContentLoaded', function() {
        // Свързване на линка от менюто в footer-а към бутона за cookie настройки
        const cookieSettingsMenuLink = document.getElementById('cookie-settings-menu-link');
        const cookieSettingsBtn = document.getElementById('cookie-settings-btn');
        
        if (cookieSettingsMenuLink && cookieSettingsBtn) {
            cookieSettingsMenuLink.addEventListener('click', function(e) {
                e.preventDefault();
                cookieSettingsBtn.click();
            });
        }
    });
} 