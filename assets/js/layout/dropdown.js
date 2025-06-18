/**
 * Dropdown menu functionality
 */
export function initializeDropdowns() {
    // Initialize all dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Специфично за езиковия dropdown - подобрена логика
    const languageDropdown = document.getElementById('languageDropdown');
    if (languageDropdown) {
        // Премахваме старите event listeners
        languageDropdown.removeEventListener('click', handleLanguageDropdown);
        
        // Добавяме нов event listener
        languageDropdown.addEventListener('click', handleLanguageDropdown);
        
        // Обработваме mobile touch events
        languageDropdown.addEventListener('touchstart', function(e) {
            e.stopPropagation();
        });
    }
}

function handleLanguageDropdown(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const dropdown = bootstrap.Dropdown.getInstance(this) || new bootstrap.Dropdown(this);
    
    // Проверяваме дали dropdown е вече отворен
    if (this.getAttribute('aria-expanded') === 'true') {
        dropdown.hide();
    } else {
        dropdown.show();
    }
}