// Импортираме стиловете за детайлната страница на търговете
import '../styles/components/auction/show.scss';

// Импортираме зависимостите
import ClipboardJS from 'clipboard';
import toastr from 'toastr';

/**
 * Функционалност за детайлна страница на имот от категория "Търгове"
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializeClipboard();
    initializeInquiryForm();
    initializePrintFunctionality();
    initializeHereMaps();
});

/**
 * Инициализира tooltips за бутоните
 */
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Инициализира функционалността за копиране на линк
 */
function initializeClipboard() {
    var clipboard = new ClipboardJS('.copy-link');
    
    clipboard.on('success', function(e) {
        // Показване на toastr съобщение
        toastr.success('Линкът е копиран в клипборда!');
        
        // Скриване на tooltip-а
        var tooltip = bootstrap.Tooltip.getInstance(e.trigger);
        if (tooltip) {
            tooltip.hide();
        }
        
        e.clearSelection();
    });
}

/**
 * Инициализира валидацията на формата за запитване
 */
function initializeInquiryForm() {
    const inquiryForm = document.getElementById('inquiryForm');
    if (!inquiryForm) return;
    
    inquiryForm.addEventListener('submit', function(event) {
        // Проверка за reCAPTCHA ако е включена
        if (typeof grecaptcha !== 'undefined') {
            var recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                event.preventDefault();
                toastr.error('Моля, потвърдете, че не сте робот');
                return false;
            }
        }
        
        // Проверка за съгласие с политиката за лични данни
        const gdprConsent = document.getElementById('gdprConsent');
        if (gdprConsent && !gdprConsent.checked) {
            event.preventDefault();
            toastr.error('Моля, съгласете се с политиката за лични данни');
            return false;
        }
    });
}

/**
 * Инициализира функционалността за принтиране
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
 * Инициализира Here Maps ако има координати на имота
 */
function initializeHereMaps() {
    // Проверяваме дали имаме контейнер за карта и координати
    const mapContainer = document.getElementById('property-map');
    if (!mapContainer || typeof H === 'undefined') return;
    
    // Вземаме координатите от data атрибути
    const lat = parseFloat(mapContainer.dataset.lat);
    const lng = parseFloat(mapContainer.dataset.lng);
    const apiKey = mapContainer.dataset.apiKey;
    
    if (isNaN(lat) || isNaN(lng)) return;
    
    try {
        // Създаваме Here Maps платформа с API ключа
        const platform = new H.service.Platform({
            apiKey: apiKey || 'hPqhtsSWJaVWUUI1vQRnmRvDBlP23bj3Mu-GbOc' // Резервен ключ, ако няма подаден
        });
        
        // Създаваме слоеве
        const defaultLayers = platform.createDefaultLayers();
        
        // Създаваме картата
        const map = new H.Map(
            mapContainer,
            defaultLayers.vector.normal.map,
            {
                center: { lat: lat, lng: lng },
                zoom: 15
            }
        );
        
        // Добавяме интерактивност и контроли
        const ui = H.ui.UI.createDefault(map, defaultLayers);
        const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
        
        // Добавяме маркер за имота
        const marker = new H.map.Marker({ lat: lat, lng: lng });
        
        // Създаваме балон с информация за имота
        const title = mapContainer.dataset.title || '';
        const location = mapContainer.dataset.location || '';
        const price = mapContainer.dataset.price || '';
        
        marker.setData(`
            <div style="padding: 8px; min-width: 150px; max-width: 200px;">
                <h5 style="font-size: 14px; margin-bottom: 5px;">${title}</h5>
                <p style="margin-bottom: 5px; font-size: 12px;">${location}</p>
                ${price ? `<p style="margin-bottom: 0; font-size: 12px;"><strong>Цена:</strong> ${price} €</p>` : ''}
            </div>
        `);
        
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
        
        // Отваряме балона автоматично след 1 секунда
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