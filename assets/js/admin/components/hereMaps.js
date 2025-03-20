/**
 * Here Maps Integration за Admin Property форма
 */

// Проверка за успешно зареждане на скриптовете за Here Maps
function checkHereMapsScriptsLoaded() {
    return typeof H !== 'undefined' && 
           typeof H.service !== 'undefined' && 
           typeof H.service.Platform !== 'undefined' && 
           typeof H.Map !== 'undefined' && 
           typeof H.mapevents !== 'undefined' && 
           typeof H.ui !== 'undefined';
}

/**
 * Инициализиране на Here Maps
 * @param {Object} config - Конфигурация за картата
 */
export function initializeHereMaps(config) {
    try {
        // Проверка за зареждане на Here Maps API
        if (!checkHereMapsScriptsLoaded()) {
            throw new Error('Here Maps API не е зареден правилно. Моля, проверете връзката с интернет.');
        }
        
        // Проверка за валидност на API ключа
        if (!config.apiKey || config.apiKey.length < 10) {
            throw new Error('Невалиден API ключ за Here Maps. Моля, проверете конфигурацията.');
        }
        
        // Инициализация на Here Maps платформата
        const platform = new H.service.Platform({
            apiKey: config.apiKey
        });

        // Проверка за успешна инициализация на платформата
        if (!platform) {
            throw new Error('Неуспешна инициализация на Here Maps платформата.');
        }

        const defaultLayers = platform.createDefaultLayers();
        
        // Проверка за успешна инициализация на слоевете
        if (!defaultLayers || !defaultLayers.raster || !defaultLayers.raster.normal || !defaultLayers.raster.normal.map) {
            throw new Error('Неуспешна инициализация на слоевете на Here Maps.');
        }
        
        // Намиране на полетата за координати
        const latitudeField = document.getElementById(config.latitudeField);
        const longitudeField = document.getElementById(config.longitudeField);
        
        // Проверка за съществуване на полетата за координати
        if (!latitudeField || !longitudeField) {
            throw new Error('Полетата за координати не съществуват.');
        }
        
        // Проверка за съществуване на полето за местоположение
        const locationField = document.getElementById(config.locationField);
        if (!locationField) {
            throw new Error('Полето за местоположение не съществува.');
        }
        
        const latitude = latitudeField.value || config.latitude || 42.6977;
        const longitude = longitudeField.value || config.longitude || 23.3219;
        
        console.log('Coordinates:', latitude, longitude);
        
        // Проверка за валидност на координатите
        if (isNaN(parseFloat(latitude)) || isNaN(parseFloat(longitude))) {
            throw new Error('Невалидни координати. Използвам координати по подразбиране.');
        }
        
        // Проверка за съществуване на елемента за картата
        const mapElement = document.getElementById(config.mapElement);
        if (!mapElement) {
            throw new Error('Елементът за картата не съществува.');
        }
        
        const map = new H.Map(
            mapElement,
            defaultLayers.raster.normal.map,
            {
                zoom: 13,
                center: { lat: parseFloat(latitude), lng: parseFloat(longitude) }
            }
        );
        
        // Проверка за успешна инициализация на картата
        if (!map) {
            throw new Error('Неуспешна инициализация на картата.');
        }
        
        // Проверка за успешно зареждане на картата
        map.addEventListener('mapviewchangeend', function() {
            console.log('Картата е заредена успешно.');
        });

        // Enable map interaction
        const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
        const ui = H.ui.UI.createDefault(map, defaultLayers);
        
        // Проверка за успешна инициализация на поведението и потребителския интерфейс
        if (!behavior || !ui) {
            throw new Error('Неуспешна инициализация на поведението или потребителския интерфейс на картата.');
        }

        // Add marker
        let marker = new H.map.Marker({ lat: parseFloat(latitude), lng: parseFloat(longitude) });
        map.addObject(marker);
        
        // Проверка за успешно добавяне на маркера
        if (!marker) {
            throw new Error('Неуспешно създаване на маркера.');
        }

        // Update marker and coordinates on map click
        map.addEventListener('tap', function(evt) {
            const coords = map.screenToGeo(evt.currentPointer.viewportX, evt.currentPointer.viewportY);
            
            // Update marker position
            map.removeObject(marker);
            marker = new H.map.Marker(coords);
            map.addObject(marker);

            // Update form fields
            latitudeField.value = coords.lat.toFixed(6);
            longitudeField.value = coords.lng.toFixed(6);
        });

        // Geocoding service
        initializeGeocoding(platform, map, marker, latitudeField, longitudeField, locationField, config);

        // Handle window resize
        window.addEventListener('resize', () => map.getViewPort().resize());
        
        return map;
    } catch (error) {
        console.error('Error initializing Here Maps:', error);
        const mapElement = document.getElementById(config.mapElement);
        if (mapElement) {
            mapElement.innerHTML = '<div class="alert alert-danger">Грешка при инициализиране на картата: ' + error.message + '</div>';
        }
        return null;
    }
}

/**
 * Инициализиране на геокодиращата услуга
 */
