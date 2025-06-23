/**
 * Select2 Integration for Property Pages
 */

export function initializePropertySelect2() {
    document.addEventListener('DOMContentLoaded', function() {
        if (jQuery && jQuery().select2) {
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
            
            jQuery('.property-type-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: translations.type_placeholder,
                allowClear: true,
                templateResult: formatPropertyType
            });
            
            // Форматиране на опциите в падащото меню
            function formatPropertyType(state) {
                if (!state.id) {
                    return state.text; // Placeholder
                }
                
                // Добавяме клас за подкатегория
                if (state.text.startsWith('—')) {
                    let $state = jQuery(
                        '<span class="child-option">' + state.text.substring(2) + '</span>'
                    );
                    return $state;
                }
                
                return state.text;
            }
        }
    });
} 