/**
 * Dropdown menu functionality
 */
export function initializeDropdowns() {
    // Initialize all dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Специфично за езиковия dropdown
    const languageDropdown = document.getElementById('languageDropdown');
    if (languageDropdown) {
        languageDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = bootstrap.Dropdown.getInstance(languageDropdown) || new bootstrap.Dropdown(languageDropdown);
            dropdown.toggle();
        });
    }
}