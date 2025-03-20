/**
 * Property Show Page JavaScript
 */

// Import the styles for the show page
import '../styles/components/property/show.scss';

// Import our dependencies
import 'bootstrap/js/dist/tooltip';
import ClipboardJS from 'clipboard';
import toastr from 'toastr';

document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializeClipboard();
    initializeInquiryFormValidation();
    initializePrintFunctionality();
    initializePropertyMap();
});

/**
 * Инициализиране на Bootstrap tooltips
 */
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Инициализиране на Clipboard.js за копиране на линкове
 */
function initializeClipboard() {
    var clipboard = new ClipboardJS('.copy-link');
    
    clipboard.on('success', function(e) {
        // Показване на toastr съобщение
        toastr.success(document.querySelector('.copy-link').getAttribute('data-success-message') || 'Линкът е копиран!');
        
        // Скриване на tooltip-а
        var tooltip = bootstrap.Tooltip.getInstance(e.trigger);
        if (tooltip) {
            tooltip.hide();
        }
        
        e.clearSelection();
    });
}

/**
 * Валидация на формата за запитване с reCAPTCHA
 */
function initializeInquiryFormValidation() {
    const inquiryForm = document.getElementById('inquiryForm');
    if (!inquiryForm) return;
    
    inquiryForm.addEventListener('submit', function(event) {
        // Проверка за reCAPTCHA
        if (typeof grecaptcha !== 'undefined') {
            var recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                event.preventDefault();
                toastr.error(document.getElementById('inquiryForm').getAttribute('data-recaptcha-error') || 'Моля, потвърдете, че не сте робот.');
                return false;
            }
        }
        
        // Проверка за съгласие с политиката за лични данни
        if (!document.getElementById('gdprConsent').checked) {
            event.preventDefault();
            toastr.error(document.getElementById('inquiryForm').getAttribute('data-gdpr-error') || 'Моля, съгласете се с политиката за лични данни.');
            return false;
        }
    });
}

/**
 * Функционалност за принтиране на детайлите за имота
 */
function initializePrintFunctionality() {
    const printButton = document.querySelector('.print-property');
    if (!printButton) return;
    
    printButton.addEventListener('click', function() {
        // Скриване на tooltip-а преди принтиране
        var tooltip = bootstrap.Tooltip.getInstance(this);
        if (tooltip) {
            tooltip.hide();
        }
        
        // Добавяне на текуща дата към детайлите
        var propertyDetails = document.querySelector('.property-details');
        if (propertyDetails) {
            var currentDate = new Date().toLocaleDateString('bg-BG');
            propertyDetails.setAttribute('data-print-date', currentDate);
        }
        
        // Извикване на принтиране
        window.print();
    });
}

/**
 * Инициализиране на Here Maps с локацията на имота
 */
function initializePropertyMap() {
    const mapContainer = document.getElementById('property-map');
    if (!mapContainer) return;
    
    // Проверяваме дали имаме атрибути за координати
    const lat = mapContainer.getAttribute('data-lat');
    const lng = mapContainer.getAttribute('data-lng');
    const title = mapContainer.getAttribute('data-title') || '';
    const location = mapContainer.getAttribute('data-location') || '';
    const price = mapContainer.getAttribute('data-price') || '';
    const priceLabel = mapContainer.getAttribute('data-price-label') || 'Цена';
    
    // Ако нямаме координати, излизаме
    if (!lat || !lng) return;
    
    try {
        // Вземаме API ключа от data атрибут
        const apiKey = mapContainer.getAttribute('data-api-key');
        
        if (!apiKey) {
            console.error('Липсва Here Maps API ключ');
            mapContainer.innerHTML = '<div class="alert alert-danger">Грешка при зареждане на картата: липсва API ключ</div>';
            return;
        }
        
        // Създаваме Here Maps платформа с API ключа
        const platform = new H.service.Platform({
            apiKey: apiKey
        });
        
        // Създаваме слоеве
        const defaultLayers = platform.createDefaultLayers();
        
        // Създаваме картата
        const map = new H.Map(
            mapContainer,
            defaultLayers.vector.normal.map,
            {
                center: { 
                    lat: parseFloat(lat), 
                    lng: parseFloat(lng) 
                },
                zoom: 15
            }
        );
        
        // Добавяме интерактивност и контроли
        const ui = H.ui.UI.createDefault(map, defaultLayers);
        const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
        
        // Добавяме маркер за имота
        const marker = new H.map.Marker({ 
            lat: parseFloat(lat), 
            lng: parseFloat(lng) 
        });
        
        // Създаваме балон с информация за имота - по-компактен
        let bubbleContent = `
            <div style="padding: 8px; min-width: 150px; max-width: 200px;">
                <h5 style="font-size: 14px; margin-bottom: 5px;">${title}</h5>
                <p style="margin-bottom: 5px; font-size: 12px;">${location}</p>`;
                
        if (price) {
            bubbleContent += `<p style="margin-bottom: 0; font-size: 12px;"><strong>${priceLabel}:</strong> ${price}</p>`;
        }
        
        bubbleContent += `</div>`;
        
        marker.setData(bubbleContent);
        
        // Добавяме маркера към картата
        map.addObject(marker);
        
        // Добавяме слушател за клик върху маркера
        marker.addEventListener('tap', function(evt) {
            const bubble = new H.ui.InfoBubble(
                evt.target.getGeometry(), 
                { content: evt.target.getData() }
            );
            ui.addBubble(bubble);
        });
        
        // Отваряме балона автоматично
        setTimeout(function() {
            const bubble = new H.ui.InfoBubble(
                marker.getGeometry(), 
                { content: marker.getData() }
            );
            ui.addBubble(bubble);
        }, 1000);
        
        // Ресайз на картата при промяна на размера на прозореца
        window.addEventListener('resize', () => {
            map.getViewPort().resize();
        });
    } catch (error) {
        console.error('Грешка при инициализиране на Here Maps:', error);
        mapContainer.innerHTML = '<div class="alert alert-danger">Грешка при зареждане на картата</div>';
    }
} 