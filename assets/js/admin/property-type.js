import '../components/property/select2';
import '../../styles/admin/property-type.scss';

document.addEventListener('DOMContentLoaded', function() {
    // Функция за превключване на видимостта на подкатегориите
    function toggleSubcategories(event) {
        const toggleIcon = event.currentTarget;
        const categoryId = toggleIcon.getAttribute('data-category-id');
        const subcategoriesContainer = document.getElementById(`subcategories-${categoryId}`);
        
        if (subcategoriesContainer) {
            subcategoriesContainer.classList.toggle('show');
            
            // Промяна на иконата
            if (subcategoriesContainer.classList.contains('show')) {
                toggleIcon.innerHTML = '<i class="bi bi-dash-square"></i>';
            } else {
                toggleIcon.innerHTML = '<i class="bi bi-plus-square"></i>';
            }
        }
    }
    
    // Функция за превключване на видимостта на типовете в категория
    function toggleCategoryTypes(event) {
        const toggleIcon = event.currentTarget;
        const categoryId = toggleIcon.getAttribute('data-category-id');
        const typesContainer = document.getElementById(`category-types-${categoryId}`);
        
        if (typesContainer) {
            typesContainer.classList.toggle('hidden');
            
            // Промяна на иконата и класа на стрелката
            const arrowIcon = toggleIcon.querySelector('.tree-arrow');
            if (typesContainer.classList.contains('hidden')) {
                toggleIcon.querySelector('i').classList.remove('bi-chevron-down');
                toggleIcon.querySelector('i').classList.add('bi-chevron-right');
                if (arrowIcon) {
                    arrowIcon.classList.remove('rotated');
                }
            } else {
                toggleIcon.querySelector('i').classList.remove('bi-chevron-right');
                toggleIcon.querySelector('i').classList.add('bi-chevron-down');
                if (arrowIcon) {
                    arrowIcon.classList.add('rotated');
                }
            }
        }
    }
    
    // Добавяне на слушатели за събития към всички бутони за превключване на подкатегории
    const toggleIcons = document.querySelectorAll('.toggle-subcategories');
    toggleIcons.forEach(icon => {
        icon.addEventListener('click', toggleSubcategories);
    });
    
    // Добавяне на слушатели за събития към всички бутони за превключване на типове в категория
    const toggleCategoryIcons = document.querySelectorAll('.toggle-category-types');
    toggleCategoryIcons.forEach(icon => {
        icon.addEventListener('click', toggleCategoryTypes);
    });
    
    // Автоматично разгъване на всички категории при зареждане
    toggleIcons.forEach(icon => {
        icon.click();
    });

    // Потвърждение при изтриване на тип имот
    document.querySelectorAll('.delete-property-type-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Сигурни ли сте, че искате да изтриете този тип имот?')) {
                e.preventDefault();
            }
        });
    });
}); 