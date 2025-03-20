// Contact Page JavaScript

// Инициализация при зареждане на DOM
document.addEventListener('DOMContentLoaded', function() {
    // Анимации при скролване
    initScrollAnimations();
    
    // Инициализация на Here Maps (ако има контейнер за карта)
    if (document.getElementById('contact-map')) {
        initContactMap();
    }
    
    // Инициализация на формата за контакт
    initContactForm();
});

/**
 * Инициализира анимации при скролване
 */
function initScrollAnimations() {
    const animatedSections = document.querySelectorAll('.animated-section');
    
    // Първоначален преглед на видимите елементи
    checkVisibility(animatedSections);
    
    // Добавяне на event listener за скролване
    window.addEventListener('scroll', function() {
        checkVisibility(animatedSections);
    });
    
    // Функция за проверка на видимостта на елементи
    function checkVisibility(elements) {
        const windowHeight = window.innerHeight;
        const windowTopPosition = window.scrollY;
        const windowBottomPosition = windowTopPosition + windowHeight;
        
        elements.forEach(function(element) {
            const elementHeight = element.offsetHeight;
            const elementTopPosition = element.offsetTop;
            const elementBottomPosition = elementTopPosition + elementHeight;
            
            // Проверка дали елементът е видим във viewport
            if (
                (elementBottomPosition >= windowTopPosition && elementTopPosition <= windowBottomPosition) ||
                (elementTopPosition <= windowTopPosition && elementBottomPosition >= windowBottomPosition)
            ) {
                element.classList.add('visible');
            }
        });
    }
}

/**
 * Инициализира Here Maps
 */
function initContactMap() {
    try {
        const mapContainer = document.getElementById('contact-map');
        const mapDataElement = document.getElementById('map-data');
        
        if (!mapContainer || !mapDataElement) {
            console.error('Липсват необходимите елементи за Here Maps');
            return;
        }
        
        const mapData = JSON.parse(mapDataElement.getAttribute('data-map'));
        const apiKey = mapDataElement.getAttribute('data-api-key');
        
        // Създаване на Here Maps платформа с API ключа
        const platform = new H.service.Platform({
            apiKey: apiKey
        });
        
        // Създаване на слоеве
        const defaultLayers = platform.createDefaultLayers();
        
        // Създаване на картата
        const map = new H.Map(
            mapContainer,
            defaultLayers.vector.normal.map,
            {
                center: { 
                    lat: mapData.latitude, 
                    lng: mapData.longitude 
                },
                zoom: 15
            }
        );
        
        // Добавяне на интерактивност и контроли
        const ui = H.ui.UI.createDefault(map, defaultLayers);
        const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
        
        // Добавяне на маркер за офиса
        const marker = new H.map.Marker({ 
            lat: mapData.latitude, 
            lng: mapData.longitude 
        });
        
        // Създаване на балон с информация за офиса
        marker.setData(`
            <div class="map-info-bubble">
                <h5>${mapData.companyName}</h5>
                <p>${mapData.address}</p>
                <p><strong>Тел:</strong> ${mapData.phone}</p>
                <p><strong>Имейл:</strong> ${mapData.email}</p>
            </div>
        `);
        
        // Добавяне на маркера към картата
        map.addObject(marker);
        
        // Добавяне на слушател за клик върху маркера
        marker.addEventListener('tap', function(evt) {
            const bubble = new H.ui.InfoBubble(
                evt.target.getGeometry(), 
                { content: evt.target.getData() }
            );
            ui.addBubble(bubble);
        });
        
        // Отваряне на балона автоматично
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
        document.getElementById('contact-map').innerHTML = '<div class="alert alert-danger">Грешка при зареждане на картата</div>';
    }
}

/**
 * Инициализира формата за контакт
 */
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (!contactForm) {
        return;
    }
    
    contactForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Деактивиране на формата по време на изпращане
        const submitButton = contactForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Изпращане...
        `;
        
        // Събиране на данните от формата
        const formData = new FormData(contactForm);
        const formDataObject = {};
        
        formData.forEach((value, key) => {
            formDataObject[key] = value;
        });
        
        // Изпращане на заявката
        fetch(contactForm.getAttribute('data-action'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(formDataObject)
        })
        .then(response => response.json())
        .then(data => {
            // Връщане на бутона в нормално състояние
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            
            // Показване на съобщение
            showNotification(data.success ? 'success' : 'danger', data.message);
            
            // Ако е успешно, изчистваме формата
            if (data.success) {
                contactForm.reset();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            showNotification('danger', 'Възникна грешка при изпращането на съобщението. Моля, опитайте отново.');
        });
    });
}

/**
 * Показва известие
 */
function showNotification(type, message) {
    // Премахване на предишни известия
    const existingAlerts = document.querySelectorAll('.alert-float');
    existingAlerts.forEach(alert => {
        alert.remove();
    });
    
    // Създаване на новото известие
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-float alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Добавяне към DOM
    document.body.appendChild(notification);
    
    // Автоматично скриване след 5 секунди
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
} 