/**
 * Admin Property Form 
 */

// Импортиране на стиловете
import '../../styles/admin/property-form.scss';

// Функции за Here Maps
import { initializeHereMaps } from './components/hereMaps';
import { initializeFeatures } from './components/features';
import { initializeFormValidation } from './components/formValidation';

document.addEventListener('DOMContentLoaded', function() {
    // Инициализиране на динамичните характеристики
    initializeFeatures();
    
    // Инициализиране на валидацията на формата
    initializeFormValidation();
    
    // Инициализиране на картата
    const mapConfigElement = document.getElementById('map-config');
    if (mapConfigElement) {
        const config = {
            apiKey: mapConfigElement.dataset.apiKey,
            latitude: mapConfigElement.dataset.latitude,
            longitude: mapConfigElement.dataset.longitude,
            latitudeField: mapConfigElement.dataset.latitudeField,
            longitudeField: mapConfigElement.dataset.longitudeField,
            locationField: mapConfigElement.dataset.locationField,
            geocodeButton: mapConfigElement.dataset.geocodeButton,
            geocodeInput: mapConfigElement.dataset.geocodeInput,
            geocodeResults: mapConfigElement.dataset.geocodeResults,
            geocodeResultsList: mapConfigElement.dataset.geocodeResultsList,
            mapElement: mapConfigElement.dataset.mapElement
        };
        
        initializeHereMaps(config);
    } else {
        console.error('Map configuration element not found!');
    }
}); 