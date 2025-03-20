/**
 * Функционалност за валидация на формата
 */

export function initializeFormValidation() {
    const form = document.querySelector('form.needs-validation');
    
    if (!form) {
        console.error('Form not found!');
        return;
    }
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
} 