// Импортираме стиловете за страницата с търгове
import '../styles/components/auction/index.scss';
import '../styles/components/property/map.scss';
import '../styles/components/property/filters.scss';

/**
 * Функционалност за страницата със списък на имоти от категория "Търгове"
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeFilterToggle();
    initializeMapToggle();
    initializeSelect2();
});

/**
 * Инициализира функционалността на бутона за филтри
 */
function initializeFilterToggle() {
    // Вземаме елементите
    const filterToggle = document.getElementById('filterToggle');
    const filterSection = document.getElementById('filterSection');
    const filtersWrapper = document.querySelector('.filters-wrapper');
    
    if (!filterToggle || !filterSection) {
        console.error('Не са намерени елементите за филтриране!');
        return;
    }
    
    // Доколко е показан филтъра е запазено в localStorage
    let isFilterVisible = localStorage.getItem('filterVisible') === 'true';
    
    // Задаваме първоначалното състояние на филтрите
    updateFilterVisibility(isFilterVisible);
    
    // Опростена функция за обработка на клик
    function handleFilterToggle(e) {
        e.preventDefault();
        
        // Превключваме стойността
        isFilterVisible = !isFilterVisible;
        
        // Обновяваме UI според новата стойност
        updateFilterVisibility(isFilterVisible);
        
        // Запазваме състоянието в localStorage
        localStorage.setItem('filterVisible', isFilterVisible);
        
        // Ако филтрите са показани, скролваме до тях 
        if (isFilterVisible && filtersWrapper) {
            window.scrollTo({
                top: filtersWrapper.offsetTop - 70,
                behavior: 'smooth'
            });
        }
    }
    
    // Функция за обновяване на видимостта
    function updateFilterVisibility(isVisible) {
        if (isVisible) {
            filterSection.classList.add('show');
            filterToggle.classList.add('active');
        } else {
            filterSection.classList.remove('show');
            filterToggle.classList.remove('active');
        }
    }
    
    // Изчистваме всички съществуващи event listeners (за всеки случай)
    filterToggle.removeEventListener('click', handleFilterToggle);
    
    // Добавяме event listener
    filterToggle.addEventListener('click', handleFilterToggle);
}

/**
 * Инициализира функционалността на бутона за картата
 */
function initializeMapToggle() {
    const mapToggle = document.getElementById('mapToggle');
    const mapWrapper = document.getElementById('mapWrapper');
    
    if (!mapToggle || !mapWrapper) return;
    
    // Проверяваме запазеното състояние на картата
    const isMapVisible = localStorage.getItem('mapVisible') === 'true';
    
    // Задаваме първоначалното състояние на картата
    if (isMapVisible) {
        mapWrapper.classList.add('show');
    }
    
    // Обработка на бутона за картата
    mapToggle.addEventListener('click', function() {
        mapWrapper.classList.toggle('show');
        
        // Запазваме състоянието
        localStorage.setItem('mapVisible', mapWrapper.classList.contains('show'));
        
        // Ако картата се показва за първи път, инициализираме я
        if (mapWrapper.classList.contains('show') && !window.mapInitialized) {
            initializeMap();
        }
    });
}

/**
 * Инициализира Select2 за падащите менюта
 */
function initializeSelect2() {    if (typeof $ !== 'undefined' && $.fn.select2) {
        // Get translations from data attribute
        const propertiesData = document.getElementById('properties-data');
        let translations = { type_placeholder: 'Изберете тип имот' }; // Default fallback
        
        if (propertiesData && propertiesData.dataset.translations) {
            try {
                translations = JSON.parse(propertiesData.dataset.translations);
            } catch (e) {
                console.error('Failed to parse translations:', e);
            }
        }
        
        $('.property-type-filter').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: translations.type_placeholder,
            allowClear: true,
            templateResult: formatPropertyType
        });
    }
    
    // Форматиране на опциите в падащото меню
    function formatPropertyType(state) {
        if (!state.id) {
            return state.text; // Placeholder
        }
        
        // Добавяме клас за подкатегория
        if (state.text.startsWith('—')) {
            let $state = $(
                '<span class="child-option">' + state.text.substring(2) + '</span>'
            );
            return $state;
        }
        
        return state.text;
    }
}

/**
 * Инициализира Here Maps
 */
function initializeMap() {
    // Проверяваме дали Here Maps API е зареден
    if (typeof H === 'undefined') {
        console.error('Here Maps API не е зареден!');
        return;
    }
    
    console.log('Инициализиране на Here Maps...');
    
    // Маркираме картата като инициализирана
    window.mapInitialized = true;
    
    try {
        const mapContainer = document.getElementById('map-container');
        if (!mapContainer) return;
        
        // Вземаме данните, необходими за картата от data атрибути на контейнера
        const apiKey = mapContainer.dataset.apiKey;
        const defaultLat = parseFloat(mapContainer.dataset.defaultLat);
        const defaultLng = parseFloat(mapContainer.dataset.defaultLng);
        const defaultZoom = parseInt(mapContainer.dataset.defaultZoom);
        
        console.log('API ключ:', apiKey);
        
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
                center: { lat: defaultLat, lng: defaultLng },
                zoom: defaultZoom
            }
        );
        
        // Добавяме интерактивност и контроли
        const ui = H.ui.UI.createDefault(map, defaultLayers);
        const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
        
        // Добавяме маркери за всеки имот
        const markers = [];
        
        // Събираме маркерите от данните, закачени към DOM елементите
        document.querySelectorAll('.property-card[data-lat][data-lng]').forEach(property => {
            markers.push({
                lat: parseFloat(property.dataset.lat),
                lng: parseFloat(property.dataset.lng),
                title: property.dataset.title,
                id: property.dataset.id
            });
        });
        
        console.log('Брой маркери:', markers.length);
        
        // Добавяме маркери на картата
        markers.forEach(marker => {
            const mapMarker = new H.map.Marker({ 
                lat: marker.lat, 
                lng: marker.lng 
            });
            
            // Създаваме балон с информация за имота
            mapMarker.setData(`
                <div style="padding: 10px;">
                    <h5>${marker.title}</h5>
                    <a href="/bg/auctions/${marker.id}" class="btn btn-sm btn-primary">Детайли</a>
                </div>
            `);
            
            // Добавяме маркера към картата
            map.addObject(mapMarker);
            
            // Добавяме слушател за клик върху маркера
            mapMarker.addEventListener('tap', function(evt) {
                const bubble = new H.ui.InfoBubble(
                    evt.target.getGeometry(), 
                    { content: evt.target.getData() }
                );
                ui.addBubble(bubble);
            });
        });
        
        // Коригираме зума, за да се виждат всички маркери
        if (markers.length > 0) {
            // Ако има само един маркер, центрираме картата върху него
            if (markers.length === 1) {
                map.setCenter({ lat: markers[0].lat, lng: markers[0].lng });
                map.setZoom(15);
            } else {
                // Ако има повече маркери, мащабираме картата, за да се виждат всички
                const group = new H.map.Group();
                markers.forEach(marker => {
                    group.addObject(new H.map.Marker({ lat: marker.lat, lng: marker.lng }));
                });
                map.addObject(group);
                map.getViewModel().setLookAtData({
                    bounds: group.getBoundingBox()
                });
            }
        }
        
        // Ресайз на картата при промяна на размера на прозореца
        window.addEventListener('resize', () => {
            map.getViewPort().resize();
        });
        
        console.log('Here Maps инициализирана успешно!');
    } catch (error) {
        console.error('Грешка при инициализиране на Here Maps:', error);
    }
} 