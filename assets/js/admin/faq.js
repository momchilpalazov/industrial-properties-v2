/**
 * Администраторски интерфейс за FAQ секцията
 */
import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', function() {
    // Toast нотификация
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.innerHTML = `
        <div id="toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    document.body.appendChild(toastContainer);

    // Инициализиране на модалния прозорец за изтриване
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const tokenInput = deleteForm.querySelector('input[name="_token"]');

    // Инициализация на Sortable.js за drag-and-drop пренареждане
    const faqList = document.getElementById('faq-list');
    new Sortable(faqList, {
        handle: '.handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function() {
            const items = faqList.children;
            const positions = {};
            
            Array.from(items).forEach((item, index) => {
                positions[item.dataset.id] = index;
            });

            fetch('/admin/faq/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ positions })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Пренареждането е запазено успешно', 'success');
                } else {
                    showToast('Възникна грешка при запазване на подредбата', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Възникна грешка при запазване на подредбата', 'danger');
            });
        }
    });

    // Обработка на бутоните за изтриване
    document.querySelectorAll('.delete-faq').forEach(button => {
        button.addEventListener('click', function() {
            const faqId = this.dataset.id;
            deleteForm.action = `/admin/faq/${faqId}/delete`;
            tokenInput.value = tokenInput.dataset.token + faqId;
            deleteModal.show();
        });
    });

    // Обработка на превключвателите за активност
    document.querySelectorAll('.toggle-active').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const faqId = this.dataset.faqId;
            
            fetch(`/admin/faq/${faqId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = this.closest('.card');
                    // Търсим статус елемента в картата
                    const statusElement = card.querySelector('.badge');
                    
                    if (statusElement) {
                        // Промяна на текста и класовете според статуса
                        statusElement.textContent = data.active ? 'Активен' : 'Неактивен';
                        
                        // Обновяване на класовете на значката
                        if (data.active) {
                            statusElement.classList.remove('bg-secondary');
                            statusElement.classList.add('bg-success');
                        } else {
                            statusElement.classList.remove('bg-success');
                            statusElement.classList.add('bg-secondary');
                        }
                    }
                    
                    // Обновяване на title атрибута
                    this.title = data.active ? 'Деактивирай' : 'Активирай';
                    
                    // Показване на toast съобщение при успех
                    showToast(data.active ? 'Активиран успешно' : 'Деактивиран успешно', 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !this.checked; // Връщане на оригиналното състояние при грешка
                showToast('Възникна грешка при обработката на заявката', 'danger');
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