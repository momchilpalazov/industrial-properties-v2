/**
 * Администраторски интерфейс за блог секцията
 */
document.addEventListener('DOMContentLoaded', function() {
    // Инициализиране на модалния прозорец за изтриване
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const tokenInput = deleteForm.querySelector('input[name="_token"]');

    // Обработка на бутоните за изтриване
    document.querySelectorAll('.delete-post').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.id;
            deleteForm.action = `/admin/blog/${postId}/delete`;
            tokenInput.value = tokenInput.dataset.token + postId;
            deleteModal.show();
        });
    });

    // Вземаме преводите от скритите елементи
    const getTranslation = (key) => {
        const element = document.querySelector(`.js-translations [data-translation="${key}"]`);
        return element ? element.textContent : '';
    };

    const publishedText = getTranslation('published');
    const draftText = getTranslation('draft');
    const publishText = getTranslation('publish');
    const hideText = getTranslation('hide');
    const publishSuccessText = getTranslation('publish_success');
    const hideSuccessText = getTranslation('hide_success');
    const errorMessageText = getTranslation('error_message');

    // Обработка на превключвателите за публикуване
    document.querySelectorAll('.toggle-published').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const postId = this.dataset.postId;
            
            fetch(`/admin/blog/${postId}/toggle-published`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const statusElement = this.closest('.card').querySelector('.blog-status');
                    
                    // Промяна на текста и класовете според статуса
                    statusElement.textContent = data.published ? publishedText : draftText;
                    statusElement.classList.toggle('status-published');
                    statusElement.classList.toggle('status-draft');
                    
                    // Обновяване на title атрибута
                    this.title = data.published ? hideText : publishText;
                    
                    // Показване на toast съобщение при успех
                    showToast(data.published ? publishSuccessText : hideSuccessText, 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !this.checked; // Връщане на оригиналното състояние при грешка
                showToast(errorMessageText, 'danger');
            });
        });
    });
    
    /**
     * Функция за показване на toast съобщение
     * @param {string} message - Съобщение за показване
     * @param {string} type - Тип на съобщението (success, danger, warning, info)
     */
    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('toast');
        const toastBody = toastEl.querySelector('.toast-body');
        
        // Премахване на всички класове за цвят
        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
        
        // Добавяне на подходящия клас за цвят
        toastEl.classList.add(`bg-${type}`);
        
        // Задаване на съобщението
        toastBody.textContent = message;
        
        // Показване на toast
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
}); 