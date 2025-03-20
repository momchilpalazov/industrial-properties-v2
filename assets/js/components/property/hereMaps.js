/**
 * Here Maps Integration
 */

export function initializeHereMaps(properties, apiKey) {
    // Проверка за необходимите елементи
    const mapToggle = document.getElementById('mapToggle');
    const mapContainer = document.getElementById('map-container');
    const mapWrapper = document.getElementById('mapWrapper');
    
    if (!mapToggle || !mapContainer || !mapWrapper) {
        console.error('Here Maps components not found!');
        return;
    }
    
    console.log('Initializing Here Maps with properties:', properties.length);
    
    // Проверяваме запазеното състояние на картата
    let isMapVisible = localStorage.getItem('mapVisible') === 'true';
    console.log('Initial map visibility:', isMapVisible);
    
    // Задаваме първоначалното състояние
    updateMapVisibility(isMapVisible);
    
    // Инициализираме картата само когато е необходимо
    let map = null;
    let mapInitialized = false;
    
    // Обработка на клик върху бутона за картата
    mapToggle.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Map toggle clicked!');
        
        // Превключваме видимостта
        isMapVisible = !isMapVisible;
        
        // Показваме/скриваме картата
        updateMapVisibility(isMapVisible);
        
        // Запазваме в localStorage
        localStorage.setItem('mapVisible', isMapVisible);
        console.log('Updated map visibility:', isMapVisible);
        
        // Инициализираме картата при първото показване
        if (isMapVisible && !mapInitialized) {
            initMap();
        }
    });
    
    // Функция за обновяване на видимостта на картата
    function updateMapVisibility(isVisible) {
        console.log('Updating map visibility to:', isVisible);
        if (isVisible) {
            mapWrapper.classList.add('show');
            if (!mapInitialized) {
                initMap();
            }
        } else {
            mapWrapper.classList.remove('show');
        }
    }
    
    // Функция за инициализиране на картата
    function initMap() {
        if (mapInitialized) return;
        
        try {
            // Check if Here Maps API is loaded
            if (!window.H) {
                console.error('Here Maps API is not loaded');
                return;
            }
            
            console.log('Creating Here Maps instance with API key:', apiKey);
            
            // Initialize platform with API key
            const platform = new H.service.Platform({
                apikey: apiKey
            });
            
            // Initialize map with default layers
            const defaultLayers = platform.createDefaultLayers();
            
            // Create map instance
            map = new H.Map(
                mapContainer,
                defaultLayers.vector.normal.map,
                {
                    zoom: 12,
                    center: { lat: properties[0].latitude, lng: properties[0].longitude }
                }
            );
            
            // Add interaction and UI components
            const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
            const ui = H.ui.UI.createDefault(map, defaultLayers);
            
            // Add markers for each property
            addPropertyMarkers(map, properties);
            
            // Resize handler for responsive map
            window.addEventListener('resize', () => map.getViewPort().resize());
            
            // Set flag to prevent re-initialization
            mapInitialized = true;
            console.log('Here Maps initialized successfully');
        } catch (error) {
            console.error('Error initializing Here Maps:', error);
        }
    }
    
    // Функция за добавяне на маркери за имотите
    function addPropertyMarkers(map, properties) {
        console.log('Adding markers for', properties.length, 'properties');
        
        const propertyGroup = new H.map.Group();
        map.addObject(propertyGroup);
        
        // Добавяме маркери за всеки имот
        properties.forEach(property => {
            const marker = new H.map.Marker({ lat: property.latitude, lng: property.longitude });
            marker.setData(property);
            propertyGroup.addObject(marker);
        });
        
        // Обработка на събитие при клик върху маркер
        propertyGroup.addEventListener('tap', function(evt) {
            const marker = evt.target;
            const propertyData = marker.getData();
            
            // Създаваме информационен прозорец
            const bubbleContent = `
                <div style="padding: 10px; max-width: 200px;">
                    <a href="/property/${propertyData.id}" style="font-weight: bold; color: #1976d2; text-decoration: none;">
                        ${propertyData.title}
                    </a>
                </div>`;
            
            // Показваме информационния прозорец
            const bubble = new H.ui.InfoBubble(marker.getGeometry(), {
                content: bubbleContent
            });
            
            ui.addBubble(bubble);
        });
        
        // Центрираме картата, за да се виждат всички маркери
        if (properties.length > 1) {
            map.getViewModel().setLookAtData({
                bounds: propertyGroup.getBoundingBox()
            });
        }
    }
} 