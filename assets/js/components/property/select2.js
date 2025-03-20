/**
 * Select2 Integration for Property Pages
 */

export function initializePropertySelect2() {
    document.addEventListener('DOMContentLoaded', function() {
        if (jQuery && jQuery().select2) {
            jQuery('.property-type-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Изберете тип имот',
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