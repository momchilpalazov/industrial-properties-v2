/**
 * Property Filters Functionality
 */

export function initializePropertyFilters() {
    // Вземаме елементите
    const filterToggle = document.getElementById('filterToggle');
    const filterSection = document.getElementById('filterSection');
    const filtersWrapper = document.querySelector('.filters-wrapper');
    
    console.log('Filter toggle element:', filterToggle);
    console.log('Filter section element:', filterSection);
    
    if (!filterToggle || !filterSection) {
        console.error('Не са намерени елементите за филтриране!');
        return;
    }
    
    // Доколко е показан филтъра е запазено в localStorage
    let isFilterVisible = localStorage.getItem('filterVisible') === 'true';
    console.log('Initial filter visibility from localStorage:', isFilterVisible);
    
    // Задаваме първоначалното състояние на филтрите
    updateFilterVisibility(isFilterVisible);
    
    // Опростена функция за обработка на клик
    function handleFilterToggle(e) {
        e.preventDefault();
        console.log('Filter toggle clicked - before toggle isFilterVisible=', isFilterVisible);
        
        // Превключваме стойността
        isFilterVisible = !isFilterVisible;
        
        // Обновяваме UI според новата стойност
        updateFilterVisibility(isFilterVisible);
        
        // Запазваме състоянието в localStorage
        localStorage.setItem('filterVisible', isFilterVisible);
        console.log('Updated filter visibility in localStorage:', isFilterVisible);
        
        // Ако филтрите са показани, скролваме до тях 
        if (isFilterVisible && filtersWrapper) {
            window.scrollTo(0, filtersWrapper.offsetTop - 70);
        }
    }
    
    // Функция за обновяване на видимостта
    function updateFilterVisibility(isVisible) {
        console.log('Updating visibility to:', isVisible);
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
    
    console.log('Property filters initialized successfully!');
} 