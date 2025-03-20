// Импортираме стиловете за страницата с имоти под наем
import '../../styles/components/renting/index.scss';

/**
 * Функционалност за страницата със списък на имоти за отдаване под наем
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeFilterToggle();
    initializeSelect2();
    initializeAnimations();
});

/**
 * Инициализира функционалността на бутона за филтри
 */
function initializeFilterToggle() {
    const filterToggle = document.getElementById('filterToggle');
    const filterSection = document.getElementById('filterSection');
    const filtersWrapper = document.querySelector('.filters-wrapper');
    
    if (!filterToggle || !filterSection) return;
    
    // Проверяваме запазеното състояние на филтрите
    const isFilterVisible = localStorage.getItem('rentingFilterVisible') === 'true';
    
    // Задаваме първоначалното състояние на филтрите
    if (isFilterVisible) {
        filterSection.classList.add('show');
        filterToggle.classList.add('active');
    }
    
    filterToggle.addEventListener('click', function() {
        filterSection.classList.toggle('show');
        filterToggle.classList.toggle('active');
        
        // Запазваме състоянието
        localStorage.setItem('rentingFilterVisible', filterSection.classList.contains('show'));
        
        // Ако филтрите са показани, скролваме до тях плавно
        if (filterSection.classList.contains('show')) {
            window.scrollTo({
                top: filtersWrapper.offsetTop - 70,
                behavior: 'smooth'
            });
        }
    });
}

/**
 * Инициализира Select2 за падащите менюта
 */
function initializeSelect2() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.property-type-filter').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Изберете тип имот',
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
 * Инициализира анимациите на страницата
 */
function initializeAnimations() {
    // Използване на Intersection Observer за добавяне на анимации при скролване
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('property-card-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.2
    });
    
    // Наблюдаваме всички карти с имоти за анимация при скролване
    document.querySelectorAll('.property-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
    
    // Добавяме CSS стил за видимите карти
    const style = document.createElement('style');
    style.textContent = `
        .property-card-visible {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
    `;
    document.head.appendChild(style);
} 