function initializeGeocoding(platform, map, marker, latitudeField, longitudeField, locationField, config) {
    // Geocoding service
    const geocoder = platform.getSearchService();
    const geocodeButton = document.getElementById(config.geocodeButton);
    const addressInput = document.getElementById(config.geocodeInput);
    
    // Проверка за успешна инициализация на геокодиращата услуга
    if (!geocoder) {
        throw new Error('Неуспешна инициализация на геокодиращата услуга.');
    }
    
    // Проверка за съществуване на бутона и полето за адрес
    if (!geocodeButton || !addressInput) {
        throw new Error('Елементите за геокодиране не съществуват.');
    }
    
    // Проверка за съществуване на елемента за резултатите от геокодирането
    const geocodeResults = document.getElementById(config.geocodeResults);
    const geocodeResultsList = document.getElementById(config.geocodeResultsList);
    if (!geocodeResults || !geocodeResultsList) {
        throw new Error('Елементите за резултатите от геокодирането не съществуват.');
    }
    
    geocodeButton.addEventListener('click', function() {
        const address = addressInput.value;
        if (!address) {
            alert('Моля, въведете адрес');
            return;
        }

        // Показваме индикатор за зареждане
        geocodeButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Търсене...';
        geocodeButton.disabled = true;

        geocoder.geocode({
            q: address,
            in: 'countryCode:BGR',
            limit: 5
        }, (result) => {
            console.log('Пълен резултат от геокодиране:', result);
            
            if (result.items && result.items.length > 0) {
                // Показваме първия резултат на картата
                const coords = result.items[0].position;
                
                // Update map center and marker
                map.setCenter(coords);
                map.setZoom(15);
                map.removeObject(marker);
                marker = new H.map.Marker(coords);
                map.addObject(marker);

                // Update form fields
                latitudeField.value = coords.lat.toFixed(6);
                longitudeField.value = coords.lng.toFixed(6);

                // Update location field if empty
                if (!locationField.value) {
                    const address = result.items[0].address;
                    const locationName = address.city || address.county || address.state || '';
                    locationField.value = locationName;
                }
                
                console.log('Geocoding успешен:', result.items[0]);
                
                // Показваме всички резултати, ако има повече от един
                if (result.items.length > 1) {
                    // Изчистваме предишните резултати
                    geocodeResultsList.innerHTML = '';
                    
                    // Добавяме всеки резултат като опция
                    result.items.forEach((item, index) => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action' + (index === 0 ? ' active' : '');
                        
                        // Форматираме адреса
                        const addr = item.address;
                        const formattedAddress = [
                            addr.label || '',
                            addr.city ? 'Град: ' + addr.city : '',
                            addr.county ? 'Област: ' + addr.county : '',
                            addr.postalCode ? 'Пощенски код: ' + addr.postalCode : ''
                        ].filter(Boolean).join(', ');
                        
                        li.innerHTML = formattedAddress;
                        
                        // Добавяме обработчик на събития
                        li.addEventListener('click', function() {
                            // Премахваме активния клас от всички елементи
                            geocodeResultsList.querySelectorAll('li').forEach(el => el.classList.remove('active'));
                            // Добавяме активен клас към текущия елемент
                            this.classList.add('active');
                            
                            // Актуализираме картата и полетата
                            const itemCoords = item.position;
                            map.setCenter(itemCoords);
                            map.removeObject(marker);
                            marker = new H.map.Marker(itemCoords);
                            map.addObject(marker);
                            
                            // Актуализираме полетата за координати
                            latitudeField.value = itemCoords.lat.toFixed(6);
                            longitudeField.value = itemCoords.lng.toFixed(6);
                            
                            // Актуализираме полето за местоположение, ако е празно
                            if (!locationField.value) {
                                const itemAddr = item.address;
                                const itemLocationName = itemAddr.city || itemAddr.county || itemAddr.state || '';
                                locationField.value = itemLocationName;
                            }
                        });
                        
                        geocodeResultsList.appendChild(li);
                    });
                    
                    // Показваме контейнера с резултати
                    geocodeResults.style.display = 'block';
                } else {
                    // Скриваме контейнера с резултати, ако има само един резултат
                    geocodeResults.style.display = 'none';
                }
            } else {
                console.error('Не са намерени резултати за адрес:', address);
                alert('Не можахме да намерим координатите за този адрес. Моля, опитайте с по-точен адрес или въведете координатите ръчно.');
                // Скриваме контейнера с резултати
                geocodeResults.style.display = 'none';
            }
            
            // Възстановяваме бутона
            geocodeButton.innerHTML = '<i class="bi bi-search"></i> Намери координати';
            geocodeButton.disabled = false;
        }, (error) => {
            console.error('Грешка при геокодиране:', error);
            alert('Възникна грешка при търсенето на координати: ' + error.message);
            
            // Възстановяваме бутона
            geocodeButton.innerHTML = '<i class="bi bi-search"></i> Намери координати';
            geocodeButton.disabled = false;
        });
    });

    // Handle Enter key in address input
    addressInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            geocodeButton.click();
        }
    });
} 