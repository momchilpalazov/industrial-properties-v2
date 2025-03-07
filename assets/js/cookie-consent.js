/**
 * Cookie Consent Manager
 * Управление на съгласието за бисквитки според GDPR
 */
class CookieConsent {
    constructor() {
        this.cookieName = 'industrial_cookie_consent';
        this.cookieExpiration = 365; // Дни
        this.necessaryCookies = true; // Необходимите бисквитки винаги са активни
        this.init();
    }

    init() {
        // Проверка дали потребителят вече е дал съгласие
        if (!this.hasConsent()) {
            this.showBanner();
        }

        // Закачане на event listeners
        document.addEventListener('DOMContentLoaded', () => {
            this.attachEventListeners();
        });
    }

    // Прикачване на event listeners към бутоните
    attachEventListeners() {
        // Бутон за приемане на всички бисквитки
        const acceptAllBtn = document.getElementById('cookie-accept-all');
        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', () => {
                this.setConsent({
                    necessary: true,
                    functional: true,
                    analytics: true,
                    marketing: true
                });
                this.hideBanner();
            });
        }

        // Бутон за приемане само на необходимите бисквитки
        const acceptNecessaryBtn = document.getElementById('cookie-accept-necessary');
        if (acceptNecessaryBtn) {
            acceptNecessaryBtn.addEventListener('click', () => {
                this.setConsent({
                    necessary: true,
                    functional: false,
                    analytics: false,
                    marketing: false
                });
                this.hideBanner();
            });
        }

        // Бутон за запазване на персонализираните настройки
        const savePreferencesBtn = document.getElementById('cookie-save-preferences');
        if (savePreferencesBtn) {
            savePreferencesBtn.addEventListener('click', () => {
                const functional = document.getElementById('cookie-functional').checked;
                const analytics = document.getElementById('cookie-analytics').checked;
                const marketing = document.getElementById('cookie-marketing').checked;

                this.setConsent({
                    necessary: true,
                    functional: functional,
                    analytics: analytics,
                    marketing: marketing
                });
                this.hideBanner();
            });
        }

        // Бутон за показване на персонализираните настройки
        const customizeBtn = document.getElementById('cookie-customize');
        if (customizeBtn) {
            customizeBtn.addEventListener('click', () => {
                document.getElementById('cookie-banner-main').classList.add('d-none');
                document.getElementById('cookie-banner-settings').classList.remove('d-none');
            });
        }

        // Връщане към основния изглед от настройките
        const backBtn = document.getElementById('cookie-back');
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                document.getElementById('cookie-banner-settings').classList.add('d-none');
                document.getElementById('cookie-banner-main').classList.remove('d-none');
            });
        }

        // Бутон в страничното меню за отваряне на банера отново
        const openSettingsBtn = document.getElementById('cookie-settings-btn');
        if (openSettingsBtn) {
            openSettingsBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.showBanner();
            });
        }
    }

    // Показване на банера
    showBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) {
            banner.classList.remove('d-none');
            
            // Възстановяване на запазените предпочитания, ако има такива
            const preferences = this.getStoredConsent();
            if (preferences) {
                document.getElementById('cookie-functional').checked = preferences.functional;
                document.getElementById('cookie-analytics').checked = preferences.analytics;
                document.getElementById('cookie-marketing').checked = preferences.marketing;
            }
        }
    }

    // Скриване на банера
    hideBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) {
            banner.classList.add('d-none');
        }
    }

    // Проверка дали потребителят вече е дал съгласие
    hasConsent() {
        return this.getCookie(this.cookieName) !== null;
    }

    // Запазване на съгласието
    setConsent(preferences) {
        const consentValue = JSON.stringify(preferences);
        this.setCookie(this.cookieName, consentValue, this.cookieExpiration);
        
        // Тук можете да интегрирате събитие за Google Tag Manager, ако използвате
        if (typeof window.dataLayer !== 'undefined') {
            window.dataLayer.push({
                event: 'cookieConsentUpdate',
                'cookies.necessary': preferences.necessary,
                'cookies.functional': preferences.functional,
                'cookies.analytics': preferences.analytics,
                'cookies.marketing': preferences.marketing
            });
        }

        // Изпълняване на колбек за всеки тип бисквитки
        if (preferences.analytics) {
            this.enableAnalytics();
        }
        
        if (preferences.marketing) {
            this.enableMarketing();
        }
    }

    // Получаване на запазените предпочитания
    getStoredConsent() {
        const consent = this.getCookie(this.cookieName);
        return consent ? JSON.parse(consent) : null;
    }

    // Активиране на аналитични бисквитки (Google Analytics и т.н.)
    enableAnalytics() {
        // Тук може да добавите код за активиране на Google Analytics например
        console.log('Аналитичните бисквитки са активирани');
    }

    // Активиране на маркетингови бисквитки (реклами и т.н.)
    enableMarketing() {
        // Тук може да добавите код за активиране на маркетингови инструменти
        console.log('Маркетинговите бисквитки са активирани');
    }

    // Помощна функция за задаване на бисквитка
    setCookie(name, value, days) {
        let expires = '';
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax; Secure';
    }

    // Помощна функция за получаване на бисквитка
    getCookie(name) {
        const nameEQ = name + '=';
        const cookies = document.cookie.split(';');
        for(let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i];
            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1, cookie.length);
            }
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length, cookie.length);
            }
        }
        return null;
    }
}

// Инициализиране на Cookie Consent
const cookieConsent = new CookieConsent();

// Инициализация при зареждане на страницата
document.addEventListener('DOMContentLoaded', () => {
    const consent = cookieConsent.getStoredConsent();
    
    // Ако има съхранено съгласие, активирайте съответните бисквитки
    if (consent) {
        if (consent.analytics) {
            cookieConsent.enableAnalytics();
        }
        
        if (consent.marketing) {
            cookieConsent.enableMarketing();
        }
    }
}); 