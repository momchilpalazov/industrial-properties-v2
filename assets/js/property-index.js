/**
 * Property Index Page
 */

// Import styles
import '../styles/components/property/filters.scss';
import '../styles/components/property/card.scss';
import '../styles/components/property/map.scss';
import '../styles/components/property/select2.scss';

// Import components
import { initializePropertyFilters } from './components/property/filters';
import { initializeHereMaps } from './components/property/hereMaps';
import { initializePropertySelect2 } from './components/property/select2';

console.log('Property index script loading...');

// Уверяваме се, че скриптът се изпълнява след пълното зареждане на DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeComponents);
} else {
    // Ако DOM е вече зареден (ако скриптът се зарежда асинхронно)
    initializeComponents();
}

// Функция за инициализиране на всички компоненти
function initializeComponents() {
    console.log('DOM fully loaded, initializing components...');
    
    // Initialize property filters
    initializePropertyFilters();
    
    // Initialize property select2
    initializePropertySelect2();
    
    // Get properties from the data attribute in HTML
    const propertiesElement = document.getElementById('properties-data');
    
    if (propertiesElement) {
        console.log('Found properties data element');
        
        try {
            // Parse properties from the data attribute
            const properties = JSON.parse(propertiesElement.dataset.properties || '[]');
            const apiKey = propertiesElement.dataset.apiKey || '';
            
            console.log(`Found ${properties.length} properties with coordinates`);
            
            // Initialize Here Maps with properties
            if (properties.length > 0) {
                initializeHereMaps(properties, apiKey);
            }
        } catch (error) {
            console.error('Error parsing properties data:', error);
        }
    } else {
        console.warn('Properties data element not found!');
    }
} 